<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\LiveSchedule;
use App\Mail\LiveSessionReminderMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schedule;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::call(function () {
    // 1. On cherche les séances qui arrivent dans les 3 prochains jours
    // et qui n'ont pas encore reçu de rappel.
    $schedules = LiveSchedule::where('reminder_sent', false)
        ->where('scheduled_at', '<=', now()->addDays(3))
        ->where('scheduled_at', '>=', now())
        ->with('course.enrollments.user') // Charger les apprenants
        ->get();

    foreach ($schedules as $schedule) {
        // 2. On récupère les emails des apprenants inscrits au cours
        $students = $schedule->course->enrollments
            ->pluck('user.email')
            ->filter()
            ->unique();

        if ($students->isEmpty()) {
            continue;
        }

        // 3. On envoie l'email
        Mail::to($students)->send(new LiveSessionReminderMail($schedule));

        // 4. On marque le rappel comme envoyé
        $schedule->update(['reminder_sent' => true]);
    }
})->daily(); // tous les jours à minuit