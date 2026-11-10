<?php

namespace App\Services\Voucher;

use App\Domain\Offers\DestinationOfferScope;
use App\Models\CategoryEntity;
use App\Models\Guiding;
use App\Models\Review;
use App\Models\User;
use App\Repositories\Guiding\GuidingCategoryAvailabilityRepository;
use App\Services\Offers\OfferCatalogPageService;
use Collator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Intl\Countries;
use Throwable;

/**
 * Live inventory behind the /guidings/voucher landing page: the country → region → city options
 * of the request form (only places with publicly visible tours, scoped by geo like the
 * /guidings/{country}/{region} pages), the region tiles with a real "from" price, the photo
 * gallery and a few top reviews. Cached for an hour; the page itself renders per request.
 *
 * Geo entities only store German names, so countries are named from their ISO code in the page
 * language; region and city names stay as stored.
 */
class VoucherLandingCatalog
{
    private const CACHE_MINUTES = 60;

    private const TILE_LIMIT = 6;

    /** Tours read per tile for its "from" price and species tag. */
    private const TILE_TOUR_SAMPLE = 60;

    private const GALLERY_LIMIT = 12;

    private const REVIEW_LIMIT = 3;

    /** grandtotal_score is out of 10. */
    private const REVIEW_MIN_SCORE = 9.5;

    private const REVIEW_MIN_LENGTH = 60;

    /** Reviews read before dropping test/staff/self reviews down to REVIEW_LIMIT. */
    private const REVIEW_CANDIDATES = 20;

    public function __construct(
        private readonly OfferCatalogPageService $offerCatalog,
        private readonly GuidingCategoryAvailabilityRepository $guidingAvailability,
    ) {}

    /**
     * @return list<array{name: string, regions: list<array{name: string, cities: list<string>}>}>
     */
    public function destinations(): array
    {
        $countries = array_map(fn (array $country) => [
            'name' => $this->countryName($country),
            'regions' => array_map(fn (array $region) => [
                'name' => $region['name'],
                'cities' => $region['cities'],
            ], $country['regions']),
        ], $this->tree());

        $collator = class_exists(Collator::class) ? new Collator(app()->getLocale()) : null;
        usort($countries, fn (array $a, array $b) => $collator?->compare($a['name'], $b['name']) ?? strcmp($a['name'], $b['name']));

        return $countries;
    }

    /** Whether country/region/city name a place offered in the form ('' region/city = left open). */
    public function offers(string $country, string $region = '', string $city = ''): bool
    {
        foreach ($this->destinations() as $option) {
            if ($option['name'] !== $country) {
                continue;
            }
            if ($region === '') {
                return $city === '';
            }
            foreach ($option['regions'] as $regionOption) {
                if ($regionOption['name'] === $region) {
                    return $city === '' || in_array($city, $regionOption['cities'], true);
                }
            }
        }

        return false;
    }

    public function hasRegions(string $country): bool
    {
        foreach ($this->destinations() as $option) {
            if ($option['name'] === $country) {
                return $option['regions'] !== [];
            }
        }

        return false;
    }

    /**
     * The regions with the most tours, each with a photo, its lowest tour price and the species
     * most of its tours target.
     *
     * @return list<array{country: string, region: string, image: ?string, from_price: ?int, species: ?string}>
     */
    public function tiles(): array
    {
        $locale = app()->getLocale();

        return Cache::remember("voucher_landing_tiles_v1_{$locale}", now()->addMinutes(self::CACHE_MINUTES), function () {
            // Each country's busiest region first, so the tiles spread across countries.
            $regions = collect($this->tree())
                ->flatMap(fn (array $country) => array_map(fn (array $region) => $region + ['country' => $this->countryName($country), 'country_id' => $country['id']], $country['regions']))
                ->sortByDesc('tours')
                ->groupBy('country_id')
                ->flatMap(fn (Collection $countryRegions) => $countryRegions->values()->map(fn (array $region, int $rank) => $region + ['rank' => $rank]))
                ->sortBy([['rank', 'asc'], ['tours', 'desc']])
                ->take(self::TILE_LIMIT);

            $entities = CategoryEntity::query()->whereIn('id', $regions->pluck('id')->merge($regions->pluck('country_id'))->unique())->get()->keyBy('id');

            return $regions->map(function (array $region) use ($entities) {
                $tours = $this->tours($entities[$region['country_id']], $entities[$region['id']]);
                $prices = $tours->map(fn (Guiding $tour) => $tour->getLowestPrice())->filter(fn (int $price) => $price > 0);
                $regionEntity = $entities[$region['id']];

                return [
                    'country' => $region['country'],
                    'region' => $region['name'],
                    'image' => $regionEntity->thumbnail_path
                        ? media_url($regionEntity->thumbnail_path)
                        : ($tours->map(fn (Guiding $tour) => get_featured_image_link($tour))->filter()->first() ?: null),
                    'from_price' => $prices->isEmpty() ? null : (int) $prices->min(),
                    'species' => $this->topSpecies($tours),
                ];
            })->values()->all();
        });
    }

    /**
     * Backdrop of the closing call to action: the landscape photo of a region with tours that isn't
     * already a tile, picked anew every hour. Null when no region has a photo.
     */
    public function finalImage(): ?string
    {
        return Cache::remember('voucher_landing_final_image_v1', now()->addMinutes(self::CACHE_MINUTES), function () {
            $tileRegions = array_column($this->tiles(), 'region');
            $regionIds = collect($this->tree())
                ->flatMap(fn (array $country) => $country['regions'])
                ->reject(fn (array $region) => in_array($region['name'], $tileRegions, true))
                ->pluck('id');

            $path = CategoryEntity::query()
                ->whereIn('id', $regionIds)
                ->whereNotNull('thumbnail_path')
                ->inRandomOrder()
                ->value('thumbnail_path');

            return $path ? media_url($path) : null;
        });
    }

    /**
     * Featured photos of the newest tours, one per tour.
     *
     * @return list<array{url: string, alt: string}>
     */
    public function gallery(): array
    {
        return Cache::remember('voucher_landing_gallery_v2', now()->addMinutes(self::CACHE_MINUTES), function () {
            return Guiding::query()
                ->publiclyVisible()
                ->latest()
                ->limit(self::GALLERY_LIMIT * 2)
                ->get()
                ->map(fn (Guiding $tour) => ['url' => get_featured_image_link($tour), 'alt' => (string) $tour->title])
                ->filter(fn (array $image) => filled($image['url']))
                ->unique('url')
                ->take(self::GALLERY_LIMIT)
                ->values()
                ->all();
        });
    }

    /**
     * Recent top-rated tour reviews by real customers, in the page language. Reviews from test
     * or staff accounts, and guides reviewing their own tour, are left out.
     *
     * @return list<array{text: string, name: string, tour: string}>
     */
    public function reviews(): array
    {
        $locale = app()->getLocale();

        return Cache::remember("voucher_landing_reviews_v2_{$locale}", now()->addMinutes(self::CACHE_MINUTES), function () {
            return Review::query()
                ->with([
                    'guiding:id,title,user_id',
                    'booking:id,user_id,is_guest',
                    'booking.guestUser:id,firstname,lastname,email',
                    'booking.registeredUser:id,firstname,lastname,email',
                ])
                ->where('is_automatic', false)
                ->where('grandtotal_score', '>=', self::REVIEW_MIN_SCORE)
                ->whereRaw('CHAR_LENGTH(comment) >= ?', [self::REVIEW_MIN_LENGTH])
                ->newestFirst()
                ->limit(self::REVIEW_CANDIDATES)
                ->get()
                ->map(fn (Review $review) => ['review' => $review, 'reviewer' => $this->reviewer($review)])
                ->filter(fn (array $row) => $this->isCustomerReview($row['review'], $row['reviewer']))
                ->take(self::REVIEW_LIMIT)
                ->map(fn (array $row) => [
                    'text' => (string) translate($row['review']->comment),
                    'name' => trim((string) $row['reviewer']->firstname),
                    'tour' => $row['review']->guiding ? (string) translate($row['review']->guiding->title) : '',
                ])
                ->values()
                ->all();
        });
    }

    /**
     * The customer who wrote the review. A guest booking's user_id points at user_guests, so the
     * name comes from the booking; Review::user() would look a guest's id up in users when
     * eager-loaded and show an unrelated account's name.
     */
    private function reviewer(Review $review): ?Model
    {
        $booking = $review->booking;

        if ($booking !== null) {
            return $booking->is_guest ? $booking->guestUser : $booking->registeredUser;
        }

        return $review->user_id ? User::query()->find($review->user_id, ['id', 'firstname', 'lastname', 'email']) : null;
    }

    private function isCustomerReview(Review $review, ?Model $reviewer): bool
    {
        if ($reviewer === null || trim((string) $reviewer->firstname) === '') {
            return false;
        }

        $identity = $reviewer->firstname.' '.$reviewer->lastname.' '.$reviewer->email;
        if (preg_match('/\btest|catch\s*a\s*guide|@catchaguide\./i', $identity)) {
            return false;
        }

        $isGuest = (bool) $review->booking?->is_guest;

        return $isGuest || $review->guiding === null || (int) $review->guiding->user_id !== (int) $reviewer->getKey();
    }

    /**
     * Countries with tours, their regions with at least one tour (with the count) and those
     * regions' cities with at least one tour.
     *
     * @return list<array{id: int, name: string, code: string, regions: list<array{id: int, name: string, tours: int, cities: list<string>}>}>
     */
    private function tree(): array
    {
        return Cache::remember('voucher_landing_destinations_v1', now()->addMinutes(self::CACHE_MINUTES), function () {
            $countries = CategoryEntity::countries()->orderBy('name')->get()
                ->filter(fn (CategoryEntity $country) => $this->guidingAvailability->hasGuidingsForCountry($country->slug, $country->countrycode))
                ->values();

            $regions = CategoryEntity::regions()->whereIn('country_id', $countries->pluck('id'))->orderBy('name')->get()->groupBy('country_id');
            $cities = CategoryEntity::cities()->whereIn('country_id', $countries->pluck('id'))->orderBy('name')->get()->groupBy('region_id');

            return $countries->map(fn (CategoryEntity $country) => [
                'id' => $country->id,
                'name' => (string) $country->name,
                'code' => strtoupper((string) $country->countrycode),
                'regions' => ($regions[$country->id] ?? collect())
                    ->map(fn (CategoryEntity $region) => [
                        'id' => $region->id,
                        'name' => (string) $region->name,
                        'tours' => $this->offerCatalog->countToursForDestination($country, $region),
                        'cities' => ($cities[$region->id] ?? collect())
                            ->filter(fn (CategoryEntity $city) => $this->offerCatalog->countToursForDestination($country, $region, $city) > 0)
                            ->map(fn (CategoryEntity $city) => (string) $city->name)
                            ->values()
                            ->all(),
                    ])
                    ->filter(fn (array $region) => $region['tours'] > 0)
                    ->values()
                    ->all(),
            ])->all();
        });
    }

    /** @param  array{name: string, code: string}  $country */
    private function countryName(array $country): string
    {
        try {
            return $country['code'] !== '' ? Countries::getName($country['code'], app()->getLocale()) : $country['name'];
        } catch (Throwable) {
            return $country['name'];
        }
    }

    /** @return Collection<int, Guiding> */
    private function tours(CategoryEntity $country, CategoryEntity $region): Collection
    {
        $input = DestinationOfferScope::mergeIntoRequest(['type' => 'tour'], $country, $region);
        $query = $this->offerCatalog->listingQueries($input)['tour'] ?? null;

        return $query ? $query->limit(self::TILE_TOUR_SAMPLE)->get() : collect();
    }

    /** @param  Collection<int, Guiding>  $tours */
    private function topSpecies(Collection $tours): ?string
    {
        $name = $tours
            ->flatMap(fn (Guiding $tour) => array_column($tour->getTargetFishNames(), 'name'))
            ->filter()
            ->countBy()
            ->sortDesc()
            ->keys()
            ->first();

        return $name !== null ? (string) $name : null;
    }
}
