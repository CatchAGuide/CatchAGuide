@extends('layouts.app-v2-1')

@section('title', __('vacations.rating_title', ['listing' => $summary['title']]))

@section('meta_robots')
    <meta name="robots" content="noindex,nofollow">
@endsection

@php
    $scoreFields = [
        'rating_overall' => __('vacations.rating_overall'),
        'rating_accommodation' => __('vacations.rating_accommodation'),
        'rating_fishing' => __('vacations.rating_fishing'),
        'rating_value' => __('vacations.rating_value'),
    ];
@endphp

@section('content')
    <div class="container py-4 rating-page-container">
        <div class="rating-wrapper shadow-lg">
            <div class="rating-header">
                <h3>
                    {{ __('vacations.rating_heading') }}
                    @if($summary['title'] !== '')
                        <span class="guide-name-highlight">{{ $summary['title'] }}</span>
                    @endif
                </h3>
                <p class="text-muted">{{ __('vacations.rating_subtitle') }}</p>
            </div>

            <div class="booking-summary">
                <h5 class="booking-summary-title">{{ __('guidings.rating_booking_summary_title') }}</h5>
                <div class="booking-summary-grid">
                    @foreach([
                        ['icon' => 'fa-campground', 'label' => __('vacations.rating_listing'), 'value' => $summary['title']],
                        ['icon' => 'fa-map-marker-alt', 'label' => __('guidings.rating_booking_location'), 'value' => $summary['location']],
                        ['icon' => 'fa-calendar-alt', 'label' => __('vacations.rating_dates'), 'value' => $summary['dates']],
                        ['icon' => 'fa-users', 'label' => __('guidings.rating_booking_guests'), 'value' => $summary['persons']],
                    ] as $item)
                        <div class="booking-summary-item">
                            <div class="booking-summary-label-row">
                                <div class="booking-summary-icon-circle"><i class="fas {{ $item['icon'] }}"></i></div>
                                <div class="booking-summary-label">{{ $item['label'] }}</div>
                            </div>
                            <div class="booking-summary-value">{{ filled($item['value']) ? $item['value'] : '–' }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <form action="{{ route('ratings.vacation.store', ['type' => $type, 'token' => $token]) }}" method="POST" class="rating-form">
                @csrf

                <div class="alert alert-danger d-none" id="ratingError" role="alert">
                    @if ($errors->any())
                        <ul class="mb-0 list-unstyled">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @else
                        {{ __('guidings.rating_error') }}
                    @endif
                </div>

                <div class="ratings-container">
                    <h4 class="form-label">{{ __('guidings.rating_scores') }}</h4>

                    <div class="rating-boxes rating-boxes--grid">
                        @foreach($scoreFields as $field => $label)
                            <div class="rating-box shadow">
                                <div class="rating-box-header">
                                    <span class="rating-title">{{ $label }}</span>
                                    <span class="rating-score">0/10</span>
                                </div>
                                <input name="{{ $field }}" class="rating" data-min="0" data-max="10" data-step="1" data-show-clear="false" data-show-caption="false">
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="review-section">
                    <h4 class="form-label">
                        {{ __('guidings.rating_comment') }} <span class="text-danger">*</span>
                    </h4>
                    <small class="text-muted">{{ __('vacations.rating_comment_help') }}</small>
                    <textarea
                        name="comment"
                        class="form-control"
                        rows="4"
                        maxlength="5000"
                        placeholder="{{ __('guidings.rating_comment_placeholder') }}"
                        required
                    ></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-orange">
                        {{ __('guidings.rating_submit') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="thankYouModal" tabindex="-1" aria-labelledby="thankYouModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-5">
                    <button type="button" class="btn-close position-absolute top-3 end-3" data-bs-dismiss="modal" aria-label="{{ __('guidings.rating_close') }}"></button>
                    <div class="success-icon mb-4">
                        <svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="32" cy="32" r="32" fill="#4CAF50" fill-opacity="0.1"/>
                            <path d="M44 24L28 40L20 32" stroke="#4CAF50" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h4 class="modal-title mb-3" id="thankYouModalLabel">{{ __('guidings.rating_thank_you_title') }}</h4>
                    <p class="text-muted mb-4">{{ __('guidings.rating_thank_you_message') }}</p>
                    <button type="button" class="btn btn-orange px-4 py-2" data-bs-dismiss="modal">
                        {{ __('guidings.rating_close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css_after')
    @include('pages.rating.partials.styles')
    <style>
        @media (min-width: 768px) {
            .rating-boxes--grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>
@endsection

@section('js_after')
    @include('pages.rating.partials.script', ['ratingFields' => array_keys($scoreFields)])
@endsection
