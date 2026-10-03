<?php

declare(strict_types=1);

use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use App\Enums\UserRole;
use App\Models\Client;
use App\Models\Incident;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Inertia\Testing\AssertableInertia;
use Spatie\Activitylog\Models\Activity;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

function incidentsAdmin(): User
{
    $u = User::factory()->create(['password_changed_at' => now()]);
    $u->assignRole(UserRole::SuperAdmin->value);

    return $u;
}

it('lists incidents for a super admin', function (): void {
    Incident::factory()->count(3)->create();

    $this->actingAs(incidentsAdmin())
        ->get('/admin/incidents')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/incidents/index')
            ->has('incidents.data', 3),
        );
});

it('creates an incident with an auto-generated reference and new status', function (): void {
    $client = Client::factory()->create();

    $this->actingAs(incidentsAdmin())
        ->post('/admin/incidents', [
            'client_id' => $client->id,
            'title' => 'Unexpected outbound traffic',
            'description' => 'Detected egress to an unknown C2 host overnight.',
            'severity' => IncidentSeverity::High->value,
        ])
        ->assertRedirect();

    $incident = Incident::firstWhere('title', 'Unexpected outbound traffic');

    expect($incident)->not->toBeNull()
        ->and($incident->status)->toBe(IncidentStatus::New)
        ->and($incident->reference)->toStartWith('INC-')
        ->and($incident->reported_by_id)->not->toBeNull();
});

it('rejects invalid payloads with 422 session errors', function (): void {
    $this->actingAs(incidentsAdmin())
        ->from('/admin/incidents/create')
        ->post('/admin/incidents', [
            'client_id' => 999999,
            'title' => '',
            'description' => '',
            'severity' => 'bogus',
        ])
        ->assertSessionHasErrors(['client_id', 'title', 'description', 'severity']);
});

it('transitions an incident via the transition endpoint', function (): void {
    $incident = Incident::factory()->withStatus(IncidentStatus::New)->create();

    $this->actingAs(incidentsAdmin())
        ->post("/admin/incidents/{$incident->id}/transition", [
            'status' => IncidentStatus::Investigating->value,
        ])
        ->assertRedirect();

    expect($incident->fresh()->status)->toBe(IncidentStatus::Investigating);
});

it('returns a validation error when attempting an illegal transition', function (): void {
    $incident = Incident::factory()->withStatus(IncidentStatus::Closed)->create();

    $this->actingAs(incidentsAdmin())
        ->from("/admin/incidents/{$incident->id}")
        ->post("/admin/incidents/{$incident->id}/transition", [
            'status' => IncidentStatus::New->value,
        ])
        ->assertSessionHasErrors('status');

    expect($incident->fresh()->status)->toBe(IncidentStatus::Closed);
});

it('assigns an incident to a user via the assign endpoint', function (): void {
    $incident = Incident::factory()->create();
    $assignee = User::factory()->create();

    $this->actingAs(incidentsAdmin())
        ->post("/admin/incidents/{$incident->id}/assign", [
            'assigned_to_id' => $assignee->id,
        ])
        ->assertRedirect();

    expect($incident->fresh()->assigned_to_id)->toBe($assignee->id);
});

it('soft-deletes an incident via the destroy endpoint', function (): void {
    $incident = Incident::factory()->create();

    $this->actingAs(incidentsAdmin())
        ->delete("/admin/incidents/{$incident->id}")
        ->assertRedirect();

    expect(Incident::find($incident->id))->toBeNull()
        ->and(Incident::withTrashed()->find($incident->id))->not->toBeNull();
});

it('records an activitylog entry when status changes', function (): void {
    $incident = Incident::factory()->withStatus(IncidentStatus::New)->create();

    $this->actingAs(incidentsAdmin())
        ->post("/admin/incidents/{$incident->id}/transition", [
            'status' => IncidentStatus::Investigating->value,
        ]);

    $logs = Activity::query()
        ->where('log_name', 'incident')
        ->where('description', 'updated')
        ->get();

    expect($logs)->not->toBeEmpty();
});
