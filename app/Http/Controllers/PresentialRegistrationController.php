<?php

namespace App\Http\Controllers;

// use App\Mail\PresentialCertificateMail;
use App\Models\PresentialParticipant;
use App\Models\PresentialTraining;
use App\Services\PresentialCertificateService;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Mail;

class PresentialRegistrationController extends Controller
{
    // ----------------------------------------------------------
    // GET /p/{token} — formulaire d'inscription
    // ----------------------------------------------------------
    public function show(string $token)
    {
        $training = PresentialTraining::where('registration_token', $token)
            ->where('registration_open', true)
            ->firstOrFail();

        return view('presential.register', compact('training'));
    }

    // ----------------------------------------------------------
    // POST /p/{token} — enregistre + génère + envoie
    // ----------------------------------------------------------
    public function register(Request $request, string $token)
    {
        $training = PresentialTraining::where('registration_token', $token)
            ->where('registration_open', true)
            ->firstOrFail();

        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        // Déjà inscrit ? On redirige vers la page succès
        $existing = PresentialParticipant::where('presential_training_id', $training->id)
            ->where('email', $validated['email'])
            ->first();

        if ($existing) {
            return redirect()->route('presential.success', $token);
        }

        $participant = PresentialParticipant::create([
            'presential_training_id' => $training->id,
            'name'                   => $validated['name'],
            'email'                  => $validated['email'],
            'phone'                  => $validated['phone'] ?? null,
            'registered_at'          => now(),
        ]);

        $training->increment('registered_count');

        // Génération du certificat
        // $certificate = PresentialCertificateService::generate($participant);

        // // Envoi email
        // Mail::to($participant->email)
        //     ->send(new PresentialCertificateMail($participant, $certificate));

        // $participant->update(['certificate_sent_at' => now()]);

        // return redirect()->route('presential.success', $token);
        // Génération du certificat
$certificate = PresentialCertificateService::generate($participant);

// ⏸️ ENVOI EMAIL DÉSACTIVÉ — sera réactivé quand le SMTP sera configuré
// Mail::to($participant->email)
//     ->send(new PresentialCertificateMail($participant, $certificate));
// $participant->update(['certificate_sent_at' => now()]);

return redirect()->route('presential.success', $token);
    }

    // ----------------------------------------------------------
    // GET /p/{token}/success — confirmation
    // ----------------------------------------------------------
    public function success(string $token)
    {
        $training = PresentialTraining::where('registration_token', $token)->firstOrFail();

        return view('presential.success', compact('training'));
    }
}