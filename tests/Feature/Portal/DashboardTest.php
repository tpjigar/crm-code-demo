<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Inertia\Testing\AssertableInertia;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

function portalClient(): User
{
    $user = User::factory()->create([
        'client_id' => 1,
        'email_verified_at' => now(),
        'password_changed_at' => now(),
    ]);
    $user->assignRole(UserRole::Client->value);

    return $user;
}

it('renders the portal dashboard inertia page for clients', function (): void {
    $this->actingAs(portalClient())
        ->get(route('portal.dashboard'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('portal/dashboard')
            ->has('stats', fn (AssertableInertia $stats) => $stats
                ->where('contacts', 0)
                ->where('open_incidents', 0)
                ->where('resolved_incidents', 0),
            ),
        );
});

it('redirects guests from the portal dashboard to login', function (): void {
    $this->get(route('portal.dashboard'))
        ->assertRedirect(route('login'));
});

it('blocks super-admin users from the portal dashboard with 403', function (): void {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
        'password_changed_at' => now(),
    ]);
    $admin->assignRole(UserRole::SuperAdmin->value);

    $this->actingAs($admin)
        ->get(route('portal.dashboard'))
        ->assertForbidden();
});
