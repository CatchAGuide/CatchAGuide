<?php

namespace App\Services\Homepage;

use App\Models\Booking;
use App\Models\CategoryPage;
use App\Models\Country;
use App\Models\MonthlyHighlight;
use App\Models\Review;
use App\Models\Target;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class HomepageLandingService
{
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

        return [
            'featuredCountries' => $this->countries->featured(8),
            'countryCount' => $this->countries->totalCount(),
            'mixedOffers' => $this->mixedOffers->mixed(9),
            'offerModules' => $this->mixedOffers->byModule(8),
            'targetSpecies' => $this->targetSpecies($locale),
            'testimonials' => $this->testimonials(),
            'magazineThreads' => $this->magazineThreads($locale),
            'season' => $this->seasonModule($locale),
            'trust' => $this->trustStats(),
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
                    'url' => route('category.targets', ['type' => 'targets', 'slug' => $page->slug]),
                ];
            });
        });
    }

    /**
     * Real guest reviews only: not automatic, grandtotal score 8–10.
     */
    private function testimonials(): Collection
    {
        return Cache::remember('homepage_testimonials_v4_'.app()->getLocale(), now()->addMinutes(30), function () {
            $reviews = Review::query()
                ->with([
                    'guiding:id,title,slug',
                    'booking.calendar_schedule',
                    'booking.blocked_event',
                    'booking.guestUser:id,firstname',
                    'booking.registeredUser:id,firstname',
                ])
                ->where(function ($q) {
                    $q->where('is_automatic', false)->orWhereNull('is_automatic');
                })
                ->whereBetween('grandtotal_score', [8, 10])
                ->whereNotNull('comment')
                ->where('comment', '!=', '')
                ->where('comment', 'not like', 'Successfully completed fishing tour%')
                ->latest('id')
                ->limit(6)
                ->get();

            return $reviews
                ->map(function (Review $review) {
                    $guiding = $review->guiding;
                    $booking = $review->booking;
                    $author = $booking?->user?->firstname
                        ?: User::query()->whereKey($review->user_id)->value('firstname');
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
                            ? route('guidings.show', [$guiding->id, $guiding->slug])
                            : null,
                    ];
                })
                ->filter(fn (array $item) => filled($item['quote']) && $item['score'] >= 8 && $item['score'] <= 10)
                ->values();
        });
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
     * @return array{month: string, title: string, text: string, cta_url: string, species: Collection}
     */
    private function seasonModule(string $locale): array
    {
        $monthNumber = (int) now()->month;
        $month = now()->translatedFormat('F');
        $cacheKey = "homepage_season_v3_{$locale}_{$monthNumber}";

        return Cache::remember($cacheKey, now()->addMinutes(20), function () use ($locale, $monthNumber, $month) {
            $highlight = MonthlyHighlight::forMonth($monthNumber);

            if ($highlight) {
                return [
                    'month' => $month,
                    'title' => $highlight->localizedTitle($locale),
                    'text' => $highlight->localizedSubtitle($locale) ?? __('homepage.season_text'),
                    'cta_url' => route(($locale === 'de' ? 'blogde' : 'blog').'.index'),
                    'species' => $this->resolveHighlightCards($highlight, $locale),
                ];
            }

            return [
                'month' => $month,
                'title' => __('homepage.season_title', ['month' => $month]),
                'text' => __('homepage.season_text'),
                'cta_url' => route(($locale === 'de' ? 'blogde' : 'blog').'.index'),
                'species' => $this->targetSpecies($locale)->take(MonthlyHighlight::MAX_ITEMS),
            ];
        });
    }

    /**
     * @return Collection<int, array{name: string, slug: string, thumbnail: string, url: string, type: string}>
     */
    private function resolveHighlightCards(MonthlyHighlight $highlight, string $locale): Collection
    {
        $items = $highlight->normalizedItems();
        if ($items->isEmpty()) {
            return collect();
        }

        $countryIds = $items->where('type', MonthlyHighlight::ITEM_TYPE_COUNTRY)->pluck('id')->all();
        $targetPageIds = $items->where('type', MonthlyHighlight::ITEM_TYPE_TARGET)->pluck('id')->all();

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

        return $items->map(function (array $item) use ($countries, $targetPages, $targets) {
            if ($item['type'] === MonthlyHighlight::ITEM_TYPE_COUNTRY) {
                $country = $countries->get($item['id']);
                if (! $country) {
                    return null;
                }

                return [
                    'type' => MonthlyHighlight::ITEM_TYPE_COUNTRY,
                    'name' => $country->name,
                    'slug' => $country->slug,
                    'thumbnail' => $country->getThumbnailPath(),
                    'url' => route('destination.country', ['country' => $country->slug]),
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
                'slug' => $page->slug,
                'thumbnail' => $page->getThumbnailPath(),
                'url' => route('category.targets', ['type' => 'targets', 'slug' => $page->slug]),
            ];
        })->filter()->values();
    }

    /**
     * @return array{rating: ?string, bookings: ?string, reviews_count: int, reviews_label: ?string, rating_label: string, bookings_label: string}
     */
    private function trustStats(): array
    {
        return Cache::remember('homepage_trust_stats_v3', now()->addHour(), function () {
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

            return [
                'rating' => $avg ? number_format((float) $avg, 1).'/10' : null,
                'bookings' => $bookingsLabel,
                'reviews_count' => $reviewsCount,
                'reviews_label' => $reviewsLabel,
                'rating_label' => __('homepage.trust_rating_label'),
                'bookings_label' => __('homepage.trust_bookings_label'),
            ];
        });
    }
}
