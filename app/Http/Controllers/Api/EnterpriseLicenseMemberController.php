<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EnterpriseLicense;
use App\Models\EnterpriseLicenseMember;
use Illuminate\Http\Request;

class EnterpriseLicenseMemberController extends Controller
{
    // ============================================================
    // GET /api/enterprise-licenses/{uuid}/members — AUTH
    // Liste des membres (owner uniquement)
    // ============================================================
    public function index(Request $request, string $uuid)
    {
        $license = $this->findLicenseForOwner($uuid, $request);

        if (! $license) {
            return response()->json(['message' => 'Licence introuvable ou accès refusé.'], 404);
        }

        return response()->json([
            'data' => $license->members->map(fn ($m) => $this->formatMember($m)),
            'meta' => [
                'seatsTotal'     => $license->seats_total,
                'seatsUsed'      => $license->seats_used,
                'seatsRemaining' => $license->seatsRemaining(),
            ],
        ]);
    }

    // ============================================================
    // POST /api/enterprise-licenses/{uuid}/members — AUTH
    // Inviter un membre
    // ============================================================
    public function store(Request $request, string $uuid)
    {
        $license = $this->findLicenseForOwner($uuid, $request);

        if (! $license) {
            return response()->json(['message' => 'Licence introuvable ou accès refusé.'], 404);
        }

        $data = $request->validate([
            'email' => 'required|email',
            'name'  => 'nullable|string|max:255',
        ]);

        $email = strtolower(trim($data['email']));

        if ($license->seatsRemaining() <= 0) {
            return response()->json(['message' => 'Plus de place disponible.'], 422);
        }

        if ($license->members()->whereRaw('LOWER(email) = ?', [$email])->exists()) {
            return response()->json(['message' => 'Cet email est déjà invité.'], 422);
        }

        $member = EnterpriseLicenseMember::create([
            'license_id' => $license->id,
            'email'      => $email,
            'name'       => $data['name'] ?? null,
            'status'     => 'invite',
            'invited_at' => now(),
        ]);

        $license->update(['seats_used' => $license->seats_used + 1]);

        // TODO Phase 3.5 : envoyer le mail d'invitation ici
        // Mail::to($email)->queue(new EnterpriseLicenseInvitation($license, $member));

        return response()->json([
            'data'    => $this->formatMember($member),
            'message' => "Invitation envoyée à {$email}.",
        ], 201);
    }

    // ============================================================
    // DELETE /api/enterprise-licenses/{uuid}/members/{memberId} — AUTH
    // Retirer un membre (révoque l'accès)
    // ============================================================
    public function destroy(Request $request, string $uuid, string $memberId)
    {
        $license = $this->findLicenseForOwner($uuid, $request);

        if (! $license) {
            return response()->json(['message' => 'Licence introuvable ou accès refusé.'], 404);
        }

        $member = $license->members()->where('id', $memberId)->first();

        if (! $member) {
            return response()->json(['message' => 'Membre introuvable.'], 404);
        }

        $member->delete();
        $license->update([
            'seats_used' => max(1, $license->seats_used - 1),
        ]);

        return response()->json(['message' => 'Membre retiré.']);
    }

    // ============================================================
    // POST /api/enterprise-licenses/{uuid}/members/{memberId}/resend — AUTH
    // Renvoyer l'invitation
    // ============================================================
    public function resend(Request $request, string $uuid, string $memberId)
    {
        $license = $this->findLicenseForOwner($uuid, $request);

        if (! $license) {
            return response()->json(['message' => 'Licence introuvable ou accès refusé.'], 404);
        }

        $member = $license->members()->where('id', $memberId)->first();

        if (! $member) {
            return response()->json(['message' => 'Membre introuvable.'], 404);
        }

        if ($member->status !== 'invite') {
            return response()->json(['message' => 'Ce membre est déjà actif.'], 422);
        }

        $member->update(['invited_at' => now()]);

        // TODO Phase 3.5 : renvoyer le mail
        // Mail::to($member->email)->queue(new EnterpriseLicenseInvitation($license, $member));

        return response()->json([
            'message' => "Invitation renvoyée à {$member->email}.",
        ]);
    }

    // ============================================================
    // HELPERS
    // ============================================================
    private function findLicenseForOwner(string $uuid, Request $request): ?EnterpriseLicense
    {
        return EnterpriseLicense::with('members')
            ->where('uuid', $uuid)
            ->where('owner_id', $request->user()->id)
            ->first();
    }

    private function formatMember(EnterpriseLicenseMember $m): array
    {
        return [
            'id'       => (string) $m->id,
            'email'    => $m->email,
            'name'     => $m->name,
            'status'   => $m->status,
            'invitedAt'=> $m->invited_at?->toIso8601String(),
            'joinedAt' => $m->joined_at?->toIso8601String(),
        ];
    }
}