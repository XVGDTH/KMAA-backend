<?php

use App\Models\Certificate;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PresentialRegistrationController;
use App\Http\Controllers\Api\CertificateController;

Route::get('/verifier-certificat/{uuid}', function (string $uuid) {
    $certificate = Certificate::where('uuid', $uuid)
        ->with('enrollment.course', 'enrollment.user')
        ->first();

    return view('certificates.verify', compact('certificate'));
})->name('certificates.verify.page');




// ============================================================
// INSCRIPTION FORMATION PRÉSENTIELLE (publique)
// ============================================================
Route::prefix('p')->name('presential.')->group(function () {
    Route::get   ('/{token}',         [PresentialRegistrationController::class, 'show'])     ->name('show');
    Route::post  ('/{token}',         [PresentialRegistrationController::class, 'register']) ->name('register');
    Route::get   ('/{token}/success', [PresentialRegistrationController::class, 'success'])  ->name('success');
});



// ============================================================
// TÉLÉCHARGEMENT EN MASSE (ZIP) — admin/formateur
// ============================================================
Route::get('/admin/certificates/download-zip', [CertificateController::class, 'downloadZip'])
    ->middleware(['auth'])
    ->name('certificates.download-zip');