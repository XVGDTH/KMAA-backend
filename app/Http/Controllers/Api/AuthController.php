<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Services\LoginAttemptService;

class AuthController extends Controller
{

public function __construct(
        private readonly LoginAttemptService $loginAttempts,
    ) {}

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Tout nouvel inscrit via le formulaire public est un apprenant.
        // TODO (Salomon) : les comptes formateur seront créés uniquement depuis /admin par toi ou ton chef de projet.
        $user->assignRole('apprenant');

        $token = $user->createToken('kmaa-app')->plainTextToken;

        return response()->json([
            'user' => $this->formatUser($user),
            'token' => $token,
        ], 201);
    }

    // public function login(Request $request)
    // {
    //     $validated = $request->validate([
    //         'email' => ['required', 'email'],
    //         'password' => ['required'],
    //     ]);

    //     $user = User::where('email', $validated['email'])->first();

    //     if (! $user || ! Hash::check($validated['password'], $user->password)) {
    //         return response()->json([
    //             'message' => 'Identifiants incorrects.',
    //         ], 401);
    //     }

    //     if (! $user->is_active) {
    //         return response()->json([
    //             'message' => 'Ce compte a été désactivé. Contactez l\'administration.',
    //         ], 403);
    //     }

    //     $token = $user->createToken('kmaa-app')->plainTextToken;

    //     return response()->json([
    //         'user' => $this->formatUser($user),
    //         'token' => $token,
    //     ]);
    // }

     public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        // ----------------------------------------------------------
        // 1. Vérifier si le compte est bloqué AVANT toute chose
        // ----------------------------------------------------------
        if ($user) {
            $lockStatus = $this->loginAttempts->checkLocked($user);

            if ($lockStatus['locked']) {
                return response()->json([
                    'message' => 'Compte temporairement bloqué suite à plusieurs tentatives échouées. Réessayez plus tard.',
                    'reason' => $lockStatus['reason'],
                    'locked' => true,
                    'remaining_seconds' => $lockStatus['remaining_seconds'],
                ], 429);
            }
        }

        // ----------------------------------------------------------
        // 2. Vérifier les credentials
        // ----------------------------------------------------------
        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            // Enregistrer la tentative échouée si l'email existe
            if ($user) {
                $this->loginAttempts->recordFailedAttempt($user, $request->ip());

                $remaining = $this->loginAttempts->checkLocked($user);
                $attempts = $user->fresh()->failed_login_attempts;
                $max = LoginAttemptService::MAX_ATTEMPTS;

                return response()->json([
                    'message' => $remaining['locked']
                        ? 'Compte bloqué suite à 3 tentatives échouées.'
                        : 'Identifiants incorrects.',
                    'locked' => $remaining['locked'],
                    'remaining_seconds' => $remaining['remaining_seconds'],
                    'attempts_left' => max(0, $max - $attempts),
                ], 401);
            }

            return response()->json([
                'message' => 'Identifiants incorrects.',
                'locked' => false,
            ], 401);
        }

        // ----------------------------------------------------------
        // 3. Vérifier que le compte est actif
        // ----------------------------------------------------------
        if (! $user->is_active) {
            return response()->json([
                'message' => 'Ce compte a été désactivé. Contactez l\'administration.',
            ], 403);
        }

        // ----------------------------------------------------------
        // 4. Connexion réussie → réinitialiser les tentatives
        // ----------------------------------------------------------
        $this->loginAttempts->clearAttempts($user);

        $token = $user->createToken('kmaa-app')->plainTextToken;

        return response()->json([
            'user' => $this->formatUser($user),
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnecté avec succès.']);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $this->formatUser($request->user()),
        ]);
    }

    /**
     * Formate l'utilisateur envoyé au frontend, avec son rôle principal
     * (c'est ce champ que login.ts utilisera pour décider où rediriger).
     */
    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'role' => $user->getRoleNames()->first(), // 'admin' | 'formateur' | 'apprenant'
        ];
    }
}