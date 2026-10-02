<?php

declare(strict_types=1);

namespace App\Listeners\Auth;

use App\Models\User;
use App\Notifications\Auth\SuspiciousLoginNotification;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;

class RecordLoginMetadata
{
    public function __construct(private readonly Request $request) {}

    /**
     * On every successful login:
     * - If the request IP differs from the previously stored IP,
     *   send a suspicious-login notification before overwriting.
     *   Skipped for first-ever login (no previous IP to compare).
     * - Record the current IP and timestamp as the new baseline.
     */
    public function handle(Login $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        $user = $event->user;
        $currentIp = $this->request->ip();
        $previousIp = $user->last_login_ip;

        if ($previousIp !== null && $currentIp !== null && $previousIp !== $currentIp) {
            $user->notify(new SuspiciousLoginNotification($currentIp, $previousIp));
        }

        $user->forceFill([
            'last_login_ip' => $currentIp,
            'last_login_at' => now(),
        ])->saveQuietly();
    }
}
