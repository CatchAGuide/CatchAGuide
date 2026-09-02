@php
    use App\Domain\Vacation\CountrySlug;
    use App\Domain\Vacation\VacationListingFilter;

    $vacationDestinations = app(\App\Repositories\Vacation\VacationDestinationRepository::class);
    $vacationCountryOptions = $vacationDestinations->countriesForSearch();
    $isVacationProductPage = request()->routeIs(
        'vacations.trips.show',
        'vacations.camps.show',
        'vacations.show',
        'trips.show',
        'vacations.v2',
    );
    $currentVacationCountry = $currentVacationCountry
        ?? (request()->routeIs('vacations.all-offers')
            ? 'all-offers'
            : ($isVacationProductPage
                ? null
                : (request()->route('country')
                    ?? request()->route('slug')
                    ?? request('country'))));
    if (is_string($currentVacationCountry) && $currentVacationCountry !== '' && $currentVacationCountry !== 'all-offers') {
        $canonicalCountry = CountrySlug::canonicalize($currentVacationCountry) ?? $currentVacationCountry;
        $matchedCountry = $vacationCountryOptions->firstWhere('slug', $canonicalCountry)
            ?? $vacationCountryOptions->first(function ($country) use ($canonicalCountry) {
                if (CountrySlug::canonicalize($country->name) === $canonicalCountry) {
                    return true;
                }
                foreach (CountrySlug::storageVariants($country->slug) as $variant) {
                    if (CountrySlug::canonicalize($variant) === $canonicalCountry) {
                        return true;
                    }
                }

                return false;
            });
        $currentVacationCountry = $matchedCountry->slug ?? $canonicalCountry;
    }
    $listingTitle = trim((string) ($listingTitle ?? __('vacations.hub_header_title')));
    $listingSubtitle = trim((string) ($listingSubtitle ?? __('vacations.hub_header_subtitle')));
    $titleTag = in_array($titleTag ?? 'h1', ['h1', 'p', 'div'], true) ? ($titleTag ?? 'h1') : 'h1';
    $headerEyebrow = trim((string) ($headerEyebrow ?? ''));
    $breadcrumbItems = $breadcrumbItems ?? [
        ['label' => __('vacations.hub_breadcrumb'), 'url' => null],
    ];
    // Product pages (trip/camp show) render this header with titleTag "p" and have no
    // listing to filter — only catalog pages (hub/country/pillar, titleTag "h1") search.
    $showVacationPersonsFilter = $titleTag === 'h1';
    $vacationGuestsValue = max(1, min(
        VacationListingFilter::MAX_GUESTS,
        (int) (request()->num_guests ?: VacationListingFilter::DEFAULT_GUESTS)
    ));
@endphp
<div class="vacations-page-header-shell cag-site-nav-shell" data-vacations-header-shell>
    @include('layouts.partials.site-nav', [
        'overlay' => true,
        'idPrefix' => 'vacations',
    ])

    <section class="vacations-page-header" data-vacations-page-header>
        <div class="vacations-page-header__band" data-vacations-header-band>
            <div class="vacations-page-header__inner vacations-page-header__inner--copy">
                <div class="vacations-page-header__copy">
                    @if($headerEyebrow !== '')
                        <p class="vacations-page-header__eyebrow">{{ $headerEyebrow }}</p>
                    @endif
                    <{{ $titleTag }} class="vacations-page-header__title">{{ $listingTitle }}</{{ $titleTag }}>
                    @if($listingSubtitle !== '')
                        <x-title-rule theme="dark" />
                        <p class="vacations-page-header__sub">{{ $listingSubtitle }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="vacations-page-header__inner vacations-page-header__inner--search">
            <form
                id="vacations-catalog-search"
                class="vacations-page-header__search"
                action="{{ route('vacations.index') }}"
                method="get"
                data-vacations-header-search
            >
                <div class="vacations-page-header__search-box">
                    <label class="vacations-page-header__segment vacations-page-header__segment--country" for="vacationsCatalogCountry">
                        <span class="vacations-page-header__segment-label">{{ __('offers.search_where') }}</span>
                        <span class="vacations-page-header__segment-control">
                            <i class="fa fa-globe" aria-hidden="true"></i>
                            <select
                                id="vacationsCatalogCountry"
                                class="form-select vacations-page-header__country-select"
                                name="country"
                                data-vacations-country-select
                            >
                                <option value="">{{ __('vacations.catalog_header_country_select_placeholder') }}</option>
                                <option value="all-offers" {{ ($currentVacationCountry ?? '') === 'all-offers' ? 'selected' : '' }}>
                                    {{ __('vacations.all_offers_nav') }}
                                </option>
                                @foreach($vacationCountryOptions as $country)
                                    <option
                                        value="{{ $country->slug }}"
                                        {{ ($currentVacationCountry ?? '') === $country->slug ? 'selected' : '' }}
                                    >
                                        {{ translate($country->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </span>
                    </label>

                    @if($showVacationPersonsFilter)
                        <div class="vacations-page-header__segment vacations-page-header__segment--persons" data-vacations-persons>
                            <span class="vacations-page-header__segment-label" id="vacationsWhoLabel">{{ __('offers.search_who') }}</span>
                            <div
                                class="offers-persons-stepper offers-persons-stepper--catalog"
                                data-offers-persons-stepper
                                role="group"
                                aria-labelledby="vacationsWhoLabel"
                            >
                                <button type="button" class="offers-persons-stepper__btn" data-offers-persons-delta="-1" aria-label="-">−</button>
                                <div class="offers-persons-stepper__value">
                                    <i class="fa fa-user" aria-hidden="true"></i>
                                    <span data-offers-persons-label>{{ trans_choice('offers.persons_count', $vacationGuestsValue, ['count' => $vacationGuestsValue]) }}</span>
                                </div>
                                <input type="hidden" name="num_guests" value="{{ $vacationGuestsValue }}" data-offers-persons-input>
                                <button type="button" class="offers-persons-stepper__btn" data-offers-persons-delta="1" aria-label="+">+</button>
                            </div>
                        </div>
                    @endif

                    <button type="submit" class="vacations-page-header__search-btn">
                        <span>{{ __('homepage.searchbar-search') }}</span>
                        <i class="fas fa-arrow-right" aria-hidden="true"></i>
                    </button>
                </div>
            </form>
        </div>

        <nav class="vacations-page-header__breadcrumbs" aria-label="Breadcrumb">
            <ol class="vacations-page-header__crumb-list">
                <li><a href="{{ route('welcome') }}">@lang('message.home')</a></li>
                @foreach($breadcrumbItems as $crumb)
                    <li aria-hidden="true"><i class="fas fa-chevron-right"></i></li>
                    @if(!empty($crumb['url']))
                        <li><a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a></li>
                    @else
                        <li class="is-active" aria-current="page">{{ $crumb['label'] }}</li>
                    @endif
                @endforeach
            </ol>
        </nav>
    </section>
</div>

@if($showVacationPersonsFilter)
    @include('layouts.partials.offers-persons-stepper-script')
@endif

@once
<script>
(function () {
    var form = document.getElementById('vacations-catalog-search');
    var select = form ? form.querySelector('[data-vacations-country-select]') : null;
    if (!form || !select) {
        return;
    }

    var overlay = document.getElementById('vacation-page-loading-overlay');
    function showLoader() {
        if (!overlay) {
            return;
        }
        overlay.hidden = false;
        document.body.style.overflow = 'hidden';
    }

    // Any of these present on the current URL counts as "another filter is active" and forces
    // a switch to /offers (with the new country applied) instead of jumping to the sibling
    // /vacations/{country} page, which would otherwise silently drop them.
    var vacationOtherFilterKeys = ['species', 'accommodation_type', 'has_guiding', 'has_rental_boat', 'duration', 'sortby', 'num_guests', 'place', 'city', 'region', 'placeLat', 'placeLng', 'pillar'];
    // num_guests is only "owned" by this form (and safe to drop from the check below) on
    // pages that render the persons stepper — product pages have no such field, so a
    // num_guests already on the URL there must still trigger the /offers fallback.
    if (form.querySelector('[data-offers-persons-input]')) {
        vacationOtherFilterKeys = vacationOtherFilterKeys.filter(function (key) {
            return key !== 'num_guests';
        });
    }
    var offersIndexUrl = @json(route('offers.index'));

    function vacationOtherFiltersActive() {
        var params = new URLSearchParams(window.location.search);
        for (var i = 0; i < vacationOtherFilterKeys.length; i++) {
            var key = vacationOtherFilterKeys[i];
            var values = params.getAll(key).concat(params.getAll(key + '[]'));
            for (var j = 0; j < values.length; j++) {
                if ((values[j] || '').trim() !== '') {
                    return true;
                }
            }
        }
        return false;
    }

    function offersFallbackUrl(newCountrySlug) {
        var params = new URLSearchParams(window.location.search);
        var pillar = params.get('pillar');
        params.delete('pillar');
        params.set('country', newCountrySlug);
        params.set('type', 'vacation');
        if (pillar === 'trips' || pillar === 'camps') {
            params.set('vacation', pillar === 'trips' ? 'trip' : 'camp');
        }
        return offersIndexUrl + '?' + params.toString();
    }

    function goToCountry() {
        var selectedCountry = (select.value || '').trim().toLowerCase();
        if (selectedCountry === 'all-offers') {
            showLoader();
            window.location.href = @json(route('vacations.all-offers'));
            return;
        }
        if (selectedCountry && vacationOtherFiltersActive()) {
            showLoader();
            window.location.href = offersFallbackUrl(selectedCountry);
            return;
        }
        showLoader();
        if (selectedCountry) {
            form.action = @json(url('/vacations')) + '/' + encodeURIComponent(selectedCountry);
        } else {
            form.action = @json(route('vacations.index'));
        }
        form.submit();
    }

    select.addEventListener('change', goToCountry);
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        goToCountry();
    });

    var shell = document.querySelector('[data-vacations-header-shell]');
    var nav = shell ? shell.querySelector('.cag-site-nav') : null;
    var band = shell ? shell.querySelector('[data-vacations-header-band]') : null;
    if (nav && band) {
        function syncNavSolid() {
            nav.classList.toggle('is-solid', band.getBoundingClientRect().bottom <= nav.offsetHeight + 12);
        }
        window.addEventListener('scroll', syncNavSolid, { passive: true });
        window.addEventListener('resize', syncNavSolid);
        syncNavSolid();
    }
})();
</script>
@endonce
