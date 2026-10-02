<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

it('redirects users with an expired password to the security settings page', function (): void {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
        'password_changed_at' => now()->subDays(91),
    ]);
    $admin->assignRole(UserRole::SuperAdmin->value);

    $this->actingAs($admin)
        ->get('/admin/dashboard')
        ->assertRedirect(route('security.edit'))
        ->assertSessionHas('warning');
});

it('redirects users whose password_changed_at is null (legacy)', function (): void {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
        'password_changed_at' => null,
    ]);
    $admin->assignRole(UserRole::SuperAdmin->value);

    $this->actingAs($admin)
        ->get('/admin/dashboard')
        ->assertRedirect(route('security.edit'));
});

it('lets users with a fresh password through', function (): void {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
        'password_changed_at' => now()->subDays(5),
    ]);
    $admin->assignRole(UserRole::SuperAdmin->value);

    $this->actingAs($admin)
        ->get('/admin/dashboard')
        ->assertOk();
});

it('does not force-redirect when the expired user visits the security page', function (): void {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
        'password_changed_at' => now()->subDays(200),
    ]);
    $admin->assignRole(UserRole::SuperAdmin->value);

    // security.edit sits behind RequirePassword middleware, so a 302 to
    // password confirmation is expected — but it must NOT be our own
    // middleware redirecting back to security.edit (which would loop).
    $response = $this->actingAs($admin)->get(route('security.edit'));

    expect($response->headers->get('Location'))
        ->not->toBe(route('security.edit'));
});
