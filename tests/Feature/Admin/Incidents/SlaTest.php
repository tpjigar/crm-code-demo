<?php

declare(strict_types=1);

use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use App\Models\Incident;

it('reports sla_breached=false when a high-severity incident is within the 4h window', function (): void {
    $incident = Incident::factory()
        ->withSeverity(IncidentSeverity::High)
        ->withStatus(IncidentStatus::New)
        ->create(['created_at' => now()->subHours(1)]);

    expect($incident->slaBreached())->toBeFalse();
});

it('reports sla_breached=true when a critical incident has been un-acknowledged for 2h (SLA is 1h)', function (): void {
    $incident = Incident::factory()
        ->withSeverity(IncidentSeverity::Critical)
        ->withStatus(IncidentStatus::New)
        ->create(['created_at' => now()->subHours(2)]);

    expect($incident->slaBreached())->toBeTrue();
});

it('reports sla_breached=false once acknowledged, regardless of elapsed time', function (): void {
    $incident = Incident::factory()
        ->withSeverity(IncidentSeverity::Critical)
        ->withStatus(IncidentStatus::Investigating)
        ->create([
            'created_at' => now()->subDays(5),
            'acknowledged_at' => now()->subDays(5)->addMinutes(10),
        ]);

    expect($incident->slaBreached())->toBeFalse();
});
