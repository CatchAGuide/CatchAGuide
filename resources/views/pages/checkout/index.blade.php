@extends('layouts.app-v2-1')

@section('title', 'Checkout')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.0.3/css/font-awesome.css" />
    {{-- Checkout-specific styles are now handled by SCSS compilation --}}
    <!-- template styles -->
    <link rel="stylesheet" href="{{ asset('assets/css/tevily.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/tevily-responsive.css') }}" />
    <!--Page Header Start-->
    {{-- <section class="page-header">
        <div class="page-header__top">
            <div class="page-header-bg-magazin" style="background-image: url({{asset('assets/images/shutterstock_620805824.jpg')}}); "></div>
            <div class="page-header-bg-overly-magazin"></div>
            <div class="container">
                <div class="page-header__top-inner">
                    <h2>@yield('title')</h2>
                </div>
            </div>
        </div>
        <div class="page-header__bottom">
            <div class="container">
                <div class="page-header__bottom-inner">
                    <ul class="thm-breadcrumb list-unstyled">
                        <li><a href="{{ route('welcome') }}">@lang('message.back')</a></li>
                        <li><span>&#183;</span></li>
                        <li class="active">@yield('title')</li>
                    </ul>
                </div>
            </div>
        </div>
    </section> --}}
    <!--Page Header End-->
    <livewire:checkout :guiding="$guiding" :persons="$persons" :initial-selected-date="$selectedDate" />
@endsection