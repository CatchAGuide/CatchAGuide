<?php

namespace App\Models;

use App\Models\BoatExtras;
use App\Models\Method;
use App\Models\Target;
use App\Models\Water;
use App\Services\Trip\TripCacheService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saved(function (Trip $trip) {
            app(TripCacheService::class)->clearTripOfferCacheBySlug($trip->slug);
            if ($trip->wasChanged('slug')) {
                $original = $trip->getOriginal('slug');
                if ($original) {
                    app(TripCacheService::class)->clearTripOfferCacheBySlug($original);
                }
            }
        });
    }

    protected $fillable = [
        'title',
        'slug',
        'location',
        'latitude',
        'longitude',
        'country',
        'city',
        'region',
        'thumbnail_path',
        'gallery_images',
        'target_species',
        'fishing_methods',
        'fishing_style',
        'water_types',
        'skill_level',
        'duration_nights',
        'duration_days',
        'group_size_min',
        'group_size_max',
        'trip_schedule',
        'meeting_point',
        'best_season_from',
        'best_season_to',
        'catering',
        'best_arrival_options',
        'arrival_day',
        'boat_type',
        'boat_features',
        'boat_information',
        'accommodation_description',
        'accommodation_type',
        'room_types',
        'distance_to_water',
        'nearest_airport',
        'provider_name',
        'provider_photo',
        'provider_experience',
        'provider_certifications',
        'boat_staff',
        'guide_languages',
        'description',
        'trip_highlights',
        'included',
        'excluded',
        'additional_info',
        'cancellation_policy',
        'price_per_person',
        'price_single_room_addition',
        'downpayment_policy',
        'currency',
        'status',
        'user_id',
    ];

    protected $casts = [
        'gallery_images'             => 'array',
        'latitude'                   => 'decimal:8',
        'longitude'                  => 'decimal:8',
        'target_species'             => 'array',
        'fishing_methods'            => 'array',
        'water_types'                => 'array',
        'skill_level'                => 'array',
        'trip_schedule'              => 'array',
        'catering'                   => 'array',
        'boat_features'              => 'array',
        'room_types'                 => 'array',
        'guide_languages'            => 'array',
        'trip_highlights'            => 'array',
        'included'                   => 'array',
        'excluded'                   => 'array',
        'additional_info'            => 'array',
        'price_per_person'           => 'decimal:2',
        'price_single_room_addition' => 'decimal:2',
    ];

    protected $attributes = [
        'status' => 'draft',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function availabilityDates(): HasMany
    {
        return $this->hasMany(TripAvailabilityDate::class);
    }

    public function getLowestPrice(): float
    {
        return $this->price_per_person ? (float) $this->price_per_person : 0.0;
    }

    /**
     * Resolve stored target species IDs to localized {id, name} objects.
     * Mirrors Guiding::getTargetFishNames() — stored as array of IDs, resolved via Target model.
     */
    public function getTargetSpeciesNames(): array
    {
        $ids = $this->target_species ?? [];
        if (empty($ids)) {
            return [];
        }

        return collect($ids)->map(function ($item) {
            // Already normalized {id, name} structure
            if (is_array($item) && isset($item['name'])) {
                return [
                    'id'   => $item['id'] ?? null,
                    'name' => (string) $item['name'],
                ];
            }
            if (is_numeric($item)) {
                $target = Target::find($item);
                if ($target && $target->name) {
                    return ['id' => $target->id, 'name' => $target->name];
                }
            }
            return $item ? ['id' => null, 'name' => (string) $item] : null;
        })->filter()->values()->toArray();
    }

    /**
     * Resolve stored fishing method IDs to localized {id, name} objects.
     * Mirrors Guiding::getFishingMethodNames() — stored as array of IDs, resolved via Method model.
     */
    public function getFishingMethodNames(): array
    {
        $ids = $this->fishing_methods ?? [];
        if (empty($ids)) {
            return [];
        }

        return collect($ids)->map(function ($item) {
            if (is_array($item) && isset($item['name'])) {
                return [
                    'id'   => $item['id'] ?? null,
                    'name' => (string) $item['name'],
                ];
            }
            if (is_numeric($item)) {
                $method = Method::find($item);
                if ($method && $method->name) {
                    return ['id' => $method->id, 'name' => $method->name];
                }
            }
            return $item ? ['id' => null, 'name' => (string) $item] : null;
        })->filter()->values()->toArray();
    }

    /**
     * Resolve stored water type IDs to localized {id, name} objects.
     * Mirrors Guiding::getWaterNames() — stored as array of IDs, resolved via Water model.
     */
    public function getWaterTypeNames(): array
    {
        $ids = $this->water_types ?? [];
        if (empty($ids)) {
            return [];
        }

        return collect($ids)->map(function ($item) {
            if (is_array($item) && isset($item['name'])) {
                return [
                    'id'   => $item['id'] ?? null,
                    'name' => (string) $item['name'],
                ];
            }
            if (is_numeric($item)) {
                $water = Water::find($item);
                if ($water && $water->name) {
                    return ['id' => $water->id, 'name' => $water->name];
                }
            }
            return $item ? ['id' => null, 'name' => (string) $item] : null;
        })->filter()->values()->toArray();
    }

    /**
     * Resolve stored boat feature IDs to localized {id, name} objects.
     * Mirrors Guiding::getBoatExtras() — stored as array of IDs, resolved via BoatExtras model.
     */
    public function getBoatFeaturesNames(): array
    {
        $ids = $this->boat_features ?? [];
        if (empty($ids)) {
            return [];
        }

        return collect($ids)->map(function ($item) {
            if (is_array($item) && isset($item['name'])) {
                return [
                    'id'   => $item['id'] ?? null,
                    'name' => (string) $item['name'],
                ];
            }
            if (is_numeric($item)) {
                $extra = BoatExtras::find($item);
                if ($extra && $extra->name) {
                    return ['id' => $extra->id, 'name' => $extra->name];
                }
            }
            return $item ? ['id' => null, 'name' => (string) $item] : null;
        })->filter()->values()->toArray();
    }
}

