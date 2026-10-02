<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordNotExpired
{
    /**
     * Password lifetime in days before a reset is required.
     * NIST SP 800-63B no longer mandates periodic rotation, but many
     * compliance regimes (PCI DSS, SOC 2) still require 90-day expiry.
     */
    private const MAX_AGE_DAYS = 90;

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return $next($request);
        }

        // Routes used to resolve an expired password must stay open,
        // otherwise the user cannot recover.
        if ($this->shouldBypass($request)) {
            return $next($request);
        }

        $changedAt = $user->password_changed_at;

        // Users predating this policy have no changed_at value.
        // Force them to rotate at next login to establish the baseline.
        if ($changedAt === null || $changedAt->diffInDays(now()) >= self::MAX_AGE_DAYS) {
            return redirect()
                ->route('security.edit')
                ->with('warning', 'Your password has expired. Please update it to continue.');
        }

        return $next($request);
    }

    private function shouldBypass(Request $request): bool
    {
        return $request->routeIs(
            'security.edit',
            'user-password.update',
            'logout',
            'password.*',
        );
    }
}
