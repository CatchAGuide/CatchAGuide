@extends('layouts.app-v2-1')

@section('title', __('checkout.camp.page_title').' · '.$checkout->product()['title'])

@section('meta_robots')
    <meta name="robots" content="noindex,nofollow">
@endsection

@section('custom_style')
    @include('pages.modern-checkout.partials.fonts')
@endsection

@section('content')
    @include('pages.camp-checkout.partials.layout', ['checkout' => $checkout])
@endsection

@push('js_push')
    <script src="{{ mix('js/camp-checkout.js') }}" defer></script>
@endpush
