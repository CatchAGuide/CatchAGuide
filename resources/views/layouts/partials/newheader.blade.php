<header class="header" style="background-image: url('{{ asset('assets/images/allguidings.jpg') }}'); background-size: cover; background-position: center;">
    <div class="overlay"></div>
    <nav class="navbar-custom container">
        <div class="logo">
            <a href="https://sg.catchaguide.de"><img src="https://sg.catchaguide.de/assets/images/logo_mobil.jpg" alt="Logo"></a>
        </div>
        <div class="nav-links">
            <a href="#" class="button">List Your Boat</a>
            <a href="{{route('login')}}">Log in</a>
            <a href="{{route('login')}}">Sign up</a>
        </div>
    </nav>
    <div class="header-content container">
        <h1 class="h2">{{ucwords(isset($place) ? translate('Alle Guidings bei ') . $place : translate('Alle Guidings') )}}</h1>
        <p>Discover top-rated fishing charters</p>
    </div>
    <form class="search-form row gx-2">
        
        <form action="{{route('guidings.index')}}" method="get">
            <div class="col-md-3">
                <input type="text" id="searchLocation" name="location" class="form-control" placeholder="@lang('homepage.searchbar-destination')">
                <input type="hidden" id="LocationLat" name="LocationLat"/>
                <input type="hidden" id="LocationLng" name="LocationLng"/>
            </div>
            <div class="col-md-3">
                <input type="date" class="form-control" placeholder="Select date">
            </div>
            <div class="col-md-3">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle w-100" type="button" id="guestDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        2 adults • 0 children
                    </button>
                    <div class="dropdown-menu p-3">
                        <div class="counter-box">
                            <span>Adults</span>
                            <button type="button" class="btn btn-light" onclick="changeGuestCount('adults', -1)">-</button>
                            <input type="text" id="adults" value="2" readonly>
                            <button type="button" class="btn btn-light" onclick="changeGuestCount('adults', 1)">+</button>
                        </div>
                        <div class="counter-box">
                            <span>Children</span>
                            <button type="button" class="btn btn-light" onclick="changeGuestCount('children', -1)">-</button>
                            <input type="text" id="children" value="0" readonly>
                            <button type="button" class="btn btn-light" onclick="changeGuestCount('children', 1)">+</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-warning w-100">Check availability</button>
            </div>
        </form>
    </form>
</header>

@section('js_after')
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBiGuDOg_5yhHeoRz-7bIkc9T1egi1fA7Q&libraries=places,geocoder"></script>
<script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>

<script>
    function initialize() {
        var input = document.getElementById('searchPlace');
        var autocomplete = new google.maps.places.Autocomplete(input);
        google.maps.event.addListener(autocomplete, 'place_changed', function () {
            var place = autocomplete.getPlace();
            document.getElementById('LocationLat').value = place.geometry.location.lat();
            document.getElementById('LocationLng').value = place.geometry.location.lng();
        });
    }

    window.addEventListener('load', initialize);
</script>
@endsection