<div class="contact-card mt-4 mb-4 {{ $wrapperClass ?? '' }}">
    <h5 class="contact-card__title">{{ __('trips.contact_us_title') }}</h5>
    <div class="contact-card__content">
        <p>{{ __('trips.contact_us_message') }}</p>
        <div>
            <div class="contact-info">
                <i class="fas fa-phone-alt me-2"></i>
                <a href="tel:+49{{ config('cag.contact_num') }}" class="text-decoration-none">+49 (0) {{ config('cag.contact_num') }}</a>
            </div>
            <a
                href="#"
                class="btn btn-outline-orange"
                data-bs-toggle="modal"
                data-bs-target="#tripGeneralContactModal"
                data-analytics-trip-contact
            >
                {{ __('trips.contact_form') }}
                <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</div>
