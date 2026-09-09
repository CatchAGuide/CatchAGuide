@php
    $offerModules = $offerModules ?? [
        'tour' => collect(),
        'camp' => collect(),
        'trip' => collect(),
    ];
    $offersTitle = $offersTitle ?? __('homepage.offers_title');
    $offersSectionClass = trim('cag-home-section cag-home-offers '.($offersSectionClass ?? ''));
    $offersVariant = $offersVariant ?? 'home';
    $offersEmptyMessage = $offersEmptyMessage ?? null;
    $offerBrowseUrls = $offerBrowseUrls ?? [];
    $offerModuleConfig = [
        'tour' => [
            'title' => __('homepage.offers_tours_title'),
            'browse' => __('homepage.mixed_browse_tours'),
            'url' => $offerBrowseUrls['tour'] ?? route('guidings.index'),
            'icon' => 'rod',
            'sub' => __('homepage.offers_tours_sub'),
        ],
        'camp' => [
            'title' => __('homepage.offers_camps_title'),
            'browse' => __('homepage.mixed_browse_camps'),
            'url' => $offerBrowseUrls['camp'] ?? route('vacations.camps.index'),
            'icon' => 'camp',
            'sub' => __('homepage.offers_camps_sub'),
        ],
        'trip' => [
            'title' => __('homepage.offers_trips_title'),
            'browse' => __('homepage.mixed_browse_trips'),
            'url' => $offerBrowseUrls['trip'] ?? route('vacations.trips.index'),
            'icon' => 'globe',
            'sub' => __('homepage.offers_trips_sub'),
        ],
    ];
    $hasAnyOffers = collect($offerModules)->contains(fn ($items) => $items instanceof \Illuminate\Support\Collection && $items->isNotEmpty());
@endphp

@if($hasAnyOffers)
<section class="{{ $offersSectionClass }}" data-cag-reveal @if($offersVariant === 'destination') data-dest-offers @endif>
    <div class="cag-home-container">
        <div class="cag-home-section__header cag-reveal__header">
            <h2 class="cag-home-section__title">{{ $offersTitle }}</h2>
        </div>

        @foreach($offerModuleConfig as $type => $module)
            @php $cards = $offerModules[$type] ?? collect(); @endphp
            @if($cards->isNotEmpty())
                <div class="cag-home-offers__module cag-home-offers__module--{{ $type }} cag-reveal__block" data-offer-module="{{ $type }}" style="--reveal-i: {{ $loop->index }}">
                    <div class="cag-home-offers__module-header">
                        <div class="cag-home-offers__module-heading">
                            <span class="cag-home-offers__module-mark" aria-hidden="true">
                                @include('pages.home.partials.cag-icon', ['name' => $module['icon'], 'size' => 20])
                            </span>
                            <div class="cag-home-offers__module-copy">
                                <div class="cag-home-offers__module-title-row">
                                    <h3 class="cag-home-offers__module-title">{{ $module['title'] }}</h3>
                                    <a
                                        href="{{ $module['url'] }}"
                                        class="cag-home-section__link cag-home-section__link--mobile"
                                        data-home-analytics="homepage_mixed_browse_click"
                                        data-product-type="{{ $type }}"
                                    >{{ __('homepage.mixed_see_all') }}</a>
                                </div>
                                <p class="cag-home-offers__module-sub">{{ $module['sub'] }}</p>
                            </div>
                        </div>
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
                                @include('pages.home.partials.offer-card', [
                                    'card' => $card,
                                    'type' => $type,
                                    'revealIndex' => min($loop->index, 6),
                                ])
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</section>
@elseif(filled($offersEmptyMessage))
<section class="{{ $offersSectionClass }} cag-dest-offers--empty" data-cag-reveal>
    <div class="cag-home-container">
        <div class="cag-home-section__header cag-reveal__header">
            <h2 class="cag-home-section__title">{{ $offersTitle }}</h2>
        </div>
        <p class="cag-dest-offers__empty">{{ $offersEmptyMessage }}</p>
    </div>
</section>
@endif
