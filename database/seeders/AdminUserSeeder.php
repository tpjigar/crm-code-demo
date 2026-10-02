<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@crm-demo.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('ChangeMe123!'),
                'email_verified_at' => now(),
                'password_changed_at' => now(),
            ],
        );

        if (! $admin->hasRole(UserRole::SuperAdmin->value)) {
            $admin->assignRole(UserRole::SuperAdmin->value);
        }
    }
}
