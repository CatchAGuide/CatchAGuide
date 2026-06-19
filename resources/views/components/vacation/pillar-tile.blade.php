@props(['tile'])

@php
    use App\Domain\Vacation\Pillar;

    $modifier = $tile->pillar === Pillar::Trip ? 'trip' : 'camp';
    $taglineKey = $modifier === 'trip' ? 'vacations.pillar_trips_tagline' : 'vacations.pillar_camps_tagline';
    $priceLabel = null;

    if ($tile->minPrice) {
        $sym = $tile->currency === 'EUR' ? '€' . number_format($tile->minPrice, 0) : $tile->currency . ' ' . number_format($tile->minPrice, 0);
        $priceLabel = __('vacations.pillar_tile_from', ['price' => $sym]);
    }

    $icon = $modifier === 'trip' ? 'fa-suitcase-rolling' : 'fa-campground';
@endphp

<a
    href="{{ $tile->url }}"
    class="vacation-pillar-tile vacation-pillar-tile--{{ $modifier }}"
    data-analytics-vacation-pillar-tile
    data-pillar="{{ $modifier }}"
>
    <div class="vacation-pillar-tile__brand" aria-hidden="true">
        <div class="vacation-pillar-tile__mark">
            <i class="fas {{ $icon }}"></i>
        </div>
    </div>

    <div class="vacation-pillar-tile__body">
        <span class="vacation-pillar-tile__shine" aria-hidden="true"></span>

        <div class="vacation-pillar-tile__top">
            <span class="vacation-pillar-tile__badge">{{ $modifier === 'trip' ? __('vacations.badge_all_inclusive') : __('vacations.badge_camp') }}</span>
            <ul class="vacation-pillar-tile__stats">
                <li>
                    <i class="fas fa-th-list" aria-hidden="true"></i>
                    {{ $tile->listingCount }}
                </li>
                <li>
                    <i class="fas fa-globe-europe" aria-hidden="true"></i>
                    {{ $tile->countryCount }}
                </li>
                @if($priceLabel)
                    <li>
                        <i class="fas fa-tag" aria-hidden="true"></i>
                        {{ $priceLabel }}
                    </li>
                @endif
            </ul>
        </div>

        <div class="vacation-pillar-tile__main">
            <div class="vacation-pillar-tile__copy">
                <h2 class="vacation-pillar-tile__title">{{ $tile->title }}</h2>
                <p class="vacation-pillar-tile__tagline">{{ __($taglineKey) }}</p>
            </div>

            <span class="vacation-pillar-tile__cta-glow">
                <span class="vacation-pillar-tile__cta">
                    {{ __('vacations.pillar_tile_explore') }}
                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </span>
            </span>
        </div>

        <span class="vacation-pillar-tile__chevron" aria-hidden="true">
            <i class="fas fa-chevron-right"></i>
        </span>
    </div>
</a>
