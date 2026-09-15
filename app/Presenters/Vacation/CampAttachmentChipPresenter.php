<?php

namespace App\Presenters\Vacation;

use App\Models\Guiding;

class CampAttachmentChipPresenter
{
    /**
     * Shore/Boat chip for guiding cards. Drops leftover private/shared tour types.
     */
    public static function fishingFromChipValue(mixed $type): ?string
    {
        $trimmed = trim((string) $type);
        if ($trimmed === '') {
            return null;
        }

        if (Guiding::looksLikeTourPrivacy($trimmed)) {
            return null;
        }

        return $trimmed;
    }

    /**
     * Shared persons/capacity label for camp attachment cards.
     * Numeric values become "4 Pers"; non-numeric strings are kept as-is.
     */
    public static function personsValue(mixed $count): ?string
    {
        if ($count === null || $count === '') {
            return null;
        }

        if (is_numeric($count)) {
            $numeric = (int) $count;
            if ($numeric <= 0) {
                return null;
            }

            return $numeric.' '.__('vacations.pers_short');
        }

        $trimmed = trim((string) $count);

        return $trimmed !== '' ? $trimmed : null;
    }

    /**
     * Living-area chip value. Numeric sqm gets a unit suffix.
     */
    public static function areaValue(mixed $sqm): ?string
    {
        if ($sqm === null || $sqm === '') {
            return null;
        }

        if (is_numeric($sqm)) {
            return $sqm.' '.__('vacations.unit_sqm');
        }

        $trimmed = trim((string) $sqm);

        return $trimmed !== '' ? $trimmed : null;
    }

    /**
     * Map rental-boat spec keys onto the shared chip types.
     *
     * @param  array<int, array<string, mixed>>  $specs
     * @return array<int, array{type: string, value: string, label: ?string}>
     */
    public static function boatChips(array $specs): array
    {
        $chips = [];

        foreach ($specs as $spec) {
            if (! is_array($spec)) {
                continue;
            }

            $key = (string) ($spec['key'] ?? '');
            $rawValue = $spec['value'] ?? null;
            if ($rawValue === null || $rawValue === '') {
                continue;
            }

            if ($key === 'capacity' || $key === 'persons') {
                $value = self::personsValue($rawValue);
                if ($value === null) {
                    continue;
                }

                $chips[] = [
                    'type' => 'persons',
                    'value' => $value,
                    'label' => null,
                ];
                continue;
            }

            $type = match ($key) {
                'engine' => 'engine',
                'license' => 'license',
                'length' => 'length',
                default => 'generic',
            };

            $chips[] = [
                'type' => $type,
                'value' => (string) $rawValue,
                'label' => $type === 'generic' ? ($spec['label'] ?? null) : null,
            ];
        }

        return $chips;
    }

    /**
     * Bedroom-count chip, same numeric treatment as bathrooms.
     */
    public static function bedroomsValue(mixed $count): ?string
    {
        if ($count === null || $count === '') {
            return null;
        }

        if (is_numeric($count)) {
            $numeric = (int) $count;

            return $numeric > 0 ? (string) $numeric : null;
        }

        $trimmed = trim((string) $count);
        if ($trimmed === '' || strtolower($trimmed) === 'keine angabe') {
            return null;
        }

        return $trimmed;
    }

    /**
     * One chip per bed type, e.g. "(5) Einzelbett".
     *
     * @param  array<int, array<string, mixed>|string>  $items
     * @return array<int, array{type: string, value: string, label: ?string}>
     */
    public static function bedChips(array $items = [], mixed $summary = null): array
    {
        $chips = [];
        $locale = app()->getLocale();

        foreach ($items as $item) {
            if (is_string($item)) {
                $trimmed = trim($item);
                if ($trimmed === '') {
                    continue;
                }

                $chips[] = [
                    'type' => 'bed',
                    'value' => $trimmed,
                    'label' => null,
                ];
                continue;
            }

            if (! is_array($item)) {
                continue;
            }

            $count = $item['count'] ?? ($item['value'] ?? null);
            if ($count === null || $count === '') {
                continue;
            }

            $name = $locale === 'en'
                ? ($item['name_en'] ?? ($item['name'] ?? null))
                : ($item['name'] ?? ($item['name_en'] ?? null));
            $name = is_string($name) ? trim($name) : '';
            if ($name === '') {
                continue;
            }

            $chips[] = [
                'type' => 'bed',
                'value' => '('.$count.') '.$name,
                'label' => null,
            ];
        }

        if ($chips === [] && is_string($summary) && trim($summary) !== '') {
            $trimmedSummary = trim($summary);
            if (strtolower($trimmedSummary) !== 'keine angaben zur bettenanzahl') {
                foreach (preg_split('/\s*,\s*/', $trimmedSummary) ?: [] as $part) {
                    if ($part === '') {
                        continue;
                    }

                    $chips[] = [
                        'type' => 'bed',
                        'value' => $part,
                        'label' => null,
                    ];
                }
            }
        }

        return $chips;
    }

    /**
     * One chip per catalog water type, e.g. "See" / "Lake".
     *
     * @param  array<int, array<string, mixed>|string>  $items
     * @return array<int, array{type: string, value: string, label: ?string}>
     */
    public static function waterTypeChips(array $items): array
    {
        $chips = [];

        foreach ($items as $item) {
            $value = is_array($item)
                ? translated_catalog_label($item)
                : trim((string) $item);
            if ($value === '') {
                continue;
            }

            $chips[] = [
                'type' => 'water-type',
                'value' => $value,
                'label' => null,
            ];
        }

        return $chips;
    }

    /**
     * Hover tooltip naming the fact behind a chip icon.
     */
    public static function tooltipFor(string $type, ?string $label = null): string
    {
        $fromType = match ($type) {
            'persons' => __('vacations.max_persons'),
            'duration' => __('guidings.Duration'),
            'tour' => __('vacations.type_label'),
            'boat' => __('vacations.rental_boat'),
            'area' => __('vacations.chip_living_area'),
            'bath' => __('vacations.chip_bathrooms'),
            'bedrooms' => __('vacations.chip_bedrooms'),
            'bed' => __('vacations.chip_beds'),
            'water' => __('vacations.label_water'),
            'water-type' => __('vacations.chip_water_type'),
            'parking' => __('vacations.label_parking'),
            'jetty' => __('vacations.label_jetty'),
            'engine' => __('rental_boats.engine'),
            'license' => __('rental_boats.license'),
            'length' => __('rental_boats.length'),
            default => null,
        };

        if (filled($fromType)) {
            return (string) $fromType;
        }

        if (filled($label)) {
            return (string) $label;
        }

        return __('vacations.chip_details');
    }
}
