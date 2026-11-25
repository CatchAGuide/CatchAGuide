{{-- One booking from the per-booking guest link (GuestBookingController::show). Same card as
     the customer area, under a plain header: no account menu, no "profile" wording. --}}
@extends('layouts.app-v2-1')

@section('title', __('guest_bookings.title'))

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
        <header class="ga-page__header">
            <div>
                <p class="gb-page__eyebrow">{{ __($type === 'voucher' ? 'guest_bookings.eyebrow_voucher' : 'guest_bookings.eyebrow') }}</p>
                <h1 class="ga-page__title">{{ __('guest_bookings.title') }}</h1>
                <p class="ga-page__subtitle">{{ __('guest_bookings.subtitle') }}</p>
            </div>
        </header>

        @if ($redacted)
            <p class="ga-note gb-page__note"><i class="fas fa-lock" aria-hidden="true"></i> {{ __('guest_bookings.redacted_note') }}</p>
        @endif

        <ul class="ga-booking-list">
            @if ($type === 'tour')
                @include('livewire.customer.partials.tour-card', ['item' => $item, 'focused' => true])
            @elseif ($type === 'voucher')
                @include('livewire.customer.partials.voucher-card', ['item' => $item])
            @else
                @include('livewire.customer.partials.request-card', ['item' => $item])
            @endif
        </ul>

        @if ($calendar || $voucher_pdf)
            <div class="gb-page__actions">
                @if ($calendar)
                    <a href="{{ route('guest-booking.calendar', ['access' => $access->id]) }}" class="ga-btn ga-btn--outline ga-btn--sm" rel="nofollow">
                        <i class="far fa-calendar-plus" aria-hidden="true"></i> {{ __('guest_bookings.add_to_calendar') }}
                    </a>
                @endif
                @if ($voucher_pdf)
                    <a href="{{ route('guest-booking.voucher-pdf', ['access' => $access->id]) }}" class="ga-btn ga-btn--outline ga-btn--sm" rel="nofollow">
                        <i class="far fa-file-pdf" aria-hidden="true"></i> {{ __('guest_bookings.download_voucher') }}
                    </a>
                @endif
            </div>
        @endif

        <section class="ga-card gb-page__help">
            <h2 class="ga-card__title">{{ __('guest_bookings.help_title') }}</h2>
            <p>{{ __($type === 'tour' ? 'guest_bookings.help_tour' : 'guest_bookings.help_other') }}</p>
            <a href="{{ route('additional.contact') }}" class="ga-btn ga-btn--dark ga-btn--sm">{{ __('guest_bookings.contact_support') }}</a>
        </section>

        <p class="gb-page__all">
            <a href="{{ route('guest-bookings.find') }}" class="ga-plain-link">{{ __('guest_bookings.find.see_all') }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
        </p>

        <p class="ga-help gb-page__privacy">
            {{ __('guest_bookings.privacy') }}
            <a href="{{ route('law.data-protection') }}">{{ __('guest_bookings.privacy_link') }}</a>
        </p>
    </div>
</div>
@endsection
