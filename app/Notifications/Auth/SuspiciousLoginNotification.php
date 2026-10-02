<?php

declare(strict_types=1);

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SuspiciousLoginNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $newIp,
        private readonly ?string $previousIp,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('New login to your '.config('app.name').' account')
            ->greeting('Hi,')
            ->line('We noticed a login to your account from a new IP address.')
            ->line('New IP: '.$this->newIp);

        if ($this->previousIp !== null) {
            $message->line('Previous IP: '.$this->previousIp);
        }

        return $message
            ->line('If this was you, no action is needed.')
            ->line('If you do not recognize this activity, change your password immediately and review your active sessions.')
            ->action('Review account security', url('/settings/security'));
    }
}
