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

        /* .guidings-gallery .left-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        } */

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

        /* Booking Form Styles */
        .tour-details-two__sidebar {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .booking-type-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        .booking-type-btn {
            flex: 1;
            padding: 10px;
            border: 2px solid #dee2e6;
            background: white;
            color: #495057;
            transition: all 0.3s ease;
            font-weight: 500;
            border-radius: 5px;
        }

        .booking-type-btn:hover {
            background: #f8f9fa;
            border-color: #E85B40;
            color: #E85B40;
        }

        .booking-type-btn.active {
            background: #E85B40;
            color: white;
            border-color: #E85B40;
        }

        .booking-form-container {
            margin-bottom: 20px;
        }

        .booking-form-container label {
            font-weight: 500;
            margin-bottom: 5px;
            color: #495057;
        }

        .booking-form-container .form-control {
            border: 1px solid #dee2e6;
            padding: 8px 12px;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .booking-form-container .form-control:focus {
            border-color: #E85B40;
            box-shadow: 0 0 0 0.2rem rgba(253, 93, 20, 0.25);
        }

        .btn-orange {
            /* background: #E85B40; */
            color: white;
            border: none;
            padding: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-orange:hover {
            background: #e64d0c;
            color: white;
        }

        .booking-options {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        /* Contact Card Styles */
        .contact-card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            margin-left: auto;
            width: 100%;
            max-width: 400px;
        }

        .contact-card__title {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .contact-card__content {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .contact-card__content .contact-info {
            display: flex;
            align-items: center;
        }

        .contact-card__content .btn-outline-orange {
            border-color: #E85B40;
            color: #E85B40;
        }

        .contact-card__content .btn-outline-orange:hover {
            background: #E85B40;
            color: white;
        }

        @media screen and (max-width: 767px) {
            .contact-card {
                margin-left: 0;
            }
        }
    </style>
@endsection

@section('content')
 <div id="guidings-page" class="container vacations-single">
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
                                <i class="bi bi-geo-alt"></i>{{ translate('Fishing Trip in') }} <strong>{{translate($vacation->location)}}</strong>
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
                $thumbnailPath = $vacation->gallery[0] ?? 'images/placeholder_guide.jpg';
                $finalImages = [];
                $overallImages = [];
                $hiddenCount = 0;

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
                $thumbnailPath = $vacation->gallery[0] ?? 'images/placeholder_guide.jpg';
                $finalImages = [];
                $overallImages = [];
                
                // Filter gallery images that exist
                if ($galleryImages) {
                    foreach ($galleryImages as $image) {
                        if (file_exists(public_path($image))) {
                            $overallImages[] = asset($image);
                            if ($image !== $thumbnailPath) {
                                $finalImages[] = asset($image);
                            }   
                        }
                    }
                }
                
                $hiddenCount = count($finalImages) > 2 ? count($finalImages) - 2 : 0;

                if (count($finalImages) > 3) {
                    // More than 3 valid gallery images
                    $finalImages = array_slice($finalImages, 0, 2);
                } else {
                    // 3 or fewer valid gallery images
                    if (count($finalImages) < 3) {
                        $finalImages = $finalImages;
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
                <!-- Boat Availability -->
                <div class="info-item">
                    <i class="fas fa-ship"></i>
                    <small class="mb-0">{{translate('Boat Available:')}}</small>
                    <strong><i class="fas {{ count($vacation->boats) > 0 || count($vacation->packages) > 0 ? 'fa-check text-success' : 'fa-times text-danger' }}"></i></strong>
                </div>

                <!-- Guiding Availability -->
                <div class="info-item">
                    <i class="fas fa-user-tie"></i>
                    <small class="mb-0">Guiding</small>
                    <strong><i class="fas {{ count($vacation->guidings) > 0 ? 'fa-check text-success' : 'fa-times text-danger' }}"></i></strong>
                </div>

                <!-- Pets -->
                <div class="info-item">
                    <i class="fas fa-paw"></i>
                    <small class="mb-0">{{translate('Pets:')}}</small>
                    <strong><i class="fas {{ $vacation->pets_allowed ? 'fa-check text-success' : 'fa-times text-danger' }}"></i></strong>
                </div>
            </div>
    
            <!-- Description Section -->
             <div class="description-container card p-3 mb-5">
                <div class="description-list">
                    <!-- Course of Action -->
                    @if ($vacation->surroundings_description)
                    <div class="description-item">
                        <div class="header-container">
                            <span>{{ translate('Beschreibung')}}</span>
                        </div>
                        <span class="text-wrapper">
                            {!! translate($vacation->surroundings_description) !!}
                        </span>
                    </div>
                    @endif
                    <hr>
                    @if ($vacation->best_travel_times)
                    <div class="description-item">
                        <div class="header-container">
                            <span>{{ translate('Best travel times')}}</span>
                        </div>
                        <p class="text-wrapper">
                           {!! translate(implode(', ', json_decode($vacation->best_travel_times))) !!}
                        </p>
                    </div>
                    @endif
                    @if ($vacation->target_fish)
                    <div class="description-item">
                        <div class="header-container">
                            <span>{{ translate('Target fish') }}</span>
                        </div>
                        <p class="text-wrapper">
                        {!! translate(implode(', ', json_decode($vacation->target_fish))) !!}
                        </p>
                    </div>
                    @endif
                    <div class="row">
                        <div class="col-12">
                            <strong class="subtitle-text">{{ translate('Reiseinformationen') }}</strong>
                        </div>
                        <ol class="px-3">
                            <li class="mx-4">
                                <strong class="subtitle-text">{{ translate('Hin- & Rückreise') }}:</strong>
                                {{ translate($vacation->travel_included)}}
                            </li>
                            <li class="mx-4">
                            @if(!empty($vacation->travel_options))
                                <div class="tab-category mb-4">
                                    <strong class="subtitle-text">{{ translate('Reiseempfehlung') }}:</strong>
                                    <div class="row">
                                        @foreach (json_decode($vacation->travel_options) as $travel_option)
                                            <div class="col-12 text-start">
                                                - {{translate($travel_option)}}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <p class="mb-4">No travel options specified</p>
                            @endif
                            </li>
                        </ol>
                       
                    </div>
                    <hr>
                    <div class="row mx-4">
                        <table class="table">
                            <tbody>
                                @if(!empty($vacation->airport_distance))
                                <tr>
                                    <td><strong>{{ translate('Nächster Flughafen') }}</strong></td>
                                    <td>{{translate($vacation->airport_distance)}}</td>
                                </tr>
                                @endif
                                @if(!empty($vacation->water_distance))
                                <tr>
                                    <td><strong>{{ translate('Entfernung zum Wasser') }}</strong></td>
                                    <td>{{translate($vacation->water_distance)}}</td>
                                </tr>
                                @endif
                                @if(!empty($vacation->shopping_distance))
                                <tr>
                                    <td><strong>{{ translate('Einkaufsmöglichkeiten') }}</strong></td>
                                    <td>{{translate($vacation->shopping_distance)}}</td>
                                </tr>
                                @endif
                                @if(!empty($vacation->pets_allowed))
                                <tr>
                                    <td><strong>{{ translate('Pets Allowed') }}</td>
                                    <td>{{ translate($vacation->pets_allowed || $vacation->pets_allowed !== null || $vacation->pets_allowed !== '' ? 'Yes' : 'No')}}</td>
                                </tr>
                                @endif
                                @if(!empty($vacation->smoking_allowed))
                                <tr>
                                    <td><strong>{{ translate('Smoking Allowed') }}</strong></td>
                                    <td>{{translate($vacation->smoking_allowed || $vacation->smoking_allowed !== null || $vacation->smoking_allowed !== '' ? 'Yes' : 'No')}}</td>
                                </tr>
                                @endif
                                @if(!empty($vacation->disability_friendly))
                                <tr>
                                    <td><strong>{{ translate('Disability Friendly') }}</strong></td>
                                    <td>{{translate($vacation->disability_friendly || $vacation->disability_friendly !== null || $vacation->disability_friendly !== '' ? 'Yes' : 'No')}}</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="tabs-container mb-5">
                <div class="nav nav-tabs" id="guiding-tab" role="tablist">
                    @php 
                        // Initialize activeTab based on available sections
                        $activeTab = '';
                        if (!empty($vacation->accommodations) && count($vacation->accommodations) > 0) {
                            $activeTab = 'accommodation';
                        } elseif (!empty($vacation->boats) && count($vacation->boats) > 0) {
                            $activeTab = 'boat';
                        } elseif (!empty($vacation->packages) && count($vacation->packages) > 0) {
                            $activeTab = 'package';
                        } elseif (!empty($vacation->guidings) && count($vacation->guidings) > 0) {
                            $activeTab = 'guiding';
                        }
                    @endphp
                    
                    @if (!empty($vacation->accommodations) && count($vacation->accommodations) > 0)
                        <button class="nav-link {{ $activeTab == 'accommodation' ? 'active' : '' }}" 
                                id="nav-accommodation-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#nav-accommodation" 
                                type="button" 
                                role="tab" 
                                aria-controls="nav-accommodation" 
                                aria-selected="{{ $activeTab == 'accommodation' ? 'true' : 'false' }}">
                            {{ translate('Accommodation') }}
                        </button>
                    @endif

                    @if (!empty($vacation->boats) && count($vacation->boats) > 0)
                        <button class="nav-link {{ $activeTab == 'boat' ? 'active' : '' }}" 
                                id="nav-boat-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#nav-boat" 
                                type="button" 
                                role="tab" 
                                aria-controls="nav-boat" 
                                aria-selected="{{ $activeTab == 'boat' ? 'true' : 'false' }}">
                            {{ translate('Mietboot') }}
                        </button>
                    @endif

                    @if (!empty($vacation->packages) && count($vacation->packages) > 0)
                        <button class="nav-link {{ $activeTab == 'package' ? 'active' : '' }}" 
                                id="nav-package-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#nav-package" 
                                type="button" 
                                role="tab" 
                                aria-controls="nav-package" 
                                aria-selected="{{ $activeTab == 'package' ? 'true' : 'false' }}">
                            {{ translate('Komplettpaket') }}
                        </button>
                    @endif

                    @if (!empty($vacation->guidings) && count($vacation->guidings) > 0)
                        <button class="nav-link {{ $activeTab == 'guiding' ? 'active' : '' }}" 
                                id="nav-guiding-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#nav-guiding" 
                                type="button" 
                                role="tab" 
                                aria-controls="nav-guiding" 
                                aria-selected="{{ $activeTab == 'guiding' ? 'true' : 'false' }}">
                            Guiding
                        </button>
                    @endif
                </div>
    
                <div class="tab-content mb-5" id="guidings-tabs">
                    @php
                        $sections = [
                            'accommodation' => [
                                'items' => $vacation->accommodations ?? [],
                                'title' => translate('Accommodation')
                            ],
                            'boat' => [
                                'items' => $vacation->boats ?? [],
                                'title' => translate('Mietboot')
                            ],
                            'package' => [
                                'items' => $vacation->packages ?? [],
                                'title' => translate('Komplettpaket')
                            ],
                            'guiding' => [
                                'items' => $vacation->guidings ?? [],
                                'title' => 'Guiding'
                            ]
                        ];
                    @endphp

                    @foreach($sections as $sectionKey => $section)
                        <div class="tab-pane fade {{ $sectionKey === $activeTab ? 'show active' : '' }}" 
                             id="nav-{{ $sectionKey }}" 
                             role="tabpanel" 
                             aria-labelledby="nav-{{ $sectionKey }}-tab">

                            @foreach($section['items'] as $itemIndex => $item)
                                <div class="card tab-card h-100 shadow mb-4">
                                    <div class="card-body">
                                        <div class="row">
                                            <!-- Description Column -->
                                            <div class="col-12 col-lg-7 mb-3 mb-lg-0 tab-item">
                                                <h6 class="card-title mb-3">{{ !empty($item->title) ? translate($item->title) : translate($sectionKey . ' ' . ($itemIndex + 1)) }}</h6>
                                                <span class="text-wrapper">
                                                    {!! translate($item->description) !!}
                                                </span>
                                                <!-- Other Details Row -->
                                                @php $dynamicFields = json_decode($item->dynamic_fields) @endphp
                                                <div class="row mt-4">
                                                    @foreach($dynamicFields as $field => $value)
                                                        @if($field !== 'prices')
                                                            @if($sectionKey === 'accommodation')
                                                                @php
                                                                    // Define priority fields and their order for accommodation
                                                                    $priorityFields = ['bed_count', 'living_area', 'min_rental_days', 'facilities'];
                                                                    
                                                                    // Skip if this field has already been displayed
                                                                    if (in_array($field, $priorityFields) && $field !== current($priorityFields)) {
                                                                        continue;
                                                                    }
                                                                    
                                                                    // Display priority fields first
                                                                    foreach ($priorityFields as $priorityField) {
                                                                        if (isset($dynamicFields->$priorityField)) {
                                                                            echo '<div class="details-row">
                                                                                    <h6 class="mb-1">' . translate(ucwords(str_replace('_', ' ', $priorityField))) . ':</h6>
                                                                                    <p class="mb-0">' . translate($dynamicFields->$priorityField)    . '</p>
                                                                                </div>';
                                                                        }
                                                                    }
                                                                    
                                                                    // Display remaining fields if not in priority list
                                                                    if (!in_array($field, $priorityFields)) {
                                                                        echo '<div class="details-row">
                                                                                <h6 class="mb-1">' . translate(ucwords(str_replace('_', ' ', $field))) . '</h6>
                                                                                <p class="mb-0">' . translate($value) . '</p>
                                                                            </div>';
                                                                    }
                                                                @endphp
                                                            @else
                                                                <div class="details-row">
                                                                    <h6 class="mb-1">{{ translate(ucwords(str_replace('_', ' ', $field))) }}:</h6>
                                                                    <p class="mb-0">{{ translate($value) }}</p>
                                                                </div>
                                                            @endif
                                                        @endif
                                                    @endforeach
                                                </div>
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
                                                                        <th style="width: 50px !important;">{{ translate('Persons') }}</th>
                                                                        <th style="width: 150px !important;">{{ translate('Price') }}</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($value as $index => $price)
                                                                        @php
                                                                            $pricePerPerson = $price / ($index + 1);
                                                                        @endphp
                                                                        <tr>
                                                                            <td style="width: 50px !important;">{{ $index + 1 }}</td>
                                                                            <td style="width: 150px !important;">
                                                                                €{{ number_format($pricePerPerson, (floor($pricePerPerson) == $pricePerPerson) ? 0 : 2, '.', ',') }} p.P.
                                                                            </td>
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
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>

            @if($agent->ismobile())
                <div class="contact-card mb-4">
                    <h5 class="contact-card__title">{{ translate('Contact Card') }}</h5>
                    <div class="contact-card__content">
                        <p class="text-muted">{{ translate('Do you have questions about this vacation? Our team is here to help!') }}</p>
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div class="contact-info">
                                <i class="fas fa-phone-alt me-2"></i>
                                <a href="tel:+49{{env('CONTACT_NUM')}}" class="text-decoration-none">+49 (0) {{env('CONTACT_NUM')}}</a>
                            </div>
                            <a href="{{ route('additional.contact') }}" class="btn btn-outline-orange">
                                {{ translate('Contact Form') }}
                                <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Accordion mobile ver -->
            <div class="accordion mb-5" id="guidings-accordion">
                @php
                    $sections = [
                        'accommodation' => [
                            'items' => $vacation->accommodations,
                            'title' => translate('Accommodation')
                        ],
                        'boat' => [
                            'items' => $vacation->boats,
                            'title' => translate('Mietboot')
                        ],
                        'package' => [
                            'items' => $vacation->packages,
                            'title' => translate('Komplettpaket')
                        ],
                        'guiding' => [
                            'items' => $vacation->guidings,
                            'title' => 'Guiding'
                        ]
                    ];
                @endphp

                @foreach($sections as $sectionKey => $section)
                    <div class="accordion-item">
                    @if (!empty($section['items']) && count($section['items']) > 0)
                        <h2 class="accordion-header" id="heading{{ $sectionKey }}">
                            <button class="accordion-button {{ $sectionKey === $activeTab ? '' : 'collapsed' }}" 
                                    type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#collapse{{ $sectionKey }}" 
                                    aria-expanded="{{ $sectionKey === $activeTab ? 'true' : 'false' }}" 
                                    aria-controls="collapse{{ $sectionKey }}">
                                {{ translate($section['title']) }}
                            </button>
                        </h2>
                    @endif
                        <div id="collapse{{ $sectionKey }}" 
                            class="accordion-collapse collapse {{ $sectionKey === $activeTab ? 'show' : '' }}" 
                            aria-labelledby="heading{{ $sectionKey }}" 
                            data-bs-parent="#guidings-accordion">
                            <div class="accordion-body">
                                @foreach($section['items'] as $itemIndex => $item)
                                    <div class="card h-100 shadow mb-4">
                                        <div class="card-body">
                                            <div class="row">
                                                <!-- Description Column -->
                                                <div class="col-12 col-lg-7 mb-3 mb-lg-0 tab-item">
                                                    <h6 class="card-title mb-3">{{ !empty($item->title) ? translate($item->title) : translate($sectionKey . ' ' . ($itemIndex + 1)) }}</h6>
                                                    <span class="text-wrapper">
                                                        {!! translate($item->description) !!}
                                                    </span>
                                                    <!-- Other Details Row -->
                                                    @php $dynamicFields = json_decode($item->dynamic_fields) @endphp
                                            <div class="row mt-4">
                                                @foreach($dynamicFields as $field => $value)
                                                    @if($field !== 'prices')
                                                        @if($sectionKey === 'accommodation')
                                                            @php
                                                                // Define priority fields and their order for accommodation
                                                                $priorityFields = ['bed_count', 'living_area', 'min_rental_days', 'facilities'];
                                                                
                                                                // Skip if this field has already been displayed
                                                                if (in_array($field, $priorityFields) && $field !== current($priorityFields)) {
                                                                    continue;
                                                                }
                                                                
                                                                // Display priority fields first
                                                                foreach ($priorityFields as $priorityField) {
                                                                    if (isset($dynamicFields->$priorityField)) {
                                                                        echo '<div class="details-row">
                                                                                <h6 class="mb-1">' . translate(ucwords(str_replace('_', ' ', $priorityField))) . ':</h6>
                                                                                <p class="mb-0">' . translate($dynamicFields->$priorityField) . '</p>
                                                                            </div>';
                                                                    }
                                                                }
                                                                
                                                                // Display remaining fields if not in priority list
                                                                if (!in_array($field, $priorityFields)) {
                                                                    echo '<div class="details-row">
                                                                            <h6 class="mb-1">' . translate(ucwords(str_replace('_', ' ', $field))) . '</h6>
                                                                            <p class="mb-0">' . translate($value) . '</p>
                                                                        </div>';
                                                                }
                                                            @endphp
                                                        @else
                                                            <div class="details-row">
                                                                <h6 class="mb-1">{{ translate(ucwords(str_replace('_', ' ', $field))) }}:</h6>
                                                                <p class="mb-0">{{ translate($value) }}</p>
                                                            </div>
                                                        @endif
                                                    @endif
                                                @endforeach
                                            </div>
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
                                                                                @php
                                                                                    $pricePerPerson = $price / ($index + 1);
                                                                                @endphp
                                                                                <tr>
                                                                                    <td>{{ $index + 1 }}</td>
                                                                                    <td>€{{ number_format($pricePerPerson, (floor($pricePerPerson) == $pricePerPerson) ? 0 : 2, '.', ',') }} p.P.</td>
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
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Description Section -->
            @if ( ($vacation->included_services && !empty(json_decode($vacation->included_services))) || ($vacation->extras && count($vacation->extras) > 0))

            <div class="description-container inclusions card p-3 mb-5">
                <div class="description-list">
                    @if ($vacation->included_services && !empty(json_decode(translate($vacation->included_services))))
                        <div class="description-item">
                            <div class="header-container">
                                <span>{{ translate('Included Services')}}</span>
                            </div>
                            <p class="text-wrapper">
                                {!! translate(implode(', ', json_decode($vacation->included_services))) !!}
                            </p>
                        </div>
                    @endif
                    @if ($vacation->extras && count($vacation->extras) > 0)
                        <div class="description-item">
                            <div class="header-container">
                                <span>{{ translate('Extras')}}</span>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>{{ translate('Description') }}</th>
                                            <th>{{ translate('Price') }}</th>
                                            <th style="min-width:100px !important">{{ translate('Price Type') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($vacation->extras as $itemIndex => $item)
                                            <tr>
                                                <td>{{ translate($item->description) }}</td>
                                                <td>€ {{ $item->price }}</td>
                                                <td style="text-transform: capitalize; min-width:100px !important">{{ translate(str_replace('_', ' ', $item->type)) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Description Section -->  
        <!-- Right Column -->
        <div id="book-now" class="vacation-book">
            @if(!$agent->ismobile())
                @include('pages.vacations.content.bookvacation')
            @endif
        </div>
    </section>

    <!-- Map Section -->
    <div id="map" class="mb-5" style="height: 400px;"></div>

    @if ($sameCountries && count($sameCountries) > 0)
    <section class="tour-details-two mb-5 p-0">
        <div class="container">
            <div class="tour-details-two__related-tours {{$agent->ismobile() ? 'text-center' : ''}}">
                <h3 class="tour-details-two__title">{{ translate('Other Vacations in' . $vacation->country) }}</h3>
                <div class="popular-tours__carousel owl-theme owl-carousel">
                    @foreach($sameCountries as $same_country)
                
                        <div class="popular-tours__single">
                            <a class="popular-tours__img" href="{{ route('guidings.show',[$same_country->id,$same_country->slug]) }}" title="{{ translate('Guide aufmachen') }}">
                                <div class="popular-tours__img__wrapper">
                                    @if($same_country->gallery)
                                        <img src="{{ asset($same_country->gallery[0]) }}" alt="{{ $same_country->title }}"/>
                                    @endif
                                </div>
                            </a>

                            <div class="popular-tours__content">
                            <h5 class="crop-text-2 card-title h6">
                                <a href="{{ route('guidings.show', [$same_country->id, $same_country->slug]) }}">
                                    {{ $same_country->title ? translate(Str::limit($same_country->title, 50)) : translate($same_country->title) }}
                                </a>
                            </h5>    
                            <small class="crop-text-1 small-text text-muted">{{ translate($same_country->location) }}</small>
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
<div class="vacations-book-mobile">
<button type="button" class="btn btn-orange w-100" data-bs-toggle="modal" data-bs-target="#vacationModalLabel">
{{ translate('Book Vacation') }}
</button>
<div class="modal fade" id="vacationModalLabel" tabindex="-1" aria-labelledby="vacationModalLabel" aria-hidden="true">
<div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">{{ translate('Book Vacation') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
         @include('pages.vacations.content.bookvacation', ['inModal' => true])
      </div>
    </div>
  </div>
   
</div>
</div>
@endsection

@section('js_after')
<script>

function initMap() {
    var location = { 
        lat: {{ $vacation->latitude ?? 41.40338 }}, 
        lng: {{ $vacation->longitude ?? 2.17403 }} 
    };
    var map = new google.maps.Map(document.getElementById('map'), {
        zoom: 10,
        center: location,
        mapTypeControl: false,
        streetViewControl: false,
        mapId: '8f348c2f6c51f6f0'
    });

    // Create an AdvancedMarkerElement with the required Map ID
    const marker = new google.maps.marker.AdvancedMarkerElement({
        map,
        position: location,
    });
}

document.addEventListener("DOMContentLoaded", function() {
    // Load Google Maps API dynamically with marker library
    if (typeof google === 'undefined') {
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY', 'AIzaSyBiGuDOg_5yhHeoRz-7bIkc9T1egi1fA7Q') }}&libraries=marker&callback=initMap`;
        script.async = true;
        script.defer = true;
        document.head.appendChild(script);
    } else {
        // If Google Maps API is already loaded, just call initMap
        initMap();
    }

    // Define initOwlCarousel function first
    function initOwlCarousel() {
        const $toursList = $(".tours-list__inner");
        const $popularTours = $(".popular-tours__carousel");

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
            $(".guiding-list-item").each(function(index) {
                // Show only the first two items on desktop
                $(this).toggleClass("show", index < 2);
            });
        }

        // Initialize popular tours carousel if it exists
        if ($popularTours.length) {
            $popularTours.owlCarousel({
                loop: true,
                margin: 30,
                nav: true,
                dots: true,
                responsive: {
                    0: {
                        items: 1
                    },
                    768: {
                        items: 2
                    },
                    992: {
                        items: 3
                    }
                }
            });
        }
    }

    // Initialize owl carousel
    initOwlCarousel(); // Initialize on page load
    $(window).resize(initOwlCarousel); // Reinitialize on resize

    // Show More button functionality
    const showMoreBtn = document.getElementById("showMoreBtn");
    if (showMoreBtn) {
        const items = document.querySelectorAll(".guiding-list-item");
        if (items.length > 0) {
            let isExpanded = false;
            showMoreBtn.addEventListener("click", function() {
                isExpanded = !isExpanded;
                items.forEach((item, index) => {
                    item.classList.toggle("show", isExpanded || index < 2);
                });
                showMoreBtn.textContent = isExpanded ? "Show Less" : "Show More";
            });
        }
    }

    // Description text truncation
    const truncateText = (elements) => {
        elements.forEach((item) => {
            const originalText = item.innerHTML.trim();
            const words = originalText.split(/\s+/);
            if (words.length > 30) {
                const truncatedText = words.slice(0, 30).join(" ") + "... ";
                item.innerHTML = truncatedText;
                const toggle = document.createElement("small");
                toggle.textContent = "See More";
                toggle.style.cursor = "pointer";
                toggle.classList.add("text-orange");
                toggle.onclick = () => {
                    const isExpanded = toggle.textContent === "See Less";
                    item.innerHTML = isExpanded ? truncatedText : originalText + " ";
                    toggle.textContent = isExpanded ? "See More" : "See Less";
                    item.appendChild(toggle);
                };
                item.appendChild(toggle);
            }
        });
    };

    // Apply truncation to description items
    truncateText(document.querySelectorAll(".description-item .text-wrapper"));
    truncateText(document.querySelectorAll(".tab-item .text-wrapper"));

    // Comment content functionality
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

    // Booking form functionality
    const typeButtons = document.querySelectorAll('.booking-type-btn');
    const typeInput = document.querySelector('input[name="booking_type"]');
    const packageOptions = document.getElementById('package-options');
    const customOptions = document.getElementById('custom-options');

    if (typeButtons.length && typeInput && packageOptions && customOptions) {
        typeButtons.forEach(button => {
            button.addEventListener('click', function() {
                try {
                    typeButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    const bookingType = this.dataset.type;
                    typeInput.value = bookingType;
                    
                    if (bookingType === 'Komplettpaket') {
                        packageOptions.style.display = 'block';
                        customOptions.style.display = 'none';
                    } else {
                        packageOptions.style.display = 'none';
                        customOptions.style.display = 'block';
                    }
                } catch (error) {
                    console.error('Error in booking type button click handler:', error);
                }
            });
        });
    }

    // Initialize datepicker if element exists
    const datepickerElement = document.getElementById('lite-datepicker');
    if (datepickerElement) {
        const blockedEvents = @json($blocked_events ?? []);
        let lockDays = [];
        
        if (blockedEvents && typeof blockedEvents === 'object') {
            lockDays = Object.values(blockedEvents).flatMap(event => {
                const fromDate = new Date(event.from);
                const dueDate = new Date(event.due);
                const dates = [];
                for (let d = fromDate; d <= dueDate; d.setDate(d.getDate() + 1)) {
                    dates.push(new Date(d));
                }
                return dates;
            });
        }

        new Litepicker({
            element: datepickerElement,
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
    }
});

function initCheckNumberOfColumns() {
    return window.innerWidth < 768 ? 1 : 2;
}
</script>
@endsection