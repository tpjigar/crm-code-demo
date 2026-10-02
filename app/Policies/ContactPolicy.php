<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Contact;
use App\Models\User;

/**
 * Super admins get full CRUD on every contact.
 *
 * Client-role users may only *view* contacts, and only ones belonging
 * to their own tenant. The ClientTenantScope already filters queries,
 * but we re-check client_id here for direct-id requests (route-model
 * binding bypasses the scope on findOrFail when hitting the show
 * route from an attacker-forged URL).
 */
class ContactPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isClient();
    }

    public function view(User $user, Contact $contact): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isClient()
            && $user->client_id !== null
            && $contact->client_id === $user->client_id;
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() && $user->can('contacts.create');
    }

    public function update(User $user, Contact $contact): bool
    {
        return $user->isSuperAdmin() && $user->can('contacts.update');
    }

    public function delete(User $user, Contact $contact): bool
    {
        return $user->isSuperAdmin() && $user->can('contacts.delete');
    }
}
