@extends('layouts.app')

@section('content')
<section class="page-header">
    <div class="page-header__top">
        <div class="page-header-bg"
             style="background-image: url({{asset('assets/images/allguidings.jpg')}})">
        </div>
        <div class="page-header-bg-overly"></div>
        <div class="container">
            <div class="page-header__top-inner">
                {{-- <h1 class="h2">{{ucwords(isset($place) ? translate('Alle Guidings bei ') . $place : translate('Alle Guidings') )}}</h1> --}}
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
                        {{-- {{ucwords( isset($place) ? translate('Alle Guidings bei ') . $place : translate('Alle Guidings'))}} --}}
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="news-one" style="padding: 25px;">
    <div class="container">
        <div class="row">
                <div class="col-xl-4 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay="100ms" style=" border-radius: 5px; border-color: #e8604c; border-width: 2px; border-style: solid; padding: 10px">
                    <!--News One Single-->
                    <div class="news-one__single">
                        <div class="news-one__img">
                            <div  style="height:300px; position: relative; overflow: hidden;">
                                <img src="#" alt="">
                            </div>

                            <a href="{{ route($blogPrefix.'.thread.show',[$thread->slug]) }}">
                                <span class="news-one__plus"></span>
                            </a>
                            <div class="news-one__date">
                                <p>{{ $thread->created_at->format('d') }} <br> <span>{{ $thread->created_at->shortMonthName }}</span></p>
                            </div>
                        </div>
                        <div class="news-one__content">
                            <span>{{$thread->author}}</span>
                            <h3 class="news-one__title">
                                <a href="#">{{ translate($thread->title) }}</a>
                            </h3>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</section>

@endsection
