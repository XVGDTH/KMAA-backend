<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    // POST /api/payments — AUTH:sanctum (appelé à la validation de l'OTP)
    public function store(Request $request)
    {
        $data = $request->validate([
            'course_slug'      => ['required', 'string', 'exists:courses,slug'],
            'pricing_plan_id'  => ['nullable', 'integer', 'exists:pricing_plans,id'],
            'amount'           => ['required', 'integer', 'min:0'],
            'provider'         => ['required', 'in:orange,moov,coris'],
            'phone_number'     => ['nullable', 'string', 'max:50'],
        ]);

        $course = Course::where('slug', $data['course_slug'])->firstOrFail();
        $user   = $request->user();

        // Déjà inscrit ? → on refuse proprement
        $alreadyEnrolled = DB::table('enrollments')
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->exists();

        if ($alreadyEnrolled) {
            return response()->json([
                'message' => 'Vous êtes déjà inscrit à ce cours.',
            ], 409);
        }

        $payment = Payment::create([
            'user_id'                => $user->id,
            'course_id'              => $course->id,
            'pricing_plan_id'        => $data['pricing_plan_id'] ?? null,
            'amount'                 => $data['amount'],
            'provider'               => $data['provider'],
            'phone_number'           => $data['phone_number'] ?? null,
            'status'                 => 'paye',
            'transaction_reference'  => 'SIM-' . strtoupper(Str::random(10)),
        ]);

        // ✅ NOTIFICATION : paiement confirmé → l'apprenant
        \App\Services\NotificationService::send(
            'paiement',
            'Paiement confirmé — merci !',
            'Ton paiement de ' . number_format($payment->amount, 0, ',', ' ') . ' FCFA pour « ' . $course->title . ' » a bien été reçu. Bon apprentissage !',
            $user,
            $course->slug,
        );

        return response()->json([
            'message' => 'Paiement confirmé. Procédure d\'inscription en cours…',
            'data'    => [
                'payment_id'            => $payment->id,
                'transaction_reference' => $payment->transaction_reference,
            ],
        ], 201);
    }
}