<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    // POST /api/newsletter/subscribe — PUBLIC
    // public function subscribe(Request $request)
    // {
    //     $data = $request->validate([
    //         'email' => ['required', 'email', 'max:255'],
    //         'name'  => ['nullable', 'string', 'max:255'],
    //     ]);

    //     $subscriber = NewsletterSubscriber::withTrashed()
    //         ->where('email', $data['email'])
    //         ->first();

    //     // Déjà inscrit et actif
    //     if ($subscriber && $subscriber->status === 'subscribed') {
    //         return response()->json([
    //             'message' => 'Vous êtes déjà inscrit à la newsletter KMAA. Merci de votre fidélité !',
    //         ]);
    //     }

    //     // Déjà existant mais désinscrit → réabonnement
    //     if ($subscriber) {
    //         $subscriber->update([
    //             'status'          => 'subscribed',
    //             'name'            => $data['name'] ?? $subscriber->name,
    //             'subscribed_at'   => now(),
    //             'unsubscribed_at' => null,
    //         ]);

    //         return response()->json([
    //             'message' => 'Bon retour ! Votre inscription à la newsletter est réactivée.',
    //         ]);
    //     }

    //     // Nouvelle inscription
    //     NewsletterSubscriber::create([
    //         'email' => $data['email'],
    //         'name'  => $data['name'] ?? null,
    //     ]);

    //     return response()->json([
    //         'message' => 'Inscription confirmée ! Vous recevrez prochainement le KMAA Brief. 🎉',
    //     ], 201);
    // }

        // POST /api/newsletter/subscribe — PUBLIC
    public function subscribe(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'name'  => ['nullable', 'string', 'max:255'],
        ]);

        // ✅ Recherche simple (sans withTrashed — pas de SoftDeletes ici)
        $subscriber = NewsletterSubscriber::where('email', $data['email'])->first();

        // Déjà inscrit et actif
        if ($subscriber && $subscriber->status === 'subscribed') {
            return response()->json([
                'message' => 'Vous êtes déjà inscrit à la newsletter KMAA. Merci de votre fidélité !',
            ]);
        }

        // Déjà existant mais désinscrit → réabonnement
        if ($subscriber) {
            $subscriber->update([
                'status'          => 'subscribed',
                'name'            => $data['name'] ?? $subscriber->name,
                'subscribed_at'   => now(),
                'unsubscribed_at' => null,
            ]);

            return response()->json([
                'message' => 'Bon retour ! Votre inscription à la newsletter est réactivée.',
            ]);
        }

        // Nouvelle inscription
        NewsletterSubscriber::create([
            'email' => $data['email'],
            'name'  => $data['name'] ?? null,
        ]);

        return response()->json([
            'message' => 'Inscription confirmée ! Vous recevrez prochainement le KMAA Brief. 🎉',
        ], 201);
    }

    // GET /api/newsletter/unsubscribe/{token} — PUBLIC (lien des emails)
    public function unsubscribe(string $token)
    {
        $subscriber = NewsletterSubscriber::where('unsubscribe_token', $token)->first();

        if (! $subscriber) {
            return response()->json(['message' => 'Lien de désinscription invalide ou expiré.'], 404);
        }

        if ($subscriber->status === 'unsubscribed') {
            return response()->json(['message' => 'Vous êtes déjà désinscrit de la newsletter.']);
        }

        $subscriber->update([
            'status'          => 'unsubscribed',
            'unsubscribed_at' => now(),
        ]);

        return response()->json(['message' => 'Vous avez bien été désinscrit de la newsletter. À bientôt !']);
    }
}