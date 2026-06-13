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
        $facilities = $camp->facilities->pluck('name')->take(3)->all();

        return [
            'type' => 'camp',
            'id' => $camp->id,
            'title' => translate($camp->title),
            'slug' => $camp->slug,
            'url' => route('vacations.camps.show', $camp->slug),
            'image' => media_url($camp->thumbnail_path),
            'badge' => __('vacations.badge_camp'),
            'badge_class' => 'camp',
            'location' => $camp->location,
            'meta_line' => $this->metaLine($camp, $facilities),
            'addon_pills' => $addons,
            'duration_pill' => null,
            'price' => $price,
            'price_label' => $price !== null
                ? __('vacations.price_from_per_night', ['price' => '€' . number_format($price, 0)])
                : null,
            'cta' => __('vacations.book_now'),
            'cta_class' => 'camp',
            'trust' => $this->trust->resolve($camp),
        ];
    }

    public function presentListRow(Camp $camp, ?int $destinationId = null): array
    {
        $card = $this->present($camp);
        $card['layout'] = 'row';
        $card['destination_id'] = $destinationId;

        return $card;
    }

    /**
     * @return array<int, string>
     */
    private function addonPills(Camp $camp): array
    {
        $pills = [];
        if ($camp->rentalBoats()->where('status', 'active')->exists()) {
            $pills[] = '+ ' . __('vacations.addon_boat');
        }
        if ($camp->guidings()->exists()) {
            $pills[] = '+ ' . __('vacations.addon_guide');
        }

        return $pills;
    }

    private function metaLine(Camp $camp, array $facilities): string
    {
        $parts = array_filter([
            $camp->location,
            ! empty($facilities) ? implode(' · ', $facilities) : null,
        ]);

        return implode(' · ', $parts);
    }
}
