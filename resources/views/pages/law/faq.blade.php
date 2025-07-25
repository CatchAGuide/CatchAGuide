@extends('layouts.app-v2')

@section('title', 'FAQ')
@section('description',translate('Frequently Asked Questions for Catch A Guide'))

@section('content')
<div class="container">
        <section class="page-header">
            <div class="page-header__bottom breadcrumb-container guiding">
                <div class="page-header__bottom-inner">
                    <ul class="thm-breadcrumb list-unstyled">
                        <li><a href="{{ route('welcome') }}">@lang('message.home')</a></li>
                        <li><span><i class="fas fa-solid fa-chevron-right"></i></span></li>
                        <li class="active">@yield('title')</li>
                    </ul>
                </div>
            </div>
        </section>
    </div>
    <!--Page Header Start-->
    <!-- <section class="page-header">
        <div class="page-header__top">
            <div class="page-header-bg-magazin" style="background-image: url({{asset('assets/images/faq.jpg')}}); "></div>
            <div class="page-header-bg-overly-magazin"></div>
            <div class="container">
                <div class="page-header__top-inner">
                    <h1 class="h2">@yield('title')</h1>
                </div>
            </div>
        </div>
        <div class="page-header__bottom">
            <div class="container">
                <div class="page-header__bottom-inner">
                    <ul class="thm-breadcrumb list-unstyled">
                        <li><a href="{{ route('welcome') }}">@lang('message.home')</a></li>
                        <li><span>&#183;</span></li>
                        <li class="active">@yield('title')</li>
                    </ul>
                </div>
            </div>
        </div>
    </section> -->
    <!--Page Header End-->
    <div class="container my-3">
        <h1>Frequently Asked Questions</h1>
        <br>
        <div class="accordion" id="accordionExample">
            @foreach($faqs as $faq)
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading-{{ $faq->id }}">
                        <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $faq->id }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="collapse-{{ $faq->id }}">
                            {{ translate($faq->question) }}
                        </button>
                    </h2>
                    <div id="collapse-{{ $faq->id }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" aria-labelledby="heading-{{ $faq->id }}" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            {!!  translate($faq->answer) !!}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
