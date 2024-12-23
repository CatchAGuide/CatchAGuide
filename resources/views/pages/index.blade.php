@extends('layouts.app')
@section('title', __('message.home_meta_title'))
@section('description',__('message.home_meta_description'))

@section('title', __('message.home'))
@section('content')
    <!--Main Slider Start-->
    <section class="main-slider">
        <div class="swiper-container thm-swiper__slider" data-swiper-options='{"slidesPerView": 1, "loop": false,
    "effect": "fade",
    "pagination": {
        "el": "#main-slider-pagination",
        "type": "bullets",
        "clickable": true
      },
    "navigation": {
        "nextEl": ".main-slider-button-next",
        "prevEl": ".main-slider-button-prev",
        "clickable": true
    },
    "autoplay": {
        "delay": 5000
    }}'>

            <div class="swiper-wrapper">
                <div class="swiper-slide">
                    <div class="image-layer"
                         style="background-image: url({{asset('assets/images/Coverbild_Startseite.webp')}});"></div>
                    <div class="image-layer-overlay"></div>
                    <div class="container">
                        <div class="swiper-slide-inner">
                            <div class="row">
                                <div class="col-xl-12">
                                    <h1 class="h2">@lang('message.hero-title')</h1><br>
                                    <p>@lang('message.find-your-guide')</p>
                                </div>
                            </div>
                        </div>  
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!--Tour Search Start-->
    <section class="tour-search">
        <div class="container">
            <div class="row">
                <div class="col-xl-12">
                    <div class="tour-search-box">
                        <form class="tour-search-one" action="{{route('guidings.index')}}" method="get">
                            <div class="tour-search-one__inner">
                                <div class="tour-search-one__inputs">
                                    <div class="tour-search-one__input-box">
                                        <label for="searchPlace">@lang('message.location')</label>
                                        <input type="text" placeholder="@lang('message.enter-location')" name="place" id="searchPlace"
                                               autocomplete="on">
                                        <input type="hidden" id="placeLat" name="placeLat"/>
                                        <input type="hidden" id="placeLng" name="placeLng"/>
                                    </div>
                                    <div class="tour-search-one__input-box">
                                        <label for="radius">Radius</label>
                                        <select id="radius" class="selectpicker" name="radius">
                                            <option value="">@lang('message.choose')...</option>
                                            <option value="50">50km</option>
                                            <option value="100">100km</option>
                                            <option value="150">150km</option>
                                            <option value="250">250km</option>
                                            <option value="500">500km</option>
                                        </select>
                                    </div>                                    
                                    <div class="tour-search-one__input-box tour-search-one__input-box-last">
                                        <label for="target_fish">@lang('message.target-fish')</label>
                                        <select class="selectpicker" id="target_fish" name="target_fish[]">
                                            <option value="">@lang('message.all')...</option>
                                            @foreach($targets as $target)
                                                <option value="{{$target->id}}">
                                                    @switch(app()->getLocale())
                                                    @case('de')
                                                    {{$target->name}}
                                                    @break;
                                                    @case('en')
                                                    {{$target->name_en ? $target->name_en : $target->name }}
                                                    @break
                                                    {{$target->name}}
                                                    @default
                                                @endswitch
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>  
                                </div>
                                <div class="tour-search-one__btn-wrap">
                                    <button type="submit" class="thm-btn tour-search-one__btn">@lang('message.Search')</button>
                                </div>
                            </div>
                        </form>
                        <div class="tour-search-one__favorites">
                            <span>
                                <img src="{{asset('assets/images/icons/fish.png')}}" height="20" width="20" alt="" />
                                @lang('message.popularFish'):
                            </span> 
                            <div class="tour-search-one__favorites__buttons">
                                <form class="one" action="{{route('search')}}" method="post">
                                    @csrf
                                    <input type="hidden" name="target_fish" value="Wels">
                                    <button data-id="bs-select-2-11" class="pill" type="submit">@lang('message.guiding1')</button>
                                </form>
                                <form class="two-left" action="{{route('search')}}" method="post">
                                    @csrf
                                    <input type="hidden" name="target_fish" value="Karpfen">
                                    <button data-id="bs-select-2-17" class="pill" type="submit">@lang('message.guiding2')</button>
                                </form>
                                <form class="two-right" action="{{route('search')}}" method="post">
                                    @csrf
                                    <input type="hidden" name="target_fish" value="Raubfische">
                                    <button data-id="bs-select-2-12" class="pill" type="submit">@lang('message.guiding3')</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--Tour Search End-->

    <div class="popular-tours">
        <div class="popular-tours__container">
            <div class="section-title text-center">
                <span class="section-title__tagline">@lang('message.popularGiude')</span>
                <h2 class="section-title__title">@lang('message.frequentlyBooked')</h2>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="popular-tours__carousel owl-theme owl-carousel">
                        @foreach($most_booked_guidings as $most_booked_guiding)
                        <div class="popular-tours__single">
                            <a class="popular-tours__img" href="{{ route('guidings.show', [$most_booked_guiding->id,$most_booked_guiding->slug]) }}" title="Guide aufmachen">
                                <figure class="popular-tours__img__wrapper">
                                    @if(get_featured_image_link($most_booked_guiding))
                                    <img src="{{get_featured_image_link($most_booked_guiding)}}" alt="" style="object-fit: contain"/>
                                    @endif
                                </figure>
                            </a>
                            <div class="popular-tours__content {{$agent->isMobile() ? 'text-center' : ''}}">
                                <h3 class="popular-tours__title">
                                    <a href="{{ route('guidings.show', [$most_booked_guiding->id,$most_booked_guiding->slug]) }}" title="Guide aufmachen">{{translate($most_booked_guiding->title)}}</a>
                                </h3>
                                <span>{{translate($most_booked_guiding->location)}}</span>
                                <p class="popular-tours__rate"><span>@lang('message.from') {{ two($most_booked_guiding->price) }}€</span></p>
                                
                                <span class="popular-tours__time"><img src="{{asset('assets/images/icons/clock.svg')}}" height="20" width="20" alt="" />
                                {{ $most_booked_guiding->duration }} @lang('message.hours')</span>
                            </div>

                            <div class="popular-tours__icon">
                                <a href="{{ route('wishlist.add-or-remove', $most_booked_guiding->id) }}">
                                    <i class="fa fa-heart {{ (auth()->check() ? (auth()->user()->isWishItem($most_booked_guiding->id) ? 'text-danger' : '') : '') }}"></i>
                                </a>
                            </div>
                         
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--About One Start-->
    <div class="about-one">
        <div class="about-one-shape-1 wow slideInLeft" data-wow-delay="100ms" data-wow-duration="2500ms">
            <img src="{{asset('assets/images/shapes/about-one-shape-1.png')}}" alt="">
        </div>

        <div class="container">
            <div class="row">
                <div class="col-xl-6 wow fadeInLeft" data-wow-duration="1500ms">
                    <div class="">
                        <div class="">
                            <div class="about-one__circle">
                                @if($agent->isMobile())
                                    <img class="rounded-circle"
                                         src="{{ asset('assets/images/Coverbild_Listing-Seite.webp') }}" alt="" height="350" style="height:350px;width:350px;">
                                @else
                                    <img class="rounded-circle"
                                         src="{{ asset('assets/images/Coverbild_Listing-Seite.webp') }}" alt="" height="600" style="height:600px;width:600px;">
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="about-one__right {{$agent->isMobile() ? 'text-center' : ''}}">
                        <div class="section-title text-left">
                            <span class="section-title__tagline">Catch A Guide</span>
                            <h2 class="section-title__title">@lang('message.awaits')</h2>
                        </div>
                        <p class="about-one__right-text">@lang('message.awaitsStatement')</p>
                        {{--<a href="#" class="about-one__btn thm-btn mt-2 mb-2">Jetzt Guide Buchen</a>--}}
                        <a href="{{route('additional.about_us')}}" class="about-one__btn thm-btn mb-2 mt-2">@lang('message.about')</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--About One End-->

    <!-- Testimonial Start-->
    <div class="testimonial-one" style="padding-top: 40px; padding-bottom: 40px;">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-indicators">
                            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
                            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2" aria-label="Slide 3"></button>
                        </div>
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <div class="row">
                                    <div class="carousel-item__img">
                                        <figure class="testimonial-one__img" >
                                            <img src="{{asset('assets/images/icons/001-perch.png')}}" alt="" />
                                        </figure>
                                    </div>
                                    <div class="carousel-item__text">
                                        <div class="carousel-item__text__title">
                                           @lang('message.Know')
                                        </div>
                                        <p>
                                            @lang('message.KnowMsg')
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <div class="row">
                                    <div class="carousel-item__img">
                                        <figure class="testimonial-one__img">
                                            <img src="{{asset('assets/images/icons/003-handshake.png')}}" alt="" />
                                        </figure>
                                    </div>
                                    <div class="carousel-item__text">
                                        <div class="carousel-item__text__title">@lang('message.friends')</div>
                                        <p>
                                            @lang('message.friendsMsg')
                                        </p>
                                    </div>

                                </div>
                            </div>
                            <div class="carousel-item">
                                <div class="row">
                                    <div class="carousel-item__img">
                                        <figure class="testimonial-one__img">
                                            <img src="{{asset('assets/images/icons/005-lake.png')}}" alt="" />
                                        </figure>
                                    </div>
                                    <div class="carousel-item__text">
                                        <div class="carousel-item__text__title">@lang('message.successful')</div>
                                        <p>
                                            @lang('message.successfulMsg')
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
  
    <!--Why Choose End-->
    <div class="popular-tours">
        <div class="popular-tours__container">
            <div class="section-title text-center">
                <span class="section-title__tagline">@lang('message.newOffers')</span>
                <h2 class="section-title__title">@lang('message.recentlyAddedGuides')</h2>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="popular-tours__carousel owl-theme owl-carousel">
                        @foreach($recent_guidings as $recent_guiding)
                            <div class="popular-tours__single">
                                <a class="popular-tours__img" title="Guide aufmachen" href="{{ route('guidings.show', [$recent_guiding->id,$recent_guiding->slug]) }}">
                                    <figure class="popular-tours__img__wrapper">
                                        @if(get_featured_image_link($recent_guiding))
                                        <img src="{{get_featured_image_link($recent_guiding)}}" alt="{{translate($recent_guiding->title)}}" style="object-fit: contain"/>
                                        @endif
                                    </figure>                          
                                </a>
                                <div class="popular-tours__content {{$agent->isMobile() ? 'text-center' : ''}}">
                                    <h3 class="popular-tours__title">
                                        <a href="{{ route('guidings.show', [$recent_guiding->id,$recent_guiding->slug]) }}" title="Guide aufmachen">{{translate($recent_guiding->title)}}</a>
                                    </h3>
                                    <span>{{translate($recent_guiding->location)}}</span>
                                    <p class="popular-tours__rate"><span>@lang('message.from') {{ two($recent_guiding->price) }}€</span></p>
                                    <span class="popular-tours__time">
                                        <img src="{{asset('assets/images/icons/clock.svg')}}" height="20" width="20" alt="" />
                                        {{ $recent_guiding->duration }} @lang('message.hours')
                                    </span>
                                </div>

                                <div class="popular-tours__icon">
                                    <a href="{{ route('wishlist.add-or-remove', $recent_guiding->id) }}">
                                        <i class="fa fa-heart {{ (auth()->check() ? (auth()->user()->isWishItem($recent_guiding->id) ? 'text-danger' : '') : '') }}"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--Video One Start-->
    <div class="video-one mt-5">
        <div class="video-one-bg jarallax"
             style="background-image: url('{{asset('assets/images/Landschaft.jpg')}}'); background-size: inherit !important;filter: grayscale(100%);"></div>
        <div class="container">
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-sm-12">
                    <div class="row">
                        <div class="col-md-9">
                            <div class="video-one__left {{$agent->isMobile() ? 'text-center' : ''}}">

                                <p class="video-one__tagline">@lang('message.likeTohear') </p>
                                <h2 class="video-one__title">@lang('message.guideNow')</h2>
                                <ul class="video-one__list mb-5">
                                    <li><span style="color: #FFFFFF !important;">@lang('message.bullet-1')</span>
                                    </li>
                                    <li><span style="color: #FFFFFF !important;">@lang('message.bullet-2')</span>
                                    </li>
                                    <li style="color: #e8604c;"> <span style="color: #FFFFFF !important;">@lang('message.bullet-3')</span>
                                    </li>
                                    <li> <span style="color: #FFFFFF !important;">@lang('message.bullet-4')</span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-md-3" id="get-guide">
                            @if(auth()->check())
                                <a href="{{route('profile.newguiding')}}" class="about-one__btn thm-btn" data-bs-toggle="modal"
                                   data-bs-target="#exampleModal" style="margin-top: 200px;">@lang('message.guideNow')</a>
                            @else
                                <a href="{{route('login')}}" class="about-one__btn thm-btn" style="margin-top: 200px;">@lang('message.guideNow')</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

     <!-- New News Section -->
    <div class="news-list">
        <div class="news-list__container container">
            <div class="row">
                <div class="col-xl-9 col-lg-9">
                    <div class="news-one__top-left">
                        <div class="section-title text-left {{$agent->isMobile() ? 'text-center' : ''}}">
                            <span class="section-title__tagline">@lang('message.ourMagazine')</span>
                            <h2 class="section-title__title">@lang('message.postNews')</h2>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3">
                    <div class="news-one__top-right {{$agent->isMobile() ? 'text-center' : ''}}">
                        <a href="{{ route($blogPrefix.'.index') }}" class="news-one__btn thm-btn">@lang('message.allPost')</a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="popular-tours__carousel owl-theme thread-carousel owl-carousel">
                        @foreach($threads as $thread)
                            <div class="news-list__single">
                                <a class="news-list__single__img" title="{{translate($thread->title)}}" href="{{ route($blogPrefix.'.thread.show', [$thread->slug]) }}">
                                    <figure>
                                        <img src="{{ $thread->getThumbnailPath() }}" alt="" />
                                    </figure>
                           
                                    <div class="news-one__date">
                                        <p>{{ $thread->created_at->format('d') }} <br>
                                            <span>{{ $thread->created_at->shortMonthName }}</span>
                                        </p>
                                    </div>
                                </a>
                                <div class="news-list__single__content {{$agent->isMobile() ? 'text-center' : ''}}">
                                <h3 class="news-list__single__title">
                                    <a href="{{ route($blogPrefix.'.thread.show',[$thread->slug,app()->getLocale()]) }}">{{translate($thread->title) }}</a>
                                </h3>    
                                <a class="pill" href="{{ route($blogPrefix.'.categories.show', $thread->category) }}">{{translate($thread->category->name)}}</a>
                                <p class="news-list__single__author"><em>{{ $thread->author }}</em></p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js_after')
{{-- <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY', 'AIzaSyBiGuDOg_5yhHeoRz-7bIkc9T1egi1fA7Q') }}&libraries=places,geocoding"></script> --}}
<script>
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
</script>


  

@endsection
