@extends('layouts.app-v2-1')

@if(app()->getLocale() == 'en')
    @section('title',translate($guiding->title))
@else
    @section('title',$guiding->title)
@endif

@section('description',$guiding->excerpt)

@section('share_tags')
    <meta property="og:title" content="{{translate($guiding->title)}}" />
    <meta property="og:description" content="{{translate($guiding->excerpt)}}" />
    @if(!empty(app('guiding')->getImagesUrl($guiding)) && is_array(app('guiding')->getImagesUrl($guiding)) && count(app('guiding')->getImagesUrl($guiding)))
    <meta property="og:image" content="{{app('guiding')->getImagesUrl($guiding)['image_0']}}"/>
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

        .carousel .carousel-control-next, .carousel .carousel-control-prev {
            padding: 3px;
            width: 24px;
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

    </style>
@endsection

@section('content')
 <div id="guidings-page" class="container">
    <div class="title-container">
        <div class="title-wrapper">
            {{-- <h1>Fishing trip in {{$guiding->location}} - {{$guiding->title}}</h1> --}}
            <div class="row px-2">
                <div class="col-24 col mb-1 guiding-title">
                    <h1>
                    {{ translate($guiding->title) }}
                    </h1>
                    <a class="btn" href="#" role="button"><i data-lucide="share-2"></i></a>
                </div>
                <div class="col-12">
                    <div class="location-row">
                        <div class="location">
                            <a href="#" class="fs-6 text-decoration-none text-muted">
                                    <i class="bi bi-geo-alt"></i>@lang('guidings.Fishing_Trip') <strong>{{$guiding->location}}</strong>
                                </a>
                        </div>
                        <div class="location-map">
                            <a href="#map" class="fs-6 text-decoration-none text-muted">
                                <span class="text-primary">{{translate('Show on map')}}</span>
                            </a>

                        </div>
                    </div>
                </div>
                <div class="col-auto pe-0 me-1">
                  
                    <p class="mb-1">
                        <span class="text-warning">★</span> {{$average_rating}}/{{$ratings->count()}} ({{$ratings->count()}} reviews)
                    </p>
                </div>
                <div class="col-auto p-0">
                </div>
            </div>
            <div class="col-12 col-lg-6 title-right-container">
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
                <img data-bs-toggle="modal" data-bs-target="#galleryModal" src="{{asset($guiding->thumbnail_path)}}" class="img-fluid" alt="Main Image">
            </div>
            <div class="right-images">
                <div class="gallery">
                @php
                    $galleryImages = json_decode($guiding->gallery_images,true);
                    $thumbnailPath = asset($guiding->thumbnail_path);
                    $finalImages = [];
                    $hiddenCount = count($galleryImages) > 4 ? count($galleryImages) - 4 : 0;
                    if (!$galleryImages || count($galleryImages) === 0) {
                        // No gallery images, duplicate thumbnailPath 4 times
                        $finalImages = array_fill(0, 4, $thumbnailPath);
                    } elseif (count($galleryImages) > 5) {
                        // More than 5 gallery images, exclude the thumbnailPath and take the first 4 gallery images
                        $finalImages = array_slice(array_filter($galleryImages, function($image) use ($thumbnailPath) {
                            return asset($image) !== $thumbnailPath;
                        }), 0, 4);
                    } else {
                        // Less than or equal to 5 gallery images
                        $filteredGallery = array_filter($galleryImages, function($image) use ($thumbnailPath) {
                            return asset($image) !== $thumbnailPath;
                        });
                        
                        if (count($filteredGallery) < 4) {
                            $finalImages = $filteredGallery;

                            if (count($finalImages) == 2) {
                                // Add the thumbnailPath and the first gallery image to complete 4 items
                                $finalImages[] = $thumbnailPath;
                                $finalImages[] = $filteredGallery[0];
                            } elseif (count($finalImages) == 1) {
                                // Add thumbnailPath and repeat the 1 gallery image and thumbnail to make 4 items
                                $finalImages[] = $thumbnailPath;
                                $finalImages[] = $filteredGallery[0];
                                $finalImages[] = $thumbnailPath;
                            } else {
                                // Add thumbnailPath until the list reaches 4 items
                                while (count($finalImages) < 4) {
                                    $finalImages[] = $thumbnailPath;
                                }
                            }
                        } else {
                            $finalImages = $filteredGallery;
                        }
                    }
                @endphp

                    @foreach ($finalImages as $index => $image)
                        @if ($index < 3)
                            <div class="gallery-item">
                                <img src="{{asset($image)}}" class="img-fluid" alt="Gallery Image {{ $index + 1 }}" data-bs-toggle="modal" data-bs-target="#galleryModal" data-image="{{ asset($image) }}">
                            </div>
                        @elseif ($index == 3 && $hiddenCount !== 0)
                            <div class="gallery-item">
                                <img src="{{asset($image)}}" class="img-fluid" alt="Gallery Image {{ $index + 1 }} (and {{ $hiddenCount }} more)"  data-image="{{ asset($image) }}">
                                <span data-bs-toggle="modal" data-bs-target="#galleryModal" class="position-absolute" style="top: 50%; left: 50%; transform: translate(-50%, -50%);">+{{ $hiddenCount }} more</span>
                            </div>
                        @elseif ($index == 3)
                            <div class="gallery-item">
                                <img src="{{asset($image)}}" class="img-fluid" alt="Gallery Image {{ $index + 1 }} (and {{ $hiddenCount }} more)"  data-image="{{ asset($image) }}">
                            </div>
                        @endif
                    @endforeach
                </div>
                <div class="gallery-mobile">
                    @php
                    $galleryImages = json_decode($guiding->gallery_images ?? '[]');
                    $thumbnailPath = asset($guiding->thumbnail_path);
                    $finalImages = [];
                    $hiddenCount = count($galleryImages) > 2 ? count($galleryImages) - 2 : 0;
                    if (!$galleryImages || count($galleryImages) === 0) {
                        // No gallery images, duplicate thumbnailPath 4 times
                        $finalImages = array_fill(0, 2, $thumbnailPath);
                    } elseif (count($galleryImages) > 3) {
                        // More than 5 gallery images, exclude the thumbnailPath and take the first 4 gallery images
                        $finalImages = array_slice(array_filter($galleryImages, function($image) use ($thumbnailPath) {
                            return asset($image) !== $thumbnailPath;
                        }), 0, 2);
                    } else {
                        // Less than or equal to 5 gallery images
                        $filteredGallery = array_filter($galleryImages, function($image) use ($thumbnailPath) {
                            return asset($image) !== $thumbnailPath;
                        });
                        
                        if (count($filteredGallery) < 3) {
                            $finalImages = $filteredGallery;

                            if (count($finalImages) == 2) {
                                // Add the thumbnailPath and the first gallery image to complete 4 items
                                $finalImages[] = $thumbnailPath;
                                $finalImages[] = $filteredGallery[0];
                            } elseif (count($finalImages) == 1) {
                                // Add thumbnailPath and repeat the 1 gallery image and thumbnail to make 4 items
                                $finalImages[] = $thumbnailPath;
                                $finalImages[] = $filteredGallery[0];
                                $finalImages[] = $thumbnailPath;
                            } else {
                                // Add thumbnailPath until the list reaches 4 items
                                while (count($finalImages) < 3) {
                                    $finalImages[] = $thumbnailPath;
                                }
                            }
                        } else {
                            $finalImages = $filteredGallery;
                        }
                    }
                @endphp
                    @foreach ($finalImages as $index => $image)
                        @if ($index < 1)
                            <div class="gallery-item">
                                <img src="{{ asset($image) }}" class="img-fluid" alt="Gallery Image {{ $index + 1 }}" data-bs-toggle="modal" data-bs-target="#galleryModal" data-image="{{ asset($image) }}">
                            </div>
                        @elseif ($index == 1)
                            <div class="gallery-item">
                                <img src="{{ asset($image) }}" class="img-fluid" alt="Gallery Image {{ $index + 1 }} (and {{ $hiddenCount }} more)" data-image="{{ asset($image) }}">
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
                            @foreach ($galleryImages as $image)
                                <div class="col-12 mb-2">
                                    <img src="{{ asset($image) }}" class="img-fluid" alt="Gallery Image" />
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
                        <strong><p class="mb-0">{{$guiding->is_boat ? ($guiding->boat_type || $guiding->boat_type !== '' && $guiding->boatType['name'] !== null ? $guiding->boatType['name'] : __('guidings.boat')) : __('guidings.shore')}}</p></strong>
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

                        <p class="mb-0">{{ __('guidings.'.$guiding->duration_type) }} : <strong>{{$guiding->duration}} {{ $guiding->duration_type == 'multi_day' ? __('guidings.days') : __('guidings.hours') }}</strong></p>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-users"></i>
                       <p class="mb-0">{{translate('Number of guests:')}} <strong>{{$guiding->max_guests}}</strong></p>
                    </div>
            </div>
    
            <!-- Description Section -->
            <div class="description-container card p-3 mb-5">
                <div class="description-list">
                    <!-- Course of Action -->
                    <div class="description-item">
                        <div class="header-container">
                            <span>{{translate('Course of action')}}</span>
                        </div>
                        <p class="text-wrapper">
                           {!! $guiding->desc_course_of_action !!}
                        </p>
                    </div>
                    <div class="description-item">
                        <div class="header-container">
                            <span>Tour highlights</span>
                        </div>
                        <p class="text-wrapper">
                        {!! $guiding->desc_tour_unique !!}
                        </p>
                    </div>
                    
                    <div class="row">
                        <!-- Starting Time -->
                        @if ($guiding->desc_starting_time)
                        <div class="description-item col-12 col-md-6">
                            <div class="header-container">
                                <span> @lang('guidings.Starting_Time')</span>
                            </div>
                            <p>{!! $guiding->desc_starting_time !!}</p>
                        </div>
                        @endif
                        <!-- Meeting Point -->
                        @if ($guiding->desc_meeting_point)
                        <div class="description-item col-12 col-md-6">
                            <div class="header-container">
                            <span> @lang('guidings.Meeting_Point')</span>
                            </div>
                            <p>{!! $guiding->desc_meeting_point !!}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="tabs-container mb-5">
                <div class="nav nav-tabs" id="guiding-tab" role="tablist">
                    <button class="nav-link active" id="nav-fishing-tab" data-bs-toggle="tab" data-bs-target="#fishing" type="button" role="tab" aria-controls="nav-fishing" aria-selected="true">@lang('guidings.Tour_Info')</button>
                    <button class="nav-link" id="nav-include-tab" data-bs-toggle="tab" data-bs-target="#include" type="button" role="tab" aria-controls="nav-include" aria-selected="false">@lang('guidings.Inclusions')</button>
                    @if ($guiding->is_boat)
                    <button class="nav-link" id="nav-boat-tab" data-bs-toggle="tab" data-bs-target="#boat" type="button" role="tab" aria-controls="nav-boat" aria-selected="false">@lang('guidings.Boat_Details')</button>
                    @endif
                    <button class="nav-link" id="nav-info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab" aria-controls="nav-info" aria-selected="false">@lang('guidings.Additional_Info')</button>
                </div>
    
                <div class="tab-content mb-5" id="guidings-tabs">
    
                    <!-- What's Included Tab -->
                    <div class="tab-pane fade" id="include" role="tabpanel" aria-labelledby="nav-include-tab">
                        <div class="row">
                            <div class="col-6">
                                @if(!empty($guiding->inclusions))
                                    <div class="row">
                                        <strong class="mb-2 subtitle-text">@lang('guidings.Inclusions')</strong>
                                        @foreach ($guiding->getInclusionNames() as $index => $inclusion)
                                            <div class="col-12 mb-2 text-start">
                                            <i data-lucide="wrench"></i> {{$inclusion['name']}}
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p>No inclusions specified</p>
                                @endif
                            </div>
                            <div class="col-6">
                            @if(!empty(json_decode($guiding->pricing_extra)))
                                    <div class="row">
                                        <strong class="mb-2 subtitle-text">Additional Extras</strong>
                                        @foreach (json_decode($guiding->pricing_extra) as $pricing_extras)
                                            <div class="mb-2">
                                                <strong>{{$pricing_extras->name}}</strong> 
                                                <span>{{$pricing_extras->price}}€ p.P</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
    
                    <!-- Fishing Experience Tab -->
                    <div class="tab-pane fade show active" id="fishing" role="tabpanel" aria-labelledby="nav-fishing-tab">
                    <div class="row">
                        
                        @if(!empty($guiding->target_fish))
                            <div class="tab-category mb-4 col-12 col-lg-4">
                                <strong class="subtitle-text">@lang('guidings.Target_Fish')</strong>
                                <div class="row">
                                    @foreach ($guiding->getTargetFishNames() as $fish)
                                        <div class="col-12 text-start">
                                            {{$fish['name']}}
                                        </div>
                                        @if(($loop->index + 1) % 2 == 0)
                                            </div><div class="row">
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @else
                        <!-- Fish Section -->
                            <p class="mb-4">No fish specified</p>
                        @endif
                        <!-- Methods Section -->
                        @if(!empty($guiding->fishing_methods))
                            <div class="tab-category mb-4 col-12 col-lg-4">
                                <strong class="subtitle-text">@lang('guidings.Fishing_Method')</strong>
                                <div class="row">
                                    @foreach ($guiding->getFishingMethodNames() as $index => $fishing_method)
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
                        @if(!empty($guiding->water_types))
                            <div class="tab-category mb-4 col-12 col-lg-4">
                                <strong class="subtitle-text">@lang('guidings.Water_Type')</strong>
                                <div class="row">
                                    @foreach ($guiding->getWaterNames() as $water)
                                        <div class="col-12 text-start">
                                            {{$water['name']}}
                                        </div>
                                        @if(($loop->index + 1) % 2 == 0)
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
    
                    @php
                        $boatInformation = $guiding->getBoatInformationAttribute();
                    @endphp
                <div class="tab-pane fade" id="boat" role="tabpanel" aria-labelledby="nav-boat-tab">
                    <div class="row">
                        <div class="col-md-12">
                            @if(!empty($boatInformation))
                            <strong class="subtitle-text">{{translate('Boat')}}</strong>
                            <!-- Boat Information as a Table -->
                            <table class="table ">
                                <tbody>
                                    @foreach($boatInformation as $key => $value)
                                    <tr>
                                        <th>{{$value['name']}}</th>
                                        <td colspan="1">{{ $value['value'] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @else
                            <p>No boat information specified</p>
                            @endif
                        </div>
    
                        <div class="col-md-6">
                            @if(!empty($guiding->boat_extras))
                                <strong class="subtitle-text">@lang('guidings.Boat_Extras'):</strong>
                                <!-- Boat Extras as a List -->
                                <ul>
                                    @foreach(json_decode($guiding->boat_extras) as $extra)
                                        <li>{{ $extra->value }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
    
                    @if(empty($guiding->boat_type) && empty($guiding->boat_information) && empty($guiding->boat_extras))
                        <p>No boat information specified</p>
                    @endif
                </div>
    
    
                    <!-- Important Information Tab -->
                    <div class="tab-pane fade" id="info" role="tabpanel" aria-labelledby="nav-info-tab">
    
                    <!-- Requirements Section -->
                    @if(!empty($guiding->requirements) && $guiding->requirements !== null && $guiding->requirements->count() > 0)
                        <div class="tab-category mb-4">
                            <strong class="subtitle-text">@lang('guidings.Requirements')</strong>
                            <div class="row">
                                @foreach ($guiding->getRequirementsAttribute() as $requirements)
                                    <div class="col-12 text-start">
                                        <ul>
                                            <li>
                                                <strong>{{ $requirements['name'] }}:</strong> {{ $requirements['value'] ?? '' }}
                                            </li>
                                        </ul>
                                    </div>
                                    @if(($loop->index + 1) % 2 == 0)
                                        </div><div class="row">
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        <hr/>
                    @endif
                    <!-- Other Information Section -->
                    @if(!empty($guiding->other_information) && $guiding->other_information !== null && $guiding->other_information->count() > 0)
                        <div class="tab-category mb-4">
                            <strong class="subtitle-text">@lang('guidings.Other_Info')</strong>
                            <div class="row">
                                @foreach ($guiding->getOtherInformationAttribute() as $otherIndex => $other)
                                    <div class="col-12 text-start">
                                    <ul>
                                    <li>
                                        <strong>{{ $other['name'] }}:</strong> {{ $other['value'] ?? '' }}
                                    </li>
                                    </ul>
                                    </div>
                                    @if(($loop->index + 1) % 2 == 0)
                                        </div><div class="row">
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        <hr/>
                    @endif
                    <!-- Recommended Preparation Section -->
                    @if(!empty($guiding->recommendations ) || $guiding->recommendations !== null && $guiding->recommendations->count() > 0)
                        <div class="tab-category mb-4">
                            <strong class="subtitle-text">@lang('guidings.Reco_Prep')</strong>
                            <div class="row">
                                @foreach ($guiding->getRecommendationsAttribute() as $recIndex => $recommendations)
                                    <div class="col-12 text-start">
                                    <ul>
                                    <li>
                                        <strong>{{ $recommendations['name'] }}:</strong> {{ $recommendations['value'] ?? '' }}
                                    </li>
                                    </ul>
                                    </div>
                                    @if(($loop->index + 1) % 2 == 0)
                                        </div><div class="row">
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        <hr/>
                    @endif
                    <!-- Essential Details Section -->
                    <div class="row mb-4">
    
                        <div class="col-md-6">
                            @if(!empty($guiding->style_of_fishing))
                                    <strong class="subtitle-text">@lang('guidings.Style_Fishing'):</strong> 
                                    <span class="">{{ $guiding->style_of_fishing }}</span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if(!empty($guiding->tour_type))
                                <div>
                                    <strong class="subtitle-text">@lang('guidings.Tour_Type'):</strong> 
                                    <span class="">{{ $guiding->tour_type }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
    
                    @if(empty($guiding->experience_level) && empty($guiding->tour_type) && empty($guiding->style_of_fishing))
                        <p>No information specified</p>
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
                    @if(!empty($guiding->inclusions))
                        @php
                            $inclussions = $guiding->getInclusionNames();
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
                <div class="col-12 mb-4">
                    @if(!empty($guiding->pricing_extra))
                        <div class="row">
                            <strong class="mb-2 subtitle-text">@lang('guidings.Inclusions')</strong>
                            @foreach (json_decode($guiding->pricing_extra) as $pricing_extras)
                                <div class="col-12 text-start">
                                    <i data-lucide="wrench"></i> {{$pricing_extras->name}}
                                    <i data-lucide="wrench"></i> {{$pricing_extras->price}}€ p.P
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p>No inclusions specified</p>
                    @endif
                </div>
                <div class="col-12">
                @if(!empty(json_decode($guiding->pricing_extra)))
                    <div class="row">
                        <strong class="mb-2 subtitle-text">Additional Extras</strong>
                        @foreach (json_decode($guiding->pricing_extra) as $pricing_extras)
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
                @if(!empty($guiding->target_fish))
                    <div class="col-12 mb-4">
                        <strong class="subtitle-text"> @lang('guidings.Target_Fish')</strong>
                        <div class="row">
                            @foreach ($guiding->getTargetFishNames() as $index => $target_fish)
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
                @if(!empty($guiding->fishing_methods))
                    <div class="col-12 mb-4">
                        <strong class="subtitle-text"> @lang('guidings.Fishing_Method')</strong>
                        <div class="row">
                            @foreach ($guiding->getFishingMethodNames() as $index => $fishing_method)
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
                @if(!empty($guiding->water_types))
                    <div class="col-12 mb-3">
                        <strong class="subtitle-text"> @lang('guidings.Water_Type')</strong>
                        <div class="row">
                            @foreach ($guiding->getWaterNames() as $index => $water_type)
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

<!-- Boat Information Accordion -->
<div class="accordion-item">
    <h2 class="accordion-header" id="headingBoat">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBoat" aria-expanded="false" aria-controls="collapseBoat">
        @lang('guidings.Boat_Details')
        </button>
    </h2>
    <div id="collapseBoat" class="accordion-collapse collapse" aria-labelledby="headingBoat" data-bs-parent="#accordionTabs">
        <div class="accordion-body">
            @if(!empty($guiding->boat_information))
                <strong class="subtitle-text">{{translate('Boat')}}:</strong>
                <table class="table my-4">
                    <tbody>
                        @php $boatInformation = json_decode($guiding->boat_information, true); @endphp
                        <tr><th>Seats</th><td>{{ $boatInformation['seats'] ?? '' }}</td></tr>
                        <tr><th>Length</th><td>{{ $boatInformation['length'] ?? '' }}</td></tr>
                        <tr><th>Width</th><td>{{ $boatInformation['width'] ?? '' }}</td></tr>
                        <tr><th>Year Built</th><td>{{ $boatInformation['year_built'] ?? '' }}</td></tr>
                        <tr><th>Engine Manufacturer</th><td>{{ $boatInformation['engine_manufacturer'] ?? '' }}</td></tr>
                        <tr><th>Engine Power (hp)</th><td>{{ $boatInformation['engine_power'] ?? '' }}</td></tr>
                        <tr><th>Max Speed</th><td>{{ $boatInformation['max_speed'] ?? '' }}</td></tr>
                        <tr><th>Manufacturer</th><td>{{ $boatInformation['manufacturer'] ?? '' }}</td></tr>
                    </tbody>
                </table>
            @else
                <p>No boat information specified</p>
            @endif

            <!-- Boat Extras Section -->
            @if(!empty($guiding->boat_extras))
                <strong class="subtitle-text"> @lang('guidings.Boat_Extras'):</strong>
                <ul>
                    @foreach(json_decode($guiding->boat_extras) as $extra)
                        <li>{{ $extra->value }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>

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
            @if(!empty($guiding->requirements))
                <strong class="subtitle-text">@lang('guidings.Requirements')</strong>
                <ul>
                    @foreach ($guiding->getRequirementsAttribute() as $requirements)
                        <li><span>{{ $requirements['name'] }}:</span> {{ $requirements['value'] ?? '' }}</li>
                    @endforeach
                </ul>
            @else
                <p>No requirements specified</p>
            @endif
            <hr/>
            <!-- Other Information Section -->
            @if(!empty($guiding->other_information))
                <strong class="subtitle-text">@lang('guidings.Other_Info')</strong>
                <ul>
                    @foreach ($guiding->getOtherInformationAttribute() as $otherIndex => $other)
                        <li><span>{{ $other['name'] }}:</span> {{ $other['value'] ?? '' }}</li>
                    @endforeach
                </ul>
            @else
                <p>No other information specified</p>
            @endif
            <hr/>
            <!-- Recommended Preparation Section -->
            @if(!empty($guiding->recommendations))
                <strong class="subtitle-text">@lang('guidings.Reco_Prep')</strong>
                <ul>
                    @foreach ($guiding->getRecommendationsAttribute() as $recIndex => $recommendations)
                        <li><span>{{ $recommendations['name'] }}:</span> {{ $recommendations['value'] ?? '' }}</li>
                    @endforeach
                </ul>
            @else
                <p>No recommendations specified</p>
            @endif
            <hr/>
            <div class="row p-0">
                <div class="col-md-6">
                    @if(!empty($guiding->style_of_fishing))
                            <strong class="subtitle-text">@lang('guidings.Style_Fishing'):</strong> 
                            <span class="">{{ $guiding->style_of_fishing }}</span>
                    @endif
                </div>
                <div class="col-md-6">
                    <hr/>
                </div>
                <div class="col-md-6">
                    @if(!empty($guiding->tour_type))
                        <div>
                            <strong class="subtitle-text">@lang('guidings.Tour_Type'):</strong> 
                            <span class="">{{ $guiding->tour_type }}</span>
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
                @include('pages.guidings.content.bookguiding')
            @endif
        </div>
    </section>

    <!-- Map Section -->
    <div id="map" class="mb-5" style="height: 400px;">
        <!-- Google Map will be rendered here -->
    </div>

    <!-- Rating Summary -->
    <div class="guidings-rating mb-5">
    @if(round($average_rating) > 0)
        <div class="ratings-head">
            <h3>Bewertungen</h3>
            <div class="ratings-score-ave">
                <div class="ratings-card">
                    <span class="text-warning">★</span> {{$average_rating}}/5 ({{ $guiding->user->received_ratings->count() }} reviews)
                </div>
            </div>
        </div>
        <div class="ratings-slider owl-carousel">
            @foreach($guiding->user->received_ratings as $received_rating)
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
            @endforeach
        </div>
    @endif
</div>


    <div class="mb-5">
        <div class="tour-details-two__about">
            <div class="row">
                <div class="col-md-3 wow fadeInLeft" data-wow-duration="1500ms">
                    <div class="about-one__left">
                        <div class="about-one__img-box">
                            <div class="tour-details__review-comment-top-img">
                                @if($guiding->user->profil_image)
                                    <img class="center-block rounded-circle"
                                         src="{{asset('images/'. $guiding->user->profil_image)}}" alt="" width="200px"
                                         height="200px">
                                @else
                                    <img class="center-block rounded-circe"
                                         src="{{asset('images/placeholder_guide.jpg')}}" alt="" width="200px"
                                         height="200px">
                                @endif

                            </div>
                            <h4 class="mt-3"
                                style="text-align: center">{{$guiding->user->firstname}}</h4>
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
                                    <p><b>{{ translate('Lieblingsfisch') }}:</b>{{ translate($guiding->user->information['favorite_fish']) }}
                                    </p>
                                </div>
                            </li>
                            <li>
                                <div class="icon-small">
                                    <i class="fa fa-check"></i>
                                </div>
                                <div class="text">
                                    <p>
                                        <b>{{ translate('Sprachen') }}:</b> {{ translate($guiding->user->information['languages']) }}
                                    </p>
                                </div>
                            </li>
                            <li>
                                <div class="icon-small">
                                    <i class="fa fa-check"></i>
                                </div>
                                <div class="text">
                                    <p>
                                        <b>{{ translate('Angelt seit') }}:</b> {{ $guiding->user->information['fishing_start_year'] }}
                                    </p>
                                </div>
                            </li>
                        </ul>

                        <p class="js-trigger-more-text"><b>{{ translate('Über mich') }}:</b>
                            {!! translate($guiding->aboutme()[0]) !!}
                            {!! translate($guiding->aboutme()[1]) !!}
                        </p>
                        <button class="thm-btn js-btn-more-text" onclick="moreOrLessFunction(this)">{{ translate('Mehr') }} </button>
                    </div>


                </div>
            </div>
        </div>
    </div>
    @if($same_guiding)
    <section class="tour-details-two mb-5 p-0">
        <div class="container">
    <div class="row">
        <div class="col-lg-12 col-sm-12">
            <h3 class="tour-details-two__title">@lang('guidings.More_Fishing') {{$guiding->user->firstname}}</h3>
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
                                                        <img class="d-block" src="{{asset($gallery_image_link)}}" style="width:300px; height: 240px;">
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
                                    <a href="{{ route('guidings.show', [$guiding->id, $guiding->slug]) }}">
                                            <div class="guidings-item">
                                                <div class="guidings-item-title">
                                                    <h5 class="fw-bolder text-truncate">{{translate($guiding->title)}}</h5>
                                                    <span class="text-center"><i class="fas fa-map-marker-alt me-2"></i>{{ translate($guiding->location) }}</span>                                      
                                                </div>
                                                @if ($guiding->user->average_rating())
                                                <div class="guidings-item-ratings">
                                                <div class="ratings-score">
                                                <span class="text-warning">★</span>
                                                        <span>{{$guiding->user->average_rating()}} </span>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                            <div class="guidings-item-icon">
                                                <div class="guidings-icon-container"> 
                                                            <img src="{{asset('assets/images/icons/clock-new.svg')}}" height="20" width="20" alt="" />
                                                        <div class="">
                                                            {{ $guiding->duration }} @if($guiding->duration != 1) {{translate('Stunden')}} @else {{translate('Stunde')}} @endif
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
                                                                $guidingTargets = collect($guiding->getTargetFishNames())->pluck('name')->toArray();
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
                                                                {{$guiding->is_boat ? ($guiding->boat_type || $guiding->boat_type !== '' && $guiding->boatType['name'] !== null ? $guiding->boatType['name'] : __('guidings.boat')) : __('guidings.shore')}}
                                                            </div>
                                                        
                                                        </div>
                                                </div>
                                            </div>
                                            <div class="inclusions-price">
                                                    <div class="guidings-inclusions-container">
                                                        @if(!empty($guiding->getInclusionNames()))
                                                        <div class="guidings-included">
                                                            <strong>What's Included</strong>
                                                            <div class="inclusions-list">
                                                                @php
                                                                    $inclussions = $guiding->getInclusionNames();
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
                </div>

                <div class="text-center">
                    <button id="showMoreBtn" class="btn btn-orange mt-3 text-center">{{ translate('Show More') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
    </section>
    @endif
    <section class="tour-details-two mb-5 p-0">
        <div class="container">

            <div class="tour-details-two__related-tours {{$agent->ismobile() ? 'text-center' : ''}}">
                <h3 class="tour-details-two__title">{{ translate('Matching Guidings') }}</h3>
                <div class="popular-tours__carousel owl-theme owl-carousel">
                    @foreach($other_guidings as $other_guiding)
                
                        <div class="popular-tours__single">
                            <a class="popular-tours__img" href="{{ route('guidings.show',[$other_guiding->id,$other_guiding->slug]) }}" title="Guide aufmachen">
                                <figure class="popular-tours__img__wrapper">
                                        @if($other_guiding->thumbnail_path)
                                            <img src="{{ asset($other_guiding->thumbnail_path) }}" alt="{{ $other_guiding->title }}"/>
                                        @endif
                                </figure>
                            </a>

                            <div class="popular-tours__content">
                                <h5 class="crop-text-2 card-title h6"><a href="{{ route('guidings.show', [$other_guiding->id,$other_guiding->slug]) }}">{{  $other_guiding->title ?  translate( $other_guiding->title) :  $other_guiding->title }}</a>
                                </h5>
                                <small class="crop-text-1 small-text text-muted">{{ $other_guiding->location ? translate($other_guiding->location) : $other_guiding->location }}</small>
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
</div>
<div class="guidings-book-mobile">
            @if($agent->ismobile())
                @include('pages.guidings.content.bookguidingmobile')
            @endif
        </div>
@endsection

@section('js_after')
<script async src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_API_KEY') }}&callback=initMap"></script>

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
        var location = { lat: 41.40338, lng: 2.17403 }; // Example coordinates
        var map = new google.maps.Map(document.getElementById('map'), {
            zoom: 10,
            center: location
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
        const blockedEvents = JSON.parse('{!! json_encode($blocked_events) !!}');

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
    const totalItems = {{ $same_guiding->count() }};
    
    document.getElementById('show-more').addEventListener('click', function() {
        const container = document.getElementById('same-guidings-container');
        
        // Fetch the next set of items
        for (let i = currentCount; i < currentCount + 3 && i < totalItems; i++) {
            const guiding = @json($same_guiding); // Convert PHP variable to JavaScript
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

