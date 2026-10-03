<?php

declare(strict_types=1);

namespace App\Enums;

enum IncidentStatus: string
{
    case New = 'new';
    case Investigating = 'investigating';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Investigating => 'Investigating',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::New => 'red',
            self::Investigating => 'amber',
            self::Resolved => 'blue',
            self::Closed => 'emerald',
        };
    }

    /**
     * Returns the set of statuses this status is allowed to transition to.
     *
     * Rules encoded here (match an ISO 27035-style flow):
     *   new            → investigating
     *   investigating  → resolved
     *   resolved       → closed | investigating   (re-open on regression)
     *   closed         →                          (terminal)
     *
     * @return list<self>
     */
    public function allowedNext(): array
    {
        return match ($this) {
            self::New => [self::Investigating],
            self::Investigating => [self::Resolved],
            self::Resolved => [self::Closed, self::Investigating],
            self::Closed => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedNext(), true);
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $c): string => $c->value, self::cases());
    }
}
