@php
    $modalTarget = $modalTarget ?? '#tripGeneralContactModal';
    $title = $title ?? __('trips.contact_us_title');
    $message = $message ?? __('trips.contact_us_message');
    $buttonLabel = $buttonLabel ?? __('trips.contact_form');
    $showTripAnalytics = $showTripAnalytics ?? true;
@endphp
<div class="contact-card mt-4 mb-4 {{ $wrapperClass ?? '' }}">
    <h5 class="contact-card__title">{{ $title }}</h5>
    <div class="contact-card__content">
        <p>{{ $message }}</p>
        <div>
            <div class="contact-info">
                <i class="fas fa-phone-alt me-2"></i>
                <a href="tel:+49{{ config('cag.contact_num') }}" class="text-decoration-none">+49 (0) {{ config('cag.contact_num') }}</a>
            </div>
            <a
                href="#"
                class="btn btn-outline-orange"
                data-bs-toggle="modal"
                data-bs-target="{{ $modalTarget }}"
                @if($showTripAnalytics) data-analytics-trip-contact @endif
            >
                {{ $buttonLabel }}
                <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</div>
