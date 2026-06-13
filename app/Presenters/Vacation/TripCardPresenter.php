<?php

namespace App\Presenters\Vacation;

use App\Models\Trip;

class TripCardPresenter
{
    public function __construct(
        private TripTrustSignalResolver $trust,
    ) {}

    public function present(Trip $trip): array
    {
        $durationPill = $this->durationPill($trip);
        $currency = $trip->currency ?: 'EUR';
        $sym = $currency === 'EUR' ? '€' : $currency . ' ';

        return [
            'type' => 'trip',
            'id' => $trip->id,
            'title' => $trip->title,
            'slug' => $trip->slug,
            'url' => route('vacations.trips.show', $trip->slug),
            'image' => media_url($trip->thumbnail_path),
            'badge' => __('vacations.badge_all_inclusive'),
            'badge_class' => 'trip',
            'location' => $trip->location,
            'meta_line' => $this->metaLine($trip),
            'addon_pills' => [],
            'duration_pill' => $durationPill,
            'price' => $trip->price_per_person,
            'price_label' => $trip->price_per_person
                ? __('vacations.price_from_per_person_days', [
                    'price' => $sym . number_format((float) $trip->price_per_person, 0),
                    'days' => $durationPill ?? '',
                ])
                : null,
            'cta' => __('vacations.request_trip'),
            'cta_class' => 'trip',
            'trust' => $this->trust->resolve($trip),
        ];
    }

    public function presentListRow(Trip $trip): array
    {
        $card = $this->present($trip);
        $card['layout'] = 'row';

        return $card;
    }

    private function durationPill(Trip $trip): ?string
    {
        if ($trip->duration_days) {
            return $trip->duration_days . ' ' . __('trips.duration_days');
        }
        if ($trip->duration_nights) {
            return $trip->duration_nights . ' ' . __('trips.duration_nights');
        }

        return null;
    }

    private function metaLine(Trip $trip): string
    {
        $inclusions = __('vacations.trip_meta_inclusions');
        $group = '';
        if ($trip->group_size_min || $trip->group_size_max) {
            $min = $trip->group_size_min;
            $max = $trip->group_size_max;
            if ($min && $max) {
                $group = __('vacations.trip_meta_group', ['min' => $min, 'max' => $max]);
            } elseif ($max) {
                $group = __('vacations.trip_meta_group_max', ['max' => $max]);
            }
        }

        return trim($inclusions . ($group ? ' · ' . $group : ''));
    }
}
