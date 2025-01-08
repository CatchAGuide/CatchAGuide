@extends('layouts.app-v2-1')

@if(app()->getLocale() == 'en')
    @section('title',translate($vacation->title))
@else
    @section('title',$vacation->title)
@endif

@section('description',$vacation->description)

@section('share_tags')
    <meta property="og:title" content="{{translate($vacation->title)}}" />
    <meta property="og:description" content="{{translate($vacation->description ?? "")}}" />
    @if(!empty(app('guiding')->getImagesUrl($vacation)) && is_array(app('guiding')->getImagesUrl($vacation)) && count(app('guiding')->getImagesUrl($vacation)))
    <meta property="og:image" content="{{app('guiding')->getImagesUrl($vacation)['image_0']}}"/>
    @endif

@endsection

@section('css_after')
    <style>
        .carousel .carousel-control-next, .carousel .carousel-control-prev {
            top: 50%;
            transform: translateY(-50%);
        }
        .carousel .carousel-control-next {
            right: 20px;
        }

        .carousel .carousel-control-prev {
            left: 20px;
        }

        .carousel-item-next, .carousel-item-prev, .carousel-item.active {
            display: flex;
        }
        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            width: 10px;
            height: 10px;
        }
        
        .card-header {
            background-color: transparent;
            color: #262e35;
            font-weight:bold;
        }
        .similar-guides-section {
            margin-bottom: 2rem; /* Adjust as needed */
        }
      
        @media screen and (max-width: 767px) {
            .price-details{
                display:none;
            }
            .sticky-booking {
                position: sticky;
                top: 20px;
            }
        }

        .guidings-gallery {
            min-height: 400px;
            gap: 16px;
            margin-bottom: 16px !important;
        }

        .guidings-gallery .left-image {
            width: 600px;
            padding: 0 !important;
            height: 400px;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .guidings-gallery .left-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .guidings-gallery .right-images img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            aspect-ratio: 3/2;
        }

        /* Update modal gallery images */
        div#masonry-grid img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            aspect-ratio: 3/2;
        }

    </style>
@endsection

@section('content')
 <div id="guidings-page" class="container">
    <div class="title-container">
        <div class="title-wrapper">
            <div class="title-left-container">
                <div class="col-24 col mb-1 guiding-title">
                    <h1>
                    {{ translate($vacation->title) }}
                    </h1>
                    <a class="btn" href="#" role="button"><i data-lucide="share-2"></i></a>
                </div>
                <div class="col-12">
                    <div class="location-row">
                        <div class="location">
                            <a href="#" class="fs-6 text-decoration-none text-muted">
                                    <i class="bi bi-geo-alt"></i>@lang('guidings.Fishing_Trip') <strong>{{$vacation->location}}</strong>
                                </a>
                        </div>
                        <div class="location-map">
                            <a href="#map" class="fs-6 text-decoration-none text-muted">
                                <span class="text-primary">{{translate('Show on map')}}</span>
                            </a>

                        </div>
                    </div>
                </div>
                {{-- @if ($average_rating)
                <div class="col-auto pe-0 me-1">
                    <p class="mb-1">
                        <span class="text-warning">★</span> {{$average_rating}}/5 ({{$ratings->count()}} reviews)
                    </p>
                </div> 
                @endif --}}
                <div class="col-auto p-0">
                </div>
            </div>
            <div class="title-right-container">
                <div class="title-right-buttons">
                    <a class="btn" href="#" role="button"><i data-lucide="share-2"></i></a>
                    <a href="#book-now" class="btn btn-orange">@lang('message.reservation')</a>
                </div>
                <span>{{translate('Best price guarantee')}}</span>
                </div>
        </div>
        </div>
        <!-- Image Gallery -->
        <div class="guidings-gallery row mx-0 mb-3">
            <div class="left-image">
                {{-- @if(file_exists(public_path(str_replace(asset(''), '', asset($vacation->thumbnail_path)))))
                    <img data-bs-toggle="modal" data-bs-target="#galleryModal" src="{{asset($vacation->thumbnail_path)}}" class="img-fluid" alt="Main Image">
                @else --}}
                    <div class="text-center p-4">
                        <p>No image found</p>
                    </div>
                {{-- @endif --}}
            </div>
            <div class="right-images">
                <div class="gallery">
                  @php
                    $galleryImages = json_decode($vacation->gallery,true);
                    $thumbnailPath = $vacation->thumbnail_path ?? 'images/placeholder_guide.jpg';
                    $finalImages = [];
                    $overallImages = [];
                    $hiddenCount = 0;

                    // Check if thumbnail exists
                    if (file_exists(public_path($thumbnailPath))) {
                        $finalImages[] = asset($thumbnailPath);
                        $overallImages[] = asset($thumbnailPath);
                    }

                    // Filter and validate gallery images
                    if ($galleryImages) {
                        foreach ($galleryImages as $image) {
                            if (file_exists(public_path($image)) 
                                && $image !== $thumbnailPath) {
                                $finalImages[] = asset($image);
                                $overallImages[] = asset($image);
                            }
                        }
                    }

                    // Calculate hidden count if more than 4 valid images
                    if (count($finalImages) > 4) {
                        $hiddenCount = count($finalImages) - 4;
                        $finalImages = array_slice($finalImages, 0, 4);
                    }

                    // If less than 4 images and thumbnail exists, pad with thumbnail
                    if (count($finalImages) < 4 && file_exists(public_path($thumbnailPath))) {
                        while (count($finalImages) < 4) {
                            $finalImages[] = asset($thumbnailPath);
                        }
                    }
                    
                    // Remove duplicates from overallImages while preserving order
                    $overallImages = array_values(array_unique($overallImages));
                  @endphp

                    @foreach ($finalImages as $index => $image)
                        @if ($index < 3)
                            <div class="gallery-item">
                                <img src="{{$image}}" class="img-fluid" alt="Gallery Image {{ $index + 1 }}" data-bs-toggle="modal" data-bs-target="#galleryModal" data-image="{{ $image }}">
                            </div>
                        @elseif ($index == 3 && $hiddenCount !== 0)
                            <div class="gallery-item">
                                <img src="{{$image}}" class="img-fluid" alt="Gallery Image {{ $index + 1 }} (and {{ $hiddenCount }} more)"  data-image="{{ $image }}">
                                <span data-bs-toggle="modal" data-bs-target="#galleryModal" class="position-absolute" style="top: 50%; left: 50%; transform: translate(-50%, -50%);">+{{ $hiddenCount }} more</span>
                            </div>
                        @elseif ($index == 3)
                            <div class="gallery-item">
                                <img src="{{$image}}" class="img-fluid" alt="Gallery Image {{ $index + 1 }} (and {{ $hiddenCount }} more)"  data-image="{{ $image }}">
                            </div>
                        @endif
                    @endforeach
                </div>
                <div class="gallery-mobile">
                    @php
                    $galleryImages = json_decode($vacation->gallery ?? '[]');
                    $thumbnailPath = $vacation->thumbnail_path ?? 'images/placeholder_guide.jpg';
                    $finalImages = [];
                    $overallImages = [];
                    

                    // Validate thumbnail exists
                    if (file_exists(public_path($thumbnailPath))) {
                        $finalImages[] = asset($thumbnailPath);
                        $overallImages[] = asset($thumbnailPath);
                    }
                    
                    // Filter gallery images that exist
                    if ($galleryImages) {
                        foreach ($galleryImages as $image) {
                            if (file_exists(public_path($image))) {
                                $finalImages[] = asset($image);
                                $overallImages[] = asset($image);
                            }
                        }
                    }
                    
                    $hiddenCount = count($finalImages) > 2 ? count($finalImages) - 2 : 0;

                    if (empty($finalImages)) {
                        // No valid gallery images, use thumbnail if it exists
                        if (file_exists(public_path($thumbnailPath))) {
                            $finalImages = array_fill(0, 2, asset($thumbnailPath));
                        }
                    } elseif (count($finalImages) > 3) {
                        // More than 3 valid gallery images
                        $finalImages = array_slice($finalImages, 0, 2);
                    } else {
                        // 3 or fewer valid gallery images
                        if (count($finalImages) < 3) {
                            $finalImages = $finalImages;
                            
                            // Pad with thumbnail if it exists
                            if (file_exists(public_path($thumbnailPath))) {
                                while (count($finalImages) < 2) {
                                    $finalImages[] = asset($thumbnailPath);
                                }
                            } 
                        } else {
                            $finalImages = array_slice($finalImages, 0, 2);
                        }
                    }

                    // Remove duplicates from overallImages while preserving order
                    $overallImages = array_values(array_unique($overallImages));
                    @endphp
                    @foreach ($finalImages as $index => $image)
                        @if ($index < 1)
                            <div class="gallery-item">
                                <img src="{{ $image }}" class="img-fluid" alt="Gallery Image {{ $index + 1 }}" data-bs-toggle="modal" data-bs-target="#galleryModal" data-image="{{ $image }}">
                            </div>
                        @elseif ($index == 1)
                            <div class="gallery-item">
                                <img src="{{ $image }}" class="img-fluid" alt="Gallery Image {{ $index + 1 }} (and {{ $hiddenCount }} more)" data-image="{{ $image }}">
                                <span data-bs-toggle="modal" data-bs-target="#galleryModal" class="position-absolute" style="top: 50%; left: 50%; transform: translate(-50%, -50%);">+{{ $hiddenCount }} more</span>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="galleryModal" tabindex="-1" aria-labelledby="galleryModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="galleryModalLabel">Gallery</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="masonry-grid" class="row">
                            @foreach ($overallImages as $image)
                                <div class="col-12 mb-2">
                                    <img src="{{ $image }}" class="img-fluid" alt="Gallery Image" />
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <section class="guidings-description-container mb-5">
        <!-- Left Column -->
        <div class="guidings-descriptions">
            <!-- Title, Rating, and Location -->
    
            <!-- Important Information -->
            <div class="important-info">
                    <div class="info-item">
                        <i class="fas fa-ship"></i>
                        {{-- <strong><p class="mb-0">{{$vacation->is_boat ? ($vacation->boatType && $vacation->boatType->name !== null ? $vacation->boatType->name : __('guidings.boat')) : __('guidings.shore')}}</p></strong> --}}
                    </div>
                    <div class="info-item">
                        <i>
                            <svg class="time-icon" width="18px" height="18px" viewBox="0 0 347.442 347.442" xmlns="http://www.w3.org/2000/svg">
                                <g>
                                    <g>
                                        <path d="M173.721 347.442c95.919 0 173.721-77.802 173.721-173.721S269.64 0 173.721 0 0 77.802 0 173.721s77.802 173.721 173.721 173.721zm-12.409-272.99c0-6.825 5.584-12.409 12.409-12.409s12.409 5.584 12.409 12.409v93.313l57.39 45.912c5.336 4.281 6.204 12.098 1.923 17.434-2.42 3.04-6.018 4.653-9.679 4.653-2.73 0-5.46-.869-7.755-2.73l-62.043-49.634c-2.916-2.358-4.653-5.894-4.653-9.679v-99.269z"/>
                                    </g>
                                </g>
                            </svg>
                        </i>

                        {{-- <p class="mb-0">{{ __('guidings.'.$vacation->duration_type) }} : <strong>{{$vacation->duration}} {{ $vacation->duration_type == 'multi_day' ? __('guidings.days') : __('guidings.hours') }}</strong></p> --}}
                    </div>
                    <div class="info-item">
                        <i class="fas fa-users"></i>
                       <p class="mb-0">{{translate('Number of guests:')}} <strong>{{$vacation->max_guests}}</strong></p>
                    </div>
            </div>
    
            <!-- Description Section -->
            <div class="description-container card p-3 mb-5">
                <div class="description-list">
                    <!-- Course of Action -->
                    @if ($vacation->best_travel_times)
                    <div class="description-item">
                        <div class="header-container">
                            <span>{{ translate('Best travel times')}}</span>
                        </div>
                        <p class="text-wrapper">
                           {!! implode(', ', json_decode($vacation->best_travel_times)) !!}
                        </p>
                    </div>
                    @endif
                    @if ($vacation->surroundings_description)
                    <div class="description-item">
                        <div class="header-container">
                            <span>{{ translate('Beschreibung der Umgebung')}}</span>
                        </div>
                        <p class="text-wrapper">
                        {!! $vacation->surroundings_description !!}
                        </p>
                    </div>
                    @endif
                    @if ($vacation->target_fish)
                    <div class="description-item">
                        <div class="header-container">
                            <span>{{ translate('Target fish') }}</span>
                        </div>
                        <p class="text-wrapper">
                        {!! implode(', ', json_decode($vacation->target_fish)) !!}
                        </p>
                    </div>
                    @endif
                </div>
            </div>
            <div class="tabs-container mb-5">
                <div class="nav nav-tabs" id="guiding-tab" role="tablist">
                    <button class="nav-link active" id="nav-location-tab" data-bs-toggle="tab" data-bs-target="#location" type="button" role="tab" aria-controls="nav-location" aria-selected="true">{{ translate('Location Features') }}</button>
                    <button class="nav-link" id="nav-accommodation-tab" data-bs-toggle="tab" data-bs-target="#accommodation" type="button" role="tab" aria-controls="nav-accommodation" aria-selected="false">{{ translate('Accommodation') }}</button>
                    <button class="nav-link" id="nav-boat-tab" data-bs-toggle="tab" data-bs-target="#boat" type="button" role="tab" aria-controls="nav-boat" aria-selected="false">{{ translate('Boat Information') }}</button>
                    <button class="nav-link" id="nav-fishing-tab" data-bs-toggle="tab" data-bs-target="#fishing" type="button" role="tab" aria-controls="nav-fishing" aria-selected="false">{{ translate('Fishing Information') }}</button>
                    <button class="nav-link" id="nav-pricing-tab" data-bs-toggle="tab" data-bs-target="#pricing" type="button" role="tab" aria-controls="nav-pricing" aria-selected="false">{{ translate('Pricing') }}</button>
                </div>
    
                <div class="tab-content mb-5" id="guidings-tabs">
    
                    <!-- Fishing Experience Tab -->
                    <div class="tab-pane fade show active" id="location" role="tabpanel" aria-labelledby="nav-location-tab">
                        <div class="row">
                            
                            @if(!empty($vacation->airport_distance))
                                <div class="tab-category mb-4 col-12 col-lg-4">
                                    <strong class="subtitle-text">{{ translate('Airport Distance') }}</strong>
                                    <div class="row">
                                        {{$vacation->airport_distance}}
                                    </div>
                                </div>
                            @endif
                            
                            @if(!empty($vacation->water_distance))
                                <div class="tab-category mb-4 col-12 col-lg-4">
                                    <strong class="subtitle-text">{{ translate('Water Distance') }}</strong>
                                    <div class="row">
                                    {{$vacation->water_distance}}
                                    </div>
                                </div>
                            @endif
                            
                            @if(!empty($vacation->shopping_distance))
                                <div class="tab-category mb-4 col-12 col-lg-4">
                                    <strong class="subtitle-text">{{ translate('Shopping Distance') }}</strong>
                                    <div class="row">
                                    {{$vacation->shopping_distance}}
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <strong class="subtitle-text">{{ translate('Travel Included') }}</strong>
                                {{ $vacation->travel_included || $vacation->travel_included !== null || $vacation->travel_included !== '' ? 'Yes' : 'No'}}
                            </div>
                            
                            <div class="col-6">
                                @if(!empty($vacation->travel_options))
                                    <div class="tab-category mb-4">
                                        <strong class="subtitle-text">{{ translate('Travel Options') }}</strong>
                                        <div class="row">
                                            @foreach (json_decode($vacation->travel_options) as $travel_option)
                                                <div class="col-12 text-start">
                                                    - {{$travel_option}}
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <p class="mb-4">No travel options specified</p>
                                @endif
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-4">
                                <strong class="subtitle-text">{{ translate('Pets Allowed?') }}</strong>
                                {{ $vacation->pets_allowed || $vacation->pets_allowed !== null || $vacation->pets_allowed !== '' ? 'Yes' : 'No'}}
                            </div>
                            
                            <div class="col-4">
                                <strong class="subtitle-text">{{ translate('Smoking Allowed?') }}</strong>
                                {{ $vacation->smoking_allowed || $vacation->smoking_allowed !== null || $vacation->smoking_allowed !== '' ? 'Yes' : 'No'}}
                            </div>
                            
                            <div class="col-4">
                                <strong class="subtitle-text">{{ translate('Disability Friendly?') }}</strong>
                                {{ $vacation->disability_friendly || $vacation->disability_friendly !== null || $vacation->disability_friendly !== '' ? 'Yes' : 'No'}}
                            </div>
                        </div>
                    </div>
        
                    <div class="tab-pane fade" id="accommodation" role="tabpanel" aria-labelledby="nav-accommodation-tab">
                        <strong class="subtitle-text">{{ translate('Description of Accommodation') }}</strong>
                        {!! $vacation->accommodation_description !!}

                        <div class="row">
                            <div class="col-4">
                                <strong class="subtitle-text">{{ translate('Living Area') }}</strong>
                                {{ $vacation->living_area || $vacation->living_area !== null || $vacation->living_area !== '' ? 'Yes' : 'No'}}
                            </div>
                            
                            <div class="col-4">
                                <strong class="subtitle-text">{{ translate('Number of Bedrooms') }}</strong>
                                {{ $vacation->bedroom_count || $vacation->bedroom_count !== null || $vacation->bedroom_count !== '' ? 'Yes' : 'No'}}
                            </div>
                            
                            <div class="col-4">
                                <strong class="subtitle-text">{{ translate('Number of Beds') }}</strong>
                                {{ $vacation->bed_count || $vacation->bed_count !== null || $vacation->bed_count !== '' ? 'Yes' : 'No'}}
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-6">
                                <strong class="subtitle-text">{{ translate('Number of Persons') }}</strong>
                                {{ $vacation->max_persons || $vacation->max_persons !== null || $vacation->max_persons !== '' ? 'Yes' : 'No'}}
                            </div>
                            
                            <div class="col-6">
                                <strong class="subtitle-text">{{ translate('Max Rental Days') }}</strong>
                                {{ $vacation->max_rental_days || $vacation->max_rental_days !== null || $vacation->max_rental_days !== '' ? 'Yes' : 'No'}}
                            </div>
                        </div>

                        @if(!empty($vacation->amenities))
                            <div class="tab-category mb-4">
                                <strong class="subtitle-text">{{ translate('Amenities') }}</strong>
                                <div class="row">
                                    @foreach (json_decode($vacation->amenities) as $amenity)
                                        <div class="col-12 text-start">
                                            - {{$amenity}}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <p class="mb-4">No amenities specified</p>
                        @endif
                    </div>
                    
                    <div class="tab-pane fade" id="boat" role="tabpanel" aria-labelledby="nav-boat-tab">
                        <strong class="subtitle-text">{{ translate('Boat Description') }}</strong>
                        {!! $vacation->boat_description !!}

                        @if(!empty($vacation->equipment))
                            <div class="tab-category mb-4">
                                <strong class="subtitle-text">{{ translate('Equipments') }}</strong>
                                <div class="row">
                                    @foreach (json_decode($vacation->equipment) as $equipment)
                                        <div class="col-12 text-start">
                                            - {{$equipment}}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <p class="mb-4">No equipments specified</p>
                        @endif
                    </div>
                    
                    <div class="tab-pane fade" id="fishing" role="tabpanel" aria-labelledby="nav-fishing-tab">
                        <strong class="subtitle-text">{{ translate('Fishing Description') }}</strong>
                        {!! $vacation->basic_fishing_description !!}

                        @if(!empty($vacation->catering_info))
                            <div class="tab-category mb-4">
                                <strong class="subtitle-text">{{ translate('Catering and More') }}</strong>
                                <div class="row">
                                    @foreach ((array)json_decode($vacation->catering_info) as $catering)
                                        <div class="col-12 text-start">
                                            - {{$catering}}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <p class="mb-4">No catering and more specified</p>
                        @endif
                    </div>

                    <div class="tab-pane fade" id="pricing" role="tabpanel" aria-labelledby="nav-pricing-tab">
                        <strong class="subtitle-text">{{ translate('Total Package Prices (All-Inclusive) per Person') }}</strong>
                        @if($vacation->max_guests > 0)
                            {!! number_format(((float)$vacation->package_price_per_person + (float)$vacation->accommodation_price + (float)$vacation->boat_rental_price + (float)$vacation->guiding_price )/ $vacation->max_guests, 2) !!}
                        @else
                            {!! number_format((float)$vacation->package_price_per_person + (float)$vacation->accommodation_price + (float)$vacation->boat_rental_price + (float)$vacation->guiding_price, 2) !!}
                        @endif
                        
                        <strong class="subtitle-text">{{ translate('Individual Prices (Accommodation) per Unit') }}</strong>
                        {!! $vacation->accommodation_price !!}

                        <strong class="subtitle-text">{{ translate('Individual Prices (Boat Rental) per Unit') }}</strong>
                        {!! $vacation->boat_rental_price !!}

                        <strong class="subtitle-text">{{ translate('Individual Prices (Guiding) per Tour') }}</strong>
                        {!! $vacation->guiding_price !!}
                        
                        @if(!empty($vacation->additional_services))
                            <div class="tab-category mb-4">
                                <strong class="subtitle-text">{{ translate('Additional Services') }}</strong>
                                <div class="row">
                                    @foreach (json_decode($vacation->additional_services) as $additional_service)
                                        <div class="col-12 text-start">
                                            - {{$additional_service}}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <p class="mb-4">No additional services specified</p>
                        @endif
                        
                        @if(!empty($vacation->included_services))
                            <div class="tab-category mb-4">
                                <strong class="subtitle-text">{{ translate('Included Services') }}</strong>
                                <div class="row">
                                    @foreach (json_decode($vacation->included_services) as $included_service)
                                        <div class="col-12 text-start">
                                            - {{$included_service}}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <p class="mb-4">No additional services specified</p>
                        @endif
                    </div>
                </div>
            </div>
<!-- Accordion mobile ver -->
<div class="accordion mb-5" id="guidings-accordion">

<!-- What's Included Accordion -->
<div class="accordion-item">
    <h2 class="accordion-header" id="headingInclude">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInclude" aria-expanded="true" aria-controls="collapseInclude">
        @lang('guidings.Inclusions')
        </button>
    </h2>
    <div id="collapseInclude" class="accordion-collapse collapse show" aria-labelledby="headingInclude" data-bs-parent="#accordionTabs">
        <div class="accordion-body">
            <div class="row">
                <div class="col-12 mb-4">
                    @if(!empty($vacation->inclusions))
                        @php
                            $inclussions = $vacation->getInclusionNames();
                            $maxToShow = 3; // Maximum number of inclusions to display
                        @endphp
                        <div class="row">
                            <strong class="mb-2 subtitle-text">@lang('guidings.Inclusions')</strong>
                            @foreach ($inclussions as $index => $inclussion)
                                <div class="col-12 text-start">
                                    <i data-lucide="wrench"></i> {{$inclussion['name']}}
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p>No inclusions specified</p>
                    @endif
                </div>
                <div class="col-12">
                @if(!empty(json_decode($vacation->pricing_extra)))
                    <div class="row">
                        <strong class="mb-2 subtitle-text">@lang('guidings.Additional_Extra')</strong>
                        @foreach (json_decode($vacation->pricing_extra) as $pricing_extras)
                            <div class="mb-2">
                                <span>{{$pricing_extras->name}}</span> 
                                <span>{{$pricing_extras->price}}€ p.P</span>
                            </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fishing Experience Accordion -->
<div class="accordion-item">
    <h2 class="accordion-header" id="headingFishing">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFishing" aria-expanded="false" aria-controls="collapseFishing">
            @lang('guidings.Tour_Info')
        </button>
    </h2>
    <div id="collapseFishing" class="accordion-collapse collapse" aria-labelledby="headingFishing" data-bs-parent="#accordionTabs">
        <div class="accordion-body">
            <div class="row">
                @if(!empty($vacation->target_fish))
                    <div class="col-12 mb-4">
                        <strong class="subtitle-text"> @lang('guidings.Target_Fish')</strong>
                        <div class="row">
                            {{-- @foreach ($vacation->getTargetFishNames() as $index => $target_fish) --}}
                            @foreach ([] as $index => $target_fish)
                                <div class="col-12 text-start">
                                    {{$target_fish['name']}}
                                </div>
                                @if(($index + 1) % 2 == 0)
                                    </div><div class="row">
                                @endif
                            @endforeach
                        </div>
                    </div>
                @else
                    <p class="mb-4">No fish specified</p>
                @endif

                <!-- Methods Section -->
                @if(!empty($vacation->fishing_methods))
                    <div class="col-12 mb-4">
                        <strong class="subtitle-text"> @lang('guidings.Fishing_Method')</strong>
                        <div class="row">
                            {{-- @foreach ($vacation->getFishingMethodNames() as $index => $fishing_method) --}}
                            @foreach ([] as $index => $fishing_method)
                                <div class="col-12 text-start">
                                    {{$fishing_method['name']}}
                                </div>
                                @if(($index + 1) % 2 == 0)
                                    </div><div class="row">
                                @endif
                            @endforeach
                        </div>
                    </div>
                @else
                    <p class="mb-4">No methods specified</p>
                @endif

                <!-- Water Types Section -->
                @if(!empty($vacation->water_types))
                    <div class="col-12 mb-3">
                        <strong class="subtitle-text"> @lang('guidings.Water_Type')</strong>
                        <div class="row">
                            {{-- @foreach ($vacation->getWaterNames() as $index => $water_type) --}}
                            @foreach ([] as $index => $water_type)
                                <div class="col-12 text-start">
                                    {{$water_type['name']}}
                                </div>
                                @if(($index + 1) % 2 == 0)
                                    </div><div class="row">
                                @endif
                            @endforeach
                        </div>
                    </div>
                @else
                    <p class="mb-4">No water types specified</p>
                @endif
            </div>
        </div>
    </div>
</div>
@if ($vacation->is_boat)
<!-- Boat Information Accordion -->
<div class="accordion-item">
    <h2 class="accordion-header" id="headingBoat">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBoat" aria-expanded="false" aria-controls="collapseBoat">
        @lang('guidings.Boat_Details')
        </button>
    </h2>
    <div id="collapseBoat" class="accordion-collapse collapse" aria-labelledby="headingBoat" data-bs-parent="#accordionTabs">
        <div class="accordion-body">
            <!-- @php $boatInformation = json_decode($vacation->boat_information, true); @endphp
            <tr><th>Seats</th><td>{{ $boatInformation['seats'] ?? '' }}</td></tr>
            <tr><th>Length</th><td>{{ $boatInformation['length'] ?? '' }}</td></tr>
            <tr><th>Width</th><td>{{ $boatInformation['width'] ?? '' }}</td></tr>
            <tr><th>Year Built</th><td>{{ $boatInformation['year_built'] ?? '' }}</td></tr>
            <tr><th>Engine Manufacturer</th><td>{{ $boatInformation['engine_manufacturer'] ?? '' }}</td></tr>
            <tr><th>Engine Power (hp)</th><td>{{ $boatInformation['engine_power'] ?? '' }}</td></tr>
            <tr><th>Max Speed</th><td>{{ $boatInformation['max_speed'] ?? '' }}</td></tr>
            <tr><th>Manufacturer</th><td>{{ $boatInformation['manufacturer'] ?? '' }}</td></tr> -->
            <!-- Check if $boatInformation is not empty -->
            @if(!empty($boatInformation))
            <strong class="subtitle-text">{{ translate('Boat') }}:</strong>
            <table class="table my-4">
                <tbody>
                    @foreach($boatInformation as $key => $value)
                        <tr><th>{{ $value['name'] }}</th><td colspan="1">{{ $value['value'] }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <!-- If $boatInformation is empty, show this message -->
            <p>No boat information specified</p>
        @endif

        <!-- Boat Extras Section -->
            @if($vacation->boat_extras != null || $vacation->boat_extras != '' || $vacation->boat_extras != '[]')
                <strong class="subtitle-text">@lang('guidings.Boat_Extras'):</strong>
                <ul>
                    {{-- @foreach($vacation->getBoatExtras() as $extra) --}}
                    @foreach ([] as $extra)
                        <li>{{ $extra['name'] }}</li>
                    @endforeach
                </ul>
            @endif
    </div>
</div>
</div>
@endif
<!-- Important Information Accordion -->
<div class="accordion-item">
    <h2 class="accordion-header" id="headingInfo">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInfo" aria-expanded="false" aria-controls="collapseInfo">
        @lang('guidings.Additional_Info')
        </button>
    </h2>
    <div id="collapseInfo" class="accordion-collapse collapse" aria-labelledby="headingInfo" data-bs-parent="#accordionTabs">
        <div class="accordion-body">
            <!-- Requirements Section -->
            @if(!empty($vacation->requirements))
                <strong class="subtitle-text">@lang('guidings.Requirements')</strong>
                <ul>
                    @foreach ($vacation->getRequirementsAttribute() as $requirements)
                        <li><span>{{ $requirements['name'] }}:</span> {{ $requirements['value'] ?? '' }}</li>
                    @endforeach
                </ul>
                <hr/>
            @else
                <p>No requirements specified</p>
                <hr/>
            @endif
            <!-- Other Information Section -->
            @if(!empty($vacation->other_information) && $vacation->other_information !== null && $vacation->other_information->count() > 0)
                <strong class="subtitle-text">@lang('guidings.Other_Info')</strong>
                <ul>
                    @foreach ($vacation->getOtherInformationAttribute() as $otherIndex => $other)
                        <li><span>{{ $other['name'] }}:</span> {{ $other['value'] ?? '' }}</li>
                    @endforeach
                </ul>
                <hr/>
            @endif
            <!-- Recommended Preparation Section -->
            @if(!empty($vacation->recommendations ) && $vacation->recommendations !== null && $vacation->recommendations->count() > 0)
                <strong class="subtitle-text">@lang('guidings.Reco_Prep')</strong>
                <ul>
                    @foreach ($vacation->getRecommendationsAttribute() as $recIndex => $recommendations)
                        <li><span>{{ $recommendations['name'] }}:</span> {{ $recommendations['value'] ?? '' }}</li>
                    @endforeach
                </ul>
                <hr/>
            @endif
            <div class="row p-0">
                <div class="col-md-6">
                    @if(!empty($vacation->style_of_fishing))
                            <strong class="subtitle-text">@lang('guidings.Style_Fishing'):</strong> 
                            <span class="">{{ $vacation->style_of_fishing }}</span>
                    @endif
                </div>
                <div class="col-md-6">
                    @if(!empty($vacation->tour_type))
                        <div>
                            <strong class="subtitle-text">@lang('guidings.Tour_Type'):</strong> 
                            <span class="">{{ $vacation->tour_type }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

</div>

 <!-- Description Section -->
 <div class="">
 <h2 class="mb-3">@lang('guidings.Availability')</h2>
                    <div id="lite-datepicker" wire:ignore></div>
            </div>
        </div>
    
        <!-- Right Column -->
        <div id="book-now" class="guidings-book">
            @if(!$agent->ismobile())
                {{-- @include('pages.guidings.content.bookguiding') --}}
            @endif
        </div>
    </section>

    <!-- Map Section -->
    <div id="map" class="mb-5" style="height: 400px;">
        <!-- Google Map will be rendered here -->
    </div>

    <!-- Rating Summary -->
    <div class="guidings-rating mb-5">
    {{-- @if(round($average_rating) > 0) --}}
        <div class="ratings-head">
            <h3>Bewertungen</h3>
            <div class="ratings-score-ave">
                <div class="ratings-card">
                    {{-- <span class="text-warning">★</span> {{$average_rating}}/5 ({{ $vacation->user->received_ratings->count() }} reviews) --}}
                </div>
            </div>
        </div>
        <div class="ratings-slider owl-carousel">
            {{-- @foreach($vacation->user->received_ratings as $received_rating)
                <div class="ratings-item">
                    <div class="ratings-comment">
                        <div class="ratings-comment-top">
                            <div class="user-info">
                                <p class="user">{{$received_rating->user->firstname }}</p>
                                <p class="date">{{ ($received_rating->created_at != null) ? Carbon\Carbon::parse($received_rating->created_at)->format('F j, Y') : "-" }}</p>
                            </div>
                            <p>
                                <span class="text-warning">★</span> {{ floor($received_rating->rating)}}/5
                            </p>
                        </div>
                        <div class="comment-content">
                            <p class="description">{{ $received_rating->description }}</p>
                            <small class="see-more text-orange">See More</small>
                            <small class="show-less text-orange">Show Less</small>
                        </div>
                    </div>
                </div>
            @endforeach --}}
        </div>
    {{-- @endif --}}
</div>


    <div class="mb-5">
        <div class="tour-details-two__about">
            <div class="row">
                <div class="col-md-3 wow fadeInLeft" data-wow-duration="1500ms">
                    <div class="about-one__left">
                        <div class="about-one__img-box">
                            <div class="tour-details__review-comment-top-img">
                                {{-- @if($vacation->user->profil_image)
                                    <img class="center-block rounded-circle"
                                         src="{{asset('images/'. $vacation->user->profil_image)}}" alt="" width="200px"
                                         height="200px">
                                @else
                                    <img class="center-block rounded-circe"
                                         src="{{asset('images/placeholder_guide.jpg')}}" alt="" width="200px"
                                         height="200px">
                                @endif --}}

                            </div>
                            {{-- <h4 class="mt-3" style="text-align: center">{{$vacation->user->firstname}}</h4> --}}
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="text-left">
                        <ul class="list-unstyled mb-3 tour-details-two__overview-bottom-list">
                            <li>
                                <div class="icon-small">
                                    <i class="fa fa-check"></i>
                                </div>
                                <div class="text">
                                    <p>
                                        {{-- <b>{{ translate('Lieblingsfisch') }}:</b>{{ translate($vacation->user->information['favorite_fish']) }}  --}}
                                    </p>
                                </div>
                            </li>
                            <li>
                                <div class="icon-small">
                                    <i class="fa fa-check"></i>
                                </div>
                                <div class="text">
                                    <p>
                                        {{-- <b>{{ translate('Sprachen') }}:</b> {{ translate($vacation->user->information['languages']) }} --}}
                                    </p>
                                </div>
                            </li>
                            <li>
                                <div class="icon-small">
                                    <i class="fa fa-check"></i>
                                </div>
                                <div class="text">
                                    <p>
                                        {{-- <b>{{ translate('Angelt seit') }}:</b> {{ $vacation->user->information['fishing_start_year'] }} --}}
                                    </p>
                                </div>
                            </li>
                        </ul>

                        <p class="js-trigger-more-text"><b>{{ translate('Über mich') }}:</b>
                            {{-- {!! translate($vacation->aboutme()[0]) !!}
                            {!! translate($vacation->aboutme()[1]) !!} --}}
                        </p>
                        <button class="thm-btn js-btn-more-text" onclick="moreOrLessFunction(this)">{{ translate('Mehr') }} </button>
                    </div>


                </div>
            </div>
        </div>
    </div>
    {{-- @if($same_guiding && count($same_guiding ) > 0)
    <section class="tour-details-two mb-5 p-0">
        <div class="container">
    <div class="row">
        <div class="col-lg-12 col-sm-12">
            <h3 class="tour-details-two__title">@lang('guidings.More_Fishing') {{$vacation->user->firstname}}</h3>
            <div class="tours-list__right">
                <!-- Slider container -->
                <div class="tours-list__inner">
                    @foreach($same_guiding as $index => $other_guiding) <!-- Removed `.take(4)` -->
                    <div class="row m-0 mb-2 guiding-list-item {{ $index < 2 ? 'show' : '' }}">
                        <div class="col-md-12">
                            <div class="row p-2 border shadow-sm bg-white rounded">
                                <!-- Carousel and other content for each guiding item -->
                                <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 mt-1 p-0">
                                    <div id="carouselExampleControls-{{$other_guiding->id}}" class="carousel slide" data-bs-ride="carousel" data-bs-interval="false">
                                        <div class="carousel-inner">
                                            @if(count(get_galleries_image_link($other_guiding)))
                                                @foreach(get_galleries_image_link($other_guiding) as $index => $gallery_image_link)
                                                    <div class="carousel-item @if($index == 0) active @endif">
                                                        <img class="d-block" src="{{$gallery_image_link}}">
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                        @if(count(get_galleries_image_link($other_guiding)) > 1)
                                            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls-{{$other_guiding->id}}" data-bs-slide="prev">
                                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Previous</span>
                                            </button>
                                            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls-{{$other_guiding->id}}" data-bs-slide="next">
                                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Next</span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                <div class="guiding-item-desc col-12 col-sm-12 col-md-8 col-lg-8 col-xl-8 col-xxl-8 p-2 p-md-3 mt-md-1">
                                    <a href="{{ route('guidings.show', [$other_guiding->id, $other_guiding->slug]) }}">
                                            <div class="guidings-item">
                                                <div class="guidings-item-title">
                                                    <h5 class="fw-bolder text-truncate">{{translate($other_guiding->title)}}</h5>
                                                    <span class="text-center"><i class="fas fa-map-marker-alt me-2"></i>{{ translate($other_guiding->location) }}</span>                                      
                                                </div>
                                                @if ($other_guiding->user->average_rating())
                                                <div class="guidings-item-ratings">
                                                <div class="ratings-score">
                                                <span class="text-warning">★</span>
                                                        <span>{{$other_guiding->user->average_rating()}} </span>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                            <div class="guidings-item-icon">
                                                <div class="guidings-icon-container"> 
                                                            <img src="{{asset('assets/images/icons/clock-new.svg')}}" height="20" width="20" alt="" />
                                                        <div class="">
                                                            {{ $other_guiding->duration }} @if($other_guiding->duration != 1) {{translate('Stunden')}} @else {{translate('Stunde')}} @endif
                                                        </div>
                                                </div>
                                                <div class="guidings-icon-container"> 
                                                        <img src="{{asset('assets/images/icons/user-new.svg')}}" height="20" width="20" alt="" />
                                                        <div class="">
                                                        {{ $other_guiding->max_guests }} @if($other_guiding->max_guests != 1) {{translate('Personen')}} @else {{translate('Person')}} @endif
                                                        </div>
                                                </div>
                                                <div class="guidings-icon-container"> 
                                                            <img src="{{asset('assets/images/icons/fish-new.svg')}}" height="20" width="20" alt="" />
                                                        <div class="">
                                                            <div class="tours-list__content__trait__text" >

                                                                @php
                                                                $guidingTargets = collect($vacation->getTargetFishNames())->pluck('name')->toArray();
                                                                @endphp
                                                                
                                                                @if(!empty($guidingTargets))
                                                                    {{ implode(', ', $guidingTargets) }}
                                                                @endif
                                                            </div>
                                                        
                                                        </div>
                                                </div>
                                                <div class="guidings-icon-container">
                                                            <img src="{{asset('assets/images/icons/fishing-tool-new.svg')}}" height="20" width="20" alt="" />
                                                        <div class="">
                                                            <div class="tours-list__content__trait__text" >
                                                                {{$other_guiding->is_boat ? ($other_guiding->boatType && $other_guiding->boatType->name !== null ? $other_guiding->boatType->name : __('guidings.boat')) : __('guidings.shore')}}
                                                            </div>
                                                        
                                                        </div>
                                                </div>
                                            </div>
                                            <div class="inclusions-price">
                                                    <div class="guidings-inclusions-container">
                                                        @if(!empty($other_guiding->getInclusionNames()))
                                                        <div class="guidings-included">
                                                            <strong>@lang('guidings.Whats_Included')</strong>
                                                            <div class="inclusions-list">
                                                                @php
                                                                    $inclussions = $vacation->getInclusionNames();
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
                                                        <h5 class="mr-1 fw-bold text-end"><span class="p-1">@lang('message.from') {{$other_guiding->getLowestPrice()}}€ p.P.</span></h5>
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

                <div class="text-center">
                    <button id="showMoreBtn" class="btn btn-orange mt-3 text-center">{{ translate('Show More') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
    </section>
    @endif --}}
    {{-- @if ($other_guidings)
    <section class="tour-details-two mb-5 p-0">
        <div class="container">

            <div class="tour-details-two__related-tours {{$agent->ismobile() ? 'text-center' : ''}}">
                <h3 class="tour-details-two__title">@lang('guidings.Match_Guiding')</h3>
                <div class="popular-tours__carousel owl-theme owl-carousel">
                    @foreach($other_guidings as $other_guiding)
                
                        <div class="popular-tours__single">
                            <a class="popular-tours__img" href="{{ route('guidings.show',[$other_guiding->id,$other_guiding->slug]) }}" title="Guide aufmachen">
                                <div class="popular-tours__img__wrapper">
                                        @if($other_guiding->thumbnail_path)
                                            <img src="{{ asset($other_guiding->thumbnail_path) }}" alt="{{ $other_guiding->title }}"/>
                                        @endif
                                </div>
                            </a>

                            <div class="popular-tours__content">
                            <h5 class="crop-text-2 card-title h6">
    <a href="{{ route('guidings.show', [$other_guiding->id, $other_guiding->slug]) }}">
        {{ $other_guiding->title ? translate(Str::limit($other_guiding->title, 50)) : $other_guiding->title }}
    </a>
</h5>    
                                <small class="crop-text-1 small-text text-muted">{{ $other_guiding->location }}</small>
                                <p class="fw-bold text-muted">
                                    <span>@lang('message.from') {{ $other_guiding->getLowestPrice() }}€</span>
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="text-center my-3">
                    <a href="/guidings" class="btn btn-outline-secondary">{{ translate('View all guidings') }}</a>
                </div>
            </div>
            
        </div>
    </section>
    @endif --}}
</div>
<div class="guidings-book-mobile">
            @if($agent->ismobile())
                {{-- @include('pages.guidings.content.bookguidingmobile') --}}
            @endif
        </div>
@endsection

@section('js_after')
{{-- <script async src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_API_KEY') }}&callback=initMap"></script> --}}

<script>
$(document).ready(function(){
  $(".ratings-slider").owlCarousel({
    items: 3,
    margin: 10,
    loop:false,
    nav:false,
    dots: true,
    slideBy:3 ,
    autoplay: true,
    autoplayTimeout: 10000,
    responsive: {
      0: {
        items: 1,
        slideBy: 1
      },
      767: {
        items: 2
      },
      1000: {
        items: 3
      }
    }
  });
  initOwlCarousel(); // Initialize on page load
    $(window).resize(initOwlCarousel); // Reinitialize on resize

    // "Show More" button functionality for desktop
    const showMoreBtn = document.getElementById("showMoreBtn");
    const items = document.querySelectorAll(".guiding-list-item");
    let isExpanded = false;

    showMoreBtn.addEventListener("click", function () {
        isExpanded = !isExpanded;
        items.forEach((item, index) => {
            // Show all items if expanded, otherwise show only the first two
            item.classList.toggle("show", isExpanded || index < 2);
        });
        // Toggle button text
        showMoreBtn.textContent = isExpanded ? "Show Less" : "Show More";
    });
  function initOwlCarousel() {
    const $toursList = $(".tours-list__inner");

    if ($(window).width() < 768) {
        // Initialize Owl Carousel for mobile
        if (!$toursList.hasClass("owl-carousel")) {
            $toursList.addClass("owl-carousel").owlCarousel({
                items: 1,
                loop: false,
                margin: 10,
                nav: true,
                dots: true,
            });
        }
        $("#showMoreBtn").hide(); // Hide "Show More" button on mobile
        $(".guiding-list-item").addClass("show"); // Show all items
    } else {
        // Destroy Owl Carousel for desktop
        if ($toursList.hasClass("owl-carousel")) {
            $toursList.trigger("destroy.owl.carousel").removeClass("owl-carousel owl-loaded");
            $toursList.find(".owl-stage-outer").children().unwrap();
        }
        $("#showMoreBtn").show(); // Show "Show More" button on desktop
        $(".guiding-list-item").each(function (index) {
            // Show only the first two items on desktop
            $(this).toggleClass("show", index < 2);
        });
    }
}
initMap();
});
document.querySelectorAll(".description-item .text-wrapper").forEach((item) => {
    const originalText = item.innerHTML.trim();
    const words = originalText.split(/\s+/);
    if (words.length > 30) {
        const truncatedText = words.slice(0, 30).join(" ") + "... ";
        item.innerHTML = truncatedText;
        const toggle = document.createElement("small");
        toggle.textContent = "See More";
        toggle.style.cursor = "pointer";
        toggle.classList.add("text-orange")
        toggle.onclick = () => {
            const isExpanded = toggle.textContent === "See Less";
            item.innerHTML = isExpanded ? truncatedText : originalText + " ";
            toggle.textContent = isExpanded ? "See More" : "See Less";
            item.appendChild(toggle);
        };
        item.appendChild(toggle);
    }
});


document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll('.comment-content').forEach(content => {
        const descriptionElement = content.querySelector('.description');
        const seeMore = content.querySelector('.see-more');
        const showLess = content.querySelector('.show-less');

        if (descriptionElement && seeMore && showLess) {
            const fullText = descriptionElement.innerText;
            const words = fullText.split(" ");

            if (words.length > 20) {
                descriptionElement.innerText = words.slice(0, 20).join(" ") + "...";
                seeMore.style.display = 'inline';
                showLess.style.display = 'none';

                const toggleText = (isExpanded) => {
                    descriptionElement.innerText = isExpanded ? fullText : words.slice(0, 20).join(" ") + "...";
                    seeMore.style.display = isExpanded ? 'none' : 'inline';
                    showLess.style.display = isExpanded ? 'inline' : 'none';
                };

                seeMore.addEventListener('click', () => toggleText(true));
                showLess.addEventListener('click', () => toggleText(false));
            } else {
                seeMore.style.display = 'none';
                showLess.style.display = 'none';
            }
        }
    });
});
    function initMap() {
      console.log('initMap');
      console.log('{{ $vacation->lat }}');
      console.log('{{ $vacation->lng }}');
        //var location = { lat: 41.40338, lng: 2.17403 }; // Example coordinates
        var location = { lat: {{ $vacation->lat }}, lng: {{ $vacation->lng }} }; // Example coordinates
        var map = new google.maps.Map(document.getElementById('map'), {
            zoom: 10,
            center: location,
            mapTypeControl: false,
            streetViewControl: false,
        });
        var marker = new google.maps.Marker({
            position: location,
            map: map
        });
    }

    function initCheckNumberOfColumns() {
        return window.innerWidth < 768 ? 1 : 2;
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Replace direct access to $blocked_events with a safe default
        const blockedEvents = @json($blocked_events ?? []);

        let lockDays = [];
        if (blockedEvents && typeof blockedEvents === 'object') {
            lockDays = Object.values(blockedEvents).flatMap(event => {
                const fromDate = new Date(event.from);
                const dueDate = new Date(event.due);

                // Create an array of all dates in the range
                const dates = [];
                for (let d = fromDate; d <= dueDate; d.setDate(d.getDate() + 1)) {
                    dates.push(new Date(d));
                }
                return dates;
            });
        }

        const picker = new Litepicker({
            element: document.getElementById('lite-datepicker'),
            inlineMode: true,
            singleDate: true,
            numberOfColumns: initCheckNumberOfColumns(),
            numberOfMonths: initCheckNumberOfColumns(),
            minDate: new Date(new Date().getTime() + 24 * 60 * 60 * 1000),
            lockDays: ['2024-11-24'],
            lang: '{{app()->getLocale()}}',
            setup: (picker) => {
                window.addEventListener('resize', () => {
                    picker.setOptions({
                        numberOfColumns: initCheckNumberOfColumns(),
                        numberOfMonths: initCheckNumberOfColumns()
                    });
                });
            },
        });
    });
    
    let currentCount = 3; // Initial count of displayed items
    const totalItems = {{ isset($same_guiding) ? $same_guiding->count() : 0 }};
    
    document.getElementById('show-more').addEventListener('click', function() {
        const container = document.getElementById('same-guidings-container');
        
        // Fetch the next set of items
        for (let i = currentCount; i < currentCount + 3 && i < totalItems; i++) {
            const guiding = @json($same_guiding ?? []); // Convert PHP variable to JavaScript
            const newGuiding = guiding[i];

            const colDiv = document.createElement('div');
            colDiv.className = 'col-md-6 mb-3';
            colDiv.innerHTML = `
                <div class="card">
                    <img src="${newGuiding.thumbnail_path}" class="card-img-top" alt="${newGuiding.title}">
                    <div class="card-body">
                        <h5 class="card-title">${newGuiding.title}</h5>
                        <p class="card-text">${newGuiding.location}</p>
                        <a href="/guidings/${newGuiding.id}/${newGuiding.slug}" class="btn btn-primary">Details</a>
                    </div>
                </div>
            `;
            container.appendChild(colDiv);
        }

        currentCount += 3; // Update the count of displayed items

        // Hide the button if all items are displayed
        if (currentCount >= totalItems) {
            this.style.display = 'none';
        }
    });
</script>
@endsection

