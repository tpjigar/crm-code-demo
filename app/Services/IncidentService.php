<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Incidents\AssignIncidentAction;
use App\Actions\Incidents\CreateIncidentAction;
use App\Actions\Incidents\DeleteIncidentAction;
use App\Actions\Incidents\UpdateIncidentAction;
use App\Contracts\Services\IncidentServiceInterface;
use App\DTOs\IncidentData;
use App\Enums\IncidentStatus;
use App\Models\Incident;
use App\Models\User;
use App\Services\Incidents\IncidentWorkflowService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class IncidentService implements IncidentServiceInterface
{
    public function __construct(
        private readonly CreateIncidentAction $createAction,
        private readonly UpdateIncidentAction $updateAction,
        private readonly AssignIncidentAction $assignAction,
        private readonly DeleteIncidentAction $deleteAction,
        private readonly IncidentWorkflowService $workflow,
    ) {}

    /**
     * @return LengthAwarePaginator<int, Incident>
     */
    public function paginate(?string $search = null, ?string $status = null, ?int $clientId = null, int $perPage = 15): LengthAwarePaginator
    {
        return Incident::query()
            ->with(['client:id,name', 'assignee:id,name', 'reporter:id,name'])
            ->when($clientId !== null, fn ($q) => $q->where('client_id', $clientId))
            ->when($status !== null && $status !== '', fn ($q) => $q->where('status', $status))
            ->when($search !== null && $search !== '', function ($query) use ($search): void {
                $term = '%'.$search.'%';
                $query->where(function ($q) use ($term): void {
                    $q->where('title', 'like', $term)
                        ->orWhere('reference', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(IncidentData $data): Incident
    {
        return $this->createAction->execute($data);
    }

    public function update(Incident $incident, IncidentData $data): Incident
    {
        return $this->updateAction->execute($incident, $data);
    }

    public function assign(Incident $incident, ?User $assignee): Incident
    {
        return $this->assignAction->execute($incident, $assignee);
    }

    public function transition(Incident $incident, IncidentStatus $to): Incident
    {
        return $this->workflow->transition($incident, $to);
    }

    public function delete(Incident $incident): void
    {
        $this->deleteAction->execute($incident);
    }
}
