<?php

namespace App\Presenters\Offers;

use App\Models\Guiding;

class TourCardPresenter
{
    public function present(Guiding $guiding): array
    {
        $title = translate($guiding->title);
        $gallery = $this->galleryImages($guiding);
        $image = $gallery[0] ?? (get_featured_image_link($guiding) ?: asset('images/placeholder_guide.jpg'));
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
            'url' => route('guidings.show', [$guiding->id, $guiding->slug]),
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
            'target_fish_tags' => $species,
            'target_fish_tags_extra' => max(0, count($species) - 3),
        ];
    }

    public function presentListRow(Guiding $guiding): array
    {
        $card = $this->present($guiding);
        $price = $guiding->getLowestPrice();
        $inclusions = $this->inclusionNames($guiding);
        $waters = $this->waterNames($guiding);

        $card['layout'] = 'row';
        $card['image_badge'] = null;
        $card['listing_included'] = array_slice($inclusions, 0, 2);
        $card['listing_included_extra'] = max(0, count($inclusions) - 2);
        $card['listing_price_prefix'] = __('message.from');
        $card['listing_price_display'] = $price > 0
            ? number_format((float) $price, 0, ',', '.').'€'
            : null;
        $card['listing_price_suffix'] = 'p.P.';
        $card['listing_cta'] = __('offers.cta_tour');
        $card['rating'] = $this->averageRating($guiding);
        $card['review_count'] = $this->reviewCount($guiding);
        $card['duration_label'] = $this->durationLabel($guiding);
        $card['guests_label'] = $this->guestsLabel($guiding);
        $card['water_label'] = $waters !== [] ? implode(', ', $waters) : null;
        $card['boat_label'] = $this->boatLabel($guiding);
        $card['whats_included_title'] = __('guidings.Whats_Included');

        return $card;
    }

    /**
     * @return array<int, string>
     */
    private function galleryImages(Guiding $guiding): array
    {
        if (! empty($guiding->cached_gallery_images) && is_array($guiding->cached_gallery_images)) {
            return array_values(array_filter($guiding->cached_gallery_images));
        }

        $decoded = is_string($guiding->gallery_images)
            ? (json_decode($guiding->gallery_images, true) ?: [])
            : (array) ($guiding->gallery_images ?? []);

        $images = array_values(array_filter($decoded));
        if ($images === []) {
            $featured = get_featured_image_link($guiding);
            if ($featured) {
                $images[] = $featured;
            }
        }

        return $images;
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
        $person = $max === 1 ? translate('Person') : translate('Personen');

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
