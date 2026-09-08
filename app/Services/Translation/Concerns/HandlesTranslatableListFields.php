<?php

namespace App\Services\Translation\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Generic scalar/list-field collection + reconstruction used by translation services
 * that flatten a model's translatable fields into a flat key => string map for the
 * translation engine, then rebuild the original (scalar or JSON-list) shape afterwards.
 */
trait HandlesTranslatableListFields
{
    /**
     * @param  array<int, string>  $fieldNames
     * @return array<string, string>
     */
    private function collectScalarFields(Model $listing, array $fieldNames): array
    {
        $fields = [];

        foreach ($fieldNames as $fieldName) {
            $value = $listing->{$fieldName} ?? null;

            if (is_string($value) && trim($value) !== '' && ! is_numeric($value)) {
                $fields[$fieldName] = trim($value);
            }
        }

        return $fields;
    }

    /**
     * @return array<string, string>
     */
    private function collectListField(Model $listing, string $fieldName): array
    {
        $fields = [];
        $decoded = $this->decodeValue($listing->{$fieldName} ?? null);

        if (! is_array($decoded)) {
            if (is_string($listing->{$fieldName} ?? null) && str_contains((string) $listing->{$fieldName}, ',')) {
                $decoded = array_map('trim', explode(',', (string) $listing->{$fieldName}));
            } else {
                return $fields;
            }
        }

        foreach ($decoded as $index => $item) {
            if (is_string($item) && trim($item) !== '' && ! is_numeric($item)) {
                $fields["{$fieldName}_{$index}"] = trim($item);
                continue;
            }

            if (is_array($item)) {
                if (! empty($item['value']) && is_string($item['value']) && ! is_numeric($item['value'])) {
                    $fields["{$fieldName}_{$index}"] = trim($item['value']);
                } elseif (! empty($item['name']) && is_string($item['name']) && ! is_numeric($item['name'])) {
                    $fields["{$fieldName}_{$index}"] = trim($item['name']);
                }
            }
        }

        return $fields;
    }

    /**
     * @param  array<int, mixed>  $original
     * @param  array<string, string>  $translatedFields
     * @return array<int, mixed>
     */
    private function reconstructIndexedArray(array $original, string $fieldPrefix, array &$translatedFields): array
    {
        $reconstructed = $original;

        foreach ($original as $index => $item) {
            $key = "{$fieldPrefix}_{$index}";

            if (! isset($translatedFields[$key])) {
                continue;
            }

            if (is_string($item)) {
                $reconstructed[$index] = $translatedFields[$key];
            } elseif (is_array($item)) {
                if (array_key_exists('value', $item)) {
                    $reconstructed[$index]['value'] = $translatedFields[$key];
                } elseif (array_key_exists('name', $item)) {
                    $reconstructed[$index]['name'] = $translatedFields[$key];
                }
            }

            unset($translatedFields[$key]);
        }

        if ($fieldPrefix === 'pricing_extra') {
            foreach ($original as $index => $item) {
                $nameKey = "pricing_extra_{$index}_name";
                if (isset($translatedFields[$nameKey]) && is_array($reconstructed[$index] ?? null)) {
                    $reconstructed[$index]['name'] = $translatedFields[$nameKey];
                    unset($translatedFields[$nameKey]);
                }
            }
        }

        if ($fieldPrefix === 'boat_information') {
            foreach ($original as $index => $item) {
                $valueKey = "boat_information_{$index}_value";
                if (isset($translatedFields[$valueKey]) && is_array($reconstructed[$index] ?? null)) {
                    $reconstructed[$index]['value'] = $translatedFields[$valueKey];
                    unset($translatedFields[$valueKey]);
                }
            }
        }

        // Keep associative keys intact (e.g. keyed maps). Only re-index true lists.
        return array_is_list($reconstructed) ? array_values($reconstructed) : $reconstructed;
    }

    private function decodeValue(mixed $value): mixed
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }
}
