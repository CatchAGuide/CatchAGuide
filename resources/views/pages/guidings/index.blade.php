@extends('layouts.app-v2-1')

@php
    // Generate SEO-friendly title and description
    $baseTitle = __('homepage.listings-title');
    $filteredTitle = '';
    
    // Add location to title if present
    // if (isset($place) && !empty($place)) {
    //     $filteredTitle = translate('Alle Guidings bei ') . $place;
    // } else {
    $filteredTitle = $baseTitle;
    // }
    
    // Add filter information to title if present
    $activeFilters = [];
    if (request()->has('target_fish')) $activeFilters[] = translate('Target Fish');
    if (request()->has('methods')) $activeFilters[] = translate('Fishing Methods');
    if (request()->has('water')) $activeFilters[] = translate('Water Types');
    if (request()->has('duration_types')) $activeFilters[] = translate('Duration');
    if (request()->has('num_persons')) $activeFilters[] = translate('Group Size');
    
    if (!empty($activeFilters)) {
        $filteredTitle .= ' - ' . implode(', ', $activeFilters);
    }
    
    // Generate description
    $description = translate('Find and book guided fishing trips online. Browse through our selection of professional fishing guides and tours.');
    if (isset($place) && !empty($place)) {
        $description = translate('Find guided fishing trips in ') . $place . translate('. Book professional fishing guides and tours online.');
    }
@endphp

@section('title', $filteredTitle)
@section('description', $description)

@section('header_title', $filteredTitle)
@section('header_sub_title', '')

<!-- Meta robots for filtered pages -->
@if(request()->has('target_fish') || request()->has('methods') || request()->has('water') || request()->has('duration_types') || request()->has('num_persons') || request()->has('price_min') || request()->has('price_max'))
    @section('meta_robots')
    <meta name="robots" content="NOINDEX, NOFOLLOW" />
    @endsection
@endif

@stack('guidingListingStyles')
@section('css_after')
<style>
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 15px;
        width: 100%;
    }

    .page-header {
        /*margin-top: -60px;*/
    }

    .page-header-bg-overly,
    .pager-header-bg {
        display: none;
    }

    .floating-search-container {
        position: relative;
        margin-top: -30px;
        z-index: 100;
    }

    .carousel.slide img {
        height: 250px;
        object-fit: cover;
        width: 100%;
        background: black;
    }

    .form-custom-input {
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 8px 12px;
        width: 100%;
    }

    .form-custom-input:focus {
        border-color: #e8604c;
        outline: none;
        box-shadow: 0 0 0 2px rgba(232,96,76,0.25);
    }

    a:hover {
        color: black;
    }
    .page-header-bg-overly {
        background-color: rgba(0,0,0,0);
    }
    .pager-header-bg {
        filter: none !important;
    }

    .carousel-item {
        aspect-ratio: 4/3;
        background-color: #f8f9fa;
    }

    .carousel-item-next, .carousel-item-prev, .carousel-item.active {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .form-custom-input{
    border: 1px solid #d4d5d6;
    border-radius: 5px;
    padding: 8px 10px;
    width:100%;
    }
    .form-control:focus{
       box-shadow: none;
    }
    .form-custom-input:focus-visible{
        border:0;
        outline:solid #e8604c 1px !important;
    }
    li.select2-selection__choice{
        background-color: #E8604C !important;
        color: #fff !important;
        border: 0 !important;
        font-size:14px;
        vertical-align: middle !important;
        margin-top:0 !important;
     
    }
    button.select2-selection__choice__remove{
        border: 0 !important;
        color: #fff !important;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover, .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:focus{
        background:none;
    }
    span.select2-selection.select2-selection--multiple{
    border: 1px solid #d4d5d6;
    border-radius: 5px;
    padding: 7px 10px;
    }
    .select2-selection--multiple:before {
    content: "";
    position: absolute;
    right: 7px;
    top: 42%;
    border-top: 5px solid #888;
    border-left: 4px solid transparent;
    border-right: 4px solid transparent;
    }

    .sort-row .form-select{
        width: auto;
    }

    @media only screen and (max-width: 450px) {
        #map-placeholder a.btn {
            top: 35%;
            left: 30%;
        }

        .page-header {
            margin-top: -60px!important;
        }
    }

    @media only screen and (min-width: 451px) and (max-width: 766px) {
        #map-placeholder a.btn {
            top: 40%;
            left: 35%;
        }

        .page-header {
            margin-top: -60px!important;
        }
    }
    @media only screen and (min-width: 767px) and (max-width: 991px) {
        #map-placeholder a.btn {
            top: 45%;
            left: 45%;
        }

        .page-header {
            margin-top: -30px!important;
        }
    }
    @media only screen and (min-width: 992px) {
        .page-header {
            margin-top: -30px!important;
        }
    }
    #radius{
        background: url("data:image/svg+xml,<svg height='10px' width='10px' viewBox='0 0 16 16' fill='%23808080' xmlns='http://www.w3.org/2000/svg'><path d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/></svg>") no-repeat;
        background-position: right 0.3rem center !important;
    }
    #num-guests{
        background: url("data:image/svg+xml,<svg height='10px' width='10px' viewBox='0 0 16 16' fill='%23808080' xmlns='http://www.w3.org/2000/svg'><path d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/></svg>") no-repeat;
        background-position: right 0.3rem center !important;
    }
    .custom-select:has(option:disabled:checked[hidden]) {
    color: gray;
    }
    .custom-select option{
        color:black;
    }

    .btn-outline-theme{
    color: #E8604C;
    border-color: #E8604C;
    }
    .new-bg{
        background:#313041;
    }
    #map-placeholder {
        position: relative;
        width:100%;
        height: 200px;
        background-image: url({{ url('') }}/assets/images/map-bg.png);
    }
    #map-placeholder a.btn {
        position: absolute;
        top: calc(50% - 19px);
        right: calc(50% - 81px);
    }
    #guidings-menu-search {
        position: absolute;
        top: 133px;
        z-index: 3;
    }
    #guidings-result {
        line-height: 14px;
    }
    .pac-container {
        z-index: 2000;
    }
    .cag-btn {
        background-color: #E8604C !important;
        color: #fff !important;
        border: 2px solid #E8604C !important;
    }
    .cag-btn-inverted {
        background-color: #313041 !important;
        color: #fff !important;
        border: 2px solid #313041 !important;
    }
    .mobile-selection-sfm {
        position: sticky;
        z-index: 10;
        top: 0;
        background-color: #fff;
        padding-top: 15px;
        padding-left: 15px;
        padding-right: 15px;
    }
    .guiding-item-price {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
    }

    .guiding-item-price h5 {
        margin: 0;
        white-space: nowrap;
        font-size: clamp(14px, 2vw, 18px);  /* Responsive font size between 14px and 18px */
    }

    .guiding-item-price span {
        display: inline-block;
        padding: 4px 8px;
    }

    .tours-list {
        position: relative;
        padding: 30px 0;  /* Reduced default padding */
    }

    @media only screen and (max-width: 767px) {
        .tours-list {
            padding: 15px 0;  /* Even smaller padding for mobile */
        }
    }

    .inclusions-price {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
        margin-top: 10px;
    }

    .guidings-inclusions-container {
        flex: 1;
        min-width: 0; /* Prevents flex item from overflowing */
    }

    .guidings-included {
        font-size: 14px;
    }

    .guidings-included strong {
        display: block;
    }

    .inclusions-list {
        display: flex;
        flex-wrap: wrap;
        max-width: 100%;
    }

    .inclusion-item {
        white-space: nowrap;
        padding: 2px 8px;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
    }

    .inclusion-item i {
        font-size: 10px;
        margin-right: 4px;
        /* color: #E8604C; */
    }

    .guiding-item-price {
        text-align: right;
        min-width: fit-content;
        padding-left: 10px;
    }

    @media (max-width: 767px) {
        .inclusions-price {
            flex-direction: column;
        }
        
        .guiding-item-price {
            width: 100%;
            text-align: left;
            padding-left: 0;
        }

        .inclusion-item {
            font-size: 13px; /* Larger font size for mobile */
            padding: 3px 10px;
        }
        
        .guidings-included strong {
            font-size: 14px;
        }
    }

    .guidings-item-title {
        margin-bottom: 10px;
    }

    .guidings-item-title h5 {
        font-size: clamp(18px, 2vw, 22px);
        margin-bottom: 5px;
    }

    .guidings-item-title span {
        display: block;
        font-size: 15px;
        color: #444;
        max-width: 100%;
    }

    /* Only apply truncation on desktop */
    @media (min-width: 768px) {
        .guidings-item-title span.truncate {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    }

    .guidings-item-title i {
        font-size: 13px;
        margin-right: 4px;
        color: #666;
    }

    .inclusion-item {
        font-size: 15px;
        white-space: nowrap;
        padding: 2px 8px;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
    }

    .guiding-item-price h5 {
        margin: 0;
        white-space: nowrap;
        font-size: clamp(14px, 2vw, 18px);  /* Responsive font size between 14px and 18px */
    }

    /* Mobile specific styles */
    @media (max-width: 767px) {
        .guidings-item-title h5 {
            font-size: 18px;
            margin-bottom: 0
        }
        
        .guidings-item-title span {
            font-size: 15px; /* Keeping font size readable on mobile */
        }

        .inclusion-item {
            font-size: 15px; /* Larger font size for mobile */
            padding: 3px 10px; /* Slightly larger padding for better touch targets */
        }
        
        .guiding-item-price h5 {
            font-size: 18px; /* Fixed size for better readability on mobile */
        }
        
        .guidings-included strong {
            font-size: 15px;
        }
    }

    #filter-loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.7);
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: center;
        transition: opacity 0.3s ease;
    }
    
    .listings-container.loading {
        opacity: 0.5;
        pointer-events: none;
    }
    .slider-label {
    position: absolute;
    top: -25px; /* Adjust as needed */
    transform: translateX(-50%);
    background-color: white;
    padding: 2px 5px;
    border-radius: 3px;
    font-size: 12px;
    color: black;
    white-space: nowrap;
}
</style>

@endsection
@section('custom_style')
@include('layouts.schema.listings')

<!-- Structured Data for Search Results -->
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "ItemList",
    "name": @json($filteredTitle),
    "description": @json($description),
    "url": @json(request()->url()),
    "numberOfItems": {{ $guidings->total() }},
    "itemListElement": [
        @foreach($guidings as $index => $guiding)
        {
            "@@type": "ListItem",
            "position": {{ ($guidings->currentPage() - 1) * $guidings->perPage() + $index + 1 }},
            "item": {
                "@@type": "TouristAttraction",
                "name": @json(translate($guiding->title)),
                "description": @json(translate($guiding->excerpt ?? '')),
                "url": @json(route('guidings.show', [$guiding->id, $guiding->slug])),
                "location": {
                    "@@type": "Place",
                    "name": @json($guiding->location)
                },
                "offers": {
                    "@@type": "Offer",
                    "price": @json($guiding->getLowestPrice()),
                    "priceCurrency": "EUR"
                }
            }
        }@if(!$loop->last),@endif
        @endforeach
    ]
}
</script>
@endsection
@section('content')
    <div class="container">
        <section class="page-header">
            <div class="page-header__bottom breadcrumb-container">
                <div class="page-header__bottom-inner">
                    <ul class="thm-breadcrumb list-unstyled">
                        <li><a href="{{ route('welcome') }}">@lang('message.home')</a></li>
                        <li><span><i class="fas fa-solid fa-chevron-right"></i></span></li>
                        <li class="active">
                            {{ucwords( isset($place) ? translate('Alle Guidings bei ') . $place : translate('Alle Guidings'))}}
                        </li>
                    </ul>
                </div>
            </div>
        </section>
    </div>

    <!--Tours List Start-->
    <section class="tours-list">
        <div class="container">
            <div class="row">
                <!-- Mobile Sort / Filter / Map Bar -->
                <div class="col-12 d-block d-sm-none mb-3 mobile-selection-sfm">
                    <div class="sfm-bar">

                        {{-- Sort --}}
                        <div class="sfm-bar__item">
                            <div class="dropdown w-100">
                                <button type="button"
                                        class="sfm-bar__btn dropdown-toggle w-100"
                                        data-bs-toggle="dropdown"
                                        data-bs-auto-close="true"
                                        aria-expanded="false">
                                    <span class="sfm-bar__icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM5 10a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1zM7 15a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1z"/>
                                        </svg>
                                    </span>
                                    <span class="sfm-bar__label">@lang('message.sortby')</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-start sfm-bar__dropdown">
                                    <li><a class="dropdown-item mobile-sort-option" href="javascript:void(0)" data-sort="newest">@lang('message.newest')</a></li>
                                    <li><a class="dropdown-item mobile-sort-option" href="javascript:void(0)" data-sort="price-asc">@lang('message.lowprice')</a></li>
                                    <li><a class="dropdown-item mobile-sort-option" href="javascript:void(0)" data-sort="short-duration">@lang('message.shortduration')</a></li>
                                    <li><a class="dropdown-item mobile-sort-option" href="javascript:void(0)" data-sort="long-duration">@lang('message.longduration')</a></li>
                                </ul>
                            </div>
                        </div>

                        <div class="sfm-bar__divider"></div>

                        {{-- Filter --}}
                        <div class="sfm-bar__item">
                            <button type="button"
                                    class="sfm-bar__btn"
                                    id="sfmFilterBtn"
                                    data-bs-toggle="offcanvas"
                                    data-bs-target="#offcanvasBottomSearch"
                                    aria-controls="offcanvasBottomSearch">
                                <span class="sfm-bar__icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L13 10.414V17a1 1 0 01-1.447.894l-4-2A1 1 0 017 15v-4.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd"/>
                                    </svg>
                                </span>
                                <span class="sfm-bar__label">@lang('message.filter')</span>
                                <span class="sfm-bar__badge" id="active-filter-counter"></span>
                            </button>
                        </div>

                        <div class="sfm-bar__divider"></div>

                        {{-- Map --}}
                        <div class="sfm-bar__item">
                            <a class="sfm-bar__btn sfm-bar__btn--map"
                               id="openMapModal"
                               data-bs-target="#mapModal"
                               data-bs-toggle="modal"
                               href="javascript:void(0)">
                                <span class="sfm-bar__icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                    </svg>
                                </span>
                                <span class="sfm-bar__label">@lang('destination.show_on_map')</span>
                            </a>
                        </div>

                    </div>
                </div>

                <!-- Desktop Filter -->
                <div id="filterCard" class="col-sm-12 col-lg-3">        
                    <div class="card mb-2 d-none d-sm-block">
                        <div id="map-placeholder">
                            <a class="btn btn-primary" id="openMapModal" data-bs-target="#mapModal" data-bs-toggle="modal" href="javascript:void(0)">@lang('destination.show_on_map')</a>
                        </div>
                    </div>            
                    @include('pages.guidings.includes.filters', ['formAction' => route('guidings.index')])
                </div>

                <div class="col-sm-12 col-lg-9">
                    <!-- Add search message display -->
                    @if(!empty($searchMessage))
                        <div class="alert alert-info mb-3" role="alert" id="search-message-title">
                            {{ str_replace('$countReplace', count($allGuidings), $searchMessage) }}
                        </div>
                    @endif

                    <!-- column-reverse-row-normal -->
                    <div class="row">
                        <div class="col-lg-12 col-sm-12">
                            <div class="tours-list__right">
                                <div class="tours-list__inner" id="guidings-list">
                                    @include('pages.guidings.partials.guiding-list')
                                    @if(count($guidings) && $guidings->hasPages())
                                        @if($guidings->previousPageUrl())
                                            <link rel="prev" href="{{ $guidings->previousPageUrl() }}" />
                                        @endif
                                        @if($guidings->nextPageUrl())
                                            <link rel="next" href="{{ $guidings->nextPageUrl() }}" />
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--Tours List End-->

    <div class="modal fade map-modal" id="mapModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
        <div class="modal-dialog map-modal__dialog">
            <div class="modal-content map-modal__content">

                {{-- Floating header bar over the map --}}
                <div class="map-modal__header">
                    <div class="map-modal__header-left">
                        <span class="map-modal__pin-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                        <div>
                            <h6 class="map-modal__title" id="mapModalLabel">@lang('destination.show_on_map')</h6>
                            @if(count($allGuidings) > 0)
                            <span class="map-modal__subtitle">{{ count($allGuidings) }} {{ count($allGuidings) == 1 ? translate('result') : translate('results') }}</span>
                            @endif
                        </div>
                    </div>
                    <button type="button" class="map-modal__close" data-bs-dismiss="modal" aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>

                {{-- Map fills the rest --}}
                @php
                    $mapCenterLat = request()->get('placeLat')
                        ?: (isset($guidings[0]) ? $guidings[0]->lat : config('services.maps.default_center.lat'));
                    $mapCenterLng = request()->get('placeLng')
                        ?: (isset($guidings[0]) ? $guidings[0]->lng : config('services.maps.default_center.lng'));

                    if ($allGuidings->isEmpty()) {
                        $mapSource = $otherguidings ?? collect();
                        $mapGrayIds = collect($mapSource)->pluck('id')->map(fn ($id) => (int) $id)->all();
                    } else {
                        $mapSource = $allGuidings;
                        if (isset($otherguidings) && count($otherguidings) > 0) {
                            $mapSource = $allGuidings->concat($otherguidings);
                        }
                        $mapGrayIds = isset($otherguidings)
                            ? collect($otherguidings)->pluck('id')->map(fn ($id) => (int) $id)->all()
                            : [];
                    }
                    $guidingMapMarkers = \App\Support\Maps\MapMarkerCollection::fromGuidings($mapSource, $mapGrayIds);
                @endphp
                <x-maps.listing
                    class="map-modal__map"
                    :markers="$guidingMapMarkers"
                    layout="modal"
                    modal-id="mapModal"
                    map-id="map"
                    height="100%"
                    :center="['lat' => (float) $mapCenterLat, 'lng' => (float) $mapCenterLng]"
                    instance-key="guidings"
                    :cluster="true"
                    :show-gray-nearby="true"
                    :single-zoom="12"
                    :default-zoom="5"
                    :lazy-modal="true"
                    :updatable="true"
                    :interactive-preview="true"
                />

            </div>
        </div>
    </div>
    <!-- Endmodal -->

    <br>

    @include('pages.guidings.includes.filters-mobile', ['formAction' => route('guidings.index')])
@endsection

@section('js_after')
<script>
    $('#sortby, #sortby-2').on('change', function() {
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('sortby', $(this).val());
        
        const newUrl = `${window.location.pathname}?${urlParams.toString()}`;
        window.location.href = newUrl;
    });

    initializeSelect2();

    function initializeSelect2() {
        var selectTarget = $('#target_fish, #target_fishOffCanvass');
        var selectWater = $('#water, #waterOffCanvass');
        var selectMethod = $('#methods, #methodsOffCanvass');

        // Target Fish
        selectTarget.append(new Option('@lang('message.choose')...', '', true, true));
        @foreach($alltargets as $target)
            var targetname = '{{$target->name}}';
            @if(app()->getLocale() == 'en')
                targetname = '{{$target->name_en}}';
            @endif
            selectTarget.append(new Option(targetname, '{{ $target->id }}', false, false));
        @endforeach

        // Water Types
        selectWater.append(new Option('@lang('message.choose')...', '', true, true));
        @foreach($guiding_waters as $water)
            var watername = '{{$water->name}}';
            @if(app()->getLocale() == 'en')
                watername = '{{$water->name_en}}';
            @endif
            selectWater.append(new Option(watername, '{{ $water->id }}', false, false));
        @endforeach

        // Fishing Methods
        selectMethod.append(new Option('@lang('message.choose')...', '', true, true));
        @foreach($guiding_methods as $method)
            var methodname = '{{$method->name}}';
            @if(app()->getLocale() == 'en')
                methodname = '{{$method->name_en}}';
            @endif
            selectMethod.append(new Option(methodname, '{{ $method->id }}', false, false));
        @endforeach

        // Initialize Select2 and set values
        $("#target_fish, #target_fishOffCanvass").select2({
            multiple: true,
            placeholder: '@lang('message.target-fish')',
            width: 'resolve',
        }).val(@json(request()->get('target_fish', [])))
        .trigger('change');

        $("#water, #waterOffCanvass").select2({
            multiple: true,
            placeholder: '@lang('message.body-type')',
            width: 'resolve'
        }).val(@json(request()->get('water', [])))
        .trigger('change');

        $("#methods, #methodsOffCanvass").select2({
            multiple: true,
            placeholder: '@lang('message.fishing-technique')',
            width: 'resolve'
        }).val(@json(request()->get('methods', [])))
        .trigger('change');

        // Set other form values if they exist
        @if(request()->get('price_range'))
            $('#price_range, #price_rangeOffCanvass').val('{{ request()->get('price_range') }}');
        @endif

        @if(request()->get('num_guests'))
            $('#num-guests, #num-guestsOffCanvass').val('{{ request()->get('num_guests') }}');
        @endif
    }

    // Prevent the filter button click from bubbling to document and re-closing the offcanvas
    (function () {
        var filterBtn = document.getElementById('sfmFilterBtn');
        if (filterBtn) {
            filterBtn.addEventListener('click', function (e) {
                e.stopPropagation();
            });
        }
    })();

    // Handle mobile sorting
    document.querySelectorAll('.mobile-sort-option').forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Get current URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            
            // Set the sort parameter
            urlParams.set('sortby', this.dataset.sort);
            
            // Redirect to the new URL with all parameters
            window.location.href = `${window.location.pathname}?${urlParams.toString()}`;
        });
    });
</script>

@php
    $listingMediaUsesObjectStorage = app(\App\Services\Media\MediaWriteStorageResolver::class)->usesObjectStorage();
    $listingMediaCdnBase = $listingMediaUsesObjectStorage
        ? rtrim((string) config('filesystems.disks.' . config('media_storage.disk', 'do_spaces') . '.url', ''), '/')
        : '';
    $listingMediaEnvPrefix = $listingMediaUsesObjectStorage
        ? app(\App\Services\Media\MediaEnvironmentResolver::class)->bucketPrefix()
        : '';
    $listingMediaLocalBase = rtrim(url('/'), '/');
    $listingMediaPlaceholder = media_url(null);
@endphp
<script>
    const listingMediaUsesObjectStorage = @json($listingMediaUsesObjectStorage);
    const listingMediaCdnBase = @json($listingMediaCdnBase);
    const listingMediaEnvPrefix = @json($listingMediaEnvPrefix);
    const listingMediaLocalBase = @json($listingMediaLocalBase);
    const listingMediaPlaceholder = @json($listingMediaPlaceholder);

    function resolveListingMediaUrl(path) {
        if (!path) {
            return listingMediaPlaceholder;
        }
        if (path.startsWith('http://') || path.startsWith('https://')) {
            return path;
        }
        const normalized = String(path).replace(/^\/+/, '');
        if (!listingMediaUsesObjectStorage) {
            return `${listingMediaLocalBase}/${normalized}`;
        }
        return listingMediaCdnBase
            ? `${listingMediaCdnBase}/${listingMediaEnvPrefix}/${normalized}`
            : `/${normalized}`;
    }

    // Leaflet ListingMap registers window.updateMapWithGuidings; enrich AJAX payloads with media URLs
    window.__cagEnrichGuidingsForMap = function (guidings) {
        return (guidings || []).map(function (guiding) {
            var price = guiding.lowest_price != null ? guiding.lowest_price : guiding.price;
            if (price !== null && price !== '' && Number(price) <= 0) {
                price = null;
            }
            return Object.assign({}, guiding, {
                thumbnail: guiding.thumbnail_path
                    ? resolveListingMediaUrl(guiding.thumbnail_path)
                    : listingMediaPlaceholder,
                url: guiding.id && guiding.slug
                    ? `/guidings/${guiding.id}/${guiding.slug}`
                    : (guiding.url || '#'),
                lowest_price: price,
                price: price,
                priceLabel: price != null ? ('ab ' + price + '€ p.P.') : null,
                badge: guiding.badge || @json(function_exists('translate') ? translate('Guiding') : 'Guiding'),
                cta: guiding.cta || @json(__('vacations.view_details')),
                pillar: guiding.pillar || 'guiding',
            });
        });
    };

    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(showPosition);
        } else {
            console.log('Geolocation is not supported by this browser.');
        }
    }

    function showPosition(position) {
        var lat = position.coords.latitude;
        var lng = position.coords.longitude;
        document.getElementById('placeLat').value = lat;
        document.getElementById('placeLng').value = lng;
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/core-js-bundle@3.30.2/minified.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {

    function attachFilterRemoveListeners() {
        document.querySelectorAll('.active-filters .btn-close').forEach(button => {
            button.addEventListener('click', function() {
                const filterType = this.dataset.filterType;
                const filterId = this.dataset.filterId;
                
                // Find and uncheck the corresponding checkbox in both desktop and mobile filter panels
                const desktopCheckbox = document.querySelector(`#filterContainer input[name="${filterType}[]"][value="${filterId}"]`);
                const mobileCheckbox = document.querySelector(`#filterContainerOffCanvas input[name="${filterType}[]"][value="${filterId}"]`);
                
                // Uncheck both checkboxes if they exist
                if (desktopCheckbox) {
                    desktopCheckbox.checked = false;
                }
                
                if (mobileCheckbox) {
                    mobileCheckbox.checked = false;
                }

                // Get the form from the mobile filter panel
                const mobileForm = document.getElementById('filterContainerOffCanvas');
                if (mobileForm) {
                    const formData = new FormData(mobileForm);
                    // Call updateResults with the form data
                    if (typeof window.updateResults === 'function') {
                        window.updateResults(formData);
                    }
                }
                
                // Remove all matching filter tags from both desktop and mobile views
                document.querySelectorAll(`.active-filters .badge`).forEach(badge => {
                    const badgeButton = badge.querySelector('.btn-close');
                    if (badgeButton.dataset.filterType === filterType && 
                        badgeButton.dataset.filterId === filterId) {
                        badge.remove();
                    }
                });
            });
        });
    }

    // Initial attachment of listeners
    attachFilterRemoveListeners();

    // Make sure reinitializeComponents includes reattaching filter listeners
    function reinitializeComponents() {
        document.querySelectorAll('.carousel').forEach(carousel => {
            new bootstrap.Carousel(carousel, {
                interval: false
            });
        });
        
        // Reattach filter remove listeners
        attachFilterRemoveListeners();
    }

    // Make functions available globally
    window.reinitializeComponents = reinitializeComponents;
    window.attachFilterRemoveListeners = attachFilterRemoveListeners;
    
    updateActiveFilterCounter();

    function updateActiveFilterCounter() {
        const urlParams = new URLSearchParams(window.location.search);
        let activeFilterCount = 0;

        // List of filter parameters to check
        const filterParams = ['target_fish[]', 'methods[]', 'water[]', 'duration_types[]', 'num_persons'];

        filterParams.forEach(param => {
            if (urlParams.has(param)) {
                const values = urlParams.getAll(param);
                if (values.length > 0) {
                    activeFilterCount += values.length;
                }
            }
        });

        // Check price range separately
        const defaultMinPrice = 50;
        const defaultMaxPrice = {{ isset($overallMaxPrice) ? $overallMaxPrice : 1000 }}; // Use the actual max price from controller
        const priceMin = parseInt(urlParams.get('price_min'));
        const priceMax = parseInt(urlParams.get('price_max'));
        
        if (priceMin && priceMin !== defaultMinPrice) activeFilterCount++;
        if (priceMax && priceMax !== defaultMaxPrice) activeFilterCount++;

        // Update the counter
        const filterCounter = document.getElementById('active-filter-counter');
        if (activeFilterCount > 0) {
            filterCounter.textContent = activeFilterCount;
            filterCounter.style.display = 'inline-block';
        } else {
            filterCounter.style.display = 'none';
        }
    }
});
</script>

<script>
// Global lazy loading observer
let lazyImageObserver = null;

function initLazyLoading() {
    // Clean up existing observer
    if (lazyImageObserver) {
        lazyImageObserver.disconnect();
    }

    var lazyImages = [].slice.call(document.querySelectorAll("img.lazy"));

    if ("IntersectionObserver" in window) {
        lazyImageObserver = new IntersectionObserver(function(entries, observer) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    let lazyImage = entry.target;
                    lazyImage.src = lazyImage.dataset.src;
                    lazyImage.classList.remove("lazy");
                    lazyImageObserver.unobserve(lazyImage);
                }
            });
        });

        lazyImages.forEach(function(lazyImage) {
            lazyImageObserver.observe(lazyImage);
        });
    } else {
        // Fallback for browsers that don't support IntersectionObserver
        let active = false;

        const lazyLoad = function() {
            if (active === false) {
                active = true;

                setTimeout(function() {
                    lazyImages.forEach(function(lazyImage) {
                        if ((lazyImage.getBoundingClientRect().top <= window.innerHeight && lazyImage.getBoundingClientRect().bottom >= 0) && getComputedStyle(lazyImage).display !== "none") {
                            lazyImage.src = lazyImage.dataset.src;
                            lazyImage.classList.remove("lazy");

                            lazyImages = lazyImages.filter(function(image) {
                                return image !== lazyImage;
                            });

                            if (lazyImages.length === 0) {
                                document.removeEventListener("scroll", lazyLoad);
                                window.removeEventListener("resize", lazyLoad);
                                window.removeEventListener("orientationchange", lazyLoad);
                            }
                        }
                    });

                    active = false;
                }, 200);
            }
        };

        document.addEventListener("scroll", lazyLoad);
        window.addEventListener("resize", lazyLoad);
        window.addEventListener("orientationchange", lazyLoad);
    }
}

// Initialize lazy loading on page load
document.addEventListener("DOMContentLoaded", function() {
    initLazyLoading();
});

// Make function globally available for filter updates
window.initLazyLoading = initLazyLoading;
</script>

<script>
// Mobile carousel counter: update "1/N" badge on slide change
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-counter-for]').forEach(function (counter) {
        var carouselId = counter.getAttribute('data-counter-for');
        var total      = parseInt(counter.getAttribute('data-total'), 10);
        var carousel   = document.getElementById(carouselId);

        if (!carousel) return;

        carousel.addEventListener('slide.bs.carousel', function (e) {
            counter.textContent = (e.to + 1) + '/' + total;
        });
    });
});
</script>

@endsection

@push('guidingListingScripts')
<script>
// ── Guiding Gallery Lightbox ──────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-guiding-gallery]').forEach(function (carousel) {
        const guidingId = carousel.getAttribute('data-guiding-gallery');
        const images    = JSON.parse(carousel.getAttribute('data-gallery-images') || '[]');
        const modal     = document.querySelector('[data-guiding-modal="' + guidingId + '"]');

        if (!modal || images.length === 0) return;

        const modalImage   = modal.querySelector('.guiding-gallery-modal__image');
        const modalPrev    = modal.querySelector('.guiding-gallery-modal__prev');
        const modalNext    = modal.querySelector('.guiding-gallery-modal__next');
        const modalClose   = modal.querySelector('.guiding-gallery-modal__close');
        const modalCurrent = modal.querySelector('.guiding-gallery-modal__current');

        let currentIndex = 0;

        function showImage(index) {
            if (index < 0) index = images.length - 1;
            if (index >= images.length) index = 0;
            currentIndex = index;
            if (modalImage)   modalImage.src = images[currentIndex];
            if (modalCurrent) modalCurrent.textContent = currentIndex + 1;
        }

        function openModal(index) {
            showImage(index);
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }

        // Sync with Bootstrap carousel slide index
        carousel.addEventListener('slide.bs.carousel', function (e) {
            currentIndex = e.to;
        });

        // Click any image in the carousel to open modal
        carousel.querySelectorAll('[data-guiding-open-modal]').forEach(function (img, idx) {
            img.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                openModal(idx);
            });
        });

        if (modalClose) modalClose.addEventListener('click', closeModal);
        if (modalPrev)  modalPrev.addEventListener('click', function () { showImage(currentIndex - 1); });
        if (modalNext)  modalNext.addEventListener('click', function () { showImage(currentIndex + 1); });

        // Close on backdrop click
        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeModal();
        });

        // Close on Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.classList.contains('show')) closeModal();
        });
    });
});
</script>
@endpush

@stack('guidingListingScripts')