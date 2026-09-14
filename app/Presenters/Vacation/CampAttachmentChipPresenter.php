<?php

namespace App\Presenters\Vacation;

class CampAttachmentChipPresenter
{
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
            'water' => __('vacations.label_water'),
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
