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
            background-color: #262e35;
            color: white;
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
 <div class="container">
    <div class="row">
        <div class="mb-3">
            {{-- <h1>Fishing trip in {{$guiding->location}} - {{$guiding->title}}</h1> --}}
            <h1>
                <a href="#" class="fs-5 text-decoration-none text-muted">
                    <i class="bi bi-geo-alt"></i> Fishing trip in <strong>{{$guiding->location}}</strong>
                </a>
                <br>
                {{$guiding->title}}
            </h1>
            <span class="text-primary">Show on map</span>
            <p class="mb-1">
                <span class="text-warning">★</span> 3.9/5 (4 reviews)
            </p>
            {{-- <p>
                <a href="#" class="text-decoration-none text-muted">
                    <i class="bi bi-geo-alt"></i> {{$guiding->location}} - 
                    <span class="text-primary">Show map</span>
                </a>
            </p> --}}
        </div>
        <!-- Image Gallery -->
        <div class="row mb-3">
            <div class="col-7">
                <img src="{{asset($guiding->thumbnail_path)}}" class="img-fluid" alt="Main Image">
            </div>
            <div class="col-5">
                <div class="row g-2">
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
                            <div class="col-6 position-relative">
                                <img src="{{asset($image)}}" class="img-fluid" alt="Gallery Image {{ $index + 1 }}" data-bs-toggle="modal" data-bs-target="#galleryModal" data-image="{{ asset($image) }}">
                            </div>
                        @elseif ($index == 4)
                            <div class="col-6 position-relative">
                                <img src="{{asset($image)}}" class="img-fluid" alt="Gallery Image {{ $index + 1 }} (and {{ $hiddenCount }} more)" data-bs-toggle="modal" data-bs-target="#galleryModal" data-image="{{ asset($image) }}">
                                <span class="text-muted position-absolute" style="top: 50%; left: 50%; transform: translate(-50%, -50%);">+{{ $hiddenCount }} more</span>
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
                        <div class="row">
                            @foreach ($galleryImages as $image)
                                <div class="col-12 mb-3">
                                    <img src="{{ asset($image) }}" class="img-fluid" alt="Gallery Image" />
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Left Column -->
        <div class="col-md-8">
            <!-- Title, Rating, and Location -->

            <!-- Important Information -->
            <div class="alert alert-warning">
                <div class="row">
                    <div class="col-md-4 d-flex align-items-center">
                        <i class="fas fa-ship fa-2x me-3"></i>
                        <p class="mb-0">{{$guiding->is_boat ? $guiding->boat_type : 'Shore'}}</p>
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <i class="far fa-clock fa-2x me-3"></i>
                        <p class="mb-0">{{ ucfirst(str_replace('_', ' ', $guiding->duration_type)) }} : {{$guiding->duration}} {{ $guiding->duration_type == 'multi_day' ? 'day/s' : 'hour/s' }}</p>
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <i class="fas fa-users fa-2x me-3"></i>
                        <p class="mb-0">Number of guests: {{$guiding->max_guests}}</p>
                    </div>
                </div>
            </div>

            <!-- Description Section -->
            <div class="card mb-3">
                <div class="card-header">Description</div>
                <div class="card-body">
                    <p>{!! $guiding->description !!}</p>
                </div>
            </div>

            <!-- Description Section -->
            <div class="card mb-3">
                <div class="card-header">Calendar</div>
                <div class="card-body">
                    <div id="lite-datepicker" wire:ignore></div>
                </div>
            </div>

            <!-- Included Items -->
            <div class="card mb-3">
                <div class="card-header">Included</div>
                <div class="card-body">
                    @if(!empty($guiding->inclusions))
                        <div class="row">
                            @foreach (json_decode($guiding->inclusions) as $index => $inclusion)
                                <div class="col-6 text-start">
                                    <i class="fas fa-check-circle mr-2"></i> {{$inclusion->value}}
                                </div>
                                @if(($index + 1) % 2 == 0)
                                    </div><div class="row">
                                @endif
                            @endforeach
                        </div>
                    @else
                        <p>No inclusions specified</p>
                    @endif
                </div>
            </div>

            <!-- Fish Section -->
            <div class="card mb-3">
                <div class="card-header">Fish</div>
                <div class="card-body">
                    @if(!empty($guiding->target_fish))
                        <div class="row">
                            @foreach (json_decode($guiding->target_fish) as $index => $target_fish)
                                <div class="col-6 text-start">
                                    <i class="fas fa-check-circle mr-2"></i> {{$target_fish->value}}
                                </div>
                                @if(($index + 1) % 2 == 0)
                                    </div><div class="row">
                                @endif
                            @endforeach
                        </div>
                    @else
                        <p>No fish specified</p>
                    @endif
                </div>
            </div>

            <!-- Methods Section -->
            <div class="card mb-3">
                <div class="card-header">Methods</div>
                <div class="card-body">
                    @if(!empty($guiding->fishing_methods))
                        <div class="row">
                            @foreach (json_decode($guiding->fishing_methods) as $index => $fishing_method)
                                <div class="col-6 text-start">
                                    <i class="fas fa-check-circle mr-2"></i> {{$fishing_method->value}}
                                </div>
                                @if(($index + 1) % 2 == 0)
                                    </div><div class="row">
                                @endif
                            @endforeach
                        </div>
                    @else
                        <p>No methods specified</p>
                    @endif
                </div>
            </div>

            <!-- Water Types Section -->
            <div class="card mb-3">
                <div class="card-header">Water Types</div>
                <div class="card-body">
                    @if(!empty($guiding->water_types))
                        <div class="row">
                            @foreach (json_decode($guiding->water_types) as $index => $water_type)
                                <div class="col-6 text-start">
                                    <i class="fas fa-check-circle mr-2"></i> {{$water_type->value}}
                                </div>
                                @if(($index + 1) % 2 == 0)
                                    </div><div class="row">
                                @endif
                            @endforeach
                        </div>
                    @else
                        <p>No water types specified</p>
                    @endif
                </div>
            </div>

            <!-- Extra Prices Section -->
            <div class="card mb-3">
                <div class="card-header">Extra Prices</div>
                <div class="card-body">
                    <ul>
                        @if(!empty($guiding->pricing_extra))
                            @foreach (json_decode($guiding->pricing_extra) as $pricing_extras)
                                <li>
                                    <div class="row">
                                    <div class="col-md-6">{{$pricing_extras->name}}</div>
                                    <div class="col-md-6">{{$pricing_extras->price}}</div>
                                    </div>
                                </li>
                            @endforeach
                        @else
                            <li>No extra prices specified</li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Boat Section -->
            <div class="card mb-3">
                <div class="card-header">Boat</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            @if(!empty($guiding->boat_information))
                                <h6>Boat Information:</h6>
                                <ul>
                                    @foreach(json_decode($guiding->boat_information) as $key => $value)
                                        <li><b>{{ ucfirst(str_replace('_', ' ', $key)) }}:</b> {{$value }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if(!empty($guiding->boat_extras))
                                <h6>Boat Extras:</h6>
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
            </div>

            <!-- Requirements -->
            <div class="card mb-3">
                <div class="card-header">Requirements</div>
                <div class="card-body">
                    <ul>
                        @if(!empty($guiding->requirements))
                            @foreach (json_decode($guiding->requirements) as $reqIndex => $requirements)
                                <li>
                                    <div class="row">
                                    <div class="col-md-6">{{$reqIndex}}</div>
                                    <div class="col-md-6">{{$requirements}}</div>
                                </div>
                            </li>
                            @endforeach
                        @else
                            <li>No requirements specified</li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Other Information -->
            <div class="card mb-3">
                <div class="card-header">Other Information</div>
                <div class="card-body">
                    <ul>
                        @if(!empty($guiding->other_information))
                            @foreach (json_decode($guiding->other_information) as $otherIndex => $other)
                                <li>
                                    <div class="row">
                                    <div class="col-md-6">{{$otherIndex}}</div>
                                    <div class="col-md-6">{{$other}}</div>
                                </div>
                            </li>
                            @endforeach
                        @else
                            <li>No other information specified</li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Recommended Preparation -->
            <div class="card mb-3">
                <div class="card-header">Recommended Preparation</div>
                <div class="card-body">
                    <ul>
                        @if(!empty($guiding->recommendations))
                            @foreach (json_decode($guiding->recommendations) as $recIndex => $recommendations)
                                <li>
                                    <div class="row">
                                    <div class="col-md-6">{{$recIndex}}</div>
                                    <div class="col-md-6">{{$recommendations}}</div>
                                </div>
                                </li>
                            @endforeach
                        @else
                            <li>No recommendations specified</li>
                        @endif
                    </ul>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">Essential details</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            @if(!empty($guiding->experience_level))
                                <h6>Experience Level:</h6>
                                <ul>
                                    @foreach(json_decode($guiding->experience_level) as $value)
                                        <li>{{$value}}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if(!empty($guiding->style_of_fishing))
                                <div class="row">
                                    <div class="col">
                                        <h6 style="display: inline;">Style of Fishing:</h6> <span class="badge bg-primary">{{ $guiding->style_of_fishing }}</span>
                                    </div>
                                </div>
                            @endif
                            @if(!empty($guiding->tour_type))
                                <div class="row">
                                    <div class="col">
                                        <h6 style="display: inline;">Tour Type:</h6> <span class="badge bg-primary">{{ $guiding->tour_type }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                        </div>
                    </div>
                    @if(empty($guiding->experience_level) && empty($guiding->tour_type) && empty($guiding->style_of_fishing))
                        <p>No information specified</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-md-4">
            @if(!$agent->ismobile())
                @include('pages.guidings.content.bookguiding')
            @endif
        </div>

        <!-- Map Section -->
        <div id="map" class="mb-3" style="height: 400px;">
            <!-- Google Map will be rendered here -->
        </div>

        <!-- Rating Summary -->
        <div class="mt-3">
            @if($guiding->user->profil_image)
            <img class="center-block rounded-circle"
            src="{{asset('images/'. $guiding->user->profil_image)}}" alt="" width="20"
            height="20">
            @else
                <img class="center-block rounded-circle"
                    src="{{asset('images/placeholder_guide.jpg')}}" alt="" width="20"
                    height="20">
            @endif
            <span class="color-primary" style="font-size:1rem">{{$guiding->user->firstname}}</span>
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
</div>
@endsection

@section('js_after')
<script async src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_API_KEY') }}&callback=initMap"></script>

<script>
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
