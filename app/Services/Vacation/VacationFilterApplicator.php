<?php

namespace App\Services\Vacation;

use App\Domain\Vacation\VacationListingFilter;
use App\Models\Camp;
use App\Models\Trip;
use Illuminate\Database\Eloquent\Builder;

class VacationFilterApplicator
{
    public function applyToCampQuery(Builder $query, VacationListingFilter $filter): Builder
    {
        if ($filter->species !== null) {
            $needle = strtolower($filter->species);
            $query->where(function (Builder $q) use ($needle) {
                $q->whereRaw('LOWER(CAST(target_fish AS CHAR)) LIKE ?', ['%' . $needle . '%']);
            });
        }

        if ($filter->duration !== null) {
            $this->applyCampDuration($query, $filter->duration);
        }

        return $query;
    }

    public function applyToTripQuery(Builder $query, VacationListingFilter $filter): Builder
    {
        if ($filter->species !== null) {
            $needle = strtolower($filter->species);
            $query->where(function (Builder $q) use ($needle) {
                $q->whereRaw('LOWER(CAST(target_species AS CHAR)) LIKE ?', ['%' . $needle . '%']);
            });
        }

        if ($filter->duration !== null) {
            $this->applyTripDuration($query, $filter->duration);
        }

        return $query;
    }

    public function applyCampSort(Builder $query, VacationListingFilter $filter): Builder
    {
        return match ($filter->sortBy) {
            'newest' => $query->orderByDesc('created_at'),
            'price-asc' => $query->orderBy('id'),
            'price-desc' => $query->orderByDesc('id'),
            default => $query->orderByDesc('created_at'),
        };
    }

    public function applyTripSort(Builder $query, VacationListingFilter $filter): Builder
    {
        return match ($filter->sortBy) {
            'newest' => $query->orderByDesc('created_at'),
            'price-asc' => $query->orderBy('price_per_person'),
            'price-desc' => $query->orderByDesc('price_per_person'),
            default => $query->orderByDesc('created_at'),
        };
    }

    /**
     * @return array<int, string>
     */
    public function speciesOptionsForCountry(?string $country): array
    {
        $campSpecies = Camp::query()
            ->where('status', 'active')
            ->when($country, fn ($q) => $q->whereRaw('LOWER(country) = ?', [strtolower($country)]))
            ->pluck('target_fish');

        $tripSpecies = Trip::query()
            ->where('status', 'active')
            ->when($country, fn ($q) => $q->whereRaw('LOWER(country) = ?', [strtolower($country)]))
            ->pluck('target_species');

        $names = collect();

        foreach ($campSpecies->merge($tripSpecies) as $json) {
            $items = is_array($json) ? $json : (is_string($json) ? json_decode($json, true) : []);
            if (! is_array($items)) {
                continue;
            }
            foreach ($items as $item) {
                $name = is_array($item) ? ($item['name'] ?? $item['value'] ?? null) : $item;
                if ($name) {
                    $names->push((string) $name);
                }
            }
        }

        return $names->unique()->sort()->values()->all();
    }

    private function applyCampDuration(Builder $query, string $duration): void
    {
        if (str_contains($duration, '+')) {
            $minNights = (int) filter_var($duration, FILTER_SANITIZE_NUMBER_INT);
            if ($minNights > 0) {
                $query->whereHas('accommodations', function (Builder $q) use ($minNights) {
                    $q->where('minimum_stay_nights', '>=', $minNights);
                });
            }
        }
    }

    private function applyTripDuration(Builder $query, string $duration): void
    {
        if (str_contains($duration, '+')) {
            $minDays = (int) filter_var($duration, FILTER_SANITIZE_NUMBER_INT);
            if ($minDays > 0) {
                $query->where('duration_days', '>=', $minDays);
            }
            return;
        }

        if (preg_match('/^(\d+)$/', $duration, $m)) {
            $query->where('duration_days', (int) $m[1]);
        }
    }
}
