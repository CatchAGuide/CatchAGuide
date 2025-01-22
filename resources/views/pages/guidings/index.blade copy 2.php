@extends('layouts.app')

@section('title', __('message.guidings_meta_title'))
@section('description',__('message.guidings_meta_description'))
@section('css_after')
{{-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> --}}

    <style>
        .fixedmap {
            position: fixed;
            right: 0px;
            bottom: 10%;
            height: 70%;
        }
        a:hover {
            color: black;
        }
        .page-header-bg-overly {
            background-color: rgba(0,0,0,0);
        }
        .pager-header-bg {
            filter: none !important;
        }

        .carousel .carousel-control-next, .carousel .carousel-control-prev {
            top: 50%;
            transform: translateY(-50%);
        }

        .carousel.slide {
            max-height: 265px;
        }

        .carousel .carousel-control-next {
            right: 0;
        }

        .carousel .carousel-control-prev {
            left: 0;
        }

        .carousel-item {
            min-height: 50px;
        }
        .carousel .carousel-control-next, .carousel .carousel-control-prev {
            padding: 3px;
            width: 24px;
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
        .form-custom-input{
        /* border: solid #e8604c 1px; */
        border: 1px solid #d4d5d6;
        border-radius: 5px;
        padding: 8px 10px;
        width:100%;
        }
        .form-control:focus{
            /* border: solid #e8604c 1px !important; */
           box-shadow: none;
        }
        .form-custom-input:focus-visible{
            /* border: solid #e8604c 1px !important; */
            border:0;
            outline:solid #e8604c 1px !important;
        }
        li.select2-selection__choice{
            background-color: #E8604C !important;
            color: #fff !important;
            border: 0 !important;
            font-size:14px;
            vertical-align: middle !important;
            margin-top:0 !important;
         
        }
        button.select2-selection__choice__remove{
            border: 0 !important;
            color: #fff !important;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover, .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:focus{
            background:none;
        }
        span.select2-selection.select2-selection--multiple{
        border: 1px solid #d4d5d6;
        border-radius: 5px;
        padding: 7px 10px;
        }
        .select2-selection--multiple:before {
        content: "";
        position: absolute;
        right: 7px;
        top: 42%;
        border-top: 5px solid #888;
        border-left: 4px solid transparent;
        border-right: 4px solid transparent;
        }

        #toggleFilterBtn{
            display:none;
        }
        .sort-row .form-select{
            width: auto;
        }

        @media only screen and (max-width: 600px) {
            #toggleFilterBtn{
                display:block;
            }
            #filter-view{
                display:none;
            }
            
        }

        #filterbyview input[type="radio"] {
            display: none;
        }

        #filterbyview .button input[type="radio"]:checked + label {
        background: #20b8be;
        border-radius: 4px;
        }

        #filterbyview .button label {
        cursor: pointer;
        z-index: 90;
        line-height: 1.8em;
        }


    
    </style>

@endsection

@section('content')
    <section class="page-header">
        <div class="page-header__top">
            <div class="page-header-bg"
                 style="background-image: url({{asset('assets/images/allguidings.jpg')}})">
            </div>
            <div class="page-header-bg-overly"></div>
            <div class="container">
                <div class="page-header__top-inner">
                    <h2>{{ucwords(isset($place) ? translate('Alle Guidings bei ') . $place : translate('Alle Guidings') )}}</h2>
                </div>
            </div>
        </div>
        <div class="page-header__bottom">
            <div class="container">
                <div class="page-header__bottom-inner">
                    <ul class="thm-breadcrumb list-unstyled">
                        <li><a href="{{ route('welcome') }}">@lang('message.home')</a></li>
                        <li><span>&#183;</span></li>
                        <li class="active">
                            {{ucwords( isset($place) ? translate('Alle Guidings bei ') . $place : translate('Alle Guidings'))}}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!--Tours List Start-->
    <section class="tours-list" style="padding-top: 20px;">

        <div class="container">
            <div class="row">
                <div  id="filterResults" class="col-md-12 p-0">
                    <form id="filterContainer" action="{{route('guidings.index')}}" method="get">
                        <div  id="filter-view" class="row rounded m-0">
                            <div class="col-md-4">
                                <div class="form-group my-1">
                                    <label for="place">@lang('message.location')</label>
                                  <input  id="searchPlace" name="place" type="text" value="{{ request()->get('place') ? request()->get('place') : null }}" class="form-control form-custom-input" placeholder="@lang('message.enter-location')"  autocomplete="on">
                                  <input type="hidden" id="placeLat" value="{{ request()->get('placeLat') ? request()->get('placeLat') : null }}" name="placeLat"/>
                                  <input type="hidden" id="placeLng" value="{{ request()->get('placeLng') ? request()->get('placeLng') : null }}" name="placeLng"/>
                                </div>
                            </div>
                                    <div class="col-md-4">
                                        <div class="form-group my-1">
                                          <label for="radius">Radius</label>
                                          <select id="radius" class="form-control form-custom-input form-select" name="radius">
                                              <option value="" disabled selected>@lang('message.choose')...</option>
                                              <option value="50" {{ request()->get('radius') ? request()->get('radius') == 50 ? 'selected' : null : null }}>50 miles</option>
                                              <option value="100" {{ request()->get('radius') ? request()->get('radius') == 100 ? 'selected' : null : null }}>100 miles</option>
                                              <option value="150" {{ request()->get('radius') ? request()->get('radius') == 150 ? 'selected' : null : null }}>150 miles</option>
                                              <option value="250" {{ request()->get('radius') ? request()->get('radius') == 250 ? 'selected' : null : null }}>250 miles</option>
                                              <option value="500" {{ request()->get('radius') ? request()->get('radius') == 500 ? 'selected' : null : null }}>500 miles</option>
                                          </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group my-1">
                                            <label for="radius">Number of guests</label>
                                            <select id="radius" class="form-control form-custom-input form-select" name="num_guests">
                                                <option value="" disabled selected>@lang('message.choose')...</option>
                                                <option value="1" {{ request()->get('num_guests') ? request()->get('num_guests') == 1 ? 'selected' : null : null }}>1</option>
                                                <option value="2" {{ request()->get('num_guests') ? request()->get('num_guests') == 2 ? 'selected' : null : null }}>2</option>
                                                <option value="3" {{ request()->get('num_guests') ? request()->get('num_guests') == 3 ? 'selected' : null : null }}>3</option>
                                                <option value="4" {{ request()->get('num_guests') ? request()->get('num_guests') == 4 ? 'selected' : null : null }}>4</option>
                                                <option value="5" {{ request()->get('num_guests') ? request()->get('num_guests') == 5 ? 'selected' : null : null }}>5</option>
                                            </select>
                                          </div>
                                    </div>
                    
                                    <div class="col-md-4">
                                        <div class="form-group my-1">
                                            <label for="target_fish">@lang('message.target-fish')</label>
                                            <select class="form-control form-custom-input form-select" id="target_fish" name="target_fish[]" style="width:100%">
        
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="form-group my-1">
                                            <label for="water">@lang('message.body-type')</label>
                                            <select class="form-control form-select" id="water" name="water[]" style="width:100%">
                                    
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group my-1">
                                            <label for="methods">@lang('message.fishing-technique')</label>
                                            <select class="form-control form-select" id="methods" name="methods[]" style="width:100%">
                                            </select>
                                        </div>
                                    </div>
                                
                        </div>
                        <div class="row  rounded m-0">
                            <div class="col-6 col-sm-6 d-flex align-items-center my-2">
                                <a href="">View:</a>
                                <ul id="filterbyview" class="list-group list-group-horizontal ">
                                    <li class="list-group-item border-0 px-0">
                                        <div class="">
                                        <input class="form-check-input" type="radio" name="view" id="radiolist" {{ request()->get('view') ? request()->get('view') == 'list' ? 'checked' : null : null }} value="list">
                                        <label class="btn btn-default px-0 {{ request()->get('view') ? request()->get('view') == 'list' ? 'active' : '' : ''}}" for="radiolist"> <i class="fa fa-list" aria-hidden="true"></i> List</label>
                                      </div>
                                  </li>
                                    <li class="list-group-item border-0 px-0">
                                        <div class="">
                                        <input class="form-check-input" type="radio" name="view" id="radiomap" {{ request()->get('view') ? request()->get('view') == 'map' ? 'checked' : '' : ''}} value="map">
                                        <label class="btn btn-default px-0 {{ request()->get('view') ? request()->get('view') == 'list' ? 'active' : '' : ''}}" for="radiomap"><i class="fa fa-map" aria-hidden="true"></i> Map</label>
                                      </div>
                                    </li>
                                </ul>
                         
                            </div>
                            <div class="col-6 col-sm-6 my-2">
                                <div class="row-sort">
                                    <div class="d-flex flex-sm-row flex-column align-items-sm-center align-items-stretch my-2 justify-content-end">
                                        <div class="d-flex align-items-center justify-content-end">
                                            <label class="fs-sm me-2 pe-1 text-nowrap" for="sortby"><i class="fi-arrows-sort text-muted mt-n1 me-2"></i>Sort by:</label>
                                            <select class="form-select form-select-sm" name="sortby" id="sortby">
                                                <option value="" disabled selected>@lang('message.choose')...</option>
                                                <option value="newest" {{request()->get('sortby') ? request()->get('sortby') == 'newest' ? 'selected' : '' : '' }}>Newest</option>
                                                <option value="price-asc" {{request()->get('sortby') ? request()->get('sortby') == 'price-asc' ? 'selected' : '' : '' }}>Low to High Price</option>
                                                <option value="price-desc" {{request()->get('sortby') ? request()->get('sortby') == 'price-desc' ? 'selected' : '' : '' }}>High to Low Price</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
                   
                <div class="d-flex justify-content-start">
                    <button  id="toggleFilterBtn" class="btn outline-none"><span class="fw-bold text-decoration-underline">Filters</span><i class="fa fa-filter color-primary" aria-hidden="true"></i></button>
                </div>

            </div>

            <div class="row column-reverse-row-normal">
                <div id="contentContainer" class="col-xxl-12 col-lg-12">
                    <div class="tours-list__right">
                        <div class="tours-list__inner">
                            <div id="guidings-list">
                                @include('pages.guidings.partials.guidingcontainer')
                            </div>                   
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>
    <!--Tours List End-->

    <!-- Modal -->
    @foreach($guidings as $guiding)
        {{-- @include('pages.guidings.content.guidingModal') --}}
    @endforeach


    <!-- Endmodal -->

    <br>
@endsection

@section('js_after')


<script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>


<script>
    // Function to update the URL with the filter parameters and page number
    function updateUrlWithFilters(page) {
        var formData = $('#filterContainer').serialize();
        var url = '{{ route('guidings.index') }}' + '?' + formData;
        if (page) {
            url += '&page=' + page;
        }
        history.replaceState(null, null, url);
    }

    // Function to submit the form via AJAX
    function submitFormViaAjax(page = 1) {
        // Serialize the form data
        var formData = $('#filterContainer').serialize() + '&page=' + page + '&view=list';

        var selectedView = $("input[name='view']:checked").val();
        formData += '&view=' + selectedView;


        // Send an AJAX request with the serialized data to the backend server
        $.ajax({
            type: 'GET',
            url: '{{ route('guidings.index') }}',
            data: formData,
            success: function (response) {
                // Update the list of guidings with the filtered results
                $('#guidings-list').html(response.guidings);
                // Update the URL with the filter parameters and page number
                updateUrlWithFilters(page);

                if (selectedView === 'map') {
                    setTimeout(function() {
                        initializeMap(response.allGuidings);
                    }, 200); // Adjust the delay (in milliseconds) if needed
                }
            },
            error: function (xhr, textStatus, errorThrown) {
                console.log(xhr.responseText);
            }
        });
    }

    $(document).ready(function () {
        // Attach event listeners to filter inputs
        $('#filterContainer select, #filterContainer input[type="radio"]').on('change input', function () {
            submitFormViaAjax();
        });

        // Attach event listener to the form's reset event
        $('#filterContainer').on('reset', function () {
            // Delay the form reset to let the browser process it first
            setTimeout(function () {
                // Check if any filter inputs have values
                var anyFilterApplied = $('#filterContainer select, #filterContainer input[type="text"]').filter(function () {
                    return $(this).val() !== '';
                }).length > 0;

                if (anyFilterApplied) {
                    // If any filters are still applied, submit the form via AJAX
                    submitFormViaAjax();
                } else {
                    // If no filters are applied, reset the URL to its initial state
                    updateUrlWithFilters();
                }
            }, 10);
        });

        // Initial form submission on page load
        submitFormViaAjax();

        // Handle pagination links with event delegation
        $(document).on('click', '.pagination a', function (e) {
            e.preventDefault();
            var page = $(this).attr('href').split('page=')[1];
            submitFormViaAjax(page);
        });


    });
</script>


<script>
    $('#sortby').on('change',function(){
        $('#form-sortby').submit();
    });
</script>
<script>
// Get the toggle button and filter container elements
var toggleBtn = document.getElementById('toggleFilterBtn');
var filterContainer = document.getElementById('filter-view');

// Add click event listener to the toggle button
toggleBtn.addEventListener('click', function() {
    // Toggle the visibility of the filter container
    filterContainer.classList.toggle('d-block');
});
</script>

<script>
    initializeSelect2();

function initializeSelect2() {

    var selectTarget = $('#target_fish');
    var selectWater = $('#water');
    var selectMethod = $('#methods');

    $("#target_fish").select2({

        multiple: true,
        width: 'resolve' // need to override the changed default

    });

    @foreach($alltargets as $target)
    var targetOption = new Option('{{ $target->name }}', '{{ $target->id }}');
    selectTarget.append(targetOption);

    @if(request()->get('target_fish'))
        @if(in_array($target->id, request()->get('target_fish')))
        $(targetOption).prop('selected', true);
        @endif
    @endif


    @endforeach
  
    // Trigger change event to update Select2 display
    selectTarget.trigger('change');






    $("#water").select2({
        multiple: true,
        width: 'resolve' // need to override the changed default
    });

    @foreach($allwaters as $water)
    var waterOption = new Option('{{ $water->name }}', '{{ $water->id }}');
    selectWater.append(waterOption);

    @if(request()->get('water'))
        @if(in_array($water->id, request()->get('water')))
        $(waterOption).prop('selected', true);
        @endif
    @endif


    @endforeach
  
    // Trigger change event to update Select2 display
    selectWater.trigger('change');





    $("#methods").select2({
        multiple: true,
        width: 'resolve' // need to override the changed default
    });

    @foreach($allmethods as $method)
    var methodOption = new Option('{{ $method->name }}', '{{ $method->id }}');
    selectMethod.append(methodOption);

    
    @if(request()->get('methods'))
        @if(in_array($method->id, request()->get('methods')))
        $(methodOption).prop('selected', true);
        @endif
    @endif


    @endforeach
  
    // Trigger change event to update Select2 display
    selectMethod.trigger('change');




}



</script>


<script>
function initializeMap(guidingData) {
    const map = new google.maps.Map(document.getElementById("map"), {
        zoom: 5,
        center: { lat: parseFloat('{{ request()->get('placeLat') ?: 51.165691 }}'), lng: parseFloat('{{ request()->get('placeLng') ?: 10.451526 }}') },
        mapId: '8f348c2f6c51f6f0'
    });

    // If guidingData is not empty, add markers for each guiding
    if (guidingData.length > 0) {
        const markers = guidingData.map((guiding) => {
            return new google.maps.marker.AdvancedMarkerElement({
                position: { lat: parseFloat(guiding.lat), lng: parseFloat(guiding.lng) },
                map: map,
            });
        });

        // Add info windows for each marker
        const infowindows = guidingData.map((guiding) => {
            return new google.maps.InfoWindow({
                content: `
                    <div class="card p-0 border-0" style="width: 200px;">
                        <div class="card-body border-0 p-0">
                            <h5 class="card-title" style="font-size: 14px;">${guiding.title}</h5>
                            <!-- Add other guiding information here as needed -->
                        </div>
                    </div>
                `
            });
        });

        // Create bounds object to fit all markers
        const bounds = new google.maps.LatLngBounds();

        // Extend bounds with each marker position
        markers.forEach((marker) => {
            bounds.extend(marker.getPosition());
        });

        // Fit the map to the new bounds
        map.fitBounds(bounds);

        // Add click event listeners to show info windows on marker click
        markers.forEach((marker, index) => {
            marker.addListener("click", () => {
                infowindows.forEach((infowindow) => {
                    infowindow.close();
                });
                infowindows[index].open(map, marker);
            });
        });
    }
}








function initialize() {
    var input = document.getElementById('searchPlace');
    var autocomplete = new google.maps.places.Autocomplete(input);
    google.maps.event.addListener(autocomplete, 'place_changed', function () {
        var place = autocomplete.getPlace();
        document.getElementById('placeLat').value = place.geometry.location.lat();
        document.getElementById('placeLng').value = place.geometry.location.lng();
        submitFormViaAjax();
    });
}

window.addEventListener('load', initialize);

window.addEventListener('load', function() {
    var placeLatitude = '{{ request()->get('placeLat') }}'; // Replace with the actual value from the request
    var placeLongitude = '{{ request()->get('placeLng') }}'; // Replace with the actual value from the request

    if (placeLatitude && placeLongitude) {
        // The place latitude and longitude are present, so set the values in the form fields
        document.getElementById('placeLat').value = placeLatitude;
        document.getElementById('placeLng').value = placeLongitude;
    } else {
        // The place latitude and longitude are not present, so execute the geolocation function
        getLocation();
    }
});

function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(showPosition);
    } else {
        console.log('Geolocation is not supported by this browser.');
    }
}

function showPosition(position) {
    var lat = position.coords.latitude;
    var lng = position.coords.longitude;
    document.getElementById('placeLat').value = lat;
    document.getElementById('placeLng').value = lng;
    
    codeLatLng(lat, lng);
}

function codeLatLng(lat, lng) {
    return null;
    // var geocoder = new google.maps.Geocoder();
    // var latlng = new google.maps.LatLng(lat, lng);
    // geocoder.geocode({'latLng': latlng}, function (results, status) {
    //     if (status === google.maps.GeocoderStatus.OK) {
    //         if (results[0]) {
    //             document.getElementById('searchPlace').value = results[0].formatted_address;
    //         } else {
    //             console.log('No results found');
    //             return null;
    //         }
    //     } else {
    //         console.log('Geocoder failed due to: ' + status);
    //         return null;
    //     }
    // });
}

       
</script>


    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>

@endsection

