@php
    use App\Domain\Vacation\CountrySlug;

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
                    @if($headerEyebrow !== '')
                        <span class="vacations-page-header__divider" aria-hidden="true">
                            <span class="vacations-page-header__divider-dot"></span>
                            <span class="vacations-page-header__divider-line"></span>
                        </span>
                    @endif
                    @if($listingSubtitle !== '')
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
                                <option value="">{{ translate('Select Country') }}</option>
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

    function goToCountry() {
        showLoader();
        var selectedCountry = (select.value || '').trim().toLowerCase();
        if (selectedCountry === 'all-offers') {
            window.location.href = @json(route('vacations.all-offers'));
            return;
        }
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
