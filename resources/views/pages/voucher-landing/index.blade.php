@extends('layouts.app-v2')

{{--
    Gift voucher landing page (/guidings/voucher). Expects $landing (VoucherLandingViewModel);
    on the thank-you URL also $conversion (ConversionDataLayer), and the form shows the summary.
    The voucherLanding Alpine component (resources/js/vouchers/voucher-landing.js) drives the
    live preview, the region tiles and the client-side checks; the form posts normally.
--}}
@php
    $copy = 'guidings.voucher_landing.';
    $success = $landing->success();
    $xmas = $landing->isChristmasSeason();
@endphp

@section('title', __($copy.'meta_title'))
@section('description', __($copy.'meta_description'))

@if($success)
    @section('meta_robots')
        <meta name="robots" content="noindex,nofollow">
    @endsection
    @include('layouts.partials.conversion-datalayer')
@endif

@section('share_tags')
    <meta property="og:title" content="{{ __($copy.'meta_title') }}" />
    <meta property="og:description" content="{{ __($copy.'meta_description') }}" />
    <meta property="og:image" content="{{ asset('assets/images/vouchers/landing-hero.jpg') }}" />
@endsection

@section('custom_style')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..100,700..900&family=IBM+Plex+Mono:wght@500;600&family=Inter:wght@400;500;600;700&display=swap">
@endsection

@section('content')
<div class="vl" x-data="voucherLanding" data-analytics-page="voucher-landing">
    @include('pages.voucher-landing.partials.hero')
    @include('pages.voucher-landing.partials.why')
    @include('pages.voucher-landing.partials.tiles')
    @include('pages.voucher-landing.partials.steps')
    @include('pages.voucher-landing.partials.form')
    @include('pages.voucher-landing.partials.gallery')
    @include('pages.voucher-landing.partials.faq')
    @include('pages.voucher-landing.partials.final')

    @unless($success)
        <div class="vl-bar" :class="{ 'is-visible': showBar }" aria-hidden="true" x-cloak>
            <div class="vl-bar__amount">
                <span class="vl-mono" x-text="amountLabel"></span>
                <small>{{ __($copy.'mobile_bar_label') }}</small>
            </div>
            <a href="#gutschein" class="vl-btn vl-btn--sm" tabindex="-1">{{ __($copy.'cta') }}</a>
        </div>
    @endunless
</div>

<script type="application/json" id="voucher-landing-config">@json($landing->clientConfig($errors ?? null, session()->getOldInput()))</script>
@endsection

@push('js_push')
    <script src="{{ mix('js/voucher-landing.js') }}" defer></script>
@endpush
