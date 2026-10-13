@extends('layouts.app-v2-1')

@section('title', __('cookie.settings-title'))

@section('meta_robots')
    <meta name="robots" content="noindex, nofollow">
@endsection

@section('content')
    <div class="container">
        <section class="page-header">
            <div class="page-header__bottom breadcrumb-container guiding">
                <div class="page-header__bottom-inner">
                    <ul class="thm-breadcrumb list-unstyled">
                        <li><a href="{{ route('welcome') }}">@lang('message.home')</a></li>
                        <li><span><i class="fas fa-solid fa-chevron-right"></i></span></li>
                        <li class="active">@lang('cookie.settings-title')</li>
                    </ul>
                </div>
            </div>
        </section>
    </div>

    <section class="about-pages">
        <div class="container">
            <div class="cag-cookie-page">
                <h1 class="h2">@lang('cookie.settings-title')</h1>
                <p class="cag-cookie__text">
                    @lang('cookie.settings-intro')
                    <a href="{{ route('law.data-protection') }}">@lang('cookie.policy-link')</a>
                </p>
                @include('layouts.partials.cookie-settings-panel', ['panelKey' => 'page'])
            </div>
        </div>
    </section>
@endsection
