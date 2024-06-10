@extends('layouts.app')

@section('title', $thread->title)
@section('description',$thread->excerpt)


@section('share_tags')
<meta property="og:title" content="{{ $thread->title }}" />
<meta property="og:description" content="{{$thread->excerpt}}" />
<meta property="og:image" content="{{ $thread->getThumbnailPath() }}"/>
@endsection

@section('content')
    <!--News One Start-->
    <section class="news-details">
        <div class="container">
            <div class="row">
                <div class="col-xl-8 col-lg-7">
                    <div class="news-details__left">
                        <div class="news-details__img">
                            <img src="{{ $thread->getThumbnailPath() }}" alt="">
                            <div class="news-one__date">
                                <p>{{ $thread->created_at->format('d') }} <br>
                                    <span>{{ $thread->created_at->shortMonthName }}</span></p>
                            </div>
                        </div>
                        <div class="news-details__content">
                            <h1 class="news-details__title">{{ $thread->title }}</h1>
                            <p class="news-details__text-1">{!! $thread->body !!}</p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-5">
                    <div class="sidebar">
                        <div class="sidebar__single sidebar__post {{$agent->ismobile() ? 'text-center' : ''}}">
                            <ul class="list-unstyled">
                                <li>{{$thread->author}}</li>
                                <li>{{getLocalizedValue($thread->category)}}</li>
                                <li>{{ $thread->created_at->format('d.m.Y') }}</li>
                            </ul>
                            <div class="news-details__social-list {{$agent->ismobile() ? 'text-center' : ''}}"
                                 style=" {{$agent->ismobile() ? 'justify-content: center;' : ''}}">
                                <a href="#"><i class="fab fa-facebook" ></i></a>
                                <a href="tel: +4915752996580"><i class="fab fa-whatsapp"></i></a>
                                <a href="https://www.instagram.com/catchaguide_official/"><i class="fab fa-instagram"></i></a>
                            </div>
                        </div>
                        <div class="sidebar__single sidebar__category {{$agent->ismobile() ? 'text-center' : ''}}">
                            <h3 class="sidebar__title">{{translate('Alle Kategorien')}}</h3>
                            <ul class="sidebar__category-list list-unstyled">
                                @foreach($categories as $category)
                                    <li>
                                        <a href="{{ route($blogPrefix.'.categories.show', $category) }}">{{getLocalizedValue($category)}}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 {{$agent->ismobile() ? 'text-center' : ''}}">
                        <div class="news-details__bottom">
                            <div class="row">
                                <h2 class="sidebar__title">Weitere Beiträge</h2>
                                @php($i = 1)
                                @foreach($recent_threads as $thread)
                                    <div class="col-xl-4 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay="{{ $i }}00ms">
                                        <!--News One Single-->
                                        <div class="news-one__single">
                                            <div class="news-one__img">
                                                <img src="{{ $thread->getThumbnailPath() }}" alt="">
                                                <a href="{{ route($blogPrefix.'.thread.show',[$thread->slug]) }}">
                                                    <span class="news-one__plus"></span>
                                                </a>
                                                <div class="news-one__date">
                                                    <p>{{ $thread->created_at->format('d') }} <br>
                                                        <span>{{ $thread->created_at->shortMonthName }}</span></p>
                                                </div>
                                            </div>
                                            <div class="news-one__content">
                                                <h3 class="news-one__title">
                                                    <a href="{{ route($blogPrefix.'.thread.show',[$thread->slug]) }}">{{ $thread->title }}</a>
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                    @php($i++)
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--News One End-->
@endsection
