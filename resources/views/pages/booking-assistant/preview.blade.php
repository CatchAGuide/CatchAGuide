@extends('layouts.app-v2-1')

@section('meta_robots')
    <meta name="robots" content="noindex, nofollow" />
@endsection

@section('title', __('booking-assistant.preview_page_title'))

@section('description', __('booking-assistant.preview_page_description'))

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-7">
                <p class="text-muted small mb-2">{{ __('booking-assistant.preview_page_label') }}</p>
                <h1 class="h3 mb-3">{{ __('booking-assistant.preview_page_title') }}</h1>
                <p class="mb-0">{{ __('booking-assistant.preview_page_note') }}</p>
            </div>
        </div>
    </div>
@endsection
