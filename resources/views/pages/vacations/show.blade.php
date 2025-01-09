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
            @if(isset($vacation->gallery) && is_array($vacation->gallery) && !empty($vacation->gallery[0]) && file_exists(public_path(str_replace(asset(''), '', asset($vacation->gallery[0])))))
                <img data-bs-toggle="modal" data-bs-target="#galleryModal" src="{{asset($vacation->gallery[0])}}" class="img-fluid" alt="Main Image">
            @else
                <div class="text-center p-4">
                    <p>No image found</p>
                </div>
            @endif
        </div>
        <div class="right-images">
            <div class="gallery">
                @php
                $galleryImages = $vacation->gallery ?? [];
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
                $galleryImages = $vacation->gallery ?? '[]';
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
        <div class="guidings-descriptions">
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
            </div>

            <div class="tabs-container mb-5">
                <div class="nav nav-tabs" id="guiding-tab" role="tablist">
                    <button class="nav-link active" id="nav-accommodation-tab" data-bs-toggle="tab" data-bs-target="#accommodation" type="button" role="tab" aria-controls="nav-accommodation" aria-selected="true">{{ translate('Accommodation') }}</button>
                    <button class="nav-link" id="nav-boat-tab" data-bs-toggle="tab" data-bs-target="#boat" type="button" role="tab" aria-controls="nav-boat" aria-selected="false">{{ translate('Boat Information') }}</button>
                    <button class="nav-link" id="nav-package-tab" data-bs-toggle="tab" data-bs-target="#package" type="button" role="tab" aria-controls="nav-package" aria-selected="false">{{ translate('Package') }}</button>
                    <button class="nav-link" id="nav-guiding-tab" data-bs-toggle="tab" data-bs-target="#guiding" type="button" role="tab" aria-controls="nav-guiding" aria-selected="false">{{ translate('Guiding') }}</button>
                </div>
    
                <div class="tab-content mb-5" id="guidings-tabs">
                    @php
                        $sections = [
                            'accommodation' => [
                                'items' => $vacation->accommodations,
                                'title' => 'Accommodation'
                            ],
                            'boat' => [
                                'items' => $vacation->boats,
                                'title' => 'Boat Information'
                            ],
                            'package' => [
                                'items' => $vacation->packages,
                                'title' => 'Package'
                            ],
                            'guiding' => [
                                'items' => $vacation->guidings,
                                'title' => 'Guiding'
                            ]
                        ];
                    @endphp

                    @foreach($sections as $sectionKey => $section)
                        <div class="tab-pane fade {{ $sectionKey === 'accommodation' ? 'show active' : '' }}" 
                             id="{{ $sectionKey }}" 
                             role="tabpanel" 
                             aria-labelledby="nav-{{ $sectionKey }}-tab">
                            
                             <h5 class="card-title mb-3">{{ translate('Description of ' . $sectionKey) }}</h5>

                            @foreach($section['items'] as $itemIndex => $item)
                                <div class="card h-100 shadow-sm mb-4">
                                    <div class="card-body">
                                        <div class="row">
                                            <!-- Description Column -->
                                            <div class="col-12 col-lg-7 mb-3 mb-lg-0">
                                                <h6 class="card-title mb-3">{{ translate( $sectionKey . ' ' . ($itemIndex + 1)) }}</h6>
                                                {!! $item->description !!}
                                            </div>

                                            <!-- Pricing Column -->
                                            <div class="col-12 col-lg-5">
                                                @php $dynamicFields = json_decode($item->dynamic_fields) @endphp
                                                @foreach($dynamicFields as $field => $value)
                                                    @if($field === 'prices')
                                                        <div class="mb-3">
                                                            <h6 class="mb-2">{{ translate('Pricing') }}</h6>
                                                            <div class="table-responsive">
                                                                <table class="table table-sm">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>{{ translate('Persons') }}</th>
                                                                            <th>{{ translate('Price') }}</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach($value as $index => $price)
                                                                            <tr>
                                                                                <td>{{ $index + 1 }}</td>
                                                                                <td>€{{ number_format($price, 2, ',', '.') }}</td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>

                                        <!-- Other Details Row -->
                                        <div class="row mt-4">
                                            @foreach($dynamicFields as $field => $value)
                                                @if($field !== 'prices')
                                                    <div class="col-12 col-sm-6 col-md-4 mb-3">
                                                        <h6 class="mb-1">{{ translate(ucwords(str_replace('_', ' ', $field))) }}</h6>
                                                        <p class="mb-0">{{ $value }}</p>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Accordion mobile ver -->
            <div class="accordion mb-5" id="guidings-accordion">
                @php
                    $sections = [
                        'accommodation' => [
                            'items' => $vacation->accommodations,
                            'title' => 'Accommodation'
                        ],
                        'boat' => [
                            'items' => $vacation->boats,
                            'title' => 'Boat Information'
                        ],
                        'package' => [
                            'items' => $vacation->packages,
                            'title' => 'Package'
                        ],
                        'guiding' => [
                            'items' => $vacation->guidings,
                            'title' => 'Guiding'
                        ]
                    ];
                @endphp

                @foreach($sections as $sectionKey => $section)
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading{{ $sectionKey }}">
                            <button class="accordion-button {{ $sectionKey === 'accommodation' ? '' : 'collapsed' }}" 
                                    type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#collapse{{ $sectionKey }}" 
                                    aria-expanded="{{ $sectionKey === 'accommodation' ? 'true' : 'false' }}" 
                                    aria-controls="collapse{{ $sectionKey }}">
                                {{ translate($section['title']) }}
                            </button>
                        </h2>
                        <div id="collapse{{ $sectionKey }}" 
                            class="accordion-collapse collapse {{ $sectionKey === 'accommodation' ? 'show' : '' }}" 
                            aria-labelledby="heading{{ $sectionKey }}" 
                            data-bs-parent="#guidings-accordion">
                            <div class="accordion-body">
                                @foreach($section['items'] as $itemIndex => $item)
                                    <div class="card h-100 shadow-sm mb-4">
                                        <div class="card-body">
                                            <div class="row">
                                                <!-- Description Column -->
                                                <div class="col-12 col-lg-7 mb-3 mb-lg-0">
                                                    <h6 class="card-title mb-3">{{ translate($sectionKey . ' ' . ($itemIndex + 1)) }}</h6>
                                                    {!! $item->description !!}
                                                </div>

                                                <!-- Pricing Column -->
                                                <div class="col-12 col-lg-5">
                                                    @php $dynamicFields = json_decode($item->dynamic_fields) @endphp
                                                    @foreach($dynamicFields as $field => $value)
                                                        @if($field === 'prices')
                                                            <div class="mb-3">
                                                                <h6 class="mb-2">{{ translate('Pricing') }}</h6>
                                                                <div class="table-responsive">
                                                                    <table class="table table-sm">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>{{ translate('Persons') }}</th>
                                                                                <th>{{ translate('Price') }}</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach($value as $index => $price)
                                                                                <tr>
                                                                                    <td>{{ $index + 1 }}</td>
                                                                                    <td>€{{ number_format($price, 2, ',', '.') }}</td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>

                                            <!-- Other Details Row -->
                                            <div class="row mt-4">
                                                @foreach($dynamicFields as $field => $value)
                                                    @if($field !== 'prices')
                                                        <div class="col-12 col-sm-6 col-md-4 mb-3">
                                                            <h6 class="mb-1">{{ translate(ucwords(str_replace('_', ' ', $field))) }}</h6>
                                                            <p class="mb-0">{{ $value }}</p>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Description Section -->    
        <!-- Right Column -->
        <div id="book-now" class="guidings-book">
            @if(!$agent->ismobile())
                {{-- @include('pages.guidings.content.bookguiding') --}}
            @endif
        </div>
    </section>

    <!-- Map Section -->
    <div id="map" class="mb-5" style="height: 400px;"></div>

    @if ($sameCountries)
    <section class="tour-details-two mb-5 p-0">
        <div class="container">
            <div class="tour-details-two__related-tours {{$agent->ismobile() ? 'text-center' : ''}}">
                <h3 class="tour-details-two__title">{{ translate('Same Country Vacations') }}</h3>
                <div class="popular-tours__carousel owl-theme owl-carousel">
                    @foreach($sameCountries as $same_country)
                
                        <div class="popular-tours__single">
                            <a class="popular-tours__img" href="{{ route('guidings.show',[$same_country->id,$same_country->slug]) }}" title="Guide aufmachen">
                                <div class="popular-tours__img__wrapper">
                                    @if($same_country->gallery)
                                        <img src="{{ asset($same_country->gallery[0]) }}" alt="{{ $same_country->title }}"/>
                                    @endif
                                </div>
                            </a>

                            <div class="popular-tours__content">
                            <h5 class="crop-text-2 card-title h6">
                                <a href="{{ route('guidings.show', [$same_country->id, $same_country->slug]) }}">
                                    {{ $same_country->title ? translate(Str::limit($same_country->title, 50)) : $same_country->title }}
                                </a>
                            </h5>    
                            <small class="crop-text-1 small-text text-muted">{{ $same_country->location }}</small>
                            <p class="fw-bold text-muted">
                                <span>@lang('message.from') {{ $same_country->getLowestPrice() }}€</span>
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="text-center my-3">
                    <a href="/vacations" class="btn btn-outline-secondary">{{ translate('View all vacations') }}</a>
                </div>
            </div>
        </div>
    </section>
    @endif
</div>
<div class="guidings-book-mobile">
    @if($agent->ismobile())
        {{-- @include('pages.guidings.content.bookguidingmobile') --}}
    @endif
</div>
@endsection

@section('js_after')
<script async src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_API_KEY') }}&callback=initMap"></script>

<script>
$(document).ready(function(){
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
        console.log('{{ $vacation->latitude }}');
        console.log('{{ $vacation->longitude }}');
        var location = { lat: {{ $vacation->latitude ?? 41.40338 }}, lng: {{ $vacation->longitude ?? 2.17403 }} }; // Example coordinates
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

