@props(['tile'])

@php
    use App\Domain\Vacation\Pillar;

    $modifier = $tile->pillar === Pillar::Trip ? 'trip' : 'camp';
    $priceBadge = null;

    if ($tile->minPrice) {
        $sym = $tile->currency === 'EUR' ? '€' . number_format($tile->minPrice, 0) : $tile->currency . ' ' . number_format($tile->minPrice, 0);
        $priceKey = $modifier === 'trip' ? 'vacations.price_from_per_person' : 'vacations.price_from_per_night';
        $priceBadge = __($priceKey, ['price' => $sym]);
    }

    $featureBadge = $modifier === 'trip'
        ? __('vacations.pillar_badge_all_inclusive')
        : __('vacations.pillar_badge_instant_booking');

    $statsKey = $modifier === 'trip' ? 'vacations.pillar_tile_stats_trips' : 'vacations.pillar_tile_stats_camps';
    $statsLabel = __($statsKey, [
        'count' => $tile->listingCount,
        'countries' => $tile->countryCount,
    ]);

    $icon = $modifier === 'trip' ? 'fa-compass' : 'fa-campground';
@endphp

<a
    href="{{ $tile->url }}"
    class="vacation-pillar-tile vacation-pillar-tile--{{ $modifier }}"
    data-analytics-vacation-pillar-tile
    data-pillar="{{ $modifier }}"
>
    <span class="vacation-pillar-tile__decor" aria-hidden="true"></span>

    <div class="vacation-pillar-tile__icon" aria-hidden="true">
        <i class="fas {{ $icon }}"></i>
    </div>

    <h2 class="vacation-pillar-tile__title">{{ $tile->title }}</h2>
    <p class="vacation-pillar-tile__desc">{{ $tile->description }}</p>

    <div class="vacation-pillar-tile__badges">
        <span class="vacation-pillar-tile__badge">{{ $featureBadge }}</span>
        @if($priceBadge)
            <span class="vacation-pillar-tile__badge">{{ $priceBadge }}</span>
        @endif
    </div>

    <div class="vacation-pillar-tile__footer">
        <span class="vacation-pillar-tile__stats">{{ $statsLabel }}</span>
        <span class="vacation-pillar-tile__cta">
            {{ __('vacations.pillar_tile_explore') }}
            <i class="fas fa-arrow-right" aria-hidden="true"></i>
        </span>
    </div>
</a>
