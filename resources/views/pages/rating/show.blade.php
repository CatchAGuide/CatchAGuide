@extends('layouts.app-v2-1')

@section('title', __('guidings.rating_title'))

@section('content')
    <div class="container py-4 rating-page-container">
        <div class="rating-wrapper shadow-lg">
            <div class="rating-header">
                <h3>
                    {{ __('guidings.rating_title') }}
                    <span class="guide-name-highlight">{{ $booking->guiding->user->firstname }}</span>
                </h3>
                <p class="text-muted">{{ __('guidings.rating_subtitle') }}</p>
            </div>

            <!-- Booking summary -->
            <div class="booking-summary">
                <h5 class="booking-summary-title">
                    {{ __('guidings.rating_booking_summary_title') }}
                </h5>
                <div class="booking-summary-grid">
                    <div class="booking-summary-item">
                        <div class="booking-summary-label-row">
                            <div class="booking-summary-icon-circle">
                                <i class="fas fa-fish"></i>
                            </div>
                            <div class="booking-summary-label">
                                {{ __('guidings.rating_booking_title') }}
                            </div>
                        </div>
                        <div class="booking-summary-value">
                            {{ $booking->guiding->title ?? '–' }}
                        </div>
                    </div>
                    <div class="booking-summary-item">
                        <div class="booking-summary-label-row">
                            <div class="booking-summary-icon-circle">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="booking-summary-label">
                                {{ __('guidings.rating_booking_location') }}
                            </div>
                        </div>
                        <div class="booking-summary-value">
                            {{ $booking->guiding->location ?? '–' }}
                        </div>
                    </div>
                    <div class="booking-summary-item">
                        <div class="booking-summary-label-row">
                            <div class="booking-summary-icon-circle">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="booking-summary-label">
                                {{ __('guidings.rating_booking_date') }}
                            </div>
                        </div>
                        <div class="booking-summary-value">
                            @php
                                $tripDate = null;
                                if ($booking->calendar_schedule) {
                                    $tripDate = \Carbon\Carbon::parse($booking->calendar_schedule->date)->format('D, M j, Y');
                                } elseif ($booking->blocked_event) {
                                    $tripDate = \Carbon\Carbon::parse($booking->blocked_event->from)->format('D, M j, Y');
                                }
                            @endphp
                            {{ $tripDate ?? '–' }}
                        </div>
                    </div>
                    <div class="booking-summary-item">
                        <div class="booking-summary-label-row">
                            <div class="booking-summary-icon-circle">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="booking-summary-label">
                                {{ __('guidings.rating_booking_guests') }}
                            </div>
                        </div>
                        <div class="booking-summary-value">
                            {{ $booking->count_of_users ?? 1 }}
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('ratings.store', ['token' => $booking->token]) }}" method="POST" class="rating-form">
                @csrf
                
                <!-- Alert Section -->
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

                <!-- Ratings Section -->
                <div class="ratings-container">
                    <h4 class="form-label">{{ __('guidings.rating_scores') }}</h4>
                    
                    <div class="rating-boxes">
                        <div class="rating-box shadow">
                            <div class="rating-box-header">
                                <span class="rating-title">{{ __('guidings.rating_overall') }}</span>
                                <span class="rating-score">0/10</span>
                            </div>
                            <input name="rating_overall" class="rating" data-min="0" data-max="10" data-step="1" data-show-clear="false" data-show-caption="false">
                        </div>
                        
                        <div class="rating-box shadow">
                            <div class="rating-box-header">
                                <span class="rating-title">{{ __('guidings.rating_guide') }}</span>
                                <span class="rating-score">0/10</span>
                            </div>
                            <input name="rating_guide" class="rating" data-min="0" data-max="10" data-step="1" data-show-clear="false" data-show-caption="false">
                        </div>
                        
                        <div class="rating-box shadow">
                            <div class="rating-box-header">
                                <span class="rating-title">{{ __('guidings.rating_region') }}</span>
                                <span class="rating-score">0/10</span>
                            </div>
                            <input name="rating_region" class="rating" data-min="0" data-max="10" data-step="1" data-show-clear="false" data-show-caption="false">
                        </div>
                    </div>
                </div>

                <!-- Comment Section -->
                <div class="review-section">
                    <h4 class="form-label">
                        {{ __('guidings.rating_comment') }} <span class="text-danger">*</span>
                    </h4>
                    <small class="text-muted">{{ __('guidings.rating_comment_help') }}</small>
                    <textarea 
                        name="comment" 
                        class="form-control" 
                        rows="4"
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
                    <button type="button" class="btn-close position-absolute top-3 end-3" data-bs-dismiss="modal" aria-label="Close"></button>
                    
                    <!-- Success Icon -->
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
@endsection

@section('js_after')
    @include('pages.rating.partials.script', ['ratingFields' => ['rating_overall', 'rating_guide', 'rating_region']])
@endsection
