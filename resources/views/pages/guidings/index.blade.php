@extends('layouts.app-v2-1')

@php
    // Generate SEO-friendly title and description
    $baseTitle = translate('Alle Guidings');
    $filteredTitle = '';
    
    // Add location to title if present
    if (isset($place) && !empty($place)) {
        $filteredTitle = translate('Alle Guidings bei ') . $place;
    } else {
        $filteredTitle = $baseTitle;
    }
    
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
    <meta name="robots" content="NOINDEX, FOLLOW" />
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
    "@context": "https://schema.org",
    "@type": "ItemList",
    "name": "{{ $filteredTitle }}",
    "description": "{{ $description }}",
    "url": "{{ request()->url() }}",
    "numberOfItems": {{ $guidings->total() }},
    "itemListElement": [
        @foreach($guidings as $index => $guiding)
        {
            "@type": "ListItem",
            "position": {{ ($guidings->currentPage() - 1) * $guidings->perPage() + $index + 1 }},
            "item": {
                "@type": "TouristAttraction",
                "name": "{{ translate($guiding->title) }}",
                "description": "{{ translate($guiding->excerpt ?? '') }}",
                "url": "{{ route('guidings.show', [$guiding->id, $guiding->slug]) }}",
                "location": {
                    "@type": "Place",
                    "name": "{{ $guiding->location }}"
                },
                "offers": {
                    "@type": "Offer",
                    "price": "{{ $guiding->getLowestPrice() }}",
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
                <!-- Mobile Sorting -->
                <div class="col-12 col-sm-4 col-md-12 d-flex mb-3 d-block d-sm-none mobile-selection-sfm">
                    <div class="d-grid gap-2 w-100">
                        <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                            <div class="btn-group border rounded-start cag-btn-inverted" role="group" style=" width:30%;">
                                <button type="button" class="btn dropdown-toggle text-white" data-bs-toggle="dropdown" aria-expanded="false"><i class="fa fa-arrow-down-arrow-up me-1"></i>@lang('message.sortby')</button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item mobile-sort-option" href="javascript:void(0)" data-sort="newest">@lang('message.newest')</a></li>
                                    <li><a class="dropdown-item mobile-sort-option" href="javascript:void(0)" data-sort="price-asc">@lang('message.lowprice')</a></li>
                                    <li><a class="dropdown-item mobile-sort-option" href="javascript:void(0)" data-sort="short-duration">@lang('message.shortduration')</a></li>
                                    <li><a class="dropdown-item mobile-sort-option" href="javascript:void(0)" data-sort="long-duration">@lang('message.longduration')</a></li>
                                </ul>
                            </div>
                            <a class="btn border-start cag-btn-inverted" data-bs-toggle="offcanvas" data-bs-target="#offcanvasBottomSearch" aria-controls="offcanvasBottomSearch" href="javascript:void(0)" style="border-left: 1px solid #ccc!important; z-index: 2; width:30%;">
                                <i class="fa fa-filter me-1"></i>@lang('message.filter')
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="active-filter-counter"></span>
                            </a>
                            <a class="btn border cag-btn-inverted" data-bs-target="#mapModal" data-bs-toggle="modal" href="javascript:void(0)" style=" border-left: 2px solid #ccc!important; width:40%;"><i class="fa fa-map-marker-alt me-2"></i>@lang('destination.show_on_map')</a>
                        </div>
                    </div>
                </div>

                <!-- Desktop Filter -->
                <div id="filterCard" class="col-sm-12 col-lg-3">        
                    <div class="card mb-2 d-none d-sm-block">
                        <div id="map-placeholder">
                            <a class="btn btn-primary" data-bs-target="#mapModal" data-bs-toggle="modal" href="javascript:void(0)">@lang('destination.show_on_map')</a>
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
                    @if(count($guidings))
                    <div class="row">
                        <div class="col-lg-12 col-sm-12">
                            <div class="tours-list__right">
                                <div class="tours-list__inner" id="guidings-list">
                                    <div class="filter-sort-container">
                                        {{-- Sort By Dropdown --}}
                                        @if(!$agent->ismobile())
                                        <div class="d-flex align-items-center">
                                            <span class="me-2">@lang('message.sortby'):</span>
                                            <form action="{{route('guidings.index')}}" method="get" style="margin-bottom: 0;">
                                                <select class="form-select form-select-sm" name="sortby" id="sortby-2" style="width: auto;">
                                                    <option value="" disabled selected>@lang('message.choose')...</option>
                                                    <option value="newest" {{request()->get('sortby') == 'newest' ? 'selected' : '' }}>@lang('message.newest')</option>
                                                    <option value="price-asc" {{request()->get('sortby') == 'price-asc' ? 'selected' : '' }}>@lang('message.lowprice')</option>
                                                    <option value="short-duration" {{request()->get('sortby') == 'short-duration' ? 'selected' : '' }}>@lang('message.shortduration')</option>
                                                    <option value="long-duration" {{request()->get('sortby') == 'long-duration' ? 'selected' : '' }}>@lang('message.longduration')</option>
                                                </select>
                                                @foreach(request()->except('sortby') as $key => $value)
                                                    @if(is_array($value))
                                                        @foreach($value as $arrayValue)
                                                            <input type="hidden" name="{{ $key }}[]" value="{{ $arrayValue }}">
                                                        @endforeach
                                                    @else
                                                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                                    @endif
                                                @endforeach
                                            </form>
                                        </div>
                                        @endif

                                        {{-- Active Filters --}}
                                        <div class="active-filters">
                                            @if(request()->has('target_fish'))
                                                @foreach(request()->get('target_fish') as $fishId)
                                                    @php
                                                        $fish = $targetFishOptions->firstWhere('id', $fishId);
                                                    @endphp
                                                    @if($fish)
                                                        <span class="badge bg-light text-dark border">
                                                            {{ app()->getLocale() == 'en' ? $fish->name_en : $fish->name }}
                                                            <button type="button" class="btn-close ms-2" data-filter-type="target_fish" data-filter-id="{{ $fishId }}"></button>
                                                        </span>
                                                    @endif
                                                @endforeach
                                            @endif

                                            @if(request()->has('methods'))
                                                @foreach(request()->get('methods') as $methodId)
                                                    @php
                                                        $method = $methodOptions->firstWhere('id', $methodId);
                                                    @endphp
                                                    @if($method)
                                                        <span class="badge bg-light text-dark border">
                                                            {{ app()->getLocale() == 'en' ? $method->name_en : $method->name }}
                                                            <button type="button" class="btn-close ms-2" data-filter-type="methods" data-filter-id="{{ $methodId }}"></button>
                                                        </span>
                                                    @endif
                                                @endforeach
                                            @endif

                                            @if(request()->has('water'))
                                                @foreach(request()->get('water') as $waterId)
                                                    @php
                                                        $water = $waterTypeOptions->firstWhere('id', $waterId);
                                                    @endphp
                                                    @if($water)
                                                        <span class="badge bg-light text-dark border">
                                                            {{ app()->getLocale() == 'en' ? $water->name_en : $water->name }}
                                                            <button type="button" class="btn-close ms-2" data-filter-type="water" data-filter-id="{{ $waterId }}"></button>
                                                        </span>
                                                    @endif
                                                @endforeach
                                            @endif
                                            

                                            {{-- Duration Type Filters --}}
                                            @if(request()->has('duration_types'))
                                                @foreach(request()->get('duration_types') as $durationType)
                                                    <span class="badge bg-light text-dark border">
                                                        @if($durationType == 'half_day')
                                                            @lang('guidings.half_day')
                                                        @elseif($durationType == 'full_day')
                                                            @lang('guidings.full_day')
                                                        @elseif($durationType == 'multi_day')
                                                            @lang('guidings.multi_day')
                                                        @endif
                                                        <button type="button" class="btn-close ms-2" data-filter-type="duration_types" data-filter-id="{{ $durationType }}"></button>
                                                    </span>
                                                @endforeach
                                            @endif

                                            {{-- Number of Persons Filter --}}
                                            @if(request()->has('num_persons'))
                                                @php
                                                    $numPersons = request()->get('num_persons');
                                                @endphp
                                                <span class="badge bg-light text-dark border">
                                                    {{ $numPersons }} {{ $numPersons == 1 ? __('message.person') : __('message.persons') }}
                                                    <button type="button" class="btn-close ms-2" data-filter-type="num_persons" data-filter-id="{{ $numPersons }}"></button>
                                                </span>
                                            @endif

                                            {{-- Price Range Filter --}}
                                            @php
                                                $priceMin = request()->get('price_min');
                                                $priceMax = request()->get('price_max');
                                                $defaultMinPrice = 50;
                                                $defaultMaxPrice = isset($overallMaxPrice) ? $overallMaxPrice : 1000;
                                                $showPriceMin = isset($priceMin) && $priceMin != $defaultMinPrice;
                                                $showPriceMax = isset($priceMax) && $priceMax != $defaultMaxPrice;
                                            @endphp
                                            @if($showPriceMin || $showPriceMax)
                                                <span class="badge bg-light text-dark border">
                                                    @if($showPriceMin && $showPriceMax)
                                                        Price from €{{ $priceMin }} to €{{ $priceMax }}
                                                    @elseif($showPriceMin)
                                                        Price from €{{ $priceMin }}
                                                    @elseif($showPriceMax)
                                                        Price up to €{{ $priceMax }}
                                                    @endif
                                                    <button type="button" class="btn-close ms-2" data-filter-type="price_range" data-filter-id="price_range"></button>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    @foreach($guidings as $guiding)
                                    <div class="row m-0 mb-2 guiding-list-item">
                                        <div class="col-md-12">
                                            <div class="row p-2 border shadow-sm bg-white rounded">
                                                <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 mt-1 p-0">
                                                    <div id="carouselExampleControls-{{$guiding->id}}" class="carousel slide" data-bs-ride="carousel" data-bs-interval="false">
                                                        <div class="carousel-inner">
                                                            @php
                                                                $galleryImages = $guiding->cached_gallery_images ?? [];
                                                            @endphp
                                                            @if(count($galleryImages))
                                                                @foreach($galleryImages as $index => $gallery_image_link)
                                                                    <div class="carousel-item @if($index == 0) active @endif">
                                                                        <img class="d-block lazy" 
                                                                             data-src="{{ $gallery_image_link }}"
                                                                             src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
                                                                             alt="{{ translate($guiding->title) }}"
                                                                             loading="lazy">
                                                                    </div>
                                                                @endforeach
                                                            @endif
                                                        </div>

                                                        @if(count($galleryImages) > 1)
                                                            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls-{{$guiding->id}}" data-bs-slide="prev">
                                                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                                <span class="visually-hidden">Previous</span>
                                                            </button>
                                                            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls-{{$guiding->id}}" data-bs-slide="next">
                                                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                                <span class="visually-hidden">Next</span>
                                                            </button>
                                                        @endif
                                                    </div>
                                            
                                                </div>
                                                <div class="guiding-item-desc col-12 col-sm-12 col-md-8 col-lg-8 col-xl-8 col-xxl-8 p-2 px-md-3 pt-md-2">
                                                    <a href="{{ route('guidings.show', [$guiding->id, $guiding->slug])}}">
                                                        <div class="guidings-item">
                                                            <div class="guidings-item-title">
                                                                <h5 class="fw-bolder text-truncate">{{ Str::limit(translate($guiding->title), 70) }}</h5>
                                                                <span class="truncate"><i class="fas fa-map-marker-alt me-2"></i>{{ $guiding->location }}</span>                                      
                                                            </div>
                                                            @php
                                                                $averageRating = $guiding->cached_average_rating ?? $guiding->user->average_rating();
                                                                $reviewCount = $guiding->cached_review_count ?? $guiding->user->reviews->count();
                                                            @endphp
                                                            @if ($averageRating)
                                                                <div class="ave-reviews-row">
                                                                    <div class="ratings-score">
                                                                    <span class="rating-value">{{number_format($averageRating, 1)}}</span>
                                                                </div>
                                                                    <span class="mb-1">
                                                                        ({{$reviewCount}} reviews)
                                                                    </span>
                                                                </div>
                                                            @else
                                                                <div class="no-reviews"><span>@lang('guidings.no_reviews')</span></div>
                                                            @endif
                                                        </div>
                                                        <div class="guidings-item-icon">
                                                            <div class="guidings-icon-container"> 
                                                                <img src="{{asset('assets/images/icons/clock-new.svg')}}" height="20" width="20" alt="" />
                                                                <div class="">
                                                                    {{$guiding->duration}} {{ $guiding->duration_type == 'multi_day' ? __('guidings.days') : __('guidings.hours') }}
                                                                </div>
                                                            </div>
                                                            <div class="guidings-icon-container"> 
                                                                <img src="{{asset('assets/images/icons/user-new.svg')}}" height="20" width="20" alt="" />
                                                                <div class="">
                                                                {{ $guiding->max_guests }} @if($guiding->max_guests != 1) {{translate('Personen')}} @else {{translate('Person')}} @endif
                                                                </div>
                                                            </div>
                                                            <div class="guidings-icon-container"> 
                                                                <img src="{{asset('assets/images/icons/fish-new.svg')}}" height="20" width="20" alt="" />
                                                                <div class="">
                                                                    <div class="tours-list__content__trait__text" >
                                                                        @php
                                                                        $guidingTargets = $guiding->cached_target_fish_names ?? $guiding->getTargetFishNames($targetsMap ?? null);
                                                                        $targetNames = collect($guidingTargets)->pluck('name')->toArray();
                                                                        @endphp
                                                                        
                                                                        @if(!empty($targetNames))
                                                                            {{ implode(', ', $targetNames) }}
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="guidings-icon-container">
                                                                <img src="{{asset('assets/images/icons/fishing-tool-new.svg')}}" height="20" width="20" alt="" />
                                                                <div class="">
                                                                    <div class="tours-list__content__trait__text" >
                                                                        {{ $guiding->cached_boat_type_name ?? ($guiding->is_boat ? ($guiding->boatType && $guiding->boatType->name !== null ? $guiding->boatType->name : __('guidings.boat')) : __('guidings.shore')) }}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="inclusions-price">
                                                            <div class="guidings-inclusions-container">
                                                                @php
                                                                    $inclussions = $guiding->cached_inclusion_names ?? $guiding->getInclusionNames();
                                                                @endphp
                                                                @if(!empty($inclussions))
                                                                <div class="guidings-included">
                                                                    <strong>@lang('guidings.Whats_Included')</strong>
                                                                    <div class="inclusions-list">
                                                                        @php
                                                                            $maxToShow = 3; // Maximum number of inclusions to display
                                                                        @endphp

                                                                        @foreach ($inclussions as $index => $inclussion)
                                                                            @if ($index < $maxToShow)
                                                                                <span class="inclusion-item"><i class="fa fa-check"></i>{{ $inclussion['name'] }}</span>
                                                                            @endif
                                                                        @endforeach

                                                                        @if (count($inclussions) > $maxToShow)
                                                                            <span class="inclusion-item">+{{ count($inclussions) - $maxToShow }} more</span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                @endif
                                                            </div>
                                                            <div class="guiding-item-price">
                                                                <h5 class="mr-1 fw-bold text-end"><span class="p-1">@lang('message.from') {{$guiding->getLowestPrice()}}€ p.P.</span></h5>
                                                                <div class="d-none d-flex flex-column mt-4">
                                                                </div>
                                                            </div>
                                                        </div>    
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                    
                                    <!-- Pagination with SEO meta tags -->
                                    @if($guidings->hasPages())
                                        <div class="pagination-wrapper">
                                            {!! $guidings->links('vendor.pagination.default') !!}
                                            
                                            <!-- Add pagination meta tags -->
                                            @if($guidings->previousPageUrl())
                                                <link rel="prev" href="{{ $guidings->previousPageUrl() }}" />
                                            @endif
                                            @if($guidings->nextPageUrl())
                                                <link rel="next" href="{{ $guidings->nextPageUrl() }}" />
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if(count($otherguidings) && ( request()->placeLat != null || request()->placelat != "" && request()->placeLng != null || request()->placelng != ""))
                    
                    <hr>
                    <div class="my-0 section-title">
                        <h2 class="h4 text-dark fw-bolder">{{translate('Additional Fishing Tour close to') }} {{ request()->placeLat != null || request()->placelat != "" && request()->placeLng != null || request()->placelng != "" ? request()->place : '' }}</h2> 
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-lg-12 col-sm-12">
                            <div class="tours-list__right">
                                <div class="tours-list__inner">
                                    @foreach($otherguidings as $otherguide)
                                    <div class="row m-0 mb-2 guiding-list-item">
                                        <div class="col-md-12">
                                            <div class="row p-2 border shadow-sm bg-white rounded">
                                                <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 mt-1 p-0">
                                                    <div id="carouselExampleControls-{{$otherguide->id}}" class="carousel slide" data-bs-ride="carousel" data-bs-interval="false">
                                                        <div class="carousel-inner">
                                                            @if(count(get_galleries_image_link($otherguide)))
                                                                @foreach(get_galleries_image_link($otherguide) as $index => $gallery_image_link)
                                                                    <div class="carousel-item @if($index == 0) active @endif">
                                                                        <img  class="d-block" src="{{asset($gallery_image_link)}}">
                                                                    </div>
                                                                @endforeach
                                                            @endif
                                                        </div>

                                                        @if(count(get_galleries_image_link($otherguide)) > 1)
                                                            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls-{{$otherguide->id}}" data-bs-slide="prev">
                                                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                                <span class="visually-hidden">Previous</span>
                                                            </button>
                                                            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls-{{$otherguide->id}}" data-bs-slide="next">
                                                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                                <span class="visually-hidden">Next</span>
                                                            </button>
                                                        @endif
                                                    </div>
                                            
                                                </div>
                                                <div class="guiding-item-desc col-12 col-sm-12 col-md-8 col-lg-8 col-xl-8 col-xxl-8 p-2 px-md-3 pt-md-2">
                                                    <a href="{{ route('guidings.show', [$otherguide->id, $otherguide->slug])}}">
                                                        <div class="guidings-item">
                                                            <div class="guidings-item-title">
                                                            <h5 class="fw-bolder text-truncate">{{ Str::limit(translate($otherguide->title), 70) }}</h5>
                                                            <span class="truncate"><i class="fas fa-map-marker-alt me-2"></i>{{ $otherguide->location }}</span>                                      
                                                            </div>
                                                            @if ($otherguide->user->average_rating())
                                                            <div class="guidings-item-ratings">
                                                            <div class="ratings-score">
                                                                    <span class="text-warning">★</span>
                                                                    <span>{{$otherguide->user->average_rating()}} </span>
                                                                    /5 ({{ $otherguide->user->received_ratings->count() }} review/s)
                                                                </div>
                                                            </div>
                                                            @endif
                                                        </div>
                                                        <div class="guidings-item-icon">
                                                            <div class="guidings-icon-container"> 
                                                                        <img src="{{asset('assets/images/icons/clock-new.svg')}}" height="20" width="20" alt="" />
                                                                    <div class="">
                                                                        {{$otherguide->duration}} {{ $otherguide->duration_type == 'multi_day' ? __('guidings.days') : __('guidings.hours') }}
                                                                    </div>
                                                            </div>
                                                            <div class="guidings-icon-container"> 
                                                                    <img src="{{asset('assets/images/icons/user-new.svg')}}" height="20" width="20" alt="" />
                                                                    <div class="">
                                                                    {{ $otherguide->max_guests }} @if($otherguide->max_guests != 1) {{translate('Personen')}} @else {{translate('Person')}} @endif
                                                                    </div>
                                                            </div>
                                                            <div class="guidings-icon-container"> 
                                                                        <img src="{{asset('assets/images/icons/fish-new.svg')}}" height="20" width="20" alt="" />
                                                                    <div class="">
                                                                        <div class="tours-list__content__trait__text" >
                                                                            @php
                                                                            $otherguideTargets = collect($otherguide->cached_target_fish_names ?? $otherguide->getTargetFishNames($targetsMap ?? null))->pluck('name')->toArray();
                                                                            @endphp
                                                                            
                                                                            @if(!empty($otherguideTargets))
                                                                                {{ implode(', ', $otherguideTargets) }}
                                                                            @endif
                                                                        </div>
                                                                    
                                                                    </div>
                                                            </div>
                                                            <div class="guidings-icon-container">
                                                                        <img src="{{asset('assets/images/icons/fishing-tool-new.svg')}}" height="20" width="20" alt="" />
                                                                    <div class="">
                                                                        <div class="tours-list__content__trait__text" >
                                                                        {{$otherguide->is_boat ? ($otherguide->boatType && $otherguide->boatType->name !== null ? $otherguide->boatType->name : __('guidings.boat')) : __('guidings.shore')}}
                                                                        </div>
                                                                    
                                                                    </div>
                                                            </div>
                                                        </div>
                                                        <div class="inclusions-price">
                                                            <div class="guidings-inclusions-container">
                                                                @if(!empty($otherguide->getInclusionNames()))
                                                                <div class="guidings-included">
                                                                    <strong>@lang('guidings.Whats_Included')</strong>
                                                                    <div class="inclusions-list">
                                                                        @php
                                                                            $inclussions = $otherguide->getInclusionNames();
                                                                            $maxToShow = 3; // Maximum number of inclusions to display
                                                                        @endphp

                                                                        @foreach ($inclussions as $index => $inclussion)
                                                                            @if ($index < $maxToShow)
                                                                                <span class="inclusion-item"><i class="fa fa-check"></i>{{ $inclussion['name'] }}</span>
                                                                            @endif
                                                                        @endforeach

                                                                        @if (count($inclussions) > $maxToShow)
                                                                            <span class="inclusion-item">+{{ count($inclussions) - $maxToShow }} more</span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                @endif
                                                            </div>
                                                            <div class="guiding-item-price">
                                                                <h5 class="mr-1 fw-bold text-end"><span class="p-1">@lang('message.from') {{$otherguide->getLowestPrice()}}€ p.P.</span></h5>
                                                                <div class="d-none d-flex flex-column mt-4">
                                                                </div>
                                                            </div>
                                                        </div>    
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
    <!--Tours List End-->

    <div class="modal show" id="mapModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" style="max-width: 100%; width: 96%; height:100%;">
            <div class="modal-content" style="height:90%;">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="mapModalLabel">Map</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div id="map" class="modal-body"></div>
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

<script type="module">
    import { MarkerClusterer } from "https://cdn.skypack.dev/@googlemaps/markerclusterer@2.3.1";
    let map; // Make map variable accessible in wider scope
    let markerCluster; // Make markerCluster accessible in wider scope
    let isDuplicateCoordinate;
    const markers = [];
    const infowindows = [];
    const uniqueCoordinates = [];
    initializeMap();

    async function initializeMap() {
    
        @php
            $lat = isset($guidings[0]) ? $guidings[0]->lat : 51.165691;
            $lng = isset($guidings[0]) ? $guidings[0]->lng : 10.451526;
        @endphp
        const position =  { lat: {{request()->get('placeLat') ? request()->get('placeLat') : $lat }} , lng: {{request()->get('placeLng') ? request()->get('placeLng') : $lng }} }; 
        const { Map, InfoWindow } = await google.maps.importLibrary("maps");
        const { AdvancedMarkerElement, PinElement } = await google.maps.importLibrary("marker");

        // Initialize map only if it hasn't been initialized yet
        if (!map) {
            const mapOptions = {
                zoom: 5,
                center: position,
                mapId: "{{env('GOOGLE_MAPS_MAP_ID')}}",
                streetViewControl: false,
                clickableIcons: false
            };
            
            map = new Map(document.getElementById("map"), mapOptions);
        }

        @if($allGuidings->isEmpty())
            @include('pages.guidings.partials.maps',['guidings' => $otherguidings])
        @else
            @php
                // Merge main guidings with other guidings if they exist
                $combinedGuidings = $allGuidings;
                
                // Only append otherguidings if there are no active checkbox filters
                $hasActiveFilters = request()->has('target_fish') || 
                                   request()->has('water') || 
                                   request()->has('methods') || 
                                   request()->has('duration_types') || 
                                   request()->has('num_persons');
                                   
                if (isset($otherguidings) && count($otherguidings) > 0 && !$hasActiveFilters) {
                    $combinedGuidings = $allGuidings->concat($otherguidings);
                }
            @endphp
            @include('pages.guidings.partials.maps',['guidings' => $combinedGuidings])
        @endif

        function getRandomOffset() {
            return (Math.random() - 0.5) * 0.0080;
        }

        markerCluster = new MarkerClusterer({ markers, map });
        google.maps.event.addListener(markerCluster, 'clusterclick', function(cluster) {
            map.setZoom(map.getZoom() + 2);
            map.setCenter(cluster.getCenter());
        });
    }

    window.updateMapWithGuidings = function(guidings) {
        // Clear existing markers
        markers.forEach(marker => marker.setMap(null));
        
        // Clear marker cluster
        if (markerCluster) {
            markerCluster.clearMarkers();
        }

        // Clear arrays but keep the map instance
        markers.length = 0;
        infowindows.forEach(infowindow => infowindow.close());
        infowindows.length = 0;
        uniqueCoordinates.length = 0;

        // Add new markers for each guiding
        guidings.forEach(guiding => {
            if (guiding.lat && guiding.lng) {
                const location = { lat: parseFloat(guiding.lat), lng: parseFloat(guiding.lng) };

                const isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
                    return coordinate.lat === location.lat && coordinate.lng === location.lng;
                });

                let marker;

                if (isDuplicateCoordinate) {
                    marker = new google.maps.marker.AdvancedMarkerElement({
                        position: {
                            lat: location.lat + ((Math.random() - 0.5) * 0.0080),
                            lng: location.lng + ((Math.random() - 0.5) * 0.0080)
                        },
                        map: map
                    });
                } else {
                    marker = new google.maps.marker.AdvancedMarkerElement({
                        position: location,
                        map: map
                    });
                    uniqueCoordinates.push(location);
                }

                markers.push(marker);

                const thumbnailPath = guiding.thumbnail_path ? 
                    `{{ asset('') }}${guiding.thumbnail_path}` :
                    '{{ asset('images/placeholder_guide.jpg') }}';

                const infowindow = new google.maps.InfoWindow({
                    content: `
                        <div class="card p-0 border-0" style="width: 200px; overflow: hidden;">
                            <div class="card-body border-0 p-0">
                                <div class="d-flex">
                                     <img src="${thumbnailPath}" alt="${guiding.title}" style="width: 100%; height: 150px; object-fit: cover;">
                                </div>
                                <div class="p-2">
                                    <a class="text-decoration-none" href="/guidings/${guiding.id}/${guiding.slug}">
                                        <h5 class="card-title mb-1" style="font-size: 14px; font-weight: bold; color: #333;">${guiding.title}</h5>
                                    </a>
                                    <div class="text-muted small">${guiding.location}</div>
                                </div>
                            </div>
                        </div>
                    `
                });

                infowindows.push(infowindow);

                marker.addListener("click", () => {
                    infowindows.forEach((iw) => {
                        iw.close();
                    });
                    infowindow.open(map, marker);
                });
            }
        });

        // Update marker cluster with new markers
        if (markerCluster) {
            markerCluster.addMarkers(markers);
        }
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
document.addEventListener("DOMContentLoaded", function() {
    // Lazy loading for images
    var lazyImages = [].slice.call(document.querySelectorAll("img.lazy"));

    if ("IntersectionObserver" in window) {
        let lazyImageObserver = new IntersectionObserver(function(entries, observer) {
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
});
</script>

@endsection

@stack('guidingListingScripts')