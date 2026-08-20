@if(($featuredCountries ?? collect())->isNotEmpty())
<section class="cag-home-section cag-home-destinations" data-cag-reveal>
    <div class="cag-home-container">
        <div class="cag-home-section__header cag-reveal__header">
            <div class="cag-home-section__heading">
                <h2 class="cag-home-section__title">{{ $title ?? __('homepage.destinations_title') }}</h2>
                @isset($subtitle)
                    <p class="cag-home-section__subtitle">{{ $subtitle }}</p>
                @endisset
                @if($showAllCountries ?? true)
                    <a
                        href="{{ route($allCountriesRoute ?? 'destination') }}"
                        class="cag-home-section__link cag-home-section__link--mobile"
                        data-home-analytics="homepage_all_countries_click"
                    >{{ $viewAllLabel ?? __('homepage.countries_all') }}</a>
                @endif
            </div>
            <div class="cag-home-section__tools">
                @if($showAllCountries ?? true)
                    <a
                        href="{{ route($allCountriesRoute ?? 'destination') }}"
                        class="cag-home-section__link d-none d-md-inline"
                        data-home-analytics="homepage_all_countries_click"
                    >{{ $viewAllLabel ?? __('homepage.countries_all') }}</a>
                @endif
                <div class="cag-home-destinations__nav d-none d-md-flex">
                    <button type="button" class="cag-home-icon-btn" data-dest-prev aria-label="{{ __('vacations.slider_prev') }}">
                        <i class="fas fa-chevron-left" aria-hidden="true"></i>
                    </button>
                    <button type="button" class="cag-home-icon-btn" data-dest-next aria-label="{{ __('vacations.slider_next') }}">
                        <i class="fas fa-chevron-right" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="cag-home-destinations__viewport" data-dest-rail>
            <div class="cag-home-destinations__rail" role="list">
                @foreach([false, true] as $isClone)
                    @foreach($featuredCountries as $country)
                        @php
                            $flagIso = strtolower((string) ($country['countrycode'] ?? ''));
                            if ($flagIso === 'uk') {
                                $flagIso = 'gb';
                            }
                        @endphp
                        <a
                            href="{{ route($countryRoute ?? 'destination.country', ['country' => $country['slug']]) }}"
                            class="cag-home-destinations__tile cag-home-ph cag-reveal__item"
                            role="listitem"
                            style="--reveal-i: {{ min($loop->index, 8) }}"
                            @if($isClone) aria-hidden="true" tabindex="-1" @endif
                            data-home-analytics="homepage_country_click"
                        >
                            <img
                                src="{{ $country['thumbnail'] }}"
                                alt="{{ $isClone ? '' : $country['name'] }}"
                                class="cag-home-destinations__img"
                                loading="lazy"
                                draggable="false"
                                width="320"
                                height="240"
                            >
                            <span class="cag-home-destinations__fade"></span>
                            <span class="cag-home-destinations__meta">
                                <span class="cag-home-destinations__name">
                                    @if($flagIso !== '')
                                        <span class="fi fi-{{ $flagIso }} cag-home-destinations__flag" aria-hidden="true"></span>
                                    @endif
                                    {{ $country['name'] }}
                                </span>
                            </span>
                        </a>
                    @endforeach
                @endforeach
            </div>
        </div>

    </div>
</section>
@endif
