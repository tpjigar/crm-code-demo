<?php

declare(strict_types=1);

use App\Enums\IncidentStatus;
use App\Exceptions\InvalidIncidentTransitionException;
use App\Models\Incident;
use App\Services\Incidents\IncidentWorkflowService;

beforeEach(function (): void {
    $this->workflow = new IncidentWorkflowService;
});

it('allows the full happy-path sequence: new → investigating → resolved → closed', function (): void {
    $incident = Incident::factory()->withStatus(IncidentStatus::New)->create();

    $this->workflow->transition($incident, IncidentStatus::Investigating);
    expect($incident->fresh()->status)->toBe(IncidentStatus::Investigating)
        ->and($incident->fresh()->acknowledged_at)->not->toBeNull();

    $this->workflow->transition($incident, IncidentStatus::Resolved);
    expect($incident->fresh()->status)->toBe(IncidentStatus::Resolved)
        ->and($incident->fresh()->resolved_at)->not->toBeNull();

    $this->workflow->transition($incident, IncidentStatus::Closed);
    expect($incident->fresh()->status)->toBe(IncidentStatus::Closed)
        ->and($incident->fresh()->closed_at)->not->toBeNull();
});

it('allows a resolved incident to be re-opened to investigating', function (): void {
    $incident = Incident::factory()->withStatus(IncidentStatus::Resolved)->create();

    $this->workflow->transition($incident, IncidentStatus::Investigating);

    expect($incident->fresh()->status)->toBe(IncidentStatus::Investigating);
});

it('rejects an invalid transition like closed → new', function (): void {
    $incident = Incident::factory()->withStatus(IncidentStatus::Closed)->create();

    expect(fn () => $this->workflow->transition($incident, IncidentStatus::New))
        ->toThrow(InvalidIncidentTransitionException::class);
});

it('rejects skipping intermediate states like new → closed', function (): void {
    $incident = Incident::factory()->withStatus(IncidentStatus::New)->create();

    expect(fn () => $this->workflow->transition($incident, IncidentStatus::Closed))
        ->toThrow(InvalidIncidentTransitionException::class);
});

it('does not overwrite acknowledged_at once set', function (): void {
    $incident = Incident::factory()->withStatus(IncidentStatus::New)->create();
    $this->workflow->transition($incident, IncidentStatus::Investigating);
    $firstAck = $incident->fresh()->acknowledged_at;

    sleep(1);
    $this->workflow->transition($incident->fresh(), IncidentStatus::Resolved);
    $this->workflow->transition($incident->fresh(), IncidentStatus::Investigating);

    expect($incident->fresh()->acknowledged_at->toIso8601String())
        ->toBe($firstAck?->toIso8601String());
});
