@extends('layouts.app-v2')

@section('title', __('message.magazine_meta_title'))
@section('description',__('message.magazine_meta_description'))

@section('header_title', __('message.Magazine'))
@section('header_sub_title', '')


@section('content')

<div class="container">
    <section class="page-header">
        <div class="page-header__bottom">
            <div class="container">
                <div class="page-header__bottom-inner">
                    <ul class="thm-breadcrumb list-unstyled">
                        <li><a href="{{ route('welcome') }}">@lang('message.home')</a></li>
                        <li><span>&#183;</span></li>
                        <li class="active">{{ __('message.Magazine') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    <!--News One Start-->
    <section class="news-one" style="padding: 25px;">
        <div class="container">
            <div class="row">
                @php($i = 1)
                @foreach($threads as $thread)
                    <div class="col-xl-4 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay="{{ $i }}00ms" style=" border-radius: 5px; border-color: #e8604c; border-width: 2px; border-style: solid; padding: 10px">
                        <!--News One Single-->
                        <div class="news-one__single">
                            <div class="news-one__img">
                                <div  style="height:300px; position: relative; overflow: hidden;">
                                    <img src="{{ $thread->getThumbnailPath() }}" alt="">
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
                                    <a href="{{ route($blogPrefix.'.thread.show',[$thread->slug]) }}">{{ translate($thread->title) }}</a>
                                </h3>
                            </div>
                        </div>
                    </div>
                    @php($i++)
                @endforeach
            </div>
        </div>
    </section>
</div>
    <!--News One End-->
@endsection
