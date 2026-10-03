<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use App\Models\Client;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Incident>
 */
class IncidentFactory extends Factory
{
    protected $model = Incident::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'reported_by_id' => null,
            'assigned_to_id' => null,
            'reference' => 'INC-'.strtoupper(Str::random(8)),
            'title' => fake()->sentence(6),
            'description' => fake()->paragraph(),
            'severity' => fake()->randomElement(IncidentSeverity::values()),
            'status' => IncidentStatus::New->value,
        ];
    }

    public function forClient(Client $client): self
    {
        return $this->state(fn (): array => ['client_id' => $client->id]);
    }

    public function reportedBy(User $user): self
    {
        return $this->state(fn (): array => ['reported_by_id' => $user->id]);
    }

    public function assignedTo(User $user): self
    {
        return $this->state(fn (): array => ['assigned_to_id' => $user->id]);
    }

    public function withStatus(IncidentStatus $status): self
    {
        return $this->state(fn (): array => ['status' => $status->value]);
    }

    public function withSeverity(IncidentSeverity $severity): self
    {
        return $this->state(fn (): array => ['severity' => $severity->value]);
    }
}
