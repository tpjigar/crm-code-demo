<?php

declare(strict_types=1);

namespace App\Enums;

enum ClientRiskLevel: string
{
    case Safe = 'safe';
    case LowRisk = 'low_risk';
    case AtRisk = 'at_risk';
    case Critical = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::Safe => 'Safe',
            self::LowRisk => 'Low Risk',
            self::AtRisk => 'At Risk',
            self::Critical => 'Critical',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Safe => 'emerald',
            self::LowRisk => 'blue',
            self::AtRisk => 'amber',
            self::Critical => 'red',
        };
    }

    public static function fromScore(int $score): self
    {
        return match (true) {
            $score >= 80 => self::Safe,
            $score >= 60 => self::LowRisk,
            $score >= 30 => self::AtRisk,
            default => self::Critical,
        };
    }
}
