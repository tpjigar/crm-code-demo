<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Client;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Inertia\Testing\AssertableInertia;
use Spatie\Activitylog\Models\Activity;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

function auditAdmin(): User
{
    $u = User::factory()->create(['password_changed_at' => now()]);
    $u->assignRole(UserRole::SuperAdmin->value);

    return $u;
}

it('lists audit log entries for a super admin', function (): void {
    activity('client')->event('created')->log('seeded');
    activity('contact')->event('updated')->log('seeded');
    activity('incident')->event('created')->log('seeded');

    $this->actingAs(auditAdmin())
        ->get('/admin/security/audit')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/security/audit/index')
            ->has('logs.data', 3)
            ->has('log_names'),
        );
});

it('filters audit log entries by log_name', function (): void {
    activity('client')->event('created')->log('one');
    activity('contact')->event('created')->log('two');
    activity('contact')->event('created')->log('three');

    $this->actingAs(auditAdmin())
        ->get('/admin/security/audit?log_name=contact')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('logs.data', 2),
        );
});

it('filters audit log entries by event', function (): void {
    activity('client')->event('created')->log('a');
    activity('client')->event('updated')->log('b');
    activity('client')->event('deleted')->log('c');

    $this->actingAs(auditAdmin())
        ->get('/admin/security/audit?event=deleted')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('logs.data', 1)
            ->where('logs.data.0.event', 'deleted'),
        );
});

it('captures causer when a client record is mutated by an admin', function (): void {
    $admin = auditAdmin();
    $this->actingAs($admin);

    $client = Client::create(['name' => 'Audited Co']);
    $client->update(['name' => 'Renamed Co']);

    $logs = Activity::query()->where('log_name', 'client')->get();

    expect($logs)->toHaveCount(2)
        ->and($logs->first()->causer_id)->toBe($admin->id);
});

it('denies audit log access to client-role users', function (): void {
    $client = User::factory()->create(['password_changed_at' => now()]);
    $client->assignRole(UserRole::Client->value);

    $this->actingAs($client)
        ->get('/admin/security/audit')
        ->assertForbidden();
});
