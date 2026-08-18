{{-- Slate title + search band for inner pages that do not own a catalog hero. --}}
@php
    use App\Domain\Offers\OfferListingFilter;

    $hasPageTitle = $__env->hasSection('header_title');
    $offersGuests = max(1, min(OfferListingFilter::MAX_GUESTS, (int) (request()->num_guests ?: OfferListingFilter::DEFAULT_GUESTS)));
    $requestHasPlace = (request()->placeLat || request()->placelat) && (request()->placeLng || request()->placelng);
    $placeValue = $requestHasPlace ? request()->place : '';
    $headerCarry = OfferListingFilter::headerCarryParams(request()->query());
@endphp
<div class="offers-page-header-shell cag-site-nav-shell" data-site-page-header-shell>
    @include('layouts.partials.site-nav', [
        'overlay' => true,
        'idPrefix' => 'page',
    ])

    <section class="offers-page-header" data-site-page-header>
        <div @class(['offers-page-header__hero', 'offers-page-header__hero--compact' => ! $hasPageTitle]) data-site-page-hero>
            @if($hasPageTitle)
                <div class="offers-page-header__inner offers-page-header__inner--hero">
                    <div class="offers-page-header__copy">
                        <h1 class="offers-page-header__title">@yield('header_title')</h1>
                        @hasSection('header_sub_title')
                            <p class="offers-page-header__sub">@yield('header_sub_title')</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <div class="offers-page-header__inner offers-page-header__inner--search">
            <form
                class="offers-page-header__search"
                action="{{ listing_search_action() }}"
                method="get"
                onsubmit="return validateSearch(event, 'sitePageSearchPlace')"
                data-site-page-header-search
            >
                @include('components.offers.partials.hidden-query-fields', ['query' => $headerCarry])
                <div class="offers-page-header__search-box">
                    <label class="offers-page-header__segment offers-page-header__segment--where" for="sitePageSearchPlace">
                        <span class="offers-page-header__segment-label">{{ __('offers.search_where') }}</span>
                        <span class="offers-page-header__segment-control">
                            <i class="fas fa-search" aria-hidden="true"></i>
                            <input
                                id="sitePageSearchPlace"
                                name="place"
                                type="text"
                                class="form-control"
                                placeholder="{{ __('offers.search_where_placeholder') }}"
                                value="{{ $placeValue }}"
                                autocomplete="off"
                            >
                        </span>
                        <input type="hidden" id="LocationLatSitePage" name="placeLat" value="{{ $requestHasPlace ? request()->placeLat : '' }}">
                        <input type="hidden" id="LocationLngSitePage" name="placeLng" value="{{ $requestHasPlace ? request()->placeLng : '' }}">
                        <input type="hidden" id="LocationCitySitePage" name="city" value="{{ $requestHasPlace ? request()->city : '' }}">
                        <input type="hidden" id="LocationCountrySitePage" name="country" value="{{ $requestHasPlace ? request()->country : '' }}">
                        <input type="hidden" id="LocationRegionSitePage" name="region" value="{{ $requestHasPlace ? request()->region : '' }}">
                        @include('layouts.partials.geosearch-hidden-fields')
                    </label>

                    <div class="offers-page-header__segment offers-page-header__segment--who" data-offers-who>
                        <span class="offers-page-header__segment-label" id="sitePageWhoLabel">{{ __('offers.search_who') }}</span>
                        <div
                            class="offers-persons-stepper offers-persons-stepper--catalog"
                            data-offers-persons-stepper
                            role="group"
                            aria-labelledby="sitePageWhoLabel"
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
                        <span>{{ __('offers.search_submit') }}</span>
                        <i class="fas fa-arrow-right" aria-hidden="true"></i>
                    </button>
                </div>
            </form>
        </div>
    </section>
</div>
@once
    @include('layouts.partials.offers-persons-stepper-script')
    <script>
    (function () {
        var shell = document.querySelector('[data-site-page-header-shell]');
        var hero = document.querySelector('[data-site-page-hero]');
        var nav = shell ? shell.querySelector('.cag-site-nav') : null;
        if (!nav || !hero) return;

        var syncNavSolid = function () {
            nav.classList.toggle('is-solid', hero.getBoundingClientRect().bottom <= nav.offsetHeight + 12);
        };
        syncNavSolid();
        window.addEventListener('scroll', syncNavSolid, { passive: true });
        window.addEventListener('resize', syncNavSolid);
    })();
    </script>
@endonce
