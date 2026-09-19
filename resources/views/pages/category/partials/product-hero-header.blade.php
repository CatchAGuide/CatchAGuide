@php
    use App\Domain\Offers\OfferListingFilter;
    use App\Services\Search\ListingSearchStateService;

    $listingTitle = trim((string) ($listingTitle ?? ''));
    $locationLabel = trim((string) ($locationLabel ?? ''));
    $mapHref = trim((string) ($mapHref ?? '#map'));
    $ratingScore = $ratingScore ?? null;
    $reviewsCount = (int) ($reviewsCount ?? 0);
    $breadcrumbItems = $breadcrumbItems ?? [];
    $searchAction = listing_search_action($searchAction ?? null);
    $offersGuests = max(1, min(OfferListingFilter::MAX_GUESTS, (int) (request()->num_guests ?: OfferListingFilter::DEFAULT_GUESTS)));
    $requestHasPlace = (request()->placeLat || request()->placelat) && (request()->placeLng || request()->placelng);

    if (! $requestHasPlace && trim((string) ($placeValue ?? '')) === '') {
        $persistedSearch = app(ListingSearchStateService::class)->current();
        if ($persistedSearch['place'] !== '' || $persistedSearch['country'] !== '') {
            $placeValue = $persistedSearch['place'] !== '' ? $persistedSearch['place'] : $persistedSearch['country'];
            $placeLat = $persistedSearch['placeLat'];
            $placeLng = $persistedSearch['placeLng'];
            $placeCity = $persistedSearch['city'];
            $placeCountry = $persistedSearch['country'];
            $placeRegion = $persistedSearch['region'];
        }
    }

    $placeValue = $requestHasPlace ? request()->place : (string) ($placeValue ?? '');
    $placeLat = $requestHasPlace ? request()->placeLat : ($placeLat ?? '');
    $placeLng = $requestHasPlace ? request()->placeLng : ($placeLng ?? '');
    $placeCity = $requestHasPlace ? request()->city : ($placeCity ?? '');
    $placeCountry = $requestHasPlace ? request()->country : ($placeCountry ?? '');
    $placeRegion = $requestHasPlace ? request()->region : ($placeRegion ?? '');
    $headerCarry = OfferListingFilter::headerCarryParams(
        request()->query(),
        isset($lockedParams) && is_array($lockedParams) ? $lockedParams : [],
    );
    $hasRating = $ratingScore !== null && $ratingScore !== '' && (float) $ratingScore > 0;

    $mobileSearchSummary = trim(collect([
        $placeValue !== '' ? $placeValue : __('offers.search_mobile_summary_empty'),
        trans_choice('offers.persons_count', $offersGuests, ['count' => $offersGuests]),
    ])->filter()->implode(' · '));
    $popularPlaces = __('offers.search_mobile_popular_places');
    $searchSuggestionChips = collect([$placeValue, $locationLabel])
        ->concat(is_array($popularPlaces) ? $popularPlaces : [])
        ->map(fn ($label) => trim((string) $label))
        ->filter()
        ->unique(fn ($label) => mb_strtolower($label))
        ->take(4)
        ->values();
@endphp
<div class="offers-page-header-shell cag-site-nav-shell" data-category-header-shell data-product-hero-header>
    @include('layouts.partials.site-nav', [
        'overlay' => true,
        'idPrefix' => 'category',
    ])

    <section class="offers-page-header offers-page-header--product" data-category-page-header>
        <div class="offers-page-header__hero" data-category-hero>
            <div class="offers-page-header__inner offers-page-header__inner--hero">
                <div class="offers-page-header__copy">
                    @if(count($breadcrumbItems) > 0)
                        <nav class="offers-page-header__breadcrumbs offers-page-header__anim" style="--offers-anim-i: 0" aria-label="Breadcrumb">
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
                    @endif

                    <h1 class="offers-page-header__title offers-page-header__anim" style="--offers-anim-i: 1">{{ $listingTitle }}</h1>

                    @if($locationLabel !== '')
                        <p class="offers-page-header__place offers-page-header__anim" style="--offers-anim-i: 2">
                            <span>{{ $locationLabel }}</span>
                            <a href="{{ $mapHref }}">{{ __('guidings.show_on_map') }}</a>
                        </p>
                    @endif

                    <div class="offers-page-header__rating offers-page-header__anim" style="--offers-anim-i: 3">
                        @if($hasRating)
                            <span class="offers-page-header__score rating-value rating-clickable" id="rating-score-link">{{ one($ratingScore) }}</span>
                            <a href="#ratings-container" id="reviews-link" class="offers-page-header__reviews">
                                {{ trans_choice('offers.reviews_count', $reviewsCount, ['count' => $reviewsCount]) }}
                            </a>
                        @else
                            <span class="offers-page-header__reviews">{{ __('guidings.no_reviews') }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="offers-page-header__inner offers-page-header__inner--search">
            <x-mobile-search-sheet
                :trigger-label="__('offers.search_mobile_trigger_label')"
                :summary="$mobileSearchSummary"
                :sheet-title="__('offers.search_mobile_sheet_title_tours')"
                sheet-id="guidingMobileSearchSheet"
            >
            <form
                class="offers-page-header__search offers-page-header__anim"
                style="--offers-anim-i: 4"
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
                            <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
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
                            class="offers-persons-stepper offers-persons-stepper--catalog offers-persons-stepper--popup"
                            data-offers-persons-stepper
                            data-offers-persons-popup
                            role="group"
                            aria-labelledby="categoryHeroWhoLabel"
                        >
                            <button
                                type="button"
                                class="offers-persons-stepper__trigger"
                                data-offers-persons-popup-toggle
                                aria-expanded="false"
                                aria-controls="categoryHeroPersonsPopup"
                            >
                                <span data-offers-persons-label>{{ trans_choice('offers.persons_count', $offersGuests, ['count' => $offersGuests]) }}</span>
                            </button>
                            <div
                                id="categoryHeroPersonsPopup"
                                class="offers-persons-stepper__popup"
                                data-offers-persons-popup-panel
                                hidden
                            >
                                <button type="button" class="offers-persons-stepper__btn" data-offers-persons-delta="-1" aria-label="−">−</button>
                                <button type="button" class="offers-persons-stepper__btn" data-offers-persons-delta="1" aria-label="+">+</button>
                            </div>
                            <input type="hidden" name="num_guests" value="{{ $offersGuests }}" data-offers-persons-input>
                        </div>
                    </div>

                    @if($searchSuggestionChips->isNotEmpty())
                        <div class="mobile-search-sheet__chips">
                            @foreach($searchSuggestionChips as $chipLabel)
                                <button
                                    type="button"
                                    class="mobile-search-sheet__chip"
                                    data-mobile-search-chip="{{ $chipLabel }}"
                                >{{ $chipLabel }}</button>
                            @endforeach
                        </div>
                    @endif

                    <button type="submit" class="offers-page-header__search-btn">
                        <span class="offers-page-header__search-btn-label offers-page-header__search-btn-label--compact">{{ __('offers.search_change') }}</span>
                        <span class="offers-page-header__search-btn-label offers-page-header__search-btn-label--sheet">{{ __('offers.search_submit') }}</span>
                        <i class="fas fa-arrow-right offers-page-header__search-btn-arrow" aria-hidden="true"></i>
                    </button>
                </div>
            </form>
            </x-mobile-search-sheet>
        </div>
    </section>
</div>

@include('layouts.partials.mobile-search-sheet-script')
