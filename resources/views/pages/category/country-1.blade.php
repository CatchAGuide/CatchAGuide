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
        background-image: url(../assets/images/map-bg.png);
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

                <div class="mb-3">{!! nl2br($row_data->introduction) !!}</div>

                <h5 class="mb-2">All Region</h5>
                <div id="carousel-regions" class="carousel slide mb-4" data-bs-ride="carousel">
                    <div class="carousel-inner" role="listbox">
                        <div class="carousel-item active">
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-img">
                                        <img src="https://place-hold.it/300x300" class="dimg-fluid" width="300px" alt="...">
                                    </div>
                                    <div class="card-img-overlay">Slide 1</div>
                                </div>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-img">
                                        <img src="https://place-hold.it/300x300" class="dimg-fluid" width="300px" alt="...">
                                    </div>
                                    <div class="card-img-overlay">Slide 2</div>
                                </div>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-img">
                                        <img src="https://place-hold.it/300x300" class="dimg-fluid" width="300px" alt="...">
                                    </div>
                                    <div class="card-img-overlay">Slide 3</div>
                                </div>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-img">
                                        <img src="https://place-hold.it/300x300" class="dimg-fluid" width="300px" alt="...">
                                    </div>
                                    <div class="card-img-overlay">Slide 4</div>
                                </div>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-img">
                                        <img src="https://place-hold.it/300x300" class="dimg-fluid" width="300px" alt="...">
                                    </div>
                                    <div class="card-img-overlay">Slide 5</div>
                                </div>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-img">
                                        <img src="https://place-hold.it/300x300" class="dimg-fluid" width="300px" alt="...">
                                    </div>
                                    <div class="card-img-overlay">Slide 6</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carousel-regions" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carousel-regions" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
                <h5 class="mb-2">All Cities</h5>
                <div id="carousel-cities" class="carousel slide mb-4" data-bs-ride="carousel">
                    <div class="carousel-inner" role="listbox">
                        <div class="carousel-item active">
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-img">
                                        <img src="https://place-hold.it/300x300" class="dimg-fluid" width="300px" alt="...">
                                    </div>
                                    <div class="card-img-overlay">Slide 1</div>
                                </div>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-img">
                                        <img src="https://place-hold.it/300x300" class="dimg-fluid" width="300px" alt="...">
                                    </div>
                                    <div class="card-img-overlay">Slide 2</div>
                                </div>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-img">
                                        <img src="https://place-hold.it/300x300" class="dimg-fluid" width="300px" alt="...">
                                    </div>
                                    <div class="card-img-overlay">Slide 3</div>
                                </div>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-img">
                                        <img src="https://place-hold.it/300x300" class="dimg-fluid" width="300px" alt="...">
                                    </div>
                                    <div class="card-img-overlay">Slide 4</div>
                                </div>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-img">
                                        <img src="https://place-hold.it/300x300" class="dimg-fluid" width="300px" alt="...">
                                    </div>
                                    <div class="card-img-overlay">Slide 5</div>
                                </div>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-img">
                                        <img src="https://place-hold.it/300x300" class="dimg-fluid" width="300px" alt="...">
                                    </div>
                                    <div class="card-img-overlay">Slide 6</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carousel-cities" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carousel-cities" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
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

                                <label for="number_of_guests" class="form-label">Number of Guests</label>
                                <select class="form-select mb-3" id="number_of_guests" name="number_of_guests">
                                    <option>--</option>
                                </select>

                                <label for="target_fish" class="form-label">Target Fish</label>
                                <select class="form-select mb-3" id="target_fish" name="target_fish">
                                    <option>--</option>
                                </select>

                                <label for="water_type" class="form-label">Target Fish</label>
                                <select class="form-select mb-3" id="water_type" name="water_type">
                                    <option>--</option>
                                </select>

                                <label for="fishing_technique" class="form-label">Fishing Technique</label>
                                <select class="form-select" id="fishing_technique" name="fishing_technique">
                                    <option>--</option>
                                </select>
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

                <h4 class="mb-2">Availability of Fish</h4>
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
                    </tbody>
                </table>

                <div class="row">
                    <div class="col-sm-12 col-md-6 col-lg-6">
                        <h4>Fish Size Limit</h4>
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
                                    <td>{{ $row_data->fish }}</td>
                                    <td>{{ $row_data->data }}</td>
                                </tr>
                                @endforeach
                            @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-6 mb-3">
                        <h4>Fish Time Limit</h4>
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
                                    <td>{{ $row_data->fish }}</td>
                                    <td>{{ $row_data->data }}</td>
                                </tr>
                                @endforeach
                            @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                <h4 class="mb-2">FAQ</h4>
                <div class="accordion mb-5" id="faq">
                    @foreach($faq as $row)
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                        <button class="accordion-button p-2" type="button" data-bs-toggle="collapse" data-bs-target="#faq{{ $row_data->id }}" aria-expanded="true" aria-controls="faq{{ $row_data->id }}">{{ $row_data->question }}</button>
                        </h2>
                        <div class="accordion-collapse collapse" id="faq{{ $row_data->id }}" data-bs-parent="#accordionExample">
                            <div class="accordion-body p-2">{{ $row_data->answer }}</div>
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

    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBiGuDOg_5yhHeoRz-7bIkc9T1egi1fA7Q&libraries=places,geocoder"></script>
    <script>(g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})
        ({key: "AIzaSyBiGuDOg_5yhHeoRz-7bIkc9T1egi1fA7Q", v: "weekly"});
    </script>
    <script>
    let items = document.querySelectorAll('.carousel .carousel-item')

    items.forEach((el) => {
        const minPerSlide = 4
        let next = el.nextElementSibling
        for (var i=1; i<minPerSlide; i++) {
            if (!next) {
                // wrap carousel by using first child
                next = items[0]
            }
            let cloneChild = next.cloneNode(true)
            el.appendChild(cloneChild.children[0])
            next = next.nextElementSibling
        }
    });
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

        const position = { lat: 37.7804 , lng: -25.497 };
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

            const location147 = { lat: 37.7804, lng: -25.497 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location147.lat && coordinate.lng === location147.lng;
});

let marker147;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker147 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location147.lat + getRandomOffset(),
            lng: location147.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker147 = new google.maps.marker.AdvancedMarkerElement({
        position: location147,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location147);
}

markers.push(marker147);

const infowindow147 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/147/deep-sea-fishing-in-the-middle-of-the-atlantic-ocean-in-so-miguel-island-portugal"><h5 class="card-title" style="font-size: 14px;">Hochseefischen mitten im Atlantik</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Makrele, Wolfsbarsch, Barrakuda, Bonitos, Zackenbarsch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/147/deep-sea-fishing-in-the-middle-of-the-atlantic-ocean-in-so-miguel-island-portugal" style="padding:3px 7px;">ab 350€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow147);

marker147.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow147.open(map, marker147);
});
const location140 = { lat: 45.35, lng: 14.3167 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location140.lat && coordinate.lng === location140.lng;
});

let marker140;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker140 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location140.lat + getRandomOffset(),
            lng: location140.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker140 = new google.maps.marker.AdvancedMarkerElement({
        position: location140,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location140);
}

markers.push(marker140);

const infowindow140 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/140/classic-fishing-tour-in-croatia-in-volosko-51410-opatija-kroatien"><h5 class="card-title" style="font-size: 14px;">Klassische Angeltour in Kroatien</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Wolfsbarsch, Brassen, Rotbarsch, Makrele
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/140/classic-fishing-tour-in-croatia-in-volosko-51410-opatija-kroatien" style="padding:3px 7px;">ab 750€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow140);

marker140.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow140.open(map, marker140);
});
const location44 = { lat: 56.1612, lng: 15.5869 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location44.lat && coordinate.lng === location44.lng;
});

let marker44;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker44 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location44.lat + getRandomOffset(),
            lng: location44.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker44 = new google.maps.marker.AdvancedMarkerElement({
        position: location44,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location44);
}

markers.push(marker44);

const infowindow44 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/44/hecht-guiding-in-den-schwedischen-schren-in-karlskrona-schweden"><h5 class="card-title" style="font-size: 14px;">Hecht Guiding  in den schwedischen SchÃ¤ren</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Hecht
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/44/hecht-guiding-in-den-schwedischen-schren-in-karlskrona-schweden" style="padding:3px 7px;">ab 528€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow44);

marker44.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow44.open(map, marker44);
});
const location201 = { lat: 37.026, lng: -7.84235 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location201.lat && coordinate.lng === location201.lng;
});

let marker201;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker201 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location201.lat + getRandomOffset(),
            lng: location201.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker201 = new google.maps.marker.AdvancedMarkerElement({
        position: location201,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location201);
}

markers.push(marker201);

const infowindow201 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/201/8-hour-trip-reef-fishing-in-olho-portugal"><h5 class="card-title" style="font-size: 14px;">8-stündiger Ausflug – Riffangeln</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Makrele, Snapper, Rotbarsch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/201/8-hour-trip-reef-fishing-in-olho-portugal" style="padding:3px 7px;">ab 900€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow201);

marker201.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow201.open(map, marker201);
});
const location116 = { lat: 37.5129, lng: -8.47575 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location116.lat && coordinate.lng === location116.lng;
});

let marker116;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker116 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location116.lat + getRandomOffset(),
            lng: location116.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker116 = new google.maps.marker.AdvancedMarkerElement({
        position: location116,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location116);
}

markers.push(marker116);

const infowindow116 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/116/black-bass-fishing-in-portugal"><h5 class="card-title" style="font-size: 14px;">Schwarzbarschangeln</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Schwarzbarsch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/116/black-bass-fishing-in-portugal" style="padding:3px 7px;">ab 150€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow116);

marker116.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow116.open(map, marker116);
});
const location250 = { lat: 37.7487, lng: -25.2391 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location250.lat && coordinate.lng === location250.lng;
});

let marker250;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker250 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location250.lat + getRandomOffset(),
            lng: location250.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker250 = new google.maps.marker.AdvancedMarkerElement({
        position: location250,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location250);
}

markers.push(marker250);

const infowindow250 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/250/family-tour-special-in-9650-povoacao-portugal"><h5 class="card-title" style="font-size: 14px;">Familientour-Special</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Makrele, Wolfsbarsch, Bonitos, Mahi Mahi, Zackenbarsch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/250/family-tour-special-in-9650-povoacao-portugal" style="padding:3px 7px;">ab 275€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow250);

marker250.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow250.open(map, marker250);
});
const location186 = { lat: 54.3233, lng: 10.1228 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location186.lat && coordinate.lng === location186.lng;
});

let marker186;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker186 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location186.lat + getRandomOffset(),
            lng: location186.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker186 = new google.maps.marker.AdvancedMarkerElement({
        position: location186,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location186);
}

markers.push(marker186);

const infowindow186 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/186/meerforellenangeln-mit-der-fliegenrute-in-ostsee"><h5 class="card-title" style="font-size: 14px;">Meerforellen Angeln mit der Fliegenrute an der deutschen und dÃ¤nischen OstseekÃ¼ste</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meerforelle
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/186/meerforellenangeln-mit-der-fliegenrute-in-ostsee" style="padding:3px 7px;">ab 349€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow186);

marker186.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow186.open(map, marker186);
});
const location174 = { lat: 61.2358, lng: 14.0345 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location174.lat && coordinate.lng === location174.lng;
});

let marker174;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker174 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location174.lat + getRandomOffset(),
            lng: location174.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker174 = new google.maps.marker.AdvancedMarkerElement({
        position: location174,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location174);
}

markers.push(marker174);

const infowindow174 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/174/ice-fishing-the-hunt-of-the-arctic-char-in-dalgatan-146-796-30-lvdalen-sverige"><h5 class="card-title" style="font-size: 14px;">Eisfischen, Die Jagd auf den Seesaibling</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            MarÃ¤ne
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/174/ice-fishing-the-hunt-of-the-arctic-char-in-dalgatan-146-796-30-lvdalen-sverige" style="padding:3px 7px;">ab 635€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow174);

marker174.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow174.open(map, marker174);
});
const location120 = { lat: 60.7029, lng: 12.5936 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location120.lat && coordinate.lng === location120.lng;
});

let marker120;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker120 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location120.lat + getRandomOffset(),
            lng: location120.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker120 = new google.maps.marker.AdvancedMarkerElement({
        position: location120,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location120);
}

markers.push(marker120);

const infowindow120 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/120/bellyboat-fishing-in-northern-vrmland-whole-day-in-680-61-bograngen-zweden"><h5 class="card-title" style="font-size: 14px;">Bellyboat-Angeln im nördlichen Värmland – ganztägig</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Hecht, Barsch, Regenbogenforelle
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/120/bellyboat-fishing-in-northern-vrmland-whole-day-in-680-61-bograngen-zweden" style="padding:3px 7px;">ab 250€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow120);

marker120.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow120.open(map, marker120);
});
const location146 = { lat: 51.691, lng: 4.21268 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location146.lat && coordinate.lng === location146.lng;
});

let marker146;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker146 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location146.lat + getRandomOffset(),
            lng: location146.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker146 = new google.maps.marker.AdvancedMarkerElement({
        position: location146,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location146);
}

markers.push(marker146);

const infowindow146 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/146/zanderangeln-vom-profi-boot-in-holland-in-3255-oude-tonge-niederlande"><h5 class="card-title" style="font-size: 14px;">Zanderangeln vom Profi-Boot in Holland</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Zander, Hecht
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss, Kanal, See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/146/zanderangeln-vom-profi-boot-in-holland-in-3255-oude-tonge-niederlande" style="padding:3px 7px;">ab 599€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow146);

marker146.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow146.open(map, marker146);
});
const location233 = { lat: 52.52, lng: 13.405 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location233.lat && coordinate.lng === location233.lng;
});

let marker233;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker233 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location233.lat + getRandomOffset(),
            lng: location233.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker233 = new google.maps.marker.AdvancedMarkerElement({
        position: location233,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location233);
}

markers.push(marker233);

const infowindow233 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/233/zweihand-fliegenfischen-anfnger-kurs-in-berlin-in-berlin-deutschland"><h5 class="card-title" style="font-size: 14px;">Zweihand Fliegenfischen - AnfÃ¤nger Kurs in Berlin</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs an Land
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/233/zweihand-fliegenfischen-anfnger-kurs-in-berlin-in-berlin-deutschland" style="padding:3px 7px;">ab 349€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow233);

marker233.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow233.open(map, marker233);
});
const location145 = { lat: 37.6415, lng: -7.66067 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location145.lat && coordinate.lng === location145.lng;
});

let marker145;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker145 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location145.lat + getRandomOffset(),
            lng: location145.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker145 = new google.maps.marker.AdvancedMarkerElement({
        position: location145,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location145);
}

markers.push(marker145);

const infowindow145 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/145/angeln-auf-schwarzbarsch-andere-raubfische-in-portugal-in-7750-mrtola-portugal"><h5 class="card-title" style="font-size: 14px;">Angeln auf Schwarzbarsch &amp; andere Raubfische in Portugal</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Schwarzbarsch, Zander, Wels, Wolfsbarsch, MeerÃ¤sche, Seewolf
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss, See, Talsperre, Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/145/angeln-auf-schwarzbarsch-andere-raubfische-in-portugal-in-7750-mrtola-portugal" style="padding:3px 7px;">ab 130€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow145);

marker145.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow145.open(map, marker145);
});
const location141 = { lat: 45.35, lng: 14.3167 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location141.lat && coordinate.lng === location141.lng;
});

let marker141;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker141 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location141.lat + getRandomOffset(),
            lng: location141.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker141 = new google.maps.marker.AdvancedMarkerElement({
        position: location141,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location141);
}

markers.push(marker141);

const infowindow141 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/141/sunrise-fishing-tour-in-croatia-in-volosko-51410-opatija-kroatien"><h5 class="card-title" style="font-size: 14px;">Angeltour bei Sonnenaufgang in Kroatien</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Thunfisch, Wolfsbarsch, Rotbarsch, Makrele, Giebel, Dorade
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/141/sunrise-fishing-tour-in-croatia-in-volosko-51410-opatija-kroatien" style="padding:3px 7px;">ab 375€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow141);

marker141.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow141.open(map, marker141);
});
const location43 = { lat: 56.5247, lng: 14.9785 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location43.lat && coordinate.lng === location43.lng;
});

let marker43;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker43 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location43.lat + getRandomOffset(),
            lng: location43.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker43 = new google.maps.marker.AdvancedMarkerElement({
        position: location43,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location43);
}

markers.push(marker43);

const infowindow43 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/43/zander-barsch-guiding-am-tiken-in-tingsryd-schweden"><h5 class="card-title" style="font-size: 14px;">Zander/- Barsch Guiding am Tiken</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Hecht, Zander
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/43/zander-barsch-guiding-am-tiken-in-tingsryd-schweden" style="padding:3px 7px;">ab 418€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow43);

marker43.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow43.open(map, marker43);
});
const location41 = { lat: 56.5247, lng: 14.9785 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location41.lat && coordinate.lng === location41.lng;
});

let marker41;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker41 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location41.lat + getRandomOffset(),
            lng: location41.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker41 = new google.maps.marker.AdvancedMarkerElement({
        position: location41,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location41);
}

markers.push(marker41);

const infowindow41 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/41/zander-hecht-guiding-am-snen-in-tingsryd-schweden"><h5 class="card-title" style="font-size: 14px;">Zander/- Hecht Guiding am Ösnen</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Hecht, Zander
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/41/zander-hecht-guiding-am-snen-in-tingsryd-schweden" style="padding:3px 7px;">ab 478€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow41);

marker41.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow41.open(map, marker41);
});
const location99 = { lat: 63.5933, lng: 19.3358 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location99.lat && coordinate.lng === location99.lng;
});

let marker99;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker99 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location99.lat + getRandomOffset(),
            lng: location99.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker99 = new google.maps.marker.AdvancedMarkerElement({
        position: location99,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location99);
}

markers.push(marker99);

const infowindow99 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/99/lachs-und-meerforellenangeln-vom-raftingboot-im-wilden-schwedischen-fluss-in-hyngelsble-113-nordmaling-schweden"><h5 class="card-title" style="font-size: 14px;">Lachs und Meerforellenangeln vom Raftingboot im wilden schwedischen Fluss</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Ã„sche, Bachforelle, Lachs, Meerforelle
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/99/lachs-und-meerforellenangeln-vom-raftingboot-im-wilden-schwedischen-fluss-in-hyngelsble-113-nordmaling-schweden" style="padding:3px 7px;">ab 450€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow99);

marker99.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow99.open(map, marker99);
});
const location179 = { lat: 43.5141, lng: 16.1077 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location179.lat && coordinate.lng === location179.lng;
});

let marker179;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker179 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location179.lat + getRandomOffset(),
            lng: location179.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker179 = new google.maps.marker.AdvancedMarkerElement({
        position: location179,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location179);
}

markers.push(marker179);

const infowindow179 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/179/trolling-jigging-fishing-tour-in-croatia-in-marina-kroatien"><h5 class="card-title" style="font-size: 14px;">Trolling- und Jigging-Angeltour in Kroatien</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Rotbarsch, Mahi Mahi, Makrele, Amberjack, Blaufisch, Snapper
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/179/trolling-jigging-fishing-tour-in-croatia-in-marina-kroatien" style="padding:3px 7px;">ab 550€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow179);

marker179.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow179.open(map, marker179);
});
const location72 = { lat: 51.2541, lng: 8.16961 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location72.lat && coordinate.lng === location72.lng;
});

let marker72;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker72 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location72.lat + getRandomOffset(),
            lng: location72.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker72 = new google.maps.marker.AdvancedMarkerElement({
        position: location72,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location72);
}

markers.push(marker72);

const infowindow72 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/72/bachforellen-angeln-am-fluss-in-sauerland-deutschland"><h5 class="card-title" style="font-size: 14px;">Bachforellen Angeln am Fluss</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Bachforelle, Regenbogenforelle, Barbe
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss, Bach
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/72/bachforellen-angeln-am-fluss-in-sauerland-deutschland" style="padding:3px 7px;">ab 182€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow72);

marker72.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow72.open(map, marker72);
});
const location164 = { lat: 54.1876, lng: 10.754 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location164.lat && coordinate.lng === location164.lng;
});

let marker164;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker164 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location164.lat + getRandomOffset(),
            lng: location164.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker164 = new google.maps.marker.AdvancedMarkerElement({
        position: location164,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location164);
}

markers.push(marker164);

const infowindow164 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/164/meerforelle-mit-blinker-wobbler-und-fliege-in-ostholstein-in-ostholstein-23-deutschland"><h5 class="card-title" style="font-size: 14px;">Meerforelle mit Blinker, Wobbler und Fliege in Ostholstein</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meerforelle, Hornhecht, Makrele
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/164/meerforelle-mit-blinker-wobbler-und-fliege-in-ostholstein-in-ostholstein-23-deutschland" style="padding:3px 7px;">ab 139€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow164);

marker164.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow164.open(map, marker164);
});
const location264 = { lat: 41.372, lng: 0.300753 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location264.lat && coordinate.lng === location264.lng;
});

let marker264;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker264 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location264.lat + getRandomOffset(),
            lng: location264.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker264 = new google.maps.marker.AdvancedMarkerElement({
        position: location264,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location264);
}

markers.push(marker264);

const infowindow264 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/264/spinnfishing-for-zander-blackbass-catfish-trout-perch-with-floattube-or-from-shore-in-50170-mequinenza-saragossa-spanien"><h5 class="card-title" style="font-size: 14px;">Spinnfischen auf Zander, Schwarzbarsch, Wels, Forelle, Barsch mit Floattube oder vom Ufer aus</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Schwarzbarsch, Seewolf, Regenbogenforelle, Barsch, Zander
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Talsperre, Fluss, See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/264/spinnfishing-for-zander-blackbass-catfish-trout-perch-with-floattube-or-from-shore-in-50170-mequinenza-saragossa-spanien" style="padding:3px 7px;">ab 300€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow264);

marker264.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow264.open(map, marker264);
});
const location212 = { lat: 39.3302, lng: 3.16855 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location212.lat && coordinate.lng === location212.lng;
});

let marker212;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker212 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location212.lat + getRandomOffset(),
            lng: location212.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker212 = new google.maps.marker.AdvancedMarkerElement({
        position: location212,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location212);
}

markers.push(marker212);

const infowindow212 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/212/nightfishing-12-hrs-in-07659-cala-figuera-balearen-spanien"><h5 class="card-title" style="font-size: 14px;">Nachtfischen 12 Std</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Makrele, Thunfisch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/212/nightfishing-12-hrs-in-07659-cala-figuera-balearen-spanien" style="padding:3px 7px;">ab 2400€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow212);

marker212.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow212.open(map, marker212);
});
const location241 = { lat: 51.1045, lng: 13.2017 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location241.lat && coordinate.lng === location241.lng;
});

let marker241;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker241 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location241.lat + getRandomOffset(),
            lng: location241.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker241 = new google.maps.marker.AdvancedMarkerElement({
        position: location241,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location241);
}

markers.push(marker241);

const infowindow241 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/241/fliegenfischen-lernen-in-sachsen-einzelkurs-individuelles-lernen-in-sachsen-deutschland"><h5 class="card-title" style="font-size: 14px;">Fliegenfischen lernen in Sachsen: Einzelkurs/Individuelles lernen</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs an Land
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/241/fliegenfischen-lernen-in-sachsen-einzelkurs-individuelles-lernen-in-sachsen-deutschland" style="padding:3px 7px;">ab 349€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow241);

marker241.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow241.open(map, marker241);
});
const location202 = { lat: 37.026, lng: -7.84235 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location202.lat && coordinate.lng === location202.lng;
});

let marker202;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker202 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location202.lat + getRandomOffset(),
            lng: location202.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker202 = new google.maps.marker.AdvancedMarkerElement({
        position: location202,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location202);
}

markers.push(marker202);

const infowindow202 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/202/8-hour-trip-giant-bluefin-tuna-in-olho-portugal"><h5 class="card-title" style="font-size: 14px;">8-stündiger Ausflug – Riesiger Blauflossen-Thunfisch</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Thunfisch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/202/8-hour-trip-giant-bluefin-tuna-in-olho-portugal" style="padding:3px 7px;">ab 1200€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow202);

marker202.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow202.open(map, marker202);
});
const location181 = { lat: 43.5141, lng: 16.1077 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location181.lat && coordinate.lng === location181.lng;
});

let marker181;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker181 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location181.lat + getRandomOffset(),
            lng: location181.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker181 = new google.maps.marker.AdvancedMarkerElement({
        position: location181,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location181);
}

markers.push(marker181);

const infowindow181 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/181/big-game-fishing-off-the-coast-of-marina-croatia-in-marina-kroatien"><h5 class="card-title" style="font-size: 14px;">Hochseefischen vor der Küste von Marina, Kroatien</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Thunfisch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/181/big-game-fishing-off-the-coast-of-marina-croatia-in-marina-kroatien" style="padding:3px 7px;">ab 700€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow181);

marker181.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow181.open(map, marker181);
});
const location94 = { lat: 49.3173, lng: 8.44122 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location94.lat && coordinate.lng === location94.lng;
});

let marker94;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker94 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location94.lat + getRandomOffset(),
            lng: location94.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker94 = new google.maps.marker.AdvancedMarkerElement({
        position: location94,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location94);
}

markers.push(marker94);

const infowindow94 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/94/spinnfischen-am-rhein-auf-barsch-und-zander-in-speyer-deutschland"><h5 class="card-title" style="font-size: 14px;">Spinnfischen am Rhein auf Barsch und Zander</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Zander
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/94/spinnfischen-am-rhein-auf-barsch-und-zander-in-speyer-deutschland" style="padding:3px 7px;">ab 180€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow94);

marker94.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow94.open(map, marker94);
});
const location152 = { lat: 44.2822, lng: 15.3478 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location152.lat && coordinate.lng === location152.lng;
});

let marker152;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker152 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location152.lat + getRandomOffset(),
            lng: location152.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker152 = new google.maps.marker.AdvancedMarkerElement({
        position: location152,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location152);
}

markers.push(marker152);

const infowindow152 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/152/sunset-fishing-in-23248-raanac-hrvatska"><h5 class="card-title" style="font-size: 14px;">Angeln bei Sonnenuntergang in Kroatien</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Brassen, Giebel, Wolfsbarsch, Makrele
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/152/sunset-fishing-in-23248-raanac-hrvatska" style="padding:3px 7px;">ab 200€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow152);

marker152.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow152.open(map, marker152);
});
const location157 = { lat: 53.2194, lng: 6.5665 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location157.lat && coordinate.lng === location157.lng;
});

let marker157;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker157 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location157.lat + getRandomOffset(),
            lng: location157.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker157 = new google.maps.marker.AdvancedMarkerElement({
        position: location157,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location157);
}

markers.push(marker157);

const infowindow157 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/157/zander-perch-fishing-in-nordholland-groningen-in-groningen-niederlande"><h5 class="card-title" style="font-size: 14px;">Zander- und Barschangeln in Nordholland (Groningen)</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Zander, Barsch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Hafen, Fluss, Kanal, See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/157/zander-perch-fishing-in-nordholland-groningen-in-groningen-niederlande" style="padding:3px 7px;">ab 180€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow157);

marker157.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow157.open(map, marker157);
});
const location208 = { lat: 51.4332, lng: 7.66159 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location208.lat && coordinate.lng === location208.lng;
});

let marker208;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker208 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location208.lat + getRandomOffset(),
            lng: location208.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker208 = new google.maps.marker.AdvancedMarkerElement({
        position: location208,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location208);
}

markers.push(marker208);

const infowindow208 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/208/zander-barsch-am-rhein-nrw-ganztags-8h-in-nordrhein-westfalen-deutschland"><h5 class="card-title" style="font-size: 14px;">Zander und Barsch am Rhein (NRW) - Ganztags (8h)</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Zander
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/208/zander-barsch-am-rhein-nrw-ganztags-8h-in-nordrhein-westfalen-deutschland" style="padding:3px 7px;">ab 360€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow208);

marker208.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow208.open(map, marker208);
});
const location131 = { lat: 41.372, lng: 0.300753 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location131.lat && coordinate.lng === location131.lng;
});

let marker131;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker131 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location131.lat + getRandomOffset(),
            lng: location131.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker131 = new google.maps.marker.AdvancedMarkerElement({
        position: location131,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location131);
}

markers.push(marker131);

const infowindow131 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/131/karpfenangeln-an-der-ebro-staustufe-auf-wildkarpfen-in-50170-mequinenza-saragossa-spanien"><h5 class="card-title" style="font-size: 14px;">Karpfenangeln an der Ebro Staustufe auf Wildkarpfen</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Karpfen
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Talsperre
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/131/karpfenangeln-an-der-ebro-staustufe-auf-wildkarpfen-in-50170-mequinenza-saragossa-spanien" style="padding:3px 7px;">ab 200€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow131);

marker131.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow131.open(map, marker131);
});
const location197 = { lat: 49.7692, lng: 11.3384 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location197.lat && coordinate.lng === location197.lng;
});

let marker197;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker197 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location197.lat + getRandomOffset(),
            lng: location197.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker197 = new google.maps.marker.AdvancedMarkerElement({
        position: location197,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location197);
}

markers.push(marker197);

const infowindow197 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/197/fliegenfischen-in-der-frnkischen-schweiz-in-91327-gssweinstein-deutschland"><h5 class="card-title" style="font-size: 14px;">Fliegenfischen in der FrÃ¤nkischen-Schweiz</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Ã„sche, Bachforelle
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Bach, Fluss
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/197/fliegenfischen-in-der-frnkischen-schweiz-in-91327-gssweinstein-deutschland" style="padding:3px 7px;">ab 349€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow197);

marker197.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow197.open(map, marker197);
});
const location254 = { lat: 32.7187, lng: -17.1723 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location254.lat && coordinate.lng === location254.lng;
});

let marker254;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker254 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location254.lat + getRandomOffset(),
            lng: location254.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker254 = new google.maps.marker.AdvancedMarkerElement({
        position: location254,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location254);
}

markers.push(marker254);

const infowindow254 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/254/mahi-mahi-gilthead-fishing-on-madeira-in-9370-calheta-portugal"><h5 class="card-title" style="font-size: 14px;">Angeln auf Mahi Mahi und Gilthead auf Madeira</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Dorade
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/254/mahi-mahi-gilthead-fishing-on-madeira-in-9370-calheta-portugal" style="padding:3px 7px;">ab 1300€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow254);

marker254.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow254.open(map, marker254);
});
const location244 = { lat: 52.52, lng: 13.405 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location244.lat && coordinate.lng === location244.lng;
});

let marker244;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker244 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location244.lat + getRandomOffset(),
            lng: location244.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker244 = new google.maps.marker.AdvancedMarkerElement({
        position: location244,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location244);
}

markers.push(marker244);

const infowindow244 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/244/fliegenfischen-lernen-in-berlin-einzelkurs-individuelles-lernen-in-berlin-deutschland"><h5 class="card-title" style="font-size: 14px;">Fliegenfischen lernen in Berlin: Einzelkurs/Individuelles lernen</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs an Land
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/244/fliegenfischen-lernen-in-berlin-einzelkurs-individuelles-lernen-in-berlin-deutschland" style="padding:3px 7px;">ab 349€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow244);

marker244.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow244.open(map, marker244);
});
const location196 = { lat: 51.011, lng: 10.8453 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location196.lat && coordinate.lng === location196.lng;
});

let marker196;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker196 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location196.lat + getRandomOffset(),
            lng: location196.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker196 = new google.maps.marker.AdvancedMarkerElement({
        position: location196,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location196);
}

markers.push(marker196);

const infowindow196 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/196/fliegenfischen-angeltouren-in-thringen-in-thringen-deutschland"><h5 class="card-title" style="font-size: 14px;">Fliegenfischen - Angeltouren in ThÃ¼ringen</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Ã„sche, Bachforelle
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Bach, Fluss, See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/196/fliegenfischen-angeltouren-in-thringen-in-thringen-deutschland" style="padding:3px 7px;">ab 349€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow196);

marker196.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow196.open(map, marker196);
});
const location134 = { lat: 49.3173, lng: 8.44122 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location134.lat && coordinate.lng === location134.lng;
});

let marker134;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker134 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location134.lat + getRandomOffset(),
            lng: location134.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker134 = new google.maps.marker.AdvancedMarkerElement({
        position: location134,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location134);
}

markers.push(marker134);

const infowindow134 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/134/marcos-angeltouren-am-rhein-altrhein-in-67346-speyer-deutschland"><h5 class="card-title" style="font-size: 14px;">Marcos Angeltouren am Rhein/Altrhein</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Hecht, Rapfen, Zander, Barsch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss, See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/134/marcos-angeltouren-am-rhein-altrhein-in-67346-speyer-deutschland" style="padding:3px 7px;">ab 250€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow134);

marker134.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow134.open(map, marker134);
});
const location160 = { lat: 51.7348, lng: 4.43821 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location160.lat && coordinate.lng === location160.lng;
});

let marker160;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker160 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location160.lat + getRandomOffset(),
            lng: location160.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker160 = new google.maps.marker.AdvancedMarkerElement({
        position: location160,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location160);
}

markers.push(marker160);

const infowindow160 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/160/angeln-in-holland-mit-bernachtung-3-tgige-angeltour-in-numansdorp-niederlande"><h5 class="card-title" style="font-size: 14px;">Angeln in Holland mit Ãœbernachtung (3-tÃ¤gige Angeltour)</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Zander, Hecht
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss, See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/160/angeln-in-holland-mit-bernachtung-3-tgige-angeltour-in-numansdorp-niederlande" style="padding:3px 7px;">ab 1699€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow160);

marker160.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow160.open(map, marker160);
});
const location103 = { lat: 59.2753, lng: 15.2134 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location103.lat && coordinate.lng === location103.lng;
});

let marker103;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker103 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location103.lat + getRandomOffset(),
            lng: location103.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker103 = new google.maps.marker.AdvancedMarkerElement({
        position: location103,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location103);
}

markers.push(marker103);

const infowindow103 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/103/ein-entspannter-tag-auf-dem-see-raubfischguiding-in-schweden-in-rebro-schweden"><h5 class="card-title" style="font-size: 14px;">Ein entspannter Tag auf dem See - Raubfischguiding in Schweden</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Hecht, Zander
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/103/ein-entspannter-tag-auf-dem-see-raubfischguiding-in-schweden-in-rebro-schweden" style="padding:3px 7px;">ab 450€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow103);

marker103.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow103.open(map, marker103);
});
const location261 = { lat: 47.4049, lng: 19.1096 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location261.lat && coordinate.lng === location261.lng;
});

let marker261;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker261 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location261.lat + getRandomOffset(),
            lng: location261.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker261 = new google.maps.marker.AdvancedMarkerElement({
        position: location261,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location261);
}

markers.push(marker261);

const infowindow261 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/261/all-day-tour-in-budapest-in-budapest-molnr-sziget-magyarorszg"><h5 class="card-title" style="font-size: 14px;">Ganztägige Angeltour in Budapest</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Barbe, Brassen, Karpfen, Wels, Seewolf, DÃ¶bel, Hecht, Rotauge, Rotfeder, Zander
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss, Kanal, Talsperre, Hafen
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/261/all-day-tour-in-budapest-in-budapest-molnr-sziget-magyarorszg" style="padding:3px 7px;">ab 175€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow261);

marker261.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow261.open(map, marker261);
});
const location205 = { lat: 37.3166, lng: -8.79926 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location205.lat && coordinate.lng === location205.lng;
});

let marker205;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker205 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location205.lat + getRandomOffset(),
            lng: location205.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker205 = new google.maps.marker.AdvancedMarkerElement({
        position: location205,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location205);
}

markers.push(marker205);

const infowindow205 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/205/land-based-rock-fishing-adventures-at-the-vicentine-natural-park-in-8670-aljezur-portugal"><h5 class="card-title" style="font-size: 14px;">Abenteuer beim Felsangeln an Land im Naturpark Vicentine</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Wolfsbarsch, Dorade, Brassen
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/205/land-based-rock-fishing-adventures-at-the-vicentine-natural-park-in-8670-aljezur-portugal" style="padding:3px 7px;">ab 89€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow205);

marker205.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow205.open(map, marker205);
});
const location71 = { lat: 51.779, lng: 9.37878 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location71.lat && coordinate.lng === location71.lng;
});

let marker71;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker71 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location71.lat + getRandomOffset(),
            lng: location71.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker71 = new google.maps.marker.AdvancedMarkerElement({
        position: location71,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location71);
}

markers.push(marker71);

const infowindow71 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/71/spinnfischkurs-fr-anfnger-in-hxter-deutschland"><h5 class="card-title" style="font-size: 14px;">Spinning-Kurs für Anfänger</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Bachforelle, Barsch, Hecht, Zander
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss, See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/71/spinnfischkurs-fr-anfnger-in-hxter-deutschland" style="padding:3px 7px;">ab 80€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow71);

marker71.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow71.open(map, marker71);
});
const location92 = { lat: 42.1318, lng: -0.407806 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location92.lat && coordinate.lng === location92.lng;
});

let marker92;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker92 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location92.lat + getRandomOffset(),
            lng: location92.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker92 = new google.maps.marker.AdvancedMarkerElement({
        position: location92,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location92);
}

markers.push(marker92);

const infowindow92 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/92/river-weekend-3-tage-in-spanien"><h5 class="card-title" style="font-size: 14px;">ABENTEUER TRIP (Bellyboat)</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Saibling
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Talsperre, See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/92/river-weekend-3-tage-in-spanien" style="padding:3px 7px;">ab 250€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow92);

marker92.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow92.open(map, marker92);
});
const location129 = { lat: 41.3692, lng: 0.274258 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location129.lat && coordinate.lng === location129.lng;
});

let marker129;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker129 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location129.lat + getRandomOffset(),
            lng: location129.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker129 = new google.maps.marker.AdvancedMarkerElement({
        position: location129,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location129);
}

markers.push(marker129);

const infowindow129 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/129/klopfen-auf-wels-am-ebro-in-50170-mequinenza-saragossa-spanien"><h5 class="card-title" style="font-size: 14px;">Klopfen auf Wels am Ebro</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Wels
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Talsperre, Fluss
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/129/klopfen-auf-wels-am-ebro-in-50170-mequinenza-saragossa-spanien" style="padding:3px 7px;">ab 250€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow129);

marker129.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow129.open(map, marker129);
});
const location142 = { lat: 45.35, lng: 14.3167 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location142.lat && coordinate.lng === location142.lng;
});

let marker142;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker142 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location142.lat + getRandomOffset(),
            lng: location142.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker142 = new google.maps.marker.AdvancedMarkerElement({
        position: location142,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location142);
}

markers.push(marker142);

const infowindow142 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/142/sunset-fishing-tour-at-the-adrian-sea-in-volosko-51410-opatija-kroatien"><h5 class="card-title" style="font-size: 14px;">Angeltour bei Sonnenuntergang am Adrianmeer</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Wolfsbarsch, Rotbarsch, Mahi Mahi, Thunfisch, Dorade, Makrele
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/142/sunset-fishing-tour-at-the-adrian-sea-in-volosko-51410-opatija-kroatien" style="padding:3px 7px;">ab 375€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow142);

marker142.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow142.open(map, marker142);
});
const location138 = { lat: 44.1182, lng: 15.2528 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location138.lat && coordinate.lng === location138.lng;
});

let marker138;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker138 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location138.lat + getRandomOffset(),
            lng: location138.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker138 = new google.maps.marker.AdvancedMarkerElement({
        position: location138,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location138);
}

markers.push(marker138);

const infowindow138 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/138/zadar-sunset-fishing-in-23000-zadar-hrvatska"><h5 class="card-title" style="font-size: 14px;">Angeln bei Sonnenuntergang in Zadar</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Makrele, Mahi Mahi, Dorsch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/138/zadar-sunset-fishing-in-23000-zadar-hrvatska" style="padding:3px 7px;">ab 300€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow138);

marker138.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow138.open(map, marker138);
});
const location102 = { lat: 59.2753, lng: 15.2134 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location102.lat && coordinate.lng === location102.lng;
});

let marker102;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker102 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location102.lat + getRandomOffset(),
            lng: location102.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker102 = new google.maps.marker.AdvancedMarkerElement({
        position: location102,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location102);
}

markers.push(marker102);

const infowindow102 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/102/barschangeln-in-schwedischen-waldseen-in-rebro-schweden"><h5 class="card-title" style="font-size: 14px;">Barschangeln in schwedischen Waldseen</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/102/barschangeln-in-schwedischen-waldseen-in-rebro-schweden" style="padding:3px 7px;">ab 450€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow102);

marker102.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow102.open(map, marker102);
});
const location258 = { lat: 54.5144, lng: 13.1672 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location258.lat && coordinate.lng === location258.lng;
});

let marker258;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker258 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location258.lat + getRandomOffset(),
            lng: location258.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker258 = new google.maps.marker.AdvancedMarkerElement({
        position: location258,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location258);
}

markers.push(marker258);

const infowindow258 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/258/plattfischangeln-an-der-ostsee-in-schaprode-deutschland"><h5 class="card-title" style="font-size: 14px;">Plattfischangeln an der Ostsee</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Scholle, Flunder, Kliesche, Steinbutt
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/258/plattfischangeln-an-der-ostsee-in-schaprode-deutschland" style="padding:3px 7px;">ab 399€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow258);

marker258.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow258.open(map, marker258);
});
const location192 = { lat: 51.4332, lng: 7.66159 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location192.lat && coordinate.lng === location192.lng;
});

let marker192;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker192 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location192.lat + getRandomOffset(),
            lng: location192.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker192 = new google.maps.marker.AdvancedMarkerElement({
        position: location192,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location192);
}

markers.push(marker192);

const infowindow192 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/192/fliegenfischen-im-sauerland-und-der-eifel-in-nordrhein-westfalen-deutschland"><h5 class="card-title" style="font-size: 14px;">Fliegenfischen im Sauerland, der Eifel und GroÃŸraum NRW</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Bachforelle, Regenbogenforelle, Hecht, Barbe, Rapfen, Zander, Ã„sche
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Bach, See, Fluss
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/192/fliegenfischen-im-sauerland-und-der-eifel-in-nordrhein-westfalen-deutschland" style="padding:3px 7px;">ab 349€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow192);

marker192.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow192.open(map, marker192);
});
const location214 = { lat: 39.3302, lng: 3.16855 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location214.lat && coordinate.lng === location214.lng;
});

let marker214;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker214 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location214.lat + getRandomOffset(),
            lng: location214.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker214 = new google.maps.marker.AdvancedMarkerElement({
        position: location214,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location214);
}

markers.push(marker214);

const infowindow214 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/214/mahi-fishing-spinfishing-and-trolling-in-07659-cala-figuera-balearen-spanien"><h5 class="card-title" style="font-size: 14px;">Mahi-Angeln, Spinnfischen und Schleppangeln</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Mahi Mahi, Thunfisch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/214/mahi-fishing-spinfishing-and-trolling-in-07659-cala-figuera-balearen-spanien" style="padding:3px 7px;">ab 1700€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow214);

marker214.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow214.open(map, marker214);
});
const location257 = { lat: 54.5144, lng: 13.1672 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location257.lat && coordinate.lng === location257.lng;
});

let marker257;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker257 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location257.lat + getRandomOffset(),
            lng: location257.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker257 = new google.maps.marker.AdvancedMarkerElement({
        position: location257,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location257);
}

markers.push(marker257);

const infowindow257 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/257/spinnfischen-auf-meerforelle-in-schaprode-deutschland"><h5 class="card-title" style="font-size: 14px;">Spinnfischen auf Meerforelle</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meerforelle
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/257/spinnfischen-auf-meerforelle-in-schaprode-deutschland" style="padding:3px 7px;">ab 399€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow257);

marker257.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow257.open(map, marker257);
});
const location195 = { lat: 50.6521, lng: 9.16244 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location195.lat && coordinate.lng === location195.lng;
});

let marker195;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker195 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location195.lat + getRandomOffset(),
            lng: location195.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker195 = new google.maps.marker.AdvancedMarkerElement({
        position: location195,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location195);
}

markers.push(marker195);

const infowindow195 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/195/gefhrten-angeltour-mit-der-fliegenrute-in-hessen-deutschland"><h5 class="card-title" style="font-size: 14px;">GefÃ¼hrten Angeltour mit der Fliegenrute</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Ã„sche, Bachforelle, Barbe, Hecht, Zander, Wels, Rapfen
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Bach, Fluss, See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/195/gefhrten-angeltour-mit-der-fliegenrute-in-hessen-deutschland" style="padding:3px 7px;">ab 349€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow195);

marker195.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow195.open(map, marker195);
});
const location114 = { lat: 52.1326, lng: 5.29127 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location114.lat && coordinate.lng === location114.lng;
});

let marker114;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker114 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location114.lat + getRandomOffset(),
            lng: location114.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker114 = new google.maps.marker.AdvancedMarkerElement({
        position: location114,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location114);
}

markers.push(marker114);

const infowindow114 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/114/xxl-hecht-massive-zander-in-niederlande"><h5 class="card-title" style="font-size: 14px;">XXL Hecht/Massiver Zander</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Hecht, Zander
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Hafen, Fluss, See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/114/xxl-hecht-massive-zander-in-niederlande" style="padding:3px 7px;">ab 400€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow114);

marker114.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow114.open(map, marker114);
});
const location166 = { lat: 32.7318, lng: -16.7913 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location166.lat && coordinate.lng === location166.lng;
});

let marker166;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker166 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location166.lat + getRandomOffset(),
            lng: location166.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker166 = new google.maps.marker.AdvancedMarkerElement({
        position: location166,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location166);
}

markers.push(marker166);

const infowindow166 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/166/pesca-de-fundo-in-machico-portugal"><h5 class="card-title" style="font-size: 14px;">Grundangeln</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Thunfisch, Rotbarsch, Giebel
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/166/pesca-de-fundo-in-machico-portugal" style="padding:3px 7px;">ab 480€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow166);

marker166.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow166.open(map, marker166);
});
const location169 = { lat: 32.7607, lng: -16.9595 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location169.lat && coordinate.lng === location169.lng;
});

let marker169;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker169 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location169.lat + getRandomOffset(),
            lng: location169.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker169 = new google.maps.marker.AdvancedMarkerElement({
        position: location169,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location169);
}

markers.push(marker169);

const infowindow169 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/169/big-game-fishing-in-madeira-portugal"><h5 class="card-title" style="font-size: 14px;">Hochseefischen vor der Küste von Madeira, Portugal</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Thunfisch, Mahi Mahi, Marlin
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/169/big-game-fishing-in-madeira-portugal" style="padding:3px 7px;">ab 1150€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow169);

marker169.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow169.open(map, marker169);
});
const location76 = { lat: 52.1326, lng: 5.29127 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location76.lat && coordinate.lng === location76.lng;
});

let marker76;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker76 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location76.lat + getRandomOffset(),
            lng: location76.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker76 = new google.maps.marker.AdvancedMarkerElement({
        position: location76,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location76);
}

markers.push(marker76);

const infowindow76 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/76/dick-barsch-angeln-in-holland-in-niederlande"><h5 class="card-title" style="font-size: 14px;">Dick-Barsch Angeln in Holland</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss, See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/76/dick-barsch-angeln-in-holland-in-niederlande" style="padding:3px 7px;">ab 350€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow76);

marker76.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow76.open(map, marker76);
});
const location176 = { lat: 61.2358, lng: 14.0345 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location176.lat && coordinate.lng === location176.lng;
});

let marker176;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker176 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location176.lat + getRandomOffset(),
            lng: location176.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker176 = new google.maps.marker.AdvancedMarkerElement({
        position: location176,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location176);
}

markers.push(marker176);

const infowindow176 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/176/3-day-grand-slam-fly-fishing-package-in-dalgatan-146-796-30-lvdalen-sverige"><h5 class="card-title" style="font-size: 14px;">3-tägiges „Grand Slam“-Fliegenfischerpaket</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Ã„sche, Hecht, MarÃ¤ne, Bachforelle
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Bach, Fluss, See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/176/3-day-grand-slam-fly-fishing-package-in-dalgatan-146-796-30-lvdalen-sverige" style="padding:3px 7px;">ab 2090€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow176);

marker176.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow176.open(map, marker176);
});
const location175 = { lat: 61.2358, lng: 14.0345 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location175.lat && coordinate.lng === location175.lng;
});

let marker175;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker175 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location175.lat + getRandomOffset(),
            lng: location175.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker175 = new google.maps.marker.AdvancedMarkerElement({
        position: location175,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location175);
}

markers.push(marker175);

const infowindow175 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/175/guided-pike-fly-fishing-adventure-in-dalgatan-146-796-30-lvdalen-sverige"><h5 class="card-title" style="font-size: 14px;">Geführtes Hecht-Fliegenfischen-Abenteuer</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Hecht
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/175/guided-pike-fly-fishing-adventure-in-dalgatan-146-796-30-lvdalen-sverige" style="padding:3px 7px;">ab 455€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow175);

marker175.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow175.open(map, marker175);
});
const location104 = { lat: 57.93, lng: 12.5362 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location104.lat && coordinate.lng === location104.lng;
});

let marker104;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker104 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location104.lat + getRandomOffset(),
            lng: location104.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker104 = new google.maps.marker.AdvancedMarkerElement({
        position: location104,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location104);
}

markers.push(marker104);

const infowindow104 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/104/full-day-fishing-for-zander-pike-and-perch-in-alingss-sweden"><h5 class="card-title" style="font-size: 14px;">Zander, Hecht und Barsch in Schweden - Tracker Targa V-18</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Hecht, Zander
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/104/full-day-fishing-for-zander-pike-and-perch-in-alingss-sweden" style="padding:3px 7px;">ab 690€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow104);

marker104.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow104.open(map, marker104);
});
const location58 = { lat: 53.5649, lng: 13.2695 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location58.lat && coordinate.lng === location58.lng;
});

let marker58;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker58 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location58.lat + getRandomOffset(),
            lng: location58.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker58 = new google.maps.marker.AdvancedMarkerElement({
        position: location58,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location58);
}

markers.push(marker58);

const infowindow58 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/58/barschtouren-auf-der-mecklenburgischen-seenplatte-in-mecklenburgische-seenplatte-deutschland"><h5 class="card-title" style="font-size: 14px;">Barschtouren auf der Mecklenburgischen Seenplatte</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            See, Kanal
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/58/barschtouren-auf-der-mecklenburgischen-seenplatte-in-mecklenburgische-seenplatte-deutschland" style="padding:3px 7px;">ab 300€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow58);

marker58.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow58.open(map, marker58);
});
const location118 = { lat: 53.5488, lng: 9.98717 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location118.lat && coordinate.lng === location118.lng;
});

let marker118;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker118 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location118.lat + getRandomOffset(),
            lng: location118.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker118 = new google.maps.marker.AdvancedMarkerElement({
        position: location118,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location118);
}

markers.push(marker118);

const infowindow118 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/118/zanderangeln-im-hamburger-hafen-in-hamburg-deutschland"><h5 class="card-title" style="font-size: 14px;">Zanderangeln im Hamburger Hafen</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Zander
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Hafen
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/118/zanderangeln-im-hamburger-hafen-in-hamburg-deutschland" style="padding:3px 7px;">ab 145€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow118);

marker118.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow118.open(map, marker118);
});
const location180 = { lat: 43.5141, lng: 16.1077 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location180.lat && coordinate.lng === location180.lng;
});

let marker180;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker180 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location180.lat + getRandomOffset(),
            lng: location180.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker180 = new google.maps.marker.AdvancedMarkerElement({
        position: location180,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location180);
}

markers.push(marker180);

const infowindow180 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/180/half-day-fishing-tour-trolling-jigging-in-marina-kroatien"><h5 class="card-title" style="font-size: 14px;">Halbtägige Angeltour – Trolling und Jigging</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Makrele, Rotbarsch, Mahi Mahi, Amberjack, Blaufisch, Snapper
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/180/half-day-fishing-tour-trolling-jigging-in-marina-kroatien" style="padding:3px 7px;">ab 300€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow180);

marker180.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow180.open(map, marker180);
});
const location135 = { lat: 43.5147, lng: 16.4435 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location135.lat && coordinate.lng === location135.lng;
});

let marker135;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker135 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location135.lat + getRandomOffset(),
            lng: location135.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker135 = new google.maps.marker.AdvancedMarkerElement({
        position: location135,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location135);
}

markers.push(marker135);

const infowindow135 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/135/fishing-tour-to-vis-and-bisevo-island-in-croatia"><h5 class="card-title" style="font-size: 14px;">Angeltour zur Insel Vis und Bisevo</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Mahi Mahi, Brassen, Makrele
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/135/fishing-tour-to-vis-and-bisevo-island-in-croatia" style="padding:3px 7px;">ab 1100€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow135);

marker135.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow135.open(map, marker135);
});
const location80 = { lat: 52.3676, lng: 4.90414 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location80.lat && coordinate.lng === location80.lng;
});

let marker80;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker80 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location80.lat + getRandomOffset(),
            lng: location80.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker80 = new google.maps.marker.AdvancedMarkerElement({
        position: location80,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location80);
}

markers.push(marker80);

const infowindow80 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/80/theplace2fish-fishing-for-seabass-in-amsterdam-netherlands"><h5 class="card-title" style="font-size: 14px;">Theplace2fish Angeln auf Wolfsbarsch</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Hecht, Zander, Wolfsbarsch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer, Kanal
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/80/theplace2fish-fishing-for-seabass-in-amsterdam-netherlands" style="padding:3px 7px;">ab 195€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow80);

marker80.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow80.open(map, marker80);
});
const location198 = { lat: 48.999, lng: 12.6698 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location198.lat && coordinate.lng === location198.lng;
});

let marker198;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker198 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location198.lat + getRandomOffset(),
            lng: location198.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker198 = new google.maps.marker.AdvancedMarkerElement({
        position: location198,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location198);
}

markers.push(marker198);

const infowindow198 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/198/angeln-mit-der-fliegenrute-in-bayerischer-wald-94354-haselbach-deutschland"><h5 class="card-title" style="font-size: 14px;">Angeln mit der Fliegenrute</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Ã„sche, Bachforelle, Huchen
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Bach, Fluss
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/198/angeln-mit-der-fliegenrute-in-bayerischer-wald-94354-haselbach-deutschland" style="padding:3px 7px;">ab 349€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow198);

marker198.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow198.open(map, marker198);
});
const location101 = { lat: 52.1326, lng: 5.29127 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location101.lat && coordinate.lng === location101.lng;
});

let marker101;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker101 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location101.lat + getRandomOffset(),
            lng: location101.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker101 = new google.maps.marker.AdvancedMarkerElement({
        position: location101,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location101);
}

markers.push(marker101);

const infowindow101 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/101/poldertour-hecht-coaching-in-niederlande"><h5 class="card-title" style="font-size: 14px;">Poldertour Hecht - Coaching</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Hecht
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Bach, Kanal
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/101/poldertour-hecht-coaching-in-niederlande" style="padding:3px 7px;">ab 200€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow101);

marker101.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow101.open(map, marker101);
});
const location248 = { lat: 53.6127, lng: 12.4296 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location248.lat && coordinate.lng === location248.lng;
});

let marker248;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker248 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location248.lat + getRandomOffset(),
            lng: location248.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker248 = new google.maps.marker.AdvancedMarkerElement({
        position: location248,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location248);
}

markers.push(marker248);

const infowindow248 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/248/fliegenfischen-lernen-in-mecklenburg-vorpommern-einzelkurs-individuelles-lernen-in-mecklenburg-vorpommern-deutschland"><h5 class="card-title" style="font-size: 14px;">Fliegenfischen lernen in Mecklenburg-Vorpommern: Einzelkurs/Individuelles lernen</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs an Land
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/248/fliegenfischen-lernen-in-mecklenburg-vorpommern-einzelkurs-individuelles-lernen-in-mecklenburg-vorpommern-deutschland" style="padding:3px 7px;">ab 349€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow248);

marker248.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow248.open(map, marker248);
});
const location216 = { lat: 39.3302, lng: 3.16855 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location216.lat && coordinate.lng === location216.lng;
});

let marker216;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker216 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location216.lat + getRandomOffset(),
            lng: location216.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker216 = new google.maps.marker.AdvancedMarkerElement({
        position: location216,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location216);
}

markers.push(marker216);

const infowindow216 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/216/4h-taster-family-fishing-trip-in-07659-cala-figuera-balearen-spanien"><h5 class="card-title" style="font-size: 14px;">4-stündiger Schnupper-/Familien-Angelausflug</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Amberjack, Makrele
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/216/4h-taster-family-fishing-trip-in-07659-cala-figuera-balearen-spanien" style="padding:3px 7px;">ab 850€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow216);

marker216.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow216.open(map, marker216);
});
const location256 = { lat: 32.7187, lng: -17.1723 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location256.lat && coordinate.lng === location256.lng;
});

let marker256;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker256 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location256.lat + getRandomOffset(),
            lng: location256.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker256 = new google.maps.marker.AdvancedMarkerElement({
        position: location256,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location256);
}

markers.push(marker256);

const infowindow256 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/256/white-marlin-fishing-tour-on-madeira-in-9370-calheta-portugal"><h5 class="card-title" style="font-size: 14px;">Angeltour zum Weißen Marlin auf Madeira</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Marlin, Mahi Mahi, Bonitos, Amberjack, Snapper
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/256/white-marlin-fishing-tour-on-madeira-in-9370-calheta-portugal" style="padding:3px 7px;">ab 1300€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow256);

marker256.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow256.open(map, marker256);
});
const location30 = { lat: 54.2328, lng: 10.2783 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location30.lat && coordinate.lng === location30.lng;
});

let marker30;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker30 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location30.lat + getRandomOffset(),
            lng: location30.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker30 = new google.maps.marker.AdvancedMarkerElement({
        position: location30,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location30);
}

markers.push(marker30);

const infowindow30 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/30/meerforellenguiding-tobias-seeger-in-24211-preetz-deutschland"><h5 class="card-title" style="font-size: 14px;">Meerforellenguiding Tobias Seeger</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meerforelle
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/30/meerforellenguiding-tobias-seeger-in-24211-preetz-deutschland" style="padding:3px 7px;">ab 165€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow30);

marker30.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow30.open(map, marker30);
});
const location231 = { lat: 48.7904, lng: 11.4979 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location231.lat && coordinate.lng === location231.lng;
});

let marker231;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker231 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location231.lat + getRandomOffset(),
            lng: location231.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker231 = new google.maps.marker.AdvancedMarkerElement({
        position: location231,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location231);
}

markers.push(marker231);

const infowindow231 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/231/fliegenfischen-lernen-in-bayern-in-bayern-deutschland"><h5 class="card-title" style="font-size: 14px;">Fliegenfischen lernen in Bayern</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs an Land
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/231/fliegenfischen-lernen-in-bayern-in-bayern-deutschland" style="padding:3px 7px;">ab 349€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow231);

marker231.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow231.open(map, marker231);
});
const location29 = { lat: 52.1326, lng: 5.29127 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location29.lat && coordinate.lng === location29.lng;
});

let marker29;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker29 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location29.lat + getRandomOffset(),
            lng: location29.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker29 = new google.maps.marker.AdvancedMarkerElement({
        position: location29,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location29);
}

markers.push(marker29);

const infowindow29 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/29/raubfischangeln-in-den-niederlanden-in-niederlande"><h5 class="card-title" style="font-size: 14px;">Raubfischangeln in den Niederlanden</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Huchen, Rapfen, Wels, Zander
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss, See, Kanal, Hafen, Talsperre
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/29/raubfischangeln-in-den-niederlanden-in-niederlande" style="padding:3px 7px;">ab 249€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow29);

marker29.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow29.open(map, marker29);
});
const location89 = { lat: 42.1318, lng: -0.407806 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location89.lat && coordinate.lng === location89.lng;
});

let marker89;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker89 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location89.lat + getRandomOffset(),
            lng: location89.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker89 = new google.maps.marker.AdvancedMarkerElement({
        position: location89,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location89);
}

markers.push(marker89);

const infowindow89 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/89/trout-fishing-pyrenees-in-spanien"><h5 class="card-title" style="font-size: 14px;">Forellenengel Pyrenäen (Aragonien)</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Bachforelle, Regenbogenforelle, Barbe
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss, Bach, Talsperre
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/89/trout-fishing-pyrenees-in-spanien" style="padding:3px 7px;">ab 200€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow89);

marker89.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow89.open(map, marker89);
});
const location194 = { lat: 50.1183, lng: 7.30895 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location194.lat && coordinate.lng === location194.lng;
});

let marker194;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker194 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location194.lat + getRandomOffset(),
            lng: location194.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker194 = new google.maps.marker.AdvancedMarkerElement({
        position: location194,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location194);
}

markers.push(marker194);

const infowindow194 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/194/fliegenfischen-auf-bachforellen-im-grossraum-rheinland-pfalz-in-rheinland-pfalz-deutschland"><h5 class="card-title" style="font-size: 14px;">Fliegenfischen auf Bachforellen im GroÃŸraum Rheinland-Pfalz</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Bachforelle, Barbe
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Bach, Fluss, See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/194/fliegenfischen-auf-bachforellen-im-grossraum-rheinland-pfalz-in-rheinland-pfalz-deutschland" style="padding:3px 7px;">ab 349€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow194);

marker194.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow194.open(map, marker194);
});
const location139 = { lat: 45.35, lng: 14.3167 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location139.lat && coordinate.lng === location139.lng;
});

let marker139;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker139 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location139.lat + getRandomOffset(),
            lng: location139.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker139 = new google.maps.marker.AdvancedMarkerElement({
        position: location139,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location139);
}

markers.push(marker139);

const infowindow139 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/139/big-game-fishing-in-croatia-in-volosko-51410-opatija-kroatien"><h5 class="card-title" style="font-size: 14px;">Hochseefischen in Kroatien</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Thunfisch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/139/big-game-fishing-in-croatia-in-volosko-51410-opatija-kroatien" style="padding:3px 7px;">ab 750€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow139);

marker139.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow139.open(map, marker139);
});
const location47 = { lat: 54.4601, lng: 11.1337 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location47.lat && coordinate.lng === location47.lng;
});

let marker47;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker47 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location47.lat + getRandomOffset(),
            lng: location47.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker47 = new google.maps.marker.AdvancedMarkerElement({
        position: location47,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location47);
}

markers.push(marker47);

const infowindow47 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/47/angelkajak-guiding-auf-fehmarn-leihausrstung-vorhanden-in-fehmarn-deutschland"><h5 class="card-title" style="font-size: 14px;">Angelkajak Guiding auf Fehmarn (LeihausrÃ¼stung vorhanden)</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Dorsch, Flunder, Hering, Hornhecht, Kliesche, Makrele, Scholle
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/47/angelkajak-guiding-auf-fehmarn-leihausrstung-vorhanden-in-fehmarn-deutschland" style="padding:3px 7px;">ab 147€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow47);

marker47.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow47.open(map, marker47);
});
const location204 = { lat: 45.3376, lng: 14.3052 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location204.lat && coordinate.lng === location204.lng;
});

let marker204;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker204 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location204.lat + getRandomOffset(),
            lng: location204.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker204 = new google.maps.marker.AdvancedMarkerElement({
        position: location204,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location204);
}

markers.push(marker204);

const infowindow204 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/204/light-bottom-fishing-in-kvarner-in-opatija-croatia"><h5 class="card-title" style="font-size: 14px;">Leichtes Grundfischen in Kvarner, Kroatien</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Wittling, Makrele
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/204/light-bottom-fishing-in-kvarner-in-opatija-croatia" style="padding:3px 7px;">ab 87€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow204);

marker204.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow204.open(map, marker204);
});
const location251 = { lat: 49.3262, lng: 8.51868 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location251.lat && coordinate.lng === location251.lng;
});

let marker251;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker251 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location251.lat + getRandomOffset(),
            lng: location251.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker251 = new google.maps.marker.AdvancedMarkerElement({
        position: location251,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location251);
}

markers.push(marker251);

const infowindow251 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/251/feedern-karpfenangeln-fr-einsteiger-in-68-hockenheim-deutschland"><h5 class="card-title" style="font-size: 14px;">Feedern / Karpfenangeln fÃ¼r Einsteiger</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Brassen, Barbe, Karpfen, Nase, Rotauge, Rotfeder, Schleie
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss, See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/251/feedern-karpfenangeln-fr-einsteiger-in-68-hockenheim-deutschland" style="padding:3px 7px;">ab 199€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow251);

marker251.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow251.open(map, marker251);
});
const location240 = { lat: 51.011, lng: 10.8453 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location240.lat && coordinate.lng === location240.lng;
});

let marker240;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker240 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location240.lat + getRandomOffset(),
            lng: location240.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker240 = new google.maps.marker.AdvancedMarkerElement({
        position: location240,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location240);
}

markers.push(marker240);

const infowindow240 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/240/fliegenfischen-lernen-in-thringen-einzelkurs-individuelles-lernen-in-thringen-deutschland"><h5 class="card-title" style="font-size: 14px;">Fliegenfischen lernen in ThÃ¼ringen Einzelkurs/Individuelles lernen</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs an Land
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/240/fliegenfischen-lernen-in-thringen-einzelkurs-individuelles-lernen-in-thringen-deutschland" style="padding:3px 7px;">ab 349€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow240);

marker240.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow240.open(map, marker240);
});
const location60 = { lat: 56.8373, lng: 13.6738 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location60.lat && coordinate.lng === location60.lng;
});

let marker60;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker60 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location60.lat + getRandomOffset(),
            lng: location60.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker60 = new google.maps.marker.AdvancedMarkerElement({
        position: location60,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location60);
}

markers.push(marker60);

const infowindow60 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/60/schwedentour-komplettpaket-eine-woche-in-bolmen-schweden"><h5 class="card-title" style="font-size: 14px;">Schwedentour (Komplettpaket eine Woche)</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Hecht, Zander
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/60/schwedentour-komplettpaket-eine-woche-in-bolmen-schweden" style="padding:3px 7px;">ab 1260€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow60);

marker60.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow60.open(map, marker60);
});
const location185 = { lat: 48.1351, lng: 11.582 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location185.lat && coordinate.lng === location185.lng;
});

let marker185;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker185 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location185.lat + getRandomOffset(),
            lng: location185.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker185 = new google.maps.marker.AdvancedMarkerElement({
        position: location185,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location185);
}

markers.push(marker185);

const infowindow185 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/185/fliegenfischen-auf-forelle-sche-dbel-co-in-mnchen-deutschland"><h5 class="card-title" style="font-size: 14px;">Fliegenfischen auf Forelle, Ã„sche, DÃ¶bel &amp; Co</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barbe, Ã„sche, Bachforelle, Regenbogenforelle
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            See, Fluss, Bach
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/185/fliegenfischen-auf-forelle-sche-dbel-co-in-mnchen-deutschland" style="padding:3px 7px;">ab 349€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow185);

marker185.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow185.open(map, marker185);
});
const location242 = { lat: 51.4332, lng: 7.66159 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location242.lat && coordinate.lng === location242.lng;
});

let marker242;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker242 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location242.lat + getRandomOffset(),
            lng: location242.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker242 = new google.maps.marker.AdvancedMarkerElement({
        position: location242,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location242);
}

markers.push(marker242);

const infowindow242 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/242/fliegenfischen-lernen-in-nordrhein-westfalen-einzelkurs-individuelles-lernen-in-nordrhein-westfalen-deutschland"><h5 class="card-title" style="font-size: 14px;">Fliegenfischen lernen in Nordrhein-Westfalen: Einzelkurs/Individuelles lernen</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs an Land
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/242/fliegenfischen-lernen-in-nordrhein-westfalen-einzelkurs-individuelles-lernen-in-nordrhein-westfalen-deutschland" style="padding:3px 7px;">ab 349€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow242);

marker242.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow242.open(map, marker242);
});
const location221 = { lat: 39.765, lng: 3.15412 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location221.lat && coordinate.lng === location221.lng;
});

let marker221;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker221 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location221.lat + getRandomOffset(),
            lng: location221.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker221 = new google.maps.marker.AdvancedMarkerElement({
        position: location221,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location221);
}

markers.push(marker221);

const infowindow221 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/221/hochseeangeln-vor-mallorca-in-07458-can-picafort-illes-balears-spanien"><h5 class="card-title" style="font-size: 14px;">Hochseefischen – Troliing &amp; Chuming</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Thunfisch, Mahi Mahi, Amberjack, Barrakuda, Speerfisch, Schwertfisch, Blauflossenthun, Bonitos, Zackenbarsch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/221/hochseeangeln-vor-mallorca-in-07458-can-picafort-illes-balears-spanien" style="padding:3px 7px;">ab 1100€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow221);

marker221.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow221.open(map, marker221);
});
const location124 = { lat: 53.3173, lng: 13.8633 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location124.lat && coordinate.lng === location124.lng;
});

let marker124;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker124 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location124.lat + getRandomOffset(),
            lng: location124.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker124 = new google.maps.marker.AdvancedMarkerElement({
        position: location124,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location124);
}

markers.push(marker124);

const infowindow124 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/124/raubfisch-guiding-in-17291-prenzlau-deutschland"><h5 class="card-title" style="font-size: 14px;">Raubfisch Guiding</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Hecht, Barsch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/124/raubfisch-guiding-in-17291-prenzlau-deutschland" style="padding:3px 7px;">ab 280€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow124);

marker124.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow124.open(map, marker124);
});
const location128 = { lat: 41.4637, lng: 0.410751 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location128.lat && coordinate.lng === location128.lng;
});

let marker128;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker128 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location128.lat + getRandomOffset(),
            lng: location128.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker128 = new google.maps.marker.AdvancedMarkerElement({
        position: location128,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location128);
}

markers.push(marker128);

const infowindow128 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/128/ansitz-angeltour-auf-wels-vom-ufer-in-25183-sers-provinz-lleida-spanien"><h5 class="card-title" style="font-size: 14px;">Ansitz Angeltour auf Wels vom Ufer</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Wels
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/128/ansitz-angeltour-auf-wels-vom-ufer-in-25183-sers-provinz-lleida-spanien" style="padding:3px 7px;">ab 200€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow128);

marker128.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow128.open(map, marker128);
});
const location159 = { lat: 51.6919, lng: 4.43791 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location159.lat && coordinate.lng === location159.lng;
});

let marker159;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker159 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location159.lat + getRandomOffset(),
            lng: location159.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker159 = new google.maps.marker.AdvancedMarkerElement({
        position: location159,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location159);
}

markers.push(marker159);

const infowindow159 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/159/angelguiding-holland-hollands-diep-haringvliet-volkerak-in-willemstad-niederlande"><h5 class="card-title" style="font-size: 14px;">Angelguiding Holland: Hollands Diep, Haringvliet, Volkerak</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Zander, Hecht
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss, See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/159/angelguiding-holland-hollands-diep-haringvliet-volkerak-in-willemstad-niederlande" style="padding:3px 7px;">ab 395€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow159);

marker159.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow159.open(map, marker159);
});
const location163 = { lat: 44.8217, lng: 13.9369 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location163.lat && coordinate.lng === location163.lng;
});

let marker163;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker163 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location163.lat + getRandomOffset(),
            lng: location163.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker163 = new google.maps.marker.AdvancedMarkerElement({
        position: location163,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location163);
}

markers.push(marker163);

const infowindow163 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/163/sunrise-sunset-wreck-tuna-fishing-in-medulin-croatia"><h5 class="card-title" style="font-size: 14px;">Wrack-Thunfischangeln bei Sonnenaufgang/Sonnenuntergang</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Makrele, Mahi Mahi, Thunfisch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/163/sunrise-sunset-wreck-tuna-fishing-in-medulin-croatia" style="padding:3px 7px;">ab 780€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow163);

marker163.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow163.open(map, marker163);
});
const location24 = { lat: 54.0924, lng: 12.0991 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location24.lat && coordinate.lng === location24.lng;
});

let marker24;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker24 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location24.lat + getRandomOffset(),
            lng: location24.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker24 = new google.maps.marker.AdvancedMarkerElement({
        position: location24,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location24);
}

markers.push(marker24);

const infowindow24 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/24/raubfischtouren-vom-belly-boat-oder-ufer-an-der-ostseekste-mecklenburg-vorpommerns-in-rostock-deutschland"><h5 class="card-title" style="font-size: 14px;">Raubfischtouren vom Belly boat oder Ufer an der OstseekÃ¼ste Mecklenburg-Vorpommerns</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Hecht
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss, Meer, Hafen
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/24/raubfischtouren-vom-belly-boat-oder-ufer-an-der-ostseekste-mecklenburg-vorpommerns-in-rostock-deutschland" style="padding:3px 7px;">ab 203€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow24);

marker24.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow24.open(map, marker24);
});
const location259 = { lat: 54.0206, lng: 13.7786 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location259.lat && coordinate.lng === location259.lng;
});

let marker259;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker259 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location259.lat + getRandomOffset(),
            lng: location259.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker259 = new google.maps.marker.AdvancedMarkerElement({
        position: location259,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location259);
}

markers.push(marker259);

const infowindow259 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/259/spinnfischen-auf-zander-in-17440-sauzin-ziemitz-deutschland"><h5 class="card-title" style="font-size: 14px;">Spinnfischen auf Zander</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Zander
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/259/spinnfischen-auf-zander-in-17440-sauzin-ziemitz-deutschland" style="padding:3px 7px;">ab 379€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow259);

marker259.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow259.open(map, marker259);
});
const location191 = { lat: 52.6367, lng: 9.84508 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location191.lat && coordinate.lng === location191.lng;
});

let marker191;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker191 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location191.lat + getRandomOffset(),
            lng: location191.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker191 = new google.maps.marker.AdvancedMarkerElement({
        position: location191,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location191);
}

markers.push(marker191);

const infowindow191 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/191/mit-der-fliegenrute-auf-forelle-hecht-barbe-sche-in-niedersachsen-deutschland"><h5 class="card-title" style="font-size: 14px;">Mit der Fliegenrute auf Forelle, Hecht, Barbe &amp; Ã„sche</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Bachforelle, Ã„sche, Regenbogenforelle, Barbe, Hecht
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Bach, Fluss, See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/191/mit-der-fliegenrute-auf-forelle-hecht-barbe-sche-in-niedersachsen-deutschland" style="padding:3px 7px;">ab 349€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow191);

marker191.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow191.open(map, marker191);
});
const location226 = { lat: 48.1351, lng: 11.582 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location226.lat && coordinate.lng === location226.lng;
});

let marker226;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker226 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location226.lat + getRandomOffset(),
            lng: location226.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker226 = new google.maps.marker.AdvancedMarkerElement({
        position: location226,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location226);
}

markers.push(marker226);

const infowindow226 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/226/fliegenfischen-kurs-in-mnchen-nymphenburg-fliegenfischerkurs-bayern-in-mnchen-deutschland"><h5 class="card-title" style="font-size: 14px;">Fliegenfischen Kurs in MÃ¼nchen (Nymphenburg) / Fliegenfischerkurs Bayern</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs an Land
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/226/fliegenfischen-kurs-in-mnchen-nymphenburg-fliegenfischerkurs-bayern-in-mnchen-deutschland" style="padding:3px 7px;">ab 199€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow226);

marker226.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow226.open(map, marker226);
});
const location59 = { lat: 53.6127, lng: 12.4296 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location59.lat && coordinate.lng === location59.lng;
});

let marker59;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker59 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location59.lat + getRandomOffset(),
            lng: location59.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker59 = new google.maps.marker.AdvancedMarkerElement({
        position: location59,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location59);
}

markers.push(marker59);

const infowindow59 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/59/wintertouren-auf-flachwasserhechte-in-mecklenburg-vorpommern-deutschland"><h5 class="card-title" style="font-size: 14px;">Wintertouren auf Flachwasserhechte</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Hecht
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss, See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/59/wintertouren-auf-flachwasserhechte-in-mecklenburg-vorpommern-deutschland" style="padding:3px 7px;">ab 300€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow59);

marker59.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow59.open(map, marker59);
});
const location82 = { lat: 50.9452, lng: 6.6555 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location82.lat && coordinate.lng === location82.lng;
});

let marker82;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker82 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location82.lat + getRandomOffset(),
            lng: location82.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker82 = new google.maps.marker.AdvancedMarkerElement({
        position: location82,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location82);
}

markers.push(marker82);

const infowindow82 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/82/forellenangeln-am-forellenteich-in-bergheim-deutschland"><h5 class="card-title" style="font-size: 14px;">Forellenangeln am Forellenteich</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Bachforelle, Regenbogenforelle, Saibling
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/82/forellenangeln-am-forellenteich-in-bergheim-deutschland" style="padding:3px 7px;">ab 200€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow82);

marker82.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow82.open(map, marker82);
});
const location262 = { lat: 37.7487, lng: -25.2391 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location262.lat && coordinate.lng === location262.lng;
});

let marker262;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker262 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location262.lat + getRandomOffset(),
            lng: location262.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker262 = new google.maps.marker.AdvancedMarkerElement({
        position: location262,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location262);
}

markers.push(marker262);

const infowindow262 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/262/2-hour-sunset-tour-in-9650-povoacao-portugal"><h5 class="card-title" style="font-size: 14px;">2-stündige Sonnenuntergangstour</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Makrele
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/262/2-hour-sunset-tour-in-9650-povoacao-portugal" style="padding:3px 7px;">ab 200€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow262);

marker262.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow262.open(map, marker262);
});
const location55 = { lat: 56.5524, lng: 14.1374 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location55.lat && coordinate.lng === location55.lng;
});

let marker55;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker55 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location55.lat + getRandomOffset(),
            lng: location55.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker55 = new google.maps.marker.AdvancedMarkerElement({
        position: location55,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location55);
}

markers.push(marker55);

const infowindow55 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/55/predator-fishing-4-hours-in-lmhult-sweden"><h5 class="card-title" style="font-size: 14px;">Raubfischangeln 4 Stunden</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Hecht, Zander
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss, See, Kanal
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/55/predator-fishing-4-hours-in-lmhult-sweden" style="padding:3px 7px;">ab 270€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow55);

marker55.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow55.open(map, marker55);
});
const location209 = { lat: 52.1326, lng: 5.29127 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location209.lat && coordinate.lng === location209.lng;
});

let marker209;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker209 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location209.lat + getRandomOffset(),
            lng: location209.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker209 = new google.maps.marker.AdvancedMarkerElement({
        position: location209,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location209);
}

markers.push(marker209);

const infowindow209 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/209/zander-barsch-in-den-nl-tagestour-8h-in-niederlande"><h5 class="card-title" style="font-size: 14px;">Zander &amp; Barsch in den NL - Tagestour (8h)</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Zander, Barsch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/209/zander-barsch-in-den-nl-tagestour-8h-in-niederlande" style="padding:3px 7px;">ab 360€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow209);

marker209.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow209.open(map, marker209);
});
const location81 = { lat: 56.0118, lng: 8.12794 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location81.lat && coordinate.lng === location81.lng;
});

let marker81;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker81 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location81.lat + getRandomOffset(),
            lng: location81.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker81 = new google.maps.marker.AdvancedMarkerElement({
        position: location81,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location81);
}

markers.push(marker81);

const infowindow81 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/81/pike-and-perch-fishing-in-hvide-sande-danmark"><h5 class="card-title" style="font-size: 14px;">Angeln auf Hecht und Barsch</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Hecht
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss, See, Kanal
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/81/pike-and-perch-fishing-in-hvide-sande-danmark" style="padding:3px 7px;">ab 600€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow81);

marker81.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow81.open(map, marker81);
});
const location42 = { lat: 56.5247, lng: 14.9785 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location42.lat && coordinate.lng === location42.lng;
});

let marker42;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker42 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location42.lat + getRandomOffset(),
            lng: location42.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker42 = new google.maps.marker.AdvancedMarkerElement({
        position: location42,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location42);
}

markers.push(marker42);

const infowindow42 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/42/barsch-hecht-guiding-am-mien-in-tingsryd-schweden"><h5 class="card-title" style="font-size: 14px;">Barsch/- Hecht Guiding am Mien</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Hecht
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/42/barsch-hecht-guiding-am-mien-in-tingsryd-schweden" style="padding:3px 7px;">ab 478€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow42);

marker42.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow42.open(map, marker42);
});
const location54 = { lat: 51.7823, lng: 5.12602 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location54.lat && coordinate.lng === location54.lng;
});

let marker54;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker54 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location54.lat + getRandomOffset(),
            lng: location54.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker54 = new google.maps.marker.AdvancedMarkerElement({
        position: location54,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location54);
}

markers.push(marker54);

const infowindow54 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/54/3-tage-raubfischguiding-in-nl-mit-haus-guide-und-boot-in-aalst-niederlande"><h5 class="card-title" style="font-size: 14px;">3 Tage Raubfischguiding All-Inclusive in NL mit Haus, Guide und Boot und Livescope</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Hecht, Zander
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss, See, Kanal, Hafen
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/54/3-tage-raubfischguiding-in-nl-mit-haus-guide-und-boot-in-aalst-niederlande" style="padding:3px 7px;">ab 700€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow54);

marker54.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow54.open(map, marker54);
});
const location123 = { lat: 41.2292, lng: 0.339442 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location123.lat && coordinate.lng === location123.lng;
});

let marker123;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker123 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location123.lat + getRandomOffset(),
            lng: location123.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker123 = new google.maps.marker.AdvancedMarkerElement({
        position: location123,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location123);
}

markers.push(marker123);

const infowindow123 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/123/predator-guiding-in-carretera-tv-7231-km-29-43783-la-pobla-de-massaluca-tarragona-espaa"><h5 class="card-title" style="font-size: 14px;">Führung zum Multi-Raubfischangeln</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Zander, Schwarzbarsch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Talsperre
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/123/predator-guiding-in-carretera-tv-7231-km-29-43783-la-pobla-de-massaluca-tarragona-espaa" style="padding:3px 7px;">ab 250€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow123);

marker123.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow123.open(map, marker123);
});
const location13 = { lat: 51.1913, lng: 5.98777 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location13.lat && coordinate.lng === location13.lng;
});

let marker13;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker13 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location13.lat + getRandomOffset(),
            lng: location13.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker13 = new google.maps.marker.AdvancedMarkerElement({
        position: location13,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location13);
}

markers.push(marker13);

const infowindow13 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/13/grossbarsch-und-andere-ruber-am-nederrijn-maas-und-anliegenden-plassen-in-roermond-niederlande"><h5 class="card-title" style="font-size: 14px;">Grossbarsch und andere RÃ¤uber am Nederrijn, Maas und anliegenden Plassen</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Hecht, Zander
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss, See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/13/grossbarsch-und-andere-ruber-am-nederrijn-maas-und-anliegenden-plassen-in-roermond-niederlande" style="padding:3px 7px;">ab 350€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow13);

marker13.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow13.open(map, marker13);
});
const location8 = { lat: 50.7456, lng: 8.0802 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location8.lat && coordinate.lng === location8.lng;
});

let marker8;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker8 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location8.lat + getRandomOffset(),
            lng: location8.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker8 = new google.maps.marker.AdvancedMarkerElement({
        position: location8,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location8);
}

markers.push(marker8);

const infowindow8 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/8/klopfen-auf-wels-in-nienburg-weser-deutschland"><h5 class="card-title" style="font-size: 14px;">Klopfen auf Wels</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Wels
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/8/klopfen-auf-wels-in-nienburg-weser-deutschland" style="padding:3px 7px;">ab 150€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow8);

marker8.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow8.open(map, marker8);
});
const location220 = { lat: 39.765, lng: 3.15412 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location220.lat && coordinate.lng === location220.lng;
});

let marker220;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker220 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location220.lat + getRandomOffset(),
            lng: location220.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker220 = new google.maps.marker.AdvancedMarkerElement({
        position: location220,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location220);
}

markers.push(marker220);

const infowindow220 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/220/fishing-trip-mallorca-big-game-in-07458-can-picafort-illes-balears-spanien"><h5 class="card-title" style="font-size: 14px;">Hochseeangeln auf Mallorca – gemeinsamer Ausflug</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Thunfisch, Marlin, Mahi Mahi, Amberjack, Barrakuda, Speerfisch, Schwertfisch, Blauflossenthun, Bonitos, Zackenbarsch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/220/fishing-trip-mallorca-big-game-in-07458-can-picafort-illes-balears-spanien" style="padding:3px 7px;">ab 250€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow220);

marker220.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow220.open(map, marker220);
});
const location223 = { lat: 39.765, lng: 3.15412 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location223.lat && coordinate.lng === location223.lng;
});

let marker223;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker223 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location223.lat + getRandomOffset(),
            lng: location223.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker223 = new google.maps.marker.AdvancedMarkerElement({
        position: location223,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location223);
}

markers.push(marker223);

const infowindow223 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/223/bottom-fishing-half-day-trip-in-07458-can-picafort-illes-balears-spanien"><h5 class="card-title" style="font-size: 14px;">Grundangeln – Halbtagesausflug</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Thunfisch, Amberjack, Dentex, Jon Dory, Drachenkopf, Meerbrasse, Zackenbarsch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/223/bottom-fishing-half-day-trip-in-07458-can-picafort-illes-balears-spanien" style="padding:3px 7px;">ab 600€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow223);

marker223.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow223.open(map, marker223);
});
const location260 = { lat: 54.29, lng: 13.12 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location260.lat && coordinate.lng === location260.lng;
});

let marker260;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker260 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location260.lat + getRandomOffset(),
            lng: location260.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker260 = new google.maps.marker.AdvancedMarkerElement({
        position: location260,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location260);
}

markers.push(marker260);

const infowindow260 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/260/hecht-barsch-guiding-am-bodden-in-strelasund-stralsund-deutschland"><h5 class="card-title" style="font-size: 14px;">Hecht &amp; Barsch Guiding am Bodden</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Hecht
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/260/hecht-barsch-guiding-am-bodden-in-strelasund-stralsund-deutschland" style="padding:3px 7px;">ab 379€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow260);

marker260.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow260.open(map, marker260);
});
const location177 = { lat: 62.1321, lng: 12.2966 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location177.lat && coordinate.lng === location177.lng;
});

let marker177;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker177 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location177.lat + getRandomOffset(),
            lng: location177.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker177 = new google.maps.marker.AdvancedMarkerElement({
        position: location177,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location177);
}

markers.push(marker177);

const infowindow177 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/177/arctic-char-ice-fishing-in-remote-mountain-scenery-in-grvelsjn-797-92-idre-sverige"><h5 class="card-title" style="font-size: 14px;">Angeln auf arktischem Saibling in abgelegener Berglandschaft!</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            MarÃ¤ne, Bachforelle
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/177/arctic-char-ice-fishing-in-remote-mountain-scenery-in-grvelsjn-797-92-idre-sverige" style="padding:3px 7px;">ab 910€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow177);

marker177.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow177.open(map, marker177);
});
const location217 = { lat: 39.3302, lng: 3.16855 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location217.lat && coordinate.lng === location217.lng;
});

let marker217;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker217 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location217.lat + getRandomOffset(),
            lng: location217.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker217 = new google.maps.marker.AdvancedMarkerElement({
        position: location217,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location217);
}

markers.push(marker217);

const infowindow217 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/217/banco-the-4-day-fishing-experience-in-07659-cala-figuera-balearen-spanien"><h5 class="card-title" style="font-size: 14px;">Banco – Das 4-tägige Angelerlebnis!</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Mahi Mahi, Thunfisch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/217/banco-the-4-day-fishing-experience-in-07659-cala-figuera-balearen-spanien" style="padding:3px 7px;">ab 8000€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow217);

marker217.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow217.open(map, marker217);
});
const location22 = { lat: 53.5649, lng: 13.2695 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location22.lat && coordinate.lng === location22.lng;
});

let marker22;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker22 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location22.lat + getRandomOffset(),
            lng: location22.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker22 = new google.maps.marker.AdvancedMarkerElement({
        position: location22,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location22);
}

markers.push(marker22);

const infowindow22 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/22/raubfischtouren-vom-belly-boat-auf-naturseen-in-der-mecklenburgischen-seenplatte-in-mecklenburgische-seenplatte-deutschland"><h5 class="card-title" style="font-size: 14px;">Raubfisch Touren vom Belly boat auf Naturseen in der Mecklenburgischen Seenplatte</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Hecht, Zander
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                        alle, Natursee
                    
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/22/raubfischtouren-vom-belly-boat-auf-naturseen-in-der-mecklenburgischen-seenplatte-in-mecklenburgische-seenplatte-deutschland" style="padding:3px 7px;">ab 203€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow22);

marker22.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow22.open(map, marker22);
});
const location133 = { lat: 41.3874, lng: 2.16857 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location133.lat && coordinate.lng === location133.lng;
});

let marker133;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker133 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location133.lat + getRandomOffset(),
            lng: location133.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker133 = new google.maps.marker.AdvancedMarkerElement({
        position: location133,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location133);
}

markers.push(marker133);

const infowindow133 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/133/mittelmeer-raubfischtour-vor-barcelona-in-carrer-de-la-pau-12-08930-sant-adri-de-bess-barcelona-spanien"><h5 class="card-title" style="font-size: 14px;">Mittelmeer Raubfischtour vor Barcelona</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Mahi Mahi, Makrele, Thunfisch, Wolfsbarsch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/133/mittelmeer-raubfischtour-vor-barcelona-in-carrer-de-la-pau-12-08930-sant-adri-de-bess-barcelona-spanien" style="padding:3px 7px;">ab 300€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow133);

marker133.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow133.open(map, marker133);
});
const location222 = { lat: 39.3302, lng: 3.16855 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location222.lat && coordinate.lng === location222.lng;
});

let marker222;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker222 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location222.lat + getRandomOffset(),
            lng: location222.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker222 = new google.maps.marker.AdvancedMarkerElement({
        position: location222,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location222);
}

markers.push(marker222);

const infowindow222 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/222/24-hrs-fishing-experience-in-07659-cala-figuera-balearen-spanien"><h5 class="card-title" style="font-size: 14px;">24 Stunden Angelerlebnis</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Mahi Mahi, Makrele, Thunfisch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/222/24-hrs-fishing-experience-in-07659-cala-figuera-balearen-spanien" style="padding:3px 7px;">ab 3000€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow222);

marker222.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow222.open(map, marker222);
});
const location203 = { lat: 37.026, lng: -7.84235 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location203.lat && coordinate.lng === location203.lng;
});

let marker203;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker203 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location203.lat + getRandomOffset(),
            lng: location203.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker203 = new google.maps.marker.AdvancedMarkerElement({
        position: location203,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location203);
}

markers.push(marker203);

const infowindow203 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/203/8-hour-trip-white-marlin-in-olho-portugal"><h5 class="card-title" style="font-size: 14px;">8-stündiger Ausflug – White Marlin</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Mahi Mahi, Marlin
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/203/8-hour-trip-white-marlin-in-olho-portugal" style="padding:3px 7px;">ab 1200€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow203);

marker203.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow203.open(map, marker203);
});
const location77 = { lat: 52.1326, lng: 5.29127 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location77.lat && coordinate.lng === location77.lng;
});

let marker77;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker77 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location77.lat + getRandomOffset(),
            lng: location77.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker77 = new google.maps.marker.AdvancedMarkerElement({
        position: location77,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location77);
}

markers.push(marker77);

const infowindow77 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/77/dick-barsch-angeln-vom-boot-in-niederlande"><h5 class="card-title" style="font-size: 14px;">Dick-Barsch Angeln vom Boot</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss, See, Hafen
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/77/dick-barsch-angeln-vom-boot-in-niederlande" style="padding:3px 7px;">ab 400€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow77);

marker77.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow77.open(map, marker77);
});
const location38 = { lat: 49.4774, lng: 8.44475 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location38.lat && coordinate.lng === location38.lng;
});

let marker38;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker38 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location38.lat + getRandomOffset(),
            lng: location38.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker38 = new google.maps.marker.AdvancedMarkerElement({
        position: location38,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location38);
}

markers.push(marker38);

const infowindow38 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/38/zanderangeln-am-rhein-oder-neckar-in-ludwigshafen-am-rhein-deutschland"><h5 class="card-title" style="font-size: 14px;">Zanderangeln am Rhein oder Neckar</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Hecht, Rapfen, Zander
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss, See, Kanal, Hafen
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/38/zanderangeln-am-rhein-oder-neckar-in-ludwigshafen-am-rhein-deutschland" style="padding:3px 7px;">ab 190€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow38);

marker38.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow38.open(map, marker38);
});
const location156 = { lat: 53.2194, lng: 6.5665 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location156.lat && coordinate.lng === location156.lng;
});

let marker156;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker156 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location156.lat + getRandomOffset(),
            lng: location156.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker156 = new google.maps.marker.AdvancedMarkerElement({
        position: location156,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location156);
}

markers.push(marker156);

const infowindow156 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/156/hechtangeln-in-holland-nordholland-groningen-in-groningen-niederlande"><h5 class="card-title" style="font-size: 14px;">Hechtangeln in Holland (Nordholland/Groningen)</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Hecht
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Kanal, See, Hafen, Fluss
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/156/hechtangeln-in-holland-nordholland-groningen-in-groningen-niederlande" style="padding:3px 7px;">ab 180€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow156);

marker156.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow156.open(map, marker156);
});
const location137 = { lat: 51.691, lng: 4.21268 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location137.lat && coordinate.lng === location137.lng;
});

let marker137;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker137 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location137.lat + getRandomOffset(),
            lng: location137.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker137 = new google.maps.marker.AdvancedMarkerElement({
        position: location137,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location137);
}

markers.push(marker137);

const infowindow137 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/137/barschangeln-vom-profi-boot-in-holland-in-3255-oude-tonge-niederlande"><h5 class="card-title" style="font-size: 14px;">Barschangeln vom Profi-Boot in Holland</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Hecht, Zander
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss, Kanal, See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/137/barschangeln-vom-profi-boot-in-holland-in-3255-oude-tonge-niederlande" style="padding:3px 7px;">ab 599€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow137);

marker137.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow137.open(map, marker137);
});
const location172 = { lat: 61.2358, lng: 14.0345 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location172.lat && coordinate.lng === location172.lng;
});

let marker172;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker172 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location172.lat + getRandomOffset(),
            lng: location172.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker172 = new google.maps.marker.AdvancedMarkerElement({
        position: location172,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location172);
}

markers.push(marker172);

const infowindow172 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/172/try-out-fly-fishing-in-flowing-river-in-dalgatan-146-796-30-lvdalen-sverige"><h5 class="card-title" style="font-size: 14px;">Probieren Sie Fliegenfischen in einem fließenden Fluss aus</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Ã„sche
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss, Bach
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/172/try-out-fly-fishing-in-flowing-river-in-dalgatan-146-796-30-lvdalen-sverige" style="padding:3px 7px;">ab 455€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow172);

marker172.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow172.open(map, marker172);
});
const location249 = { lat: 54.2194, lng: 9.69612 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location249.lat && coordinate.lng === location249.lng;
});

let marker249;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker249 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location249.lat + getRandomOffset(),
            lng: location249.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker249 = new google.maps.marker.AdvancedMarkerElement({
        position: location249,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location249);
}

markers.push(marker249);

const infowindow249 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/249/fliegenfischen-lernen-in-schleswig-holstein-einzelkurs-individuelles-lernen-in-schleswig-holstein-deutschland"><h5 class="card-title" style="font-size: 14px;">Fliegenfischen lernen in Schleswig-Holstein: Einzelkurs/Individuelles lernen</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs an Land
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/249/fliegenfischen-lernen-in-schleswig-holstein-einzelkurs-individuelles-lernen-in-schleswig-holstein-deutschland" style="padding:3px 7px;">ab 349€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow249);

marker249.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow249.open(map, marker249);
});
const location9 = { lat: 52.6056, lng: 8.37079 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location9.lat && coordinate.lng === location9.lng;
});

let marker9;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker9 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location9.lat + getRandomOffset(),
            lng: location9.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker9 = new google.maps.marker.AdvancedMarkerElement({
        position: location9,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location9);
}

markers.push(marker9);

const infowindow9 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/9/welsangeln-an-der-hunte-in-diepholz-deutschland"><h5 class="card-title" style="font-size: 14px;">Welsangeln an der Hunte</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Wels
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/9/welsangeln-an-der-hunte-in-diepholz-deutschland" style="padding:3px 7px;">ab 200€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow9);

marker9.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow9.open(map, marker9);
});
const location239 = { lat: 53.5295, lng: 9.86351 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location239.lat && coordinate.lng === location239.lng;
});

let marker239;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker239 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location239.lat + getRandomOffset(),
            lng: location239.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker239 = new google.maps.marker.AdvancedMarkerElement({
        position: location239,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location239);
}

markers.push(marker239);

const infowindow239 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/239/fliegenfischen-kurs-in-hamburg-finkenwerder-fliegenfischerkurs-hamburg-in-finkenwerder-21129-hamburg-deutschland"><h5 class="card-title" style="font-size: 14px;">Fliegenfischen Kurs in Hamburg (Finkenwerder) / Fliegenfischerkurs Hamburg</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs an Land
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/239/fliegenfischen-kurs-in-hamburg-finkenwerder-fliegenfischerkurs-hamburg-in-finkenwerder-21129-hamburg-deutschland" style="padding:3px 7px;">ab 199€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow239);

marker239.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow239.open(map, marker239);
});
const location23 = { lat: 53.3308, lng: 13.4335 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location23.lat && coordinate.lng === location23.lng;
});

let marker23;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker23 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location23.lat + getRandomOffset(),
            lng: location23.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker23 = new google.maps.marker.AdvancedMarkerElement({
        position: location23,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location23);
}

markers.push(marker23);

const infowindow23 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/23/raubfischtouren-vom-belly-boat-auf-naturseen-in-der-feldberger-seenlandschaft-in-feldberger-seenlandschaft-deutschland"><h5 class="card-title" style="font-size: 14px;">Raubfisch Touren vom Belly boat auf Naturseen in der Feldberger Seenlandschaft</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Hecht, Wels, Zander
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                        alle, Natursee
                    
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/23/raubfischtouren-vom-belly-boat-auf-naturseen-in-der-feldberger-seenlandschaft-in-feldberger-seenlandschaft-deutschland" style="padding:3px 7px;">ab 203€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow23);

marker23.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow23.open(map, marker23);
});
const location183 = { lat: 54.4601, lng: 11.1337 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location183.lat && coordinate.lng === location183.lng;
});

let marker183;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker183 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location183.lat + getRandomOffset(),
            lng: location183.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker183 = new google.maps.marker.AdvancedMarkerElement({
        position: location183,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location183);
}

markers.push(marker183);

const infowindow183 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/183/meerforellen-guiding-an-der-ostseekste-von-schleswig-holstein-incl-fehmarn-in-23769-fehmarn-deutschland"><h5 class="card-title" style="font-size: 14px;">Meerforellen-Guiding an der OstseekÃ¼ste von Schleswig-Holstein incl. Fehmarn</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meerforelle, Hornhecht
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/183/meerforellen-guiding-an-der-ostseekste-von-schleswig-holstein-incl-fehmarn-in-23769-fehmarn-deutschland" style="padding:3px 7px;">ab 90€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow183);

marker183.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow183.open(map, marker183);
});
const location150 = { lat: 44.2822, lng: 15.3478 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location150.lat && coordinate.lng === location150.lng;
});

let marker150;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker150 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location150.lat + getRandomOffset(),
            lng: location150.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker150 = new google.maps.marker.AdvancedMarkerElement({
        position: location150,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location150);
}

markers.push(marker150);

const infowindow150 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/150/morning-fishing-extended-in-23248-raanac-hrvatska"><h5 class="card-title" style="font-size: 14px;">Morgenfischen verlängert</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Makrele, Wolfsbarsch, Brassen, Giebel
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/150/morning-fishing-extended-in-23248-raanac-hrvatska" style="padding:3px 7px;">ab 240€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow150);

marker150.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow150.open(map, marker150);
});
const location61 = { lat: 54.3486, lng: 10.6051 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location61.lat && coordinate.lng === location61.lng;
});

let marker61;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker61 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location61.lat + getRandomOffset(),
            lng: location61.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker61 = new google.maps.marker.AdvancedMarkerElement({
        position: location61,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location61);
}

markers.push(marker61);

const infowindow61 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/61/plattfisch-guiding-in-der-hohwachter-bucht-fr-1-person-in-24321-behrensdorf-deutschland"><h5 class="card-title" style="font-size: 14px;">Plattfisch Guiding in der Hohwachter Bucht fÃ¼r 1 Person</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Flunder, Kliesche, Scholle
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/61/plattfisch-guiding-in-der-hohwachter-bucht-fr-1-person-in-24321-behrensdorf-deutschland" style="padding:3px 7px;">ab 269€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow61);

marker61.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow61.open(map, marker61);
});
const location18 = { lat: 52.5168, lng: 6.08302 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location18.lat && coordinate.lng === location18.lng;
});

let marker18;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker18 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location18.lat + getRandomOffset(),
            lng: location18.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker18 = new google.maps.marker.AdvancedMarkerElement({
        position: location18,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location18);
}

markers.push(marker18);

const infowindow18 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/18/raubfischtour-am-zwarte-water-zwolle-nl-in-zwolle-niederlande"><h5 class="card-title" style="font-size: 14px;">Raubfischtour am Zwarte Water, Zwolle (NL)</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Hecht, Zander
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss, Kanal
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/18/raubfischtour-am-zwarte-water-zwolle-nl-in-zwolle-niederlande" style="padding:3px 7px;">ab 162€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow18);

marker18.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow18.open(map, marker18);
});
const location127 = { lat: 41.4188, lng: 0.353067 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location127.lat && coordinate.lng === location127.lng;
});

let marker127;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker127 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location127.lat + getRandomOffset(),
            lng: location127.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker127 = new google.maps.marker.AdvancedMarkerElement({
        position: location127,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location127);
}

markers.push(marker127);

const infowindow127 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/127/angelguiding-auf-wels-vom-driftboot-mit-spinnrute-in-25185-la-granja-d-escarp-lleida-spanien"><h5 class="card-title" style="font-size: 14px;">Angelguiding auf Wels vom Driftboot mit Spinnrute</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Wels
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/127/angelguiding-auf-wels-vom-driftboot-mit-spinnrute-in-25185-la-granja-d-escarp-lleida-spanien" style="padding:3px 7px;">ab 250€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow127);

marker127.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow127.open(map, marker127);
});
const location70 = { lat: 52.1326, lng: 5.29127 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location70.lat && coordinate.lng === location70.lng;
});

let marker70;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker70 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location70.lat + getRandomOffset(),
            lng: location70.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker70 = new google.maps.marker.AdvancedMarkerElement({
        position: location70,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location70);
}

markers.push(marker70);

const infowindow70 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/70/zanderangeln-total-an-ijssel-waal-co-in-niederlande"><h5 class="card-title" style="font-size: 14px;">Zanderangeln TOTAL - an IJssel, Waal &amp; Co.</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Zander
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss, Kanal
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/70/zanderangeln-total-an-ijssel-waal-co-in-niederlande" style="padding:3px 7px;">ab 300€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow70);

marker70.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow70.open(map, marker70);
});
const location246 = { lat: 53.0793, lng: 8.80169 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location246.lat && coordinate.lng === location246.lng;
});

let marker246;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker246 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location246.lat + getRandomOffset(),
            lng: location246.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker246 = new google.maps.marker.AdvancedMarkerElement({
        position: location246,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location246);
}

markers.push(marker246);

const infowindow246 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/246/fliegenfischen-lernen-in-bremen-einzelkurs-individuelles-lernen-in-28-bremen-deutschland"><h5 class="card-title" style="font-size: 14px;">Fliegenfischen lernen in Bremen: Einzelkurs/Individuelles lernen</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs an Land
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/246/fliegenfischen-lernen-in-bremen-einzelkurs-individuelles-lernen-in-28-bremen-deutschland" style="padding:3px 7px;">ab 349€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow246);

marker246.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow246.open(map, marker246);
});
const location125 = { lat: 41.372, lng: 0.300753 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location125.lat && coordinate.lng === location125.lng;
});

let marker125;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker125 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location125.lat + getRandomOffset(),
            lng: location125.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker125 = new google.maps.marker.AdvancedMarkerElement({
        position: location125,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location125);
}

markers.push(marker125);

const infowindow125 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/125/raubfisch-angeltour-in-spanien-am-ebro-in-50170-mequinenza-saragossa-spanien"><h5 class="card-title" style="font-size: 14px;">Raubfisch Angeltour in Spanien am Ebro</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Schwarzbarsch, Barsch, Zander, Wels
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Talsperre, Fluss
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/125/raubfisch-angeltour-in-spanien-am-ebro-in-50170-mequinenza-saragossa-spanien" style="padding:3px 7px;">ab 250€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow125);

marker125.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow125.open(map, marker125);
});
const location228 = { lat: 50.6521, lng: 9.16244 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location228.lat && coordinate.lng === location228.lng;
});

let marker228;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker228 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location228.lat + getRandomOffset(),
            lng: location228.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker228 = new google.maps.marker.AdvancedMarkerElement({
        position: location228,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location228);
}

markers.push(marker228);

const infowindow228 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/228/fliegenfischen-lernen-in-hessen-in-hessen-deutschland"><h5 class="card-title" style="font-size: 14px;">Fliegenfischen lernen in Hessen</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs an Land
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/228/fliegenfischen-lernen-in-hessen-in-hessen-deutschland" style="padding:3px 7px;">ab 349€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow228);

marker228.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow228.open(map, marker228);
});
const location87 = { lat: 56.2756, lng: 12.8385 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location87.lat && coordinate.lng === location87.lng;
});

let marker87;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker87 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location87.lat + getRandomOffset(),
            lng: location87.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker87 = new google.maps.marker.AdvancedMarkerElement({
        position: location87,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location87);
}

markers.push(marker87);

const infowindow87 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/87/kstenguiding-auf-meerforelle-in-schweden-in-sklderviken-schweden"><h5 class="card-title" style="font-size: 14px;">KÃ¼stenguiding auf Meerforelle in Schweden</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meerforelle
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/87/kstenguiding-auf-meerforelle-in-schweden-in-sklderviken-schweden" style="padding:3px 7px;">ab 309€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow87);

marker87.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow87.open(map, marker87);
});
const location247 = { lat: 53.5488, lng: 9.98717 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location247.lat && coordinate.lng === location247.lng;
});

let marker247;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker247 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location247.lat + getRandomOffset(),
            lng: location247.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker247 = new google.maps.marker.AdvancedMarkerElement({
        position: location247,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location247);
}

markers.push(marker247);

const infowindow247 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/247/fliegenfischen-lernen-in-hamburg-einzelkurs-individuelles-lernen-in-hamburg-deutschland"><h5 class="card-title" style="font-size: 14px;">Fliegenfischen lernen in Hamburg: Einzelkurs/Individuelles lernen</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs an Land
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/247/fliegenfischen-lernen-in-hamburg-einzelkurs-individuelles-lernen-in-hamburg-deutschland" style="padding:3px 7px;">ab 349€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow247);

marker247.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow247.open(map, marker247);
});
const location161 = { lat: 51.7348, lng: 4.43821 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location161.lat && coordinate.lng === location161.lng;
});

let marker161;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker161 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location161.lat + getRandomOffset(),
            lng: location161.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker161 = new google.maps.marker.AdvancedMarkerElement({
        position: location161,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location161);
}

markers.push(marker161);

const infowindow161 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/161/3-tages-angeltour-lerne-mit-dem-livescope-zu-angeln-in-numansdorp-niederlande"><h5 class="card-title" style="font-size: 14px;">3-Tages Angeltour: Lerne mit dem Livescope zu angeln</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Zander, Hecht
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss, See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/161/3-tages-angeltour-lerne-mit-dem-livescope-zu-angeln-in-numansdorp-niederlande" style="padding:3px 7px;">ab 1699€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow161);

marker161.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow161.open(map, marker161);
});
const location218 = { lat: 46.9379, lng: 7.79068 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location218.lat && coordinate.lng === location218.lng;
});

let marker218;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker218 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location218.lat + getRandomOffset(),
            lng: location218.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker218 = new google.maps.marker.AdvancedMarkerElement({
        position: location218,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location218);
}

markers.push(marker218);

const infowindow218 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/218/salmoniden-im-bach-bafo-rebo-in-langnau-im-emmental-schweiz"><h5 class="card-title" style="font-size: 14px;">Lachs Im Bash (Pav-Rabh)</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Regenbogenforelle, Bachforelle
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Bach
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/218/salmoniden-im-bach-bafo-rebo-in-langnau-im-emmental-schweiz" style="padding:3px 7px;">ab 300€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow218);

marker218.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow218.open(map, marker218);
});
const location184 = { lat: 54.352, lng: 13.363 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location184.lat && coordinate.lng === location184.lng;
});

let marker184;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker184 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location184.lat + getRandomOffset(),
            lng: location184.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker184 = new google.maps.marker.AdvancedMarkerElement({
        position: location184,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location184);
}

markers.push(marker184);

const infowindow184 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/184/hechtangeln-mit-der-fliegenrute-am-bodden-in-rgen-18-deutschland"><h5 class="card-title" style="font-size: 14px;">Hechtangeln mit der Fliegenrute am Bodden</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Hecht
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer, See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/184/hechtangeln-mit-der-fliegenrute-am-bodden-in-rgen-18-deutschland" style="padding:3px 7px;">ab 349€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow184);

marker184.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow184.open(map, marker184);
});
const location171 = { lat: 32.7607, lng: -16.9595 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location171.lat && coordinate.lng === location171.lng;
});

let marker171;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker171 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location171.lat + getRandomOffset(),
            lng: location171.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker171 = new google.maps.marker.AdvancedMarkerElement({
        position: location171,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location171);
}

markers.push(marker171);

const infowindow171 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/171/bottom-fishing-in-madeira-portugal"><h5 class="card-title" style="font-size: 14px;">Grundangeln vor der Küste Madeiras</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Mahi Mahi, Amberjack, Snapper
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/171/bottom-fishing-in-madeira-portugal" style="padding:3px 7px;">ab 1030€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow171);

marker171.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow171.open(map, marker171);
});
const location190 = { lat: 43.5863, lng: 15.923 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location190.lat && coordinate.lng === location190.lng;
});

let marker190;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker190 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location190.lat + getRandomOffset(),
            lng: location190.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker190 = new google.maps.marker.AdvancedMarkerElement({
        position: location190,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location190);
}

markers.push(marker190);

const infowindow190 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/190/fishing-experience-in-primoten-croatia"><h5 class="card-title" style="font-size: 14px;">Angelerlebnis – Hochseeangeln in Kroatien</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Amberjack, Makrele, Snapper, Mahi Mahi, Thunfisch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/190/fishing-experience-in-primoten-croatia" style="padding:3px 7px;">ab 1100€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow190);

marker190.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow190.open(map, marker190);
});
const location162 = { lat: 44.8217, lng: 13.9369 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location162.lat && coordinate.lng === location162.lng;
});

let marker162;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker162 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location162.lat + getRandomOffset(),
            lng: location162.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker162 = new google.maps.marker.AdvancedMarkerElement({
        position: location162,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location162);
}

markers.push(marker162);

const infowindow162 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/162/bluefin-tuna-fishing-in-medulin-croatia"><h5 class="card-title" style="font-size: 14px;">Angeln auf Roten Thun</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Makrele, Thunfisch, Mahi Mahi
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/162/bluefin-tuna-fishing-in-medulin-croatia" style="padding:3px 7px;">ab 970€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow162);

marker162.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow162.open(map, marker162);
});
const location243 = { lat: 52.1314, lng: 13.2162 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location243.lat && coordinate.lng === location243.lng;
});

let marker243;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker243 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location243.lat + getRandomOffset(),
            lng: location243.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker243 = new google.maps.marker.AdvancedMarkerElement({
        position: location243,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location243);
}

markers.push(marker243);

const infowindow243 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/243/fliegenfischen-lernen-in-brandenburg-einzelkurs-individuelles-lernen-in-brandenburg-deutschland"><h5 class="card-title" style="font-size: 14px;">Fliegenfischen lernen in Brandenburg: Einzelkurs/Individuelles lernen</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs an Land
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/243/fliegenfischen-lernen-in-brandenburg-einzelkurs-individuelles-lernen-in-brandenburg-deutschland" style="padding:3px 7px;">ab 349€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow243);

marker243.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow243.open(map, marker243);
});
const location153 = { lat: 37.8218, lng: -25.4282 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location153.lat && coordinate.lng === location153.lng;
});

let marker153;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker153 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location153.lat + getRandomOffset(),
            lng: location153.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker153 = new google.maps.marker.AdvancedMarkerElement({
        position: location153,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location153);
}

markers.push(marker153);

const infowindow153 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/153/jigging-in-s-miguel-azores-in-9625-porto-formoso-portugal"><h5 class="card-title" style="font-size: 14px;">Jigging und andere in S. Miguel Azoren</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Rotbarsch, Flunder, Wolfsbarsch, Brassen, Giebel, Thunfisch, Dorade, Makrele, Seelachs, Schwarzbarsch, Mahi Mahi
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/153/jigging-in-s-miguel-azores-in-9625-porto-formoso-portugal" style="padding:3px 7px;">ab 170€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow153);

marker153.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow153.open(map, marker153);
});
const location91 = { lat: 53.0793, lng: 8.80169 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location91.lat && coordinate.lng === location91.lng;
});

let marker91;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker91 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location91.lat + getRandomOffset(),
            lng: location91.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker91 = new google.maps.marker.AdvancedMarkerElement({
        position: location91,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location91);
}

markers.push(marker91);

const infowindow91 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/91/vertikalguiding-auf-der-weser-in-bremen-deutschland"><h5 class="card-title" style="font-size: 14px;">Vertikalguiding auf der Weser</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Zander
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/91/vertikalguiding-auf-der-weser-in-bremen-deutschland" style="padding:3px 7px;">ab 360€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow91);

marker91.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow91.open(map, marker91);
});
const location20 = { lat: 53.1063, lng: 6.8751 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location20.lat && coordinate.lng === location20.lng;
});

let marker20;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker20 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location20.lat + getRandomOffset(),
            lng: location20.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker20 = new google.maps.marker.AdvancedMarkerElement({
        position: location20,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location20);
}

markers.push(marker20);

const infowindow20 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/20/poldertour-in-veendam-nl-in-veendam-niederlande"><h5 class="card-title" style="font-size: 14px;">Poldertour in Veendam (NL)</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Hecht
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Kanal
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/20/poldertour-in-veendam-nl-in-veendam-niederlande" style="padding:3px 7px;">ab 72€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow20);

marker20.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow20.open(map, marker20);
});
const location219 = { lat: 39.765, lng: 3.15412 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location219.lat && coordinate.lng === location219.lng;
});

let marker219;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker219 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location219.lat + getRandomOffset(),
            lng: location219.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker219 = new google.maps.marker.AdvancedMarkerElement({
        position: location219,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location219);
}

markers.push(marker219);

const infowindow219 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/219/coastal-fishing-tour-on-mallorca-in-07458-can-picafort-illes-balears-spanien"><h5 class="card-title" style="font-size: 14px;">Leichte Trolling-Angeltour auf Mallorca</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Thunfisch, Amberjack, Barrakuda, Dentex, Blauflossenthun, Bonitos
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/219/coastal-fishing-tour-on-mallorca-in-07458-can-picafort-illes-balears-spanien" style="padding:3px 7px;">ab 700€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow219);

marker219.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow219.open(map, marker219);
});
const location149 = { lat: 44.2822, lng: 15.3478 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location149.lat && coordinate.lng === location149.lng;
});

let marker149;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker149 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location149.lat + getRandomOffset(),
            lng: location149.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker149 = new google.maps.marker.AdvancedMarkerElement({
        position: location149,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location149);
}

markers.push(marker149);

const infowindow149 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/149/morning-fishing-in-23248-raanac-hrvatska"><h5 class="card-title" style="font-size: 14px;">Morgenfischen in Kroatien</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Brassen, Makrele, Wolfsbarsch, Giebel
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/149/morning-fishing-in-23248-raanac-hrvatska" style="padding:3px 7px;">ab 200€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow149);

marker149.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow149.open(map, marker149);
});
const location253 = { lat: 32.7187, lng: -17.1723 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location253.lat && coordinate.lng === location253.lng;
});

let marker253;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker253 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location253.lat + getRandomOffset(),
            lng: location253.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker253 = new google.maps.marker.AdvancedMarkerElement({
        position: location253,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location253);
}

markers.push(marker253);

const infowindow253 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/253/blue-marlin-fishing-on-madeira-in-9370-calheta-portugal"><h5 class="card-title" style="font-size: 14px;">Angeln auf Blauen Marlin auf Madeira</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Marlin
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/253/blue-marlin-fishing-on-madeira-in-9370-calheta-portugal" style="padding:3px 7px;">ab 1300€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow253);

marker253.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow253.open(map, marker253);
});
const location85 = { lat: 56.2243, lng: 12.6742 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location85.lat && coordinate.lng === location85.lng;
});

let marker85;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker85 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location85.lat + getRandomOffset(),
            lng: location85.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker85 = new google.maps.marker.AdvancedMarkerElement({
        position: location85,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location85);
}

markers.push(marker85);

const infowindow85 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/85/meerforellenangeln-vom-boot-in-jonstorp-schweden"><h5 class="card-title" style="font-size: 14px;">Meerforellenangeln vom Boot</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Makrele, Meerforelle, Wolfsbarsch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/85/meerforellenangeln-vom-boot-in-jonstorp-schweden" style="padding:3px 7px;">ab 257€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow85);

marker85.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow85.open(map, marker85);
});
const location263 = { lat: 64.7502, lng: 20.9509 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location263.lat && coordinate.lng === location263.lng;
});

let marker263;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker263 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location263.lat + getRandomOffset(),
            lng: location263.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker263 = new google.maps.marker.AdvancedMarkerElement({
        position: location263,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location263);
}

markers.push(marker263);

const infowindow263 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/263/grayling-trout-or-pike-fishing-from-belly-boat-on-the-coast-or-salmon-and-trout-fishing-in-byske-and-by-rivers-in-skellefte-sverige"><h5 class="card-title" style="font-size: 14px;">Äschen-, Forellen- oder Hechtangeln vom Belly Boat an der Küste oder Lachs- und Forellenangeln in den Flüssen Byske und Åby</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Hecht, Meerforelle
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/263/grayling-trout-or-pike-fishing-from-belly-boat-on-the-coast-or-salmon-and-trout-fishing-in-byske-and-by-rivers-in-skellefte-sverige" style="padding:3px 7px;">ab 500€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow263);

marker263.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow263.open(map, marker263);
});
const location213 = { lat: 39.3302, lng: 3.16855 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location213.lat && coordinate.lng === location213.lng;
});

let marker213;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker213 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location213.lat + getRandomOffset(),
            lng: location213.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker213 = new google.maps.marker.AdvancedMarkerElement({
        position: location213,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location213);
}

markers.push(marker213);

const infowindow213 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/213/banco-tour-fishing-for-big-tuna-swordfish-grouper-sharks-and-many-more-in-07659-cala-figuera-balearen-spanien"><h5 class="card-title" style="font-size: 14px;">Banco-Tour, Angeln auf großen Thunfisch, Schwertfisch, Zackenbarsch, Hai und viele mehr</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Mahi Mahi, Makrele, Thunfisch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/213/banco-tour-fishing-for-big-tuna-swordfish-grouper-sharks-and-many-more-in-07659-cala-figuera-balearen-spanien" style="padding:3px 7px;">ab 6800€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow213);

marker213.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow213.open(map, marker213);
});
const location98 = { lat: 63.5933, lng: 19.3358 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location98.lat && coordinate.lng === location98.lng;
});

let marker98;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker98 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location98.lat + getRandomOffset(),
            lng: location98.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker98 = new google.maps.marker.AdvancedMarkerElement({
        position: location98,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location98);
}

markers.push(marker98);

const infowindow98 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/98/lachs-und-meerforellenangeln-in-wildem-schwedischen-fluss-in-hyngelsble-113-nordmaling-schweden"><h5 class="card-title" style="font-size: 14px;">Lachs und Meerforellenangeln in wildem schwedischen Fluss</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Ã„sche, Bachforelle, Lachs, Meerforelle
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/98/lachs-und-meerforellenangeln-in-wildem-schwedischen-fluss-in-hyngelsble-113-nordmaling-schweden" style="padding:3px 7px;">ab 450€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow98);

marker98.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow98.open(map, marker98);
});
const location178 = { lat: 38.0173, lng: 12.5365 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location178.lat && coordinate.lng === location178.lng;
});

let marker178;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker178 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location178.lat + getRandomOffset(),
            lng: location178.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker178 = new google.maps.marker.AdvancedMarkerElement({
        position: location178,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location178);
}

markers.push(marker178);

const infowindow178 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/178/charter-di-pesca-al-tonno-rosso-alle-egadi-in-91100-trapani-tp-italia"><h5 class="card-title" style="font-size: 14px;">Angelcharter für Roten Thun auf den Ägadischen Inseln</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Thunfisch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/178/charter-di-pesca-al-tonno-rosso-alle-egadi-in-91100-trapani-tp-italia" style="padding:3px 7px;">ab 600€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow178);

marker178.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow178.open(map, marker178);
});
const location144 = { lat: 51.691, lng: 4.21268 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location144.lat && coordinate.lng === location144.lng;
});

let marker144;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker144 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location144.lat + getRandomOffset(),
            lng: location144.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker144 = new google.maps.marker.AdvancedMarkerElement({
        position: location144,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location144);
}

markers.push(marker144);

const infowindow144 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/144/hechtangeln-vom-profi-boot-in-holland-in-3255-oude-tonge-niederlande"><h5 class="card-title" style="font-size: 14px;">Hechtangeln vom Profi-Boot in Holland</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Zander, Hecht
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            See, Kanal, Fluss
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/144/hechtangeln-vom-profi-boot-in-holland-in-3255-oude-tonge-niederlande" style="padding:3px 7px;">ab 599€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow144);

marker144.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow144.open(map, marker144);
});
const location90 = { lat: 42.1318, lng: -0.407806 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location90.lat && coordinate.lng === location90.lng;
});

let marker90;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker90 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location90.lat + getRandomOffset(),
            lng: location90.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker90 = new google.maps.marker.AdvancedMarkerElement({
        position: location90,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location90);
}

markers.push(marker90);

const infowindow90 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/90/bass-fishing-in-spanien"><h5 class="card-title" style="font-size: 14px;">Schwarzbarsch Angeln PyrenÃ¤en</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Schwarzbarsch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            See, Talsperre
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/90/bass-fishing-in-spanien" style="padding:3px 7px;">ab 150€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow90);

marker90.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow90.open(map, marker90);
});
const location115 = { lat: 37.5129, lng: -8.47575 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location115.lat && coordinate.lng === location115.lng;
});

let marker115;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker115 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location115.lat + getRandomOffset(),
            lng: location115.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker115 = new google.maps.marker.AdvancedMarkerElement({
        position: location115,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location115);
}

markers.push(marker115);

const infowindow115 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/115/predator-fishing-in-portugal"><h5 class="card-title" style="font-size: 14px;">Raubfischangeln</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Schwarzbarsch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            See, Talsperre
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/115/predator-fishing-in-portugal" style="padding:3px 7px;">ab 250€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow115);

marker115.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow115.open(map, marker115);
});
const location66 = { lat: 51.7823, lng: 5.12602 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location66.lat && coordinate.lng === location66.lng;
});

let marker66;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker66 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location66.lat + getRandomOffset(),
            lng: location66.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker66 = new google.maps.marker.AdvancedMarkerElement({
        position: location66,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location66);
}

markers.push(marker66);

const infowindow66 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/66/1-tag-raubfischguiding-in-nl-mit-guide-und-boot-in-aalst-niederlande"><h5 class="card-title" style="font-size: 14px;">1 Tag Raubfischguiding in NL mit Guide und Boot</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Hecht, Wels, Zander
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss, See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/66/1-tag-raubfischguiding-in-nl-mit-guide-und-boot-in-aalst-niederlande" style="padding:3px 7px;">ab 200€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow66);

marker66.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow66.open(map, marker66);
});
const location207 = { lat: 51.4332, lng: 7.66159 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location207.lat && coordinate.lng === location207.lng;
});

let marker207;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker207 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location207.lat + getRandomOffset(),
            lng: location207.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker207 = new google.maps.marker.AdvancedMarkerElement({
        position: location207,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location207);
}

markers.push(marker207);

const infowindow207 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/207/zander-barsch-am-rhein-nrw-abends-4h-in-nordrhein-westfalen-deutschland"><h5 class="card-title" style="font-size: 14px;">Zander und Barsch am Rhein (NRW) - Abends(4h)</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Zander
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/207/zander-barsch-am-rhein-nrw-abends-4h-in-nordrhein-westfalen-deutschland" style="padding:3px 7px;">ab 200€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow207);

marker207.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow207.open(map, marker207);
});
const location130 = { lat: 40.7268, lng: 0.837734 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location130.lat && coordinate.lng === location130.lng;
});

let marker130;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker130 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location130.lat + getRandomOffset(),
            lng: location130.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker130 = new google.maps.marker.AdvancedMarkerElement({
        position: location130,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location130);
}

markers.push(marker130);

const infowindow130 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/130/meeres-angeltour-am-mittelmeer-im-ebro-delta-in-43580-riumar-provinz-tarragona-spanien"><h5 class="card-title" style="font-size: 14px;">Meeres Angeltour am Mittelmeer im Ebro Delta</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Mahi Mahi, Thunfisch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/130/meeres-angeltour-am-mittelmeer-im-ebro-delta-in-43580-riumar-provinz-tarragona-spanien" style="padding:3px 7px;">ab 700€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow130);

marker130.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow130.open(map, marker130);
});
const location252 = { lat: 32.7187, lng: -17.1723 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location252.lat && coordinate.lng === location252.lng;
});

let marker252;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker252 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location252.lat + getRandomOffset(),
            lng: location252.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker252 = new google.maps.marker.AdvancedMarkerElement({
        position: location252,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location252);
}

markers.push(marker252);

const infowindow252 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/252/fishing-for-bigeye-tuna-in-9370-calheta-portugal"><h5 class="card-title" style="font-size: 14px;">Angeln auf Großaugenthun</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Thunfisch, Blauflossenthun
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/252/fishing-for-bigeye-tuna-in-9370-calheta-portugal" style="padding:3px 7px;">ab 1300€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow252);

marker252.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow252.open(map, marker252);
});
const location255 = { lat: 32.7187, lng: -17.1723 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location255.lat && coordinate.lng === location255.lng;
});

let marker255;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker255 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location255.lat + getRandomOffset(),
            lng: location255.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker255 = new google.maps.marker.AdvancedMarkerElement({
        position: location255,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location255);
}

markers.push(marker255);

const infowindow255 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/255/spearfish-fishing-tour-on-madeira-in-9370-calheta-portugal"><h5 class="card-title" style="font-size: 14px;">Speerfisch-Angeltour auf Madeira</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Speerfisch, Schwertfisch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/255/spearfish-fishing-tour-on-madeira-in-9370-calheta-portugal" style="padding:3px 7px;">ab 1300€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow255);

marker255.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow255.open(map, marker255);
});
const location229 = { lat: 50.1183, lng: 7.30895 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location229.lat && coordinate.lng === location229.lng;
});

let marker229;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker229 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location229.lat + getRandomOffset(),
            lng: location229.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker229 = new google.maps.marker.AdvancedMarkerElement({
        position: location229,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location229);
}

markers.push(marker229);

const infowindow229 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/229/fliegenfischen-lernen-in-rheinland-pfalz-in-rheinland-pfalz-deutschland"><h5 class="card-title" style="font-size: 14px;">Fliegenfischen lernen in Rheinland-Pfalz</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs an Land
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/229/fliegenfischen-lernen-in-rheinland-pfalz-in-rheinland-pfalz-deutschland" style="padding:3px 7px;">ab 349€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow229);

marker229.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow229.open(map, marker229);
});
const location119 = { lat: 60.7029, lng: 12.5936 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location119.lat && coordinate.lng === location119.lng;
});

let marker119;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker119 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location119.lat + getRandomOffset(),
            lng: location119.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker119 = new google.maps.marker.AdvancedMarkerElement({
        position: location119,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location119);
}

markers.push(marker119);

const infowindow119 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/119/bellyboat-fishing-in-northern-vrmland-in-680-61-bograngen-zweden"><h5 class="card-title" style="font-size: 14px;">Bellyboat-Angeln im nördlichen Värmland – halber Tag</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Hecht, Barsch, Regenbogenforelle
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/119/bellyboat-fishing-in-northern-vrmland-in-680-61-bograngen-zweden" style="padding:3px 7px;">ab 170€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow119);

marker119.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow119.open(map, marker119);
});
const location51 = { lat: 49.3173, lng: 8.44122 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location51.lat && coordinate.lng === location51.lng;
});

let marker51;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker51 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location51.lat + getRandomOffset(),
            lng: location51.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker51 = new google.maps.marker.AdvancedMarkerElement({
        position: location51,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location51);
}

markers.push(marker51);

const infowindow51 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/51/wallerangeln-am-rhein-altwasser-in-speyer-rheinland-pfalz-deutschland"><h5 class="card-title" style="font-size: 14px;">Wallerangeln am Rhein / Altwasser</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Wels
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss, See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/51/wallerangeln-am-rhein-altwasser-in-speyer-rheinland-pfalz-deutschland" style="padding:3px 7px;">ab 150€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow51);

marker51.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow51.open(map, marker51);
});
const location227 = { lat: 60.0309, lng: 11.3396 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location227.lat && coordinate.lng === location227.lng;
});

let marker227;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker227 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location227.lat + getRandomOffset(),
            lng: location227.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker227 = new google.maps.marker.AdvancedMarkerElement({
        position: location227,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location227);
}

markers.push(marker227);

const infowindow227 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/227/viking-pike-your-fishing-adventure-guide-in-eastern-norway-in-norwegen"><h5 class="card-title" style="font-size: 14px;">Viking Pike – Ihr Angel- und Abenteuerführer in Ostnorwegen</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Quappe, Makrele, Seelachs, Saibling, Meerforelle, Hecht, Pollack, Rapfen, Zander, Barsch, Ã„sche, Bachforelle
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            See, Bach, Fluss, Meer, Hafen, Talsperre
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/227/viking-pike-your-fishing-adventure-guide-in-eastern-norway-in-norwegen" style="padding:3px 7px;">ab 150€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow227);

marker227.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow227.open(map, marker227);
});
const location234 = { lat: 49.864, lng: 10.2309 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location234.lat && coordinate.lng === location234.lng;
});

let marker234;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker234 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location234.lat + getRandomOffset(),
            lng: location234.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker234 = new google.maps.marker.AdvancedMarkerElement({
        position: location234,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location234);
}

markers.push(marker234);

const infowindow234 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/234/zweihand-fliegenfischen-anfnger-kurs-in-wrzburg-in-97332-volkach-deutschland"><h5 class="card-title" style="font-size: 14px;">Zweihand Fliegenfischen - AnfÃ¤nger Kurs in WÃ¼rzburg</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs an Land
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/234/zweihand-fliegenfischen-anfnger-kurs-in-wrzburg-in-97332-volkach-deutschland" style="padding:3px 7px;">ab 349€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow234);

marker234.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow234.open(map, marker234);
});
const location232 = { lat: 52.6367, lng: 9.84508 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location232.lat && coordinate.lng === location232.lng;
});

let marker232;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker232 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location232.lat + getRandomOffset(),
            lng: location232.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker232 = new google.maps.marker.AdvancedMarkerElement({
        position: location232,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location232);
}

markers.push(marker232);

const infowindow232 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/232/zweihand-fliegenfischen-anfnger-kurs-in-niedersachsen-in-niedersachsen-deutschland"><h5 class="card-title" style="font-size: 14px;">Zweihand Fliegenfischen - AnfÃ¤nger Kurs in Niedersachsen</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs an Land
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/232/zweihand-fliegenfischen-anfnger-kurs-in-niedersachsen-in-niedersachsen-deutschland" style="padding:3px 7px;">ab 349€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow232);

marker232.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow232.open(map, marker232);
});
const location168 = { lat: 32.7607, lng: -16.9595 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location168.lat && coordinate.lng === location168.lng;
});

let marker168;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker168 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location168.lat + getRandomOffset(),
            lng: location168.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker168 = new google.maps.marker.AdvancedMarkerElement({
        position: location168,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location168);
}

markers.push(marker168);

const infowindow168 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/168/big-game-fishing-in-madeira-portugal"><h5 class="card-title" style="font-size: 14px;">Big Game Fishing vor Madeira, Portugal – Halbtagestour</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Thunfisch, Mahi Mahi, Marlin
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/168/big-game-fishing-in-madeira-portugal" style="padding:3px 7px;">ab 680€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow168);

marker168.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow168.open(map, marker168);
});
const location45 = { lat: 50.0495, lng: 9.70593 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location45.lat && coordinate.lng === location45.lng;
});

let marker45;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker45 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location45.lat + getRandomOffset(),
            lng: location45.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker45 = new google.maps.marker.AdvancedMarkerElement({
        position: location45,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location45);
}

markers.push(marker45);

const infowindow45 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/45/barsch-zander-hecht-angeln-am-main-in-gemnden-am-main-deutschland"><h5 class="card-title" style="font-size: 14px;">Barsch, Zander, Hecht Angeln am Main</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Hecht, Zander
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/45/barsch-zander-hecht-angeln-am-main-in-gemnden-am-main-deutschland" style="padding:3px 7px;">ab 190€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow45);

marker45.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow45.open(map, marker45);
});
const location182 = { lat: 43.5141, lng: 16.1077 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location182.lat && coordinate.lng === location182.lng;
});

let marker182;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker182 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location182.lat + getRandomOffset(),
            lng: location182.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker182 = new google.maps.marker.AdvancedMarkerElement({
        position: location182,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location182);
}

markers.push(marker182);

const infowindow182 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/182/night-fishing-for-squid-and-other-target-fishes-in-croatia-in-marina-kroatien"><h5 class="card-title" style="font-size: 14px;">Nachtangeln auf Tintenfische und andere Zielfische in Kroatien</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Rotbarsch, Amberjack, Snapper
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/182/night-fishing-for-squid-and-other-target-fishes-in-croatia-in-marina-kroatien" style="padding:3px 7px;">ab 300€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow182);

marker182.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow182.open(map, marker182);
});
const location193 = { lat: 49.7592, lng: 6.51161 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location193.lat && coordinate.lng === location193.lng;
});

let marker193;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker193 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location193.lat + getRandomOffset(),
            lng: location193.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker193 = new google.maps.marker.AdvancedMarkerElement({
        position: location193,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location193);
}

markers.push(marker193);

const infowindow193 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/193/gefhrte-fliegenfischen-tour-auf-forelle-barbe-und-sche-in-born-rosport-mompach-luxemburg"><h5 class="card-title" style="font-size: 14px;">GefÃ¼hrte fliegenfischen Tour auf Forelle. Barbe und Ã„sche</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barbe, Ã„sche, Bachforelle, Regenbogenforelle
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Bach, Fluss, See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/193/gefhrte-fliegenfischen-tour-auf-forelle-barbe-und-sche-in-born-rosport-mompach-luxemburg" style="padding:3px 7px;">ab 349€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow193);

marker193.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow193.open(map, marker193);
});
const location225 = { lat: 51.4332, lng: 7.66159 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location225.lat && coordinate.lng === location225.lng;
});

let marker225;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker225 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location225.lat + getRandomOffset(),
            lng: location225.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker225 = new google.maps.marker.AdvancedMarkerElement({
        position: location225,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location225);
}

markers.push(marker225);

const infowindow225 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/225/fliegenfischen-lernen-in-nrw-in-nordrhein-westfalen-deutschland"><h5 class="card-title" style="font-size: 14px;">Fliegenfischen lernen in NRW</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs an Land
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/225/fliegenfischen-lernen-in-nrw-in-nordrhein-westfalen-deutschland" style="padding:3px 7px;">ab 349€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow225);

marker225.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow225.open(map, marker225);
});
const location106 = { lat: 52.4729, lng: 4.82185 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location106.lat && coordinate.lng === location106.lng;
});

let marker106;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker106 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location106.lat + getRandomOffset(),
            lng: location106.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker106 = new google.maps.marker.AdvancedMarkerElement({
        position: location106,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location106);
}

markers.push(marker106);

const infowindow106 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/106/vertikal-pelagisch-auf-zander-in-zaanse-schans-zaandam-niederlande"><h5 class="card-title" style="font-size: 14px;">Vertikal und Pelagisch auf Zander</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Zander
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Kanal
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/106/vertikal-pelagisch-auf-zander-in-zaanse-schans-zaandam-niederlande" style="padding:3px 7px;">ab 400€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow106);

marker106.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow106.open(map, marker106);
});
const location121 = { lat: 60.7029, lng: 12.5936 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location121.lat && coordinate.lng === location121.lng;
});

let marker121;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker121 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location121.lat + getRandomOffset(),
            lng: location121.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker121 = new google.maps.marker.AdvancedMarkerElement({
        position: location121,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location121);
}

markers.push(marker121);

const infowindow121 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/121/bellyboat-fishing-in-northern-vrmland-whole-day-in-680-61-bograngen-zweden"><h5 class="card-title" style="font-size: 14px;">Eisfischen im nördlichen Värieland</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Regenbogenforelle
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/121/bellyboat-fishing-in-northern-vrmland-whole-day-in-680-61-bograngen-zweden" style="padding:3px 7px;">ab 120€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow121);

marker121.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow121.open(map, marker121);
});
const location95 = { lat: 56.9027, lng: 12.4888 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location95.lat && coordinate.lng === location95.lng;
});

let marker95;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker95 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location95.lat + getRandomOffset(),
            lng: location95.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker95 = new google.maps.marker.AdvancedMarkerElement({
        position: location95,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location95);
}

markers.push(marker95);

const infowindow95 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/95/lakefishing-for-pike-perch-or-zander-in-falkenberg-sverige"><h5 class="card-title" style="font-size: 14px;">Seeangeln auf Hecht, Barsch oder Zander</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Hecht, Zander
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/95/lakefishing-for-pike-perch-or-zander-in-falkenberg-sverige" style="padding:3px 7px;">ab 550€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow95);

marker95.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow95.open(map, marker95);
});
const location245 = { lat: 52.6367, lng: 9.84508 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location245.lat && coordinate.lng === location245.lng;
});

let marker245;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker245 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location245.lat + getRandomOffset(),
            lng: location245.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker245 = new google.maps.marker.AdvancedMarkerElement({
        position: location245,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location245);
}

markers.push(marker245);

const infowindow245 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/245/fliegenfischen-lernen-in-niedersachsen-einzelkurs-individuelles-lernen-in-niedersachsen-deutschland"><h5 class="card-title" style="font-size: 14px;">Fliegenfischen lernen in Niedersachsen: Einzelkurs/Individuelles lernen</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs an Land
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/245/fliegenfischen-lernen-in-niedersachsen-einzelkurs-individuelles-lernen-in-niedersachsen-deutschland" style="padding:3px 7px;">ab 349€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow245);

marker245.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow245.open(map, marker245);
});
const location83 = { lat: 56.2243, lng: 12.6742 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location83.lat && coordinate.lng === location83.lng;
});

let marker83;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker83 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location83.lat + getRandomOffset(),
            lng: location83.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker83 = new google.maps.marker.AdvancedMarkerElement({
        position: location83,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location83);
}

markers.push(marker83);

const infowindow83 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/83/halber-tag-angelguiding-entlang-der-kste-von-sklderviken-bis-nach-kullaberg-in-jonstorp-schweden"><h5 class="card-title" style="font-size: 14px;">Halber Tag - Angelguiding entlang der KÃ¼ste von SkÃ¤lderviken bis nach Kullaberg</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Hecht, Dorsch, Makrele, Meerforelle, Seelachs
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/83/halber-tag-angelguiding-entlang-der-kste-von-sklderviken-bis-nach-kullaberg-in-jonstorp-schweden" style="padding:3px 7px;">ab 345€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow83);

marker83.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow83.open(map, marker83);
});
const location84 = { lat: 56.2243, lng: 12.6742 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location84.lat && coordinate.lng === location84.lng;
});

let marker84;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker84 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location84.lat + getRandomOffset(),
            lng: location84.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker84 = new google.maps.marker.AdvancedMarkerElement({
        position: location84,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location84);
}

markers.push(marker84);

const infowindow84 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/84/ganzer-tag-angelguiding-entlang-der-kste-von-sklderviken-bis-nach-kullaberg-in-jonstorp-schweden"><h5 class="card-title" style="font-size: 14px;">Ganzer Tag - Angelguiding entlang der KÃ¼ste von SkÃ¤lderviken bis nach Kullaberg</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Hecht, Makrele, Meerforelle, Seelachs
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/84/ganzer-tag-angelguiding-entlang-der-kste-von-sklderviken-bis-nach-kullaberg-in-jonstorp-schweden" style="padding:3px 7px;">ab 431€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow84);

marker84.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow84.open(map, marker84);
});
const location235 = { lat: 50.1277, lng: 8.60768 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location235.lat && coordinate.lng === location235.lng;
});

let marker235;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker235 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location235.lat + getRandomOffset(),
            lng: location235.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker235 = new google.maps.marker.AdvancedMarkerElement({
        position: location235,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location235);
}

markers.push(marker235);

const infowindow235 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/235/fliegenfischen-kurs-in-frankfurt-rdelheim-fliegenfischerkurs-hessen-in-frankfurt-rdelheim-60-frankfurt-am-main-mitte-west-deutschland"><h5 class="card-title" style="font-size: 14px;">Fliegenfischen Kurs in Frankfurt (RÃ¶delheim) / Fliegenfischerkurs Hessen</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs an Land
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/235/fliegenfischen-kurs-in-frankfurt-rdelheim-fliegenfischerkurs-hessen-in-frankfurt-rdelheim-60-frankfurt-am-main-mitte-west-deutschland" style="padding:3px 7px;">ab 199€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow235);

marker235.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow235.open(map, marker235);
});
const location224 = { lat: 39.765, lng: 3.15412 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location224.lat && coordinate.lng === location224.lng;
});

let marker224;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker224 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location224.lat + getRandomOffset(),
            lng: location224.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker224 = new google.maps.marker.AdvancedMarkerElement({
        position: location224,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location224);
}

markers.push(marker224);

const infowindow224 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/224/half-day-light-trolling-bottom-fishing-tour-shared-trip-in-07458-can-picafort-illes-balears-spanien"><h5 class="card-title" style="font-size: 14px;">Halbtägige leichte Trolling- und Grundangeltour – gemeinsamer Ausflug</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Thunfisch, Amberjack, Barrakuda, Dentex, Jon Dory, Drachenkopf, Meerbrasse, Blauflossenthun, Bonitos, Zackenbarsch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/224/half-day-light-trolling-bottom-fishing-tour-shared-trip-in-07458-can-picafort-illes-balears-spanien" style="padding:3px 7px;">ab 150€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow224);

marker224.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow224.open(map, marker224);
});
const location210 = { lat: 52.1326, lng: 5.29127 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location210.lat && coordinate.lng === location210.lng;
});

let marker210;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker210 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location210.lat + getRandomOffset(),
            lng: location210.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker210 = new google.maps.marker.AdvancedMarkerElement({
        position: location210,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location210);
}

markers.push(marker210);

const infowindow210 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/210/hechte-in-den-poldern-nl-tagestour-8h-in-niederlande"><h5 class="card-title" style="font-size: 14px;">Hechte in den Poldern NL - Tagestour (8h)</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Hecht
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Bach, Fluss
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/210/hechte-in-den-poldern-nl-tagestour-8h-in-niederlande" style="padding:3px 7px;">ab 360€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow210);

marker210.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow210.open(map, marker210);
});
const location143 = { lat: 37.6415, lng: -7.66067 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location143.lat && coordinate.lng === location143.lng;
});

let marker143;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker143 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location143.lat + getRandomOffset(),
            lng: location143.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker143 = new google.maps.marker.AdvancedMarkerElement({
        position: location143,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location143);
}

markers.push(marker143);

const infowindow143 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/143/freshwater-guide-fly-lure-bait-fishing-black-bass-zander-barbel-carp-catfish-in-7750-mrtola-portugal"><h5 class="card-title" style="font-size: 14px;">Süßwasser-Guiding für Karpfen, Barben und mehr in Portugal</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Karpfen, Barbe
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Talsperre, See, Fluss
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/143/freshwater-guide-fly-lure-bait-fishing-black-bass-zander-barbel-carp-catfish-in-7750-mrtola-portugal" style="padding:3px 7px;">ab 130€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow143);

marker143.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow143.open(map, marker143);
});
const location206 = { lat: 37.3166, lng: -8.79926 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location206.lat && coordinate.lng === location206.lng;
});

let marker206;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker206 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location206.lat + getRandomOffset(),
            lng: location206.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker206 = new google.maps.marker.AdvancedMarkerElement({
        position: location206,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location206);
}

markers.push(marker206);

const infowindow206 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/206/private-rock-fishing-tour-at-the-coast-of-the-vicentine-natural-park-in-8670-aljezur-portugal"><h5 class="card-title" style="font-size: 14px;">Private Felsangeltour an der Küste des Vicentine Natural Park</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Dorade, Wolfsbarsch, Brassen
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/206/private-rock-fishing-tour-at-the-coast-of-the-vicentine-natural-park-in-8670-aljezur-portugal" style="padding:3px 7px;">ab 267€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow206);

marker206.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow206.open(map, marker206);
});
const location238 = { lat: 52.5955, lng: 13.333 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location238.lat && coordinate.lng === location238.lng;
});

let marker238;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker238 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location238.lat + getRandomOffset(),
            lng: location238.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker238 = new google.maps.marker.AdvancedMarkerElement({
        position: location238,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location238);
}

markers.push(marker238);

const infowindow238 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/238/fliegenfischen-kurs-in-berlin-wittenau-fliegenfischerkurs-berlin-in-wittenau-13-berlin-bezirk-reinickendorf-deutschland"><h5 class="card-title" style="font-size: 14px;">Fliegenfischen Kurs in Berlin (Wittenau) / Fliegenfischerkurs Berlin</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs an Land
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/238/fliegenfischen-kurs-in-berlin-wittenau-fliegenfischerkurs-berlin-in-wittenau-13-berlin-bezirk-reinickendorf-deutschland" style="padding:3px 7px;">ab 199€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow238);

marker238.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow238.open(map, marker238);
});
const location56 = { lat: 56.5514, lng: 14.1369 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location56.lat && coordinate.lng === location56.lng;
});

let marker56;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker56 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location56.lat + getRandomOffset(),
            lng: location56.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker56 = new google.maps.marker.AdvancedMarkerElement({
        position: location56,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location56);
}

markers.push(marker56);

const infowindow56 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/56/predator-fishing-whole-day-approx-8-hours-in-lmhult-lmhult-sweden"><h5 class="card-title" style="font-size: 14px;">Raubfischangeln den ganzen Tag (ca. 8 Stunden)</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Hecht, Zander
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss, See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/56/predator-fishing-whole-day-approx-8-hours-in-lmhult-lmhult-sweden" style="padding:3px 7px;">ab 280€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow56);

marker56.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow56.open(map, marker56);
});
const location117 = { lat: 37.5129, lng: -8.47575 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location117.lat && coordinate.lng === location117.lng;
});

let marker117;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker117 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location117.lat + getRandomOffset(),
            lng: location117.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker117 = new google.maps.marker.AdvancedMarkerElement({
        position: location117,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location117);
}

markers.push(marker117);

const infowindow117 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/117/predador-fishing-3-day-trip-in-portugal"><h5 class="card-title" style="font-size: 14px;">Raubfischangeln – 3-tägiger Ausflug (alles inklusive)</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Schwarzbarsch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/117/predador-fishing-3-day-trip-in-portugal" style="padding:3px 7px;">ab 1200€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow117);

marker117.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow117.open(map, marker117);
});
const location170 = { lat: 32.7607, lng: -16.9595 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location170.lat && coordinate.lng === location170.lng;
});

let marker170;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker170 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location170.lat + getRandomOffset(),
            lng: location170.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker170 = new google.maps.marker.AdvancedMarkerElement({
        position: location170,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location170);
}

markers.push(marker170);

const infowindow170 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/170/bottom-fishing-in-madeira-portugal"><h5 class="card-title" style="font-size: 14px;">Grundangeln vor Madeira – Halbtagestour</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Mahi Mahi, Amberjack, Snapper
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/170/bottom-fishing-in-madeira-portugal" style="padding:3px 7px;">ab 620€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow170);

marker170.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow170.open(map, marker170);
});
const location136 = { lat: 27.9202, lng: -15.5474 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location136.lat && coordinate.lng === location136.lng;
});

let marker136;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker136 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location136.lat + getRandomOffset(),
            lng: location136.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker136 = new google.maps.marker.AdvancedMarkerElement({
        position: location136,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location136);
}

markers.push(marker136);

const infowindow136 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/136/big-game-fishing-vor-gran-canaria-marlin-bluefin-tuna-eskolar-mahi-mahi-wahoo-in-gran-canaria-provinz-las-palmas-spanien"><h5 class="card-title" style="font-size: 14px;">Big Game Fishing vor Gran Canaria Marlin Bluefin Thunfisch Eskolar Mahi Mahi Wahoo</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Brassen, Barsch, Thunfisch, Mahi Mahi
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/136/big-game-fishing-vor-gran-canaria-marlin-bluefin-tuna-eskolar-mahi-mahi-wahoo-in-gran-canaria-provinz-las-palmas-spanien" style="padding:3px 7px;">ab 1000€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow136);

marker136.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow136.open(map, marker136);
});
const location236 = { lat: 51.2562, lng: 7.15076 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location236.lat && coordinate.lng === location236.lng;
});

let marker236;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker236 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location236.lat + getRandomOffset(),
            lng: location236.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker236 = new google.maps.marker.AdvancedMarkerElement({
        position: location236,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location236);
}

markers.push(marker236);

const infowindow236 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/236/fliegenfischen-kurs-in-wuppertal-fliegenfischerkurs-nrw-in-42-wuppertal-deutschland"><h5 class="card-title" style="font-size: 14px;">Fliegenfischen Kurs in Wuppertal / Fliegenfischerkurs Nrw</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs an Land
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/236/fliegenfischen-kurs-in-wuppertal-fliegenfischerkurs-nrw-in-42-wuppertal-deutschland" style="padding:3px 7px;">ab 199€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow236);

marker236.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow236.open(map, marker236);
});
const location126 = { lat: 41.6177, lng: 0.620021 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location126.lat && coordinate.lng === location126.lng;
});

let marker126;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker126 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location126.lat + getRandomOffset(),
            lng: location126.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker126 = new google.maps.marker.AdvancedMarkerElement({
        position: location126,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location126);
}

markers.push(marker126);

const infowindow126 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/126/gross-forellenangeln-im-wilden-spanischen-fluss-in-lleida-provinz-lleida-spanien"><h5 class="card-title" style="font-size: 14px;">GroÃŸ-Forellenangeln im wilden spanischen Fluss</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Bachforelle, Regenbogenforelle
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/126/gross-forellenangeln-im-wilden-spanischen-fluss-in-lleida-provinz-lleida-spanien" style="padding:3px 7px;">ab 200€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow126);

marker126.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow126.open(map, marker126);
});
const location167 = { lat: 32.7607, lng: -16.9595 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location167.lat && coordinate.lng === location167.lng;
});

let marker167;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker167 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location167.lat + getRandomOffset(),
            lng: location167.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker167 = new google.maps.marker.AdvancedMarkerElement({
        position: location167,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location167);
}

markers.push(marker167);

const infowindow167 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/167/big-game-fishing-na-ilha-da-madeira-in-madeira-portugal"><h5 class="card-title" style="font-size: 14px;">Hochseefischen auf der Insel Madeira</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Thunfisch, Makrele, Marlin
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/167/big-game-fishing-na-ilha-da-madeira-in-madeira-portugal" style="padding:3px 7px;">ab 1100€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow167);

marker167.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow167.open(map, marker167);
});
const location155 = { lat: 37.8218, lng: -25.4282 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location155.lat && coordinate.lng === location155.lng;
});

let marker155;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker155 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location155.lat + getRandomOffset(),
            lng: location155.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker155 = new google.maps.marker.AdvancedMarkerElement({
        position: location155,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location155);
}

markers.push(marker155);

const infowindow155 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/155/jigging-and-others-in-s-miguel-azores-boat-charter-7-h-in-9625-porto-formoso-portugal"><h5 class="card-title" style="font-size: 14px;">Jigging und andere Aktivitäten in S. Miguel Azoren – Bootscharter 7 Stunden</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Makrele, Pollack, Giebel, Brassen, Flunder, Mahi Mahi, Rotbarsch, Thunfisch, Dorade, Seelachs
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/155/jigging-and-others-in-s-miguel-azores-boat-charter-7-h-in-9625-porto-formoso-portugal" style="padding:3px 7px;">ab 450€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow155);

marker155.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow155.open(map, marker155);
});
const location237 = { lat: 52.4125, lng: 12.5316 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location237.lat && coordinate.lng === location237.lng;
});

let marker237;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker237 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location237.lat + getRandomOffset(),
            lng: location237.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker237 = new google.maps.marker.AdvancedMarkerElement({
        position: location237,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location237);
}

markers.push(marker237);

const infowindow237 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/237/fliegenfischen-kurs-in-brandenburg-an-der-havel-in-14-brandenburg-an-der-havel-deutschland"><h5 class="card-title" style="font-size: 14px;">Fliegenfischen Kurs in Brandenburg an der Havel</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs an Land
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/237/fliegenfischen-kurs-in-brandenburg-an-der-havel-in-14-brandenburg-an-der-havel-deutschland" style="padding:3px 7px;">ab 199€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow237);

marker237.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow237.open(map, marker237);
});
const location75 = { lat: 52.1326, lng: 5.29127 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location75.lat && coordinate.lng === location75.lng;
});

let marker75;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker75 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location75.lat + getRandomOffset(),
            lng: location75.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker75 = new google.maps.marker.AdvancedMarkerElement({
        position: location75,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location75);
}

markers.push(marker75);

const infowindow75 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/75/boots-guiding-in-den-niederlanden-in-niederlande"><h5 class="card-title" style="font-size: 14px;">Boots-Guiding in den Niederlanden</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Hecht, Rapfen, Zander
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss, See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/75/boots-guiding-in-den-niederlanden-in-niederlande" style="padding:3px 7px;">ab 380€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow75);

marker75.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow75.open(map, marker75);
});
const location230 = { lat: 48.6616, lng: 9.35013 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location230.lat && coordinate.lng === location230.lng;
});

let marker230;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker230 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location230.lat + getRandomOffset(),
            lng: location230.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker230 = new google.maps.marker.AdvancedMarkerElement({
        position: location230,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location230);
}

markers.push(marker230);

const infowindow230 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/230/fliegenfischen-lernen-in-baden-wrttemberg-in-baden-wrttemberg-deutschland"><h5 class="card-title" style="font-size: 14px;">Fliegenfischen lernen in Baden-WÃ¼rttemberg: Einzelkurs/Individuelles lernen</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Angelkurs an Land
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/230/fliegenfischen-lernen-in-baden-wrttemberg-in-baden-wrttemberg-deutschland" style="padding:3px 7px;">ab 349€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow230);

marker230.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow230.open(map, marker230);
});
const location62 = { lat: 54.3486, lng: 10.6051 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location62.lat && coordinate.lng === location62.lng;
});

let marker62;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker62 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location62.lat + getRandomOffset(),
            lng: location62.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker62 = new google.maps.marker.AdvancedMarkerElement({
        position: location62,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location62);
}

markers.push(marker62);

const infowindow62 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/62/plattfisch-guiding-in-der-hohwachter-bucht-ab-3-personen-in-24321-behrensdorf-deutschland"><h5 class="card-title" style="font-size: 14px;">Plattfisch Guiding in der Hohwachter Bucht ab 3 Personen</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Flunder, Kliesche, Meerforelle, Scholle
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/62/plattfisch-guiding-in-der-hohwachter-bucht-ab-3-personen-in-24321-behrensdorf-deutschland" style="padding:3px 7px;">ab 99€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow62);

marker62.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow62.open(map, marker62);
});
const location39 = { lat: 51.4427, lng: 6.06087 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location39.lat && coordinate.lng === location39.lng;
});

let marker39;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker39 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location39.lat + getRandomOffset(),
            lng: location39.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker39 = new google.maps.marker.AdvancedMarkerElement({
        position: location39,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location39);
}

markers.push(marker39);

const infowindow39 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/39/raubfischangeln-vom-boot-mit-modernster-technik-in-limburg-niederlande"><h5 class="card-title" style="font-size: 14px;">Raubfischangeln vom Boot mit modernster Technik</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Hecht, Rapfen, Zander
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss, See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/39/raubfischangeln-vom-boot-mit-modernster-technik-in-limburg-niederlande" style="padding:3px 7px;">ab 225€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow39);

marker39.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow39.open(map, marker39);
});
const location211 = { lat: 39.3302, lng: 3.16855 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location211.lat && coordinate.lng === location211.lng;
});

let marker211;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker211 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location211.lat + getRandomOffset(),
            lng: location211.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker211 = new google.maps.marker.AdvancedMarkerElement({
        position: location211,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location211);
}

markers.push(marker211);

const infowindow211 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/211/offshore-big-game-trolling-for-mediteranean-spearfish-bonito-skipjack-little-thunny-mahi-mahi-goldmakrele-atlantic-bonito-bluefintuna-in-07659-cala-figuera-balearen-spanien"><h5 class="card-title" style="font-size: 14px;">Offshore-Großwildschleppangeln auf Speerfische im Mittelmeer, Bonito (Bonito), Zwergthunny, Mahi Mahi (Goldmakrele), Atlantischer Bonito und Blauflossenthun</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Mahi Mahi, Thunfisch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/211/offshore-big-game-trolling-for-mediteranean-spearfish-bonito-skipjack-little-thunny-mahi-mahi-goldmakrele-atlantic-bonito-bluefintuna-in-07659-cala-figuera-balearen-spanien" style="padding:3px 7px;">ab 1700€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow211);

marker211.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow211.open(map, marker211);
});
const location148 = { lat: 37.8218, lng: -25.4282 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location148.lat && coordinate.lng === location148.lng;
});

let marker148;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker148 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location148.lat + getRandomOffset(),
            lng: location148.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker148 = new google.maps.marker.AdvancedMarkerElement({
        position: location148,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location148);
}

markers.push(marker148);

const infowindow148 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/148/jigging-in-s-miguel-azores-in-9625-porto-formoso-portugal"><h5 class="card-title" style="font-size: 14px;">Jigging und andere in S. Miguel Azoren</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Schwarzbarsch, Makrele, Giebel, Brassen, Flunder, Mahi Mahi, Rotbarsch, Wolfsbarsch, Thunfisch, Dorade, Seelachs
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/148/jigging-in-s-miguel-azores-in-9625-porto-formoso-portugal" style="padding:3px 7px;">ab 100€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow148);

marker148.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow148.open(map, marker148);
});
const location86 = { lat: 56.2756, lng: 12.8385 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location86.lat && coordinate.lng === location86.lng;
});

let marker86;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker86 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location86.lat + getRandomOffset(),
            lng: location86.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker86 = new google.maps.marker.AdvancedMarkerElement({
        position: location86,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location86);
}

markers.push(marker86);

const infowindow86 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/86/1-2-tag-kstenguiding-auf-meerforelle-in-schweden-in-sklderviken-schweden"><h5 class="card-title" style="font-size: 14px;">1/2 Tag - KÃ¼stenguiding auf Meerforelle in Schweden</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meerforelle
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/86/1-2-tag-kstenguiding-auf-meerforelle-in-schweden-in-sklderviken-schweden" style="padding:3px 7px;">ab 240€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow86);

marker86.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow86.open(map, marker86);
});
const location93 = { lat: 49.9821, lng: 7.93011 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location93.lat && coordinate.lng === location93.lng;
});

let marker93;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker93 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location93.lat + getRandomOffset(),
            lng: location93.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker93 = new google.maps.marker.AdvancedMarkerElement({
        position: location93,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location93);
}

markers.push(marker93);

const infowindow93 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/93/zanderangeln-am-deutschen-rhein-in-rdesheim-am-rhein-deutschland"><h5 class="card-title" style="font-size: 14px;">Zanderangeln am deutschen Rhein</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Rapfen, Zander
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/93/zanderangeln-am-deutschen-rhein-in-rdesheim-am-rhein-deutschland" style="padding:3px 7px;">ab 300€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow93);

marker93.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow93.open(map, marker93);
});
const location189 = { lat: 52.1314, lng: 13.2162 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location189.lat && coordinate.lng === location189.lng;
});

let marker189;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker189 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location189.lat + getRandomOffset(),
            lng: location189.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker189 = new google.maps.marker.AdvancedMarkerElement({
        position: location189,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location189);
}

markers.push(marker189);

const infowindow189 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/189/gefhrtes-fliegenfischen-auf-hecht-forelle-sche-in-brandenburg-deutschland"><h5 class="card-title" style="font-size: 14px;">GefÃ¼hrtes Fliegenfischen auf Hecht, Forelle &amp; Ã„sche</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Ã„sche, Hecht, Regenbogenforelle, Bachforelle
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Bach, Fluss, See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/189/gefhrtes-fliegenfischen-auf-hecht-forelle-sche-in-brandenburg-deutschland" style="padding:3px 7px;">ab 349€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow189);

marker189.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow189.open(map, marker189);
});
const location7 = { lat: 52.6381, lng: 9.20842 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location7.lat && coordinate.lng === location7.lng;
});

let marker7;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker7 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location7.lat + getRandomOffset(),
            lng: location7.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker7 = new google.maps.marker.AdvancedMarkerElement({
        position: location7,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location7);
}

markers.push(marker7);

const infowindow7 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/7/wels-guiding-in-niedersachsen-in-nienburg-weser-deutschland"><h5 class="card-title" style="font-size: 14px;">Wels guiding in Niedersachsen</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Wels
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/7/wels-guiding-in-niedersachsen-in-nienburg-weser-deutschland" style="padding:3px 7px;">ab 350€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow7);

marker7.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow7.open(map, marker7);
});
const location46 = { lat: 50.0495, lng: 9.70593 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location46.lat && coordinate.lng === location46.lng;
});

let marker46;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker46 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location46.lat + getRandomOffset(),
            lng: location46.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker46 = new google.maps.marker.AdvancedMarkerElement({
        position: location46,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location46);
}

markers.push(marker46);

const infowindow46 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/46/spinnfischen-am-bach-in-gemnden-am-main-deutschland"><h5 class="card-title" style="font-size: 14px;">Spinnfischen am Bach</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Bachforelle, Barsch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Bach
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/46/spinnfischen-am-bach-in-gemnden-am-main-deutschland" style="padding:3px 7px;">ab 200€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow46);

marker46.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow46.open(map, marker46);
});
const location173 = { lat: 61.2358, lng: 14.0345 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location173.lat && coordinate.lng === location173.lng;
});

let marker173;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker173 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location173.lat + getRandomOffset(),
            lng: location173.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker173 = new google.maps.marker.AdvancedMarkerElement({
        position: location173,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location173);
}

markers.push(marker173);

const infowindow173 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/173/grayling-autumn-fly-fishing-in-dalarna-sweden-in-dalgatan-146-796-30-lvdalen-sverige"><h5 class="card-title" style="font-size: 14px;">Herbstliches Fliegenfischen auf Äschen in Dalarna, Schweden</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Ã„sche
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Bach, Fluss
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/173/grayling-autumn-fly-fishing-in-dalarna-sweden-in-dalgatan-146-796-30-lvdalen-sverige" style="padding:3px 7px;">ab 455€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow173);

marker173.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow173.open(map, marker173);
});
const location52 = { lat: 51.691, lng: 4.21268 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location52.lat && coordinate.lng === location52.lng;
});

let marker52;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker52 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location52.lat + getRandomOffset(),
            lng: location52.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker52 = new google.maps.marker.AdvancedMarkerElement({
        position: location52,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location52);
}

markers.push(marker52);

const infowindow52 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/52/grosszander-tour-am-volkerak-in-oude-tonge-niederlande"><h5 class="card-title" style="font-size: 14px;">Großzander Tour am Volkerak</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Zander
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            See, Talsperre
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/52/grosszander-tour-am-volkerak-in-oude-tonge-niederlande" style="padding:3px 7px;">ab 450€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow52);

marker52.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow52.open(map, marker52);
});
const location158 = { lat: 37.7412, lng: -25.6756 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location158.lat && coordinate.lng === location158.lng;
});

let marker158;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker158 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location158.lat + getRandomOffset(),
            lng: location158.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker158 = new google.maps.marker.AdvancedMarkerElement({
        position: location158,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location158);
}

markers.push(marker158);

const infowindow158 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/158/ocean-coastal-fishing-1-mile-off-the-coast-of-sao-miguel-island-in-azores-portugal"><h5 class="card-title" style="font-size: 14px;">Hochseeangeln an der Küste 1,6 km vor der Küste der Insel Sao Miguel</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Wolfsbarsch, Thunfisch, Makrele, Mahi Mahi, Bonitos, Zackenbarsch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/158/ocean-coastal-fishing-1-mile-off-the-coast-of-sao-miguel-island-in-azores-portugal" style="padding:3px 7px;">ab 450€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow158);

marker158.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow158.open(map, marker158);
});
const location199 = { lat: 48.3705, lng: 10.8978 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location199.lat && coordinate.lng === location199.lng;
});

let marker199;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker199 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location199.lat + getRandomOffset(),
            lng: location199.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker199 = new google.maps.marker.AdvancedMarkerElement({
        position: location199,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location199);
}

markers.push(marker199);

const infowindow199 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/199/forellenangeln-mit-der-fliegenrute-in-86-augsburg-deutschland"><h5 class="card-title" style="font-size: 14px;">Forellenangeln mit der Fliegenrute</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Bachforelle, Barbe, Karpfen, Saibling
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Bach
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/199/forellenangeln-mit-der-fliegenrute-in-86-augsburg-deutschland" style="padding:3px 7px;">ab 349€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow199);

marker199.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow199.open(map, marker199);
});
const location69 = { lat: 51.6685, lng: 9.37228 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location69.lat && coordinate.lng === location69.lng;
});

let marker69;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker69 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location69.lat + getRandomOffset(),
            lng: location69.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker69 = new google.maps.marker.AdvancedMarkerElement({
        position: location69,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location69);
}

markers.push(marker69);

const infowindow69 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/69/raubfischangeln-pur-zugeschnitten-auf-deine-wnsche-angeln-im-weserbergland-in-beverungen-deutschland"><h5 class="card-title" style="font-size: 14px;">Raubfischangeln PUR - Zugeschnitten auf deine WÃ¼nsche! Angeln im Weserbergland</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Bachforelle, Barsch, Hecht, Rapfen, Zander
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss, See, Bach
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/69/raubfischangeln-pur-zugeschnitten-auf-deine-wnsche-angeln-im-weserbergland-in-beverungen-deutschland" style="padding:3px 7px;">ab 120€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow69);

marker69.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow69.open(map, marker69);
});
const location48 = { lat: 51.6771, lng: 4.21013 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location48.lat && coordinate.lng === location48.lng;
});

let marker48;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker48 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location48.lat + getRandomOffset(),
            lng: location48.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker48 = new google.maps.marker.AdvancedMarkerElement({
        position: location48,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location48);
}

markers.push(marker48);

const infowindow48 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/48/grossbarschangeln-auf-dem-volkerak-in-suisendijk-14-oude-tonge-niederlande"><h5 class="card-title" style="font-size: 14px;">GroÃŸbarschangeln auf dem Volkerak</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss, See, Hafen
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/48/grossbarschangeln-auf-dem-volkerak-in-suisendijk-14-oude-tonge-niederlande" style="padding:3px 7px;">ab 600€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow48);

marker48.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow48.open(map, marker48);
});
const location57 = { lat: 53.5678, lng: 13.2779 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location57.lat && coordinate.lng === location57.lng;
});

let marker57;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker57 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location57.lat + getRandomOffset(),
            lng: location57.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker57 = new google.maps.marker.AdvancedMarkerElement({
        position: location57,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location57);
}

markers.push(marker57);

const infowindow57 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/57/hechtguiding-in-der-seenplatte-in-neubrandenburg-deutschland"><h5 class="card-title" style="font-size: 14px;">Hechtguiding in der Seenplatte</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Hecht
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/57/hechtguiding-in-der-seenplatte-in-neubrandenburg-deutschland" style="padding:3px 7px;">ab 300€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow57);

marker57.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow57.open(map, marker57);
});
const location40 = { lat: 51.4697, lng: 4.45486 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location40.lat && coordinate.lng === location40.lng;
});

let marker40;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker40 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location40.lat + getRandomOffset(),
            lng: location40.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker40 = new google.maps.marker.AdvancedMarkerElement({
        position: location40,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location40);
}

markers.push(marker40);

const infowindow40 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/40/raubfischangeln-vom-boot-mit-modernster-technik-in-hollandsdiep-niederlande"><h5 class="card-title" style="font-size: 14px;">Raubfischangeln vom Boot mit modernster Technik</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Hecht, Rapfen, Zander
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Fluss, See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/40/raubfischangeln-vom-boot-mit-modernster-technik-in-hollandsdiep-niederlande" style="padding:3px 7px;">ab 425€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow40);

marker40.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow40.open(map, marker40);
});
const location122 = { lat: 60.7029, lng: 12.5936 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location122.lat && coordinate.lng === location122.lng;
});

let marker122;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker122 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location122.lat + getRandomOffset(),
            lng: location122.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker122 = new google.maps.marker.AdvancedMarkerElement({
        position: location122,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location122);
}

markers.push(marker122);

const infowindow122 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/122/bellyboatfishing-with-quadtour-in-680-61-bograngen-zweden"><h5 class="card-title" style="font-size: 14px;">Bellyboatangeln mit Quadtour</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barsch, Hecht, Regenbogenforelle
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            See
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/122/bellyboatfishing-with-quadtour-in-680-61-bograngen-zweden" style="padding:3px 7px;">ab 450€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow122);

marker122.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow122.open(map, marker122);
});
const location200 = { lat: 48.2775, lng: 8.186 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location200.lat && coordinate.lng === location200.lng;
});

let marker200;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker200 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location200.lat + getRandomOffset(),
            lng: location200.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker200 = new google.maps.marker.AdvancedMarkerElement({
        position: location200,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location200);
}

markers.push(marker200);

const infowindow200 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/200/fliegenfischen-im-schwarzwald-der-schwbischen-alb-in-schwarzwald-deutschland"><h5 class="card-title" style="font-size: 14px;">Fliegenfischen im Schwarzwald und der SchwÃ¤bisschen Alb</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Barbe, Hecht, Bachforelle, Ã„sche
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Bach
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/200/fliegenfischen-im-schwarzwald-der-schwbischen-alb-in-schwarzwald-deutschland" style="padding:3px 7px;">ab 349€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow200);

marker200.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow200.open(map, marker200);
});
const location154 = { lat: 37.8218, lng: -25.4282 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location154.lat && coordinate.lng === location154.lng;
});

let marker154;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker154 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location154.lat + getRandomOffset(),
            lng: location154.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker154 = new google.maps.marker.AdvancedMarkerElement({
        position: location154,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location154);
}

markers.push(marker154);

const infowindow154 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/154/jigging-and-others-in-s-miguel-azores-boat-charter-4-h-in-9625-porto-formoso-portugal"><h5 class="card-title" style="font-size: 14px;">Jigging und andere Aktivitäten in S. Miguel Azoren – Bootscharter 4 Stunden</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Makrele, Pollack, Giebel, Brassen, Flunder, Mahi Mahi, Rotbarsch, Thunfisch, Dorade
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/154/jigging-and-others-in-s-miguel-azores-boat-charter-4-h-in-9625-porto-formoso-portugal" style="padding:3px 7px;">ab 300€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow154);

marker154.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow154.open(map, marker154);
});
const location79 = { lat: 54.2194, lng: 9.69612 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location79.lat && coordinate.lng === location79.lng;
});

let marker79;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker79 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location79.lat + getRandomOffset(),
            lng: location79.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker79 = new google.maps.marker.AdvancedMarkerElement({
        position: location79,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location79);
}

markers.push(marker79);

const infowindow79 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/79/meerforelle-hornhecht-an-der-ostsee-in-schleswig-holstein-deutschland"><h5 class="card-title" style="font-size: 14px;">Meerforelle &amp; Hornhecht an der Ostsee</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meerforelle
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/79/meerforelle-hornhecht-an-der-ostsee-in-schleswig-holstein-deutschland" style="padding:3px 7px;">ab 250€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow79);

marker79.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow79.open(map, marker79);
});
const location151 = { lat: 44.2822, lng: 15.3478 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location151.lat && coordinate.lng === location151.lng;
});

let marker151;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker151 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location151.lat + getRandomOffset(),
            lng: location151.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker151 = new google.maps.marker.AdvancedMarkerElement({
        position: location151,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location151);
}

markers.push(marker151);

const infowindow151 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/151/morning-fishing-catch-clean-cook-in-23248-raanac-hrvatska"><h5 class="card-title" style="font-size: 14px;">Morgenfischen + Fangen, Putzen und Kochen</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Brassen, Giebel, Rotbarsch, Makrele
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/151/morning-fishing-catch-clean-cook-in-23248-raanac-hrvatska" style="padding:3px 7px;">ab 270€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow151);

marker151.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow151.open(map, marker151);
});
const location215 = { lat: 39.3302, lng: 3.16855 };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location215.lat && coordinate.lng === location215.lng;
});

let marker215;

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker215 = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location215.lat + getRandomOffset(),
            lng: location215.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker215 = new google.maps.marker.AdvancedMarkerElement({
        position: location215,
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location215);
}

markers.push(marker215);

const infowindow215 = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="http://cag.test/guidings/215/family-bottom-fishing-for-different-delicious-species-in-07659-cala-figuera-balearen-spanien"><h5 class="card-title" style="font-size: 14px;">Familiengrundangeln auf verschiedene köstliche Arten</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/fish.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Amberjack, Hering, MeerÃ¤sche, Makrele
                                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="http://cag.test/assets/images/icons/water-waves.png" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                                        
                                            Meer
                                    </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="http://cag.test/guidings/215/family-bottom-fishing-for-different-delicious-species-in-07659-cala-figuera-balearen-spanien" style="padding:3px 7px;">ab 1300€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow215);

marker215.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow215.open(map, marker215);
});
    

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
    var placeLatitude = ''; // Replace with the actual value from the request
    var placeLongitude = ''; // Replace with the actual value from the request

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
@endsection
