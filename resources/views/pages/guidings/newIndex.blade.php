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
    @if(count(app('guiding')->getImagesUrl($guiding)))
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
    <div class="row">
        <div class="mb-3 col-6">
            {{-- <h1>Fishing trip in {{$guiding->location}} - {{$guiding->title}}</h1> --}}
            <div class="row px-2">
                <div class="col-12 mb-2">
                    <h1>
                        {{$guiding->title}}
                    </h1>
                </div>
                <div class="col-12">
                <p class="mb-1">
                        <span class="text-warning">★</span> {{$average_rating}}/5 (4 reviews)
                    </p>
                </div>
                <div class="col-auto pe-0 me-1">
                    <a href="#" class="fs-6 text-decoration-none text-muted">
                        <i class="bi bi-geo-alt"></i> Fishing trip in <strong>{{$guiding->location}}</strong>
                    </a>
                </div>
                <div class="col-auto p-0">
                <a href="#" class="fs-6 text-decoration-none text-muted">
                        <span class="text-primary">Show on map</span>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-6 title-right-container">
            <div class="title-right-buttons">
                <a class="btn" href="#" role="button"><i data-lucide="share-2"></i></a>
                <button type="button" class="btn btn-outline-primary">Book now</button>
            </div>
            <span>Best price guarantee</span>
            </div>
        <!-- Image Gallery -->
        <div class="guidings-gallery row mx-0 mb-3">
            <div class="left-image">
                <img src="{{asset($guiding->thumbnail_path)}}" class="img-fluid" alt="Main Image">
            </div>
            <div class="right-images">
                <div class="gallery">
                    @php
                        $galleryImages = json_decode($guiding->galery_images);
                        $thumbnailPath = asset($guiding->thumbnail_path);
                        $filteredImages = array_filter($galleryImages, function($image) use ($thumbnailPath) {
                            return asset($image) !== $thumbnailPath; // Exclude thumbnail from gallery
                        });
                        $hiddenCount = count($galleryImages) > 4 ? count($galleryImages) - 4 : 0;
                    @endphp
                    @foreach ($filteredImages as $index => $image)
                        @if ($index < 4)
                            <div class="gallery-item">
                                <img src="{{asset($image)}}" class="img-fluid" alt="Gallery Image {{ $index + 1 }}" data-bs-toggle="modal" data-bs-target="#galleryModal" data-image="{{ asset($image) }}">
                            </div>
                        @elseif ($index == 4)
                            <div class="gallery-item">
                                <img src="{{asset($image)}}" class="img-fluid" alt="Gallery Image {{ $index + 1 }} (and {{ $hiddenCount }} more)"  data-image="{{ asset($image) }}">
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

    </div>
    <section class="guidings-description-container">
        <!-- Left Column -->
        <div class="guidings-descriptions">
            <!-- Title, Rating, and Location -->
    
            <!-- Important Information -->
            <div class="important-info">
                <div class="row">
                    <div class="col-md-4 d-flex align-items-center">
                        <i class="fas fa-ship me-3"></i>
                        <strong><p class="mb-0">{{$guiding->is_boat ? $guiding->boat_type : 'Shore'}}</p></strong>
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <i class="far fa-clock me-3"></i>
                        <p class="mb-0">{{ ucfirst(str_replace('_', ' ', $guiding->duration_type)) }} : <strong>{{$guiding->duration}} {{ $guiding->duration_type == 'multi_day' ? 'day/s' : 'hour/s' }}</strong></p>
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <i class="fas fa-users me-3"></i>
                       <p class="mb-0">Number of guests: <strong>{{$guiding->max_guests}}</strong></p>
                    </div>
                </div>
            </div>
    
            <!-- Description Section -->
            <div class="description-container mb-3">
                <div class="description-list">
                    <!-- Course of Action -->
                    <div class="description-item">
                        <div class="header-container">
                            <span><i data-lucide="map"></i> Course of action</span>
                        </div>
                        <p>{!! $guiding->desc_course_of_action !!}</p>
                    </div>
                    
                    <!-- Starting Time -->
                    <div class="description-item">
                    <div class="header-container">
                        <span><i data-lucide="clock"></i> Starting time</span>
                    </div>
                    <p>{!! $guiding->desc_starting_time !!}</p>
                    </div>

                    <!-- Meeting Point -->
                    <div class="description-item">
                    <div class="header-container">
                        <span><i data-lucide="map-pin"></i> Meeting point</span>
                    </div>
                    <p>{!! $guiding->desc_meeting_point !!}</p>
                    </div>

                    <!-- Tour Highlights -->
                    <div class="description-item">
                    <div class="header-container">
                        <span><i data-lucide="sailboat"></i> Tour highlights</span>
                    </div>
                    <p>{!! $guiding->desc_tour_unique !!}</p>
                    </div>
                </div>
            </div>

            <div class="nav nav-tabs" id="guiding-tab" role="tablist">
                <button class="nav-link active" id="nav-include-tab" data-bs-toggle="tab" data-bs-target="#include" type="button" role="tab" aria-controls="nav-include" aria-selected="true">Inclusions</button>
                <button class="nav-link" id="nav-fishing-tab" data-bs-toggle="tab" data-bs-target="#fishing" type="button" role="tab" aria-controls="nav-fishing" aria-selected="false">Fishing Experience</button>
                <button class="nav-link" id="nav-costs-tab" data-bs-toggle="tab" data-bs-target="#costs" type="button" role="tab" aria-controls="nav-costs" aria-selected="false">Additional Extras</button>
                <button class="nav-link" id="nav-boat-tab" data-bs-toggle="tab" data-bs-target="#boat" type="button" role="tab" aria-controls="nav-boat" aria-selected="false">Boat Details</button>
                <button class="nav-link" id="nav-info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab" aria-controls="nav-info" aria-selected="false">Important Information</button>
            </div>

<div class="tab-content" id="nav-tabContent">

    <!-- What's Included Tab -->
    <div class="tab-pane fade show active" id="include" role="tabpanel" aria-labelledby="nav-include-tab">
        @if(!empty($guiding->inclusions))
            <div class="row">
                @foreach (json_decode($guiding->inclusions) as $index => $inclusion)
                    <div class="col-12 mb-2 text-start">
                    <i data-lucide="wrench"></i> {{$inclusion->value}}
                    </div>
                @endforeach
            </div>
        @else
            <p>No inclusions specified</p>
        @endif
    </div>

    <!-- Fishing Experience Tab -->
    <div class="tab-pane fade" id="fishing" role="tabpanel" aria-labelledby="nav-fishing-tab">
    <div class="row">
        
        @if(!empty($guiding->target_fish))
            <div class="tab-category mb-4 col-4">
                <strong>Target Fish</strong>
                <div class="row">
                    @foreach (json_decode($guiding->target_fish) as $index => $target_fish)
                        <div class="col-12 text-start">
                            {{$target_fish->value}}
                        </div>
                        @if(($index + 1) % 2 == 0)
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
            <div class="tab-category mb-4 col-4">
                <strong>Fishing Methods</strong>
                <div class="row">
                    @foreach (json_decode($guiding->fishing_methods) as $index => $fishing_method)
                        <div class="col-12 text-start">
                            {{$fishing_method->value}}
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
            <div class="tab-category mb-4 col-4">
                <strong>Water Types</strong>
                <div class="row">
                    @foreach (json_decode($guiding->water_types) as $index => $water_type)
                        <div class="col-12 text-start">
                            {{$water_type->value}}
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


    <!-- Additional Costs Tab -->
    <div class="tab-pane fade" id="costs" role="tabpanel" aria-labelledby="nav-costs-tab">
            @if(!empty($guiding->pricing_extra))
                @foreach (json_decode($guiding->pricing_extra) as $pricing_extras)
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <strong>{{$pricing_extras->name}}</strong> 
                                <span>{{$pricing_extras->price}}€ p.P</span>
                        </div>
                        </div>
                @endforeach
            @else
                <span>No extra prices specified</span>
            @endif
    </div>
    @php
        $boatInformation = json_decode($guiding->boat_information, true);
    @endphp
    <div class="tab-pane fade" id="boat" role="tabpanel" aria-labelledby="nav-boat-tab">
    <div class="row">
    <div class="col-md-12">
    @if(!empty($guiding->boat_information))
        <strong>Boat Information:</strong>
        <!-- Boat Information as a Table -->
        <table class="table table-striped table-bordered">
    <tbody>
        <tr>
            <th>Seats</th>
            <td colspan="1">{{ $boatInformation['seats'] ?? '' }}</td>
        </tr>
        <tr>
            <th>Length</th>
            <td>{{ $boatInformation['length'] ?? '' }}</td>
        </tr>
        <tr>
            <th>Width</th>
            <td>{{ $boatInformation['width'] ?? '' }}</td>
        </tr>
        <tr>
            <th>Year Built</th>
            <td>{{ $boatInformation['year_built'] ?? '' }}</td>
        </tr>
        <tr>
            <th>Engine Manufacturer</th>
            <td>{{ $boatInformation['engine_manufacturer'] ?? '' }}</td>
        </tr>
        <tr>
            <th>Engine Power (hp)</th>
            <td>{{ $boatInformation['engine_power'] ?? '' }}</td>
        </tr>
        <tr>
            <th>Max Speed</th>
            <td>{{ $boatInformation['max_speed'] ?? '' }}</td>
        </tr>
        <tr>
            <th>Manufacturer</th>
            <td>{{ $boatInformation['manufacturer'] ?? '' }}</td>
        </tr>
    </tbody>
</table>

    @endif
</div>


        <div class="col-md-6">
            @if(!empty($guiding->boat_extras))
                <strong>Boat Extras:</strong>
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
    @if(!empty($guiding->requirements))
        <div class="tab-category mb-4">
            <strong>Requirements</strong>
            <div class="row">
                @foreach (json_decode($guiding->requirements) as $reqIndex => $requirements)
                    <div class="col-12 text-start">
                        <strong>{{ ucfirst(str_replace('_', ' ', $reqIndex)) }}:</strong> {{ $requirements }}
                    </div>
                    @if(($loop->index + 1) % 2 == 0)
                        </div><div class="row">
                    @endif
                @endforeach
            </div>
        </div>
    @else
        <p class="mb-4">No requirements specified</p>
    @endif

    <!-- Other Information Section -->
    @if(!empty($guiding->other_information))
        <div class="tab-category mb-4">
            <strong>Other Information</strong>
            <div class="row">
                @foreach (json_decode($guiding->other_information) as $otherIndex => $other)
                    <div class="col-12 text-start">
                        <strong>{{ ucfirst(str_replace('_', ' ', $otherIndex)) }}:</strong> {{ $other }}
                    </div>
                    @if(($loop->index + 1) % 2 == 0)
                        </div><div class="row">
                    @endif
                @endforeach
            </div>
        </div>
    @else
        <p class="mb-4">No other information specified</p>
    @endif

    <!-- Recommended Preparation Section -->
    @if(!empty($guiding->recommendations))
        <div class="tab-category mb-4">
            <strong>Recommended Preparation</strong>
            <div class="row">
                @foreach (json_decode($guiding->recommendations) as $recIndex => $recommendations)
                    <div class="col-12 text-start">
                        <strong>{{ ucfirst(str_replace('_', ' ', $recIndex)) }}:</strong> {{ $recommendations }}
                    </div>
                    @if(($loop->index + 1) % 2 == 0)
                        </div><div class="row">
                    @endif
                @endforeach
            </div>
        </div>
    @else
        <p class="mb-4">No recommendations specified</p>
    @endif

    <!-- Essential Details Section -->
    <div class="row mb-4">
        <!-- <div class="col-md-6">
            @if(!empty($guiding->experience_level))
                <h6>Experience Level:</h6>
                <ul>
                    @foreach(json_decode($guiding->experience_level) as $value)
                        <li>{{ $value }}</li>
                    @endforeach
                </ul>
            @endif
        </div> -->

        <div class="col-md-6">
            @if(!empty($guiding->style_of_fishing))
                    <h6>Style of Fishing:</h6> 
                    <span class="">{{ $guiding->style_of_fishing }}</span>
            @endif
        </div>
        <div class="col-md-6">
            @if(!empty($guiding->tour_type))
                <div>
                    <h6>Tour Type:</h6> 
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
 <!-- Description Section -->
 <div class="mb-3">
                    <div id="lite-datepicker" wire:ignore></div>
            </div>
        </div>
    
        <!-- Right Column -->
        <div class="guidings-book">
            @if(!$agent->ismobile())
                @include('pages.guidings.content.bookguiding')
            @endif
        </div>
    </section>

    <!-- Map Section -->
    <div id="map" class="mb-3" style="height: 400px;">
        <!-- Google Map will be rendered here -->
    </div>

    <!-- Rating Summary -->
     <div class=guidings-rating>
        @if(round($average_rating) > 0)
            <div class="ratings-head mt-5 pt-2">
                <h4>Bewertungen</h4>
                <div class="ratings-score-ave">
                    <div class="ratings-card">
                        <span>{{$average_rating}}</span>
                            @for($i = 0; $i < (round($average_rating)); $i++)
                                <i data-lucide="star" size="32"></i> 
                            @endfor
                    </div>
                </div>
            </div>
            @foreach($guiding->user->received_ratings as $received_rating )
            <div class="ratings-item">
                    <div class="ratings-comment">
                        <div class="ratings-comment-top">
                            <div class="user-info">
                                <p class="user">{{$received_rating ->user->firstname }}</p>
                                <p class="date">{{ ($received_rating->created_at != null ) ? Carbon\Carbon::parse($received_rating->created_at)->format('F j, Y') : "-"}}</p>
                            </div>
                            <p>
                            {{floor($received_rating->rating)}}
                                @for($i = 0; $i < floor($received_rating->rating); $i++)
                                    <i data-lucide="star"></i>
                                @endfor
                            </p>
                        </div>
                        <div class="comment-content">
                            <p>{{$received_rating ->description }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
        @endif
     </div>

    <div class="my-5">
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
    
    <section class="tour-details-two mt-md-5 mt-4">
        <div class="container">
            <div class="tour-details-two__related-tours {{$agent->ismobile() ? 'text-center' : ''}}">
                <h3 class="tour-details-two__title">{{ translate('Similar Guidings') }}</h3>
                <div class="popular-tours__carousel owl-theme owl-carousel">
                    @foreach($same_guiding as $other_guiding)
                        <div class="popular-tours__single">
                            <a class="popular-tours__img" href="{{ route('guidings.show',[$other_guiding->id,$other_guiding->slug]) }}" title="Guide aufmachen">
                                <figure class="popular-tours__img__wrapper">
                                    @if($other_guiding->is_newguiding)
                                        @if($other_guiding->thumbnail_path)
                                            <img src="{{ asset(!$other_guiding->is_newguiding ? "assets/guides/".$other_guiding->thumbnail_path : $other_guiding->thumbnail_path) }}" alt="{{ $other_guiding->title }}"/>
                                        @endif
                                    @else
                                        @if(isset(app('guiding')->getImagesUrl($other_guiding)['image_0']))
                                            <img src="{{ app('guiding')->getImagesUrl($other_guiding)['image_0'] }}" alt="{{ $other_guiding->title }}"/>
                                        @endif
                                    @endif
                                    <div class="popular-tours__icon">
                                        <a href="{{ route('wishlist.add-or-remove', $other_guiding->id) }}">
                                            <i class="fa fa-heart {{ (auth()->check() ? (auth()->user()->isWishItem($other_guiding->id) ? 'text-danger' : '') : '') }}"></i>
                                        </a>
                                    </div>
                                </figure>
                            </a>

                            <div class="popular-tours__content">
                                <h3 class="popular-tours__title"><a href="{{ route('guidings.show', [$other_guiding->id,$other_guiding->slug]) }}">{{  $other_guiding->title ?  translate( $other_guiding->title) :  $other_guiding->title }}</a>
                                </h3>
                                <span>{{ $other_guiding->location ? translate($other_guiding->location) : $other_guiding->location }}</span>
                                <p class="popular-tours__rate">
                                    <span>@lang('message.from') {{ two($other_guiding->price) }}€</span>
                                </p>
                                <span><i class="far fa-hourglass"></i>{{ translate('Duration') }}: {{ two($other_guiding->duration) }} {{ translate('Hours') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    
    <section class="tour-details-two mt-md-5 mt-4">
        <div class="container">

            <div class="tour-details-two__related-tours {{$agent->ismobile() ? 'text-center' : ''}}">
                <h3 class="tour-details-two__title">{{ translate('Matching Guidings') }}</h3>
                <div class="popular-tours__carousel owl-theme owl-carousel">
                    @foreach($other_guidings as $other_guiding)
                
                        <div class="popular-tours__single">
                            <a class="popular-tours__img" href="{{ route('guidings.show',[$other_guiding->id,$other_guiding->slug]) }}" title="Guide aufmachen">
                                <figure class="popular-tours__img__wrapper">
                                    @if($other_guiding->is_newguiding)
                                        @if($other_guiding->thumbnail_path)
                                            <img src="{{ asset(!$other_guiding->is_newguiding ? "assets/guides/".$other_guiding->thumbnail_path : $other_guiding->thumbnail_path) }}" alt="{{ $other_guiding->title }}"/>
                                        @endif
                                    @else
                                        @if(isset(app('guiding')->getImagesUrl($other_guiding)['image_0']))
                                            <img src="{{ app('guiding')->getImagesUrl($other_guiding)['image_0'] }}" alt="{{ $other_guiding->title }}"/>
                                        @endif
                                    @endif
                                    <div class="popular-tours__icon">
                                        <a href="{{ route('wishlist.add-or-remove', $other_guiding->id) }}">
                                            <i class="fa fa-heart {{ (auth()->check() ? (auth()->user()->isWishItem($other_guiding->id) ? 'text-danger' : '') : '') }}"></i>
                                        </a>
                                    </div>
                                </figure>
                            </a>

                            <div class="popular-tours__content">
                                <h3 class="popular-tours__title"><a href="{{ route('guidings.show', [$other_guiding->id,$other_guiding->slug]) }}">{{  $other_guiding->title ?  translate( $other_guiding->title) :  $other_guiding->title }}</a>
                                </h3>
                                <span>{{ $other_guiding->location ? translate($other_guiding->location) : $other_guiding->location }}</span>
                                <p class="popular-tours__rate">
                                    <span>@lang('message.from') {{ two($other_guiding->price) }}€</span>
                                </p>
                                <span><i class="far fa-hourglass"></i>{{ translate('Dauer') }}: {{ two($other_guiding->duration) }} {{ translate('Stunden') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('js_after')
<script async src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_API_KEY') }}&callback=initMap"></script>

<script>
//    const masonryModal = document.getElementById('galleryModal');
//     masonryModal.addEventListener('shown.bs.modal', function () {
//       // Initialize Masonry when modal opens
//       var msnry = new Masonry('#masonry-grid', {
//         itemSelector: '.col-6',
//         columnWidth: '.col-6',
//         percentPosition: true
//       });
//     });
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
        const picker = new Litepicker({
            element: document.getElementById('lite-datepicker'),
            inlineMode: true,
            singleDate: true,
            numberOfColumns: initCheckNumberOfColumns(),
            numberOfMonths: initCheckNumberOfColumns(),
            minDate: new Date(new Date().getTime() + 24 * 60 * 60 * 1000),
            lockDays: [
                @foreach($guiding->user->blocked_events as $blocked)
                    @if($blocked->guiding_id == $guiding->id)
                    ["{{ substr($blocked->from,0,-9) }}", "{{ substr($blocked->due,0,-9) }}"],
                    @endif
                    ["{{ substr($blocked->from,0,-9) }}", "{{ substr($blocked->due,0,-9) }}"],
                @endforeach
                new Date(),
            ],
            lang: '{{app()->getLocale()}}',
            setup: (picker) => {
                // Change picker columns on resize
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
                    <img src="${newGuiding.is_newguiding ? newGuiding.thumbnail_path : 'assets/guides/' + newGuiding.thumbnail_path}" class="card-img-top" alt="${newGuiding.title}">
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
