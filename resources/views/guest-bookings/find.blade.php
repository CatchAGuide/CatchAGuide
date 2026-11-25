{{-- "Find my booking": the guest enters the email they booked with (FindMyBookingController). --}}
@extends('guest-bookings.layout')
@use('App\Services\GuestBooking\GuestLoginLinkService')

@section('gb-body')
    <section class="ga-card gb-page__state">
        <h1 class="ga-page__title">{{ __('guest_bookings.find.title') }}</h1>

        @if (session('find_booking_sent'))
            <p class="ga-note gb-page__note" role="status"><i class="fas fa-envelope" aria-hidden="true"></i> {{ __('guest_bookings.find.sent') }}</p>
            <p>{{ __('guest_bookings.find.sent_hint', ['minutes' => GuestLoginLinkService::LINK_MINUTES]) }}</p>
        @else
            @if (session('find_booking_signed_out'))
                <p class="ga-note gb-page__note" role="status">{{ __('guest_bookings.find.signed_out') }}</p>
            @elseif (session('find_booking_expired'))
                <p class="ga-note gb-page__note" role="status">{{ __('guest_bookings.find.expired_session') }}</p>
            @endif
            <p>{{ __('guest_bookings.find.intro') }}</p>

            <form method="POST" action="{{ route('guest-bookings.send') }}" class="gb-page__form" novalidate>
                @csrf
                <div class="ga-field">
                    <label for="gb-email" class="ga-label">{{ __('guest_bookings.find.email') }}</label>
                    <input id="gb-email" type="email" name="email" value="{{ old('email') }}" class="ga-input" autocomplete="email" required
                           @error('email') aria-invalid="true" aria-describedby="gb-email-error" @enderror>
                    @error('email')
                        <p id="gb-email-error" class="ga-error">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="ga-btn ga-btn--dark">{{ __('guest_bookings.find.submit') }}</button>
            </form>
        @endif
    </section>
@endsection
