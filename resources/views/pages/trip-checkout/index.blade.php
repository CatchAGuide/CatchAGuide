@extends('layouts.app-v2-1')

@section('title', __('checkout.trip.page_title').' · '.$checkout->product()['title'])

@section('meta_robots')
    <meta name="robots" content="noindex,nofollow">
@endsection

@section('custom_style')
    @include('pages.modern-checkout.partials.fonts')
@endsection

@section('content')
    @include('pages.trip-checkout.partials.layout', ['checkout' => $checkout])
@endsection

@push('js_push')
    <script src="{{ mix('js/trip-checkout.js') }}" defer></script>
@endpush
