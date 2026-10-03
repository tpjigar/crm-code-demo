<?php

declare(strict_types=1);

namespace App\Actions\Incidents;

use App\Models\Incident;
use App\Models\User;

class AssignIncidentAction
{
    public function execute(Incident $incident, ?User $assignee): Incident
    {
        $incident->assigned_to_id = $assignee?->id;
        $incident->save();

        return $incident->refresh();
    }
}
