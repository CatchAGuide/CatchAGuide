@php
    $offersGuests = max(1, min(20, (int) (request()->num_guests ?: 2)));
    $placeValue = (request()->placeLat || request()->placelat) && (request()->placeLng || request()->placelng)
        ? request()->place
        : '';
    $heroImage = asset('assets/images/homepage/hero-tour.webp');
@endphp
<div class="offers-page-header-shell cag-site-nav-shell" data-offers-header-shell>
    @include('layouts.partials.site-nav', [
        'overlay' => true,
        'idPrefix' => 'offers',
    ])

    <section class="offers-page-header" data-offers-page-header>
        <div class="offers-page-header__hero" data-offers-hero>
            <div class="offers-page-header__media" aria-hidden="true">
                <img
                    class="offers-page-header__image"
                    src="{{ $heroImage }}"
                    alt=""
                    width="1920"
                    height="640"
                    fetchpriority="high"
                >
                <div class="offers-page-header__overlay"></div>
            </div>

            <div class="offers-page-header__inner offers-page-header__inner--hero">
                <div class="offers-page-header__copy">
                    <h1 class="offers-page-header__title offers-page-header__anim" style="--offers-anim-i: 0">{{ $vm->pageTitle() }}</h1>
                    <p class="offers-page-header__sub offers-page-header__anim" style="--offers-anim-i: 1">{{ $vm->pageSubtitle() }}</p>
                </div>
            </div>
        </div>

        <div class="offers-page-header__inner offers-page-header__inner--search">
            <form
                class="offers-page-header__search offers-page-header__anim"
                style="--offers-anim-i: 2"
                action="{{ route('offers.index') }}"
                method="get"
                onsubmit="return validateSearch(event, 'offersCatalogSearchPlace')"
                data-offers-header-search
            >
                <div class="offers-page-header__search-box">
                    <label class="offers-page-header__segment offers-page-header__segment--where" for="offersCatalogSearchPlace">
                        <span class="offers-page-header__segment-label">{{ __('offers.search_where') }}</span>
                        <span class="offers-page-header__segment-control">
                            <i class="fas fa-search" aria-hidden="true"></i>
                            <input
                                id="offersCatalogSearchPlace"
                                name="place"
                                type="text"
                                class="form-control"
                                placeholder="{{ __('offers.search_where_placeholder') }}"
                                value="{{ $placeValue }}"
                                autocomplete="off"
                            >
                        </span>
                        <input type="hidden" id="LocationLatOffersCatalog" name="placeLat" value="{{ request()->placeLat }}">
                        <input type="hidden" id="LocationLngOffersCatalog" name="placeLng" value="{{ request()->placeLng }}">
                        <input type="hidden" id="LocationCityOffersCatalog" name="city" value="{{ request()->city }}">
                        <input type="hidden" id="LocationCountryOffersCatalog" name="country" value="{{ request()->country }}">
                        <input type="hidden" id="LocationRegionOffersCatalog" name="region" value="{{ request()->region }}">
                        @include('layouts.partials.geosearch-hidden-fields')
                    </label>

                    <div class="offers-page-header__segment offers-page-header__segment--who" data-offers-who>
                        <span class="offers-page-header__segment-label" id="offersWhoLabel">{{ __('offers.search_who') }}</span>
                        <div
                            class="offers-persons-stepper offers-persons-stepper--catalog"
                            data-offers-persons-stepper
                            role="group"
                            aria-labelledby="offersWhoLabel"
                        >
                            <button type="button" class="offers-persons-stepper__btn" data-offers-persons-delta="-1" aria-label="-">−</button>
                            <div class="offers-persons-stepper__value">
                                <i class="fa fa-user" aria-hidden="true"></i>
                                <span data-offers-persons-label>{{ trans_choice('offers.persons_count', $offersGuests, ['count' => $offersGuests]) }}</span>
                            </div>
                            <input type="hidden" name="num_guests" value="{{ $offersGuests }}" data-offers-persons-input>
                            <button type="button" class="offers-persons-stepper__btn" data-offers-persons-delta="1" aria-label="+">+</button>
                        </div>
                    </div>

                    <button type="submit" class="offers-page-header__search-btn">
                        <i class="fas fa-search d-md-none" aria-hidden="true"></i>
                        <span class="d-none d-md-inline">{{ __('offers.search_submit') }}</span>
                        <span class="visually-hidden d-md-none">{{ __('offers.search_submit') }}</span>
                    </button>
                </div>
            </form>

            <nav class="offers-page-header__breadcrumbs offers-page-header__anim" style="--offers-anim-i: 3" aria-label="Breadcrumb">
                <ol class="offers-page-header__crumb-list">
                    <li><a href="{{ route('welcome') }}">@lang('message.home')</a></li>
                    <li aria-hidden="true"><i class="fas fa-chevron-right"></i></li>
                    <li class="is-active" aria-current="page">{{ __('offers.breadcrumb') }}</li>
                </ol>
            </nav>
        </div>
    </section>
</div>
