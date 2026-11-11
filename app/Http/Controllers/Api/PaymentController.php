<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * POST /api/payments — AUTH:sanctum
     * Crée un paiement + une inscription + une commande (facture)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'course_id'       => ['required', 'integer', 'exists:courses,id'],
            'payment_method'  => ['required', 'in:orange,moov,telecel,card'],
            'phone_number'    => ['nullable', 'string', 'max:20'],
            'promo_code'      => ['nullable', 'string', 'max:50'],
        ]);

        $course = Course::findOrFail($data['course_id']);
        $user   = $request->user();

        // ✅ Vérifier si l'utilisateur est déjà inscrit
        $alreadyEnrolled = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->exists();

        if ($alreadyEnrolled) {
            return response()->json([
                'message' => 'Vous êtes déjà inscrit à ce cours.',
            ], 409);
        }

        // ✅ Calcul du prix (simulation — plus tard : API Ligdicash/CinetPay)
        $subtotal = $course->price ?? 0;
        $discount = 0;
        if (!empty($data['promo_code'])) {
            // TODO : logique de promotion réelle
            $discount = match (strtoupper($data['promo_code'])) {
                'KMAA10'   => round($subtotal * 0.10),
                'BIENVENUE'=> round($subtotal * 0.15),
                'FIDELITE' => round($subtotal * 0.20),
                'AUDIT5000'=> min(5000, $subtotal),
                default    => 0,
            };
        }
        $totalHT = max(0, $subtotal - $discount);
        $tva     = round($totalHT * 0.18);
        $totalTTC = $totalHT + $tva;

        // ✅ Générer un ID de transaction simulé
        $transactionId = 'SIM-' . strtoupper(Str::random(10));

        // ✅ Créer le paiement
        $payment = Payment::create([
            'user_id'               => $user->id,
            'course_id'             => $course->id,
            'pricing_plan_id'       => null,  // pas de plan individuel
            'amount'                => $totalTTC,
            'provider'              => $data['payment_method'],
            'phone_number'          => $data['phone_number'] ?? null,
            'status'                => 'paye',
            'transaction_reference' => $transactionId,
        ]);

        // ✅ Créer l'inscription
        Enrollment::firstOrCreate(
            ['user_id' => $user->id, 'course_id' => $course->id],
            ['enrolled_at' => now()]
        );

        // ✅ Créer la commande (facture)
        $methodLabels = [
            'orange' => 'Orange Money',
            'moov'   => 'Moov Money',
            'telecel' => 'Telecel Money',
            'card'   => 'Carte bancaire',
        ];

        $order = Order::create([
            'user_id'              => $user->id,
            'course_id'            => $course->id,
            'type'                 => 'course',
            'reference'            => Order::generateReference(),
            'invoice_number'       => Order::generateInvoiceNumber(),
            'subtotal'             => $subtotal,
            'discount'             => $discount,
            'tva'                  => $tva,
            'total'                => $totalTTC,
            'currency'             => 'FCFA',
            'promo_code'           => $data['promo_code'] ?? null,
            'payment_method'       => $data['payment_method'],
            'payment_method_label' => $methodLabels[$data['payment_method']] ?? 'Autre',
            'transaction_id'       => $transactionId,
            'customer_name'        => $user->name,
            'customer_email'       => $user->email,
            'status'               => 'paid',
            'paid_at'              => now(),
        ]);

        // ✅ Notification
        try {
            \App\Services\NotificationService::send(
                'paiement',
                'Paiement confirmé — merci !',
                'Ton paiement de ' . number_format($totalTTC, 0, ',', ' ') . ' FCFA pour « ' . $course->title . ' » a bien été reçu.',
                $user,
                $course->slug,
            );
        } catch (\Throwable $e) {
            \Log::warning('Notification paiement : ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Paiement confirmé. Inscription effectuée.',
            'data'    => [
                'payment_id'            => $payment->id,
                'transaction_reference' => $transactionId,
                'order_uuid'            => $order->uuid,
                'order_id'              => $order->id,
            ],
        ], 201);
    }
}