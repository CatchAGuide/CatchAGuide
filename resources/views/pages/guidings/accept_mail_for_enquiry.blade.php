@extends('layouts.app')
@section('css_after')

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
    .contact-page{
        padding:0px;
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

    .carousel.slide img {
        /* max-height: 265px; */
        object-fit: cover;
        background: black;
        height: 300px;
        /* min-height: 160px; */
        /* height:228px; */
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
        #filterContainer{
            display:none;
        }
        
    }

    #radius{
        background: url("data:image/svg+xml,<svg height='10px' width='10px' viewBox='0 0 16 16' fill='%23808080' xmlns='http://www.w3.org/2000/svg'><path d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/></svg>") no-repeat;
        background-position: right 0.3rem center !important;
    }
    #num-guests{
        background: url("data:image/svg+xml,<svg height='10px' width='10px' viewBox='0 0 16 16' fill='%23808080' xmlns='http://www.w3.org/2000/svg'><path d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/></svg>") no-repeat;
        background-position: right 0.3rem center !important;
    }
    .custom-select:has(option:disabled:checked[hidden]) {
    color: gray;
    }
    .custom-select option{
        color:black;
    }

    .btn-outline-theme{
    color: #E8604C;
    border-color: #E8604C;
    }
    .test{
        font-family: var(--thm-reey-font);
        font-size: 45px;
        padding: 30px 0px;
        margin-bottom:30px;
    }
    .question-label{
        font-family: var(--thm-reey-font);
        padding:30px 0px;
    }
    .information__single{
        padding:15px !important;
    }
    .sr-description{
        text-align: left;
    }
    .new-bg{
            background:#313041;
    }
    .btn:focus {
        outline: none;
        box-shadow: none;
    }
    .counter{
        background: #E8604C;
        padding: 5px;
        color: #fff;
        margin-bottom: 20px;
    }
    .counter .h2{
        font-weight: bolder;
    }
</style>
@endsection
@section('content')
<div class="container">

    {{-- <h1 class="h2 fw-bold color-primary mt-3 test">@lang('request.title')</h1> --}}

    <div>
        <ul class="thm-breadcrumb list-unstyled">
            <li><a href="{{ route('welcome') }}">@lang('message.home')</a></li>
            <li><span>&#183;</span></li>
            <li class="active">@lang('request.btnsearch')</li>
        </ul>
    </div>

</div>

<!--Page Header End-->

<!--Contact Page Start-->
<section class="contact-page">
    <div class="container">
        <div class="row">
            <div class="col-xl-12 col-lg-12">
                <div class="contact-page__right">
                    <div class="container border rounded shadow-sm my-4 px-0">
                        <div class="header new-bg">
                            <h1 class="h3 p-3 header-title text-white">@lang('request.btnsearch')</h1>
                        </div>
                        <div class="content p-4">
                            <div class="content-header">
                                @livewire('search-request')                               
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--Contact Page End-->

<!--Information Start-->
<section class="information">
    <div class="container">
        <div class="row">
            <div class="col-xl-3 col-lg-3">
                <!--Information Single-->
                <div class="information__single">
                    <div class="information__icon">
                        <span class="icon-place"></span>
                    </div>
                    <div class="information__text">
                        <p>Düsseldorf <br> NRW</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-4">
                <!--Information Single-->
                <div class="information__single">
                    <div class="information__icon">
                        <span class="icon-phone-call"></span>
                    </div>
                    <div class="information__text">
                        <h4>
                            <a href="tel:+49{{env('CONTACT_NUM')}}" class="information__number-1">+49 (0) {{env('CONTACT_NUM')}}</a>

                        </h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-5 col-lg-5">
                <!--Information Single-->
                <div class="information__single">
                    <div class="information__icon">
                        <span class="icon-at"></span>
                    </div>
                    <div class="information__text">
                        <h4>
                            <a href="mailto:info.catchaguide@gmail.com" class="information__mail-2">info.catchaguide@gmail.com</a>
                        </h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--Information End-->

@endsection

@section('js_after')
{{-- <script type="text/javascript"
    src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places,geocoding">
</script> --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // function initializeAutocomplete(countryInputId, cityInputId) {
        //     var countryInput = document.getElementById(countryInputId);
        //     var cityInput = document.getElementById(cityInputId);

        //     var countryAutocomplete = new google.maps.places.Autocomplete(countryInput);
        //     var cityAutocomplete = new google.maps.places.Autocomplete(cityInput);

        //     function handlePlaceChange(input, latInputId, lngInputId) {
        //         return function() {
        //             var place = input.getPlace();
        //             let location = place.formatted_address;
        //             let country = '';

        //             for (var i = 0; i < place.address_components.length; i++) {
        //                 for (var j = 0; j < place.address_components[i].types.length; j++) {
        //                     if (place.address_components[i].types[j] == 'country') {
        //                         country = place.address_components[i].long_name;
        //                         break;
        //                     }
        //                 }
        //             }

        //             // Livewire.emit('locationSelected', {
        //             //     country,
        //             //     location
        //             // });

        //             // Also update the hidden fields for latitude and longitude
        //             document.getElementById(latInputId).value = place.geometry.location.lat();
        //             document.getElementById(lngInputId).value = place.geometry.location.lng();
        //         };
        //     }

        //     google.maps.event.addListener(countryAutocomplete, 'place_changed', handlePlaceChange(
        //         countryAutocomplete,
        //         'countryPlaceLat', 'countryPlaceLng'));
        //     google.maps.event.addListener(cityAutocomplete, 'place_changed', handlePlaceChange(cityAutocomplete,
        //         'cityPlaceLat', 'cityPlaceLng'));
        // }

        // initializeAutocomplete('countrySearchPlace', 'citySearchPlace');

        

        var dayType = document.getElementById('guideType');
        var one = document.getElementById('fishingHoliday');
        var two = document.getElementById('guidedFishing');

        dayType.addEventListener('change', function() {
            
            if (dayType.value == 'fishing Holidays') {
                one.classList.add('d-block');
                one.classList.remove('d-none');

                two.classList.add('d-none')
                two.classList.remove('d-block')
            }else{
                one.classList.add('d-none');
                one.classList.remove('d-block');

                two.classList.add('d-block')
                two.classList.remove('d-none')
            }
        });

    }, false);
</script>

@endsection

