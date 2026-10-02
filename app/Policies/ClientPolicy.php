<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

/**
 * Super admins can perform every action. Other users have no access
 * to the clients module — client-role users operate through the portal
 * and never read/write Client rows directly.
 */
class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, Client $client): bool
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() && $user->can('clients.create');
    }

    public function update(User $user, Client $client): bool
    {
        return $user->isSuperAdmin() && $user->can('clients.update');
    }

    public function delete(User $user, Client $client): bool
    {
        return $user->isSuperAdmin() && $user->can('clients.delete');
    }
}
