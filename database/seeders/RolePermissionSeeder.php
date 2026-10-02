<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $registrar = app(PermissionRegistrar::class);
        $registrar->forgetCachedPermissions();

        DB::transaction(function () use ($registrar): void {
            $all = collect(UserRole::cases())
                ->flatMap(fn (UserRole $role): array => $role->defaultPermissions())
                ->unique()
                ->values();

            foreach ($all as $permission) {
                Permission::findOrCreate($permission, 'web');
            }

            // Spatie caches permissions, so new rows aren't visible to syncPermissions()
            // without an explicit flush between inserts and role sync.
            $registrar->forgetCachedPermissions();

            foreach (UserRole::cases() as $role) {
                $model = Role::findOrCreate($role->value, 'web');
                $model->syncPermissions($role->defaultPermissions());
            }
        });
    }
}
