@php
    use App\Domain\Offers\OfferListingFilter;

    $listingEyebrow = trim((string) ($listingEyebrow ?? ''));
    $listingTitle = trim((string) ($listingTitle ?? ''));
    $listingSubtitle = trim((string) ($listingSubtitle ?? ''));
    $breadcrumbItems = $breadcrumbItems ?? [];
    $searchAction = listing_search_action($searchAction ?? null);
    $offersGuests = max(1, min(OfferListingFilter::MAX_GUESTS, (int) (request()->num_guests ?: OfferListingFilter::DEFAULT_GUESTS)));
    $requestHasPlace = (request()->placeLat || request()->placelat) && (request()->placeLng || request()->placelng);
    $placeValue = $requestHasPlace ? request()->place : (string) ($placeValue ?? '');
    $placeLat = $requestHasPlace ? request()->placeLat : ($placeLat ?? '');
    $placeLng = $requestHasPlace ? request()->placeLng : ($placeLng ?? '');
    $placeCity = $requestHasPlace ? request()->city : ($placeCity ?? '');
    $placeCountry = $requestHasPlace ? request()->country : ($placeCountry ?? '');
    $placeRegion = $requestHasPlace ? request()->region : ($placeRegion ?? '');
    $titleTag = in_array($titleTag ?? 'h1', ['h1', 'p', 'div'], true) ? ($titleTag ?? 'h1') : 'h1';
    $headerCarry = OfferListingFilter::headerCarryParams(
        request()->query(),
        isset($lockedParams) && is_array($lockedParams) ? $lockedParams : [],
    );
@endphp
<div class="offers-page-header-shell cag-site-nav-shell" data-category-header-shell>
    @include('layouts.partials.site-nav', [
        'overlay' => true,
        'idPrefix' => 'category',
    ])

    <section class="offers-page-header" data-category-page-header>
        <div class="offers-page-header__hero" data-category-hero>
            <div class="offers-page-header__inner offers-page-header__inner--hero">
                <div class="offers-page-header__copy">
                    @if($listingEyebrow !== '')
                        <p class="offers-page-header__eyebrow offers-page-header__anim" style="--offers-anim-i: 0">{{ $listingEyebrow }}</p>
                    @endif
                    <{{ $titleTag }} class="offers-page-header__title offers-page-header__anim" style="--offers-anim-i: 0">{{ $listingTitle }}</{{ $titleTag }}>
                    @if($listingSubtitle !== '')
                        <x-title-rule theme="dark" class="offers-page-header__anim" style="--offers-anim-i: 1" />
                        <p class="offers-page-header__sub offers-page-header__anim" style="--offers-anim-i: 1">{{ $listingSubtitle }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="offers-page-header__inner offers-page-header__inner--search">
            <form
                class="offers-page-header__search offers-page-header__anim"
                style="--offers-anim-i: 2"
                action="{{ $searchAction }}"
                method="get"
                onsubmit="return validateSearch(event, 'categoryHeroSearchPlace')"
                data-category-header-search
            >
                @include('components.offers.partials.hidden-query-fields', ['query' => $headerCarry])
                <div class="offers-page-header__search-box">
                    <label class="offers-page-header__segment offers-page-header__segment--where" for="categoryHeroSearchPlace">
                        <span class="offers-page-header__segment-label">{{ __('offers.search_where') }}</span>
                        <span class="offers-page-header__segment-control">
                            <i class="fas fa-search" aria-hidden="true"></i>
                            <input
                                id="categoryHeroSearchPlace"
                                name="place"
                                type="text"
                                class="form-control"
                                placeholder="{{ __('offers.search_where_placeholder') }}"
                                value="{{ $placeValue }}"
                                autocomplete="off"
                            >
                        </span>
                        <input type="hidden" id="LocationLatCategoryHero" name="placeLat" value="{{ $placeLat }}">
                        <input type="hidden" id="LocationLngCategoryHero" name="placeLng" value="{{ $placeLng }}">
                        <input type="hidden" id="LocationCityCategoryHero" name="city" value="{{ $placeCity }}">
                        <input type="hidden" id="LocationCountryCategoryHero" name="country" value="{{ $placeCountry }}">
                        <input type="hidden" id="LocationRegionCategoryHero" name="region" value="{{ $placeRegion }}">
                        @include('layouts.partials.geosearch-hidden-fields')
                    </label>

                    <div class="offers-page-header__segment offers-page-header__segment--who" data-offers-who>
                        <span class="offers-page-header__segment-label" id="categoryHeroWhoLabel">{{ __('offers.search_who') }}</span>
                        <div
                            class="offers-persons-stepper offers-persons-stepper--catalog"
                            data-offers-persons-stepper
                            role="group"
                            aria-labelledby="categoryHeroWhoLabel"
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

        <nav class="offers-page-header__breadcrumbs offers-page-header__anim" style="--offers-anim-i: 3" aria-label="Breadcrumb">
            <ol class="offers-page-header__crumb-list">
                <li><a href="{{ route('welcome') }}">@lang('message.home')</a></li>
                @foreach($breadcrumbItems as $item)
                    <li aria-hidden="true"><i class="fas fa-chevron-right"></i></li>
                    @if(! empty($item['url']))
                        <li><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>
                    @else
                        <li class="is-active" aria-current="page">{{ $item['label'] }}</li>
                    @endif
                @endforeach
            </ol>
        </nav>
    </section>
</div>
