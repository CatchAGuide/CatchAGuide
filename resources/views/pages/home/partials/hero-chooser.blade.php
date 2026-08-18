@php
    use App\Domain\Offers\OfferListingFilter;

    $heroGuests = max(1, min(OfferListingFilter::MAX_GUESTS, (int) (request()->num_guests ?: OfferListingFilter::DEFAULT_GUESTS)));
    $heroSlides = [
        [
            'src' => asset('assets/images/homepage/hero-tour.webp'),
            'key' => 'tour',
        ],
        [
            'src' => asset('assets/images/homepage/hero-camp.webp'),
            'key' => 'camp',
        ],
        [
            'src' => asset('assets/images/homepage/hero-trip.webp'),
            'key' => 'trip',
        ],
        [
            'src' => asset('assets/images/homepage/hero-vacation.webp'),
            'key' => 'vacation',
        ],
    ];
@endphp

<section class="cag-home-hero" data-hero-carousel data-cag-reveal>
    <div class="cag-home-hero__media cag-home-ph" aria-hidden="true">
        <div class="cag-home-hero__slides">
            @foreach($heroSlides as $index => $slide)
                <img
                    class="cag-home-hero__image{{ $index === 0 ? ' is-active' : '' }}"
                    src="{{ $slide['src'] }}"
                    alt=""
                    width="1920"
                    height="1080"
                    @if($index === 0) fetchpriority="high" @else loading="lazy" @endif
                    data-hero-slide="{{ $slide['key'] }}"
                >
            @endforeach
        </div>
        <div class="cag-home-hero__overlay"></div>
    </div>

    <div class="cag-home-hero__dots" role="tablist" aria-label="{{ __('homepage.hero_carousel_label') }}">
        @foreach($heroSlides as $index => $slide)
            <button
                type="button"
                class="cag-home-hero__dot{{ $index === 0 ? ' is-active' : '' }}"
                data-hero-dot="{{ $index }}"
                aria-label="{{ __('homepage.hero_carousel_slide', ['n' => $index + 1]) }}"
                aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
            ></button>
        @endforeach
    </div>

    <div class="cag-home-hero__inner">
        <div class="cag-home-hero__copy cag-reveal__item" style="--reveal-i: 0">
            <p class="cag-home-hero__eyebrow">{{ __('homepage.hero_eyebrow') }}</p>
            <h1 class="cag-home-hero__title">{{ __('homepage.hero_h1') }}</h1>
            <div class="cag-home-hero__rule" aria-hidden="true"></div>
            <p class="cag-home-hero__sub">{{ __('homepage.hero_sub') }}</p>
        </div>

        <form
            class="cag-home-hero__search cag-reveal__item"
            style="--reveal-i: 1"
            action="{{ route('offers.index') }}"
            method="get"
            onsubmit="return validateSearch(event, 'homeHeroSearchPlace')"
            data-home-analytics="homepage_search_submit"
        >
            <div class="cag-home-hero__search-box">
                <div class="cag-home-hero__search-field">
                    @include('pages.home.partials.cag-icon', ['name' => 'search', 'size' => 17, 'iconClass' => 'cag-home-hero__search-icon'])
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
                <div class="cag-home-hero__who" data-offers-who>
                    <div
                        class="offers-persons-stepper offers-persons-stepper--home"
                        data-offers-persons-stepper
                        role="group"
                        aria-label="{{ __('homepage.searchbar-person') }}"
                    >
                        <button type="button" class="offers-persons-stepper__btn" data-offers-persons-delta="-1" aria-label="-">−</button>
                        <div class="offers-persons-stepper__value">
                            @include('pages.home.partials.cag-icon', ['name' => 'user', 'size' => 14, 'iconClass' => 'cag-home-hero__who-icon'])
                            <span data-offers-persons-label>{{ trans_choice('offers.persons_count', $heroGuests, ['count' => $heroGuests]) }}</span>
                        </div>
                        <input type="hidden" name="num_guests" value="{{ $heroGuests }}" data-offers-persons-input>
                        <button type="button" class="offers-persons-stepper__btn" data-offers-persons-delta="1" aria-label="+">+</button>
                    </div>
                </div>
                <button type="submit" class="cag-home-hero__search-btn">
                    <span>{{ __('homepage.searchbar-search') }}</span>
                    @include('pages.home.partials.cag-icon', ['name' => 'arrow', 'size' => 15])
                </button>
            </div>
        </form>

        <div class="cag-home-hero__doors">
            <a
                href="{{ route('guidings.landing') }}"
                class="cag-home-hero__door cag-reveal__item"
                style="--reveal-i: 2"
                data-home-analytics="homepage_chooser_guidings_click"
            >
                <span class="cag-home-hero__door-icon" aria-hidden="true">
                    @include('pages.home.partials.cag-icon', ['name' => 'rod', 'size' => 40])
                </span>
                <span class="cag-home-hero__door-body">
                    <span class="cag-home-hero__door-label">{{ __('homepage.chooser_tour_label') }}</span>
                    <span class="cag-home-hero__door-title">{{ __('homepage.chooser_tour_title') }}</span>
                    <span class="cag-home-hero__door-sub">{{ __('homepage.chooser_tour_sub') }}</span>
                </span>
                <span class="cag-home-hero__door-arrow" aria-hidden="true">
                    @include('pages.home.partials.cag-icon', ['name' => 'arrow', 'size' => 20])
                </span>
            </a>
            <a
                href="{{ route('vacations.index') }}"
                class="cag-home-hero__door cag-reveal__item"
                style="--reveal-i: 3"
                data-home-analytics="homepage_chooser_vacations_click"
            >
                <span class="cag-home-hero__door-icon" aria-hidden="true">
                    @include('pages.home.partials.cag-icon', ['name' => 'camp', 'size' => 40])
                </span>
                <span class="cag-home-hero__door-body">
                    <span class="cag-home-hero__door-label">{{ __('homepage.chooser_vacation_label') }}</span>
                    <span class="cag-home-hero__door-title">{{ __('homepage.chooser_vacation_title') }}</span>
                    <span class="cag-home-hero__door-sub">{{ __('homepage.chooser_vacation_sub') }}</span>
                </span>
                <span class="cag-home-hero__door-arrow" aria-hidden="true">
                    @include('pages.home.partials.cag-icon', ['name' => 'arrow', 'size' => 20])
                </span>
            </a>
        </div>
    </div>
</section>
