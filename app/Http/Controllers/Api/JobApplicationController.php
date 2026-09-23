<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobOffer;
use Illuminate\Http\Request;

class JobApplicationController extends Controller
{
    // POST /api/job-offers/{jobOffer}/apply — PUBLIC (formulaire du site)
    public function apply(Request $request, JobOffer $jobOffer)
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255'],
            'phone'     => ['nullable', 'string', 'max:50'],
            'message'   => ['nullable', 'string', 'max:5000'],
            'cv'        => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'], // 10 Mo
        ]);

        // ✅ Chemin RELATIF (règle établie — jamais d'URL absolue en DB)
         $cvPath = $request->file('cv')->store('candidatures', 'public');

        $application = JobApplication::create([
            'job_offer_id' => $jobOffer->id,
            'full_name'    => $data['full_name'],
            'email'        => $data['email'],
            'phone'        => $data['phone'] ?? null,
            'message'      => $data['message'] ?? null,
            'cv_path'      => $cvPath,
            'status'       => 'nouvelle',
        ]);

        return response()->json([
            'message' => 'Votre candidature a bien été envoyée. Nous reviendrons vers vous rapidement.',
            'data'    => ['id' => $application->id],
        ], 201);
    }

        // POST /api/job-applications/spontaneous — PUBLIC (CV + email uniquement)
    public function spontaneous(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'cv'    => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);

        $application = JobApplication::create([
            'type'         => 'spontanee',
            'job_offer_id' => null,
            'full_name'    => null,
            'email'        => $data['email'],
            'cv_path'      => $request->file('cv')->store('candidatures', 'public'), // ✅ chemin relatif
            'status'       => 'nouvelle',
        ]);

        return response()->json([
            'message' => 'Votre candidature spontanée a bien été reçue. Nous vous contacterons dès qu\'une opportunité correspond à votre profil.',
            'data'    => ['id' => $application->id],
        ], 201);
    }
}