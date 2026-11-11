<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\Api\VoucherController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\CertificateController;
use App\Http\Controllers\Api\FormateurContentController;
use App\Http\Controllers\Api\FormateurCourseController;
use App\Http\Controllers\Api\ProgressController;
use App\Http\Controllers\Api\LiveSessionController;
use App\Http\Controllers\Api\BlogPostController; 
use App\Http\Controllers\Api\NewsArticleController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\JobOfferController;
use App\Http\Controllers\Api\JobApplicationController;
use App\Http\Controllers\Api\MasterclassController;
use App\Http\Controllers\Api\NewsletterController;
use App\Jobs\SendNewsletterCampaign;
use App\Models\NewsletterCampaign;
use Illuminate\Support\Facades\Schedule;
use App\Http\Controllers\Api\PricingPlanController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\AssignmentSubmissionController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ModuleController;
use App\Http\Controllers\Api\PresentialTrainingApiController;   
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PromoController;
use App\Http\Controllers\Api\EnterpriseLicenseController;
use App\Http\Controllers\Api\EnterpriseLicenseMemberController;

// ============================================================
// AUTHENTIFICATION
// ============================================================

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);


// ============================================================
// ROUTES PROTÉGÉES
// ============================================================

Route::middleware('auth:sanctum')->group(function () {

    // --------------------------------------------------------
    // Authentification
    // --------------------------------------------------------

    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);


    // --------------------------------------------------------
    // FORMATEUR — SES COURS
    // --------------------------------------------------------

    Route::get('/formateur/courses', [CourseController::class, 'myCourses']);

    Route::post('/formateur/courses',          [FormateurCourseController::class, 'store']);
    Route::patch('/formateur/courses/{course}',  [FormateurCourseController::class, 'update']);
    Route::delete('/formateur/courses/{course}', [FormateurCourseController::class, 'destroy']);

    Route::get('/formateur/dashboard', [\App\Http\Controllers\Api\InstructorDashboardController::class, 'index']);


    // --------------------------------------------------------
    // FORMATEUR — CONTENU (chapitres / séquences)
    // --------------------------------------------------------

    Route::get('/formateur/courses/{course}/content', [FormateurContentController::class, 'content']);
    Route::post('/formateur/courses/{course}/chapters', [FormateurContentController::class, 'storeChapter']);
    Route::patch('/formateur/chapters/{chapter}',  [FormateurContentController::class, 'updateChapter']);
    Route::delete('/formateur/chapters/{chapter}', [FormateurContentController::class, 'destroyChapter']);
    Route::post('/formateur/chapters/{chapter}/sequences', [FormateurContentController::class, 'storeSequence']);
    Route::patch('/formateur/sequences/{sequence}',   [FormateurContentController::class, 'updateSequence']);
    Route::delete('/formateur/sequences/{sequence}',  [FormateurContentController::class, 'destroySequence']);


    // --------------------------------------------------------
    // VOUCHERS
    // --------------------------------------------------------

    Route::post('/vouchers/redeem', [VoucherController::class, 'redeem']);


    // --------------------------------------------------------
    // FORUM
    // --------------------------------------------------------

    Route::get('/courses/{courseId}/forum', [ForumController::class, 'index']);
    Route::post('/forum/threads', [ForumController::class, 'storeThread']);
    Route::post('/forum/threads/{thread}/replies', [ForumController::class, 'storeReply']);
    Route::post('/forum/threads/{thread}/upvote', [ForumController::class, 'upvoteThread']);
    Route::post('/forum/replies/{reply}/upvote', [ForumController::class, 'upvoteReply']);
    Route::patch('/forum/threads/{thread}/resolved', [ForumController::class, 'toggleResolved']);

    // Discussions des cours du formateur connecté
    Route::get('/formateur/forum', [ForumController::class, 'instructorForum']);   // ✅ CORRIGÉ


    // --------------------------------------------------------
    // ENROLLMENTS
    // --------------------------------------------------------

    Route::post('/enrollments', [EnrollmentController::class, 'store']);
    Route::get('/me/enrollments', [EnrollmentController::class, 'myEnrollments']);


    // --------------------------------------------------------
    // CERTIFICATS (génération, protégée)
    // --------------------------------------------------------

    Route::post('/courses/{slug}/certificate', [CertificateController::class, 'generate']);


    //route pour la progression  du certificat

    Route::post('/sequences/{sequenceId}/complete', [ProgressController::class, 'complete']);

    // route pour la progression
    // Route::get('/courses/{slug}/enrollment-progress', [ProgressController::class, 'show']);
    Route::get('/courses/{slugOrId}/enrollment-progress', [ProgressController::class, 'show']);

    // route pour les apis de live en direct avec jitsi meet
    // DANS le groupe auth:sanctum :
Route::post('/formateur/courses/{courseId}/live/start', [LiveSessionController::class, 'start']);
Route::post('/formateur/courses/{courseId}/live/stop',  [LiveSessionController::class, 'stop']);

// route ajouter apres l'ajout de zoom

Route::get('/formateur/courses/{courseId}/live/host-url', [LiveSessionController::class, 'hostUrl']);


// DANS le groupe auth:sanctum (paiement = utilisateur connecté) :
Route::post('/payments', [PaymentController::class, 'store']);

// import du live session avec la possibilité de suivre les cours selon les prix.
Route::get('/live-sessions/{courseId}/join', [LiveSessionController::class, 'join']);




// 🔒 Niveau d'accès + capacités (résout le cours par slug OU id)
Route::get('/courses/{slugOrId}/access', [EnrollmentController::class, 'access']);

//routes de get pour les notes des devoirs, quiz et tp 

Route::get('/courses/{slugOrId}/my-evaluations', [EnrollmentController::class, 'myEvaluations']);


// routes niveau pour le plan de chaque user
Route::get('/courses/{slugOrId}/my-plan', [EnrollmentController::class, 'myPlan']);



// 🔒 Soumissions de devoirs (gated level 2 dans le contrôleur)
Route::post('/sequences/{sequence}/submission', [AssignmentSubmissionController::class, 'store']);
Route::get('/sequences/{sequence}/submission', [AssignmentSubmissionController::class, 'show']);
Route::get('/courses/{course}/my-submissions', [AssignmentSubmissionController::class, 'indexForCourse']);

// Formateur — soumissions de devoirs de ses cours
Route::get('/formateur/submissions', [AssignmentSubmissionController::class, 'indexForInstructor']);
Route::patch('/formateur/submissions/{submission}', [AssignmentSubmissionController::class, 'grade']);


// route pour la reprise automatique des cours apres la connecion
Route::get('/my-dashboard', [DashboardController::class, 'index']);

Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);

// 🔔 Notifications (cloche + panneau)
Route::get('/notifications', [NotificationController::class, 'index']);
Route::post('/notifications/read', [NotificationController::class, 'markRead']);
Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);



///routes pour la creation des cours par semaines 


    // --------------------------------------------------------
    // FORMATEUR — MODULES
    // --------------------------------------------------------

    Route::post  ('/formateur/courses/{course}/modules', [ModuleController::class, 'store']);
    Route::patch ('/formateur/modules/{module}',         [ModuleController::class, 'update']);
    Route::delete('/formateur/modules/{module}',         [ModuleController::class, 'destroy']);

    // Quiz de fin de module (optionnel — le formateur décide)
    Route::post  ('/formateur/modules/{module}/quiz',    [FormateurContentController::class, 'storeModuleQuiz']);

    // Examen final du cours (optionnel — le formateur décide)
    Route::post  ('/formateur/courses/{course}/final-exam', [FormateurContentController::class, 'storeFinalExam']);




    // --------------------------------------------------------
    // FORMATIONS PRÉSENTIELLES (formateur + admin)
    // --------------------------------------------------------

    Route::get   ('/formateur/presential-trainings',              [PresentialTrainingApiController::class, 'index']);
    Route::post  ('/formateur/presential-trainings',              [PresentialTrainingApiController::class, 'store']);
    Route::get   ('/formateur/presential-trainings/{training}',   [PresentialTrainingApiController::class, 'show']);
    Route::delete('/formateur/presential-trainings/{training}',   [PresentialTrainingApiController::class, 'destroy']);





// """"""""""""""""""""""""""" les routes des cours d'entreprises

Route::get('/me/enterprise-licenses', [EnterpriseLicenseController::class, 'myLicenses']);
Route::post('/enterprise-licenses', [EnterpriseLicenseController::class, 'store']);
Route::get('/enterprise-licenses/access/check', [EnterpriseLicenseController::class, 'access']);
Route::get('/enterprise-licenses/{uuid}', [EnterpriseLicenseController::class, 'show']);


//""""""""""""""""""""" routes de verifications des licences des inscrits
Route::get   ('/enterprise-licenses/{uuid}/members',                  [EnterpriseLicenseMemberController::class, 'index']);
Route::post  ('/enterprise-licenses/{uuid}/members',                  [EnterpriseLicenseMemberController::class, 'store']);
Route::delete('/enterprise-licenses/{uuid}/members/{memberId}',       [EnterpriseLicenseMemberController::class, 'destroy']);
Route::post  ('/enterprise-licenses/{uuid}/members/{memberId}/resend',[EnterpriseLicenseMemberController::class, 'resend']);


// route de paiement pour les cours individuels
Route::get('/orders/{uuid}', [\App\Http\Controllers\Api\OrderController::class, 'show']);
Route::get('/me/orders', [\App\Http\Controllers\Api\OrderController::class, 'myOrders']);

// routes pour le feedback des users
Route::post('/sequences/{sequence}/feedback', [ProgressController::class, 'feedback']);


// import de l'avatar du formateur 
 Route::post('/auth/avatar', [AuthController::class, 'updateAvatar']);


 // route pour la programmation des meets cotés formateurs 
Route::post('/formateur/courses/{courseId}/live/schedule', [LiveSessionController::class, 'scheduleMany']);


// Formateur : voir ses programmations pour un cours
Route::get('/formateur/courses/{courseId}/live/schedules', [LiveSessionController::class, 'getSchedules']);

// Apprenant : voir les programmations de son cours
Route::get('/live-sessions/{courseId}/schedules', [LiveSessionController::class, 'getSchedules']);

// routes pour les seances programmées
    // À ajouter à côté des autres routes de LiveSession
    Route::get('/formateur/live/upcoming-schedules', [LiveSessionController::class, 'upcomingSchedules']);

    // nouvelles routes pour les seances programmées via l'enregistrement des seances meets

    // Route::post('/webhooks/zoom', [\App\Http\Controllers\Api\ZoomWebhookController::class, 'handle']);



    Route::patch('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::patch('/auth/password', [AuthController::class, 'updatePassword']);
















});   // fermeture unique du groupe middleware


// ============================================================
// COURS — LECTURE PUBLIQUE
// ============================================================

Route::get('/courses', [CourseController::class, 'index']);
Route::get('/courses/{slug}', [CourseController::class, 'show']);

// 
// 
// live en en direcy coté appprenant pour mieux voir cela.
Route::get('/live-sessions/{courseId}', [LiveSessionController::class, 'status']);


// ============================================================
// CERTIFICATS — PUBLIC
// ============================================================

Route::get('/certificates/{uuid}/download', [CertificateController::class, 'download'])->name('certificates.download');
Route::get('/certificates/{uuid}/verify', [CertificateController::class, 'verify'])->name('certificates.verify');

// PUBLIC — Blog KMAA
Route::get('/blog-posts', [BlogPostController::class, 'index']);

// PUBLIC — Actualités KMAA
Route::get('/news-articles', [NewsArticleController::class, 'index']);
Route::get('/news-articles/{slug}', [NewsArticleController::class, 'show']);


// PUBLIC — Équipe KMAA
Route::get('/team', [TeamController::class, 'index']);

// PUBLIC — Offres d'emploi KMAA
Route::get('/job-offers', [JobOfferController::class, 'index']);
Route::get('/job-offers/{id}', [JobOfferController::class, 'show']);

// PUBLIC — Candidatures aux offres d'emploi
Route::post('/job-offers/{jobOffer}/apply', [JobApplicationController::class, 'apply']);

// PUBLIC — Candidature spontanée
Route::post('/job-applications/spontaneous', [JobApplicationController::class, 'spontaneous']);

// PUBLIC — Masterclass KMAA
Route::get('/masterclass', [MasterclassController::class, 'show']);

// PUBLIC — Newsletter KMAA
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe']);
Route::get('/newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe']);


//routes des newsletters
// a configurer plus tard via mailtrap.io :https://mailtrap.io/
// Chaque jour à 9h : si une campagne "draft" est marquée "prête", elle part
Schedule::call(function () {
    $campaign = NewsletterCampaign::where('status', 'draft')->oldest()->first();
    if ($campaign) {
        SendNewsletterCampaign::dispatch($campaign);
    }
})->monthlyOn(1, '09:00')->name('newsletter-auto');


// PUBLIC — Formules de prix
Route::get('/pricing-plans', [PricingPlanController::class, 'index']);


// Formules entreprise (public)
Route::get('/enterprise/plans', [EnterpriseLicenseController::class, 'plans']);

// Codes promo — public (utilisé dans le devis)
Route::post('/promo/validate', [\App\Http\Controllers\Api\PromoController::class, 'validate']);

//"""""""""""""""""""""""""""""""""""""""""""
// route pour l'enregistrement et le stockage des seances meets
Route::post('/webhooks/zoom', [\App\Http\Controllers\Api\ZoomWebhookController::class, 'handle']);





