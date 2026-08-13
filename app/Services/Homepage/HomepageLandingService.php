<?php

namespace App\Services\Homepage;

use App\Domain\Offers\DestinationOfferScope;
use App\Models\Booking;
use App\Models\CategoryPage;
use App\Models\Country;
use App\Models\MonthlyHighlight;
use App\Models\Review;
use App\Models\Target;
use App\Models\Thread;
use App\Models\User;
use App\Models\UserGuest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class HomepageLandingService
{
    /** Marketing floor for catalog size shown on the homepage trust band. */
    public const TRUST_OFFERS_LABEL = '450+';

    public const SEASON_CACHE_PREFIX = 'homepage_season_v7';

    public function __construct(
        private HomepageCountrySelector $countries,
        private HomepageMixedOfferSelector $mixedOffers,
    ) {}

    /**
     * @return array{
     *     featuredCountries: Collection,
     *     countryCount: int,
     *     mixedOffers: Collection,
     *     offerModules: array{tour: Collection, camp: Collection, trip: Collection},
     *     targetSpecies: Collection,
     *     testimonials: Collection,
     *     magazineThreads: Collection,
     *     season: array,
     *     trust: array
     * }
     */
    public function build(): array
    {
        $locale = app()->getLocale();
        $countryCount = $this->countries->totalCount();

        return [
            'featuredCountries' => $this->countries->featured(),
            'countryCount' => $countryCount,
            'mixedOffers' => $this->mixedOffers->mixed(9),
            'offerModules' => $this->mixedOffers->byModule(8),
            'targetSpecies' => $this->targetSpecies($locale),
            'testimonials' => $this->testimonials(),
            'magazineThreads' => $this->magazineThreads($locale),
            'season' => $this->seasonModule($locale),
            'trust' => $this->trustStats($countryCount),
        ];
    }

    private function targetSpecies(string $locale): Collection
    {
        return Cache::remember("homepage_target_species_v5_{$locale}", now()->addMinutes(30), function () use ($locale) {
            $limit = 10;

            $favorites = CategoryPage::query()
                ->where('type', 'Targets')
                ->where('is_favorite', 1)
                ->orderBy('name')
                ->limit($limit)
                ->get();

            $pages = $favorites;

            if ($pages->count() < $limit) {
                $extra = CategoryPage::query()
                    ->where('type', 'Targets')
                    ->whereNotIn('id', $pages->pluck('id'))
                    ->orderBy('name')
                    ->limit($limit - $pages->count())
                    ->get();

                $pages = $pages->concat($extra);
            }

            $targets = Target::query()
                ->whereIn('id', $pages->pluck('source_id')->filter()->unique())
                ->get()
                ->keyBy('id');

            return $pages->map(function (CategoryPage $page) use ($targets) {
                $target = $targets->get($page->source_id);

                return [
                    'name' => $target?->name ?? $page->name,
                    'slug' => $page->slug,
                    'thumbnail' => $page->getThumbnailPath(),
                    'url' => route('targets.show', ['slug' => $page->slug]),
                ];
            });
        });
    }

    /**
     * Latest real guest reviews only: not automatic, grandtotal score > 8.
     */
    private function testimonials(): Collection
    {
        return Cache::remember('homepage_testimonials_v5_'.app()->getLocale(), now()->addMinutes(30), function () {
            $reviews = Review::query()
                ->with([
                    'guiding:id,title,slug',
                    'booking:id,user_id,is_guest,book_date,blocked_event_id',
                    'booking.calendar_schedule:id,date',
                    'booking.blocked_event:id,from',
                    'booking.guestUser:id,firstname',
                    'booking.registeredUser:id,firstname',
                ])
                ->where(function ($q) {
                    $q->where('is_automatic', false)->orWhereNull('is_automatic');
                })
                ->where('grandtotal_score', '>', 8)
                ->where('grandtotal_score', '<=', 10)
                ->whereNotNull('comment')
                ->where('comment', '!=', '')
                ->where('comment', 'not like', 'Successfully completed fishing tour%')
                ->latest('id')
                ->limit(15)
                ->get();

            return $reviews
                ->map(function (Review $review) {
                    $guiding = $review->guiding;
                    $booking = $review->booking;
                    $author = $this->testimonialAuthor($review, $booking);
                    $tourDate = $booking?->getBookingDate() ?? $review->created_at;
                    $tourTitle = $guiding?->title
                        ? translate($guiding->title)
                        : null;

                    return [
                        'quote' => Str::limit(strip_tags((string) translate($review->comment)), 180),
                        'score' => round((float) $review->grandtotal_score, 1),
                        'author' => $author ?: __('homepage.testimonial_guest'),
                        'date' => $tourDate?->translatedFormat('M Y'),
                        'tour_title' => $tourTitle ? Str::limit($tourTitle, 60) : null,
                        'tour_url' => ($guiding?->id && $guiding?->slug)
                            ? $guiding->publicShowUrl()
                            : null,
                    ];
                })
                ->filter(fn (array $item) => filled($item['quote']) && $item['score'] > 8 && $item['score'] <= 10)
                ->values();
        });
    }

    /**
     * Prefer the booking guest/registered firstname; never resolve guest IDs against users.
     */
    private function testimonialAuthor(Review $review, ?Booking $booking): ?string
    {
        $fromBooking = trim((string) ($booking?->user?->firstname ?? ''));
        if ($fromBooking !== '') {
            return $fromBooking;
        }

        if (! $review->user_id) {
            return null;
        }

        if ($booking?->is_guest) {
            return UserGuest::query()->whereKey($review->user_id)->value('firstname');
        }

        return User::query()->whereKey($review->user_id)->value('firstname');
    }

    private function magazineThreads(string $locale): Collection
    {
        return Cache::remember("homepage_magazine_{$locale}", now()->addMinutes(15), function () use ($locale) {
            return Thread::query()
                ->where('language', $locale)
                ->latest()
                ->limit(3)
                ->get();
        });
    }

    /**
     * @return array{month: string, title: string, text: string, species: Collection}
     */
    private function seasonModule(string $locale): array
    {
        $monthNumber = (int) now()->month;
        $month = now()->translatedFormat('F');
        $cacheKey = self::SEASON_CACHE_PREFIX."_{$locale}_{$monthNumber}";

        return Cache::remember($cacheKey, now()->addMinutes(20), function () use ($locale, $monthNumber, $month) {
            $highlight = MonthlyHighlight::forMonth($monthNumber);

            if ($highlight) {
                return [
                    'month' => $month,
                    'title' => $highlight->localizedTitle($locale),
                    'text' => $highlight->localizedSubtitle($locale) ?? __('homepage.season_text'),
                    'species' => $this->resolveHighlightCards($highlight, $locale),
                ];
            }

            return [
                'month' => $month,
                'title' => __('homepage.season_title', ['month' => $month]),
                'text' => __('homepage.season_text'),
                'species' => $this->targetSpecies($locale)
                    ->take(MonthlyHighlight::MAX_ITEMS)
                    ->map(fn (array $card) => $card + [
                        'type' => MonthlyHighlight::ITEM_TYPE_TARGET,
                        'country' => null,
                    ])
                    ->values(),
            ];
        });
    }

    /**
     * @return Collection<int, array{name: string, slug: string, thumbnail: string, url: string, type: string, country: ?string, fish?: string}>
     */
    private function resolveHighlightCards(MonthlyHighlight $highlight, string $locale): Collection
    {
        $items = $highlight->normalizedItems();
        if ($items->isEmpty()) {
            return collect();
        }

        $countryIds = $items
            ->flatMap(function (array $item) {
                if ($item['type'] === MonthlyHighlight::ITEM_TYPE_PAIR) {
                    return [$item['country_id']];
                }

                return $item['type'] === MonthlyHighlight::ITEM_TYPE_COUNTRY ? [$item['id']] : [];
            })
            ->unique()
            ->filter()
            ->values()
            ->all();

        $targetPageIds = $items
            ->flatMap(function (array $item) {
                if ($item['type'] === MonthlyHighlight::ITEM_TYPE_PAIR) {
                    return [$item['target_id']];
                }

                return $item['type'] === MonthlyHighlight::ITEM_TYPE_TARGET ? [$item['id']] : [];
            })
            ->unique()
            ->filter()
            ->values()
            ->all();

        $countries = Country::query()
            ->whereIn('id', $countryIds)
            ->get()
            ->keyBy('id');

        $targetPages = CategoryPage::query()
            ->where('type', 'Targets')
            ->whereIn('id', $targetPageIds)
            ->get()
            ->keyBy('id');

        $targets = Target::query()
            ->whereIn('id', $targetPages->pluck('source_id')->filter()->unique())
            ->get()
            ->keyBy('id');

        return $items->map(function (array $item) use ($countries, $targetPages, $targets, $locale) {
            if ($item['type'] === MonthlyHighlight::ITEM_TYPE_PAIR) {
                $country = $countries->get($item['country_id']);
                $page = $targetPages->get($item['target_id']);
                if (! $country || ! $page) {
                    return null;
                }

                $target = $targets->get($page->source_id);
                $fishName = $target?->name ?? $page->name;
                $countryName = $this->countries->labelFor($country, $locale);

                return [
                    'type' => MonthlyHighlight::ITEM_TYPE_PAIR,
                    'fish' => $fishName,
                    'country' => $countryName,
                    'name' => __('homepage.season_card_title', [
                        'fish' => $fishName,
                        'country' => $countryName,
                    ]),
                    'slug' => $page->slug,
                    'thumbnail' => $page->getThumbnailPath(),
                    'url' => $this->offersCatalogUrl($country, $target, $page),
                ];
            }

            if ($item['type'] === MonthlyHighlight::ITEM_TYPE_COUNTRY) {
                $country = $countries->get($item['id']);
                if (! $country) {
                    return null;
                }

                $countryName = $this->countries->labelFor($country, $locale);

                return [
                    'type' => MonthlyHighlight::ITEM_TYPE_COUNTRY,
                    'name' => $countryName,
                    'country' => $countryName,
                    'slug' => $country->slug,
                    'thumbnail' => $country->getThumbnailPath(),
                    'url' => $this->offersCatalogUrl($country),
                ];
            }

            $page = $targetPages->get($item['id']);
            if (! $page) {
                return null;
            }

            $target = $targets->get($page->source_id);

            return [
                'type' => MonthlyHighlight::ITEM_TYPE_TARGET,
                'name' => $target?->name ?? $page->name,
                'country' => null,
                'slug' => $page->slug,
                'thumbnail' => $page->getThumbnailPath(),
                'url' => $this->offersCatalogUrl(null, $target, $page),
            ];
        })->filter()->values();
    }

    /**
     * Homepage season cards open /offers with location and/or target-fish filters applied.
     */
    private function offersCatalogUrl(
        ?Country $country = null,
        ?Target $target = null,
        ?CategoryPage $page = null,
    ): string {
        $params = $country
            ? DestinationOfferScope::mergeIntoRequest([], $country)
            : [];

        $speciesId = $target?->id ?? (int) ($page?->source_id ?? 0);
        if ($speciesId > 0) {
            $params['species'] = [$speciesId];
        } elseif ($page) {
            $speciesName = $target?->name ?? $page->name;
            if (filled($speciesName)) {
                $params['species'] = [$speciesName];
            }
        }

        if (isset($params['place_types']) && is_array($params['place_types'])) {
            $params['place_types'] = json_encode(array_values($params['place_types']));
        }

        $params = array_filter(
            $params,
            static fn ($value) => $value !== null && $value !== '' && $value !== []
        );

        return route('offers.index', $params);
    }

    /**
     * @return array{
     *     rating: ?string,
     *     bookings: ?string,
     *     offers: string,
     *     countries: ?string,
     *     reviews_count: int,
     *     reviews_label: ?string,
     *     rating_label: string,
     *     bookings_label: string,
     *     offers_label: string,
     *     countries_label: string
     * }
     */
    private function trustStats(int $countryCount): array
    {
        $locale = app()->getLocale();

        return Cache::remember("homepage_trust_stats_v4_{$locale}_{$countryCount}", now()->addHour(), function () use ($countryCount) {
            $realReviews = Review::query()
                ->where(function ($q) {
                    $q->where('is_automatic', false)->orWhereNull('is_automatic');
                })
                ->whereNotNull('grandtotal_score');

            $avg = (clone $realReviews)->avg('grandtotal_score');
            $reviewsCount = (int) (clone $realReviews)->count();
            $bookings = (int) Booking::query()->count();

            $bookingsLabel = null;
            if ($bookings > 0) {
                $bookingsLabel = $bookings >= 1000
                    ? number_format((int) floor($bookings / 1000) * 1000).'+'
                    : (string) $bookings;
            }

            $reviewsLabel = null;
            if ($reviewsCount > 0) {
                $rounded = $reviewsCount >= 100
                    ? number_format((int) floor($reviewsCount / 10) * 10)
                    : (string) $reviewsCount;
                $reviewsLabel = __('homepage.trust_view_reviews', ['count' => $rounded]);
            }

            $countriesLabel = $countryCount > 0
                ? $countryCount.'+'
                : null;

            return [
                'rating' => $avg ? number_format((float) $avg, 1).'/10' : null,
                'bookings' => $bookingsLabel,
                'offers' => self::TRUST_OFFERS_LABEL,
                'countries' => $countriesLabel,
                'reviews_count' => $reviewsCount,
                'reviews_label' => $reviewsLabel,
                'rating_label' => __('homepage.trust_rating_label'),
                'bookings_label' => __('homepage.trust_bookings_label'),
                'offers_label' => __('homepage.trust_offers_label'),
                'countries_label' => __('homepage.trust_countries_label'),
            ];
        });
    }
}
