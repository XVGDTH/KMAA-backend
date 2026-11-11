<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\EnterpriseLicense;
use App\Models\EnterpriseLicenseMember;
use App\Models\EnterprisePricingPlan;
use App\Models\Order;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnterpriseLicenseController extends Controller
{
    private const TVA_RATE = 0.18;

    // ============================================================
    // GET /api/enterprise/plans — PUBLIC
    // ============================================================
    public function plans()
    {
        try {
            $plans = EnterprisePricingPlan::orderBy('sort_order')->get();

            return response()->json([
                'data' => $plans->map(fn ($p) => [
                    'id'           => (string) $p->id,
                    'slug'         => $p->slug,
                    'name'         => $p->name,
                    'pricePerSeat' => (int) $p->price_per_seat,
                    'minSeats'     => $p->min_seats,
                    'maxSeats'     => $p->max_seats,
                    'badge'        => $p->badge,
                    'description'  => $p->description,
                    'features'     => $p->features ?? [],
                    'featured'     => (bool) $p->featured,
                ]),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Erreur plans entreprise : ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur serveur.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ============================================================
    // GET /api/me/enterprise-licenses — AUTH
    // ============================================================
    public function myLicenses(Request $request)
    {
        $user = $request->user();

        $licenses = EnterpriseLicense::with(['course', 'plan', 'members'])
            ->where('owner_id', $user->id)
            ->where('status', 'active')
            ->latest()
            ->get();

        return response()->json([
            'data' => $licenses->map(fn ($l) => $this->format($l)),
        ]);
    }

    // ============================================================
    // GET /api/enterprise-licenses/{uuid} — AUTH
    // Détail d'une licence (owner ou membre)
    // ============================================================
    public function show(Request $request, string $uuid)
    {
        $user = $request->user();

        $license = EnterpriseLicense::with(['course', 'plan', 'members', 'owner'])
            ->where('uuid', $uuid)
            ->first();

        if (! $license) {
            return response()->json(['message' => 'Licence introuvable.'], 404);
        }

        if (! $this->userCanView($license, $user)) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        // Récupérer la commande liée (via transaction_id ou promo ?)
        // On stocke le transaction_id dans la licence via meta si besoin.
        // Pour l'instant on retourne la licence seule.
        return response()->json([
            'data' => $this->format($license),
        ]);
    }

    // ============================================================
    // GET /api/enterprise-licenses/access?courseId=X — AUTH
    // Vérifie si l'utilisateur connecté a accès à un cours via licence
    // ============================================================
    public function access(Request $request)
    {
        $data = $request->validate([
            'course_id' => 'required|integer|exists:courses,id',
        ]);

        $user = $request->user();
        $email = strtolower($user->email);

        $license = EnterpriseLicense::with('members')
            ->where('course_id', $data['course_id'])
            ->where('status', 'active')
            ->where(function ($q) use ($user, $email) {
                $q->where('owner_id', $user->id)
                  ->orWhereHas('members', function ($m) use ($email) {
                      $m->whereRaw('LOWER(email) = ?', [$email])
                        ->where('status', '!=', 'revoked');
                  });
            })
            ->first();

        if (! $license) {
            return response()->json([
                'hasAccess' => false,
                'license'   => null,
            ]);
        }

        $role = $license->owner_id === $user->id ? 'owner' : 'member';

        return response()->json([
            'hasAccess'  => true,
            'role'       => $role,
            'licenseId'  => $license->uuid,
            'expiresAt'  => $license->expires_at?->toIso8601String(),
        ]);
    }

    // ============================================================
    // POST /api/enterprise-licenses — AUTH
    // Crée la licence + membres + order en une transaction
    // ============================================================
    public function store(Request $request)
    {
        $data = $request->validate([
            'course_id'          => 'required|exists:courses,id',
            'plan_id'            => 'required|exists:enterprise_pricing_plans,id',
            'seats_total'        => 'required|integer|min:1|max:100',
            'company_name'       => 'required|string|max:255',
            'company_email'      => 'required|email',
            'company_phone'      => 'nullable|string|max:20',
            'company_address'    => 'nullable|string',
            'company_tax_id'     => 'nullable|string',
            'promo_code'         => 'nullable|string|max:50',
            'payment_method'     => 'required|in:virement,orange,moov,telecel,card',
            'members'            => 'nullable|array',
            'members.*.email'    => 'required|email|distinct',
            'members.*.name'     => 'nullable|string',
        ]);

        $plan = EnterprisePricingPlan::findOrFail($data['plan_id']);
        $course = Course::findOrFail($data['course_id']);
        $user = $request->user();

        // ---- Vérifications métier ----
        if ($data['seats_total'] < $plan->min_seats) {
            return response()->json([
                'message' => "Le plan {$plan->name} requiert au moins {$plan->min_seats} place(s).",
            ], 422);
        }
        if ($data['seats_total'] > $plan->max_seats) {
            return response()->json([
                'message' => "Le plan {$plan->name} est limité à {$plan->max_seats} place(s).",
            ], 422);
        }

        $membersCount = count($data['members'] ?? []);
        if ($membersCount > $data['seats_total'] - 1) {
            return response()->json([
                'message' => "Trop d'invités : max " . ($data['seats_total'] - 1) . " (hors propriétaire).",
            ], 422);
        }

        // ---- Calcul du sous-total et promo ----
        $subtotal = $plan->price_per_seat * $data['seats_total'];
        $promo = null;
        $discount = 0;

        if (! empty($data['promo_code'])) {
            $promo = PromoCode::active()
                ->where('code', strtoupper(trim($data['promo_code'])))
                ->first();

            if ($promo) {
                $error = $promo->validationError($subtotal, $data['seats_total']);
                if ($error === null) {
                    $discount = $promo->computeDiscount($subtotal);
                }
            }
        }

        $totalHT = max(0, $subtotal - $discount);
        $tva = (int) round($totalHT * self::TVA_RATE);
        $totalTTC = $totalHT + $tva;

        // ---- Transaction ----
        try {
            $result = DB::transaction(function () use (
                $user, $course, $plan, $data, $promo, $subtotal, $discount, $tva, $totalTTC
            ) {
                // 1) Licence
                $license = EnterpriseLicense::create([
                    'course_id'       => $course->id,
                    'plan_id'         => $plan->id,
                    'owner_id'        => $user->id,
                    'company_name'    => $data['company_name'],
                    'company_email'   => $data['company_email'],
                    'company_phone'   => $data['company_phone'] ?? null,
                    'company_address' => $data['company_address'] ?? null,
                    'company_tax_id'  => $data['company_tax_id'] ?? null,
                    'seats_total'     => $data['seats_total'],
                    'seats_used'      => 1,
                    'price_per_seat'  => $plan->price_per_seat,
                    'total_paid'      => $totalTTC,
                    'discount_applied'=> $discount,
                    'promo_code'      => $promo?->code,
                    'status'          => 'active',
                    'purchased_at'    => now(),
                    'expires_at'      => now()->addYear(),
                ]);

                // 2) Membres invités
                $seatsUsed = 1;
                foreach ($data['members'] ?? [] as $m) {
                    EnterpriseLicenseMember::create([
                        'license_id' => $license->id,
                        'email'      => strtolower($m['email']),
                        'name'       => $m['name'] ?? null,
                        'status'     => 'invite',
                        'invited_at' => now(),
                    ]);
                    $seatsUsed++;
                }
                if ($seatsUsed !== $license->seats_used) {
                    $license->update(['seats_used' => $seatsUsed]);
                }

                // 3) Order (facture)
                $transactionId = 'TX-' . strtoupper(bin2hex(random_bytes(4)));
                $order = Order::create([
                    'user_id'              => $user->id,
                    'course_id'            => $course->id,
                    'type'                 => 'enterprise',
                    'reference'            => Order::generateReference(),
                    'invoice_number'       => Order::generateInvoiceNumber(),
                    'subtotal'             => $subtotal,
                    'discount'             => $discount,
                    'tva'                  => $tva,
                    'total'                => $totalTTC,
                    'currency'             => 'FCFA',
                    'promo_code'           => $promo?->code,
                    'payment_method'       => $data['payment_method'],
                    'payment_method_label' => $this->paymentMethodLabel($data['payment_method']),
                    'transaction_id'       => $transactionId,
                    'customer_name'        => $data['company_name'],
                    'customer_email'       => $data['company_email'],
                    'status'               => 'paid',
                    'paid_at'              => now(),
                ]);

                // 4) Incrément promo
                if ($promo) {
                    $promo->increment('used_count');
                }

                return ['license' => $license, 'order' => $order];
            });
        } catch (\Throwable $e) {
            \Log::error('Erreur création licence entreprise : ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de la création de la licence.',
                'error'   => $e->getMessage(),
            ], 500);
        }

        $license = $result['license']->fresh(['course', 'plan', 'members']);
        $order   = $result['order'];

        return response()->json([
            'data'  => $this->format($license),
            'order' => [
                'uuid'           => $order->uuid,
                'reference'      => $order->reference,
                'invoiceNumber'  => $order->invoice_number,
                'subtotal'       => $order->subtotal,
                'discount'       => $order->discount,
                'tva'            => $order->tva,
                'total'          => $order->total,
                'currency'       => $order->currency,
                'status'         => $order->status,
                'paidAt'         => $order->paid_at?->toIso8601String(),
                'transactionId'  => $order->transaction_id,
            ],
        ], 201);
    }

    // ============================================================
    // HELPERS
    // ============================================================
    private function userCanView(EnterpriseLicense $license, $user): bool
    {
        if ($license->owner_id === $user->id) return true;

        $email = strtolower($user->email);
        return $license->members->contains(
            fn ($m) => strtolower($m->email) === $email && $m->status !== 'revoked'
        );
    }

    private function paymentMethodLabel(string $method): string
    {
        return [
            'orange'   => 'Orange Money',
            'moov'     => 'Moov Money',
            'telecel'  => 'Telecel Money',
            'card'     => 'Carte bancaire',
            'virement' => 'Virement bancaire',
        ][$method] ?? $method;
    }

    private function format(EnterpriseLicense $license): array
    {
        return [
            'id'             => $license->uuid,
            'courseId'       => $license->course_id,
            'courseSlug'     => $license->course?->slug,
            'courseTitle'    => $license->course?->title,
            'courseImage'    => $license->course?->image,
            'planId'         => (string) $license->plan_id,
            'planName'       => $license->plan?->name,
            'seatsTotal'     => $license->seats_total,
            'seatsUsed'      => $license->seats_used,
            'seatsRemaining' => $license->seatsRemaining(),
            'pricePerSeat'   => $license->price_per_seat,
            'totalPaid'      => $license->total_paid,
            'discountApplied'=> $license->discount_applied,
            'promoCode'      => $license->promo_code,
            'status'         => $license->status,
            'purchasedAt'    => $license->purchased_at?->toIso8601String(),
            'expiresAt'      => $license->expires_at?->toIso8601String(),
            'companyName'    => $license->company_name,
            'companyEmail'   => $license->company_email,
            'members'        => $license->members->map(fn ($m) => [
                'id'       => (string) $m->id,
                'email'    => $m->email,
                'name'     => $m->name,
                'status'   => $m->status,
                'joinedAt' => $m->joined_at?->toIso8601String(),
            ]),
        ];
    }
}