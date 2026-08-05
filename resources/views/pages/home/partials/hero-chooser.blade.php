@php
    $heroImage = asset('assets/images/Coverbild_Startseite.webp');
@endphp

<section class="cag-home-hero">
    <div class="cag-home-hero__media" aria-hidden="true">
        <img
            class="cag-home-hero__image"
            src="{{ $heroImage }}"
            alt=""
            width="1920"
            height="1080"
            fetchpriority="high"
        >
        <div class="cag-home-hero__overlay"></div>
    </div>

    <div class="cag-home-hero__inner">
        <div class="cag-home-hero__copy">
            <h1 class="cag-home-hero__title">{{ __('homepage.hero_h1') }}</h1>
            <p class="cag-home-hero__sub">{{ __('homepage.hero_sub') }}</p>
        </div>

        <form
            class="cag-home-hero__search"
            action="{{ route('guidings.index') }}"
            method="get"
            onsubmit="return validateSearch(event, 'homeHeroSearchPlace')"
            data-home-analytics="homepage_search_submit"
        >
            <div class="cag-home-hero__search-box">
                <div class="cag-home-hero__search-field">
                    <i class="fas fa-search" aria-hidden="true"></i>
                    <input
                        id="homeHeroSearchPlace"
                        name="place"
                        type="text"
                        class="form-control"
                        placeholder="{{ __('homepage.hero_search_placeholder') }}"
                        autocomplete="on"
                    >
                    <input type="hidden" id="LocationCityHomeHero" name="city" value=""/>
                    <input type="hidden" id="LocationCountryHomeHero" name="country" value=""/>
                    <input type="hidden" id="LocationRegionHomeHero" name="region" value=""/>
                    <input type="hidden" id="LocationLatHomeHero" name="placeLat" value=""/>
                    <input type="hidden" id="LocationLngHomeHero" name="placeLng" value=""/>
                    @include('layouts.partials.geosearch-hidden-fields')
                </div>
                <button type="submit" class="cag-home-hero__search-btn">
                    <span>{{ __('homepage.searchbar-search') }}</span>
                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </button>
            </div>
        </form>

        <div class="cag-home-hero__doors">
            <a
                href="{{ route('guidings.index') }}"
                class="cag-home-hero__door"
                data-home-analytics="homepage_chooser_guidings_click"
            >
                <span class="cag-home-hero__door-label">{{ __('homepage.chooser_tour_label') }}</span>
                <span class="cag-home-hero__door-title">{{ __('homepage.chooser_tour_title') }}</span>
                <span class="cag-home-hero__door-sub">{{ __('homepage.chooser_tour_sub') }}</span>
                <span class="cag-home-hero__door-arrow" aria-hidden="true"><i class="fas fa-arrow-right"></i></span>
            </a>
            <a
                href="{{ route('vacations.index') }}"
                class="cag-home-hero__door"
                data-home-analytics="homepage_chooser_vacations_click"
            >
                <span class="cag-home-hero__door-label">{{ __('homepage.chooser_vacation_label') }}</span>
                <span class="cag-home-hero__door-title">{{ __('homepage.chooser_vacation_title') }}</span>
                <span class="cag-home-hero__door-sub">{{ __('homepage.chooser_vacation_sub') }}</span>
                <span class="cag-home-hero__door-arrow" aria-hidden="true"><i class="fas fa-arrow-right"></i></span>
            </a>
        </div>
    </div>
</section>
