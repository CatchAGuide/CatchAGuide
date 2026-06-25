<?php

namespace App\Presenters\Vacation;

use App\Models\Camp;

class CampCardPresenter
{
    public function __construct(
        private CampTrustSignalResolver $trust,
    ) {}

    public function present(Camp $camp): array
    {
        $price = $camp->getLowestAccommodationOrOfferPrice();
        $addons = $this->addonPills($camp);
        $facilities = $this->facilityLabels($camp);
        $targetFishAll = $this->targetFishTags($camp);
        $targetFish = array_slice($targetFishAll, 0, 3);
        $sliderTags = array_slice($targetFishAll, 0, 2);

        return [
            'type' => 'camp',
            'id' => $camp->id,
            'title' => translate($camp->title),
            'slug' => $camp->slug,
            'url' => route('vacations.camps.show', $camp->slug),
            'image' => media_url($camp->thumbnail_path),
            'gallery_images' => get_galleries_image_link($camp, 0),
            'badge' => __('vacations.badge_camp'),
            'badge_class' => 'camp',
            'location' => $camp->location,
            'meta_line' => $this->metaLine($camp, $facilities),
            'traits' => $this->traits($camp, $targetFish),
            'feature_badges' => $this->featureBadges($camp),
            'facilities' => $facilities,
            'addon_pills' => $addons,
            'duration_pill' => null,
            'price' => $price,
            'price_label' => $price !== null
                ? __('vacations.price_from_per_night', ['price' => '€' . number_format($price, 0)])
                : null,
            'compact_price_label' => $price !== null
                ? __('vacations.pillar_tile_from', ['price' => '€' . number_format($price, 0)]) . ' / ' . __('vacations.night')
                : null,
            'price_amount' => $price !== null ? '€' . number_format($price, 0) : null,
            'price_unit' => $price !== null ? __('vacations.night') : null,
            'slider_tags' => $sliderTags,
            'slider_tags_extra' => max(0, count($targetFishAll) - count($sliderTags)),
            'slider_availability' => $this->availabilityItems($camp),
            'slider_cta' => __('vacations.book_now'),
            'cta' => __('vacations.book_now'),
            'cta_class' => 'camp',
            'trust' => $this->trust->resolve($camp),
        ];
    }

    public function presentListRow(Camp $camp, ?int $destinationId = null): array
    {
        $card = $this->present($camp);
        $facilities = $this->facilityLabels($camp);
        $card['layout'] = 'row';
        $card['destination_id'] = $destinationId;
        $card['image_badge'] = $this->imageBadge($camp);
        $card['target_fish_tags'] = $this->targetFishTags($camp);
        $card['facilities_extra'] = max(0, $camp->facilities->count() - count($facilities));
        $card['listing_price_suffix'] = __('vacations.per_night_label');
        $card['listing_availability'] = $this->availabilityItems($camp);
        $card['listing_facilities'] = $this->facilityItems($camp);
        $card['target_fish_tags_extra'] = max(0, count($card['target_fish_tags']) - 3);

        return $card;
    }

    /**
     * @return array<int, string>
     */
    private function addonPills(Camp $camp): array
    {
        $pills = [];
        if ($camp->rentalBoats->where('status', 'active')->isNotEmpty()) {
            $pills[] = '+ ' . __('vacations.addon_boat');
        }
        if ($camp->guidings->isNotEmpty()) {
            $pills[] = '+ ' . __('vacations.addon_guide');
        }

        return $pills;
    }

    /**
     * @return array<int, array{label: string, available: bool}>
     */
    private function availabilityItems(Camp $camp): array
    {
        return [
            [
                'label' => __('vacations.guiding'),
                'available' => $camp->guidings->isNotEmpty(),
            ],
            [
                'label' => __('vacations.rental_boat'),
                'available' => $camp->rentalBoats->where('status', 'active')->isNotEmpty(),
            ],
        ];
    }

    /**
     * @return array<int, array{label: string, icon: string}>
     */
    private function facilityItems(Camp $camp, int $limit = 3): array
    {
        return $camp->facilities->take($limit)->map(function ($facility) {
            $locale = app()->getLocale();

            if ($locale === 'en' && ! empty($facility->name_en)) {
                $label = $facility->name_en;
            } elseif ($locale === 'de' && ! empty($facility->name_de)) {
                $label = $facility->name_de;
            } else {
                $label = $facility->name;
            }

            $label = trim((string) $label);

            return [
                'label' => $label,
                'icon' => vacation_camp_facility_icon($label),
            ];
        })->filter(fn (array $item) => $item['label'] !== '')->values()->all();
    }

    /**
     * @param  array<int, string>  $targetFish
     * @return array<int, array{label: string, value: string}>
     */
    private function traits(Camp $camp, array $targetFish = []): array
    {
        $traits = [];

        if (empty($targetFish)) {
            $targetFish = $this->targetFishLabels($camp);
        }
        if (! empty($targetFish)) {
            $traits[] = [
                'label' => __('vacations.target_fish'),
                'value' => implode(', ', $targetFish),
            ];
        }

        $methods = $this->methodLabels($camp);
        if (! empty($methods)) {
            $traits[] = [
                'label' => __('vacations.method'),
                'value' => implode(', ', $methods),
            ];
        }

        $accommodation = $camp->accommodations->first();
        if ($accommodation?->minimum_stay_nights) {
            $nights = (int) $accommodation->minimum_stay_nights;
            $traits[] = [
                'label' => __('vacations.duration_label'),
                'value' => $nights . ' ' . ($nights === 1 ? __('vacations.night') : __('vacations.nights')),
            ];
        }

        if ($accommodation?->max_occupancy) {
            $capacity = (int) $accommodation->max_occupancy;
            $traits[] = [
                'label' => __('vacations.capacity_label'),
                'value' => 'Max ' . $capacity . ' ' . ($capacity === 1 ? __('vacations.person') : __('vacations.persons')),
            ];
        }

        return array_slice($traits, 0, 3);
    }

    /**
     * @return array<int, array{icon: string, label: string}>
     */
    private function featureBadges(Camp $camp): array
    {
        $badges = [];

        if ($camp->rentalBoats->where('status', 'active')->isNotEmpty()) {
            $badges[] = ['icon' => 'fa-ship', 'label' => __('vacations.modern_boat')];
        }

        if ($camp->guidings->isNotEmpty()) {
            $badges[] = ['icon' => 'fa-fish', 'label' => __('vacations.pro_guide')];
        }

        return $badges;
    }

    /**
     * @return array<int, string>
     */
    private function facilityLabels(Camp $camp): array
    {
        return $camp->facilities->map(function ($facility) {
            $locale = app()->getLocale();

            if ($locale === 'en' && ! empty($facility->name_en)) {
                return $facility->name_en;
            }

            if ($locale === 'de' && ! empty($facility->name_de)) {
                return $facility->name_de;
            }

            return $facility->name;
        })->filter()->take(3)->values()->all();
    }

    /**
     * @return array<int, string>
     */
    private function targetFishTags(Camp $camp): array
    {
        $raw = $camp->target_fish ?? [];

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = is_array($decoded) ? $decoded : array_map('trim', explode(',', $raw));
        }

        return vacation_fish_tags(is_array($raw) ? $raw : []);
    }

    /**
     * @return array<int, string>
     */
    private function targetFishLabels(Camp $camp): array
    {
        return array_slice($this->targetFishTags($camp), 0, 3);
    }

    /**
     * @return array<int, string>
     */
    private function methodLabels(Camp $camp): array
    {
        return $camp->guidings
            ->flatMap(fn ($guiding) => $guiding->guidingMethods ?? collect())
            ->pluck('name')
            ->unique()
            ->filter()
            ->take(2)
            ->values()
            ->all();
    }

    private function metaLine(Camp $camp, array $facilities): string
    {
        $parts = array_filter([
            $camp->location,
            ! empty($facilities) ? implode(' · ', $facilities) : null,
        ]);

        return implode(' · ', $parts);
    }

    private function imageBadge(Camp $camp): ?string
    {
        if ($camp->guidings->isNotEmpty()) {
            return 'top';
        }

        if ($camp->rentalBoats->where('status', 'active')->isNotEmpty()) {
            return 'limited';
        }

        return null;
    }
}
