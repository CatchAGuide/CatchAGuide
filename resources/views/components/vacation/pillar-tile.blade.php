@props(['tile'])

@php
    use App\Domain\Vacation\Pillar;

    $modifier = $tile->pillar === Pillar::Trip ? 'trip' : 'camp';
    $priceLabel = null;

    if ($tile->minPrice) {
        $sym = $tile->currency === 'EUR' ? '€' . number_format($tile->minPrice, 0) : $tile->currency . ' ' . number_format($tile->minPrice, 0);
        $priceLabel = $tile->pillar === Pillar::Trip
            ? __('vacations.price_from_per_person_days', ['price' => $sym, 'days' => ''])
            : __('vacations.price_from_per_night', ['price' => $sym]);
    }

    $icon = $modifier === 'trip' ? 'fa-suitcase-rolling' : 'fa-campground';
@endphp

<a
    href="{{ $tile->url }}"
    class="vacation-pillar-tile vacation-pillar-tile--{{ $modifier }}"
    data-analytics-vacation-pillar-tile
    data-pillar="{{ $modifier }}"
>
    <div class="vacation-pillar-tile__icon" aria-hidden="true">
        <i class="fas {{ $icon }}"></i>
    </div>

    <div class="vacation-pillar-tile__content">
        <div class="vacation-pillar-tile__row">
            <div class="vacation-pillar-tile__head">
                <div class="vacation-pillar-tile__header">
                    <span class="vacation-pillar-tile__badge">{{ $modifier === 'trip' ? __('vacations.badge_all_inclusive') : __('vacations.badge_camp') }}</span>
                    @if($tile->descriptor)
                        <span class="vacation-pillar-tile__descriptor">{{ $tile->descriptor }}</span>
                    @endif
                </div>
                <h2 class="vacation-pillar-tile__title">{{ $tile->title }}</h2>
            </div>
            <span class="vacation-pillar-tile__cta">
                {{ __('vacations.pillar_tile_explore') }}
                <i class="fas fa-arrow-right" aria-hidden="true"></i>
            </span>
        </div>

        <p class="vacation-pillar-tile__desc">{{ $tile->description }}</p>

        <ul class="vacation-pillar-tile__stats">
            <li>{{ __('vacations.pillar_tile_listings', ['count' => $tile->listingCount]) }}</li>
            <li>{{ __('vacations.pillar_tile_countries', ['count' => $tile->countryCount]) }}</li>
            @if($priceLabel)
                <li>{{ trim($priceLabel) }}</li>
            @endif
        </ul>
    </div>
</a>
