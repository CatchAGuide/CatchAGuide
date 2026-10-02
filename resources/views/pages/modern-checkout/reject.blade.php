{{-- Guide declines a booking request and suggests alternative dates (/booking-reject/{token}).
     Same design system and calendar as the tour checkout; separate mobile/desktop layouts in
     resources/sass/page/_tour-checkout.scss (.tc--reject). --}}
@extends('layouts.app-v2-1')

@section('title', __('checkout.reject.title'))

@section('meta_robots')
    <meta name="robots" content="noindex,nofollow">
@endsection

@section('custom_style')
    <meta name="referrer" content="no-referrer">
    @include('pages.modern-checkout.partials.fonts')
@endsection

@section('content')
    @php
        $partials = 'pages.modern-checkout.partials';
        $request = $reject->request();
        $max = $reject->maxDates();
    @endphp

    <div class="tc tc--reject" x-data="bookingReject">
        <div class="tc__container">
            <div class="tc__grid">
                @include($partials.'.head', [
                    'title' => __('checkout.reject.title'),
                    'steps' => [
                        'calendar' => 'checkout.reject.step_dates',
                        'message' => 'checkout.reject.step_message',
                        'send' => 'checkout.reject.step_send',
                    ],
                    'intro' => $partials.'.reject-intro',
                    'introData' => ['customer' => $request['customer'], 'max' => $max],
                ])

                @include($partials.'.calendar', [
                    'title' => __('checkout.reject.dates_title'),
                    'note' => __('checkout.reject.dates_note', ['max' => $max]),
                    'showSelected' => false,
                    'afterPartial' => $partials.'.reject-dates',
                ])

                @include($partials.'.reject-message')

                <div class="tc__aside">
                    @include($partials.'.product', ['product' => $reject->product()])

                    <div class="tc__summary">
                        @include($partials.'.reject-summary', ['request' => $request])
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="application/json" id="booking-reject-config">@json($reject->clientConfig())</script>
@endsection

@push('js_push')
    <script src="{{ mix('js/booking-reject.js') }}" defer></script>
@endpush
