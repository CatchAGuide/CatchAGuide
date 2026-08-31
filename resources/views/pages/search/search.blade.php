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
                <h1 class="h2">{{ucwords(isset($place) ? __('guidings.all_guidings_at') . $place : __('profile.all_guidings') )}}</h1>
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
                        {{ucwords( isset($place) ? __('guidings.all_guidings_at') . $place : __('profile.all_guidings'))}}
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

@endsection