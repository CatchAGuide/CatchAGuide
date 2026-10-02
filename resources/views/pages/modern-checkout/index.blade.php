@extends('layouts.app-v2-1')

@section('title', __('checkout.booking_request'))

@section('meta_robots')
    <meta name="robots" content="noindex,nofollow">
@endsection

@section('custom_style')
    @include('pages.modern-checkout.partials.fonts')
@endsection

@section('content')
    @include('pages.modern-checkout.partials.layout', ['checkout' => $checkout])
@endsection

@push('js_push')
    <script src="{{ mix('js/tour-checkout.js') }}" defer></script>
@endpush
