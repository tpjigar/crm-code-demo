<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Http\Responses\LoginResponse;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

it('sends super admins to the admin dashboard', function (): void {
    $admin = User::factory()->create(['password_changed_at' => now()]);
    $admin->assignRole(UserRole::SuperAdmin->value);

    $this->actingAs($admin);

    $response = (new LoginResponse)->toResponse(request());

    expect($response->headers->get('Location'))->toBe(route('admin.dashboard'));
});

it('sends clients to the portal dashboard', function (): void {
    $client = User::factory()->create(['password_changed_at' => now()]);
    $client->assignRole(UserRole::Client->value);

    $this->actingAs($client);

    $response = (new LoginResponse)->toResponse(request());

    expect($response->headers->get('Location'))->toBe(route('portal.dashboard'));
});

it('falls back to fortify home when the user has no role', function (): void {
    $user = User::factory()->create(['password_changed_at' => now()]);

    $this->actingAs($user);

    $response = (new LoginResponse)->toResponse(request());

    expect($response->headers->get('Location'))->toContain(config('fortify.home'));
});
