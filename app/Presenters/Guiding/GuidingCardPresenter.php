<?php

namespace App\Presenters\Guiding;

use App\Models\Guiding;

class GuidingCardPresenter
{
    /**
     * Shaped to match `pages.home.partials.offer-card`'s card array contract
     * (type/title/url/image/badge/location/price_amount/price_unit), plus the
     * optional meta/rating/is_new/requested_count fields that partial also renders.
     *
     * @return array{
     *     type: string,
     *     id: int,
     *     url: string,
     *     image: string,
     *     badge: string,
     *     title: string,
     *     location: string,
     *     meta: string,
     *     rating: ?string,
     *     review_count: int,
     *     is_new: bool,
     *     price_amount: string,
     *     price_unit: string,
     *     requested_count: ?int
     * }
     */
    public function present(Guiding $guiding, ?int $requestedCount = null): array
    {
        $averageRating = $guiding->cached_average_rating ?? $guiding->user?->average_rating();
        $reviewCount = $guiding->cached_review_count ?? ($guiding->user?->reviews->count() ?? 0);
        $durationUnit = $guiding->duration_type === 'multi_day' ? __('guidings.days') : __('guidings.hours');

        return [
            'type' => 'tour',
            'id' => $guiding->id,
            'url' => $guiding->publicShowUrl(),
            'image' => get_featured_image_link($guiding) ?: asset('images/placeholder_guide.webp'),
            'badge' => __('homepage.landing_card_badge'),
            'title' => translate($guiding->title),
            'location' => translate($guiding->location),
            'meta' => trim(($guiding->duration ?: 0).' '.$durationUnit.' · '.__('homepage.landing_card_max_guests', ['count' => (int) $guiding->max_guests])),
            'rating' => $averageRating ? number_format((float) $averageRating, 1) : null,
            'review_count' => (int) $reviewCount,
            'is_new' => ! $averageRating,
            'price_amount' => '€'.number_format($guiding->getLowestPrice(), 0, ',', '.'),
            'price_unit' => __('vacations.person'),
            'requested_count' => $requestedCount,
        ];
    }
}
