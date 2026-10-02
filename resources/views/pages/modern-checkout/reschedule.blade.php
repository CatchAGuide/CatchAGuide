@extends('layouts.app-v2-1')

@section('title', __('checkout.reschedule.title'))

@section('meta_robots')
    <meta name="robots" content="noindex,nofollow">
@endsection

@section('custom_style')
    <meta name="referrer" content="no-referrer">
    @include('pages.modern-checkout.partials.fonts')
@endsection

@section('content')
    @include('pages.modern-checkout.partials.layout', [
        'checkout' => $checkout,
        'title' => __('checkout.reschedule.title'),
        'steps' => [
            'send' => 'checkout.reschedule.step_pick',
            'clock' => 'checkout.tour.step_confirm',
            'coin' => 'checkout.tour.step_pay',
        ],
        'intro' => 'pages.modern-checkout.partials.reschedule-intro',
        'introData' => compact('guideName', 'guideMessage', 'originalDate'),
        'calendarNote' => __('checkout.reschedule.suggested_dates'),
        'submitLabel' => __('checkout.reschedule.submit'),
        'submitShortLabel' => __('checkout.reschedule.submit_short'),
        // The emailed link already authenticates the request; no extra hurdle for the customer.
        'withCaptcha' => false,
    ])
@endsection

@push('js_push')
    <script src="{{ mix('js/tour-checkout.js') }}" defer></script>
@endpush
