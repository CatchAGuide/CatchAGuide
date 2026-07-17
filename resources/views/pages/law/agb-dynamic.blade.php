@extends('layouts.app-v2-1')

@section('title', $translation->title . ' - ' . ucwords(translate('Allgemeine Geschäftsbedingungen')))
@section('meta_robots')
    <meta name="robots" content="noindex, nofollow">
@endsection

@include('pages.law.partials.terms-page-assets')

@php
    $sectionIcons = [
        'fa-building', 'fa-handshake', 'fa-file-signature', 'fa-user-check', 'fa-euro-sign',
        'fa-gavel', 'fa-calendar-check', 'fa-shield-alt', 'fa-lock', 'fa-balance-scale',
    ];
    $icon = $sectionIcons[($sectionNumber - 1) % count($sectionIcons)];
@endphp

@section('content')
<div id="termsProgress" class="terms-progress"></div>

<div class="container">
    <section class="page-header">
        <div class="page-header__bottom breadcrumb-container guiding">
            <div class="page-header__bottom-inner">
                <ul class="thm-breadcrumb list-unstyled">
                    <li><a href="{{ route('welcome') }}">@lang('message.home')</a></li>
                    <li><span><i class="fas fa-solid fa-chevron-right"></i></span></li>
                    <li><a href="{{ route('law.agb') }}">@lang('message.term-conditions')</a></li>
                    <li><span><i class="fas fa-solid fa-chevron-right"></i></span></li>
                    <li class="active">{{ $translation->title }}</li>
                </ul>
            </div>
        </div>
    </section>
</div>

<section class="terms-page">
    <div class="container">

        <div class="terms-hero">
            <span class="terms-hero__eyebrow"><i class="fas fa-balance-scale"></i> @lang('terms.eyebrow')</span>
            <h1>@lang('message.term-conditions')</h1>
            <p class="terms-hero__subtitle">@lang('terms.subtitle')</p>
            <div class="terms-hero__meta">
                <span class="terms-hero__badge"><i class="fas fa-list-ol"></i> {{ count($navItems) }} @lang('terms.sections')</span>
                <span class="terms-hero__badge"><i class="fas fa-clock"></i> <span id="termsReadTime"></span></span>
                <button type="button" class="terms-print-btn" onclick="window.print()">
                    <i class="fas fa-print"></i> @lang('terms.print')
                </button>
            </div>
        </div>

        <div class="row">
            @include('pages.law.partials.terms-sidebar')

            <div class="col-lg-8 col-xl-9">
                <div id="termsContent">
                    <article class="terms-section">
                        <div class="terms-section__header">
                            <span class="terms-section__icon"><i class="fas {{ $icon }}"></i></span>
                            <div class="terms-section__heading">
                                <span class="terms-section__kicker">&sect; {{ $sectionNumber }}</span>
                                <h2 class="terms-section__title">{{ $translation->title }}</h2>
                            </div>
                            <button type="button" class="terms-copy-link" aria-label="@lang('terms.copy_link')" title="@lang('terms.copy_link')">
                                <i class="fas fa-link"></i>
                            </button>
                        </div>
                        <div class="terms-section__body">
                            {!! $translation->content !!}
                        </div>
                    </article>

                    <div class="terms-no-results" id="termsNoResults">
                        <i class="fas fa-search"></i>
                        <h5>@lang('terms.no_results_title')</h5>
                        <p class="mb-0">@lang('terms.no_results_text')</p>
                    </div>

                    <nav class="terms-pager">
                        @if($prevItem)
                            <a href="{{ $prevItem['url'] }}" class="terms-pager__link terms-pager__link--prev">
                                <span class="terms-pager__label"><i class="fas fa-arrow-left"></i> @lang('terms.prev')</span>
                                <span class="terms-pager__title">{{ $prevItem['title'] }}</span>
                            </a>
                        @else
                            <span></span>
                        @endif

                        @if($nextItem)
                            <a href="{{ $nextItem['url'] }}" class="terms-pager__link terms-pager__link--next">
                                <span class="terms-pager__label">@lang('terms.next') <i class="fas fa-arrow-right"></i></span>
                                <span class="terms-pager__title">{{ $nextItem['title'] }}</span>
                            </a>
                        @endif
                    </nav>
                </div>
            </div>
        </div>
    </div>
</section>

<button type="button" class="terms-back-top" id="termsBackTop" aria-label="@lang('terms.back_to_top')">
    <i class="fas fa-arrow-up"></i>
</button>
@endsection
