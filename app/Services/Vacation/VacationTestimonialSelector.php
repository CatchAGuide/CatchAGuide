<?php

namespace App\Services\Vacation;

use App\Models\VacationTestimonial;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class VacationTestimonialSelector
{
    /**
     * Admin-curated vacation testimonials (Trip/Camp/Accommodation/RentalBoat/SpecialOffer stays),
     * as opposed to the tour/guiding reviews surfaced by App\Services\Reviews\TestimonialSelector.
     *
     * @return Collection<int, array{quote: string, score: float, author: string, date: ?string, listing_title: ?string, listing_url: ?string}>
     */
    public function latest(int $limit = 6): Collection
    {
        return Cache::remember("vacation_testimonials_latest_v1_{$limit}_".app()->getLocale(), now()->addMinutes(30), function () use ($limit) {
            return VacationTestimonial::query()
                ->published()
                ->orderBy('sort_order')
                ->latest('reviewed_on')
                ->latest('id')
                ->limit($limit)
                ->get()
                ->map(fn (VacationTestimonial $testimonial) => [
                    'quote' => Str::limit(strip_tags($testimonial->quote), 180),
                    'score' => round((float) $testimonial->rating, 1),
                    'author' => $testimonial->author,
                    'date' => $testimonial->reviewed_on?->translatedFormat('M Y'),
                    'listing_title' => $testimonial->listing_title,
                    'listing_url' => $testimonial->listing_url,
                ])
                ->values();
        });
    }
}
