<?php

declare(strict_types=1);

namespace App\Actions\Incidents;

use App\DTOs\IncidentData;
use App\Models\Incident;

class UpdateIncidentAction
{
    public function execute(Incident $incident, IncidentData $data): Incident
    {
        $incident->fill([
            'title' => $data->title,
            'description' => $data->description,
            'severity' => $data->severity->value,
            'assigned_to_id' => $data->assignedToId,
        ])->save();

        return $incident->refresh();
    }
}
