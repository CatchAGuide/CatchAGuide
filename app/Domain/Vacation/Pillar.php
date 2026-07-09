<?php

namespace App\Domain\Vacation;

enum Pillar: string
{
    case Camp = 'camp';
    case Trip = 'trip';

    public function labelKey(): string
    {
        return match ($this) {
            self::Camp => 'vacations.pillar_camp',
            self::Trip => 'vacations.pillar_trip',
        };
    }

    public function marketingKeywordsKey(): string
    {
        return match ($this) {
            self::Camp => 'vacations.pillar_camps_keywords',
            self::Trip => 'vacations.pillar_trips_keywords',
        };
    }
}
