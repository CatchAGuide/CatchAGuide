@php
    use App\Domain\Vacation\CountrySlug;
    use App\Domain\Vacation\VacationListingFilter;
    use App\Services\Search\ListingSearchStateService;

    $vacationDestinations = app(\App\Repositories\Vacation\VacationDestinationRepository::class);
    $vacationCountryOptions = $vacationDestinations->countriesForSearch();
    $titleTag = in_array($titleTag ?? 'h1', ['h1', 'p', 'div'], true) ? ($titleTag ?? 'h1') : 'h1';
    $isVacationListingPdp = $titleTag === 'p';
    $currentVacationCountry = $currentVacationCountry
        ?? (request()->routeIs('vacations.all-offers')
            ? 'all-offers'
            : ($isVacationListingPdp
                ? request('country')
                : (request()->route('country')
                    ?? request()->route('slug')
                    ?? request('country'))));

    // On a listing detail page, a location carried over from an earlier search takes
    // priority over the listing's own country -- but only as a fallback if it fails to
    // match a known destination below.
    $vacationCountryFallback = $currentVacationCountry;
    if ($isVacationListingPdp && ! request()->filled('country')) {
        $persistedSearch = app(ListingSearchStateService::class)->resolveFromRequest(request());
        $persistedLocation = $persistedSearch['country'] !== '' ? $persistedSearch['country'] : $persistedSearch['place'];
        if ($persistedLocation !== '') {
            $currentVacationCountry = $persistedLocation;
        }
    }

    $queryCountry = request('country');
    if ($isVacationListingPdp && is_string($queryCountry) && $queryCountry !== '') {
        $currentVacationCountry = $queryCountry;
    }
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
        $currentVacationCountry = $matchedCountry->slug ?? $vacationCountryFallback ?? $canonicalCountry;
    }
    $listingTitle = trim((string) ($listingTitle ?? __('vacations.hub_header_title')));
    $listingSubtitle = trim((string) ($listingSubtitle ?? __('vacations.hub_header_subtitle')));
    $headerEyebrow = trim((string) ($headerEyebrow ?? ''));
    $breadcrumbItems = $breadcrumbItems ?? [
        ['label' => __('vacations.hub_breadcrumb'), 'url' => null],
    ];
    // num_guests is intentionally not carried on crawlable links to a listing detail page (see
    // CLAUDE.md's "SEO / catalog page conventions") — fall back to the session-remembered search
    // (same ListingSearchStateService used for location above) instead of request()->num_guests
    // alone, so the prefill still works with a clean URL.
    $requestGuestsRaw = request()->filled('num_guests') ? request()->query('num_guests') : null;
    $sessionGuestsRaw = $requestGuestsRaw === null
        ? (app(ListingSearchStateService::class)->current()['numGuests'] ?: null)
        : null;
    $vacationGuestsValue = max(1, min(
        VacationListingFilter::MAX_GUESTS,
        (int) ($requestGuestsRaw ?? $sessionGuestsRaw ?? VacationListingFilter::DEFAULT_GUESTS)
    ));

    // Mobile-only pill + bottom-sheet search (see components/mobile-search-sheet.blade.php).
    // Opt-in only: the calling PDP (Camp/Trip) passes these explicitly, so the generic
    // vacations listing/hub pages -- and the legacy vacations/show.blade.php page, which
    // never passes them -- are completely unaffected.
    $enableMobileSearchSheet = (bool) ($enableMobileSearchSheet ?? false);
    $heroProductTitle = trim((string) ($heroProductTitle ?? ''));
    $heroLocationLabel = trim((string) ($heroLocationLabel ?? ''));
    $heroMapHref = trim((string) ($heroMapHref ?? '#map'));
    $showMobileProductHero = $enableMobileSearchSheet && $heroProductTitle !== '';
    $mobileSearchTriggerLabel = trim((string) ($mobileSearchTriggerLabel ?? __('offers.search_mobile_trigger_label')));
    $mobileSearchSheetTitle = trim((string) ($mobileSearchSheetTitle ?? __('offers.search_mobile_sheet_title')));

    $vacationCountryLabel = '';
    if (($currentVacationCountry ?? '') === 'all-offers') {
        $vacationCountryLabel = __('vacations.all_offers_nav');
    } elseif (is_string($currentVacationCountry ?? null) && $currentVacationCountry !== '') {
        $matchedCountryName = optional($vacationCountryOptions->firstWhere('slug', $currentVacationCountry))->name;
        $vacationCountryLabel = $matchedCountryName ? translate($matchedCountryName) : '';
    }
    $vacationMobileSearchSummary = trim(collect([
        $vacationCountryLabel !== '' ? $vacationCountryLabel : __('offers.search_mobile_summary_empty'),
        trans_choice('offers.persons_count', $vacationGuestsValue, ['count' => $vacationGuestsValue]),
    ])->filter()->implode(' · '));
@endphp
<div class="vacations-page-header-shell cag-site-nav-shell" data-vacations-header-shell>
    @include('layouts.partials.site-nav', [
        'overlay' => true,
        'idPrefix' => 'vacations',
    ])

    <section class="vacations-page-header @if($showMobileProductHero) vacations-page-header--product @endif" data-vacations-page-header>
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

                    @if($showMobileProductHero)
                        <p class="vacations-page-header__product-title">{{ $heroProductTitle }}</p>
                        @if($heroLocationLabel !== '')
                            <p class="vacations-page-header__place">
                                <span>{{ $heroLocationLabel }}</span>
                                <a href="{{ $heroMapHref }}">{{ __('vacations.show_on_map') }}</a>
                            </p>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <div class="vacations-page-header__inner vacations-page-header__inner--search">
            <x-mobile-search-sheet
                :enabled="$enableMobileSearchSheet"
                :trigger-label="$mobileSearchTriggerLabel"
                :summary="$vacationMobileSearchSummary"
                :sheet-title="$mobileSearchSheetTitle"
                sheet-id="vacationsMobileSearchSheet"
            >
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
                            <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
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

                    <button type="submit" class="vacations-page-header__search-btn">
                        <span class="vacations-page-header__search-btn-label vacations-page-header__search-btn-label--compact">{{ __('homepage.searchbar-search') }}</span>
                        <span class="vacations-page-header__search-btn-label vacations-page-header__search-btn-label--sheet">{{ __('offers.search_submit') }}</span>
                        <i class="fas fa-arrow-right vacations-page-header__search-btn-arrow" aria-hidden="true"></i>
                    </button>
                </div>
            </form>
            </x-mobile-search-sheet>
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

@include('layouts.partials.offers-persons-stepper-script')

@if($enableMobileSearchSheet)
    @include('layouts.partials.mobile-search-sheet-script')
@endif

@once
<script>
(function () {
    var form = document.getElementById('vacations-catalog-search');
    var select = form ? form.querySelector('[data-vacations-country-select]') : null;
    if (!form || !select) {
        return;
    }

    function showLoader() {
        if (window.PageLoader) {
            window.PageLoader.show();
        }
    }

    // Any of these present on the current URL counts as "another filter is active" and forces
    // a switch to /offers (with the new country applied) instead of jumping to the sibling
    // /vacations/{country} page, which would otherwise silently drop them.
    // num_guests is owned by this form on every vacation page (catalog + PDP).
    var vacationOtherFilterKeys = ['species', 'accommodation_type', 'has_guiding', 'has_rental_boat', 'duration', 'sortby', 'place', 'city', 'region', 'placeLat', 'placeLng', 'pillar'];
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
