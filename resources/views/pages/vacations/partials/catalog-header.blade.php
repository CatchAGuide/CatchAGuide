@php
    $vacationDestinations = app(\App\Repositories\Vacation\VacationDestinationRepository::class);
    $vacationCountryOptions = $vacationDestinations->countriesForSearch();
    $currentVacationCountry = $currentVacationCountry
        ?? (request()->routeIs('vacations.all-offers')
            ? 'all-offers'
            : (request()->route('country')
                ?? request()->route('slug')
                ?? request('country')));
    $listingTitle = trim((string) ($listingTitle ?? __('vacations.hub_header_title')));
    $listingSubtitle = trim((string) ($listingSubtitle ?? __('vacations.hub_header_subtitle')));
    $breadcrumbItems = $breadcrumbItems ?? [
        ['label' => __('vacations.hub_breadcrumb'), 'url' => null],
    ];
@endphp
<div class="vacations-page-header-shell" data-vacations-header-shell>
    @include('layouts.partials.site-nav', [
        'overlay' => false,
        'idPrefix' => 'vacations',
    ])

    <section class="vacations-page-header" data-vacations-page-header>
        <div class="vacations-page-header__band">
            <div class="vacations-page-header__inner vacations-page-header__inner--copy">
                <div class="vacations-page-header__copy">
                    <h1 class="vacations-page-header__title">{{ $listingTitle }}</h1>
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
                        <i class="fas fa-search d-md-none" aria-hidden="true"></i>
                        <span class="d-none d-md-inline">{{ __('homepage.searchbar-search') }}</span>
                        <span class="visually-hidden d-md-none">{{ __('homepage.searchbar-search') }}</span>
                    </button>
                </div>
            </form>

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
        </div>
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
})();
</script>
@endonce
