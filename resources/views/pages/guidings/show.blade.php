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
      
        @media screen and (max-width: 767px) {
            .price-details{
                display:none;
            }
        }

    </style>
@endsection

@section('custom_style')
@include('layouts.schema.single-listing')
@endsection

@section('content')
    <!--Tour Details End-->
    @if($agent->ismobile())
        <div style="position: fixed; bottom: 0; z-index: 9999999; -webkit-transform: translate3d(0,0,0);
transform: translate3d(0,0,0); width: 100%;">
            @include('pages.guidings.content.bookguiding')
        </div>
    @endif
    <section class="tour-details">

        <div class="tour-details__top">

            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <div class="about-one__left mb-3 mb-md-0">
                            <div class="about-one__img-box">
                                <div class="about-one__img">
                                    <div id="carouselExampleControls-{{$guiding->id}}" class="carousel slide" data-bs-ride="carousel" data-bs-interval="false">
                                        <div class="carousel-inner">
                                            @if(count(get_galleries_image_link($guiding)))
                                                @foreach(get_galleries_image_link($guiding) as $index => $gallery_image_link)
                                                    <div class="carousel-item @if($index == 0) active @endif">
                                                        <img  class="d-block w-100" src="{{asset($gallery_image_link)}}">
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
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-12">
                                <span class="tour-details-two__location">
                                    <div class="text">
                                        {{$guiding->location}}
                                    </div>
                                </span>
                                <h1 class="tour-details-two__title">{{ $guiding->title ? translate($guiding->title) : null }}</h1>

                                <div class="tours-list__content__traits">

                                    <div class="tours-list__content__trait">
                                        <img src="{{asset('assets/images/icons/fish.png')}}" height="25" width="25" alt="" />
                                        <div class="tours-list__content__trait__text" title="{{$guiding->threeTargets()}}">
                                            @php
                                            $guidingTargets = $guiding->guidingTargets->pluck('name')->toArray();

                                            if(app()->getLocale() == 'en'){
                                                $guidingTargets =  $guiding->guidingTargets->pluck('name_en')->toArray();
                                            }
                                            @endphp
                                            
                                            @if(!empty($guidingTargets))
                                                {{ implode(', ', $guidingTargets) }}
                                            @else
                                                {{ $guiding->threeTargets() }}
                                                {{$guiding->target_fish_sonstiges ? " & " . $guiding->target_fish_sonstiges : ""}}
                                            @endif
                                        </div>
                                    </div>


                                    <div class="tours-list__content__trait">
                                        <img src="{{asset('assets/images/icons/water-waves.png')}}" height="25" width="25" alt="" />
                                        <div class="tours-list__content__trait__text" title="{{$guiding->threeWaters()}}">
                                            @php

                                            $guidingWaters = $guiding->guidingWaters->pluck('name')->toArray();

                                            if(app()->getLocale() == 'en'){
                                                $guidingWaters =  $guiding->guidingWaters->pluck('name_en')->toArray();
                                            }

                                            @endphp
                                            
                                            @if(!empty($guidingWaters))
                                                {{ implode(', ', $guidingWaters) }}
                                            @else
                                            {{-- {{ translate($guiding->threeWaters()) }}
                                            {{$guiding->water_sonstiges ? " & " . translate($guiding->water_sonstiges) : ""}} --}}
                                            @endif
                                        </div>
                                    </div>

                                    <div class="tours-list__content__trait">
                                        <img src="{{asset('assets/images/icons/fishing-tool.png')}}" height="25" width="25" alt="" />
                                        <div class="tours-list__content__trait__text">
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

                                    <div class="tours-list__content__trait">
                                        <img src="{{asset('assets/images/icons/fishing.png')}}" height="25" width="25" alt="" />
                                        <div class="tours-list__content__trait__text" title="guiding methods">
                                            @php
                                            $guidingMethods = $guiding->guidingMethods->pluck('name')->toArray();

                                            if(app()->getLocale() == 'en'){
                                                $guidingMethods =  $guiding->guidingMethods->pluck('name_en')->toArray();
                                            }
                                            @endphp
                                            
                                            @if(!empty($guidingMethods))
                                                {{ implode(', ', $guidingMethods) }}
                                            @else
                                            {{-- {{ $guiding->threeMethods() }}
                                            {{$guiding->methods_sonstiges && $guiding->threeMethods() > 0 ? " & " . translate($guiding->methods_sonstiges) : null}} --}}
                                            @endif
                                        </div>
                                    </div>

                                    <div class="tours-list__content__trait">
                                        <img src="{{asset('assets/images/icons/fishing-man.png')}}" height="25" width="25" alt="" />
                                        <div class="tours-list__content__trait__text"> 
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

                                    <div class="tours-list__content__trait">
                                        <div class="icon-small" style="font-size: 1.5rem;">
                                            <span class="icon-user"></span>
                                        </div>
                                        <div class="tours-list__content__trait__text">
                                            {{ $guiding->max_guests }} @if($guiding->max_guests != 1) @lang('message.persons') @else @lang('message.person') @endif
                                        </div>
                                    </div>

                                    <div class="tours-list__content__trait">
                                        <img src="{{asset('assets/images/icons/clock.svg')}}" height="25" width="25" alt="" />
                                        <div class="tours-list__content__trait__text">{{ $guiding->duration }} 
                                            @if($guiding->duration != 1) {{translate('Stunden')}} @else {{translate('Stunde')}} @endif</div>
                                    </div>

                            </div>
                        </div>
                    </div>

                    @php
                    // Divide the data into three columns with four items each
                    $columnSize = 4;
                    $columns = array_chunk($guiding->inclussions->toArray(), $columnSize);
                    
                    @endphp
                    
                    @if(count($columns))
                    <div class="row mt-3">
                        <h5>@lang('profile.inclussion')</h5>
                        @foreach($columns as $column)
                            <div class="col-4 col-sm-4 col-md-4 mt-3">
                                <ul class="option-list">
                                    @foreach($column as $option)
                                        <li>
                                            {{-- Display the English name if available, otherwise use the default name --}}
                                            @if(app()->getLocale() == 'en' && !empty($option['name_en']))
                                                {{ $option['name_en'] }}
                                            @else
                                                {{ $option['name'] }}
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
    <!--Tour Details End-->

    <!--Tour Details Two Start-->
    <section class="tour-details-two mt-md-5 mt-4">
        <div class="container">
            <div class="row">
                <div class="col-xl-8 col-lg-7 order-1 order-md-0">
                    <div class="tour-details-two__left">
                        <div class="tour-details-two__overview">
                            <h3 class="tour-details-two__title">{{translate('Über dieses Guiding')}}</h3>
                            <p class="tour-details-two__overview-text">{!! $guiding->description ? translate(nl2br(e($guiding->description))) : null !!}</p>

                            @if($guiding->boat_information)
                            <div class="tour-details-two__overview-bottom pt-5">
                                <h3 class="tour-details-two__title">@lang('profile.aboutboat')</h3>
                                <div class="tour-details-two__overview-bottom-inner">
                                    <div class="tour-details-two__overview-bottom-left">
                                        <p>{{$guiding->boat_information}}</p>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <div class="tour-details-two__overview-bottom pt-5">
                                <h3 class="tour-details-two__title">{{translate('Zusätzliche Informationen') }}</h3>
                                <div class="tour-details-two__overview-bottom-inner">
                                    <div class="tour-details-two__overview-bottom-left">
                                        <ul class="list-unstyled tour-details-two__overview-bottom-list">
                                            {{-- @if($guiding->catering)
                                                <li>
                                                    <div class="icon-small">
                                                        <i class="fa fa-check"></i>
                                                    </div>
                                                    <div class="text">
                                                        <p><b>{{ translate('Verpflegung') }}: </b> {{ translate($guiding->catering)}}</p>
                                                    </div>
                                                </li>
                                            @endif --}}
                                            @if($guiding->meeting_point)
                                                <li>
                                                    <div class="icon-small">
                                                        <i class="fa fa-check"></i>
                                                    </div>
                                                    <div class="text">
                                                        <p><b>{{ translate('Treffpunkt') }}: </b>{{ translate($guiding->meeting_point) }}</p>
                                                    </div>
                                                </li>
                                            @endif
                                            @if($guiding->additional_information)
                                                <li>
                                                    <div class="icon-small">
                                                        <i class="fa fa-check"></i>
                                                    </div>
                                                    <div class="text">
                                                        <p><b>{{ translate('Sonstiges') }}: </b>{{translate($guiding->additional_information) }}</p>
                                                    </div>
                                                </li>
                                            @endif

                                            <li>
                                                <div class="icon-small">
                                                    <i class="fa fa-check"></i>
                                                </div>
                                                <div class="text">
                                                    <p>
                                                        <b>{{ translate('Gast-/Gewässerkarte') }}: </b>{{$guiding->required_special_license ? " & " . translate($guiding->required_special_license) : translate('Nein') }}
                                                    </p>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="icon-small">
                                                    <i class="fa fa-check"></i>
                                                </div>
                                                <div class="text">
                                                    <p><b>Equipment:</b>
                                                        @if($guiding->equipmentStatus)
                                                            {{ $guiding->equipmentStatus->name == 'ist vorhanden' ? translate($guiding->equipmentStatus->name) : translate($guiding->needed_equipment) }}
                                                        @endif
                                                    </p>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-lg-5 text-center order-0 order-md-1">
                    @if(!$agent->ismobile())
                        @include('pages.guidings.content.bookguiding')
                    @endif
                </div>
        </div>
        <div class="row">
            <div class="tour-details-two__location {{$agent->ismobile() ? 'text-center' : ''}}">
                <h3 class="tour-details-two__title">{{ translate('Karte') }}</h3>
                <div id="map" style="height: 400px; width: 100%;">
               
                </div>
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


            @if(round($average_rating) > 0)
                <div class="tour-details-two__location mt-5 pt-2">

                    <h3 class="tour-details-two__title">Bewertungen</h3>
                    <div class="tour-details__review-score-ave">
                        <div class="my-auto">
                            <h3>{{$average_rating}}</h3>
                            <p>
                                @for($i = 0; $i < (round($average_rating)); $i++)
                                    <i class="fa fa-star"></i>
                                @endfor
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="col">
                @foreach($guiding->user->received_ratings as $received_rating )
                    <div class="col-md-3">
                        <div class="tour-details__review-comment">
                            <div class="tour-details__review-comment-single">
                                <div class="tour-details__review-comment-top mt-3">
                                    <div class="tour-details__review-comment-top-content mt-5">
                                        <h3>{{$received_rating ->user->firstname }}</h3>
                                    
                                        <p>{{ ($received_rating->created_at != null ) ? Carbon\Carbon::parse($received_rating->created_at)->format('F j, Y') : "-"}}</p>
                                        <p>{{$received_rating ->description }}</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <p><span class="px-2">Bewertung: </span>
                                            {{floor($received_rating->rating)}}
                                            @for($i = 0; $i < floor($received_rating->rating); $i++)
                                                <i class="fa fa-star"></i>
                                            @endfor
                                        </p>
                                    </div><!-- /.col-md-4 -->
                                </div><!-- /.row -->
                            </div>
                        </div>
                    </div>

                @endforeach
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
                                    <span>@lang('message.from') {{ two($other_guiding->getLowestPrice()) }}€</span>
                                </p>
                                <span><i class="far fa-hourglass"></i>{{ translate('Dauer') }}: {{ two($other_guiding->duration) }} {{ translate('Stunden') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Lokalität -->
    <!-- Modal -->
    @include('pages.guidings.content.guidingModal')

    <!-- Endmodal -->

    <!--Tour Details Two End-->
@endsection
@section('js_after')
    <!-- MORE BUTTON -->
    <script>
        // trigger expand text button
        const moreText = document.querySelector(".js-trigger-more-text");
        let expanded = false;

        function moreOrLessFunction(e) {

            if (!expanded) {
                // alert(e);
                expanded = true;
                moreText.classList.add('expand-text');
                e.innerHTML = '{{translate("Weniger")}}';
            } else {
                expanded = false;
                moreText.classList.remove('expand-text');
                e.innerHTML = '{{translate("Mehr")}}';
            }
        }

        // remove all inline Styles
        $('.tour-details-two__overview *').removeAttr('style');
    </script>

    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'de',
                events: {
                    url: '/events',
                    method: 'GET'
                },

                headerToolbar: {
                    right: 'today,prev,next',
                    center: 'title',
                    left: ''
                }
            });
            calendar.render();
        });
    </script>
    <!--
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBiGuDOg_5yhHeoRz-7bIkc9T1egi1fA7Q&libraries=places,geocoding"></script>
    -->
    <style>
    .custom-marker {
        background-color: #4285f4;
        border-radius: 50%;
        color: white;
        padding: 10px;
        text-align: center;
        font-size: 14px;
    }
    </style>
    <!-- <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBiGuDOg_5yhHeoRz-7bIkc9T1egi1fA7Q" ></script> -->
    <!-- <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBiGuDOg_5yhHeoRz-7bIkc9T1egi1fA7Q&callback=initMap" async defer></script> -->
    <script>(g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})
        ({key: "{{ env('GOOGLE_MAP_API_KEY') }}", v: "weekly"});</script>


    <!-- <script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script> -->
    
    <script>
        
        
        initMap();
 
            // Initialize and add the map
            async function initMap() {
                // The location of Uluru
                const position = { lat: {{$guiding->lat}}, lng: {{$guiding->lng}} };
                // Request needed libraries.
                //@ts-ignore
                const { Map } = await google.maps.importLibrary("maps");
                const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");

                // The map, centered at Uluru
                map = new Map(document.getElementById("map"), {
                    zoom: 10,
                    center: position,
                    mapId: "DEMO_MAP_ID",
                    mapTypeControl: false,
                    streetViewControl: false,
                });

                // The marker, positioned at Uluru
                const marker = new AdvancedMarkerElement({
                    map: map,
                    position: position,
                });
            }

            function initMap_original() {
                // The location of guiding
                const location = {lat: {{$guiding->lat}}, lng: {{$guiding->lng}}};
                // The map, centered at location
                const map = new google.maps.Map(document.getElementById("map"), {
                    zoom: 10,
                    center: location,
                });
                // The marker, positioned at Uluru
                const marker = new google.maps.marker.AdvancedMarkerElement({
                    position: location,
                    map: map,
                });
                // Add Even on Marker
                marker.addListener("click", () => {
                    $('#guidingModal{{$guiding->id}}').modal('show');
                })
            }

            function toggleHighlight(markerView) {
                if (markerView.content.classList.contains("highlight")) {
                    markerView.content.classList.remove("highlight");
                    markerView.zIndex = null;
                } else {
                    markerView.content.classList.add("highlight");
                    markerView.zIndex = 1;
                }
            }

        $('#person').on('change',function(){
            $('.price-details').css('display','block');
        })

        const mediaQuery = window.matchMedia('(max-width: 767px)')
        const selectGuide = document.getElementById('person');
        const rate = document.querySelectorAll('[data-rate]');
        if (mediaQuery.matches) {
        selectGuide.addEventListener("change", function () {
                rate.forEach((el) => {
                    if (this.value === el.getAttribute('data-rate')) {
                        el.style.display = "flex";
                    }else{
                        el.style.display = "none";
                    }
                })
            });
        }

        // if (mediaQuery.matches) {
        //     selectGuide.addEventListener("change", function () {
        //         rate.forEach((el) => {
        //             if (this.value === el.getAttribute('data-rate')) {
        //                 el.style.display = "flex";
        //             }
        //         })
        //     });
        // }

    </script>

@endsection

@section('css_after')
    <style>
        .availableEvent {
            border: 1px solid lightgrey;
            border-radius: 5%;
            padding: 5px 10px;
            cursor: pointer;
            margin-bottom: 5px;
            margin-right: 7px;
            transition: all 0.2s ease-in-out;
        }

        .availableEvent.selected {
            background-color: var(--thm-primary);
            color: white !important;
            border: none;
        }

        .availableEvent:hover {
            background-color: var(--thm-primary);
            color: white !important;
        }

        .leadingEvents {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
        }

        .is-start-date, .is-end-date {
            color: white !important;
            background-color: var(--thm-success) !important;
        }

        .is-today {
            background-color: var(--thm-danger) !important;
            color: white !important;
        }

        .litepicker .container__days .day-item:hover {
            color: var(--thm-primary);
            -webkit-box-shadow: inset 0 0 0 1px var(--thm-primary);
            box-shadow: inset 0 0 0 1px var(--thm-primary);
        }
    </style>
@endsection