<?php

namespace App\Services\Admin;

use App\Models\AccommodationDetail;
use App\Models\AccommodationExtra;
use App\Models\AccommodationInclusive;
use App\Models\AccommodationPolicy;
use App\Models\AccommodationRentalCondition;
use App\Models\AccommodationType;
use App\Models\BathroomAmenity;
use App\Models\BoatExtras;
use App\Models\CampFacility;
use App\Models\ExtrasPrice;
use App\Models\Facility;
use App\Models\FishingEquipment;
use App\Models\FishingFrom;
use App\Models\FishingType;
use App\Models\GuidingAdditionalInformation;
use App\Models\GuidingBoatDescription;
use App\Models\GuidingBoatType;
use App\Models\GuidingRecommendations;
use App\Models\GuidingRequirements;
use App\Models\Inclussion;
use App\Models\KitchenEquipment;
use App\Models\Levels;
use App\Models\Method;
use App\Models\RentalBoatRequirement;
use App\Models\RoomConfiguration;
use App\Models\Target;
use App\Models\Water;
use App\Services\Accommodation\AccommodationCacheService;
use App\Services\Camp\CampCacheService;
use App\Services\RentalBoat\RentalBoatCacheService;
use App\Services\SpecialOffer\SpecialOfferCacheService;
use App\Services\Trip\TripCacheService;
use InvalidArgumentException;

class ListingAttributeRegistry
{
    public const GROUP_GUIDING = 'guiding';
    public const GROUP_ACCOMMODATION = 'accommodation';
    public const GROUP_CAMP = 'camp';
    public const GROUP_RENTAL_BOAT = 'rental_boat';

    public const CACHE_ACCOMMODATION = 'accommodation';
    public const CACHE_RENTAL_BOAT = 'rental_boat';
    public const CACHE_CAMP = 'camp';
    public const CACHE_TRIP = 'trip';
    public const CACHE_SPECIAL_OFFER = 'special_offer';

    /**
     * @return array<string, array{
     *     model: class-string,
     *     group: string,
     *     label_key: string,
     *     fields: array<int, string>,
     *     caches: array<int, string>,
     *     legacy_slugs?: array<int, string>
     * }>
     */
    public static function all(): array
    {
        return [
            // Guiding
            'levels' => [
                'model' => Levels::class,
                'group' => self::GROUP_GUIDING,
                'label_key' => 'levels',
                'fields' => ['name', 'name_en'],
                'caches' => [],
                'legacy_slugs' => ['levels'],
            ],
            'fishing-types' => [
                'model' => FishingType::class,
                'group' => self::GROUP_GUIDING,
                'label_key' => 'fishing_types',
                'fields' => ['name', 'name_en'],
                'caches' => [],
                'legacy_slugs' => ['fishingtype'],
            ],
            'fishing-equipment' => [
                'model' => FishingEquipment::class,
                'group' => self::GROUP_GUIDING,
                'label_key' => 'fishing_equipment',
                'fields' => ['name', 'name_en'],
                'caches' => [],
                'legacy_slugs' => ['equipment'],
            ],
            'fishing-from' => [
                'model' => FishingFrom::class,
                'group' => self::GROUP_GUIDING,
                'label_key' => 'fishing_from',
                'fields' => ['name', 'name_en'],
                'caches' => [],
                'legacy_slugs' => ['fishingfrom'],
            ],
            'inclussions' => [
                'model' => Inclussion::class,
                'group' => self::GROUP_GUIDING,
                'label_key' => 'inclussions',
                'fields' => ['name', 'name_en'],
                'caches' => [self::CACHE_RENTAL_BOAT],
                'legacy_slugs' => ['inclussions'],
            ],
            'methods' => [
                'model' => Method::class,
                'group' => self::GROUP_GUIDING,
                'label_key' => 'methods',
                'fields' => ['name', 'name_en'],
                'caches' => [self::CACHE_CAMP, self::CACHE_TRIP, self::CACHE_SPECIAL_OFFER],
                'legacy_slugs' => ['methods'],
            ],
            'waters' => [
                'model' => Water::class,
                'group' => self::GROUP_GUIDING,
                'label_key' => 'waters',
                'fields' => ['name', 'name_en'],
                'caches' => [],
                'legacy_slugs' => ['waters'],
            ],
            'targets' => [
                'model' => Target::class,
                'group' => self::GROUP_GUIDING,
                'label_key' => 'targets',
                'fields' => ['name', 'name_en'],
                'caches' => [self::CACHE_CAMP, self::CACHE_TRIP, self::CACHE_SPECIAL_OFFER],
                'legacy_slugs' => ['targets'],
            ],
            'boat-extras' => [
                'model' => BoatExtras::class,
                'group' => self::GROUP_GUIDING,
                'label_key' => 'boat_extras',
                'fields' => ['name', 'name_en'],
                'caches' => [self::CACHE_RENTAL_BOAT],
                'legacy_slugs' => ['boat-extras'],
            ],
            'guiding-boat-types' => [
                'model' => GuidingBoatType::class,
                'group' => self::GROUP_GUIDING,
                'label_key' => 'guiding_boat_types',
                'fields' => ['name', 'name_en'],
                'caches' => [self::CACHE_RENTAL_BOAT, self::CACHE_TRIP],
            ],
            'guiding-boat-descriptions' => [
                'model' => GuidingBoatDescription::class,
                'group' => self::GROUP_GUIDING,
                'label_key' => 'guiding_boat_descriptions',
                'fields' => ['name', 'name_en'],
                'caches' => [self::CACHE_RENTAL_BOAT],
            ],
            'guiding-additional-informations' => [
                'model' => GuidingAdditionalInformation::class,
                'group' => self::GROUP_GUIDING,
                'label_key' => 'guiding_additional_informations',
                'fields' => ['name', 'name_en'],
                'caches' => [],
            ],
            'guiding-recommendations' => [
                'model' => GuidingRecommendations::class,
                'group' => self::GROUP_GUIDING,
                'label_key' => 'guiding_recommendations',
                'fields' => ['name', 'name_en'],
                'caches' => [],
            ],
            'guiding-requirements' => [
                'model' => GuidingRequirements::class,
                'group' => self::GROUP_GUIDING,
                'label_key' => 'guiding_requirements',
                'fields' => ['name', 'name_en'],
                'caches' => [],
            ],
            'extras-prices' => [
                'model' => ExtrasPrice::class,
                'group' => self::GROUP_GUIDING,
                'label_key' => 'extras_prices',
                'fields' => ['name', 'name_en'],
                'caches' => [],
            ],

            // Accommodation
            'accommodation-types' => [
                'model' => AccommodationType::class,
                'group' => self::GROUP_ACCOMMODATION,
                'label_key' => 'accommodation_types',
                'fields' => ['name', 'name_en', 'is_active', 'sort_order'],
                'caches' => [self::CACHE_ACCOMMODATION],
            ],
            'facilities' => [
                'model' => Facility::class,
                'group' => self::GROUP_ACCOMMODATION,
                'label_key' => 'facilities',
                'fields' => ['name', 'name_en', 'is_active', 'sort_order'],
                'caches' => [self::CACHE_ACCOMMODATION],
                'legacy_slugs' => ['facilities'],
            ],
            'kitchen-equipment' => [
                'model' => KitchenEquipment::class,
                'group' => self::GROUP_ACCOMMODATION,
                'label_key' => 'kitchen_equipment',
                'fields' => ['name', 'name_en', 'is_active', 'sort_order'],
                'caches' => [self::CACHE_ACCOMMODATION],
                'legacy_slugs' => ['kitchen-equipment'],
            ],
            'bathroom-amenities' => [
                'model' => BathroomAmenity::class,
                'group' => self::GROUP_ACCOMMODATION,
                'label_key' => 'bathroom_amenities',
                'fields' => ['name', 'name_en', 'is_active', 'sort_order'],
                'caches' => [self::CACHE_ACCOMMODATION],
            ],
            'room-configurations' => [
                'model' => RoomConfiguration::class,
                'group' => self::GROUP_ACCOMMODATION,
                'label_key' => 'room_configurations',
                'fields' => ['name', 'name_en', 'is_active', 'sort_order'],
                'caches' => [self::CACHE_ACCOMMODATION],
            ],
            'accommodation-details' => [
                'model' => AccommodationDetail::class,
                'group' => self::GROUP_ACCOMMODATION,
                'label_key' => 'accommodation_details',
                'fields' => ['name', 'name_en', 'input_type', 'placeholder', 'is_active', 'sort_order'],
                'caches' => [self::CACHE_ACCOMMODATION],
            ],
            'accommodation-extras' => [
                'model' => AccommodationExtra::class,
                'group' => self::GROUP_ACCOMMODATION,
                'label_key' => 'accommodation_extras',
                'fields' => ['name', 'name_en', 'is_active', 'sort_order'],
                'caches' => [self::CACHE_ACCOMMODATION],
            ],
            'accommodation-inclusives' => [
                'model' => AccommodationInclusive::class,
                'group' => self::GROUP_ACCOMMODATION,
                'label_key' => 'accommodation_inclusives',
                'fields' => ['name', 'name_en', 'is_active', 'sort_order'],
                'caches' => [self::CACHE_ACCOMMODATION],
            ],
            'accommodation-policies' => [
                'model' => AccommodationPolicy::class,
                'group' => self::GROUP_ACCOMMODATION,
                'label_key' => 'accommodation_policies',
                'fields' => ['name', 'name_en', 'is_active', 'sort_order'],
                'caches' => [self::CACHE_ACCOMMODATION],
            ],
            'accommodation-rental-conditions' => [
                'model' => AccommodationRentalCondition::class,
                'group' => self::GROUP_ACCOMMODATION,
                'label_key' => 'accommodation_rental_conditions',
                'fields' => ['name', 'name_en', 'input_type', 'placeholder', 'is_active', 'sort_order'],
                'caches' => [self::CACHE_ACCOMMODATION],
            ],

            // Camp
            'camp-facilities' => [
                'model' => CampFacility::class,
                'group' => self::GROUP_CAMP,
                'label_key' => 'camp_facilities',
                'fields' => ['name', 'name_de', 'name_en', 'is_active'],
                'caches' => [self::CACHE_CAMP],
            ],

            // Rental boat
            'rental-boat-requirements' => [
                'model' => RentalBoatRequirement::class,
                'group' => self::GROUP_RENTAL_BOAT,
                'label_key' => 'rental_boat_requirements',
                'fields' => ['name', 'name_en', 'input_type', 'placeholder', 'placeholder_en', 'is_active', 'sort_order'],
                'caches' => [self::CACHE_RENTAL_BOAT],
            ],
        ];
    }

    /**
     * @return array<string, array{model: class-string, group: string, label_key: string, fields: array<int, string>, caches: array<int, string>}>
     */
    public static function forGroup(string $group): array
    {
        return array_filter(
            self::all(),
            static fn (array $config): bool => $config['group'] === $group
        );
    }

    /**
     * @return list<string>
     */
    public static function groups(): array
    {
        return [
            self::GROUP_GUIDING,
            self::GROUP_ACCOMMODATION,
            self::GROUP_CAMP,
            self::GROUP_RENTAL_BOAT,
        ];
    }

    /**
     * @return list<string>
     */
    public static function slugs(): array
    {
        return array_keys(self::all());
    }

    /**
     * @return array{model: class-string, group: string, label_key: string, fields: array<int, string>, caches: array<int, string>, legacy_slugs?: array<int, string>}
     */
    public static function get(string $type): array
    {
        $all = self::all();

        if (! isset($all[$type])) {
            throw new InvalidArgumentException("Unknown listing attribute type [{$type}].");
        }

        return $all[$type];
    }

    public static function has(string $type): bool
    {
        return isset(self::all()[$type]);
    }

    public static function hasField(string $type, string $field): bool
    {
        return in_array($field, self::get($type)['fields'], true);
    }

    /**
     * Map a legacy settings path slug (e.g. fishingtype) to the new registry key.
     */
    public static function resolveLegacySlug(string $legacySlug): ?string
    {
        foreach (self::all() as $slug => $config) {
            if ($slug === $legacySlug) {
                return $slug;
            }

            if (in_array($legacySlug, $config['legacy_slugs'] ?? [], true)) {
                return $slug;
            }
        }

        return null;
    }

    public static function flushCaches(string $type): void
    {
        foreach (self::get($type)['caches'] as $cache) {
            match ($cache) {
                self::CACHE_ACCOMMODATION => app(AccommodationCacheService::class)->clearFormDataCache(),
                self::CACHE_RENTAL_BOAT => app(RentalBoatCacheService::class)->clearFormDataCache(),
                self::CACHE_CAMP => app(CampCacheService::class)->clearFormDataCache(),
                self::CACHE_TRIP => app(TripCacheService::class)->clearFormDataCache(),
                self::CACHE_SPECIAL_OFFER => app(SpecialOfferCacheService::class)->clearFormDataCache(),
                default => null,
            };
        }
    }
}
