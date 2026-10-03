<?php

declare(strict_types=1);

use App\Enums\IncidentSeverity;
use App\Enums\UserRole;
use App\Models\Client;
use App\Models\Incident;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Inertia\Testing\AssertableInertia;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

function portalIncidentClientFor(Client $client): User
{
    $u = User::factory()->create([
        'client_id' => $client->id,
        'password_changed_at' => now(),
    ]);
    $u->assignRole(UserRole::Client->value);

    return $u;
}

it('lists only the viewers own-tenant incidents', function (): void {
    $own = Client::factory()->create();
    $other = Client::factory()->create();

    Incident::factory()->forClient($own)->count(2)->create();
    Incident::factory()->forClient($other)->count(5)->create();

    $this->actingAs(portalIncidentClientFor($own))
        ->get('/portal/incidents')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('incidents.data', 2),
        );
});

it('creates a portal-submitted incident scoped to the viewers own tenant even if the payload is forged', function (): void {
    $own = Client::factory()->create();
    $other = Client::factory()->create();
    $viewer = portalIncidentClientFor($own);

    $this->actingAs($viewer)
        ->post('/portal/incidents', [
            // forged client_id attempting to attribute the incident to another tenant
            'client_id' => $other->id,
            'title' => 'Portal-submitted',
            'description' => 'Reported from the portal.',
            'severity' => IncidentSeverity::Medium->value,
        ])
        ->assertRedirect();

    $incident = Incident::withoutGlobalScopes()->firstWhere('title', 'Portal-submitted');
    expect($incident)->not->toBeNull()
        ->and($incident->client_id)->toBe($own->id)
        ->and($incident->reported_by_id)->toBe($viewer->id);
});

it('blocks direct access to another tenants incident via a guessed route id', function (): void {
    $own = Client::factory()->create();
    $other = Client::factory()->create();
    $leakTarget = Incident::factory()->forClient($other)->create();

    $response = $this->actingAs(portalIncidentClientFor($own))
        ->get("/portal/incidents/{$leakTarget->id}");

    expect([404, 403])->toContain($response->status());
});

it('denies the admin incidents module to client-role users entirely', function (): void {
    $incident = Incident::factory()->create();

    $client = User::factory()->create(['password_changed_at' => now()]);
    $client->assignRole(UserRole::Client->value);

    $show = $this->actingAs($client)->get("/admin/incidents/{$incident->id}");
    expect([403, 404])->toContain($show->status());

    $transition = $this->actingAs($client)
        ->post("/admin/incidents/{$incident->id}/transition", ['status' => 'investigating']);
    expect([403, 404])->toContain($transition->status());
});
