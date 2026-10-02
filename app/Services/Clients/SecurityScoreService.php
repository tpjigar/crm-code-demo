<?php

declare(strict_types=1);

namespace App\Services\Clients;

use App\Enums\ClientRiskLevel;
use App\Models\Client;

/**
 * Domain-specific scoring that turns a Client's current state into a
 * 0-100 security score and a derived risk level.
 *
 * Starts at 100 and subtracts penalties:
 * - no contact email (-10)   : can't be reached in an incident
 * - no contact phone (-5)    : no out-of-band contact channel
 * - no owner assigned (-15)  : nobody internal is accountable
 * - no notes recorded (-5)   : no engagement history
 *
 * Once Incidents land, criticals and opens will subtract significantly more.
 */
class SecurityScoreService
{
    private const BASE_SCORE = 100;

    public function compute(Client $client): int
    {
        $score = self::BASE_SCORE;

        if ($client->contact_email === null || $client->contact_email === '') {
            $score -= 10;
        }

        if ($client->contact_phone === null || $client->contact_phone === '') {
            $score -= 5;
        }

        if ($client->owner_id === null) {
            $score -= 15;
        }

        if ($client->notes === null || $client->notes === '') {
            $score -= 5;
        }

        return max(0, min(self::BASE_SCORE, $score));
    }

    public function riskLevel(Client $client): ClientRiskLevel
    {
        return ClientRiskLevel::fromScore($this->compute($client));
    }
}
