@php
    $instance = $instance ?? 'desktop';
    $isMobileInstance = $instance === 'mobile';
    $preselectedGuests = $preselectedGuests ?? null;
    $guestOptions = $guiding->bookingGuestOptions();
    $selectedGuests = $guiding->defaultBookingGuestCount(
        $preselectedGuests !== null ? (int) $preselectedGuests : null
    );
    $selectedOption = collect($guestOptions)->firstWhere('person', $selectedGuests) ?? ($guestOptions[0] ?? null);
    $selectedAmount = $selectedOption['amount'] ?? (float) $guiding->price;
    $formatBookPrice = static function ($amount): string {
        return preg_replace('/\.00$/', '', number_format((float) $amount, 2, '.', '')).'€';
    };
    $personLabel = (int) $selectedGuests === 1 ? __('booking.person') : __('booking.people');
    $showPerPersonBreakdown = (int) $selectedGuests > 1;
    $perPersonAmount = (int) round((float) $selectedAmount / max(1, (int) $selectedGuests));
    $ctaId = $isMobileInstance ? 'reserveButtonMobile' : 'reserveButton';
    $dateInputId = $isMobileInstance ? 'selectedDateInputMobile' : 'selectedDateInput';
    $selectedIndex = max(0, collect($guestOptions)->search(fn ($option) => (int) $option['person'] === (int) $selectedGuests));
@endphp

<div
    class="guidings-book-card"
    data-guidings-book
    data-guidings-book-instance="{{ $instance }}"
    data-guidings-book-options='@json($guestOptions)'
    data-guidings-book-index="{{ $selectedIndex }}"
    data-guidings-book-person-singular="{{ __('booking.person') }}"
    data-guidings-book-person-plural="{{ __('booking.people') }}"
    data-guidings-book-reserve-label="{{ __('booking.reserve_now') }}"
    data-guidings-book-reserve-for="{{ __('booking.reserve_for_date') }}"
    data-guidings-book-locale="{{ str_replace('_', '-', app()->getLocale()) }}"
    role="region"
    aria-label="{{ __('booking.reserve_now') }}"
>
    <form action="{{ route('checkout') }}" method="POST" class="checkout-form guidings-book-card__form">
        @csrf
        <div class="guidings-book-card__top">
            <div class="guidings-book-card__price">
                <span class="guidings-book-card__amount" data-guidings-book-price>{{ $formatBookPrice($selectedAmount) }}</span>
                <span class="guidings-book-card__unit">
                    {{ __('booking.per_guiding') }}<span class="guidings-book-card__breakdown" data-guidings-book-breakdown @unless($showPerPersonBreakdown) hidden @endunless> / <span data-guidings-book-per-person>{{ $formatBookPrice($perPersonAmount) }}</span> {{ __('booking.per_person') }}</span>
                </span>
            </div>
            <div class="guidings-book-card__stepper" data-guidings-book-stepper>
                <button
                    type="button"
                    class="guidings-book-card__stepper-btn"
                    data-guidings-book-delta="-1"
                    aria-label="{{ __('booking.decrease_guests') }}"
                    @disabled($selectedIndex <= 0)
                >−</button>
                <span class="guidings-book-card__stepper-label" data-guidings-book-label>{{ $selectedGuests }} {{ $personLabel }}</span>
                <button
                    type="button"
                    class="guidings-book-card__stepper-btn"
                    data-guidings-book-delta="1"
                    aria-label="{{ __('booking.increase_guests') }}"
                    @disabled($selectedIndex >= max(0, count($guestOptions) - 1))
                >+</button>
            </div>
        </div>

        <input type="hidden" name="guiding_id" value="{{ $guiding->id }}">
        <input type="hidden" name="person" value="{{ $selectedGuests }}" required data-guidings-book-person>
        <input type="hidden" name="selected_date" id="{{ $dateInputId }}" value="" data-guidings-book-date>

        <button type="submit" class="guidings-book-card__cta" id="{{ $ctaId }}" data-guidings-book-cta>
            <span class="guidings-book-card__cta-text" data-guidings-book-cta-text>{{ __('booking.reserve_now') }}</span>
            <span class="guidings-book-card__cta-arrow" aria-hidden="true">→</span>
        </button>
    </form>
</div>

@once
    @include('layouts.partials.guidings-booking-widget-script')
@endonce
