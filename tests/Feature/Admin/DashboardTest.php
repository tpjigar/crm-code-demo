<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Inertia\Testing\AssertableInertia;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

function superAdmin(): User
{
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'password_changed_at' => now(),
    ]);
    $user->assignRole(UserRole::SuperAdmin->value);

    return $user;
}

it('renders the admin dashboard inertia page for super admins', function (): void {
    $this->actingAs(superAdmin())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/dashboard')
            ->has('stats', fn (AssertableInertia $stats) => $stats
                ->where('clients', 0)
                ->where('contacts', 0)
                ->where('open_incidents', 0)
                ->where('critical_incidents', 0),
            ),
        );
});

it('redirects guests from the admin dashboard to login', function (): void {
    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));
});

it('blocks client-role users from the admin dashboard with 403', function (): void {
    $client = User::factory()->create([
        'email_verified_at' => now(),
        'password_changed_at' => now(),
    ]);
    $client->assignRole(UserRole::Client->value);

    $this->actingAs($client)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

it('exposes a stats array shaped for KPI tiles', function (): void {
    $this->actingAs(superAdmin())
        ->get(route('admin.dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('stats.clients')
            ->has('stats.contacts')
            ->has('stats.open_incidents')
            ->has('stats.critical_incidents'),
        );
});
