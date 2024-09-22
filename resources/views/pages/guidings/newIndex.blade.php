@extends('layouts.app')

@section('title', substr($guiding->title, 0, -3))
@section('description', $guiding->title)

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
    <div class="row mb-4">
        <div class="col-12">
            <div class="col-12">
                <form class="d-flex">
                    <select class="form-select me-2">
                        <option>Spain</option>
                        <!-- Other options... -->
                    </select>
                    <input type="date" class="form-control me-2" value="2024-07-10">
                    <select class="form-select me-2">
                        <option>1 day</option>
                        <!-- Other options... -->
                    </select>
                    <select class="form-select me-2">
                        <option>2 adults · 0 children</option>
                        <!-- Other options... -->
                    </select>
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="mb-3">
            <h1>{{$guiding->title}}</h1>
            <p class="mb-1">
                <span class="text-warning">★</span> 3.9/5 (4 reviews)
            </p>
            <p>
                <a href="#" class="text-decoration-none text-muted">
                    <i class="bi bi-geo-alt"></i> {{$guiding->location}} - 
                    <span class="text-primary">Show map</span>
                </a>
            </p>
        </div>

        <!-- Image Gallery -->
        <div class="row mb-3">
            <div class="col-7">
                <img src="{{asset($guiding->thumbnail_path)}}" class="img-fluid" alt="Main Image">
            </div>
            <div class="col-5">
                <div class="row g-2">
                    @foreach (json_decode($guiding->galery_images) as $index => $image)
                        <div class="col-6">
                            <img src="{{asset($image)}}" class="img-fluid" alt="Gallery Image {{ $index + 1 }}">
                        </div>
                    @endforeach
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
        </div>

        <!-- Right Column -->
        <div class="col-md-4">
            <div class="sticky-top" style="top: 20px;">
                <!-- Guiding Booking -->
                <div class="card mb-3">
                    <div class="card-header">Guiding Buchen</div>
                    <div class="card-body">
                        <form>
                            <div class="mb-3">
                                <select class="form-select" aria-label="Personenanzahl">
                                    <option selected>Bitte wähle die Personenanzahl</option>
                                    @if($guiding->price_type == 'per_person')
                                        @foreach(json_decode($guiding->prices) as $price)
                                            <option value="{{ $price->person }}">{{ $price->person }} {{ $price->person == 1 ? 'Person' : 'Personen' }}</option>
                                        @endforeach
                                    @else
                                        <option value="1">1 Person</option>
                                        <option value="2">2 Personen</option>
                                        <option value="3">3 Personen</option>
                                        <option value="4">4 Personen</option>
                                    @endif
                                </select>
                            </div>
                            <button type="submit" class="btn btn-danger w-100">VERFÜGBARKEIT PRÜFEN & BUCHEN</button>
                        </form>
                        <div class="mt-3">
                            <h5>Preis</h5>
                            @if($guiding->price_type == 'per_person')
                                <ul class="list-unstyled">
                                    @foreach(json_decode($guiding->prices) as $price)
                                        <li class="d-flex justify-content-between align-items-center">
                                            <span>{{ $price->person }} {{ $price->person == 1 ? 'Person' : 'Personen' }}</span>
                                            <span class="text-right">
                                                <span class="text-danger">{{ $price->person > 1 ? round($price->amount / $price->person) : $price->amount }}€</span>
                                                @if($price->person > 1)
                                                    <span class="text-black" style="font-size: 0.8em;"> p.P</span>
                                                @endif
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <ul class="list-unstyled">
                                    @foreach(json_decode($guiding->prices) as $price)
                                        <li class="d-flex justify-content-between align-items-center">
                                            <span>{{ $price->person }} {{ $price->person == 1 ? 'Person' : 'Personen' }}</span>
                                            <span class="text-right">
                                                <span class="text-danger">{{ round($price->amount / $price->person) }}€</span>
                                                @if($price->person > 1)
                                                    <span class="text-black" style="font-size: 0.8em;"> p.P</span>
                                                @endif
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
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

        <div class="tour-details-two__related-tours {{$agent->ismobile() ? 'text-center' : ''}}">
            <h3 class="tour-details-two__title">{{ translate('Ähnliche Guidings') }}</h3>
            <div class="popular-tours__carousel owl-theme owl-carousel">
                @foreach($other_guidings as $other_guiding)
            
                    <div class="popular-tours__single">
                        <a class="popular-tours__img" href="{{ route('guidings.show',[$other_guiding->id,$other_guiding->slug]) }}" title="Guide aufmachen">
                            <figure class="popular-tours__img__wrapper">
                                @if(isset(app('guiding')->getImagesUrl($other_guiding)['image_0']))
                                    <img src="{{app('guiding')->getImagesUrl($other_guiding)['image_0']}}" alt="{{$other_guiding->title}}"/>
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

    <!-- More Charters Section -->
    <div class="card mt-5">
        <div class="card-header">More charters like this</div>
        <div class="card-body">
            <div class="row">
                <div class="col-sm-4 mb-3">
                    <!-- Similar Charter 1 -->
                </div>
                <div class="col-sm-4 mb-3">
                    <!-- Similar Charter 2 -->
                </div>
                <div class="col-sm-4 mb-3">
                    <!-- Similar Charter 3 -->
                </div>
            </div>
        </div>
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
                    ["{{ substr($blocked->from,0,-9) }}", "{{ substr($blocked->from,0,-9) }}"],
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
</script>
@endsection
