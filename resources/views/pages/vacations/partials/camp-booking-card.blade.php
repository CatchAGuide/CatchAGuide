@php
    $bookingDateMin = now()->toDateString();
    $bookingDateMax = now()->copy()->addYears(2)->toDateString();
@endphp

<div class="camp-booking-card">
    <div class="camp-booking-card__header">
        <h5 class="camp-booking-card__title">{{ __('vacations.contact_us') }}</h5>
    </div>

    <div class="camp-booking-card__body">
        <div class="camp-booking-card__field-group">
            <label class="camp-booking-card__field-label" for="camp-booking-date-{{ $instance }}">
                {{ __('trips.select_date') }}
            </label>
            <input
                type="date"
                id="camp-booking-date-{{ $instance }}"
                class="camp-booking-card__date-input"
                min="{{ $bookingDateMin }}"
                max="{{ $bookingDateMax }}"
                data-camp-booking-date
                required
            >
        </div>

        <div class="camp-booking-card__field-group">
            <span class="camp-booking-card__field-label">{{ __('trips.guests_label') }}</span>
            <div class="camp-booking-card__guest-stepper" data-camp-booking-guests>
                <button
                    type="button"
                    class="camp-booking-card__stepper-btn"
                    data-camp-booking-guests-minus
                    aria-label="{{ __('trips.decrease_guests') }}"
                >−</button>
                <span class="camp-booking-card__guest-count" data-camp-booking-guests-label>1</span>
                <button
                    type="button"
                    class="camp-booking-card__stepper-btn"
                    data-camp-booking-guests-plus
                    aria-label="{{ __('trips.increase_guests') }}"
                >+</button>
            </div>
        </div>

        <button type="button" class="camp-booking-card__cta" data-camp-booking-cta>
            {{ __('vacations.contact_us_button') }}
        </button>
    </div>
</div>
