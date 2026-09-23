<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class CertificateController extends Controller
{
    // ============================================================
    // POST /api/courses/{slug}/certificate (protégée)
    // Génère (ou réutilise) le certificat d'un apprenant à 100%.
    // ============================================================
    public function generate(Request $request, string $slug)
    {
        $course = Course::where('slug', $slug)->firstOrFail();

        $enrollment = Enrollment::where('user_id', $request->user()->id)
            ->where('course_id', $course->id)
            ->firstOrFail();

        // 🔒 GATING CERTIFICAT : tous les quiz du cours doivent être
        // passés avec au moins 85%.
        $failedQuizzes = \App\Models\Sequence::whereHas('chapter', function ($q) use ($course) {
                $q->where('course_id', $course->id);
            })
            ->where('type', 'quiz')
            ->whereDoesntHave('progress', function ($q) use ($enrollment) {
                $q->where('enrollment_id', $enrollment->id)
                  ->where('passed', true);
            })
            ->pluck('title');

        if ($failedQuizzes->isNotEmpty()) {
            return response()->json([
                'message'        => 'Certains quiz ne sont pas encore validés (85% minimum requis) : ' . $failedQuizzes->implode(', '),
                'failedQuizzes'  => $failedQuizzes,
            ], 403);
        }

        if (! $enrollment->completed_at) {
            $enrollment->update(['completed_at' => now()]);
        }

        $enrollment->refresh();

        $completedAt = $enrollment->completed_at
            ? \Carbon\Carbon::parse($enrollment->completed_at)
            : null;

        $durationHours = $completedAt
            ? (int) $completedAt->diffInHours($enrollment->created_at)
            : 1;

        $durationHours = max(1, $durationHours);

        $certificate = Certificate::firstOrCreate(
            ['enrollment_id' => $enrollment->id],
            [
                'uuid'           => (string) Str::uuid(),
                'reference'      => Certificate::generateReference(),
                'type'           => $course->diploma ?? 'certificate',
                'duration_hours' => $durationHours,
                'issued_at'      => now(),
            ]
        );

        return response()->json([
            'data' => [
                ...$certificate->toArray(),
                'downloadUrl' => route('certificates.download', $certificate->uuid),
            ],
        ]);
    }

    // ============================================================
    // GET /api/certificates/{uuid}/download (publique)
    // Télécharge le PDF d'un seul certificat.
    // ============================================================
    public function download(string $uuid)
    {
        $certificate = Certificate::where('uuid', $uuid)->firstOrFail();

        // Bloque les certificats révoqués
        if ($certificate->revoked) {
            abort(403, 'Ce certificat a été révoqué.');
        }

        // Fabrique le PDF
        $pdf = $this->renderPdf($certificate);

        // Télécharge
        $label = $certificate->type === 'attestation' ? 'Attestation' : 'Certificat';

        return $pdf->download("KMAA-{$label}-{$certificate->reference}.pdf");
    }

    // ============================================================
    // GET /admin/certificates/download-zip?ids=1,2,3
    // Télécharge plusieurs certificats dans un ZIP.
    // ============================================================
    public function downloadZip(Request $request)
    {
        $ids = array_filter(explode(',', $request->query('ids', '')));

        if (empty($ids)) {
            abort(400, 'Aucun certificat sélectionné.');
        }

        $certificates = Certificate::whereIn('id', $ids)->get();

        if ($certificates->isEmpty()) {
            abort(404, 'Aucun certificat trouvé.');
        }

        $zipName = 'certificats-' . now()->format('Y-m-d-His') . '.zip';
        $tempDir = storage_path('app/temp');

        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zipPath = "{$tempDir}/{$zipName}";

        $zip = new \ZipArchive();

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Impossible de créer le ZIP.');
        }

        foreach ($certificates as $certificate) {
            // On saute les révoqués
            if ($certificate->revoked) {
                continue;
            }

            try {
                $pdf      = $this->renderPdf($certificate);
                $filename = "KMAA-{$certificate->reference}.pdf";

                $zip->addFromString($filename, $pdf->output());
            } catch (\Throwable $e) {
                \Log::warning("Impossible de générer le PDF pour {$certificate->reference}: " . $e->getMessage());
                continue;
            }
        }

        $zip->close();

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    // ============================================================
    // 🔧 MÉTHODE PRIVÉE — fabrique le PDF
    // Utilisée par download() ET downloadZip()
    // NE TÉLÉCHARGE PAS, retourne juste l'objet PDF
    // ============================================================
    private function renderPdf(Certificate $certificate): \Barryvdh\DomPDF\PDF
    {
        // ============================================================
        // CAS 1 : Certificat en ligne (enrollment)
        // ============================================================
        if ($certificate->enrollment_id) {
            $enrollment = $certificate->enrollment;
            $course     = $enrollment->course;
            $student    = $enrollment->user;

            $studentName    = $student->name;
            $courseTitle    = $course->title;
            $instructorName = $course->instructor?->name ?? '—';
            $startedAt      = $enrollment->enrolled_at?->format('d/m/Y') ?? '—';
            $endedAt        = $enrollment->completed_at?->format('d/m/Y') ?? '—';
            $promotionLabel = $course->promotion_label ?? null;
        }
        // ============================================================
        // CAS 2 : Certificat présentiel
        // ============================================================
        else {
            $training = $certificate->presentialTraining;

            $studentName    = $certificate->student_name ?? '—';
            $courseTitle    = $certificate->course_title ?? ($training?->title ?? '—');
            $instructorName = $training?->instructor_name ?? '—';
            $startedAt      = $training?->starts_at?->format('d/m/Y') ?? '—';
            $endedAt        = $training?->ends_at?->format('d/m/Y') ?? '—';
            $promotionLabel = null;
        }

        // ============================================================
        // QR code
        // ============================================================
        $verifyUrl = route('certificates.verify.page', $certificate->uuid);

        $options = new QROptions([
            'outputType'   => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel'     => QRCode::ECC_L,
            'scale'        => 6,
            'marginModule' => 2,
            'imageBase64'  => false,
        ]);

        $qrCodeBase64 = base64_encode((new QRCode($options))->render($verifyUrl));

        // ============================================================
        // Choix du template
        // ============================================================
        $view = $certificate->type === 'attestation'
            ? 'certificates.attestation'
            : 'certificates.certificate';

        // ============================================================
        // Construction du PDF
        // ============================================================
        return Pdf::loadView($view, [
            'studentName'    => $studentName,
            'courseTitle'    => $courseTitle,
            'certificate'    => $certificate,
            'issuedAt'       => $certificate->issued_at->format('d F Y'),
            'reference'      => $certificate->reference,
            'uuid'           => $certificate->uuid,
            'qrCode'         => $qrCodeBase64,
            'logoPath'       => public_path('images/logo.png'),
            'durationHours'  => $certificate->duration_hours,
            'instructorName' => $instructorName,
            'startedAt'      => $startedAt,
            'endedAt'        => $endedAt,
            'promotionLabel' => $promotionLabel,
        ])->setPaper('a4', 'landscape');
    }

    // ============================================================
    // GET /api/certificates/{uuid}/verify (publique)
    // ============================================================
    public function verify(string $uuid)
    {
        $certificate = Certificate::where('uuid', $uuid)
            ->with('enrollment.course', 'enrollment.user')
            ->first();

        if (! $certificate) {
            return response()->json(['valid' => false], 404);
        }

        // Gère les 2 cas (en ligne / présentiel)
        $studentName = $certificate->student_name
            ?? $certificate->enrollment?->user?->name
            ?? '—';

        $courseTitle = $certificate->course_title
            ?? $certificate->enrollment?->course?->title
            ?? '—';

        return response()->json([
            'valid' => true,
            'data'  => [
                'reference'   => $certificate->reference,
                'type'        => $certificate->type,
                'studentName' => $studentName,
                'courseTitle' => $courseTitle,
                'issuedAt'    => $certificate->issued_at->format('d F Y'),
            ],
        ]);
    }
}