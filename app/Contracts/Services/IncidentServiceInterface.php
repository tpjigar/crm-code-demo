<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\DTOs\IncidentData;
use App\Enums\IncidentStatus;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface IncidentServiceInterface
{
    /**
     * @return LengthAwarePaginator<int, Incident>
     */
    public function paginate(?string $search = null, ?string $status = null, ?int $clientId = null, int $perPage = 15): LengthAwarePaginator;

    public function create(IncidentData $data): Incident;

    public function update(Incident $incident, IncidentData $data): Incident;

    public function assign(Incident $incident, ?User $assignee): Incident;

    public function transition(Incident $incident, IncidentStatus $to): Incident;

    public function delete(Incident $incident): void;
}
