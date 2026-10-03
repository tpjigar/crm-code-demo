<?php

declare(strict_types=1);

namespace App\Services\Incidents;

use App\Enums\IncidentStatus;
use App\Exceptions\InvalidIncidentTransitionException;
use App\Models\Incident;
use Illuminate\Support\Facades\DB;

/**
 * Guards status transitions against the IncidentStatus state machine
 * and stamps the right lifecycle timestamp.
 *
 * Callers never set $incident->status directly — they call transition()
 * so an illegal state (e.g. closed → investigating → new) is caught at
 * the service boundary, not at the database.
 */
class IncidentWorkflowService
{
    public function transition(Incident $incident, IncidentStatus $to): Incident
    {
        $from = $incident->status;

        if (! $from->canTransitionTo($to)) {
            throw InvalidIncidentTransitionException::between($from, $to);
        }

        return DB::transaction(function () use ($incident, $to): Incident {
            $incident->status = $to;

            match ($to) {
                IncidentStatus::Investigating => $incident->acknowledged_at ??= now(),
                IncidentStatus::Resolved => $incident->resolved_at = now(),
                IncidentStatus::Closed => $incident->closed_at = now(),
                default => null,
            };

            $incident->save();

            return $incident->refresh();
        });
    }
}
