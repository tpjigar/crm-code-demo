<?php

declare(strict_types=1);

namespace App\Actions\Incidents;

use App\DTOs\IncidentData;
use App\Enums\IncidentStatus;
use App\Models\Incident;
use Illuminate\Support\Str;

class CreateIncidentAction
{
    public function execute(IncidentData $data): Incident
    {
        return Incident::create(array_merge($data->toArray(), [
            'reference' => $this->generateReference(),
            'status' => IncidentStatus::New->value,
        ]));
    }

    private function generateReference(): string
    {
        do {
            $ref = 'INC-'.strtoupper(Str::random(8));
        } while (Incident::withoutGlobalScopes()->where('reference', $ref)->exists());

        return $ref;
    }
}
