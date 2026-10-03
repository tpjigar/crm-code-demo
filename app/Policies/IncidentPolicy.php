<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Incident;
use App\Models\User;

/**
 * Super admins: full CRUD + assign + transition.
 *
 * Client-role users: viewAny + view (own tenant) + create (within own
 * tenant). They cannot update, delete, assign or transition — those
 * are the SOC's job. Same-tenant check duplicated on view() to defend
 * against route-ID guessing that bypasses the global scope.
 */
class IncidentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isClient();
    }

    public function view(User $user, Incident $incident): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isClient()
            && $user->client_id !== null
            && $incident->client_id === $user->client_id;
    }

    public function create(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return $user->can('incidents.create');
        }

        return $user->isClient() && $user->can('incidents.create');
    }

    public function update(User $user, Incident $incident): bool
    {
        return $user->isSuperAdmin() && $user->can('incidents.update');
    }

    public function delete(User $user, Incident $incident): bool
    {
        return $user->isSuperAdmin() && $user->can('incidents.delete');
    }

    public function assign(User $user, Incident $incident): bool
    {
        return $user->isSuperAdmin() && $user->can('incidents.assign');
    }

    public function transition(User $user, Incident $incident): bool
    {
        return $user->isSuperAdmin() && $user->can('incidents.update');
    }
}
