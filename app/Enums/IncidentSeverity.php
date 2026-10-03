<?php

declare(strict_types=1);

namespace App\Enums;

enum IncidentSeverity: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Critical = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Low',
            self::Medium => 'Medium',
            self::High => 'High',
            self::Critical => 'Critical',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Low => 'emerald',
            self::Medium => 'blue',
            self::High => 'amber',
            self::Critical => 'red',
        };
    }

    /**
     * SLA to acknowledge in hours (ISO/IEC 27035-aligned defaults).
     */
    public function ackSlaHours(): int
    {
        return match ($this) {
            self::Low => 72,
            self::Medium => 24,
            self::High => 4,
            self::Critical => 1,
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $c): string => $c->value, self::cases());
    }
}
