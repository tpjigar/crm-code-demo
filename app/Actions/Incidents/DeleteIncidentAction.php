<?php

declare(strict_types=1);

namespace App\Actions\Incidents;

use App\Models\Incident;

class DeleteIncidentAction
{
    public function execute(Incident $incident): void
    {
        $incident->delete();
    }
}
