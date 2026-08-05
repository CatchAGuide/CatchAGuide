<?php

namespace App\Services\Homepage;

use App\Models\Booking;
use App\Models\CategoryPage;
use App\Models\Review;
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
     *     offerModules: array{tour: Collection, trip: Collection, camp: Collection},
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
        return Cache::remember("homepage_target_species_{$locale}", now()->addMinutes(30), function () use ($locale) {
            return CategoryPage::query()
                ->where('type', 'Targets')
                ->where('is_favorite', 1)
                ->orderBy('name')
                ->limit(8)
                ->get()
                ->map(function (CategoryPage $page) use ($locale) {
                    $lang = $page->language($locale);

                    return [
                        'name' => $lang?->title ?? $page->name,
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
        return Cache::remember('homepage_testimonials_v3_'.app()->getLocale(), now()->addMinutes(30), function () {
            $reviews = Review::query()
                ->with(['guiding:id,title'])
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

            $authorNames = User::query()
                ->whereIn('id', $reviews->pluck('user_id')->filter()->unique())
                ->pluck('firstname', 'id');

            return $reviews
                ->map(function (Review $review) use ($authorNames) {
                    $tourTitle = $review->guiding?->title
                        ? translate($review->guiding->title)
                        : null;

                    return [
                        'quote' => Str::limit(strip_tags((string) $review->comment), 180),
                        'score' => round((float) $review->grandtotal_score, 1),
                        'author' => $authorNames[$review->user_id] ?? __('homepage.testimonial_guest'),
                        'date' => optional($review->created_at)?->translatedFormat('M Y'),
                        'tour_title' => $tourTitle ? Str::limit($tourTitle, 60) : null,
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
        $month = now()->translatedFormat('F');
        $species = $this->targetSpecies($locale)->take(2);

        return [
            'month' => $month,
            'title' => __('homepage.season_title', ['month' => $month]),
            'text' => __('homepage.season_text'),
            'cta_url' => route(($locale === 'de' ? 'blogde' : 'blog').'.index'),
            'species' => $species,
        ];
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
