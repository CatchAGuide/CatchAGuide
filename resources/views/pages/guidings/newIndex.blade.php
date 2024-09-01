@extends('layouts.app')

@section('title', substr($title, 0, -3))
@section('description', $title)
@section('css_after')

@endsection

@section('content')
 <div class="container"> <!-- Search Bar -->
    <div class="row mb-4">
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

    <div class="row">
        <!-- Left Column -->
        <div class="col-md-8">
            <!-- Title, Rating, and Location -->
            <div class="mb-3">
                <h1>Ebro Dream Fishing, S.L.</h1>
                <p class="mb-1">
                    <span class="text-warning">★</span> 3.9/5 (4 reviews)
                </p>
                <p>
                    <a href="#" class="text-decoration-none text-muted">
                        <i class="bi bi-geo-alt"></i> Carrer la Palla, Riba-roja d'Ebre, CT 43790, Spain – 
                        <span class="text-primary">Show map</span>
                    </a>
                </p>
            </div>

            <!-- Image Gallery -->
            <div class="row mb-3">
                <div class="col-7">
                    <img src="https://via.placeholder.com/640x480" class="img-fluid" alt="Main Image">
                </div>
                <div class="col-5">
                    <div class="row g-2">
                        @for ($i = 0; $i < 6; $i++)
                            <div class="col-6">
                                <img src="https://via.placeholder.com/160x160" class="img-fluid" alt="Gallery Image {{ $i + 1 }}">
                            </div>
                        @endfor
                    </div>
                </div>
            </div>

            <!-- Important Information -->
            <div class="alert alert-warning">
                <strong>Important information:</strong> Boat/shore; Duration; Number of guests
            </div>

            <!-- Description Section -->
            <div class="card mb-3">
                <div class="card-header">Description</div>
                <div class="card-body">
                    <p>Here goes the description of the service...</p>
                </div>
            </div>

            <!-- Included Items -->
            <div class="card mb-3">
                <div class="card-header">Included</div>
                <div class="card-body">
                    <ul>
                        <li>Items include: Boat, tackle, drinks, etc.</li>
                    </ul>
                </div>
            </div>

            <!-- Fish Section -->
            <div class="card mb-3">
                <div class="card-header">Fish</div>
                <div class="card-body">
                    <ul>
                        <li>Types of fish: Catfish, carp, etc.</li>
                    </ul>
                </div>
            </div>

            <!-- Methods Section -->
            <div class="card mb-3">
                <div class="card-header">Methods</div>
                <div class="card-body">
                    <ul>
                        <li>Fishing methods: Live bait, casting, etc.</li>
                    </ul>
                </div>
            </div>

            <!-- Water Types Section -->
            <div class="card mb-3">
                <div class="card-header">Water Types</div>
                <div class="card-body">
                    <ul>
                        <li>Water types: River, lake, etc.</li>
                    </ul>
                </div>
            </div>

            <!-- Extra Prices Section -->
            <div class="card mb-3">
                <div class="card-header">Extra Prices</div>
                <div class="card-body">
                    <ul>
                        <li>Additional prices: Tackle, drinks, etc.</li>
                    </ul>
                </div>
            </div>

            <!-- Boat Section -->
            <div class="card mb-3">
                <div class="card-header">Boat</div>
                <div class="card-body">
                    <ul>
                        <li>Boat type: Cabin boat, open boat, etc.</li>
                    </ul>
                </div>
            </div>

            <!-- Map Section -->
            <div id="map" class="mb-3" style="height: 400px;">
                <!-- Google Map will be rendered here -->
            </div>

            <!-- Rating Summary -->
            <div class="card mb-3">
                <div class="card-header">Rating Summary</div>
                <div class="card-body">
                    <!-- Ratings and Reviews Summary here -->
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

            <!-- Other Information -->
            <div class="card mb-3">
                <div class="card-header">Other Information</div>
                <div class="card-body">
                    <ul>
                        <li>Other relevant details...</li>
                    </ul>
                </div>
            </div>

            <!-- Recommended Preparation -->
            <div class="card mb-3">
                <div class="card-header">Recommended Preparation</div>
                <div class="card-body">
                    <ul>
                        <li>Preparation tips...</li>
                    </ul>
                </div>
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
