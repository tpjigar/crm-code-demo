<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope that filters queries by the authenticated user's client_id
 * whenever that user has the Client role.
 *
 * Super admins and unauthenticated contexts (seeders, console commands,
 * jobs) are not scoped — they see every row.
 *
 * Apply with:
 *     #[ScopedBy(ClientTenantScope::class)]
 *     class Contact extends Model {}
 *
 * The guarantee: a developer forgetting a where('client_id', ...) in
 * portal code cannot leak data across tenants, because the scope has
 * already added it.
 *
 * @implements Scope<Model>
 */
class ClientTenantScope implements Scope
{
    /**
     * @param  Builder<covariant Model>  $builder
     */
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return;
        }

        if (! $user->hasRole(UserRole::Client->value)) {
            return;
        }

        $builder->where(
            $model->qualifyColumn('client_id'),
            $user->client_id,
        );
    }
}
