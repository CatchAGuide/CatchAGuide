<?php

namespace App\Domain\Vacation;

use Illuminate\Http\Request;

enum VacationPillar: string
{
    case Trips = 'trips';
    case Camps = 'camps';

    public static function fromRoute(string $pillar): self
    {
        return self::from(strtolower($pillar));
    }

    public static function fromRequest(Request $request): self
    {
        $route = $request->route();
        $pillar = $route?->defaults['pillar'] ?? match ($route?->getName()) {
            'vacations.trips.index', 'vacations.trips.show' => 'trips',
            'vacations.camps.index', 'vacations.camps.show' => 'camps',
            default => null,
        };

        if ($pillar === null) {
            throw new \InvalidArgumentException('Unable to resolve vacation pillar from route.');
        }

        return self::fromRoute($pillar);
    }

    public function indexRouteName(): string
    {
        return match ($this) {
            self::Trips => 'vacations.trips.index',
            self::Camps => 'vacations.camps.index',
        };
    }

    public function showRouteName(): string
    {
        return match ($this) {
            self::Trips => 'vacations.trips.show',
            self::Camps => 'vacations.camps.show',
        };
    }

    public function indexTitleKey(): string
    {
        return match ($this) {
            self::Trips => 'vacations.pillar_index_trips_title',
            self::Camps => 'vacations.pillar_index_camps_title',
        };
    }

    public function descriptionKey(): string
    {
        return match ($this) {
            self::Trips => 'vacations.pillar_trips_desc',
            self::Camps => 'vacations.pillar_camps_desc',
        };
    }

    public function marketingKeywordsKey(): string
    {
        return match ($this) {
            self::Trips => 'vacations.pillar_trips_keywords',
            self::Camps => 'vacations.pillar_camps_keywords',
        };
    }

    public function countryTitleKey(): string
    {
        return match ($this) {
            self::Trips => 'vacations.pillar_country_trips_title',
            self::Camps => 'vacations.pillar_country_camps_title',
        };
    }

    public function emptyStateKey(): string
    {
        return match ($this) {
            self::Trips => 'vacations.empty_state_body_trip',
            self::Camps => 'vacations.empty_state_body_camp',
        };
    }

    public function cssModifier(): string
    {
        return match ($this) {
            self::Trips => 'trip',
            self::Camps => 'camp',
        };
    }

    public function analyticsPage(bool $isCountryPage): string
    {
        return match ($this) {
            self::Trips => $isCountryPage ? 'vacation-trips-country' : 'vacation-trips-index',
            self::Camps => $isCountryPage ? 'vacation-camps-country' : 'vacation-camps-index',
        };
    }

    public function sliderId(): string
    {
        return $this->value.'-countries';
    }

    public function faqPageKey(): string
    {
        return match ($this) {
            self::Trips => 'vacation-trips',
            self::Camps => 'vacation-camps',
        };
    }

    public function faqConfigKey(): string
    {
        return match ($this) {
            self::Trips => 'trips_faq',
            self::Camps => 'camps_faq',
        };
    }

    public function offerControllerClass(): string
    {
        return match ($this) {
            self::Trips => \App\Http\Controllers\TripOfferController::class,
            self::Camps => \App\Http\Controllers\CampOfferController::class,
        };
    }
}
