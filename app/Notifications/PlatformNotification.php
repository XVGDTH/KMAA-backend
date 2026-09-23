<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PlatformNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $kind,       // 'devoir_corrige' | 'forum_question' | 'forum_reply' | 'live_started' | 'paiement'
        public string $title,      // titre affiché
        public string $message,    // corps affiché
        public ?string $courseSlug = null,   // lien cliquable (optionnel)
        public ?string $actionUrl = null,    // URL email (optionnel)
    ) {}

    /** Le type enregistré en DB (sert au front pour l'icône). */
    public function via($notifiable): array
    {
        return ['database', 'mail'];   // ✅ DB pour la cloche + EMAIL automatique !
    }

    public function toArray($notifiable): array
    {
        return [
            'kind'       => $this->kind,
            'title'      => $this->title,
            'message'    => $this->message,
            'courseSlug' => $this->courseSlug,
            'actionUrl'  => $this->actionUrl,
        ];
    }

    /** ✅ L'email transactionnel (réutilise ton pipeline SMTP Mailtrap/Brevo) */
    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->title)
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line($this->message);

        if ($this->courseSlug) {
            $mail->action('Voir sur KMAA', url('/apprentissage/' . $this->courseSlug));
        }

        return $mail->line('— L\'équipe KMAA');
    }
}