<?php

declare(strict_types=1);

namespace App\Services\Security;

use App\Models\Session;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

/**
 * Thin service wrapping the Laravel sessions table so admins can
 * inspect and revoke active logins across the fleet.
 *
 * Revocation is a straight DELETE on the session row — the next request
 * carrying that cookie fails the session lookup and the user is logged
 * out. We never touch the request's own session, so an admin cannot
 * accidentally sign themselves out while cleaning up others.
 */
class SessionManager
{
    public function __construct(
        private readonly UserAgentParser $uaParser,
    ) {}

    /**
     * @return LengthAwarePaginator<int, Session>
     */
    public function paginate(?int $userId = null, int $perPage = 25): LengthAwarePaginator
    {
        return Session::query()
            ->with('user:id,name,email')
            ->when($userId !== null, fn ($q) => $q->where('user_id', $userId))
            ->orderByDesc('last_activity')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function revoke(string $sessionId, ?string $currentSessionId = null): bool
    {
        if ($currentSessionId !== null && $sessionId === $currentSessionId) {
            return false;
        }

        return Session::where('id', $sessionId)->delete() > 0;
    }

    public function revokeAllForUser(User $user, ?string $exceptSessionId = null): int
    {
        return Session::where('user_id', $user->id)
            ->when($exceptSessionId !== null, fn ($q) => $q->where('id', '!=', $exceptSessionId))
            ->delete();
    }

    /**
     * @return array<string, mixed>
     */
    public function describe(Session $session, ?string $currentSessionId = null): array
    {
        $ua = $this->uaParser->parse($session->user_agent);
        $lastActivity = Carbon::createFromTimestamp($session->last_activity);

        return [
            'id' => $session->id,
            'user' => $session->user ? [
                'id' => $session->user->id,
                'name' => $session->user->name,
                'email' => $session->user->email,
            ] : null,
            'ip_address' => $session->ip_address,
            'browser' => $ua['browser'],
            'os' => $ua['os'],
            'device' => $ua['device'],
            'is_current' => $currentSessionId !== null && $session->id === $currentSessionId,
            'last_activity_at' => $lastActivity->toIso8601String(),
            'last_activity_human' => $lastActivity->diffForHumans(),
        ];
    }
}
