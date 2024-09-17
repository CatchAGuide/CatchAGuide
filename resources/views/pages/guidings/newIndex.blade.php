@extends('layouts.app')

@section('title', substr($guiding->title, 0, -3))
@section('description', $guiding->title)
@section('css_after')

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
                <strong>Important information:</strong> {{$guiding->is_boat ? 'Boat :'. $guiding->boat_type : 'Shore'}}; Duration: {{$guiding->duration}} {{ $guiding->duration_type == 'multi_day' ? 'day/s' : 'hour/s' }}; Number of guests: {{$guiding->max_guests}}
            </div>

            <!-- Description Section -->
            <div class="card mb-3">
                <div class="card-header">Description</div>
                <div class="card-body">
                    <p>{!! $guiding->description !!}</p>
                </div>
            </div>

            <!-- Included Items -->
            <div class="card mb-3">
                <div class="card-header">Included</div>
                <div class="card-body">
                    <ul>
                        @if(!empty($guiding->inclusions))
                            @foreach (json_decode($guiding->inclusions) as $inclusion)
                                <li>{{$inclusion->value}}</li>
                            @endforeach
                        @else
                            <li>No inclusions specified</li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Fish Section -->
            <div class="card mb-3">
                <div class="card-header">Fish</div>
                <div class="card-body">
                    <ul>
                        @if(!empty($guiding->target_fish))
                            @foreach (json_decode($guiding->target_fish) as $target_fish)
                                <li>{{$target_fish->value}}</li>
                            @endforeach
                        @else
                            <li>No fish specified</li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Methods Section -->
            <div class="card mb-3">
                <div class="card-header">Methods</div>
                <div class="card-body">
                    <ul>
                        @if(!empty($guiding->fishing_methods))
                            @foreach (json_decode($guiding->fishing_methods) as $fishing_methods)
                                <li>{{$fishing_methods->value}}</li>
                            @endforeach
                        @else
                            <li>No methods specified</li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Water Types Section -->
            <div class="card mb-3">
                <div class="card-header">Water Types</div>
                <div class="card-body">
                    <ul>
                        @if(!empty($guiding->water_types))
                            @foreach (json_decode($guiding->water_types) as $water_types)
                                <li>{{$water_types->value}}</li>
                            @endforeach
                        @else
                            <li>No water types specified</li>
                        @endif
                    </ul>
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
                    <ul>
                        @if(!empty($guiding->boat_extras))
                            @foreach (json_decode($guiding->boat_extras) as $boat_extras)
                                <li>{{$boat_extras->value}}</li>
                            @endforeach
                        @else
                            <li>No boat extras specified</li>
                        @endif
                    </ul>
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
            <!-- Guiding Booking -->
            <div class="card mb-3">
                <div class="card-header">Guiding Booking</div>
                <div class="card-body">
                    <!-- Booking details here -->
                </div>
            </div>

            <!-- Calendar -->
            <div class="card mb-3">
                <div class="card-header">Calendar</div>
                <div class="card-body">
                    <!-- Calendar or Date Picker Component here -->
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

        
        <div class="col-12 col-sm-12 col-md-2 col-lg-3 col-xl-2 col-xxl-2  mt-3">
            <div class="text-center">
                <h5 class="mr-1 color-primary fw-bold text-center">@lang('message.from') {{$guiding->price}}€</h4>
            </div>
            <div class="d-flex flex-column mt-4">
                <a class="btn theme-primary btn-theme-new btn-sm" href="{{ route('guidings.show',[$guiding->id,$guiding->slug]) }}">Details</a>
                <a class="btn btn-sm mt-2   {{ (auth()->check() ? (auth()->user()->isWishItem($guiding->id) ? 'btn-danger' : 'btn-outline-theme ') : 'btn-outline-theme') }}" href="{{ route('wishlist.add-or-remove', $guiding->id) }}">
                    {{ (auth()->check() ? (auth()->user()->isWishItem($guiding->id) ? 'Added to Favorites' : 'Add to Favorites') : 'Add to Favorites') }}
                </a>
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
</script>
@endsection
