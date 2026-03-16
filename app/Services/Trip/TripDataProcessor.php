<?php

namespace App\Services\Trip;

use App\Models\Trip;
use App\Models\TripAvailabilityDate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TripDataProcessor
{
    public function processRequestData(Request $request, ?Trip $existingTrip = null): array
    {
        $userId = $request->user_id ?? Auth::id();

        return [
            'user_id'                     => $userId,
            'title'                       => $request->title ?? 'Untitled',
            'location'                    => $request->location ?? '',
            'latitude'                    => $request->latitude ?? null,
            'longitude'                   => $request->longitude ?? null,
            'country'                     => $request->country ?? '',
            'city'                        => $request->city ?? '',
            'region'                      => $request->region ?? '',
            'target_species'              => $this->processTagifyField($request->input('target_species')),
            'fishing_methods'             => $this->processTagifyField($request->input('fishing_methods')),
            'fishing_style'               => $request->input('fishing_style') ?: null,
            'water_types'                 => $this->processTagifyField($request->input('water_types')),
            'skill_level'                 => $this->processTagifyField($request->input('skill_level')),
            'duration_nights'             => $request->input('duration_nights') ?: null,
            'duration_days'               => $request->input('duration_days') ?: null,
            'group_size_min'              => $request->input('group_size_min') ?: null,
            'group_size_max'              => $request->input('group_size_max') ?: null,
            'trip_schedule'               => $this->processTripSchedule($request),
            'meeting_point'               => $request->input('meeting_point') ?: null,
            'best_season_from'            => $this->normalizeMonthOption($request->input('best_season_from')),
            'best_season_to'              => $this->normalizeMonthOption($request->input('best_season_to')),
            'catering'                    => $this->processTagifyField($request->input('catering')),
            'best_arrival_options'        => $request->input('best_arrival_options') ?: null,
            'arrival_day'                 => $request->input('arrival_day') ?: null,
            'boat_type'                   => $request->input('boat_type') ?: null,
            'boat_features'               => $this->processTagifyField($request->input('boat_features')),
            'boat_information'            => $request->input('boat_information') ?: null,
            'accommodation_description'   => $request->input('accommodation_description') ?: null,
            'accommodation_type'          => $request->input('accommodation_type') ?: null,
            'room_types'                  => $this->processTagifyField($request->input('room_types')),
            'distance_to_water'           => $request->input('distance_to_water') ?: null,
            'nearest_airport'             => $request->input('nearest_airport') ?: null,
            'provider_name'               => $request->input('provider_name') ?: null,
            'provider_experience'         => $request->input('provider_experience') ?: null,
            'provider_certifications'     => $request->input('provider_certifications') ?: null,
            'boat_staff'                  => $request->input('boat_staff') ?: null,
            'guide_languages'             => $this->processTagifyField($request->input('guide_languages')),
            'description'                 => $request->input('description') ?: null,
            'trip_highlights'             => $this->processTripHighlights($request),
            'included'                    => $this->processTagifyField($request->input('included')),
            'excluded'                    => $this->processTagifyField($request->input('excluded')),
            'additional_info'             => $this->processAdditionalInfo($request),
            'cancellation_policy'         => $request->input('cancellation_policy') ?: null,
            'price_per_person'            => $request->input('price_per_person') ?: null,
            'price_single_room_addition'  => $request->input('price_single_room_addition') ?: null,
            'downpayment_policy'          => $request->input('downpayment_policy') ?: null,
            'currency'                    => $request->input('currency') ?: 'EUR',
        ];
    }

    public function prepareEditFormData(Trip $trip): array
    {
        return [
            'is_update'                    => 1,
            'id'                           => $trip->id,
            'trip_id'                      => $trip->id,
            'user_id'                      => $trip->user_id,
            'title'                        => $trip->title,
            'location'                     => $trip->location,
            'latitude'                     => $trip->latitude,
            'longitude'                    => $trip->longitude,
            'country'                      => $trip->country,
            'city'                         => $trip->city,
            'region'                       => $trip->region,
            'status'                       => $trip->status,
            'thumbnail_path'               => $trip->thumbnail_path,
            'gallery_images'               => $trip->gallery_images,
            'existing_images'              => json_encode($trip->gallery_images ?? []),
            // Use model methods to resolve stored IDs to localized {id, name} objects
            // so Tagify can pre-populate with display names (mirrors Guiding edit form pattern)
            'target_species'               => $trip->getTargetSpeciesNames(),
            'fishing_methods'              => $trip->getFishingMethodNames(),
            'fishing_style'                => $trip->fishing_style,
            'water_types'                  => $trip->getWaterTypeNames(),
            'skill_level'                  => $trip->skill_level,
            'duration_nights'              => $trip->duration_nights,
            'duration_days'                => $trip->duration_days,
            'group_size_min'               => $trip->group_size_min,
            'group_size_max'               => $trip->group_size_max,
            'trip_schedule'                => $trip->trip_schedule ?? [],
            'meeting_point'                => $trip->meeting_point,
            'best_season_from'             => $this->normalizeMonthOption($trip->best_season_from),
            'best_season_to'               => $this->normalizeMonthOption($trip->best_season_to),
            'catering'                     => $trip->catering ?? [],
            'best_arrival_options'         => $trip->best_arrival_options,
            'arrival_day'                  => $trip->arrival_day,
            'boat_type'                    => $trip->boat_type,
            'boat_features'                => $trip->getBoatFeaturesNames(),
            'boat_information'             => $trip->boat_information,
            'accommodation_description'    => $trip->accommodation_description,
            'accommodation_type'           => $trip->accommodation_type,
            'room_types'                   => $trip->room_types ?? [],
            'distance_to_water'            => $trip->distance_to_water,
            'nearest_airport'              => $trip->nearest_airport,
            'provider_name'                => $trip->provider_name,
            'provider_photo'               => $trip->provider_photo,
            'provider_experience'          => $trip->provider_experience,
            'provider_certifications'      => $trip->provider_certifications,
            'boat_staff'                   => $trip->boat_staff,
            'guide_languages'              => $trip->guide_languages ?? [],
            'description'                  => $trip->description,
            'trip_highlights'              => $trip->trip_highlights ?? [],
            'included'                     => $trip->included ?? [],
            'excluded'                     => $trip->excluded ?? [],
            'additional_info'              => $trip->additional_info ?? [],
            'cancellation_policy'          => $trip->cancellation_policy,
            'price_per_person'             => $trip->price_per_person,
            'price_single_room_addition'   => $trip->price_single_room_addition,
            'downpayment_policy'           => $trip->downpayment_policy,
            'currency'                     => $trip->currency ?? 'EUR',
            'availability_dates'           => $trip->availabilityDates()
                                                        ->orderBy('departure_date')
                                                        ->get()
                                                        ->map(function (TripAvailabilityDate $date) {
                                                            return [
                                                                'departure_date'  => optional($date->departure_date)->format('Y-m-d'),
                                                                'spots_available' => $date->spots_available,
                                                            ];
                                                        })
                                                        ->toArray(),
        ];
    }

    public function processAvailabilityDates(Request $request, Trip $trip): void
    {
        $dates = $request->input('availability_dates', []);

        // Support JSON-encoded payload from JS (string) as well as array input
        if (is_string($dates)) {
            $decoded = json_decode($dates, true);
            $dates = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($dates)) {
            $dates = [];
        }
        $trip->availabilityDates()->delete();

        foreach ($dates as $row) {
            if (empty($row['departure_date'])) {
                continue;
            }

            TripAvailabilityDate::create([
                'trip_id'         => $trip->id,
                'departure_date'  => $row['departure_date'],
                'spots_available' => isset($row['spots_available']) ? (int) $row['spots_available'] : 0,
                'status'          => $row['status'] ?? 'available',
            ]);
        }
    }

    /**
     * Process a Tagify field value into a storable array of objects.
     *
     * Each item is normalized to:
     * [
     *   'id'   => <model ID or null>,
     *   'name' => <current display name / custom value>,
     * ]
     *
     * This matches the desired storage format for both DB-backed and free-form Tagify fields.
     */
    /**
     * Normalize best season month to 2-digit string (01-12) for consistent storage and form display.
     */
    private function normalizeMonthOption($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        $num = (int) preg_replace('/\D/', '', (string) $value);
        if ($num < 1 || $num > 12) {
            return null;
        }
        return str_pad((string) $num, 2, '0', STR_PAD_LEFT);
    }

    private function processTagifyField($value): array
    {
        if (empty($value)) {
            return [];
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : array_map('trim', explode(',', $value));
        }

        if (!is_array($value)) {
            return [];
        }

        $items = [];
        foreach ($value as $item) {
            if (is_array($item)) {
                $id = $item['id'] ?? null;
                $name = $item['name'] ?? $item['value'] ?? null;
            } else {
                $id = null;
                $name = (string) $item;
            }

            if ($name === null || $name === '') {
                continue;
            }

            $items[] = [
                'id'   => $id !== '' ? $id : null,
                'name' => $name,
            ];
        }

        return array_values($items);
    }

    private function processTripSchedule(Request $request): array
    {
        $schedule = $request->input('trip_schedule', []);

        // Support JSON-encoded payload from JS (string) as well as array input
        if (is_string($schedule)) {
            $decoded = json_decode($schedule, true);
            $schedule = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($schedule)) {
            return [];
        }

        $normalized = [];

        foreach ($schedule as $row) {
            if (empty($row['day_label']) && empty($row['description'])) {
                continue;
            }

            $normalized[] = [
                'time'        => isset($row['time']) ? trim((string) $row['time']) : null,
                'day_label'   => $row['day_label'] ?? '',
                'description' => $row['description'] ?? '',
            ];
        }

        return $normalized;
    }

    private function processTripHighlights(Request $request): array
    {
        $items = $request->input('trip_highlights_items', []);

        // Support JSON-encoded payload from JS (string) as well as array input
        if (is_string($items)) {
            $decoded = json_decode($items, true);
            $items = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($items)) {
            $items = [];
        }

        $cleanItems = [];
        foreach ($items as $item) {
            $text = is_array($item) ? ($item['text'] ?? '') : (string) $item;
            $text = trim($text);
            if ($text !== '') {
                $cleanItems[] = $text;
            }
        }

        return [
            'items' => $cleanItems,
        ];
    }

    private function processAdditionalInfo(Request $request): array
    {
        $keys = [
            'child_friendly',
            'accessible',
            'smoking_allowed',
            'alcohol_allowed',
            'catch_and_release',
            'catch_success',
            'license_required',
            'clothing_recommendations',
            'experience_level_required',
            'equipment_to_bring',
            'minimum_age',
            'maximum_age',
            'non_fishing_activities',
            'cuisine_food_style',
        ];

        $result = [];

        foreach ($keys as $key) {
            $enabled = (bool) $request->input($key . '_enabled', false);
            $details = $request->input($key . '_details') ?: null;

            $result[$key] = [
                'enabled' => $enabled,
                'details' => $details,
            ];
        }

        return $result;
    }
}

