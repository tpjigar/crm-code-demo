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

function clientsAdmin(): User
{
    $u = User::factory()->create(['password_changed_at' => now()]);
    $u->assignRole(UserRole::SuperAdmin->value);

    return $u;
}

it('lists clients for a super admin', function (): void {
    Client::factory()->count(3)->create();

    $this->actingAs(clientsAdmin())
        ->get('/admin/clients')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/clients/index')
            ->has('clients.data', 3),
        );
});

it('creates a client via the store endpoint', function (): void {
    $owner = User::factory()->create();

    $response = $this->actingAs(clientsAdmin())
        ->post('/admin/clients', [
            'name' => 'Acme Corp',
            'industry' => 'Technology',
            'website' => 'https://acme.test',
            'contact_email' => 'ops@acme.test',
            'contact_phone' => '+1-555-0100',
            'country' => 'US',
            'notes' => 'VIP client.',
            'owner_id' => $owner->id,
        ]);

    $client = Client::firstWhere('name', 'Acme Corp');

    expect($client)->not->toBeNull()
        ->and($client->owner_id)->toBe($owner->id);

    $response->assertRedirect(route('admin.clients.show', $client));
});

it('rejects invalid payloads with 422 session errors', function (): void {
    $this->actingAs(clientsAdmin())
        ->from('/admin/clients/create')
        ->post('/admin/clients', [
            'name' => '',
            'website' => 'not-a-url',
            'contact_email' => 'not-an-email',
        ])
        ->assertSessionHasErrors(['name', 'website', 'contact_email']);
});

it('updates a client via the update endpoint', function (): void {
    $client = Client::factory()->create(['name' => 'Old Name']);

    $this->actingAs(clientsAdmin())
        ->put("/admin/clients/{$client->id}", [
            'name' => 'New Name',
            'industry' => $client->industry,
            'website' => $client->website,
            'contact_email' => $client->contact_email,
            'contact_phone' => $client->contact_phone,
            'country' => $client->country,
            'notes' => $client->notes,
            'owner_id' => $client->owner_id,
        ])
        ->assertRedirect(route('admin.clients.show', $client));

    expect($client->fresh()->name)->toBe('New Name');
});

it('soft-deletes a client via the destroy endpoint', function (): void {
    $client = Client::factory()->create();

    $this->actingAs(clientsAdmin())
        ->delete("/admin/clients/{$client->id}")
        ->assertRedirect(route('admin.clients.index'));

    expect(Client::find($client->id))->toBeNull()
        ->and(Client::withTrashed()->find($client->id))->not->toBeNull();
});

it('denies client-role users all access to the module', function (): void {
    $client = Client::factory()->create();

    $portalClient = User::factory()->create(['password_changed_at' => now()]);
    $portalClient->assignRole(UserRole::Client->value);

    $this->actingAs($portalClient)
        ->get('/admin/clients')
        ->assertForbidden();

    $this->actingAs($portalClient)
        ->get("/admin/clients/{$client->id}")
        ->assertForbidden();

    $this->actingAs($portalClient)
        ->delete("/admin/clients/{$client->id}")
        ->assertForbidden();
});

it('records an activitylog entry when a client is created', function (): void {
    $this->actingAs(clientsAdmin())
        ->post('/admin/clients', [
            'name' => 'Logged Client',
        ]);

    $logs = Activity::query()
        ->where('log_name', 'client')
        ->get();

    expect($logs)->not->toBeEmpty()
        ->and($logs->first()->description)->toBe('created');
});

it('records an activitylog entry when a client is updated', function (): void {
    $client = Client::factory()->create(['name' => 'Original']);

    $this->actingAs(clientsAdmin())
        ->put("/admin/clients/{$client->id}", array_merge(
            $client->only(['industry', 'website', 'contact_email', 'contact_phone', 'country', 'notes', 'owner_id']),
            ['name' => 'Updated'],
        ));

    $update = Activity::query()
        ->where('log_name', 'client')
        ->where('description', 'updated')
        ->latest()
        ->first();

    expect($update)->not->toBeNull()
        ->and($update->subject_id)->toBe($client->id);
});
