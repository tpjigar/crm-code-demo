<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\IncidentSeverity;

final readonly class IncidentData
{
    public function __construct(
        public int $clientId,
        public string $title,
        public string $description,
        public IncidentSeverity $severity,
        public ?int $assignedToId,
        public ?int $reportedById,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated, ?int $reportedById = null): self
    {
        return new self(
            clientId: (int) $validated['client_id'],
            title: (string) $validated['title'],
            description: (string) $validated['description'],
            severity: IncidentSeverity::from((string) $validated['severity']),
            assignedToId: isset($validated['assigned_to_id']) ? (int) $validated['assigned_to_id'] : null,
            reportedById: $reportedById,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'client_id' => $this->clientId,
            'title' => $this->title,
            'description' => $this->description,
            'severity' => $this->severity->value,
            'assigned_to_id' => $this->assignedToId,
            'reported_by_id' => $this->reportedById,
        ];
    }
}
