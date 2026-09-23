<?php

namespace App\Mail;

use App\Models\Certificate;
use App\Models\PresentialParticipant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PresentialCertificateMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PresentialParticipant $participant,
        public Certificate $certificate,
    ) {}

    public function envelope(): Envelope
    {
        $label = $this->certificate->type === 'attestation'
            ? 'Votre attestation KMAA'
            : 'Votre certificat KMAA';

        return new Envelope(
            subject: $label . ' — ' . $this->certificate->course_title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.presential-certificate',
            with: [
                'participant' => $this->participant,
                'certificate' => $this->certificate,
                'training'    => $this->participant->training,
                'downloadUrl' => route('certificates.download', $this->certificate->uuid),
                'verifyUrl'   => route('certificates.verify.page', $this->certificate->uuid),
            ],
        );
    }
}