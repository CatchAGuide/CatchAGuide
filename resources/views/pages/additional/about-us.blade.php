@extends('layouts.app')

@section('title', ucwords(translate('Über Uns')))

@section('content')
    <style>
        .video-one-bg:before {
            background-color: inherit !important;
        }
    </style>
    <!--Page Header Start-->
    <section class="page-header">
        <div class="page-header__top">
            <div class="video-one-bg"
                 style="background-image: url({{asset('assets/images/Coverbild_News_Blog_1.1.jpg')}}); background-position: bottom; width: auto !important; height: 258px !important;">
            </div>

            <div class="container">
                <div class="page-header__top-inner">
                    <h2>@lang('message.about-us')</h2>
                </div>
            </div>
        </div>
        <div class="page-header__bottom">
            <div class="container">
                <div class="page-header__bottom-inner">
                    <ul class="thm-breadcrumb list-unstyled">
                        <li><a href="{{ route('welcome') }}">@lang('message.home')</a></li>
                        <li><span>&#183;</span></li>
                        <li class="active">@lang('message.about-us')</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    <!--Page Header End-->


    <!--About Page Start-->
    <!--Mission Start-->
    <section class="team-one">
        <div class="container">
            <div class="section-title text-center">
                <span class="section-title__tagline" style="color: var(--thm-primary)">Mission</span>
                <h2 class="section-title__title">@lang('about-us.ourGoal')</h2>
            </div>
            <div class="row col-12" style="margin:auto">
                <p class="text-align:center">@lang('about-us.ourGoalMsg')</p>
            </div>
        </div>
    </section>
    <!--Mission End-->


    <!--About Page End-->
    <!--Why Choose Start-->
    <section class="why-choose">
        <div class="why-choose__container mb-4">
            <div class="why-choose__left">
                <div class="why-choose__left-bg"
                     style="background-image: url({{asset('assets/images/SonstigeNutzung1.3.png')}}); height: 900px"></div>
            </div>
            <div class="why-choose__right col-8">
                <div class="why-choose__right-map col-3"
                     style="background-image: url({{asset('assets/images/shapes/why-choose-right-map.png')}})"></div>
                <div class="why-choose__right-content col-9 {{$agent->ismobile() ? 'text-center' : ''}}">
                    <div class="section-title text-left">
                        <span class="section-title__tagline" style="color: var(--thm-primary)">@lang('about-us.searchAndFind')</span>
                        <h2 class="section-title__title">@lang('about-us.whyCatch')</h2>
                    </div>
                    <ul class="list-unstyled why-choose__list">
                        <li>
                            <div class="text">
                                <h4>@lang('about-us.findGuide')</h4>
                                <p>@lang('about-us.findGuideMsg')
                            
                                </p>
                            </div>
                        </li>
                        <li>

                            <div class="text">
                                <h4>@lang('about-us.becomeAGuide')</h4>
                                <p>@lang('about-us.becomeAGuideMsg')
                                </p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    <!--Why Choose End-->
    <!--Book Now Start-->
    <section class="book-now">
        <div class="book-now-shape"
             style="background-image: url({{asset('assets/images/shapes/book-now-shape.png')}})"></div>
        <div class="container">
            <div class="row">
                <div class="col-xl-12">
                    <div class="book-now__inner">
                        <div class="book-now__left">
                            <p>@lang('about-us.plan')</p>
                            <h2>@lang('about-us.findYourNext')</h2>
                        </div>
                        <div class="book-now__right">
                            <a href="{{route('guidings.index')}}" class="thm-btn book-now__btn">@lang('about-us.findBtn')</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--Book Now End-->




@endsection
