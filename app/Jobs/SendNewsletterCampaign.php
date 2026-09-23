<?php

namespace App\Jobs;

use App\Mail\KmaaBriefMail;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendNewsletterCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public NewsletterCampaign $campaign
    ) {}

    public function handle(): void
    {
        $this->campaign->update(['status' => 'sending']);

        $subscribers = NewsletterSubscriber::where('status', 'subscribed')->get();
        $sentCount = 0;

        foreach ($subscribers as $subscriber) {
            try {
                Mail::to($subscriber->email)
                    ->send(new KmaaBriefMail($this->campaign, $subscriber));

                $sentCount++;
            } catch (\Throwable $e) {
                // Un email échoué ne bloque pas l'envoi aux autres
                \Log::warning("Newsletter non envoyée à {$subscriber->email} : {$e->getMessage()}");
            }
        }

        $this->campaign->update([
            'status'     => 'sent',
            'sent_count' => $sentCount,
            'sent_at'    => now(),
        ]);
    }
}