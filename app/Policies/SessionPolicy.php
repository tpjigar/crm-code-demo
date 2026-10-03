<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Session;
use App\Models\User;

/**
 * Only super admins may inspect the session store. The sessions.view_all
 * and sessions.revoke_any permissions give fine-grained control if a
 * future role (e.g. "SOC analyst") needs read access without revoke.
 */
class SessionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() && $user->can('sessions.view_all');
    }

    public function revoke(User $user, Session $session): bool
    {
        return $user->isSuperAdmin() && $user->can('sessions.revoke_any');
    }
}
