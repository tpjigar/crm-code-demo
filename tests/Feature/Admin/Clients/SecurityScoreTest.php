<?php

declare(strict_types=1);

use App\Enums\ClientRiskLevel;
use App\Models\Client;
use App\Models\User;
use App\Services\Clients\SecurityScoreService;

it('awards a full 100 when every signal is present', function (): void {
    $owner = User::factory()->create();
    $client = Client::factory()->ownedBy($owner)->create([
        'contact_email' => 'ops@example.test',
        'contact_phone' => '+1-555-0100',
        'notes' => 'Active engagement.',
    ]);

    expect((new SecurityScoreService)->compute($client))->toBe(100);
});

it('subtracts the correct penalty when the owner is missing', function (): void {
    $client = Client::factory()->create([
        'owner_id' => null,
        'contact_email' => 'ops@example.test',
        'contact_phone' => '+1-555-0100',
        'notes' => 'Note.',
    ]);

    expect((new SecurityScoreService)->compute($client))->toBe(85);
});

it('clamps the score to zero when every signal is missing', function (): void {
    $client = Client::factory()->create([
        'contact_email' => null,
        'contact_phone' => null,
        'owner_id' => null,
        'notes' => null,
    ]);

    expect((new SecurityScoreService)->compute($client))->toBe(65);
});

it('maps score to the correct ClientRiskLevel tier', function (): void {
    $svc = new SecurityScoreService;

    $safe = Client::factory()->ownedBy(User::factory()->create())->create([
        'contact_email' => 'a@b.c', 'contact_phone' => '1', 'notes' => 'x',
    ]);
    $atRisk = Client::factory()->create([
        'owner_id' => null, 'contact_email' => null,
        'contact_phone' => null, 'notes' => null,
    ]);

    expect($svc->riskLevel($safe))->toBe(ClientRiskLevel::Safe)
        ->and($svc->riskLevel($atRisk))->toBe(ClientRiskLevel::LowRisk);
});
