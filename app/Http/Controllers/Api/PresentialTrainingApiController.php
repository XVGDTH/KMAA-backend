<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PresentialTraining;
use Illuminate\Http\Request;

class PresentialTrainingApiController extends Controller
{
    // ============================================================
    // GET /api/formateur/presential-trainings
    // Liste des formations présentielles du formateur connecté
    // (ou toutes, si admin).
    // ============================================================
    public function index(Request $request)
    {
        $user = $request->user();

        $query = PresentialTraining::query()
            ->withCount('participants')
            ->orderByDesc('starts_at');

        // Formateur : uniquement ses formations. Admin : tout.
        $isAdmin = method_exists($user, 'hasRole')
            ? $user->hasRole('admin')
            : ($user->role ?? null) === 'admin';

        if (! $isAdmin) {
            $query->where('created_by', $user->id);
        }

        $trainings = $query->get();

        return response()->json([
            'data' => $trainings->map(fn (PresentialTraining $t) => $this->format($t)),
        ]);
    }

    // ============================================================
    // POST /api/formateur/presential-trainings
    // Créer une nouvelle formation présentielle
    // ============================================================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'                 => ['required', 'string', 'max:255'],
            'description'           => ['nullable', 'string', 'max:5000'],
            'starts_at'             => ['required', 'date'],
            'ends_at'               => ['required', 'date', 'after_or_equal:starts_at'],
            'days_count'            => ['required', 'integer', 'min:1', 'max:365'],
            'hours_count'           => ['nullable', 'integer', 'min:1', 'max:1000'],
            'location'              => ['nullable', 'string', 'max:255'],
            'instructor_name'       => ['nullable', 'string', 'max:255'],
            'instructor_role'       => ['nullable', 'string', 'max:255'],
            // 'expected_participants' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'max_participants' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'certificate_type'      => ['required', 'in:certificate,attestation'],
        ]);

        $training = PresentialTraining::create([
            ...$validated,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Formation présentielle créée.',
            'data'    => $this->format($training),
        ], 201);
    }

    // ============================================================
    // GET /api/formateur/presential-trainings/{training}
    // ============================================================
    // public function show(Request $request, PresentialTraining $training)
    // {
    //     $this->authorizeOwner($request, $training);

    //     $training->load('participants');

    //     return response()->json([
    //         'data' => array_merge(
    //             $this->format($training),
    //             [
    //                 'participants' => $training->participants->map(fn ($p) => [
    //                     'id'                => $p->id,
    //                     'name'              => $p->name,
    //                     'email'             => $p->email,
    //                     'phone'             => $p->phone,
    //                     'registeredAt'      => $p->registered_at?->toIso8601String(),
    //                     'certificateSentAt' => $p->certificate_sent_at?->toIso8601String(),
    //                     'revoked'           => (bool) $p->revoked,
    //                     'revocationReason'  => $p->revocation_reason,
    //                 ])->values(),
    //             ]
    //         ),
    //     ]);
    // }

    public function show(Request $request, PresentialTraining $training)
{
    $this->authorizeOwner($request, $training);

    $training->load('participants.certificate');

    return response()->json([
        'data' => array_merge(
            $this->format($training),
            [
                'participants' => $training->participants->map(fn ($p) => [
                    'id'                => $p->id,
                    'name'              => $p->name,
                    'email'             => $p->email,
                    'phone'             => $p->phone,
                    'registeredAt'      => $p->registered_at?->format('d/m/Y H:i'),
                    'certificateSentAt' => $p->certificate_sent_at?->format('d/m/Y H:i'),
                    'revoked'           => (bool) $p->revoked,
                    'revocationReason'  => $p->revocation_reason,
                    // 🆕 Certificat
                    'certificateId'     => $p->certificate?->id,
                    'certificateUuid'   => $p->certificate?->uuid,
                    'certificateRef'    => $p->certificate?->reference,
                    'certificateUrl'    => $p->certificate
                        ? route('certificates.download', $p->certificate->uuid)
                        : null,
                ])->values(),
            ]
        ),
    ]);
}

    // ============================================================
    // DELETE /api/formateur/presential-trainings/{training}
    // ============================================================
    public function destroy(Request $request, PresentialTraining $training)
    {
        $this->authorizeOwner($request, $training);

        $training->delete();

        return response()->json(['message' => 'Formation supprimée.']);
    }

    // ============================================================
    // HELPERS
    // ============================================================

    private function authorizeOwner(Request $request, PresentialTraining $training): void
    {
        $user = $request->user();

        $isAdmin = method_exists($user, 'hasRole')
            ? $user->hasRole('admin')
            : ($user->role ?? null) === 'admin';

        if (! $isAdmin && (int) $training->created_by !== (int) $user->id) {
            abort(403, 'Vous n\'êtes pas autorisé à modifier cette formation.');
        }
    }

    private function format(PresentialTraining $t): array
    {
        return [
            'id'                    => $t->id,
            'title'                 => $t->title,
            'description'           => $t->description,
            'starts_at'             => $t->starts_at?->format('Y-m-d'),
            'ends_at'               => $t->ends_at?->format('Y-m-d'),
            'days_count'            => $t->days_count,
            'hours_count'           => $t->hours_count,
            'location'              => $t->location,
            'instructor_name'       => $t->instructor_name,
            'instructor_role'       => $t->instructor_role,
            // 'expected_participants' => $t->expected_participants,
            'max_participants' => $t->max_participants,   
            'registered_count'      => $t->registered_count ?? 0,
            'slug'                  => $t->slug,
            'registration_token'    => $t->registration_token,
            'registration_open'     => (bool) $t->registration_open,
            'certificate_type'      => $t->certificate_type,
            'registration_url'      => $t->getRegistrationUrl(),
            'created_at'            => $t->created_at?->toIso8601String(),
        ];
    }
}