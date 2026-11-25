{{-- Every booking under the verified email (Livewire Customer\GuestBookings). --}}
@extends('guest-bookings.layout', ['wide' => true, 'live' => true])

@section('gb-body')
    @livewire('customer.guest-bookings')

    <section class="ga-card gb-page__help">
        <h2 class="ga-card__title">{{ __('guest_bookings.help_title') }}</h2>
        <p>{{ __('guest_bookings.help_tour') }}</p>
        <a href="{{ route('additional.contact') }}" class="ga-btn ga-btn--dark ga-btn--sm">{{ __('guest_bookings.contact_support') }}</a>
    </section>
@endsection
