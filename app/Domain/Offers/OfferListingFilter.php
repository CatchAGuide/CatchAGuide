<?php

namespace App\Domain\Offers;

use App\Domain\Vacation\CountrySlug;
use App\Domain\Vacation\VacationListingFilter;

final class OfferListingFilter
{
    public const TYPES = ['all', 'tour', 'vacation'];

    public const VACATION_FILTERS = ['all', 'trip', 'camp'];

    public const TOUR_DURATION_TYPES = ['half_day', 'full_day', 'multi_day'];

    public const TRIP_DURATION_BUCKETS = VacationListingFilter::TRIP_DURATION_BUCKETS;

    public const SORT_OPTIONS = [
        'recommended',
        'newest',
        'nearest',
        'price-asc',
        'price-desc',
    ];

    public const MAX_GUESTS = 20;

    public const DEFAULT_GUESTS = 1;

    /**
     * Product-type facets that must not leak across type/vacation switches.
     *
     * @var list<string>
     */
    public const PRODUCT_FACET_KEYS = [
        'methods',
        'water',
        'duration_types',
        'duration',
        'accommodation_type',
        'has_guiding',
        'has_rental_boat',
    ];

    /**
     * Geo fields owned by the header Where input. Clearing that input must
     * drop these so leftover coords cannot keep a location search alive.
     *
     * @var list<string>
     */
    public const LOCATION_SEARCH_QUERY_KEYS = [
        'place',
        'placeLat',
        'placeLng',
        'place_lat',
        'place_lng',
        'placelat',
        'placelng',
        'city',
        'country',
        'region',
        'country_short',
        'bounds_ne_lat',
        'bounds_ne_lng',
        'bounds_sw_lat',
        'bounds_sw_lng',
        'place_types',
    ];

    /**
     * Fields the header Where/Who search owns. Submitting that form must not
     * drop sidebar filters, but these keys are re-emitted by the header itself.
     *
     * @var list<string>
     */
    public const HEADER_OWNED_QUERY_KEYS = [
        ...self::LOCATION_SEARCH_QUERY_KEYS,
        'num_guests',
        'num_persons',
        'page',
    ];

    /**
     * @param  list<int>  $speciesIds
     * @param  list<string>  $speciesNames
     * @param  array<int, string>  $placeTypes
     * @param  list<int>  $methodIds
     * @param  list<int>  $waterIds
     * @param  list<string>  $durationTypes
     */
    public function __construct(
        public readonly string $type = 'all',
        public readonly string $vacation = 'all',
        public readonly array $speciesIds = [],
        public readonly array $speciesNames = [],
        public readonly ?string $country = null,
        public readonly ?string $sortBy = null,
        public readonly ?string $place = null,
        public readonly ?float $placeLat = null,
        public readonly ?float $placeLng = null,
        public readonly ?string $city = null,
        public readonly ?string $region = null,
        public readonly ?int $numGuests = null,
        public readonly array $placeTypes = [],
        public readonly ?float $boundsNeLat = null,
        public readonly ?float $boundsNeLng = null,
        public readonly ?float $boundsSwLat = null,
        public readonly ?float $boundsSwLng = null,
        public readonly ?string $countryShort = null,
        public readonly array $methodIds = [],
        public readonly array $waterIds = [],
        public readonly array $durationTypes = [],
        public readonly ?string $tripDuration = null,
        public readonly ?int $accommodationTypeId = null,
        public readonly ?bool $hasGuiding = null,
        public readonly ?bool $hasRentalBoat = null,
        public readonly ?float $userLat = null,
        public readonly ?float $userLng = null,
    ) {}

    public static function fromRequest(array $input): self
    {
        $rawType = strtolower((string) ($input['type'] ?? 'all'));
        $vacation = strtolower((string) ($input['vacation'] ?? 'all'));

        // Legacy URLs: ?type=trip|camp → vacation primary + subfilter.
        if (in_array($rawType, ['trip', 'camp'], true)) {
            $vacation = $rawType;
            $rawType = 'vacation';
        }

        if (! in_array($rawType, self::TYPES, true)) {
            $rawType = 'all';
        }

        if ($rawType !== 'vacation' || ! in_array($vacation, self::VACATION_FILTERS, true)) {
            $vacation = 'all';
        }

        $sortBy = self::nullableString($input['sortby'] ?? null);
        if ($sortBy !== null && ! in_array($sortBy, self::SORT_OPTIONS, true)) {
            $sortBy = null;
        }

        $place = self::nullableString($input['place'] ?? null);
        $lat = self::nullableFloat($input['placeLat'] ?? $input['place_lat'] ?? $input['placelat'] ?? null);
        $lng = self::nullableFloat($input['placeLng'] ?? $input['place_lng'] ?? $input['placelng'] ?? null);
        $userLat = self::nullableFloat($input['user_lat'] ?? $input['userLat'] ?? null);
        $userLng = self::nullableFloat($input['user_lng'] ?? $input['userLng'] ?? null);
        $country = CountrySlug::canonicalize(self::nullableString($input['country'] ?? null));
        $city = self::nullableString($input['city'] ?? null);
        $region = self::nullableString($input['region'] ?? null);
        $countryShort = self::nullableString($input['country_short'] ?? null);
        $placeTypes = self::normalizePlaceTypes($input['place_types'] ?? null);
        $boundsNeLat = self::nullableFloat($input['bounds_ne_lat'] ?? null);
        $boundsNeLng = self::nullableFloat($input['bounds_ne_lng'] ?? null);
        $boundsSwLat = self::nullableFloat($input['bounds_sw_lat'] ?? null);
        $boundsSwLng = self::nullableFloat($input['bounds_sw_lng'] ?? null);

        if ($place === null) {
            $orphanGeo = $lat !== null || $lng !== null;
            $lat = null;
            $lng = null;
            $city = null;
            $region = null;
            $countryShort = null;
            $placeTypes = [];
            $boundsNeLat = null;
            $boundsNeLng = null;
            $boundsSwLat = null;
            $boundsSwLng = null;
            if ($orphanGeo) {
                $country = null;
            }
        }

        $methodIds = [];
        $waterIds = [];
        $durationTypes = [];
        $tripDuration = null;
        $accommodationTypeId = null;
        $hasGuiding = null;
        $hasRentalBoat = null;

        if ($rawType === 'tour') {
            $methodIds = self::normalizePositiveIntIds($input['methods'] ?? null);
            $waterIds = self::normalizePositiveIntIds($input['water'] ?? null);
            $durationTypes = self::normalizeDurationTypes($input['duration_types'] ?? null);
        } elseif ($rawType === 'vacation' && $vacation === 'camp') {
            $accommodationTypeId = self::nullablePositiveInt($input['accommodation_type'] ?? null);
            $hasGuiding = self::nullableBool($input['has_guiding'] ?? null);
            $hasRentalBoat = self::nullableBool($input['has_rental_boat'] ?? null);
        } elseif ($rawType === 'vacation' && $vacation === 'trip') {
            $tripDuration = VacationListingFilter::normalizeTripDuration($input['duration'] ?? null);
        }

        [$speciesIds, $speciesNames] = VacationListingFilter::normalizeSpecies($input['species'] ?? null);

        return new self(
            type: $rawType,
            vacation: $vacation,
            speciesIds: $speciesIds,
            speciesNames: $speciesNames,
            country: $country,
            sortBy: $sortBy,
            place: $place,
            placeLat: $lat,
            placeLng: $lng,
            city: $city,
            region: $region,
            numGuests: self::nullableGuests($input['num_guests'] ?? $input['num_persons'] ?? null)
                ?? self::DEFAULT_GUESTS,
            placeTypes: $placeTypes,
            boundsNeLat: $boundsNeLat,
            boundsNeLng: $boundsNeLng,
            boundsSwLat: $boundsSwLat,
            boundsSwLng: $boundsSwLng,
            countryShort: $countryShort,
            methodIds: $methodIds,
            waterIds: $waterIds,
            durationTypes: $durationTypes,
            tripDuration: $tripDuration,
            accommodationTypeId: $accommodationTypeId,
            hasGuiding: $hasGuiding,
            hasRentalBoat: $hasRentalBoat,
            userLat: $userLat,
            userLng: $userLng,
        );
    }

    public function hasSpeciesFilter(): bool
    {
        return $this->speciesIds !== [] || $this->speciesNames !== [];
    }

    /**
     * True when the header Where search owns location (place text + coords).
     */
    public function hasPlaceSearch(): bool
    {
        return $this->place !== null && $this->placeLat !== null && $this->placeLng !== null;
    }

    public function isVacation(): bool
    {
        return $this->type === 'vacation';
    }

    public function effectiveSortBy(): string
    {
        return $this->sortBy ?? 'recommended';
    }

    /**
     * Coordinates used for nearest sort: explicit user location, else place search.
     *
     * @return array{lat: float, lng: float}|null
     */
    public function nearestOrigin(): ?array
    {
        if ($this->userLat !== null && $this->userLng !== null) {
            return ['lat' => $this->userLat, 'lng' => $this->userLng];
        }

        if ($this->placeLat !== null && $this->placeLng !== null) {
            return ['lat' => $this->placeLat, 'lng' => $this->placeLng];
        }

        return null;
    }

    public function showsTours(): bool
    {
        return $this->type === 'all' || $this->type === 'tour';
    }

    public function showsTrips(): bool
    {
        if ($this->type === 'all') {
            return true;
        }

        if ($this->type !== 'vacation') {
            return false;
        }

        return $this->vacation === 'all' || $this->vacation === 'trip';
    }

    public function showsCamps(): bool
    {
        if ($this->type === 'all') {
            return true;
        }

        if ($this->type !== 'vacation') {
            return false;
        }

        return $this->vacation === 'all' || $this->vacation === 'camp';
    }

    public function showsTourFacets(): bool
    {
        return $this->type === 'tour';
    }

    public function showsCampFacets(): bool
    {
        return $this->type === 'vacation' && $this->vacation === 'camp';
    }

    public function showsTripFacets(): bool
    {
        return $this->type === 'vacation' && $this->vacation === 'trip';
    }

    public function toVacationFilter(): VacationListingFilter
    {
        $pillar = match (true) {
            $this->type === 'vacation' && $this->vacation === 'trip' => 'trips',
            $this->type === 'vacation' && $this->vacation === 'camp' => 'camps',
            default => 'all',
        };

        return new VacationListingFilter(
            pillar: $pillar,
            speciesIds: $this->speciesIds,
            speciesNames: $this->speciesNames,
            country: $this->country,
            sortBy: $this->sortBy,
            countryShort: $this->countryShort,
            tripDuration: $this->tripDuration,
            accommodationTypeId: $this->accommodationTypeId,
            hasGuiding: $this->hasGuiding,
            hasRentalBoat: $this->hasRentalBoat,
            numGuests: $this->numGuests,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function geoSearchParams(): array
    {
        return array_filter([
            'city' => $this->city,
            'country' => $this->country,
            'region' => $this->region,
            'placeLat' => $this->placeLat,
            'placeLng' => $this->placeLng,
            'place_types' => $this->placeTypes !== [] ? $this->placeTypes : null,
            'bounds_ne_lat' => $this->boundsNeLat,
            'bounds_ne_lng' => $this->boundsNeLng,
            'bounds_sw_lat' => $this->boundsSwLat,
            'bounds_sw_lng' => $this->boundsSwLng,
            'country_short' => $this->countryShort,
        ], fn ($v) => $v !== null && $v !== '');
    }

    /**
     * Query keys to keep on a tour product URL so the header and booking
     * widget can restore Where / Who from search results.
     *
     * @return array<string, mixed>
     */
    public function productPageQuery(bool $includeGuests = true): array
    {
        $query = [];

        if ($this->place !== null && $this->place !== '') {
            $query['place'] = $this->place;
            $query = array_merge($query, $this->geoSearchParams());
        }

        if ($includeGuests && $this->numGuests !== null) {
            $query['num_guests'] = $this->numGuests;
        }

        return array_filter($query, fn ($v) => $v !== null && $v !== '');
    }

    /**
     * Same as productPageQuery(), but only copies guests when the input actually
     * contained a guest param (avoids inventing num_guests=1 on destination cards).
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public static function productPageQueryFromInput(array $input): array
    {
        $rawGuests = $input['num_guests'] ?? $input['num_persons'] ?? null;
        $includeGuests = $rawGuests !== null && $rawGuests !== '';

        return self::fromRequest($input)->productPageQuery($includeGuests);
    }

    /**
     * Sidebar / chip filters to keep when the header search is submitted.
     *
     * @param  array<string, mixed>  $query
     * @param  array<string, mixed>  $locked
     * @return array<string, mixed>
     */
    public static function headerCarryParams(array $query, array $locked = []): array
    {
        $owned = array_flip(self::HEADER_OWNED_QUERY_KEYS);
        $carry = [];

        foreach (array_merge($query, $locked) as $key => $value) {
            if (isset($owned[$key]) || $value === null || $value === '') {
                continue;
            }

            $carry[$key] = $value;
        }

        return $carry;
    }

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }

    private static function nullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    private static function nullablePositiveInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        $int = (int) $value;

        return $int > 0 ? $int : null;
    }

    private static function nullableBool(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower((string) $value);

        return match ($normalized) {
            '1', 'true', 'yes' => true,
            '0', 'false', 'no' => false,
            default => null,
        };
    }

    private static function nullableGuests(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        $guests = (int) $value;
        if ($guests < 1) {
            return null;
        }

        return min($guests, self::MAX_GUESTS);
    }

    /**
     * @return list<int>
     */
    private static function normalizePositiveIntIds(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        $items = is_array($value) ? $value : [$value];
        $ids = [];

        foreach ($items as $item) {
            if ($item === null || $item === '') {
                continue;
            }

            if (is_numeric($item) && (int) $item > 0) {
                $ids[] = (int) $item;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * @return list<string>
     */
    private static function normalizeDurationTypes(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        $items = is_array($value) ? $value : [$value];
        $types = [];

        foreach ($items as $item) {
            if ($item === null || $item === '') {
                continue;
            }

            $normalized = (string) $item;
            if (in_array($normalized, self::TOUR_DURATION_TYPES, true)) {
                $types[] = $normalized;
            }
        }

        return array_values(array_unique($types));
    }

    /**
     * @return array<int, string>
     */
    private static function normalizePlaceTypes(mixed $placeTypes): array
    {
        if (is_string($placeTypes) && $placeTypes !== '') {
            $decoded = json_decode($placeTypes, true);
            $placeTypes = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($placeTypes)) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn ($type) => is_string($type) ? $type : null,
            $placeTypes
        )));
    }
}
