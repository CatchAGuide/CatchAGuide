@if(($featuredCountries ?? collect())->isNotEmpty())
<section class="cag-home-section cag-home-destinations" data-cag-reveal>
    <div class="cag-home-container">
        <div class="cag-home-section__header cag-reveal__header">
            <h2 class="cag-home-section__title">{{ __('homepage.destinations_title') }}</h2>
            <div class="cag-home-section__tools">
                <a
                    href="{{ route('destination') }}"
                    class="cag-home-section__link d-none d-md-inline"
                    data-home-analytics="homepage_all_countries_click"
                >{{ __('homepage.countries_all') }}</a>
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
                        <a
                            href="{{ route('destination.country', ['country' => $country['slug']]) }}"
                            class="cag-home-destinations__tile cag-reveal__item"
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
                                <span class="cag-home-destinations__name">{{ $country['name'] }}</span>
                                @if(!empty($country['from_price_label']))
                                    <span class="cag-home-destinations__price">{{ $country['from_price_label'] }}</span>
                                @endif
                            </span>
                        </a>
                    @endforeach
                @endforeach
            </div>
        </div>

        <div class="cag-home-destinations__all-mobile d-md-none cag-reveal__header">
            <a href="{{ route('destination') }}" data-home-analytics="homepage_all_countries_click">
                {{ __('homepage.countries_all') }}
            </a>
        </div>
    </div>
</section>
@endif
