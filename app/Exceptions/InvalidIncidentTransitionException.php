<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\IncidentStatus;
use RuntimeException;

/**
 * Raised when a workflow caller attempts a status transition the state
 * machine forbids (e.g. closed → new). This is a programming/UX bug,
 * not a validation failure — the HTTP layer catches it and returns 422.
 */
final class InvalidIncidentTransitionException extends RuntimeException
{
    public static function between(IncidentStatus $from, IncidentStatus $to): self
    {
        return new self(sprintf(
            'Invalid incident status transition: %s → %s',
            $from->value,
            $to->value,
        ));
    }
}
