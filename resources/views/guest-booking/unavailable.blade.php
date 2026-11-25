{{-- A booking link that can't be used: unknown, turned off, or opened in a browser that never
     clicked the email link. Never a dead end. --}}
@extends('layouts.app-v2-1')

@section('title', __('guest_bookings.unavailable.title'))

@section('meta_robots')
    <meta name="robots" content="noindex,nofollow">
@endsection

@section('custom_style')
    <meta name="referrer" content="no-referrer">
@endsection

@section('css_after')
    <link rel="stylesheet" href="{{ mix('css/guide-area.css') }}" />
@endsection

@section('content')
<div class="ga-body">
    <div class="gb-page">
        <section class="ga-card gb-page__state">
            <h1 class="ga-page__title">{{ __('guest_bookings.unavailable.title') }}</h1>
            <p>{{ __('guest_bookings.unavailable.text') }}</p>
            <div class="gb-page__actions">
                <a href="{{ route('guest-bookings.find') }}" class="ga-btn ga-btn--dark ga-btn--sm">{{ __('guest_bookings.find.nav') }}</a>
                <a href="{{ route('additional.contact') }}" class="ga-btn ga-btn--outline ga-btn--sm">{{ __('guest_bookings.contact_support') }}</a>
                @auth('web')
                    <a href="{{ route('profile.bookings') }}" class="ga-btn ga-btn--outline ga-btn--sm">{{ __('guest_bookings.unavailable.signed_in') }}</a>
                @endauth
                <a href="{{ route('welcome') }}" class="ga-btn ga-btn--outline ga-btn--sm">{{ __('guest_bookings.unavailable.home') }}</a>
            </div>
        </section>
    </div>
</div>
@endsection
