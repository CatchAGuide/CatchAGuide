@php
    $offerModules = $offerModules ?? [
        'tour' => collect(),
        'camp' => collect(),
        'trip' => collect(),
    ];
    $offerModuleConfig = [
        'tour' => [
            'title' => __('homepage.offers_tours_title'),
            'browse' => __('homepage.mixed_browse_tours'),
            'url' => route('guidings.index'),
        ],
        'camp' => [
            'title' => __('homepage.offers_camps_title'),
            'browse' => __('homepage.mixed_browse_camps'),
            'url' => route('vacations.camps.index'),
        ],
        'trip' => [
            'title' => __('homepage.offers_trips_title'),
            'browse' => __('homepage.mixed_browse_trips'),
            'url' => route('vacations.trips.index'),
        ],
    ];
    $hasAnyOffers = collect($offerModules)->contains(fn ($items) => $items instanceof \Illuminate\Support\Collection && $items->isNotEmpty());
@endphp

@if($hasAnyOffers)
<section class="cag-home-section cag-home-offers">
    <div class="cag-home-container">
        <div class="cag-home-section__header">
            <h2 class="cag-home-section__title">{{ __('homepage.offers_title') }}</h2>
        </div>

        @foreach($offerModuleConfig as $type => $module)
            @php $cards = $offerModules[$type] ?? collect(); @endphp
            @if($cards->isNotEmpty())
                <div class="cag-home-offers__module" data-offer-module="{{ $type }}">
                    <div class="cag-home-offers__module-header">
                        <h3 class="cag-home-offers__module-title">{{ $module['title'] }}</h3>
                        <div class="cag-home-offers__module-tools">
                            <a
                                href="{{ $module['url'] }}"
                                class="cag-home-section__link d-none d-md-inline"
                                data-home-analytics="homepage_mixed_browse_click"
                                data-product-type="{{ $type }}"
                            >{{ $module['browse'] }}</a>
                            <div class="cag-home-offers__nav d-none d-md-flex">
                                <button type="button" class="cag-home-icon-btn" data-offer-prev="{{ $type }}" aria-label="{{ __('vacations.slider_prev') }}">
                                    <i class="fas fa-chevron-left" aria-hidden="true"></i>
                                </button>
                                <button type="button" class="cag-home-icon-btn" data-offer-next="{{ $type }}" aria-label="{{ __('vacations.slider_next') }}">
                                    <i class="fas fa-chevron-right" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="cag-home-offers__viewport" data-offer-rail="{{ $type }}">
                        <div class="cag-home-offers__rail" role="list">
                            @foreach($cards as $card)
                                @include('pages.home.partials.offer-card', ['card' => $card, 'type' => $type])
                            @endforeach
                        </div>
                    </div>

                    <div class="cag-home-offers__module-browse-mobile d-md-none">
                        <a
                            href="{{ $module['url'] }}"
                            data-home-analytics="homepage_mixed_browse_click"
                            data-product-type="{{ $type }}"
                        >{{ $module['browse'] }}</a>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</section>
@endif
