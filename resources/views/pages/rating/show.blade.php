@extends('layouts.app-v2-1')

@section('title', __('guidings.rating_title'))

@section('content')
    <div class="container py-4">
        <div class="rating-wrapper shadow-lg">
            <div class="rating-header">
                <h3>{{ __('guidings.rating_title') }} {{ $booking->guiding->user->firstname }}</h3>
                <p class="text-muted">{{ __('guidings.rating_subtitle') }}</p>
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
                        {{ __('guidings.rating_comment') }}
                    </h4>
                    <small class="text-muted">{{ __('guidings.rating_comment_help') }}</small>
                    <textarea 
                        name="comment" 
                        class="form-control" 
                        rows="4"
                        placeholder="{{ __('guidings.rating_comment_placeholder') }}"
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

    <!-- Add this loading overlay HTML just before the closing </div> of rating-wrapper -->
    <div class="loading-overlay d-none">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
@endsection

@section('css_after')
    <style>
        .rating-wrapper {
            max-width: 1000px;
            margin: 0 auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
            padding: 2rem;
        }

        .rating-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .rating-header h3 {
            color: #1a1a1a;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .review-section {
            margin-bottom: 2rem;
        }

        .form-label {
            display: block;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.75rem;
        }

        .form-label small {
            display: block;
            margin-top: 0.25rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            transition: border-color 0.15s ease-in-out;
        }

        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1);
            outline: none;
        }

        .ratings-container {
            margin-bottom: 2rem;
        }

        .rating-boxes {
            display: grid;
            gap: 1.5rem;
        }

        .rating-box {
            background: #f9fafb;
            padding: 1.25rem;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }

        .rating-box-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .rating-title {
            font-weight: 500;
            color: #374151;
        }

        .rating-score {
            color: #6b7280;
            font-size: 0.875rem;
        }

        .form-actions {
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            border: none;
            font-weight: 500;
            transition: background-color 0.15s ease-in-out;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        /* Rating stars customization */
        .rating-container .filled-stars {
            color: #2563eb;
        }

        .rating-container .empty-stars {
            color: #d1d5db;
        }

        /* Mobile Responsive */
        @media (min-width: 768px) {
            .rating-boxes {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 767px) {
            .rating-wrapper {
                padding: 1.5rem;
            }

            .rating-boxes {
                grid-template-columns: 1fr;
            }

            .rating-box {
                padding: 1rem;
            }
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 6px;
            border: 1px solid transparent;
        }

        .alert-danger {
            background-color: #fee2e2;
            border-color: #fecaca;
            color: #991b1b;
        }

        /* Modal Styles */
        .modal-content {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .modal-dialog-centered {
            display: flex;
            align-items: center;
            min-height: calc(100% - 3.5rem);
        }

        .success-icon {
            display: inline-flex;
            animation: scaleIn 0.3s ease-in-out;
        }

        .btn-close {
            opacity: 0.5;
            transition: opacity 0.2s;
            right: 16px;
            top: 16px;
        }

        .btn-close:hover {
            opacity: 1;
        }

        .modal .btn-primary {
            background: #2563eb;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .modal .btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }

        @keyframes scaleIn {
            from {
                transform: scale(0.5);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        /* Fade animation for modal */
        .modal.fade .modal-dialog {
            transition: transform 0.2s ease-out;
            transform: scale(0.95);
        }

        .modal.show .modal-dialog {
            transform: scale(1);
        }

        /* Add these loading overlay styles */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
    </style>
@endsection

@section('js_after')
<script>
$(document).ready(function(){
    // Show error alert if there are validation errors
    @if ($errors->any())
        document.getElementById('ratingError').classList.remove('d-none');
    @endif

    // Initialize each rating input
    $('.rating').rating({
        min: 0,
        max: 10,
        step: 1,
        size: 'sm'
    });

    // Update score display when rating changes
    $('.rating').on('change', function(e) {
        const value = $(this).val();
        $(this).closest('.rating-box').find('.rating-score').text(value + '/10');
    });

    // Handle modal close events
    const thankYouModal = document.getElementById('thankYouModal');
    thankYouModal.addEventListener('hidden.bs.modal', function () {
        window.location.href = "{{ route('welcome') }}";
    });

    // Form submission handling
    $('.rating-form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const formData = new FormData(this);
        const errorElement = document.getElementById('ratingError');
        const loadingOverlay = document.querySelector('.loading-overlay');

        // Validate form data
        if (!formData.get('rating_overall') || 
            !formData.get('rating_guide') || 
            !formData.get('rating_region')) {

            console.log(formData.get('rating_overall'));
            console.log(formData.get('rating_guide'));
            console.log(formData.get('rating_region'));
            errorElement.innerHTML = "{{ __('guidings.rating_error') }}";
            errorElement.classList.remove('d-none');
            errorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }

        // Hide error and show loading overlay
        errorElement.classList.add('d-none');
        loadingOverlay.classList.remove('d-none');

        // Submit form via AJAX
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                loadingOverlay.classList.add('d-none');
                if (response.success) {
                    // Show thank you modal
                    const modal = new bootstrap.Modal(document.getElementById('thankYouModal'));
                    modal.show();
                    
                    // Disable form after successful submission
                    form.find('input, textarea, button').prop('disabled', true);
                }
            },
            error: function(xhr) {
                loadingOverlay.classList.add('d-none');
                // Handle validation errors
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    let errorHtml = '<ul class="mb-0 list-unstyled">';
                    Object.values(errors).forEach(function(error) {
                        errorHtml += `<li>${error}</li>`;
                    });
                    errorHtml += '</ul>';
                    errorElement.innerHTML = errorHtml;
                    errorElement.classList.remove('d-none');
                    errorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    errorElement.innerHTML = "{{ __('guidings.rating_error') }}";
                    errorElement.classList.remove('d-none');
                    errorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    });
});
</script>
@endsection
