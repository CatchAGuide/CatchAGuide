<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\AccommodationDetail;
use App\Models\AccommodationPolicy;
use App\Models\RoomConfiguration;

class Accommodation extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'user_id',
        'title',
        'slug',
        'thumbnail_path',
        'gallery_images',
        'location',
        'city',
        'country',
        'region',
        'lat',
        'lng',
        'description',
        'accommodation_type',
        'condition_or_style',
        'living_area_sqm',
        'floor_layout',
        'max_occupancy',
        'number_of_bedrooms',
        'kitchen_type',
        'bathroom',
        'location_description',
        'distance_to_water_m',
        'distance_to_boat_berth_m',
        'distance_to_shop_km',
        'distance_to_parking_m',
        'distance_to_nearest_town_km',
        'distance_to_airport_km',
        'distance_to_ferry_port_km',
        'changeover_day',
        'minimum_stay_nights',
        'price_type',
        'price_per_night',
        'price_per_week',
        'currency',
        'amenities',
        'kitchen_equipment',
        'bathroom_amenities',
        'accommodation_details',
        'room_configurations',
        'policies',
        'rental_conditions',
        'bed_types',
        'per_person_pricing',
        'extras',
        'inclusives',
    ];

    protected $casts = [
        'gallery_images' => 'array',
        'amenities' => 'array',
        'kitchen_equipment' => 'array',
        'bathroom_amenities' => 'array',
        'accommodation_details' => 'array',
        'room_configurations' => 'array',
        'policies' => 'array',
        'rental_conditions' => 'array',
        'bed_types' => 'array',
        'per_person_pricing' => 'array',
        'extras' => 'array',
        'inclusives' => 'array',
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',
    ];

    protected $attributes = [
        'status' => 'active',
        'currency' => 'EUR',
    ];

    public function getNameAttribute()
    {
        return app()->getLocale() == 'en' ? $this->attributes['name_en'] : $this->attributes['name'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function accommodationType(): BelongsTo
    {
        return $this->belongsTo(AccommodationType::class, 'accommodation_type');
    }

    /**
     * Accessor for policies that enriches stored ID/value pairs
     * with their master data and translated names.
     *
     * @param  mixed  $value
     * @return array<int, array<string, mixed>>
     */
    public function getPoliciesAttribute($value): array
    {
        if (is_null($value)) {
            return [];
        }

        $policies = is_string($value) ? json_decode($value, true) : $value;

        if (!is_array($policies) || empty($policies)) {
            return [];
        }

        $policyIds = collect($policies)
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $definitions = AccommodationPolicy::whereIn('id', $policyIds)
            ->get()
            ->keyBy('id');

        return collect($policies)
            ->filter(fn ($item) => is_array($item) && !empty($item['id']))
            ->map(function ($item) use ($definitions) {
                $id = (int) $item['id'];
                $definition = $definitions->get($id);

                return [
                    'id' => $id,
                    'value' => $item['value'] ?? null,
                    'name' => $definition?->name,
                    'name_en' => $definition?->name_en,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Accessor for accommodation details that enriches stored ID/value pairs
     * with their master data and translated names.
     *
     * @param  mixed  $value
     * @return array<int, array<string, mixed>>
     */
    public function getAccommodationDetailsAttribute($value): array
    {
        if (is_null($value)) {
            return [];
        }

        $details = is_string($value) ? json_decode($value, true) : $value;

        if (!is_array($details) || empty($details)) {
            return [];
        }

        $detailIds = collect($details)
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $definitions = AccommodationDetail::whereIn('id', $detailIds)
            ->get()
            ->keyBy('id');

        return collect($details)
            ->filter(fn ($item) => is_array($item) && !empty($item['id']))
            ->map(function ($item) use ($definitions) {
                $id = (int) $item['id'];
                $definition = $definitions->get($id);

                return [
                    'id' => $id,
                    'value' => $item['value'] ?? null,
                    'name' => $definition?->name,
                    'name_en' => $definition?->name_en,
                    'input_type' => $definition?->input_type,
                    'placeholder' => $definition?->placeholder,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Mutator for accommodation details to ensure consistent storage format.
     *
     * @param  mixed  $value
     * @return void
     */
    public function setAccommodationDetailsAttribute($value): void
    {
        if (is_null($value)) {
            $this->attributes['accommodation_details'] = null;
            return;
        }

        if (is_string($value)) {
            $this->attributes['accommodation_details'] = $value;
            return;
        }

        if (!is_array($value)) {
            $this->attributes['accommodation_details'] = json_encode([]);
            return;
        }

        $normalized = collect($value)
            ->filter(fn ($item) => is_array($item) && !empty($item['id']))
            ->map(function ($item) {
                return [
                    'id' => (int) ($item['id'] ?? 0),
                    'value' => $item['value'] ?? null,
                ];
            })
            ->values()
            ->all();

        $this->attributes['accommodation_details'] = json_encode($normalized);
    }

    /**
     * Mutator for policies to ensure consistent storage format.
     *
     * @param  mixed  $value
     * @return void
     */
    public function setPoliciesAttribute($value): void
    {
        if (is_null($value)) {
            $this->attributes['policies'] = null;
            return;
        }

        if (is_string($value)) {
            $this->attributes['policies'] = $value;
            return;
        }

        if (!is_array($value)) {
            $this->attributes['policies'] = json_encode([]);
            return;
        }

        $normalized = collect($value)
            ->filter(fn ($item) => is_array($item) && !empty($item['id']))
            ->map(function ($item) {
                return [
                    'id' => (int) ($item['id'] ?? 0),
                    'value' => $item['value'] ?? null,
                ];
            })
            ->values()
            ->all();

        $this->attributes['policies'] = json_encode($normalized);
    }

    /**
     * Accessor for room configurations with translated names.
     *
     * @param  mixed  $value
     * @return array<int, array<string, mixed>>
     */
    public function getRoomConfigurationsAttribute($value): array
    {
        if (is_null($value)) {
            return [];
        }

        $configurations = is_string($value) ? json_decode($value, true) : $value;

        if (!is_array($configurations) || empty($configurations)) {
            return [];
        }

        $configIds = collect($configurations)
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $definitions = RoomConfiguration::whereIn('id', $configIds)
            ->get()
            ->keyBy('id');

        return collect($configurations)
            ->filter(fn ($item) => is_array($item) && !empty($item['id']))
            ->map(function ($item) use ($definitions) {
                $id = (int) $item['id'];
                $definition = $definitions->get($id);

                return [
                    'id' => $id,
                    'value' => $item['value'] ?? null,
                    'name' => $definition?->name,
                    'name_en' => $definition?->name_en,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Mutator for room configurations to ensure consistent storage format.
     *
     * @param  mixed  $value
     * @return void
     */
    public function setRoomConfigurationsAttribute($value): void
    {
        if (is_null($value)) {
            $this->attributes['room_configurations'] = null;
            return;
        }

        if (is_string($value)) {
            $this->attributes['room_configurations'] = $value;
            return;
        }

        if (!is_array($value)) {
            $this->attributes['room_configurations'] = json_encode([]);
            return;
        }

        $normalized = collect($value)
            ->filter(fn ($item) => is_array($item) && !empty($item['id']))
            ->map(function ($item) {
                return [
                    'id' => (int) ($item['id'] ?? 0),
                    'value' => $item['value'] ?? null,
                ];
            })
            ->values()
            ->all();

        $this->attributes['room_configurations'] = json_encode($normalized);
    }
}
