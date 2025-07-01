@extends('layouts.app-v2-1')

@section('title', $title)
@section('header_title', __('category.' . $type . '.title'))
@section('header_sub_title', __('category.' . $type . '.sub_title'))

@section('share_tags')
    <meta property="og:title" content="{{__('category.' . $type . '.title')}}" />
    <meta property="og:description" content="{{__('category.' . $type . '.introduction')}}" />
@endsection

@section('custom_style')
<style>
    #destination{
        max-width: 1600px;
    }
    .guiding-item-desc a:hover {
        color: #000!important;
    }
    #page-main-intro {
    }
    #carousel-regions,
    #carousel-cities {
        min-height: 301.6px;
    }
    #carousel-regions .dimg-fluid,
    #carousel-cities .dimg-fluid {
        min-height: 301.6px;
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
        .dimg-fluid {
            width: 100%!important;
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
        width: 100%;
        height: 200px;
        background-image: url({{ url('') }}/assets/images/map-bg.png);
        background-size: cover;
        background-position: center;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #map-placeholder button {
        position: static;
        margin: 0;
    }

    #offcanvasBottomSearch {
        height: 90%!important;
    }

    .btn-outline-theme {
        color: #E8604C!important;
        border-color: #E8604C!important;
    }
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
    
    #fish_size_limit_table th:first-child, 
    #fish_size_limit_table td:first-child,
    #fish_time_limit_table th:first-child, 
    #fish_time_limit_table td:first-child
    {
        /*background-color: #fad4b9;*/
    }
    @media (min-width: 400px) {
        #fish_chart_table th:first-child, 
        #fish_chart_table td:first-child
        {
            position:sticky;
            left:0px;
            background-color: #fff;
            min-width: 156px !important;
        }
    }
    .card-img-overlay h5 {
        position: absolute;
        bottom: 20px;
        left: 20px;
        color: #fff;
    }
    .read-more-btn {
        background-color: #E8604C !important;
        color: #fff !important;
        border: 2px solid #E8604C !important;
    }
    .cag-btn {
        background-color: #E8604C !important;
        color: #fff !important;
        border: 2px solid #E8604C !important;
    }
    .cag-btn-inverted {
        background-color: #313041 !important;
        color: #fff !important;
        border: 2px solid #313041 !important;
    }
    .mobile-selection-sfm {
        position: sticky;
        z-index: 10;
        top: 0;
        background-color: #fff;
        padding-top: 15px;
        padding-left: 15px;
        padding-right: 15px;
    }
    .dimg-fluid {
        width: 300px;
        height: 300px;
        object-fit: cover;
    }
    .filter-select {
        background: url("data:image/svg+xml,<svg height='10px' width='10px' viewBox='0 0 16 16' fill='%23808080' xmlns='http://www.w3.org/2000/svg'><path d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/></svg>") no-repeat;
        background-position: right 0.3rem center !important;
        padding-left: 30px !important;
        border: 0;
        border-bottom: 1px solid #ccc;
    }

    .filter-group {
        position: relative;
        margin-bottom: 1rem;
    }

    .filter-icon {
        position: absolute;
        left: 8px;
        top: 50%;
        transform: translateY(-50%);
        z-index: 1;
        color: #808080;
    }

    /* Override Select2 styles to match */
    .select2-container--default .select2-selection--single,
    .select2-container--default .select2-selection--multiple {
        border: 0 !important;
        border-bottom: 1px solid #ccc !important;
        border-radius: 0 !important;
        padding-left: 30px !important;
    }
    
    .trending-card{
        position: relative;
        height:240px;
    }
    .trending-card .trending-card-wrapper {
        border-radius: 3px;
        width: 100%;
        height: 100%;
        margin:10px 0px;
        position: relative;
        background: #000;
    }
    .trending-card .trending-card-wrapper .trending-card-wrapper-content {
        position: absolute;
        bottom: 0;
        width: 100%;
    }
    .trending-card .trending-card-wrapper .trending-card-wrapper-content .overlay-wrapper {
        display: block;
        height: 100%;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(180deg,rgba(0,0,0,.2) 0,rgba(0,0,0,.4) 100%);
        z-index: 2;
    }
    .trending-card .trending-card-wrapper .trending-card-wrapper-content .trending-card-main {
        width: 100%;
        position: relative;
        bottom: 0;
        left: 0;
    }
    .trending-card .trending-card-wrapper .trending-card-wrapper-content .trending-card-main .trending-text-wrapper {
        display: flex;
        color:#fff;
        padding:20px 20px;
    }
    .trending-card .trending-card-wrapper .trending-card-wrapper-content .trending-card-main .trending-text-wrapper .trending-title {
        font-weight: bold;
        color:#fff;
    }
    .trending-card .trending-card-wrapper .trending-card-background {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        padding: 0;
        opacity: .6;
    }
</style>
@endsection

@section('content')
    <div class="container" id="destination">
    <div class="container">
        <section class="page-header">
            <div class="page-header__bottom breadcrumb-container guiding">
                <div class="page-header__bottom-inner">
                    <ul class="thm-breadcrumb list-unstyled">
                        <li><a href="{{ route('welcome') }}">@lang('message.home')</a></li>
                        <li><span><i class="fas fa-solid fa-chevron-right"></i></span></li>
                        <li class="active">{{ $title }}</li>
                    </ul>
                </div>
            </div>
        </section>
    </div>

        <div class="container">
            <div class="col-12">
                <div id="page-main-intro" class="mb-3">
                    <div class="page-main-intro-text mb-1">{!! $introduction !!}</div>
                    <p class="see-more text-center"><a href="#" class="btn btn-primary btn-sm read-more-btn">@lang('destination.read_more')</a></p>
                </div>

                @if($favories->count() > 0)
                    <h5 class="mb-2">Favorites</h5>
                    <div id="carousel-regions" class="owl-carousel owl-theme mb-4">
                        @foreach($favories as $fav)
                            <div class="item">
                                <div class="col-sm-12">
                                    <a href="{{ route('category.targets', ['type' => $type, 'slug' => $fav->slug]) }}">
                                        <div class="card">
                                            <div class="card-img">
                                                <img src="{{ $fav->getThumbnailPath() }}" class="dimg-fluid" alt="{{ $fav->language->title }}">
                                            </div>
                                            <div class="card-img-overlay">
                                                <h5>{{ $fav->source->name }}</h5>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if($allTargets->count() > 0)
                    <hr>
                    <h5 class="mb-2">All Targets</h5>
                    <div class="row">
                        @foreach($allTargets as $targets)
                            <div class="col-md-4 my-1">
                                <div class="trending-card">
                                    <a href="{{ route('category.targets', ['type' => $type, 'slug' => $targets->slug]) }}"> 
                                        <div class="trending-card-wrapper">
                                            <img alt="{{$targets->language->title}}" class="trending-card-background" src="{{asset($targets->thumbnail_path ?? 'images/placeholder_guide.jpg')}}">

                                            <div class="trending-card-wrapper-content">
                                                <div class="overlay-wrapper"></div>
                                                <div class="trending-card-main">
                                                    <div class="trending-text-wrapper">
                                                        <h4 class="trending-title">{{$targets->source->name}}</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <br><br>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('js_after')
<script>
    $('#sortby').on('change',function(){
        $('#form-sortby').submit();
    });
    
    $(document).ready(function(){
        $('#carousel-regions').owlCarousel({
            loop: false,
            margin: 10,
            nav: true,
            navText: ['<', '>'],
            autoplay: true,
            responsive: {
                0: {
                    items: 1
                },
                600: {
                    items: 2
                },
                1000: {
                    items: 4
                }
            }
        });

        $('#carousel-cities').owlCarousel({
            loop: false,            // Infinite looping
            margin: 10,            // Space between items
            nav: true,             // Show next/prev buttons
            dots: true,            // Show pagination dots
            autoplay: true,        // Enable auto-play
            navText: ['<', '>'],
            responsive: {
                0: {
                    items: 1   // Show 1 item on small screens
                },
                600: {
                    items: 2   // Show 2 items on medium screens
                },
                1000: {
                    items: 4   // Show 4 items on large screens
                }
            }
        });
    });

    let itemsCollapseCities = document.querySelectorAll('#carousel-cities .carousel-item');
    itemsCollapseCities.forEach((el) => {
        const minPerSlide = (itemsCollapseCities.length >= 4) ? 4 : itemsCollapseCities.length;
        let next = el.nextElementSibling
        for (var i=1; i<minPerSlide; i++) {
            if (!next) {
                next = itemsCollapseCities[0]
            }
            let cloneChild = next.cloneNode(true)
            el.appendChild(cloneChild.children[0])
            next = next.nextElementSibling
        }
    });
    
    $(function() {
        var word_char_count_allowed = $(window).width() <= 768 ? 300 : 1200;  // Adjust character count based on screen size
        var page_main_intro = $('.page-main-intro-text');
        var page_main_intro_text = page_main_intro.html();
        var page_main_intro_count = page_main_intro.text().length;
        var ellipsis = "..."; 
        var moreText = '<a href="#" class="btn btn-primary btn-sm read-more-btn">@lang('destination.read_more')</a>';
        var lessText = '<a href="#" class="btn btn-primary btn-sm read-more-btn">@lang('destination.read_less')</a>';

        var visible_text = page_main_intro_text.substring(0, word_char_count_allowed);
        var hidden_text = page_main_intro_text.substring(word_char_count_allowed);

        if (page_main_intro_count >= word_char_count_allowed) {
            $('.page-main-intro-text').html(visible_text + '<span class="more-ellipsis">' + ellipsis + '</span><span class="more-text" style="display:none;">' + hidden_text + '</span>');
            //$('.more-text').show();
            $('.see-more').click(function(e) {
                e.preventDefault();
                var textContainer = $(this).prev('.page-main-intro-text'); // Get the content element

                if ($(this).hasClass('less')) {
                    $(this).removeClass('less');
                    $(this).html(moreText);
                    textContainer.find('.more-text').hide();
                    textContainer.find('.more-ellipsis').show();
                } else {
                    $(this).addClass('less');
                    $(this).html(lessText);
                    textContainer.find('.more-text').show();
                    textContainer.find('.more-ellipsis').hide();
                }
            });
        } else {
            $('.see-more').hide();
        }

        // Re-adjust the text length if window is resized
        $(window).resize(function() {
            word_char_count_allowed = $(window).width() <= 768 ? 300 : 1200;
            visible_text = page_main_intro_text.substring(0, word_char_count_allowed);
            hidden_text = page_main_intro_text.substring(word_char_count_allowed);

            if (page_main_intro_count >= word_char_count_allowed) {
                $('.page-main-intro-text').html(visible_text + '<span class="more-ellipsis">' + ellipsis + '</span><span class="more-text" style="display:none;">' + hidden_text + '</span>');
            }
        });
    });

</script>

<script type="module">

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
    var placeLatitude = '{{ request()->get('placeLat') }}';
    var placeLongitude = '{{ request()->get('placeLng') }}';

    if (placeLatitude && placeLongitude) {
        document.getElementById('placeLat').value = placeLatitude;
        document.getElementById('placeLng').value = placeLongitude;
    }
});       
</script>


<script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>

@endsection
