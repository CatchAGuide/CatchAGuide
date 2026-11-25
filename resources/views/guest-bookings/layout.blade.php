{{-- Shell of the "Find my booking" pages: public layout, no search band, noindex, no referrer.
     Pages fill the gb-body section. Wording rule: "your booking", never "account" or "profile". --}}
@extends('layouts.app-v2-1')

@section('title', __('guest_bookings.find.title'))

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
    <div @class(['gb-page', 'gb-page--narrow' => ! ($wide ?? false), 'gb-page--live' => $live ?? false])>
        @yield('gb-body')

        <p class="ga-help gb-page__privacy">
            {{ __('guest_bookings.privacy') }}
            <a href="{{ route('law.data-protection') }}">{{ __('guest_bookings.privacy_link') }}</a>
        </p>
    </div>
</div>
@endsection
