<?php

namespace App\Console\Commands;

use App\Mail\EventFeedbackRequestMail;
use App\Mail\EventReminderMail;
use App\Models\Registration;
use Illuminate\Console\Command;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

class SendEventReminderEmails extends Command
{
    protected $signature = 'event-reminders:send';

    protected $description = 'Send 24h/1h event reminders and post-event feedback request emails';

    public function handle(): int
    {
        $this->send24hReminders();
        $this->send1hReminders();
        $this->sendFeedbackRequests();

        return self::SUCCESS;
    }

    private function send24hReminders(): void
    {
        $from = now()->addHours(23);
        $to = now()->addHours(25);

        $query = Registration::query()
            ->eligibleForReminderEmails()
            ->whereNull('reminder_24h_sent_at')
            ->whereHas('event', fn ($q) => $q->whereBetween('start_date', [$from, $to]));

        $count = 0;
        $query->with('event')->chunkById(50, function ($registrations) use (&$count) {
            foreach ($registrations as $registration) {
                $this->sendMailable(
                    new EventReminderMail($registration, '24h'),
                    $registration->email
                );
                $registration->update(['reminder_24h_sent_at' => now()]);
                $count++;
            }
        });

        if ($count > 0) {
            $this->info("Sent {$count} reminder email(s) (24h before).");
        }
    }

    private function send1hReminders(): void
    {
        $from = now()->addMinutes(50);
        $to = now()->addMinutes(70);

        $query = Registration::query()
            ->eligibleForReminderEmails()
            ->whereNull('reminder_1h_sent_at')
            ->whereHas('event', fn ($q) => $q->whereBetween('start_date', [$from, $to]));

        $count = 0;
        $query->with('event')->chunkById(50, function ($registrations) use (&$count) {
            foreach ($registrations as $registration) {
                $this->sendMailable(
                    new EventReminderMail($registration, '1h'),
                    $registration->email
                );
                $registration->update(['reminder_1h_sent_at' => now()]);
                $count++;
            }
        });

        if ($count > 0) {
            $this->info("Sent {$count} reminder email(s) (1h before).");
        }
    }

    private function sendFeedbackRequests(): void
    {
        $from = now()->subHours(48);
        $to = now()->subHours(24);

        $query = Registration::query()
            ->eligibleForReminderEmails()
            ->whereNull('feedback_reminder_sent_at')
            ->whereHas('event', fn ($q) => $q->where('status', 'published')
                ->whereBetween('end_date', [$from, $to]));

        $count = 0;
        $query->with('event')->chunkById(50, function ($registrations) use (&$count) {
            foreach ($registrations as $registration) {
                $this->sendMailable(
                    new EventFeedbackRequestMail($registration),
                    $registration->email
                );
                $registration->update(['feedback_reminder_sent_at' => now()]);
                $count++;
            }
        });

        if ($count > 0) {
            $this->info("Sent {$count} feedback request email(s).");
        }
    }

    private function sendMailable(Mailable $mailable, string $email): void
    {
        Mail::to($email)->send($mailable);
    }
}
