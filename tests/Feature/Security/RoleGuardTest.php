<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

it('allows super admins into the admin dashboard', function (): void {
    $admin = User::factory()->create(['email_verified_at' => now(), 'password_changed_at' => now()]);
    $admin->assignRole(UserRole::SuperAdmin->value);

    $this->actingAs($admin)
        ->get('/admin/dashboard')
        ->assertOk();
});

it('blocks clients from the admin dashboard', function (): void {
    $client = User::factory()->create(['email_verified_at' => now(), 'password_changed_at' => now()]);
    $client->assignRole(UserRole::Client->value);

    $this->actingAs($client)
        ->get('/admin/dashboard')
        ->assertForbidden();
});

it('allows clients into the portal dashboard', function (): void {
    $client = User::factory()->create(['email_verified_at' => now(), 'password_changed_at' => now()]);
    $client->assignRole(UserRole::Client->value);

    $this->actingAs($client)
        ->get('/portal/dashboard')
        ->assertOk();
});

it('blocks super admins from the portal dashboard', function (): void {
    $admin = User::factory()->create(['email_verified_at' => now(), 'password_changed_at' => now()]);
    $admin->assignRole(UserRole::SuperAdmin->value);

    $this->actingAs($admin)
        ->get('/portal/dashboard')
        ->assertForbidden();
});

it('redirects guests away from both panels', function (): void {
    $this->get('/admin/dashboard')->assertRedirect(route('login'));
    $this->get('/portal/dashboard')->assertRedirect(route('login'));
});
