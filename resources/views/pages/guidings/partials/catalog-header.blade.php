@php
    $placeValue = (request()->placeLat || request()->placelat) && (request()->placeLng || request()->placelng)
        ? request()->place
        : '';
    $guestsValue = request()->num_guests;
    $listingTitle = trim((string) ($listingTitle ?? __('homepage.listings-title')));
    $listingSubtitle = trim((string) ($listingSubtitle ?? ''));
    $breadcrumbLabel = ucwords(isset($place) && $place
        ? translate('Alle Guidings bei ').$place
        : translate('Alle Guidings'));
@endphp
<div class="guidings-page-header-shell" data-guidings-header-shell>
    @include('layouts.partials.site-nav', [
        'overlay' => false,
        'idPrefix' => 'guidings',
    ])

    <section class="guidings-page-header" data-guidings-page-header>
        <div class="guidings-page-header__band">
            <div class="guidings-page-header__inner guidings-page-header__inner--copy">
                <div class="guidings-page-header__copy">
                    <h1 class="guidings-page-header__title">{{ $listingTitle }}</h1>
                    @if($listingSubtitle !== '')
                        <p class="guidings-page-header__sub">{{ $listingSubtitle }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="guidings-page-header__inner guidings-page-header__inner--search">
            <form
                class="guidings-page-header__search"
                action="{{ route('guidings.index') }}"
                method="get"
                onsubmit="return validateSearch(event, 'guidingsCatalogSearchPlace')"
                data-guidings-header-search
            >
                <div class="guidings-page-header__search-box">
                    <label class="guidings-page-header__segment guidings-page-header__segment--where" for="guidingsCatalogSearchPlace">
                        <span class="guidings-page-header__segment-label">{{ __('offers.search_where') }}</span>
                        <span class="guidings-page-header__segment-control">
                            <i class="fas fa-search" aria-hidden="true"></i>
                            <input
                                id="guidingsCatalogSearchPlace"
                                name="place"
                                type="text"
                                class="form-control"
                                placeholder="{{ __('homepage.searchbar-destination') }}"
                                value="{{ $placeValue }}"
                                autocomplete="off"
                            >
                        </span>
                        <input type="hidden" id="LocationLatGuidingsCatalog" name="placeLat" value="{{ request()->placeLat }}">
                        <input type="hidden" id="LocationLngGuidingsCatalog" name="placeLng" value="{{ request()->placeLng }}">
                        <input type="hidden" id="LocationCityGuidingsCatalog" name="city" value="{{ request()->city }}">
                        <input type="hidden" id="LocationCountryGuidingsCatalog" name="country" value="{{ request()->country }}">
                        <input type="hidden" id="LocationRegionGuidingsCatalog" name="region" value="{{ request()->region }}">
                        @include('layouts.partials.geosearch-hidden-fields')
                    </label>

                    <label class="guidings-page-header__segment guidings-page-header__segment--who" for="guidingsCatalogGuests">
                        <span class="guidings-page-header__segment-label">{{ __('offers.search_who') }}</span>
                        <span class="guidings-page-header__segment-control">
                            <i class="fa fa-user" aria-hidden="true"></i>
                            <input
                                id="guidingsCatalogGuests"
                                type="number"
                                min="1"
                                max="5"
                                class="form-control"
                                name="num_guests"
                                placeholder="{{ __('homepage.searchbar-person') }}"
                                value="{{ $guestsValue }}"
                            >
                        </span>
                    </label>

                    <div class="guidings-page-header__segment guidings-page-header__segment--fish">
                        <span class="guidings-page-header__segment-label">{{ __('homepage.searchbar-targetfish') }}</span>
                        <span class="guidings-page-header__segment-control guidings-page-header__segment-control--tagify">
                            <i class="fa fa-fish tagify-fish-icon" aria-hidden="true"></i>
                            <input
                                class="tagify-fish-input"
                                id="tagify-fish-guidings-catalog"
                                placeholder="{{ __('homepage.searchbar-targetfish') }}..."
                            >
                        </span>
                    </div>

                    <button type="submit" class="guidings-page-header__search-btn">
                        <i class="fas fa-search d-lg-none" aria-hidden="true"></i>
                        <span class="d-none d-lg-inline">{{ __('homepage.searchbar-search') }}</span>
                        <span class="visually-hidden d-lg-none">{{ __('homepage.searchbar-search') }}</span>
                    </button>
                </div>
            </form>

            <nav class="guidings-page-header__breadcrumbs" aria-label="Breadcrumb">
                <ol class="guidings-page-header__crumb-list">
                    <li><a href="{{ route('welcome') }}">@lang('message.home')</a></li>
                    <li aria-hidden="true"><i class="fas fa-chevron-right"></i></li>
                    <li class="is-active" aria-current="page">{{ $breadcrumbLabel }}</li>
                </ol>
            </nav>
        </div>
    </section>
</div>
