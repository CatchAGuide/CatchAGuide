<?php

namespace App\Presenters\Vacation;

use App\Models\Trip;
use App\Services\Translation\ListingTranslationService;
use App\Services\Translation\ListingViewTranslationService;

class TripCardPresenter
{
    public function __construct(
        private TripTrustSignalResolver $trust,
        private ListingViewTranslationService $viewTranslation,
    ) {}

    public function present(Trip $trip): array
    {
        $this->viewTranslation->applyToModel($trip, ListingTranslationService::TYPE_TRIP);

        $durationPill = $this->durationPill($trip);
        $currency = $trip->currency ?: 'EUR';
        $sym = $currency === 'EUR' ? '€' : $currency . ' ';
        $speciesAll = vacation_fish_tags(collect($trip->getTargetSpeciesNames())->pluck('name')->all());
        $species = array_slice($speciesAll, 0, 3);
        $methods = collect($trip->getFishingMethodNames())->pluck('name')->filter()->take(2)->values()->all();
        $included = $this->includedHighlights($trip);
        $sliderTags = array_slice($speciesAll, 0, 2);

        return [
            'type' => 'trip',
            'id' => $trip->id,
            'title' => $trip->title,
            'slug' => $trip->slug,
            'url' => route('vacations.trips.show', $trip->slug),
            'image' => media_url($trip->thumbnail_path),
            'gallery_images' => get_galleries_image_link($trip, 0),
            'badge' => __('vacations.badge_trip'),
            'badge_class' => 'trip',
            'location' => $trip->location,
            'meta_line' => $this->metaLine($trip),
            'traits' => $this->traits($trip, $species, $methods),
            'feature_badges' => [],
            'facilities' => $included,
            'addon_pills' => [],
            'duration_pill' => $durationPill,
            'price' => $trip->price_per_person,
            'price_label' => $trip->price_per_person
                ? __('vacations.price_from_per_person_days', [
                    'price' => $sym . number_format((float) $trip->price_per_person, 0),
                    'days' => $durationPill ?? '',
                ])
                : null,
            'compact_price_label' => $trip->price_per_person
                ? __('vacations.pillar_tile_from', [
                    'price' => $sym . number_format((float) $trip->price_per_person, 0),
                ]) . ' / ' . __('vacations.person')
                : null,
            'price_amount' => $trip->price_per_person
                ? $sym . number_format((float) $trip->price_per_person, 0)
                : null,
            'price_unit' => $trip->price_per_person ? __('vacations.person') : null,
            'slider_tags' => $sliderTags,
            'slider_tags_extra' => max(0, count($speciesAll) - count($sliderTags)),
            'slider_availability' => [],
            'slider_cta' => __('vacations.inquire_trip'),
            'cta' => __('vacations.request_trip'),
            'cta_class' => 'trip',
            'trust' => $this->trust->resolve($trip),
        ];
    }

    public function presentListRow(Trip $trip): array
    {
        $card = $this->present($trip);
        $currency = $trip->currency ?: 'EUR';
        $sym = match ($currency) {
            'EUR' => '€',
            'USD' => '$',
            default => $currency . ' ',
        };
        $species = vacation_fish_tags(collect($trip->getTargetSpeciesNames())->pluck('name')->all());
        $allIncluded = collect($trip->included ?? [])
            ->map(fn ($item) => is_array($item) ? ($item['name'] ?? $item['title'] ?? '') : (string) $item)
            ->filter()
            ->values()
            ->all();

        $card['layout'] = 'row';
        $card['image_badge'] = null;
        $card['target_fish_tags'] = $species;
        $card['target_fish_tags_extra'] = max(0, count($species) - 3);
        $card['listing_included'] = array_slice($allIncluded, 0, 3);
        $card['listing_included_extra'] = max(0, count($allIncluded) - 3);
        $card['listing_price_prefix'] = __('vacations.starting_from_label');
        $card['listing_price_display'] = $trip->price_per_person
            ? $sym . number_format((float) $trip->price_per_person, 0, ',', '.')
            : null;
        $card['listing_price_suffix'] = __('vacations.per_person_short');
        $card['listing_cta'] = __('vacations.inquire_trip');

        return $card;
    }

    /**
     * @param  array<int, string>  $species
     * @param  array<int, string>  $methods
     * @return array<int, array{label: string, value: string}>
     */
    private function traits(Trip $trip, array $species, array $methods): array
    {
        $traits = [];

        if (! empty($species)) {
            $traits[] = [
                'label' => __('vacations.target_fish'),
                'value' => implode(', ', $species),
            ];
        }

        if (! empty($methods)) {
            $traits[] = [
                'label' => __('vacations.method'),
                'value' => implode(', ', $methods),
            ];
        }

        if ($trip->group_size_min || $trip->group_size_max) {
            $min = $trip->group_size_min;
            $max = $trip->group_size_max;
            if ($min && $max) {
                $group = __('vacations.trip_meta_group', ['min' => $min, 'max' => $max]);
            } elseif ($max) {
                $group = __('vacations.trip_meta_group_max', ['max' => $max]);
            } else {
                $group = null;
            }

            if ($group) {
                $traits[] = [
                    'label' => __('vacations.capacity_label'),
                    'value' => $group,
                ];
            }
        }

        return array_slice($traits, 0, 3);
    }

    /**
     * @return array<int, string>
     */
    private function includedHighlights(Trip $trip): array
    {
        return collect($trip->included ?? [])
            ->map(fn ($item) => is_array($item) ? ($item['name'] ?? $item['title'] ?? '') : (string) $item)
            ->filter()
            ->take(3)
            ->values()
            ->all();
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
