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
            'traits' => $this->traits($camp),
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
        $card['target_fish_tags'] = collect($camp->target_fish ?? [])
            ->map(fn ($fish) => is_array($fish) ? ($fish['name'] ?? '') : (string) $fish)
            ->filter()
            ->values()
            ->all();
        $card['facilities_extra'] = max(0, $camp->facilities->count() - count($facilities));
        $card['listing_price_suffix'] = __('vacations.per_day_label');

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
     * @return array<int, array{label: string, value: string}>
     */
    private function traits(Camp $camp): array
    {
        $traits = [];

        $targetFish = $this->targetFishLabels($camp);
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
    private function targetFishLabels(Camp $camp): array
    {
        return collect($camp->target_fish ?? [])
            ->map(fn ($fish) => is_array($fish) ? ($fish['name'] ?? '') : (string) $fish)
            ->filter()
            ->take(3)
            ->values()
            ->all();
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
