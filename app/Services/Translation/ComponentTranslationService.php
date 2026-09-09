<?php

namespace App\Services\Translation;

use App\Models\AccommodationDetail;
use App\Models\AccommodationExtra;
use App\Models\AccommodationInclusive;
use App\Models\AccommodationPolicy;
use App\Models\AccommodationRentalCondition;
use App\Models\AccommodationType;
use App\Models\BathroomAmenity;
use App\Models\BoatExtra;
use App\Models\BoatExtras;
use App\Models\CampFacility;
use App\Models\Category;
use App\Models\ExtrasPrice;
use App\Models\Facility;
use App\Models\FishingEquipment;
use App\Models\GuidingAdditionalInformation;
use App\Models\GuidingBoatDescription;
use App\Models\GuidingBoatType;
use App\Models\GuidingRecommendations;
use App\Models\GuidingRequirements;
use App\Models\Inclussion;
use App\Models\KitchenEquipment;
use App\Models\Method;
use App\Models\RentalBoatRequirement;
use App\Models\RoomConfiguration;
use App\Models\Target;
use App\Models\Water;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Stichoza\GoogleTranslate\GoogleTranslate;

/**
 * Translates the small "master data" lookup tables (amenities, extras,
 * requirements, boat types, target fish, etc.) that back the dropdowns/
 * checkboxes used across guidings, vacations, accommodations and rental
 * boats. Unlike Guiding/Vacation/Listing translation, these rows store
 * their DE/EN pair directly as columns on the row (typically `name` /
 * `name_en`) rather than in the polymorphic `languages` table.
 */
class ComponentTranslationService
{
    public const SOURCE_LANGUAGE = 'de';

    public const TARGET_LANGUAGE = 'en';

    /**
     * key => [model, label, fields => [[de_column, en_column], ...], duplicate_source]
     *
     * `duplicate_source` tells the duplicate-detector (see pendingFieldWork())
     * which language a DE==EN duplicate row is actually written in for this
     * table, so it knows which column to regenerate:
     *   - 'en': these lookups were built with English phrases and the DE
     *     column defaults/copies the EN value instead of holding a real
     *     translation (confirmed against live data — e.g. Facility rows
     *     like "Private jetty / boat dock" sitting in the `name` column).
     *   - null: curated taxonomy tables (fishing methods, waters, target
     *     fish) where a duplicate is often a genuine shared term/proper
     *     noun (species names, "GPS", "Fjord") rather than a copy bug, and
     *     direction isn't reliably guessable — these are surfaced in
     *     --report-missing for manual review instead of auto-translated.
     *
     * @var array<string, array{model: class-string<Model>, label: string, fields: array<int, array{0: string, 1: string}>, duplicate_source: ?string}>
     */
    public const REGISTRY = [
        'facility' => ['model' => Facility::class, 'label' => 'Accommodation Amenities', 'fields' => [['name', 'name_en']], 'duplicate_source' => 'en'],
        'bathroom-amenity' => ['model' => BathroomAmenity::class, 'label' => 'Bathroom Amenities', 'fields' => [['name', 'name_en']], 'duplicate_source' => 'en'],
        'kitchen-equipment' => ['model' => KitchenEquipment::class, 'label' => 'Kitchen Equipment', 'fields' => [['name', 'name_en']], 'duplicate_source' => 'en'],
        'accommodation-extra' => ['model' => AccommodationExtra::class, 'label' => 'Accommodation Extras', 'fields' => [['name', 'name_en']], 'duplicate_source' => 'en'],
        'accommodation-inclusive' => ['model' => AccommodationInclusive::class, 'label' => 'Accommodation Inclusives', 'fields' => [['name', 'name_en']], 'duplicate_source' => 'en'],
        'accommodation-policy' => ['model' => AccommodationPolicy::class, 'label' => 'Accommodation Policies', 'fields' => [['name', 'name_en']], 'duplicate_source' => 'en'],
        'accommodation-detail' => ['model' => AccommodationDetail::class, 'label' => 'Accommodation Details', 'fields' => [['name', 'name_en']], 'duplicate_source' => 'en'],
        'accommodation-rental-condition' => ['model' => AccommodationRentalCondition::class, 'label' => 'Accommodation Rental Conditions', 'fields' => [['name', 'name_en']], 'duplicate_source' => 'en'],
        'accommodation-type' => ['model' => AccommodationType::class, 'label' => 'Accommodation Types', 'fields' => [['name', 'name_en']], 'duplicate_source' => 'en'],
        'room-configuration' => ['model' => RoomConfiguration::class, 'label' => 'Room Configurations', 'fields' => [['name', 'name_en']], 'duplicate_source' => 'en'],
        'boat-extra' => ['model' => BoatExtra::class, 'label' => 'Rental Boat / Accommodation Boat Extras', 'fields' => [['name', 'name_en']], 'duplicate_source' => 'en'],
        'boat-extras' => ['model' => BoatExtras::class, 'label' => 'Guiding Boat Extras', 'fields' => [['name', 'name_en']], 'duplicate_source' => 'en'],
        'inclussion' => ['model' => Inclussion::class, 'label' => 'Rental Boat Inclusions', 'fields' => [['name', 'name_en']], 'duplicate_source' => 'en'],
        'rental-boat-requirement' => ['model' => RentalBoatRequirement::class, 'label' => 'Rental Boat Requirements', 'fields' => [['name', 'name_en'], ['placeholder', 'placeholder_en']], 'duplicate_source' => 'en'],
        'guiding-boat-type' => ['model' => GuidingBoatType::class, 'label' => 'Guiding Boat Types', 'fields' => [['name', 'name_en']], 'duplicate_source' => 'en'],
        'guiding-boat-description' => ['model' => GuidingBoatDescription::class, 'label' => 'Guiding Boat Descriptions', 'fields' => [['name', 'name_en']], 'duplicate_source' => 'en'],
        'guiding-additional-information' => ['model' => GuidingAdditionalInformation::class, 'label' => 'Guiding Additional Information', 'fields' => [['name', 'name_en']], 'duplicate_source' => 'en'],
        'guiding-recommendation' => ['model' => GuidingRecommendations::class, 'label' => 'Guiding Recommendations', 'fields' => [['name', 'name_en']], 'duplicate_source' => 'en'],
        'guiding-requirement' => ['model' => GuidingRequirements::class, 'label' => 'Guiding Requirements', 'fields' => [['name', 'name_en']], 'duplicate_source' => 'en'],
        'fishing-equipment' => ['model' => FishingEquipment::class, 'label' => 'Fishing Equipment', 'fields' => [['name', 'name_en']], 'duplicate_source' => 'en'],
        'extras-price' => ['model' => ExtrasPrice::class, 'label' => 'Extras Prices', 'fields' => [['name', 'name_en']], 'duplicate_source' => 'en'],
        'camp-facility' => ['model' => CampFacility::class, 'label' => 'Camp Facilities', 'fields' => [['name_de', 'name_en']], 'duplicate_source' => 'en'],
        'category' => ['model' => Category::class, 'label' => 'Forum Categories', 'fields' => [['name', 'name_en']], 'duplicate_source' => 'en'],
        'water' => ['model' => Water::class, 'label' => 'Waters', 'fields' => [['name', 'name_en']], 'duplicate_source' => null],
        'method' => ['model' => Method::class, 'label' => 'Fishing Methods', 'fields' => [['name', 'name_en']], 'duplicate_source' => null],
        'target' => ['model' => Target::class, 'label' => 'Target Fish', 'fields' => [['name', 'name_en']], 'duplicate_source' => null],
    ];

    /**
     * @return array<int, string>
     */
    public function modelKeys(): array
    {
        return array_keys(self::REGISTRY);
    }

    /**
     * Normalize the requested `--model` option values against the registry.
     *
     * @param  array<int, string>  $requested
     * @return array<int, string>
     */
    public function resolveKeys(array $requested): array
    {
        if (empty($requested)) {
            return $this->modelKeys();
        }

        $keys = [];
        foreach ($requested as $raw) {
            foreach (explode(',', (string) $raw) as $key) {
                $key = trim($key);
                if ($key !== '' && isset(self::REGISTRY[$key])) {
                    $keys[] = $key;
                }
            }
        }

        return array_values(array_unique($keys));
    }

    public function labelFor(string $key): string
    {
        return self::REGISTRY[$key]['label'] ?? $key;
    }

    /**
     * @return array<int, array{0: string, 1: string}>
     */
    public function fieldsFor(string $key): array
    {
        return self::REGISTRY[$key]['fields'] ?? [];
    }

    /**
     * Rows for a registry key that are worth loading: with --force every row
     * is a candidate (translation is re-derived from the DE column), without
     * it only rows with at least one incomplete DE/EN pair are loaded.
     */
    public function rowsToProcess(string $key, bool $force): Collection
    {
        $config = self::REGISTRY[$key];
        $modelClass = $config['model'];

        /** @var Builder $query */
        $query = $modelClass::query()->orderBy('id');

        if (! $force) {
            $query->where(function (Builder $outer) use ($config) {
                foreach ($config['fields'] as [$deField, $enField]) {
                    $outer->orWhere(function (Builder $pair) use ($deField, $enField) {
                        $pair->where(function (Builder $a) use ($deField, $enField) {
                            $a->whereNotNull($deField)->where($deField, '!=', '')
                                ->where(function (Builder $b) use ($enField) {
                                    $b->whereNull($enField)->orWhere($enField, '');
                                });
                        })->orWhere(function (Builder $a) use ($deField, $enField) {
                            $a->whereNotNull($enField)->where($enField, '!=', '')
                                ->where(function (Builder $b) use ($deField) {
                                    $b->whereNull($deField)->orWhere($deField, '');
                                });
                        })->orWhere(function (Builder $a) use ($deField, $enField) {
                            // DE column literally duplicates EN (never actually
                            // translated, just copied on create/import).
                            $a->whereNotNull($deField)->where($deField, '!=', '')
                                ->whereNotNull($enField)->where($enField, '!=', '')
                                ->whereColumn($deField, $enField);
                        });
                    });
                }
            });
        }

        return $query->get();
    }

    /**
     * The language a DE==EN duplicate on this table is actually written in
     * (see REGISTRY doc block), or null when the table's duplicates aren't
     * reliably one direction and should be left for manual review.
     */
    public function duplicateSourceFor(string $key): ?string
    {
        return self::REGISTRY[$key]['duplicate_source'] ?? null;
    }

    /**
     * DE/EN field pairs on this row that are an exact (case-insensitive)
     * duplicate but whose table has no reliable auto-fix direction
     * (duplicate_source is null) — surfaced for --report-missing so an
     * admin can resolve them by hand instead of them being silently
     * skipped.
     *
     * @return array<int, array{de_field: string, en_field: string}>
     */
    public function ambiguousDuplicates(Model $row, string $key): array
    {
        if ($this->duplicateSourceFor($key) !== null) {
            return [];
        }

        $ambiguous = [];

        foreach ($this->fieldsFor($key) as [$deField, $enField]) {
            $de = trim((string) $row->getRawOriginal($deField));
            $en = trim((string) $row->getRawOriginal($enField));

            if ($de !== '' && $en !== '' && mb_strtolower($de) === mb_strtolower($en)) {
                $ambiguous[] = ['de_field' => $deField, 'en_field' => $enField];
            }
        }

        return $ambiguous;
    }

    /**
     * Determine which DE/EN field pairs on this row still need a
     * translation, and in which direction. DE is treated as the
     * authoritative source: with $force, a non-empty DE column always
     * regenerates EN. Without $force, empty columns are filled — from DE
     * to EN when DE is populated, or EN to DE as a fallback when a row was
     * only ever entered in English. A DE column that's an exact (case
     * insensitive) duplicate of EN is also treated as untranslated — many
     * rows were created with the DE column defaulted/copied from EN rather
     * than actually translated — and the column matching this table's
     * duplicate_source is regenerated from the other, regardless of
     * $force, since forcing a de→en pass on English-in-disguise text would
     * not fix it. Tables with duplicate_source === null skip duplicates
     * entirely here (see ambiguousDuplicates()).
     *
     * @return array<int, array{de_field: string, en_field: string, from: string, to: string, text: string}>
     */
    public function pendingFieldWork(Model $row, string $key, bool $force): array
    {
        $work = [];
        $duplicateSource = $this->duplicateSourceFor($key);

        foreach ($this->fieldsFor($key) as [$deField, $enField]) {
            $de = trim((string) $row->getRawOriginal($deField));
            $en = trim((string) $row->getRawOriginal($enField));

            $isUntranslatedDuplicate = $de !== '' && $en !== '' && mb_strtolower($de) === mb_strtolower($en);

            if ($isUntranslatedDuplicate && $duplicateSource === self::TARGET_LANGUAGE) {
                $work[] = [
                    'de_field' => $deField,
                    'en_field' => $enField,
                    'from' => self::TARGET_LANGUAGE,
                    'to' => self::SOURCE_LANGUAGE,
                    'text' => $en,
                ];
            } elseif ($isUntranslatedDuplicate && $duplicateSource === self::SOURCE_LANGUAGE) {
                $work[] = [
                    'de_field' => $deField,
                    'en_field' => $enField,
                    'from' => self::SOURCE_LANGUAGE,
                    'to' => self::TARGET_LANGUAGE,
                    'text' => $de,
                ];
            } elseif ($isUntranslatedDuplicate) {
                // duplicate_source is null for this table — ambiguous,
                // leave both columns untouched (see ambiguousDuplicates()).
                continue;
            } elseif ($de !== '' && ($force || $en === '')) {
                $work[] = [
                    'de_field' => $deField,
                    'en_field' => $enField,
                    'from' => self::SOURCE_LANGUAGE,
                    'to' => self::TARGET_LANGUAGE,
                    'text' => $de,
                ];
            } elseif ($de === '' && $en !== '') {
                $work[] = [
                    'de_field' => $deField,
                    'en_field' => $enField,
                    'from' => self::TARGET_LANGUAGE,
                    'to' => self::SOURCE_LANGUAGE,
                    'text' => $en,
                ];
            }
        }

        return $work;
    }

    /**
     * Translate a single piece of text. Returns null on failure so callers
     * can skip writing rather than storing a blank/garbage value.
     */
    public function translateField(string $text, string $from, string $to): ?string
    {
        try {
            $translated = GoogleTranslate::trans($text, $to, $from);

            if (! is_string($translated) || trim($translated) === '') {
                return null;
            }

            return $translated;
        } catch (\Throwable $e) {
            Log::error('Component translation failed', [
                'from' => $from,
                'to' => $to,
                'text' => substr($text, 0, 100),
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
