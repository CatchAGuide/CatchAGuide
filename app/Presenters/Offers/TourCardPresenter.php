<?php

namespace App\Presenters\Offers;

use App\Domain\Offers\OfferListingFilter;
use App\Models\Guiding;

class TourCardPresenter
{
    /**
     * @param  array<string, mixed>  $query
     */
    public function present(Guiding $guiding, array $query = []): array
    {
        $title = translate($guiding->title);
        $gallery = $this->galleryImages($guiding);
        $image = $gallery[0] ?? asset('images/placeholder_guide.jpg');
        $price = $guiding->getLowestPrice();
        $priceAmount = $price > 0
            ? '€'.number_format((float) $price, 0)
            : null;
        $species = $this->speciesTags($guiding);

        return [
            'type' => 'tour',
            'id' => $guiding->id,
            'title' => $title,
            'slug' => $guiding->slug,
            'url' => $guiding->publicShowUrl($this->filterQuery($query)),
            'image' => $image,
            'gallery_images' => $gallery,
            'badge' => __('offers.badge_tour'),
            'badge_class' => 'tour',
            'location' => translate($guiding->location),
            'meta_line' => null,
            'traits' => [],
            'feature_badges' => [],
            'facilities' => [],
            'addon_pills' => [],
            'duration_pill' => $this->durationLabel($guiding),
            'price' => $price > 0 ? $price : null,
            'price_label' => $priceAmount,
            'compact_price_label' => $priceAmount,
            'price_amount' => $priceAmount,
            'price_unit' => $priceAmount ? __('vacations.person') : null,
            'slider_tags' => array_slice($species, 0, 2),
            'slider_tags_extra' => max(0, count($species) - 2),
            'slider_availability' => [],
            'slider_cta' => __('offers.cta_tour'),
            'cta' => __('offers.cta_tour'),
            'cta_class' => 'tour',
            'trust' => [],
            'rating' => ($rating = $this->averageRating($guiding)) ? number_format($rating, 1) : null,
            'review_count' => $this->reviewCount($guiding),
            'target_fish_tags' => $species,
            'target_fish_tags_extra' => max(0, count($species) - 3),
        ];
    }

    /**
     * @param  array<string, mixed>  $query
     */
    public function presentListRow(Guiding $guiding, ?int $numGuests = null, array $query = []): array
    {
        if ($numGuests !== null && ! array_key_exists('num_guests', $query)) {
            $query['num_guests'] = $numGuests;
        }

        $card = $this->present($guiding, $query);
        $price = $guiding->getLowestPrice();
        $inclusions = $this->inclusionNames($guiding);
        $waters = $this->waterNames($guiding);
        $guests = $numGuests ?? OfferListingFilter::DEFAULT_GUESTS;

        $card['layout'] = 'row';
        $card['image_badge'] = null;
        $card['listing_included'] = array_slice($inclusions, 0, 2);
        $card['listing_included_extra'] = max(0, count($inclusions) - 2);
        $card = array_merge($card, $this->listingPriceFields($guiding, $price, $guests));
        $card['listing_cta'] = __('offers.cta_tour');
        $card['rating'] = $this->averageRating($guiding);
        $card['review_count'] = $this->reviewCount($guiding);
        $card['duration_label'] = $this->durationLabel($guiding);
        $card['guests_label'] = $this->guestsLabel($guiding);
        $card['water_label'] = $waters !== [] ? implode(', ', $waters) : null;
        $card['boat_label'] = $this->boatLabel($guiding);
        $card['whats_included_title'] = __('guidings.Whats_Included');
        $card['verified'] = false;

        return $card;
    }

    /**
     * @return array{
     *     listing_price_prefix: string|null,
     *     listing_price_display: string|null,
     *     listing_price_suffix: string|null,
     *     listing_price_note: string|null
     * }
     */
    private function listingPriceFields(Guiding $guiding, int $lowestPerPerson, int $numGuests): array
    {
        $resolved = $guiding->resolvePriceForGuests(max(1, $numGuests));
        if ($resolved !== null) {
            return [
                'listing_price_prefix' => null,
                'listing_price_display' => $this->formatEuro($resolved['total']),
                'listing_price_suffix' => null,
                'listing_price_note' => __('offers.price_per_person_for_guests', [
                    'price' => $this->formatEuro($resolved['per_person']),
                    'count' => $resolved['guests'],
                ]),
            ];
        }

        return [
            'listing_price_prefix' => __('message.from'),
            'listing_price_display' => $lowestPerPerson > 0
                ? $this->formatEuro($lowestPerPerson)
                : null,
            'listing_price_suffix' => __('offers.per_person_short'),
            'listing_price_note' => null,
        ];
    }

    private function formatEuro(float|int $amount): string
    {
        return number_format((float) $amount, 0, ',', '.').'€';
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    private function filterQuery(array $query): array
    {
        return array_filter($query, fn ($v) => $v !== null && $v !== '');
    }

    /**
     * @return array<int, string>
     */
    private function galleryImages(Guiding $guiding): array
    {
        return get_galleries_image_link($guiding, 0);
    }

    /**
     * @return array<int, string>
     */
    private function speciesTags(Guiding $guiding): array
    {
        $names = $guiding->cached_target_fish_names ?? null;
        if ($names === null && method_exists($guiding, 'getTargetFishNames')) {
            try {
                $names = $guiding->getTargetFishNames();
            } catch (\Throwable) {
                $names = [];
            }
        }

        return collect($names ?? [])
            ->map(fn ($item) => is_array($item) ? ($item['name'] ?? null) : $item)
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function inclusionNames(Guiding $guiding): array
    {
        $names = $guiding->cached_inclusion_names ?? null;
        if ($names === null && method_exists($guiding, 'getInclusionNames')) {
            try {
                $names = $guiding->getInclusionNames();
            } catch (\Throwable) {
                $names = [];
            }
        }

        return collect($names ?? [])
            ->map(fn ($item) => is_array($item) ? ($item['name'] ?? null) : $item)
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function waterNames(Guiding $guiding): array
    {
        if (! method_exists($guiding, 'getWaterNames')) {
            return [];
        }

        try {
            return collect($guiding->getWaterNames())
                ->map(fn ($item) => is_array($item) ? ($item['name'] ?? null) : $item)
                ->filter()
                ->values()
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    private function durationLabel(Guiding $guiding): ?string
    {
        if (empty($guiding->duration)) {
            return null;
        }

        $unit = ($guiding->duration_type ?? '') === 'multi_day'
            ? __('guidings.days')
            : __('guidings.hours');

        return trim($guiding->duration.' '.$unit);
    }

    private function guestsLabel(Guiding $guiding): ?string
    {
        if (empty($guiding->max_guests)) {
            return null;
        }

        $max = (int) $guiding->max_guests;
        $person = $max === 1 ? __('booking.person') : __('booking.people');

        return 'Max '.$max.' '.$person;
    }

    private function boatLabel(Guiding $guiding): ?string
    {
        if (! empty($guiding->cached_boat_type_name)) {
            return (string) $guiding->cached_boat_type_name;
        }

        if ($guiding->is_boat) {
            $name = $guiding->boatType->name ?? null;

            return $name ?: __('guidings.boat');
        }

        return __('guidings.shore');
    }

    private function averageRating(Guiding $guiding): ?float
    {
        if (! empty($guiding->cached_average_rating)) {
            return (float) $guiding->cached_average_rating;
        }

        try {
            $rating = $guiding->user?->average_rating();

            return $rating ? (float) $rating : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function reviewCount(Guiding $guiding): int
    {
        if (isset($guiding->cached_review_count)) {
            return (int) $guiding->cached_review_count;
        }

        try {
            return (int) ($guiding->user?->reviews?->count() ?? 0);
        } catch (\Throwable) {
            return 0;
        }
    }
}
