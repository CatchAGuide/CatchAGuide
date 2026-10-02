@extends('layouts.app-v2-1')

@section('title', __('checkout.reject.success.title'))

@section('meta_robots')
    <meta name="robots" content="noindex,nofollow">
@endsection

@section('custom_style')
    @include('pages.modern-checkout.partials.fonts')
@endsection

@section('content')
    <div class="tc tc--state">
        <div class="tc__container">
            <section class="tc-card tc-state" aria-labelledby="tc-state-title">
                <span class="tc-steps__icon tc-state__icon">
                    @include('pages.modern-checkout.partials.icon', ['name' => 'check', 'size' => 20])
                </span>
                <h1 class="tc__title tc-state__title" id="tc-state-title">{{ __('checkout.reject.success.title') }}</h1>
                <p class="tc-state__text">{{ __('checkout.reject.success.text') }}</p>

                <div class="tc-state__actions">
                    <a class="tc-submit tc-state__primary" href="{{ route('profile.bookings') }}">{{ __('checkout.reject.success.cta_profile') }}</a>
                    <a class="tc-state__secondary" href="{{ route('welcome') }}">{{ __('checkout.reject.success.cta_home') }}</a>
                    <a class="tc-link tc-state__link" href="{{ route('additional.contact') }}">{{ __('checkout.reschedule.cta_contact') }}</a>
                </div>
            </section>
        </div>
    </div>
@endsection
