@extends('layouts.app')

@section('title', $row_data->name)
@section('description', $row_data->title)


@section('custom_style')
<style>

    /*#mobileherofilter .column-input input{
        border-bottom:1px solid #a7a7a7 !important;
        border:none;
        outline:none !important;
    }
    #mobileherofilter .column-input i{
        color:#E8604C !important;
    }
    #mobileherofilter .column-input input,select{
       padding-left:30px !important;
    }
    #mobileherofilter .form-control:focus {
        border-color: inherit;
        -webkit-box-shadow: none;
        box-shadow: none;
        outline:none !important;
    }
    #mobileherofilter .myselect2{
        border-bottom:1px solid #a7a7a7 !important;
        padding:2px 0px;
        border-width: 1px !important;
        background-color: white;
    }
    #mobileherofilter .myselect2 li.select2-selection__choice{
            background-color: #313041 !important;
            color: #fff !important;
            border: 0 !important;
            font-size:14px;
            vertical-align: middle !important;
            margin-top:0 !important;
         
    }
    #mobileherofilter .myselect2 button.select2-selection__choice__remove{
        border: 0 !important;
        color: #fff !important;
    }
    #mobileherofilter .new-filter-btn{
        background-color:#E8604C;
        color:#fff;
    }
    #mobileherofilter .new-filter-btn:hover{
        background-color:#313041;
    }*/
    #carousel-regions,
    #carousel-cities {
        min-height: 301.6px;
    }
    #carousel-regions .dimg-fluid,
    #carousel-cities .dimg-fluid {
        min-height: 301.6px;
    }
    #destination,
    #destination a
     {
        font-size: 14px;
    }
    .country-listing-item p {
        font-size: 12px;
    }
    .country-listing-item-rating p {
        line-height: 12px;
    }
    #destination-form input,
    #destination-form select {
        padding-left: 30px;
    }

    @media (max-width: 767px) {
        #carousel-regions .carousel-inner .carousel-item > div {
            display: none;
        }
        #carousel-regions .carousel-inner .carousel-item > div:first-child {
            display: block;
        }
    }

    #carousel-regions .carousel-inner .carousel-item.active,
    #carousel-regions .carousel-inner .carousel-item-next,
    #carousel-regions .carousel-inner .carousel-item-prev,
    #carousel-cities .carousel-inner .carousel-item.active,
    #carousel-cities .carousel-inner .carousel-item-next,
    #carousel-cities .carousel-inner .carousel-item-prev {
        display: flex;
    }

    /* medium and up screens */
    @media (min-width: 768px) {
        #carousel-regions .carousel-inner .carousel-item img,
        #carousel-cities .carousel-inner .carousel-item img {
            margin-right: 2px;
        }
        
        #carousel-regions .carousel-inner .carousel-item-end.active,
        #carousel-regions .carousel-inner .carousel-item-next,
        #carousel-cities .carousel-inner .carousel-item-end.active,
        #carousel-cities .carousel-inner .carousel-item-next {
          transform: translateX(25%);
        }
        
        #carousel-regions .carousel-inner .carousel-item-start.active, 
        #carousel-regions .carousel-inner .carousel-item-prev,
        #carousel-cities .carousel-inner .carousel-item-start.active, 
        #carousel-cities .carousel-inner .carousel-item-prev {
          transform: translateX(-25%);
        }
    }

    #carousel-regions .carousel-inner .carousel-item-end,
    #carousel-regions .carousel-inner .carousel-item-start,
    #carousel-cities .carousel-inner .carousel-item-end,
    #carousel-cities .carousel-inner .carousel-item-start { 
      transform: translateX(0);
    }

    #map-placeholder {
        width:100%;
        height: 200px;
        background-image: url({{ url('') }}/assets/images/map-bg.png);
        text-align: center;
        padding-top: 40%;
    }

    /*.country-listing-item .carousel-inner {
        height: 256px;
    }*/

    .country-listing-item .carousel-control-prev,
    .country-listing-item .carousel-control-next {
        width: 30px!important;
        height: 30px!important;
    }

    .country-listing-item .carousel-item,
    .country-listing-item .carousel-item img {
        width: 256px!important;
        height: 300px!important;
        object-fit: cover;
    }

    .btn-outline-theme {
        color: #E8604C!important;
        border-color: #E8604C!important;
    }
    /*.select2-container--default {
        padding-left: 25px !important;
    }
    .select2-search__field {
        border-bottom: 1px solid #ccc!important;
    }*/
    #num-guests {
        background: url("data:image/svg+xml,<svg height='10px' width='10px' viewBox='0 0 16 16' fill='%23808080' xmlns='http://www.w3.org/2000/svg'><path d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/></svg>") no-repeat;
        background-position: right 0.3rem center !important;
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
</style>
@endsection

@section('content')
    <!--News One Start-->
    <div class="container" id="destination">
        <div class="row">
            <div class="col-12">
                <h1>{{ $row_data->title }} <p class="h4">{{ $row_data->sub_title }}</p></h1>

                <form class="mt-3 mb-4" action="" method="get" id="destination-form">
                    <div class="card p-3">
                        <div class="row">
                            <div class="col-lg-3 p-1">
                                <div class="form-group">
                                    <div class="d-flex align-items-center small">
                                        <i class="fa fa-search fa-fw text-muted position-absolute ps-2"></i>
                                        <input  id="searchPlace" name="place" type="text" class="form-control rounded-0" placeholder="@lang('homepage.searchbar-destination')"  autocomplete="on">
                                        <input type="hidden" id="placeLat" name="placeLat"/>
                                        <input type="hidden" id="placeLng" name="placeLng"/>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 p-1">
                                <div class="form-group">
                                    <div class="d-flex align-items-center small">
                                        <i class="fa fa-user fa-fw text-muted position-absolute ps-2"></i>
                                        <input type="number" min="1" max="5" class="form-control rounded-0" name="num_guests" placeholder="@lang('homepage.searchbar-person')" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 p-1">
                                <div class="d-flex align-items-center small myselect2">
                                    <i class="fa fa-fish fa-fw text-muted position-absolute ps-1"></i>
                                    <select class="form-control form-select" id="home_target_fish" name="target_fish[]" style="width:100%"></select>
                                </div>
                            </div>
                            <div class="col-lg-3 p-1">
                                <button type="submit" class="btn btn-danger form-control new-filter-btn">@lang('homepage.searchbar-search')</button>
                            </div>
                        </div>
                    </div> 
                </form>

                <div class="mb-3">{!! nl2br($row_data->introduction) !!}asdf</div>

                @if($regions->count() > 0)
                <h5 class="mb-2">All Region</h5>
                <div id="carousel-regions" class="carousel slide mb-4" data-bs-ride="carousel">
                    <div class="carousel-inner" role="listbox">
                        @foreach($regions as $region)
                        <div class="carousel-item active">
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-img">
                                        <!-- <img src="https://place-hold.it/300x300" class="dimg-fluid" width="300px" alt="..."> -->
                                        <img src="{{ $region->getThumbnailPath() }}" class="dimg-fluid" width="300px" alt="...">
                                    </div>
                                    <div class="card-img-overlay">{{ $region->name }}</div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @if($regions->count() > 4)
                    <button class="carousel-control-prev" type="button" data-bs-target="#carousel-regions" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carousel-regions" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                    @endif
                </div>
                @endif
                @if($cities->count() > 0)
                <h5 class="mb-2">All Cities</h5>
                <div id="carousel-cities" class="carousel slide mb-4" data-bs-ride="carousel">
                    <div class="carousel-inner" role="listbox">
                        @foreach($cities as $city)
                        <div class="carousel-item active">
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-img">
                                        <!-- <img src="https://place-hold.it/300x300" class="dimg-fluid" width="300px" alt="..."> -->
                                        <img src="{{ $city->getThumbnailPath() }}" class="dimg-fluid" width="300px" alt="...">
                                    </div>
                                    <div class="card-img-overlay">{{ $city->name }}</div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @if($cities->count() > 4)
                    <button class="carousel-control-prev" type="button" data-bs-target="#carousel-cities" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carousel-cities" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                    @endif
                </div>
                @endif
                <h5 class="mb-2">Listing </h5>
                <div class="row mb-2">
                    <div class="col-sm-12 col-lg-3">
                        <div class="card mb-2">
                            <div id="map-placeholder">
                                <button class="btn btn-primary" data-bs-target="#mapModal" data-bs-toggle="modal">Show on map</button>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                Filter By:
                            </div>
                            <div class="card-body border-bottom">
                                <form method="get" action="{{ url()->current() }}">
                                    <div class="form-group mb-3 d-flex align-items-center border-bottom">
                                        <!-- <label for="num_guests" class="form-label">Number of Guests</label> -->
                                        <div class="input-group-prepend border-0 ">
                                            <span class="d-flex align-items-center px-2 h-100">
                                                <i class="fas fa-user"></i>
                                            </span>
                                        </div>
                                        <select class="form-control form-select  border-0 rounded-0 custom-select" id="num_guests" name="num_guests">
                                            <option disabled selected hidden>-- Select --</option>
                                            <option value="">@lang('message.choose')...</option>
                                            <option value="1" {{ request()->get('num_guests') ? request()->get('num_guests') == 1 ? 'selected' : null : null }}>1</option>
                                            <option value="2" {{ request()->get('num_guests') ? request()->get('num_guests') == 2 ? 'selected' : null : null }}>2</option>
                                            <option value="3" {{ request()->get('num_guests') ? request()->get('num_guests') == 3 ? 'selected' : null : null }}>3</option>
                                            <option value="4" {{ request()->get('num_guests') ? request()->get('num_guests') == 4 ? 'selected' : null : null }}>4</option>
                                            <option value="5" {{ request()->get('num_guests') ? request()->get('num_guests') == 5 ? 'selected' : null : null }}>5</option>
                                        </select>
                                    </div>

                                    <div class="form-group mb-3 d-flex align-items-center border-bottom">
                                        <!-- <label for="target_fish" class="form-label">Target Fish</label> -->
                                        <div class="px-2 select2-icon">
                                            <img src="{{asset('assets/images/icons/fish.png')}}" height="20" width="20" alt="" />
                                        </div>
                                        <select class="form-control form-select mb-3" id="target_fish" name="target_fish[]"></select>
                                    </div>
                                    <div class="form-group mb-3 d-flex align-items-center border-bottom">
                                        <!-- <label for="water_type" class="form-label">Water Type</label> -->
                                        <div class="px-2 select2-icon">
                                            <img src="{{asset('assets/images/icons/water-waves.png')}}" height="20" width="20" alt="" />
                                        </div>
                                        <select class="form-select mb-3" id="water" name="water[]"></select>
                                    </div>
                                    <div class="form-group mb-3 d-flex align-items-center border-bottom">
                                        <!-- <label for="fishing_technique" class="form-label">Fishing Technique</label> -->
                                        <div class="px-2 select2-icon">
                                            <img src="{{asset('assets/images/icons/fishing.png')}}" height="20" width="20" alt="" />
                                        </div>
                                        <select class="form-select" id="methods" name="methods[]"></select>
                                    </div>
                                    <button class="btn btn-sm theme-primary btn-theme-new w-100" type="submit">Search</button>   
                                </form> 
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-lg-9 country-listing-item">
                        @foreach($guidings as $guiding)
                        <div class="row m-0 mb-2">
                            <div class="col-md-12">
                                <div class="row p-2 border shadow-sm bg-white rounded">
                                    <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 mt-1">
                                        <div id="carouselExampleControls-{{$guiding->id}}" class="carousel slide" data-bs-ride="carousel" data-bs-interval="false">
                                            <div class="carousel-inner">
                                                @if(count(get_galleries_image_link($guiding)))
                                                    @foreach(get_galleries_image_link($guiding) as $index => $gallery_image_link)
                                                        <div class="object-fit-cover carousel-item @if($index == 0) active @endif">
                                                            <img class="d-block object-fit-cover w-100" src="{{ asset($gallery_image_link) }}">
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>

                                            @if(count(get_galleries_image_link($guiding)) > 1)
                                                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls-{{$guiding->id}}" data-bs-slide="prev">
                                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                    <span class="visually-hidden">Previous</span>
                                                </button>
                                                <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls-{{$guiding->id}}" data-bs-slide="next">
                                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                    <span class="visually-hidden">Next</span>
                                                </button>
                                            @endif
                                        </div>
                                
                                    </div>
                                    <div class="col-11 col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6 mt-1">
                                        <h5 class="fw-bolder text-truncate"><a class="text-dark" href="{{ route('guidings.show',[$guiding->id,$guiding->slug]) }}">{{translate($guiding->title)}}</a></h5>
                                        <div class="ratings mr-2 color-primary my-1" style="font-size:0.80rem">
                                            @if(count($guiding->user->received_ratings) > 0)
                                            @switch(two($guiding->user->average_rating()))
                                                @case(two($guiding->user->average_rating()) >= 5)
                                                    - {{one($guiding->user->average_rating())}}
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                    @break
                                                @case(two($guiding->user->average_rating()) >= 4.5)
                                                    - {{one($guiding->user->average_rating())}}
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star-half"></i>
                                                    @break
                                                @case(two($guiding->user->average_rating()) >= 4)
                                                    - {{one($guiding->user->average_rating())}}
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                    @break
                                                @case(two($guiding->user->average_rating()) >= 3.5)
                                                    - {{one($guiding->user->average_rating())}}
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star-half"></i>
                                                    @break
                                                @case(two($guiding->user->average_rating()) >= 3)
                                                    - {{one($guiding->user->average_rating())}}
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                    @break
                                                @case(two($guiding->user->average_rating()) >= 2.5)
                                                    - {{one($guiding->user->average_rating())}}
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star-half"></i>
                                                    @break
                                                @case(two($guiding->user->average_rating()) >= 2)
                                                    - {{one($guiding->user->average_rating())}}
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                    @break
                                                @case(two($guiding->user->average_rating()) >= 1.5)
                                                    - {{one($guiding->user->average_rating())}}
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star-half"></i>
                                                    @break
                                                @case(two($guiding->user->average_rating()) >= 1)
                                                    - {{one($guiding->user->average_rating())}}
                                                    <i class="fa fa-star"></i>
                                                    @break
                                                @default
                                                    - {{one($guiding->user->average_rating())}}
                                                    <i class="fa fa-star"></i>
                                            @endswitch

                                            @if(count($guiding->user->received_ratings) >= 2) 
                                                ({{count($guiding->user->received_ratings)}} Bewertungen)
                                            @else 
                                                ({{count($guiding->user->received_ratings)}} Bewertung)
                                            @endif

                                        @endif     
                                        </div>
                                        <span class="text-center" style="font-size:1rem;color:rgb(28, 28, 28)"><i class="fas fa-map-marker-alt me-2"></i>{{ translate($guiding->location) }}</span>                                      
                                        <div class="row mt-2">
                                            <div class="col-6 col-sm-6 col-md-6">
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <img src="{{asset('assets/images/icons/fish.png')}}" height="20" width="20" alt="" />
                                                    </div>
                                                    <div class="mx-2">
                                                        <div class="tours-list__content__trait__text" style="font-size:0.75rem">
                                                            @php
                                                            $guidingTargets = $guiding->guidingTargets->pluck('name')->toArray();
                                                            if(app()->getLocale() == 'en'){
                                                                $guidingTargets =  $guiding->guidingTargets->pluck('name_en')->toArray();
                                                            }
                                                            @endphp
                                                            
                                                            @if(!empty($guidingTargets))
                                                                {{ implode(', ', $guidingTargets) }}
                                                            @else
                                                            {{ translate($guiding->threeTargets()) }}
                                                            {{$guiding->target_fish_sonstiges ? " & " . translate($guiding->target_fish_sonstiges) : ""}}
                                                            @endif
                                                        </div>
                                                    
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-sm-6 col-md-6">
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <img src="{{asset('assets/images/icons/water-waves.png')}}" height="20" width="20" alt="" />
                                                    </div>
                                                    <div class="mx-2">
                                                        <div class="tours-list__content__trait__text" style="font-size:0.75rem">
                                                            @php
                                                            $guidingWaters = $guiding->guidingWaters->pluck('name')->toArray();
                                                            if(app()->getLocale() == 'en'){
                                                                $guidingWaters =  $guiding->guidingWaters->pluck('name_en')->toArray();
                                                            }
                                                            @endphp
                                                            
                                                            @if(!empty($guidingWaters))
                                                                {{ implode(', ', $guidingWaters) }}
                                                            @else
                                                            {{ translate($guiding->threeWaters()) }}
                                                            {{$guiding->water_sonstiges ? " & " . translate($guiding->water_sonstiges) : ""}}
                                                            @endif
                                                        </div>
                                                    
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-sm-6 col-md-6">
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <img src="{{asset('assets/images/icons/fishing-tool.png')}}" height="20" width="20" alt="" />
                                                    </div>
                                                    <div class="mx-2">
                                                        <div class="tours-list__content__trait__text" style="font-size:0.75rem">
                                                            @php
                                                            $fishingtype = null;
                                                            if($guiding->fishingTypes){
                                                                if(app()->getLocale() == 'en'){
                                                                    $fishingtype = $guiding->fishingTypes->name_en;
                                                                }else{
                                                                   $fishingtype =  $guiding->fishingTypes->name;
                                                                }
                                                            }
                                                        
                                                            @endphp
                
                                                            @if($fishingtype) {{$fishingtype}}  @else {{$guiding->fishing_type}}@endif
                                                        </div>
                                                    
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-sm-6 col-md-6">
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <img src="{{asset('assets/images/icons/fishing.png')}}" height="20" width="20" alt="" />
                                                    </div>
                                                    <div class="mx-2">
                                                        <div class="tours-list__content__trait__text" style="font-size:0.75rem">
                                                            @php
                                                            $guidingMethods = $guiding->guidingMethods->pluck('name')->toArray();
                                                            if(app()->getLocale() == 'en'){
                                                                $guidingMethods =  $guiding->guidingMethods->pluck('name_en')->toArray();
                                                            }
                                                            @endphp
                                                            
                                                            @if(!empty($guidingMethods))
                                                                {{ implode(', ', $guidingMethods) }}
                                                            @else
                                                            {{ $guiding->threeMethods() }}
                                                            {{$guiding->methods_sonstiges && $guiding->threeMethods() > 0 ? " & " . translate($guiding->methods_sonstiges) : null}}
                                                            @endif
                                                        </div>
                                                    
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-sm-6 col-md-6">
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <img src="{{asset('assets/images/icons/fishing-man.png')}}" height="20" width="20" alt="" />
                                                    </div>
                                                    <div class="mx-2">
                                                        <div class="tours-list__content__trait__text" style="font-size:0.75rem">
                                                            @php
                                                            $whereFishing = null;
                                                            if($guiding->fishingFrom){
                                                                if(app()->getLocale() == 'en'){
                                                                    $whereFishing = $guiding->fishingFrom->name_en;
                                                                }else{
                                                                   $whereFishing =  $guiding->fishingFrom->name;
                                                                }
                                                            }
                                                        
                                                            @endphp
                                                            @if($whereFishing) {{$whereFishing}} @else {{$guiding->fishing_from}} @endif    
                                                        </div>
                                                    
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-sm-6 col-md-6">
                                                <div class="d-flex align-items-center mt-2">
                                                    <div class="icon-small">
                                                        <span class="icon-user"></span>
                                                    </div>
                                                    <div class="mx-2" style="font-size:0.75rem">
                                                    {{ $guiding->max_guests }} @if($guiding->max_guests != 1) {{translate('Personen')}} @else {{translate('Person')}} @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-sm-6 col-md-6">
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <img src="{{asset('assets/images/icons/clock.svg')}}" height="20" width="20" alt="" />
                                                    </div>
                                                    <div class="mx-2" style="font-size:0.75rem">
                                                        {{ $guiding->duration }} @if($guiding->duration != 1) {{translate('Stunden')}} @else {{translate('Stunde')}} @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
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
                                            </div>
     
                                        </div>
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
                            </div>
                        </div>
                        @endforeach
                        {!! $guidings->links('vendor.pagination.default') !!}
                    </div>
                </div>

                <div class="mb-3">{!! $row_data->content !!}</div>

                <h4 class="mb-2">{{ $row_data->fish_avail_title }}</h4>
                <p>{!! $row_data->fish_avail_intro !!}</p>
                <table class="table table-bordered" id="fish_chart_table">
                    <thead>
                        <tr>
                            <th width="28%">Fish</th>
                            <th width="6%" class="text-center">Jan</th>
                            <th width="6%" class="text-center">Feb</th>
                            <th width="6%" class="text-center">Mar</th>
                            <th width="6%" class="text-center">Apr</th>
                            <th width="6%" class="text-center">May</th>
                            <th width="6%" class="text-center">Jun</th>
                            <th width="6%" class="text-center">Jul</th>
                            <th width="6%" class="text-center">Aug</th>
                            <th width="6%" class="text-center">Sep</th>
                            <th width="6%" class="text-center">Oct</th>
                            <th width="6%" class="text-center">Nov</th>
                            <th width="6%" class="text-center">Dec</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fish_chart as $row)
                        <tr>
                            <td>{{ $row->fish }}</td>
                            <td class="text-center" style="background-color: {{ $row->bg_color($row->jan) }}"></td>
                            <td class="text-center" style="background-color: {{ $row->bg_color($row->feb) }}"></td>
                            <td class="text-center" style="background-color: {{ $row->bg_color($row->mar) }}"></td>
                            <td class="text-center" style="background-color: {{ $row->bg_color($row->apr) }}"></td>
                            <td class="text-center" style="background-color: {{ $row->bg_color($row->may) }}"></td>
                            <td class="text-center" style="background-color: {{ $row->bg_color($row->jun) }}"></td>
                            <td class="text-center" style="background-color: {{ $row->bg_color($row->jul) }}"></td>
                            <td class="text-center" style="background-color: {{ $row->bg_color($row->aug) }}"></td>
                            <td class="text-center" style="background-color: {{ $row->bg_color($row->sep) }}"></td>
                            <td class="text-center" style="background-color: {{ $row->bg_color($row->oct) }}"></td>
                            <td class="text-center" style="background-color: {{ $row->bg_color($row->nov) }}"></td>
                            <td class="text-center" style="background-color: {{ $row->bg_color($row->dec) }}"></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="row">
                    <div class="col-sm-12 col-md-6 col-lg-6">
                        <h4>{{ $row_data->size_limit_title }}</h4>
                        <p>{!! $row_data->size_limit_intro !!}</p>
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="20%">Fish</th>
                                    <th width="80%">Size Limit</th>
                                </tr>
                            </thead>
                            <tbody>
                            @if(!empty($fish_size_limit))
                                @foreach($fish_size_limit as $row)
                                <tr>
                                    <td>{{ $row->fish }}</td>
                                    <td>{{ $row->data }}</td>
                                </tr>
                                @endforeach
                            @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-6 mb-3">
                        <h4>{{ $row_data->time_limit_title }}</h4>
                        <p>{!! $row_data->time_limit_intro !!}</p>
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="20%">Fish</th>
                                    <th width="80%">Time Limit</th>
                                </tr>
                            </thead>
                            <tbody>
                            @if(!empty($fish_size_limit))
                                @foreach($fish_time_limit as $row)
                                <tr>
                                    <td>{{ $row->fish }}</td>
                                    <td>{{ $row->data }}</td>
                                </tr>
                                @endforeach
                            @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                <h4 class="mb-2">{{ $row_data->faq_title }}</h4>
                <div class="accordion mb-5" id="faq">
                    @foreach($faq as $row)
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                        <button class="accordion-button p-2" type="button" data-bs-toggle="collapse" data-bs-target="#faq{{ $row->id }}" aria-expanded="true" aria-controls="faq{{ $row->id }}">{{ $row->question }}</button>
                        </h2>
                        <div class="accordion-collapse collapse" id="faq{{ $row->id }}" data-bs-parent="#accordionExample">
                            <div class="accordion-body p-2">{{ $row->answer }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="col-12">
            </div>
        </div>
    </div>
    <!--News One End-->

    <div class="modal show" id="mapModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="mapModalLabel">Map</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div id="map" class="modal-body" style="height:500px;"></div>
            </div>
        </div>
    </div>
@endsection

@section('js_after')


<!-- <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBiGuDOg_5yhHeoRz-7bIkc9T1egi1fA7Q&libraries=places,geocoder"></script>
<script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script> -->
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBiGuDOg_5yhHeoRz-7bIkc9T1egi1fA7Q&libraries=places,geocoder"></script>
<script>(g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})
    ({key: "AIzaSyBiGuDOg_5yhHeoRz-7bIkc9T1egi1fA7Q", v: "weekly"});
</script>
<script>
    $('#sortby').on('change',function(){
        $('#form-sortby').submit();
    });
</script>
<script>
// Get the toggle button and filter container elements
var toggleBtn = document.getElementById('toggleFilterBtn');
var filterContainer = document.getElementById('filterContainer');

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
        placeholder: '@lang('message.target-fish')',
        width: 'resolve', // need to override the changed default
    });

    @foreach($alltargets as $target)
    var targetname = '{{$target->name}}';

    @if(app()->getLocale() == 'en')
    targetname = '{{$target->name_en}}'
    @endif

    var targetOption = new Option(targetname, '{{ $target->id }}');

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
        placeholder: '@lang('message.body-type')',
        width: 'resolve' // need to override the changed default
    });

    @foreach($allwaters as $water)
        var watername = '{{$water->name}}';

        @if(app()->getLocale() == 'en')
        watername = '{{$water->name_en}}'
        @endif

        var waterOption = new Option(watername, '{{ $water->id }}');
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
        placeholder: '@lang('message.fishing-technique')',
        width: 'resolve' // need to override the changed default
    });

    @foreach($allmethods as $method)
    var methodname = '{{$method->name}}';

    @if(app()->getLocale() == 'en')
    methodname = '{{$method->name_en}}'
    @endif

    var methodOption = new Option(methodname, '{{ $method->id }}');
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


<script type="module">
    import { MarkerClusterer } from "https://cdn.skypack.dev/@googlemaps/markerclusterer@2.3.1";
     initializeMap();


     async function initializeMap() {

        var mapStyle = [
          {
            featureType: "poi",
            elementType: "labels",
            stylers: [
              {
                visibility: "off",
              },
            ],
          },
          {
            featureType: "transit",
            elementType: "labels",
            stylers: [
              {
                visibility: "off",
              },
            ],
          },
          {
            featureType: "road",
            elementType: "labels.icon",
            stylers: [
              {
                visibility: "off",
              },
            ],
          },
          {
            featureType: "road",
            elementType: "labels.text",
            stylers: [
              {
                visibility: "on",
              },
            ],
          },
          {
            featureType: "road",
            elementType: "labels.text.fill",
            stylers: [
              {
                visibility: "on",
              },
            ],
          },
          {
            featureType: "administrative.locality",
            elementType: "labels",
            stylers: [
              {
                visibility: "on",
              },
            ],
          },
        ];

    //const { Map } = await google.maps.importLibrary("maps");

    @php
        $lat = isset($guidings[0]) ? $guidings[0]->lat : 51.165691;
        $lng = isset($guidings[0]) ? $guidings[0]->lng : 10.451526;
    @endphp
    const position = { lat: {{request()->get('placeLat') ? request()->get('placeLat') : $lat }} , lng: {{request()->get('placeLng') ? request()->get('placeLng') : $lng }} };
    const { Map, InfoWindow } = await google.maps.importLibrary("maps");
    const { AdvancedMarkerElement, PinElement } = await google.maps.importLibrary("marker");

    //map = new google.maps.Map(document.getElementById("map"), {
    const map = new Map(document.getElementById("map"), {
        zoom: 5,
        center: position,
        styles: mapStyle,
        mapId: "DEMO_MAP_ID",
    });

    // The marker, positioned at Uluru
    const marker = new AdvancedMarkerElement({
        map: map,
        position: position,
    });



    const markers = [];
    const infowindows = [];
    const uniqueCoordinates = [];
    let isDuplicateCoordinate;  

    @if($allGuidings->isEmpty())
        @include('pages.guidings.partials.maps',['guidings' => $otherguidings])
    @else
        @include('pages.guidings.partials.maps',['guidings' => $allGuidings])
    @endif


    function getRandomOffset() {
      // Generate a random value between -0.00005 and 0.00005 (adjust the range as needed)
      return (Math.random() - 0.5) * 0.0080;
    }


    /* 
    // Create the MarkerClusterer
    const markerCluster = new MarkerClusterer(map, markers, {
        imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m',
        maxZoom: 12,
    });
    */
   
    const markerCluster = new MarkerClusterer({ markers, map, mapStyle });
    // Add click event listeners to individual markers inside the cluster
    google.maps.event.addListener(markerCluster, 'clusterclick', function(cluster) {
        // You can control the zoom level here
        // For example, zoom in by 2 levels when clicking on a cluster
        map.setZoom(map.getZoom() + 2);
        map.setCenter(cluster.getCenter());
    });

}



function initialize() {
    var input = document.getElementById('searchPlace');
    var autocomplete = new google.maps.places.Autocomplete(input);
    google.maps.event.addListener(autocomplete, 'place_changed', function () {
        var place = autocomplete.getPlace();
        document.getElementById('placeLat').value = place.geometry.location.lat();
        document.getElementById('placeLng').value = place.geometry.location.lng();
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
        // getLocation();
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
    var geocoder = new google.maps.Geocoder();
    var latlng = new google.maps.LatLng(lat, lng);
    geocoder.geocode({'latLng': latlng}, function (results, status) {
        if (status === google.maps.GeocoderStatus.OK) {
            if (results[0]) {
                document.getElementById('searchPlace').value = results[0].formatted_address;
            } else {
                console.log('No results found');
                return null;
            }
        } else {
            console.log('Geocoder failed due to: ' + status);
            return null;
        }
    });
}

       
</script>


    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>

@endsection
