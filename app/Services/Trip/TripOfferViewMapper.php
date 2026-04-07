<?php

namespace App\Services\Trip;

use App\Models\BoatExtras;
use App\Models\Method;
use App\Models\Target;
use App\Models\Trip;
use App\Models\Water;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Maps a Trip model to a view-ready array for the public trip offer page.
 * Uses batch loading for related lookups (Target, Method, Water, BoatExtras) to avoid N+1.
 */
class TripOfferViewMapper
{
    public function map(Trip $trip): array
    {
        $targetSpecies = $this->resolveTargetSpeciesNames($trip->target_species);
        $fishingMethods = $this->resolveFishingMethodNames($trip->fishing_methods);
        $waterTypes = $this->resolveWaterTypeNames($trip->water_types);
        $boatFeatures = $this->resolveBoatFeatureNames($trip->boat_features);

        $roomTypeNames = $this->extractTagifyNames($trip->room_types ?? []);
        $providerCertifications = $this->parseCommaList($trip->provider_certifications);
        $cateringList = $this->extractTagifyNames($trip->catering ?? []);
        $guideLanguagesList = $this->extractTagifyNames($trip->guide_languages ?? []);

        [$descriptionShort, $descriptionRest] = $this->splitDescription((string) ($trip->description ?? ''));
        $highlightItems = $this->extractHighlightItems($trip->trip_highlights ?? []);

        $additionalInfo = $trip->additional_info ?? [];
        $additionalInfoList = [];
        $additionalInfoStructured = [];
        $fullWidthKeys = ['clothing_recommendations', 'equipment_to_bring'];
        $excludeFromStructured = ['catch_and_release', 'catch_success', 'non_fishing_activities'];

        if (is_array($additionalInfo)) {
            foreach ($additionalInfo as $key => $config) {
                if (! is_array($config)) {
                    continue;
                }
                $enabled = (bool) ($config['enabled'] ?? false);
                $details = $config['details'] ?? null;
                if (! $enabled && ($details === null || $details === '')) {
                    continue;
                }
                $label = __('trips.' . $key);
                if ($label === 'trips.' . $key) {
                    $label = ucwords(str_replace('_', ' ', $key));
                }
                $additionalInfoList[] = $details ? $label . ': ' . $details : $label;
                if (! in_array($key, $excludeFromStructured, true)) {
                    $additionalInfoStructured[] = [
                        'key'        => $key,
                        'label'      => $label,
                        'value'      => $details ? (string) $details : '',
                        'full_width' => in_array($key, $fullWidthKeys, true),
                    ];
                }
            }
        }

        $nonFishingActivitiesList = [];
        if (is_array($additionalInfo) && ! empty($additionalInfo['non_fishing_activities']['enabled'])) {
            $details = $additionalInfo['non_fishing_activities']['details'] ?? null;
            if ($details && is_string($details)) {
                $nonFishingActivitiesList = array_values(array_filter(array_map('trim', preg_split('/[,;\n]+/', $details))));
            }
        }

        $catchAndReleaseValue = isset($additionalInfo['catch_and_release']['enabled']) && $additionalInfo['catch_and_release']['enabled']
            ? ($additionalInfo['catch_and_release']['details'] ?? null)
            : null;
        $catchSuccessValue = isset($additionalInfo['catch_success']['enabled']) && $additionalInfo['catch_success']['enabled']
            ? ($additionalInfo['catch_success']['details'] ?? null)
            : null;

        $included = $this->normalizeIncludedExcluded($trip->included ?? []);
        $excluded = $this->normalizeExcluded($trip->excluded ?? []);

        $rawSkillLevel = $trip->skill_level;
        if (is_array($rawSkillLevel)) {
            $skillSlugs = collect($rawSkillLevel)
                ->map(function ($item) {
                    if (is_array($item)) {
                        return $item['name'] ?? $item['value'] ?? null;
                    }
                    return $item !== null ? (string) $item : null;
                })
                ->filter()
                ->values()
                ->toArray();
        } else {
            $skillSlugs = $rawSkillLevel ? [(string) $rawSkillLevel] : [];
        }

        $skillLevelFormatted = $this->formatSkillLevelForDisplay($skillSlugs);
        $fishingStyleFormatted = $this->formatFishingStyleForDisplay($trip->fishing_style ? (string) $trip->fishing_style : null);

        $bestSeasonFromLabel = $this->formatMonthLabel($trip->best_season_from);
        $bestSeasonToLabel = $this->formatMonthLabel($trip->best_season_to);

        $acc = [
            'name'                  => $trip->accommodation_type ? (string) $trip->accommodation_type : null,
            'description'           => $trip->accommodation_description ? (string) $trip->accommodation_description : null,
            'room_types'            => $roomTypeNames,
            'catering'              => $cateringList,
            'distance_to_water'     => $trip->distance_to_water ? (string) $trip->distance_to_water : null,
            'nearest_airport'       => $trip->nearest_airport ? (string) $trip->nearest_airport : null,
            'meeting_point'         => $trip->meeting_point ? (string) $trip->meeting_point : null,
            'arrival_day'           => $trip->arrival_day ? (string) $trip->arrival_day : null,
            'best_arrival_options'  => $trip->best_arrival_options ? (string) $trip->best_arrival_options : null,
        ];
        $acc['has_content'] = $this->hasAnyValue($acc, ['name', 'description', 'room_types', 'catering', 'distance_to_water', 'nearest_airport', 'meeting_point', 'arrival_day', 'best_arrival_options']);

        $provider = [
            'name'                 => $trip->provider_name,
            'photo'                => $trip->provider_photo,
            'experience'           => $trip->provider_experience,
            'certifications_list'  => $providerCertifications,
            'guide_languages'      => $guideLanguagesList,
        ];
        $provider['has_content'] = $this->hasAnyValue($provider, ['name', 'photo', 'experience', 'certifications_list', 'guide_languages']);

        $boat = [
            'boat_type'        => $trip->boat_type ? (string) $trip->boat_type : null,
            'boat_information' => $trip->boat_information ? (string) $trip->boat_information : null,
            'boat_staff'       => $trip->boat_staff ? (string) $trip->boat_staff : null,
            'features'         => $boatFeatures,
        ];
        $boat['has_content'] = $this->hasAnyValue($boat, ['boat_type', 'boat_information', 'boat_staff', 'features']);

        return [
            'id'                         => $trip->id,
            'title'                      => $trip->title,
            'location'                   => $trip->location,
            'country'                    => $trip->country,
            'city'                       => $trip->city,
            'region'                     => $trip->region,
            'coordinates'                 => [
                'lat' => $trip->latitude,
                'lng' => $trip->longitude,
            ],
            'duration'                   => [
                'nights' => $trip->duration_nights,
                'days'   => $trip->duration_days,
            ],
            'group_size'                 => [
                'min' => $trip->group_size_min,
                'max' => $trip->group_size_max,
            ],
            'price'                      => [
                'per_person'           => $trip->price_per_person,
                'single_room_addition' => $trip->price_single_room_addition,
                'currency'             => $trip->currency ?? 'EUR',
            ],
            'skill_level'                => $skillSlugs,
            'skill_level_formatted'      => $skillLevelFormatted,
            'fishing_style'              => $trip->fishing_style,
            'fishing_style_formatted'     => $fishingStyleFormatted,
            'boat_type'                  => $trip->boat_type ? (string) $trip->boat_type : null,
            'best_season'                 => [
                'from' => $trip->best_season_from,
                'to'   => $trip->best_season_to,
            ],
            'best_season_formatted'       => [
                'from' => $bestSeasonFromLabel,
                'to'   => $bestSeasonToLabel,
            ],
            'target_species'             => $targetSpecies,
            'fishing_methods'            => $fishingMethods,
            'water_types'                => $waterTypes,
            'trip_schedule'              => is_array($trip->trip_schedule) ? $trip->trip_schedule : [],
            'included'                   => $included,
            'excluded'                  => $excluded,
            'additional_info'           => $additionalInfoList,
            'additional_info_structured' => $additionalInfoStructured,
            'non_fishing_activities_list' => $nonFishingActivitiesList,
            'catch_and_release_value'    => $catchAndReleaseValue ? (string) $catchAndReleaseValue : null,
            'catch_success_value'        => $catchSuccessValue ? (string) $catchSuccessValue : null,
            'cancellation_policy'        => $trip->cancellation_policy,
            'downpayment_policy'         => $trip->downpayment_policy ? (string) $trip->downpayment_policy : null,
            'provider'                   => $provider,
            'accommodation'              => $acc,
            'boat'                       => $boat,
            'description'                => [
                'full'  => (string) ($trip->description ?? ''),
                'intro'  => $descriptionShort,
                'rest'   => $descriptionRest,
            ],
            'trip_highlights'            => $highlightItems,
        ];
    }

    /** Resolve target species IDs to names in one query. */
    private function resolveTargetSpeciesNames(?array $items): array
    {
        if (empty($items)) {
            return [];
        }
        $ids = $this->collectNumericIds($items);
        $targets = $ids ? Target::whereIn('id', $ids)->get()->keyBy('id') : collect();
        return $this->mapItemsToNames($items, $targets, 'name');
    }

    private function resolveFishingMethodNames(?array $items): array
    {
        if (empty($items)) {
            return [];
        }
        $ids = $this->collectNumericIds($items);
        $methods = $ids ? Method::whereIn('id', $ids)->get()->keyBy('id') : collect();
        return $this->mapItemsToNames($items, $methods, 'name');
    }

    private function resolveWaterTypeNames(?array $items): array
    {
        if (empty($items)) {
            return [];
        }
        $ids = $this->collectNumericIds($items);
        $waters = $ids ? Water::whereIn('id', $ids)->get()->keyBy('id') : collect();
        return $this->mapItemsToNames($items, $waters, 'name');
    }

    private function resolveBoatFeatureNames(?array $items): array
    {
        if (empty($items)) {
            return [];
        }
        $ids = $this->collectNumericIds($items);
        $extras = $ids ? BoatExtras::whereIn('id', $ids)->get()->keyBy('id') : collect();
        return $this->mapItemsToNames($items, $extras, 'name');
    }

    private function collectNumericIds(array $items): array
    {
        $ids = [];
        foreach ($items as $item) {
            if (is_numeric($item)) {
                $ids[] = (int) $item;
            } elseif (is_array($item) && isset($item['id']) && is_numeric($item['id'])) {
                $ids[] = (int) $item['id'];
            }
        }
        return array_values(array_unique($ids));
    }

    private function mapItemsToNames(array $items, Collection $models, string $nameKey): array
    {
        $result = [];
        foreach ($items as $item) {
            if (is_array($item) && isset($item[$nameKey])) {
                $result[] = (string) $item[$nameKey];
                continue;
            }
            if (is_numeric($item)) {
                $model = $models->get((int) $item);
                if ($model && ! empty($model->$nameKey)) {
                    $result[] = (string) $model->$nameKey;
                }
                continue;
            }
            if (is_string($item) && $item !== '') {
                $result[] = $item;
            }
        }
        return array_values(array_filter($result));
    }

    private function extractTagifyNames(array $items): array
    {
        $names = [];
        foreach ($items as $item) {
            $label = is_array($item) ? ($item['value'] ?? $item['name'] ?? '') : (string) $item;
            $label = trim($label);
            if ($label !== '') {
                $names[] = $label;
            }
        }
        return $names;
    }

    private function parseCommaList(?string $value): array
    {
        if (empty($value)) {
            return [];
        }
        return array_values(array_filter(array_map('trim', explode(',', (string) $value))));
    }

    private function splitDescription(string $description): array
    {
        if (mb_strlen($description) <= 600) {
            return [$description, ''];
        }
        $intro = mb_substr($description, 0, 600);
        $lastDot = mb_strrpos($intro, '.');
        if ($lastDot !== false && $lastDot > 200) {
            $intro = mb_substr($intro, 0, $lastDot + 1);
        }
        $rest = trim(mb_substr($description, mb_strlen($intro)));
        return [$intro, $rest];
    }

    private function extractHighlightItems($raw): array
    {
        if (! is_array($raw)) {
            return [];
        }
        $items = isset($raw['items']) && is_array($raw['items']) ? $raw['items'] : (array_is_list($raw) ? $raw : []);
        $result = [];
        foreach ($items as $item) {
            $text = is_array($item) ? ($item['text'] ?? '') : (string) $item;
            $text = trim($text);
            if ($text !== '') {
                $result[] = $text;
            }
        }
        return $result;
    }

    /** Normalize included items to [{ label }]. */
    private function normalizeIncludedExcluded(array $items): array
    {
        $result = [];
        foreach ($items as $item) {
            $label = is_array($item) && isset($item['name']) ? $item['name'] : (is_array($item) ? ($item['value'] ?? '') : (string) $item);
            $label = trim($label);
            if ($label !== '') {
                $result[] = ['label' => $label];
            }
        }
        return $result;
    }

    /** Normalize excluded items to [{ label, subtext? }]. */
    private function normalizeExcluded(array $items): array
    {
        $result = [];
        foreach ($items as $item) {
            $label = is_array($item) && isset($item['name']) ? $item['name'] : (is_array($item) ? ($item['value'] ?? '') : (string) $item);
            $label = trim($label);
            $subtext = null;
            if (is_array($item)) {
                $subtext = $item['value'] ?? $item['details'] ?? null;
                $subtext = $subtext !== null ? (string) $subtext : null;
            }
            if ($label !== '') {
                $result[] = $subtext !== null && $subtext !== ''
                    ? ['label' => $label, 'subtext' => $subtext]
                    : ['label' => $label];
            }
        }
        return $result;
    }

    private function titleCase(string $value): string
    {
        return \Illuminate\Support\Str::title(str_replace('_', ' ', $value));
    }

    /**
     * @param  list<string|null>  $slugs
     */
    private function formatSkillLevelForDisplay(array $slugs): ?string
    {
        if ($slugs === []) {
            return null;
        }

        $parts = [];
        foreach ($slugs as $slug) {
            if ($slug === null || $slug === '') {
                continue;
            }
            $normalized = strtolower(str_replace([' ', '-'], '_', trim((string) $slug)));
            $parts[] = match ($normalized) {
                'beginner' => __('trips.skill_level_beginner'),
                'intermediate' => __('trips.skill_level_intermediate'),
                'advanced' => __('trips.skill_level_advanced'),
                'all_levels', 'alllevels', 'all' => __('trips.skill_level_all_levels'),
                default => $this->titleCase((string) $slug),
            };
        }

        return $parts === [] ? null : implode(' / ', $parts);
    }

    private function formatFishingStyleForDisplay(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = strtolower(str_replace([' ', '-'], '_', trim($value)));

        return match ($normalized) {
            'active' => __('trips.fishing_style_active'),
            'passive' => __('trips.fishing_style_passive'),
            'both' => __('trips.fishing_style_both'),
            default => $this->titleCase($value),
        };
    }

    private function formatMonthLabel(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Accept "01".."12", "1".."12" (from dropdown), or already-a-label values.
        if (! is_numeric($value)) {
            return (string) $value;
        }

        $month = (int) $value;
        if ($month < 1 || $month > 12) {
            return null;
        }

        // Uses current app locale (Carbon locale is set by Laravel).
        return Carbon::createFromDate(null, $month, 1)->translatedFormat('F');
    }

    private function hasAnyValue(array $data, array $keys): bool
    {
        foreach ($keys as $key) {
            $v = $data[$key] ?? null;
            if (is_array($v) ? ! empty($v) : (is_string($v) ? $v !== '' : $v !== null)) {
                return true;
            }
        }
        return false;
    }
}
