@extends('layouts.app-v2')

@if(app()->getLocale() == 'en')
    @section('title','Find & book guided fishing trips online')
@else
    @section('title','Geführte Angeltouren finden & online buchen')
@endif
@section('description',__('homepage.header-message'))

@section('share_tags')
    <meta property="og:title" content="{{__('homepage.header-title')}}" />
    <meta property="og:description" content="{{__('homepage.header-message')}}" />
    
    @if(file_exists(public_path(str_replace(asset(''), '', asset('assets/images/logo/CatchAGuide_LogoOnly_JPEG.jpg')))))
        <meta property="og:image" content="{{asset('assets/images/logo/CatchAGuide_LogoOnly_JPEG.jpg')}}"/>
    @endif
@endsection

@section('header_title', __('homepage.header-title'))
@section('header_sub_title', __('homepage.header-message'))

@section('custom_style')

<style>
    .section-title{
        font-weight: bold;
    }
    #herofilter .column-input{
        padding:0px;
        padding-right:3px;
    }
    #herofilter .column-input input,button{
        border-color:#E8604C !important;
        border-width: 2px !important;
        outline:none !important;
    }
    #herofilter .column-input i{
        color:#E8604C !important;
    }
    #herofilter .column-input input,select{
       padding-left:30px !important;
    }
    #herofilter .form-control:focus {
        border-color: inherit;
        -webkit-box-shadow: none;
        box-shadow: none;
        outline:none !important;
    }
    #herofilter .myselect2{
        border:2px solid #E8604C !important;
        padding:2px 0px;
        border-width: 2px !important;
        background-color: white;
    }
    #herofilter .myselect2 li.select2-selection__choice{
            background-color: #313041 !important;
            color: #fff !important;
            border: 0 !important;
            font-size:14px;
            vertical-align: middle !important;
            margin-top:0 !important;
         
    }
    #herofilter .myselect2 button.select2-selection__choice__remove{
        border: 0 !important;
        color: #fff !important;
    }
    #herofilter .new-filter-btn{
        background-color:#E8604C;
        color:#fff;
    }
    #herofilter .new-filter-btn:hover{
        background-color:#313041;
    }
    /*  */

    #mobileherofilter .column-input input{
        border-bottom:1px solid #a7a7a7 !important;
        /* border-bottom:2px solid #E8604C !important; */
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
        /* border-bottom:2px solid #E8604C !important; */
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
    }
    /*  */
    .section-card{
        position: relative;
    }
    .section-card .section-card-wrapper {
        border-radius: 3px;
        width: 100%;
        height: 100%;
        position: relative;
        background: #000;
    }
    .section-card .section-card-wrapper .section-card-wrapper-content {
        position: absolute;
        bottom: 0;
        width: 100%;
    }
    .section-card .section-card-wrapper .section-card-wrapper-content .overlay-wrapper {
        display: block;
        height: 100%;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(180deg,rgba(0,0,0,.2) 0,rgba(0,0,0,.4) 100%);
        z-index: 2;
    }
    .section-card .section-card-wrapper .section-card-wrapper-content .section-card-main {
        width: 100%;
        position: relative;
        bottom: 0;
        left: 0;
    }
    .section-card .section-card-wrapper .section-card-wrapper-content .section-card-main .section-text-wrapper {
       color:#fff;
       padding:10px 10px;
    }
    .section-card .section-card-wrapper .section-card-background {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        padding: 0;
        opacity: .6;
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
    .trending-card .trending-card-wrapper:hover {
        background: #333333;
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
    /* .trending-card .trending-card-wrapper .trending-card-wrapper-content .overlay-wrapper:hover {
        background: linear-gradient(180deg,rgba(255, 250, 250, 0.2) 0,rgba(253, 253, 253, 0.4) 100%);
    } */
    .trending-card .trending-card-wrapper .trending-card-wrapper-content .trending-card-main {
        width: 100%;
        position: relative;
        bottom: 0;
        left: 0;
    }
    .trending-card .trending-card-wrapper .trending-card-wrapper-content .trending-card-main .trending-text-wrapper {
        display: flex;
        align-items: center;
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
        transform: translate3d(0px, 0px, 0.1px);
    }

    .fishing-experience .nav-tabs{
        border:none !important;
    }

    .fishing-experience .nav-tabs .nav-link{    
        border:none;
        font-size:14px;
        margin:0px 5px;
    }
    .fishing-experience .nav-tabs .owl-item .nav-link.active{
        color:#f44a30 !important;
        /* border:1px solid #f44a30 !important; */
        /* border-radius: 30px; */
    }

    .fishing-magazine .magazine-bg{
        background-size: cover !important;
        height: 367px;
        background-repeat: no-repeat !important;
        width: 100%;
        background-position: 50% 50% !important;
        border-radius: 15px;
    }

    
    .fishing-magazine .magazine-small-bg{
        background-size: cover !important;
        height: 240px;
        background-repeat: no-repeat !important;
        width: 100%;
        background-position: 50% 50% !important;
        border-radius: 15px;
    }
    #nearest-listings-container .nr-image{
        background-size: cover !important;
        height: 240px;
        background-repeat: no-repeat !important;
        width: 100%;
        background-position: 50% 50% !important;
        border-radius: 15px;
    }
    .crop-text-2 {
        -webkit-line-clamp: 2;
        overflow : hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-box-orient: vertical;
    }
    .crop-text-1 {
        -webkit-line-clamp: 1;
        overflow : hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-box-orient: vertical;
    }
    .rated-star{
        font-size:12px;
        color:#FF9529;
    }
    .small-text{
        font-size:12px;
    }
    #usps{
        /* margin-top:-50px !important; */
    }
    #usps img{
        width: 90px;
    }
    #usps span{
        font-size: 1.1rem;
        font-weight: bolder;
    }


    .owl-theme .card-img-top{
        width: 100%;
        object-fit:cover;
        height: 201px;
        max-height: 201px ;
    }

    .owl-navigation img{
        height: 1vw;
    }
    .card-title{
        height: 37.8px;
    }
    .slider-dk img{
        width:100%;
        height: 192px;
        object-fit: cover;
        transform: translate3d(0px, 0px, 0.1px);
    }
    .img-top{
        object-fit:cover !important;
        transform: translate3d(0px, 0px, 0.1px) !important;
    }
    @media only screen and (max-width: 600px) {
        .card-img-top {
           height:30vw !important;
        }
        #hero .hero-container{
            margin:0 !important;
            height: 0 auto ;
            padding:0 !important;
        }
        #usps{
        margin-top: 0 !important;
        }
        .usps-item{
            margin: 2px 0px !important;
        }
        .fishing-tabs{
            display: block !important;
        }
        .fishing-tabs li{
            text-align: -webkit-left;
        }

        .custom-owl img{
            width: 100%;
            height: 192px !important;
        }
        .new-custom-owl{
            padding:0 !important;
        }
        .new-custom-owl .card-img-top{
            width: 100% !important;
            height:60vw !important;
        }

        .see-more{
            flex-direction: column;
        }
        
        .new-custom-owl .owl-stage {
            padding-left: 0 !important;
            transition-timing-function: linear !important;
        }
        .owl-navigation .owl-stage {
            padding-left: 0 !important;
            
        }
        .owl-navigation img{
            height: 5vw;
        }
        .owl-item .card-title{
            /* overflow-wrap: anywhere; */
        }
        #usps img{
            width: 60px;
        }
        .methods-custom-owl{
            padding:0 !important;
        }
        .methods-custom-owl .card-img-top{
            width: 100%;
            height: 192px !important;
        }
        .methods-custom-owl .owl-stage {
            padding-left: 0 !important;
            transition-timing-function: linear !important;
        }

    }
</style>

@endsection
@section('content')
{{-- <section id="usps" class="my-5">
    <div class="container">
        <div class="my-2 row fs-6">
            <div class="col-md-4 usps-item">
                <div class="p-2 row text-md-center text-sm-left align-items-center">
                    <div class="text-center col-3 col-md-12">
                        <img src="{{asset('assets/images/icons/booking.png')}}" alt="Easy & direct online booking">
                    </div>
                    <div class="my-3 col-9 col-md-12 fw-bold text-dark">
                        @if(app()->getLocale() == 'en')
                        <span>Easy & direct online booking</span>
                        @else
                        <span>Einfache und direkte Online-Buchung</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4 usps-item">
                <div class="p-2 row text-md-center text-sm-left align-items-center">
                    <div class="text-center col-3 col-md-12">
                        <img src="{{asset('assets/images/icons/qa.png')}}"  alt="Best customer service on the market">
                    </div>
                    <div class="my-3 col-9 col-md-12 fw-bold text-dark">
                        @if(app()->getLocale() == 'en')
                        <span>Best customer service on the market</span>
                        @else
                        <span>Bester Kundenservice auf dem Markt</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4 usps-item">
                <div class="p-2 row text-md-center text-sm-left align-items-center">
                    <div class="text-center col-3 col-md-12">
                        <img src="{{asset('assets/images/icons/review.png')}}" alt="Actual verified ratings">
                    </div>
                    <div class="my-3 col-9 col-md-12 fw-bold text-dark">
                        @if(app()->getLocale() == 'en')
                        <span>Actual verified ratings</span>
                        @else
                        <span>Verifizierte & echte Bewertungen</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section> --}}

<!-- <section id="nearest-listing" class="py-1 my-5 offer d-none">

    <div class="container my-4">
        <div class="my-2 section-title">
            <h2 class="h4 text-dark fw-bolder">@lang('homepage.nearyou-title')</h2>
            <p class="fw-light">@lang('homepage.nearyou-message') </p>
        </div>
        <div id="nearest-listings-container">
        
        </div>
    </div>
</section> -->


<section class="py-1 my-5 trending">
    <div class="container my-4">
        <div class="my-0 section-title">
            <h2 class="h4 text-dark fw-bolder">@lang('homepage.destination-title')</h2> 
            <div class="see-more d-flex justify-content-between">
                <div>
                    <p class="fw-light">@lang('homepage.destination-message')</p>
                </div>
                @if(!$agent->ismobile())
                <div>
                    @if(app()->getLocale() == 'de')
                        <a href="{{route('destination')}}" class="color-primary fw-light">Alle Länder ansehen</a>
                    @else
                        <a href="{{route('destination')}}" class="color-primary fw-light">Show all countries</a>
                    @endif
                </div>
                @endif

            </div>
        </div>
        @if(app()->getLocale() == 'de')
            @if($agent->ismobile())
                <div class="new-custom-owl owl-carousel owl-theme">
                        <div class="item">
                            <div class="trending-card">
                                <a href="/destination/deutschland"> 
                                    <div class="trending-card-wrapper">
                                        <img alt="Deutschland" class="trending-card-background" src="{{asset('assets/2024/germany/deutschland4.webp')}}">
                                        <div class="trending-card-wrapper-content">
                                            <div class="overlay-wrapper"></div>
                                            <div class="trending-card-main">
                                                <div class="trending-text-wrapper">
                                                    <h4 class="trending-title">Deutschland</h4>
                                                    <div>
                                                        <img class="mx-1" alt="Deutschland" width="20" height="20" src="{{asset('flags/de.svg')}}">
                                                    </div>
                                                  
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="item">
                            <div class="trending-card">
                                <a href="/destination/schweden"> 
                                    <div class="trending-card-wrapper">
                                        <img alt="Key West" class="trending-card-background" src="{{asset('assets/2024/sweden/schweden5.webp')}}">
                                        <div class="trending-card-wrapper-content">
                                            <div class="overlay-wrapper"></div>
                                            <div class="trending-card-main">
                                                <div class="trending-text-wrapper">
                                                    <h4 class="trending-title">Schweden</h4>
                                                    <div>
                                                        <img class="mx-1" alt="Key West" width="20" height="20" src="{{asset('flags/se.svg')}}">
                                                    </div>
                                                 
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="item">
                            <div class="trending-card">
                                <a href="/destination/spanien">
                                    <div class="trending-card-wrapper">
                                        <img alt="Key West" class="trending-card-background" src="{{asset('assets/2024/spain/spain.webp')}}">
                                        <div class="trending-card-wrapper-content">
                                            <div class="overlay-wrapper"></div>
                                            <div class="trending-card-main">
                                                <div class="trending-text-wrapper">
                                                    <h4 class="trending-title">Spanien</h4>
                                                    <div>
                                                        <img class="mx-1" alt="Key West" width="20" height="20" src="{{asset('flags/es.svg')}}">
                                                    </div>
                                                   
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="item">
                            <div class="trending-card">
                                <a href="/destination/niederlande">
                                    <div class="trending-card-wrapper">
                                        <img alt="Key West" class="trending-card-background" src="{{asset('assets/2024/netherlands/holland1.webp')}}">
                                        <div class="trending-card-wrapper-content">
                                            <div class="overlay-wrapper"></div>
                                            <div class="trending-card-main">
                                                <div class="trending-text-wrapper">
                                                    <h4 class="trending-title">Niederlande</h4>
                                                    <div>
                                                        <img class="mx-1" alt="Key West" width="20" height="20" src="{{asset('flags/nl.svg')}}">
                                                    </div>
                                                  
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="item">
                            <div class="trending-card">
                                <a href="/destination/kroatien">
                                    <div class="trending-card-wrapper">
                                        <img alt="Key West" class="trending-card-background" src="{{asset('assets/2024/croatia/croatia.webp')}}">
                                        <div class="trending-card-wrapper-content">
                                            <div class="overlay-wrapper"></div>
                                            <div class="trending-card-main">
                                                <div class="trending-text-wrapper">
                                                    <h4 class="trending-title">Kroatien</h4>
                                                    <div>
                                                        <img class="mx-1" alt="Key West" width="20" height="20" src="{{asset('flags/hr.svg')}}">
                                                    </div>
                                            
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="item">
                            <div class="trending-card">
                                <a href="/destination/Denmark">
                                    <div class="trending-card-wrapper">
                                        <img alt="Key West" class="trending-card-background" src="{{asset('assets/2024/denmark/denmark.webp')}}">
                                        <div class="trending-card-wrapper-content">
                                            <div class="overlay-wrapper"></div>
                                            <div class="trending-card-main">
                                                <div class="trending-text-wrapper">
                                                    <h4 class="trending-title">Denmark</h4>
                                                    <div>
                                                        <img class="mx-1" alt="Key West" width="20" height="20" src="{{asset('flags/dk.svg')}}">
                                                    </div>
                                                  
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="item">
                            <div class="trending-card">
                                <a href="/destination/portugal">
                                    <div class="trending-card-wrapper">
                                        <img alt="Key West" class="trending-card-background" src="{{asset('assets/2024/portugal/portugal.webp')}}">
                                        <div class="trending-card-wrapper-content">
                                            <div class="overlay-wrapper"></div>
                                            <div class="trending-card-main">
                                                <div class="trending-text-wrapper">
                                                    <h4 class="trending-title">Portugal</h4>
                                                    <div>
                                                        <img class="mx-1" alt="Key West" width="20" height="20" src="{{asset('flags/pt.svg')}}">
                                                    </div>
                                                
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                </div>
                <div class="p-1 d-flex justify-content-end">
                    @if(app()->getLocale() == 'de')
                        <a href="{{route('destination')}}" class="color-primary fw-light">Alle Länder ansehen</a>
                    @else
                        <a href="{{route('destination')}}" class="color-primary fw-light">Show all countries</a>
                    @endif
                </div>
            @else
                <div class="row">
                    <div class="col-md-6 col-xs-6">
                        <div class="trending-card">
                            <a href="/destination/deutschland"> 
                                <div class="trending-card-wrapper">
                                    <img alt="Deutschland" class="trending-card-background" src="{{asset('assets/2024/germany/deutschland4.webp')}}">
                                    <div class="trending-card-wrapper-content">
                                        <div class="overlay-wrapper"></div>
                                        <div class="trending-card-main">
                                            <div class="trending-text-wrapper">
                                                <h4 class="trending-title">Deutschland</h4>
                                                <div>
                                                    <img class="mx-1" alt="Deutschland" width="32" height="32" src="{{asset('flags/de.svg')}}">
                                                </div>
                                          
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6 col-xs-6">
                        <div class="trending-card">
                            <a href="/destination/schweden"> 
                                <div class="trending-card-wrapper">
                                    <img alt="Key West" class="trending-card-background" src="{{asset('assets/2024/sweden/schweden5.webp')}}">
                                    <div class="trending-card-wrapper-content">
                                        <div class="overlay-wrapper"></div>
                                        <div class="trending-card-main">
                                            <div class="trending-text-wrapper">
                                                <h4 class="trending-title">Schweden</h4>
                                                <div>
                                                    <img class="mx-1" alt="Key West" width="32" height="32" src="{{asset('flags/se.svg')}}">
                                                </div>
                                               
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4 col-xs-6">
                        <div class="trending-card">
                            <a href="/destination/spanien">
                                <div class="trending-card-wrapper">
                                    <img alt="Key West" class="trending-card-background" src="{{asset('assets/2024/spain/spain.webp')}}">
                                    <div class="trending-card-wrapper-content">
                                        <div class="overlay-wrapper"></div>
                                        <div class="trending-card-main">
                                            <div class="trending-text-wrapper">
                                                <h4 class="trending-title">Spanien</h4>
                                                <div>
                                                    <img class="mx-1" alt="Key West" width="32" height="32" src="{{asset('flags/es.svg')}}">
                                                </div>
                                    
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4 col-xs-6">
                        <div class="trending-card">
                            <a href="/destination/niederlande">
                                <div class="trending-card-wrapper">
                                    <img alt="Key West" class="trending-card-background" src="{{asset('assets/2024/netherlands/holland1.webp')}}">
                                    <div class="trending-card-wrapper-content">
                                        <div class="overlay-wrapper"></div>
                                        <div class="trending-card-main">
                                            <div class="trending-text-wrapper">
                                                <h4 class="trending-title">Niederlande</h4>
                                                <div>
                                                    <img class="mx-1" alt="Key West" width="32" height="32" src="{{asset('flags/nl.svg')}}">
                                                </div>
                                               
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4 col-xs-6">
                        <div class="trending-card">
                            <a href="/destination/kroatien">
                                <div class="trending-card-wrapper">
                                    <img alt="Key West" class="trending-card-background" src="{{asset('assets/2024/croatia/croatia.webp')}}">
                                    <div class="trending-card-wrapper-content">
                                        <div class="overlay-wrapper"></div>
                                        <div class="trending-card-main">
                                            <div class="trending-text-wrapper">
                                                <h4 class="trending-title">Kroatien</h4>
                                                <div>
                                                    <img class="mx-1" alt="Key West" width="32" height="32" src="{{asset('flags/hr.svg')}}">
                                                </div>
                                    
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        @else
        @if($agent->ismobile())
        <div class="new-custom-owl owl-carousel owl-theme">
            <div class="item">
                <div class="trending-card">
                    <a href="/destination/niederlande">
                        <div class="trending-card-wrapper">
                            <img alt="Key West" class="trending-card-background" src="{{asset('assets/2024/netherlands/holland1.webp')}}">
                            <div class="trending-card-wrapper-content">
                                <div class="overlay-wrapper"></div>
                                <div class="trending-card-main">
                                    <div class="trending-text-wrapper">
                                        <h4 class="trending-title">Netherlands</h4>
                                        <div>
                                            <img class="mx-1" alt="Key West" width="20" height="20"  src="{{asset('flags/nl.svg')}}">
                                        </div>
                              
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="item">
                <div class="trending-card">
                    <a href="/destination/schweden"> 
                        <div class="trending-card-wrapper">
                            <img alt="Key West" class="trending-card-background" src="{{asset('assets/2024/sweden/schweden5.webp')}}">
                            <div class="trending-card-wrapper-content">
                                <div class="overlay-wrapper"></div>
                                <div class="trending-card-main">
                                    <div class="trending-text-wrapper">
                                        <h4 class="trending-title">Sweden</h4>
                                        <div>
                                            <img class="mx-1" alt="Key West" width="20" height="20" src="{{asset('flags/se.svg')}}">
                                        </div>
                      
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="item">
                <div class="trending-card">
                    <a href="/destination/spanien">
                        <div class="trending-card-wrapper">
                            <img alt="Key West" class="trending-card-background" src="{{asset('assets/2024/spain/spain.webp')}}">
                            <div class="trending-card-wrapper-content">
                                <div class="overlay-wrapper"></div>
                                <div class="trending-card-main">
                                    <div class="trending-text-wrapper">
                                        <h4 class="trending-title">Spain</h4>
                                        <div>
                                            <img class="mx-1" alt="Key West" width="20" height="20"  src="{{asset('flags/es.svg')}}">
                                        </div>
                  
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="item">
                <div class="trending-card">
                    <a href="/destination/portugal">
                        <div class="trending-card-wrapper">
                            <img alt="Key West" class="trending-card-background" src="{{asset('assets/2024/portugal/portugal.webp')}}">
                            <div class="trending-card-wrapper-content">
                                <div class="overlay-wrapper"></div>
                                <div class="trending-card-main">
                                    <div class="trending-text-wrapper">
                                        <h4 class="trending-title">Portugal</h4>
                                        <div>
                                            <img class="mx-1" alt="Key West" width="20" height="20" src="{{asset('flags/pt.svg')}}">
                                        </div>
    
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="item">
                <div class="trending-card">
                    <a href="/destination/kroatien">
                        <div class="trending-card-wrapper">
                            <img alt="Key West" class="trending-card-background" src="{{asset('assets/2024/croatia/croatia.webp')}}">
                            <div class="trending-card-wrapper-content">
                                <div class="overlay-wrapper"></div>
                                <div class="trending-card-main">
                                    <div class="trending-text-wrapper">
                                        <h4 class="trending-title">Croatia</h4>
                                        <div>
                                            <img class="mx-1" alt="Key West" width="20" height="20"  src="{{asset('flags/hr.svg')}}">
                                        </div>
                                       
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="item">
                <div class="trending-card">
                    <a href="/destination/deutschland"> 
                        <div class="trending-card-wrapper">
                            <img alt="Key West" class="trending-card-background" src="{{asset('assets/2024/germany/deutschland4.webp')}}">
                            <div class="trending-card-wrapper-content">
                                <div class="overlay-wrapper"></div>
                                <div class="trending-card-main">
                                    <div class="trending-text-wrapper">
                                        <h4 class="trending-title">Germany</h4>
                                        <div>
                                            <img class="mx-1" alt="Key West" width="20" height="20"  src="{{asset('flags/de.svg')}}">
                                        </div>
                          
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="item">
                <div class="trending-card">
                    <a href="/destination/Denmark">
                        <div class="trending-card-wrapper">
                            <img alt="Key West" class="trending-card-background" src="{{asset('assets/2024/denmark/denmark.webp')}}">
                            <div class="trending-card-wrapper-content">
                                <div class="overlay-wrapper"></div>
                                <div class="trending-card-main">
                                    <div class="trending-text-wrapper">
                                        <h4 class="trending-title">Denmark</h4>
                                        <div>
                                            <img class="mx-1" alt="Key West" width="20" height="20"  src="{{asset('flags/dk.svg')}}">
                                        </div>  

                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        @else
            <div class="row">
                <div class="col-md-6 col-xs-6">
                    <div class="trending-card">
                        <a href="/destination/niederlande">
                            <div class="trending-card-wrapper">
                                <img alt="Key West" class="trending-card-background" src="{{asset('assets/2024/netherlands/holland1.webp')}}">
                                <div class="trending-card-wrapper-content">
                                    <div class="overlay-wrapper"></div>
                                    <div class="trending-card-main">
                                        <div class="trending-text-wrapper">
                                            <h4 class="trending-title">Netherlands</h4>
                                            <div>
                                                <img class="mx-1" alt="Key West" width="32" height="32" src="{{asset('flags/nl.svg')}}">
                                            </div>
                                
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-md-6 col-xs-6">
                    <div class="trending-card">
                        <a href="/destination/schweden"> 
                            <div class="trending-card-wrapper">
                                <img alt="Key West" class="trending-card-background" src="{{asset('assets/2024/sweden/schweden5.webp')}}">
                                <div class="trending-card-wrapper-content">
                                    <div class="overlay-wrapper"></div>
                                    <div class="trending-card-main">
                                        <div class="trending-text-wrapper">
                                            <h4 class="trending-title">Sweden</h4>
                                            <div>
                                                <img class="mx-1" alt="Key West" width="32" height="32" src="{{asset('flags/se.svg')}}">
                                            </div>
                 
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-md-4 col-xs-6">
                    <div class="trending-card">
                        <a href="/destination/spanien">
                            <div class="trending-card-wrapper">
                                <img alt="Key West" class="trending-card-background" src="{{asset('assets/2024/spain/spain.webp')}}">
                                <div class="trending-card-wrapper-content">
                                    <div class="overlay-wrapper"></div>
                                    <div class="trending-card-main">
                                        <div class="trending-text-wrapper">
                                            <h4 class="trending-title">Spain</h4>
                                            <div>
                                                <img class="mx-1" alt="Key West" width="32" height="32" src="{{asset('flags/es.svg')}}">
                                            </div>
             
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-md-4 col-xs-6">
                    <div class="trending-card">
                        <a href="/destination/portugal">
                            <div class="trending-card-wrapper">
                                <img alt="Key West" class="trending-card-background" src="{{asset('assets/2024/portugal/portugal.webp')}}">
                                <div class="trending-card-wrapper-content">
                                    <div class="overlay-wrapper"></div>
                                    <div class="trending-card-main">
                                        <div class="trending-text-wrapper">
                                            <h4 class="trending-title">Portugal</h4>
                                            <div>
                                                <img class="mx-1" alt="Key West" width="32" height="32" src="{{asset('flags/pt.svg')}}">
                                            </div>
                     
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-md-4 col-xs-6">
                    <div class="trending-card">
                        <a href="/destination/kroatien">
                            <div class="trending-card-wrapper">
                                <img alt="Key West" class="trending-card-background" src="{{asset('assets/2024/croatia/croatia.webp')}}">
                                <div class="trending-card-wrapper-content">
                                    <div class="overlay-wrapper"></div>
                                    <div class="trending-card-main">
                                        <div class="trending-text-wrapper">
                                            <h4 class="trending-title">Croatia</h4>
                                            <div>
                                                <img class="mx-1" alt="Key West" width="32" height="32" src="{{asset('flags/hr.svg')}}">
                                            </div>
                              
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        @endif
        

     
        @endif
    </div>
</section>
<section class="py-1 my-5 fishing-experience">
    <div class="container">
        <div class="my-3 section-title">
            <h2 class="h4 text-dark fw-bolder">@lang('homepage.experience-title')</h2>
            <p class="fw-light">
                @lang('homepage.experience-message')
            </p>
        </div>
        <ul class="nav nav-tabs fishing-tabs" id="myTab" role="tablist">
            <div class="owl-navigation owl-carousel owl-theme">
                <div class="item">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="action-tab" data-bs-toggle="tab" data-bs-target="#action" type="button" role="tab" aria-controls="action" aria-selected="true">
                          <div class="d-flex align-items-center">
                              <div class="me-1">
                                  <img src="{{asset('assets/images/icons/pa.png')}}" width="20px" alt="">
                              </div>
                              <div class="tab-slider-title">
                                  @lang('homepage.experience-tab-action')
                              </div>
                          </div>
                      </button>
                      </li>
                </div>
                <div class="item">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="boat-tab" data-bs-toggle="tab" data-bs-target="#boat" type="button" role="tab" aria-controls="boat" aria-selected="false">
                          <div class="d-flex">
                              <div class="me-1">
                                  <img src="{{asset('assets/images/icons/sea.png')}}" width="20px" alt="">
                              </div>
                              <div class="tab-slider-title">
                                  @lang('homepage.experience-tab-sea')
                              </div>
                          </div>
                      </button>
                      </li>
                </div>
                <div class="item">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="adventure-tab" data-bs-toggle="tab" data-bs-target="#adventure" type="button" role="tab" aria-controls="adventure" aria-selected="false">
                          <div class="d-flex">
                              <div class="me-1">
                                  <img src="{{asset('assets/images/icons/family.png')}}" width="20px" alt="">
                              </div>
                              <div class="tab-slider-title">
                                  @lang('homepage.experience-tab-family')
                              </div>
                          </div>
                          
                      </button>
                      </li>
                </div>
                <div class="item">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pelagic-tab" data-bs-toggle="tab" data-bs-target="#pelagic" type="button" role="tab" aria-controls="pelagic" aria-selected="false">
                            <div class="d-flex align-items-center">
                                <div class="me-1">
                                    <img src="{{asset('assets/images/icons/pelagic.png')}}" width="20px" alt="">
                                </div>
                                <div class="tab-slider-title">
                                    @lang('homepage.experience-tab-pelagic')
                                </div>
                            </div>
                        </button>
                    </li>
                </div>
                <div class="item">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="flyfishing-tab" data-bs-toggle="tab" data-bs-target="#flyfishing" type="button" role="tab" aria-controls="flyfishing" aria-selected="false">
                            <div class="d-flex align-items-center">
                                <div class="me-1">
                                    <img src="{{asset('assets/images/icons/fly-fishing.png')}}" width="20px" alt="">
                                </div>
                                <div class="tab-slider-title">
                                    @lang('homepage.experience-tab-fly')
                                </div>
                            </div>
                        </button>
                    </li>
                </div>
                <div class="item">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="shorefishing-tab" data-bs-toggle="tab" data-bs-target="#shorefishing" type="button" role="tab" aria-controls="shorefishing" aria-selected="false">
                            <div class="d-flex align-items-center">
                                <div class="me-1">
                                    <img src="{{asset('assets/images/icons/bank-fishing.png')}}" width="20px" alt="">
                                </div>
                                <div class="tab-slider-title">
                                    @lang('homepage.experience-tab-bank')
                                </div>
                            </div>
                        </button>
                    </li>
                </div>
                <div class="item">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="multiday-tab" data-bs-toggle="tab" data-bs-target="#multiday" type="button" role="tab" aria-controls="multiday" aria-selected="false">
                            <div class="d-flex align-items-center">
                                <div class="me-1">
                                    <img src="{{asset('assets/images/icons/multi-day.png')}}" width="20px" alt="">
                                </div>
                                <div class="tab-slider-title">
                                    @lang('homepage.experience-tab-multiday')
                                </div>
                            </div>
                        </button>
                    </li>
                </div>
            </div>

        </ul>
          <div class="my-3 tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="action" role="tabpanel" aria-labelledby="action-tab">
                <div class="px-3 row">
                    @include('pages.partials.slider',['models' => $activeFishing])
                    <div class="p-1 d-flex justify-content-end">
                        <a href="/guidings?fishing_type=1" class="color-primary">@lang('homepage.see-more')</a>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="boat" role="tabpanel" aria-labelledby="boat-tab">
                <div class="px-3 row">
                    @include('pages.partials.slider',['models' => $seaFishing])
                    <div class="p-1 d-flex justify-content-end">
                        <a href="/guidings?water[]=2" class="color-primary">@lang('homepage.see-more')</a>
                    </div>

                </div>
            </div>
            <div class="tab-pane fade" id="adventure" role="tabpanel" aria-labelledby="adventure-tab">
                <div class="px-3 row">
                    @include('pages.partials.slider',['models' => $familyAdventures])
                    <div class="p-1 d-flex justify-content-end">
                        <a href="/guidings?num_guests=4" class="color-primary">@lang('homepage.see-more')</a>
                    </div>
  
                </div>
            </div>
            <div class="tab-pane fade" id="pelagic" role="tabpanel" aria-labelledby="pelagic-tab">
                <div class="px-3 row">
                    @include('pages.partials.slider',['models' => $boatFishing])
                    <div class="p-1 d-flex justify-content-end">
                        <a href="/guidings?methods[]=16" class="color-primary">@lang('homepage.see-more')</a>
                    </div>
     
                </div>
            </div>
            <div class="tab-pane fade" id="flyfishing" role="tabpanel" aria-labelledby="flyfishing-tab">
                <div class="px-3 row">
                    @include('pages.partials.slider',['models' => $flyshings])
                    <div class="p-1 d-flex justify-content-end">
                        <a href="/guidings?methods[]=1" class="color-primary">@lang('homepage.see-more')</a>
                    </div>
              
                </div>
            </div>
            <div class="tab-pane fade" id="shorefishing" role="tabpanel" aria-labelledby="shorefishing-tab">
                <div class="px-3 row">
                    @include('pages.partials.slider',['models' => $shoreFishings])
                    <div class="p-1 d-flex justify-content-end">
                        <a href="/guidings?methods[]=2" class="color-primary">@lang('homepage.see-more')</a>
                    </div>
            
                </div>
            </div>
            <div class="tab-pane fade" id="multiday" role="tabpanel" aria-labelledby="multiday-tab">
                <div class="px-3 row">
                    @include('pages.partials.slider',['models' => $multidayfishings])
                    <div class="p-1 d-flex justify-content-end">
                        <a href="/guidings?duration=24" class="color-primary">@lang('homepage.see-more')</a>
                    </div>
                </div>
            </div>
          </div>
    </div>
</section>
<section class="py-1 my-5 popular-trips">    
    <div class="container p-3 mt-0 popular-tours">
        <div class="my-2 section-title">
            <h2 class="h4 text-dark fw-bolder">@lang('homepage.frequently-title')</h2>
            <p class="fw-light">@lang('homepage.frequently-message')</p>
        </div>
        @if($agent->ismobile())
        <div class="new-custom-owl owl-carousel owl-theme">
            @foreach($bookedGuidings as $most_booked_guiding)
                <div class="item">
                    <a href="{{ route('guidings.show', [$most_booked_guiding->id, $most_booked_guiding->slug]) }}">
                        <div class="card" style="min-height:360px;">
                            @if(get_featured_image_link($most_booked_guiding))
                            <img src="{{get_featured_image_link($most_booked_guiding)}}" class="card-img-top">
                            @else
                                <img src="{{asset('images/placeholder_guide.webp')}}" class="card-img-top">
                            @endif
                            
                            <div class="card-body">
                            <h5 class="crop-text-2 card-title h6">{{translate($most_booked_guiding->title)}}</h5>
                            <small class="crop-text-1 small-text text-muted">{{translate($most_booked_guiding->location)}}</small>
                            <small class="fw-bold text-muted">@lang('message.from') <span class="color-primary">{{ $most_booked_guiding->getLowestPrice() }}€</span> p.P.</small>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
        @else
        <div class="custom-owl owl-carousel owl-theme">
            @foreach($bookedGuidings as $most_booked_guiding)
                <div class="item">
                    <a href="{{ route('guidings.show', [$most_booked_guiding->id, $most_booked_guiding->slug]) }}">
                        <div class="card" style="min-height:360px;">
                            @if(get_featured_image_link($most_booked_guiding))
                            <img src="{{get_featured_image_link($most_booked_guiding)}}" class="card-img-top">
                            @else
                                <img src="{{asset('images/placeholder_guide.webp')}}" class="card-img-top">
                            @endif
                            <div class="card-body">
                            <h5 class="crop-text-2 card-title h6">{{translate($most_booked_guiding->title)}}</h5>
                            <small class="crop-text-1 small-text text-muted">{{translate($most_booked_guiding->location)}}</small>
                            <small class="fw-bold text-muted">@lang('message.from') <span class="color-primary">{{ $most_booked_guiding->getLowestPrice() }}€</span> p.P.</small>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
        @endif
    </div>
</section>

<section class="py-1 my-5 topmethods">
    <div class="container my-4">
        <div class="my-2 section-title">
            <div class="d-flex justify-content-between">
                <div>
                    <h2 class="h4 text-dark fw-bolder">@lang('homepage.methods-title')</h2>
                </div>
                @if(!$agent->ismobile())
                <div>
                    @if(app()->getLocale() == 'de')
                        <a href="{{ route('category.types', ['type' => 'methods']) }}" class="color-primary fw-light">@lang('homepage.fishingtype-title')</a>
                    @else
                        <a href="{{ route('category.types', ['type' => 'methods']) }}" class="color-primary fw-light">See all methods</a>
                    @endif
                </div>
                @endif
            </div>
            
            <p class="fw-light">@lang('homepage.fishingtype-message')</p>
        </div>
        @if($agent->ismobile())
        <div class="methods-custom-owl owl-carousel owl-theme">
            @foreach($CategoryPageMethods as $category)
                <div class="item">
                    <a href="{{ route('category.targets', ['type' => 'methods', 'slug' => $category->slug]) }}">
                        <div class="card">
                            <div class="row g-0">
                                <div class="col-5">
                                    <img class="img-fluid rounded-start img-top" src="{{asset($category->thumbnail_path ?? 'images/placeholder_guide.jpg')}}" style="height:100px; object-fit:cover;" alt="{{$category->source->name}}">
                                </div>
                                <div class="col-7">
                                    <div class="card-body py-2 px-3 d-flex align-items-center h-100">
                                        <h5 class="card-title mb-0">{{$category->source->name}}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
        <div class="p-1 d-flex justify-content-end">
            @if(app()->getLocale() == 'de')
                <a href="{{ route('category.types', ['type' => 'methods']) }}" class="color-primary fw-light">Alle Methoden ansehen</a>
            @else
                <a href="{{ route('category.types', ['type' => 'methods']) }}" class="color-primary fw-light">See all methods</a>
            @endif
        </div>
        @else
            <div class="row">
                @foreach($CategoryPageMethods as $category)
                    <div class="my-1 col-md-4">
                        <a href="{{ route('category.types', ['type' => 'methods', 'slug' => $category->slug]) }}">
                            <div class="flex-row card align-items-center">
                                <img class="card-img-left example-card-img-responsive img-top" src="{{asset($category->thumbnail_path ?? 'images/placeholder_guide.jpg')}}" style="width:150px;height:100px"/>
                                <div class="card-body">
                                <h5 class="card-title">{{$category->source->name}}</h5>
                                
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>

<section id="cta" class="py-1 my-5 banner">
    <div class="container">
        <div class="cta-row ">
            <div class="cta-left">
                <div class="video-one__left">
                    <h3 class="text-white h1 fw-bolder">@lang('homepage.guide-title')</h3>
                    <span class="my-2">@lang('homepage.guide-message')</span>
                    <div class="my-2 ms-lg-0 ms-2 d-flex flex-column">
                        <span><i class="fas fa-check-circle"></i> @lang('homepage.guide-list-1')</span>
                        <span><i class="fas fa-check-circle"></i> @lang('homepage.guide-list-2')</span>
                        <span><i class="fas fa-check-circle"></i> @lang('homepage.guide-list-3')</span>
                    </div>
                    
                </div>
            </div>
            <div class="cta-right">
                <a href="{{route('login')}}" class="about-one__btn thm-btn">@lang('homepage.guide-button')</a>

            </div>
        </div>
    </div>
</section>
<section class="py-1 my-5 popular-trips">
    <div class="container p-3 mt-0 popular-tours">
        <div class="my-2 section-title">
            <h2 class="h4 text-dark fw-bolder">@lang('homepage.newoffer-title')</h2>
            <p class="fw-light">@lang('homepage.newoffer-message')</p>
        </div>
        @if($agent->ismobile())
            <div class="new-custom-owl owl-carousel owl-theme">
                @foreach($newGuidings as $newGuiding)
                    <div class="item">
                        <a href="{{ route('guidings.show', [$newGuiding->id, $newGuiding->slug]) }}">
                            <div class="card" style="min-height:360px;">
                                @if(get_featured_image_link($newGuiding))
                                <img src="{{get_featured_image_link($newGuiding)}}" class="card-img-top">
                                @else
                                    <img src="{{asset('images/placeholder_guide.webp')}}" class="card-img-top">
                                @endif
                                <div class="card-body">
                                <h5 class="crop-text-2 card-title h6">{{translate($newGuiding->title)}}</h5>
                                <small class="crop-text-1 small-text text-muted">{{translate($newGuiding->location)}}</small>
                                <small class="fw-bold text-muted">@lang('message.from') <span class="color-primary">{{ $newGuiding->getLowestPrice() }}€</span> p.P.</small>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @else
        <div class="custom-owl owl-carousel owl-theme">
            @foreach($newGuidings as $newGuiding)
                <div class="item">
                    <a href="{{ route('guidings.show', [$newGuiding->id, $newGuiding->slug])}}">
                        <div class="card" style="min-height:360px;">
                            @if(get_featured_image_link($newGuiding))
                            <img src="{{get_featured_image_link($newGuiding)}}" class="card-img-top">
                            @else
                                <img src="{{asset('images/placeholder_guide.webp')}}" class="card-img-top">
                            @endif
                            <div class="card-body">
                            <h5 class="crop-text-2 card-title h6">{{translate($newGuiding->title)}}</h5>
                            <small class="crop-text-1 small-text text-muted">{{translate($newGuiding->location)}}</small>
                            <small class="fw-bold text-muted">@lang('message.from') <span class="color-primary">{{ $newGuiding->getLowestPrice() }}€</span> p.P.</small>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
        @endif
    </div>
</section>

<section class="py-1 my-5 toptargetfish">
    <div class="container my-4">
        <div class="my-2 section-title">
            <div class="d-flex justify-content-between">
                <div>
                    <h2 class="h4 text-dark fw-bolder">@lang('homepage.targetfish-title')</h2>
                </div>
                @if(!$agent->ismobile())
                <div>
                    @if(app()->getLocale() == 'de')
                        <a href="{{ route('category.types', ['type' => 'targets']) }}" class="color-primary fw-light">Alle Zielfische ansehen</a>
                    @else
                        <a href="{{ route('category.types', ['type' => 'targets']) }}" class="color-primary fw-light">See all target fish</a>
                    @endif
                </div>
                @endif
            </div>
            
            <p class="fw-light">@lang('homepage.targetfish-message')</p>
        </div>
        @if($agent->ismobile())
        <div class="methods-custom-owl owl-carousel owl-theme">
            @foreach($CategoryPage as $category)
                <div class="item">
                    <a href="{{ route('category.targets', ['type' => 'targets', 'slug' => $category->slug]) }}">
                        <div class="card">
                            <div class="row g-0">
                                <div class="col-5">
                                    <img class="img-fluid rounded-start img-top" src="{{asset($category->thumbnail_path ?? 'images/placeholder_guide.jpg')}}" style="height:100px; object-fit:cover;" alt="{{$category->source->name}}">
                                </div>
                                <div class="col-7">
                                    <div class="card-body py-2 px-3 d-flex align-items-center h-100">
                                        <h5 class="card-title mb-0">{{$category->source->name}}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
        <div class="p-1 d-flex justify-content-end">
            @if(app()->getLocale() == 'de')
                <a href="{{ route('category.types', ['type' => 'targets']) }}" class="color-primary fw-light">Alle Zielfische ansehen</a>
            @else
                <a href="{{ route('category.types', ['type' => 'targets']) }}" class="color-primary fw-light">See all target fish</a>
            @endif
        </div>
        @else
            <div class="row">
                @foreach($CategoryPage as $category)
                    <div class="my-1 col-md-4">
                        <a href="{{ route('category.targets', ['type' => 'targets', 'slug' => $category->slug]) }}">
                            <div class="flex-row card align-items-center">
                                <img class="card-img-left example-card-img-responsive img-top" src="{{asset($category->thumbnail_path ?? 'images/placeholder_guide.jpg')}}" style="width:150px;height:100px"/>
                                <div class="card-body">
                                <h5 class="card-title">{{$category->source->name}}</h5>
                                
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>

@if(count($latestThreads))
<section class="py-1 my-5 fishing-magazine">
    <div class="container my-5">
        <div class="my-2 section-title">
            <h2 class="h4 text-dark fw-bolder">@lang('homepage.magazine-title')</h2>
            <div class="see-more d-flex justify-content-md-between">
                <div>
                    <p class="fw-light">@lang('homepage.magazine-message')</p>
                </div>
            </div>
        </div>
        @if($agent->ismobile())
        <div class="custom-owl owl-carousel owl-theme">
            @foreach($latestThreads as $index => $thread)
            <div class="item">
                <a href="{{ route($blogPrefix.'.thread.show', [$thread->slug]) }}">
                    <div class="magazine-small-bg" style="background:url('{{ $thread->getThumbnailPath() }}');">
                    </div>
                    <div class="my-2 text-wrapper">
                        <h6 class="fw-bolder">{{$thread->title}}</h6>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
        @else
        <div class="row">
            @foreach($latestThreads as $index => $thread)
            @if($loop->first)
            <div class="my-1 col-12 col-md-6">
                <div class="section-card">
                    <a href="{{ route($blogPrefix.'.thread.show', [$thread->slug]) }}">
                        <div class="magazine-bg position-relative" style="  background: 
                        linear-gradient(
                          rgba(13, 13, 13, 0.45), 
                          rgba(0, 0, 0, 0.45)
                        ),
                        url('{{ $thread->getThumbnailPath() }}')">
                            <div class="">
                                <div class="overlay-wrapper"></div>
                                <div class="bottom-0 p-4 text-white section-card-main position-absolute">
                                    <div class="section-text-wrapper">
                                        <span class="section-title">{{$thread->title}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            @endif

            @endforeach

            <div class="my-1 col-12 col-md-6">
                <div class="row">
                    @foreach($latestThreads as $index => $thread)
                        @if($index >= 1)
                            <div class="col-6 col-xs-6">
                                <a href="{{ route($blogPrefix.'.thread.show', [$thread->slug]) }}">
                                    <div class="magazine-small-bg" style="background:url('{{ $thread->getThumbnailPath() }}');">
                                    </div>
                                    <div class="my-2 text-wrapper">
                                        <h6 class="fw-bolder">{{$thread->title}}</h6>
                                    </div>
                                </a>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        <div class="d-flex justify-content-end">
            <a href="/fishing-magazine" class="color-primary fw-light">@lang('homepage.see-more')</a>
        </div>

    </div>
</section>
@endif

<section class="py-1 my-5 faq">
    <div class="container my-5">
        <div class="my-2 section-title">
            <h2 class="h4 text-dark fw-bolder">@lang('homepage.faq-title')</h2>
        </div>
        <div class="accordion accordion-flush" id="accordionFlush">
                        @foreach(get_faqs_by_page('home') as $index => $faq)
                        <div class="accordion-item">
                        <h2 class="accordion-header" id="flush-heading{{$index}}">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-{{$index}}" aria-expanded="false" aria-controls="flush-{{$index}}">
                                @if(app()->getlocale()=='de')
                                {{$faq->question}}
                                @else
                                    {{translate($faq->question)}}
                                @endif
                            </button>
                        </h2>
                        <div id="flush-{{$index}}" class="accordion-collapse collapse" aria-labelledby="flush-heading{{$index}}" data-bs-parent="#accordionFlush">
                            <div class="accordion-body">
                                @if(app()->getlocale()=='de')
                                {!!$faq->answer!!}
                                @else
                                {!!translate($faq->answer)!!}
                                @endif

                            </div>
                        </div>
                        </div>
                        @endforeach
        </div>
    </div>
</section>


@endsection

@section('js_after')
<script>
    const buttons = document.querySelectorAll('.nav-link');
    
   $(".nav-tabs .nav-link").on('shown.bs.tab', function () {
           buttons.forEach(btn => btn.classList.remove('active'));
           this.classList.add('active'); 
           
   });
</script>

<script>
     var selectTarget = $('#home_target_fish');
      $("#home_target_fish").select2({
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
</script>



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
