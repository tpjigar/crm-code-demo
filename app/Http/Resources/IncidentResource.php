<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Incident
 */
class IncidentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'client_id' => $this->client_id,
            'title' => $this->title,
            'description' => $this->description,
            'severity' => [
                'value' => $this->severity->value,
                'label' => $this->severity->label(),
                'color' => $this->severity->badgeColor(),
                'ack_sla_hours' => $this->severity->ackSlaHours(),
            ],
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
                'color' => $this->status->badgeColor(),
                'allowed_next' => array_map(
                    fn ($s) => ['value' => $s->value, 'label' => $s->label()],
                    $this->status->allowedNext(),
                ),
            ],
            'sla_breached' => $this->slaBreached(),
            'acknowledged_at' => $this->acknowledged_at?->toIso8601String(),
            'resolved_at' => $this->resolved_at?->toIso8601String(),
            'closed_at' => $this->closed_at?->toIso8601String(),
            'client' => $this->whenLoaded('client', fn (): array => [
                'id' => $this->client?->id,
                'name' => $this->client?->name,
            ]),
            'assignee' => $this->whenLoaded('assignee', fn (): ?array => $this->assignee ? [
                'id' => $this->assignee->id,
                'name' => $this->assignee->name,
            ] : null),
            'reporter' => $this->whenLoaded('reporter', fn (): ?array => $this->reporter ? [
                'id' => $this->reporter->id,
                'name' => $this->reporter->name,
            ] : null),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
