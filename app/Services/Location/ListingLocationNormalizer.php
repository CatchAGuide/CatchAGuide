<?php

namespace App\Services\Location;

use App\Models\Accommodation;
use App\Models\Camp;
use App\Models\RentalBoat;
use App\Models\SpecialOffer;
use App\Models\Trip;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ListingLocationNormalizer
{
    public const TYPE_CAMP = 'camp';

    public const TYPE_TRIP = 'trip';

    public const TYPE_RENTAL_BOAT = 'rental_boat';

    public const TYPE_SPECIAL_OFFER = 'special_offer';

    public const TYPE_ACCOMMODATION = 'accommodation';

    /** @var array<string, array{model: class-string<Model>, lat: string, lng: string}> */
    public const LISTING_TYPES = [
        self::TYPE_CAMP => [
            'model' => Camp::class,
            'lat' => 'latitude',
            'lng' => 'longitude',
        ],
        self::TYPE_TRIP => [
            'model' => Trip::class,
            'lat' => 'latitude',
            'lng' => 'longitude',
        ],
        self::TYPE_RENTAL_BOAT => [
            'model' => RentalBoat::class,
            'lat' => 'lat',
            'lng' => 'lng',
        ],
        self::TYPE_SPECIAL_OFFER => [
            'model' => SpecialOffer::class,
            'lat' => 'latitude',
            'lng' => 'longitude',
        ],
        self::TYPE_ACCOMMODATION => [
            'model' => Accommodation::class,
            'lat' => 'lat',
            'lng' => 'lng',
        ],
    ];

    private readonly Client $client;

    public function __construct(private readonly CountryResolver $countryResolver)
    {
        $this->client = new Client(['timeout' => 15]);
    }

    /**
     * @return array<string, array{model: class-string<Model>, lat: string, lng: string}>
     */
    public function listingTypes(): array
    {
        return self::LISTING_TYPES;
    }

    public function configFor(string $listingType): array
    {
        if (! isset(self::LISTING_TYPES[$listingType])) {
            throw new \InvalidArgumentException("Unknown listing type [{$listingType}]");
        }

        return self::LISTING_TYPES[$listingType];
    }

    /**
     * @return array{changes: array<string, mixed>, notes: array<int, string>}
     */
    public function analyze(
        Model $listing,
        string $listingType,
        bool $onlyCountry,
        int $sleepSeconds
    ): array {
        $notes = [];
        $config = $this->configFor($listingType);
        $latColumn = $config['lat'];
        $lngColumn = $config['lng'];

        $changes = [];
        $iso = $this->countryResolver->resolveIso(null, $listing->country ?? null);

        if ($iso) {
            $englishCountry = $this->countryResolver->englishName($iso);
            if ($englishCountry && ($listing->country ?? null) !== $englishCountry) {
                $changes['country'] = $englishCountry;
                $notes[] = "Country will change from [{$listing->country}] to [{$englishCountry}] (ISO {$iso}).";
            } else {
                $notes[] = 'Country already normalized to English ('.($listing->country ?? 'empty').').';
            }
        } else {
            $notes[] = 'Country could not be resolved to ISO ('.($listing->country ?? 'empty').').';
        }

        if ($onlyCountry) {
            return ['changes' => $changes, 'notes' => $notes];
        }

        $lat = $listing->{$latColumn} ?? null;
        $lng = $listing->{$lngColumn} ?? null;
        $needsCoords = empty($lat) || empty($lng) || (float) $lat == 0.0 || (float) $lng == 0.0;

        if (! $needsCoords) {
            $notes[] = "Coordinates already set ({$latColumn}={$lat}, {$lngColumn}={$lng}).";

            return ['changes' => $changes, 'notes' => $notes];
        }

        $notes[] = "Coordinates missing ({$latColumn}=".($lat ?? 'null').", {$lngColumn}=".($lng ?? 'null').'); will geocode via Nominatim.';

        if ($sleepSeconds > 0) {
            sleep($sleepSeconds);
        }

        $coords = $this->geocodeListing($listing, $lat, $lng, $geocodeError);
        if (! $coords) {
            $notes[] = 'Geocoding failed: '.($geocodeError ?? 'no result from Nominatim');
            $notes[] = 'Geocode query: '.$this->buildGeocodeQuery($listing);

            return ['changes' => $changes, 'notes' => $notes];
        }

        $notes[] = 'Geocoding succeeded: lat='.$coords['lat'].', lng='.$coords['lng'].'.';

        $changes[$latColumn] = $coords['lat'];
        $changes[$lngColumn] = $coords['lng'];

        if (! empty($coords['city']) && empty($listing->city)) {
            $changes['city'] = $coords['city'];
        }

        if (! empty($coords['region']) && empty($listing->region)) {
            $changes['region'] = $coords['region'];
        }

        if (! empty($coords['country']) && empty($changes['country'])) {
            $resolvedIso = $this->countryResolver->resolveIso(
                $coords['country_short'] ?? null,
                $coords['country']
            );

            if ($resolvedIso) {
                $changes['country'] = $this->countryResolver->englishName($resolvedIso);
            }
        }

        return ['changes' => $changes, 'notes' => $notes];
    }

    /**
     * @return array<string, mixed>
     */
    public function buildChanges(
        Model $listing,
        string $listingType,
        bool $onlyCountry,
        int $sleepSeconds
    ): array {
        return $this->analyze($listing, $listingType, $onlyCountry, $sleepSeconds)['changes'];
    }

    private function buildGeocodeQuery(Model $listing): string
    {
        return implode(', ', array_filter([
            $listing->location ?? null,
            $listing->city ?? null,
            $listing->region ?? null,
            $listing->country ?? null,
        ]));
    }

    /**
     * @return array{lat: float, lng: float, city?: string, region?: string, country?: string, country_short?: string}|null
     */
    private function geocodeListing(Model $listing, mixed $lat, mixed $lng, ?string &$error = null): ?array
    {
        $query = $this->buildGeocodeQuery($listing);

        if ($query === '' && (empty($lat) || empty($lng))) {
            $error = 'no location text or coordinates available';

            return null;
        }

        try {
            if (! empty($lat) && ! empty($lng) && (float) $lat != 0.0) {
                $response = $this->client->get('https://nominatim.openstreetmap.org/reverse', [
                    'query' => [
                        'lat' => $lat,
                        'lon' => $lng,
                        'format' => 'json',
                        'accept-language' => 'en',
                        'addressdetails' => 1,
                    ],
                    'headers' => [
                        'User-Agent' => 'CAG-Listing-App/1.0 (normalize-locations)',
                    ],
                ]);
            } else {
                $response = $this->client->get('https://nominatim.openstreetmap.org/search', [
                    'query' => [
                        'q' => $query,
                        'format' => 'json',
                        'limit' => 1,
                        'accept-language' => 'en',
                        'addressdetails' => 1,
                    ],
                    'headers' => [
                        'User-Agent' => 'CAG-Listing-App/1.0 (normalize-locations)',
                    ],
                ]);
            }

            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            if ($statusCode >= 400) {
                $error = "HTTP {$statusCode} from Nominatim";

                return null;
            }

            if (! $data || $data === []) {
                $error = 'Nominatim returned no matches';

                return null;
            }

            if (isset($data[0])) {
                $data = $data[0];
            }

            $address = $data['address'] ?? [];

            return [
                'lat' => (float) ($data['lat'] ?? 0),
                'lng' => (float) ($data['lon'] ?? 0),
                'city' => $address['city'] ?? $address['town'] ?? $address['village'] ?? null,
                'region' => $address['state'] ?? null,
                'country' => $address['country'] ?? null,
                'country_short' => $address['country_code'] ?? null,
            ];
        } catch (\Throwable $e) {
            $error = $e->getMessage();
            Log::warning('Nominatim geocode failed in listings:normalize-locations', [
                'listing_type' => $listing::class,
                'listing_id' => $listing->getKey(),
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
