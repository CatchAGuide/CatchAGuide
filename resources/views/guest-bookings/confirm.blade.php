{{-- Landing of the emailed sign-in link. The button uses the link up (a mail scanner opening
     the link doesn't). $usable: false when the link expired or was used. --}}
@extends('guest-bookings.layout')

@section('gb-body')
    <section class="ga-card gb-page__state">
        @if ($usable)
            <h1 class="ga-page__title">{{ __('guest_bookings.find.confirm_title') }}</h1>
            <p>{{ __('guest_bookings.find.confirm_text') }}</p>
            <form method="POST" action="{{ route('guest-bookings.start') }}" class="gb-page__actions">
                @csrf
                <button type="submit" class="ga-btn ga-btn--dark">{{ __('guest_bookings.find.confirm_button') }}</button>
            </form>
        @else
            <h1 class="ga-page__title">{{ __('guest_bookings.find.link_expired_title') }}</h1>
            <p>{{ __('guest_bookings.find.link_expired_text') }}</p>
            <div class="gb-page__actions">
                <a href="{{ route('guest-bookings.find') }}" class="ga-btn ga-btn--dark ga-btn--sm">{{ __('guest_bookings.find.new_link') }}</a>
            </div>
        @endif
    </section>
@endsection
