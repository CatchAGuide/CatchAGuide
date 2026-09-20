@php
    $preselectedGuests = $preselectedGuests ?? null;
    $guestOptions = $guiding->bookingGuestOptions();
    $selectedGuests = $preselectedGuests !== null
        ? $guiding->defaultBookingGuestCount((int) $preselectedGuests)
        : null;
    $selectedOption = $selectedGuests
        ? collect($guestOptions)->firstWhere('person', (int) $selectedGuests)
        : null;
    $fromAmount = $selectedOption['amount'] ?? ($guestOptions[0]['amount'] ?? (float) $guiding->price);
    $formatBookPrice = static function ($amount): string {
        return preg_replace('/\.00$/', '', number_format((float) $amount, 2, '.', '')).'€';
    };
    $isPerPerson = $guiding->price_type === 'per_person';
@endphp

<div class="col-md-12 tour-details-two__sticky sticky-lg-top {{$agent->ismobile() ? 'text-center' : ''}}">
    <div class="tour-details-two__sidebar">
        <div class="tour-details-two__book-tours card shadow-sm" id="booking-tour">
            <div class="card-body p-4">
                <form action="{{ route('checkout') }}" method="POST" class="checkout-form" data-guidings-classic-book>
                    @csrf
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div>
                            @if($isPerPerson)
                                <h4 class="mb-1">
                                    <small class="from-text" @if($selectedGuests) style="display: none;" @endif>{{ __('booking.from') }}</small>
                                    <span class="total-price">{{ $formatBookPrice($fromAmount) }}</span>
                                    <span class="fs-6 fw-normal per-guiding-text" @unless($selectedGuests) style="display: none;" @endunless>{{ __('booking.per_guiding') }}</span>
                                </h4>
                            @else
                                <h4 class="mb-1">
                                    <span class="total-price">{{ $formatBookPrice($guiding->price) }}</span>
                                    <span class="fs-6 fw-normal per-guiding-text">{{ __('booking.per_guiding') }}</span>
                                </h4>
                            @endif
                        </div>
                        <div class="booking-select" style="min-width: 150px;">
                            <select class="form-select border-0" aria-label="{{ __('booking.people') }}" name="person" required id="personSelect">
                                <option value="" @selected($selectedGuests === null) disabled>{{ __('booking.people') }}</option>
                                @foreach($guestOptions as $price)
                                    <option
                                        value="{{ $price['person'] }}"
                                        data-price="{{ $price['amount'] }}"
                                        @selected($selectedGuests !== null && (int) $price['person'] === (int) $selectedGuests)
                                    >
                                        {{ $price['person'] }} {{ (int) $price['person'] === 1 ? __('booking.person') : __('booking.people') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div id="priceCalculation" class="price-calculation mt-3" @unless($selectedGuests) style="display: none;" @endunless>
                        <div class="price-breakdown">
                            <div class="d-flex justify-content-between mb-2">
                                <div class="price-item">
                                    @if($isPerPerson)
                                        <span class="base-price">{{ $selectedGuests ? $formatBookPrice((int) round($fromAmount / max(1, (int) $selectedGuests))) : '' }}</span>
                                        {{ __('booking.per_person_for_a_tour_of') }}
                                        <span class="person-count">{{ $selectedGuests }}</span>
                                        <span class="people-text">{{ (int) $selectedGuests === 1 ? __('booking.person') : __('booking.people') }}</span>{{ __('booking.you_wont_be_charged_yet')}}
                                    @else
                                        {{ __('booking.fixed_price_for') }}
                                        <span class="person-count">{{ $selectedGuests }}</span>
                                        <span class="people-text">{{ (int) $selectedGuests === 1 ? __('booking.person') : __('booking.people') }}</span>{{ __('booking.you_wont_be_charged_yet')}}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($guiding->min_guests)
                        <small> * {{ str_replace('[Min Guest]', $guiding->min_guests, __('booking.min_guest')) }} </small>
                    @endif

                    <div class="booking-form-container">
                        <input type="hidden" name="guiding_id" value="{{ $guiding->id }}">
                        <input type="hidden" name="selected_date" id="selectedDateInput" value="">
                        <button type="submit" class="btn btn-orange w-100 py-3 mb-3 reserve-now-btn" id="reserveButton">{{ __('booking.reserve_now') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if(!$agent->ismobile())
    <div class="contact-card mb-4 mt-4 tour-details-two__book-tours">
        <h5 class="contact-card__title">{{ __('booking.contact_us') }}</h5>
        <div class="contact-card__content">
            <p class="">{{ __('booking.do_you_have_questions') }}</p>
            <div class="">
                <div class="contact-info">
                    <i class="fas fa-phone-alt me-2"></i>
                    <a href="tel:+49{{config('cag.contact_num')}}" class="text-decoration-none">+49 (0) {{config('cag.contact_num')}}</a>
                </div>
                <a href="#" id="contact-product" class="btn btn-outline-orange" data-bs-toggle="modal" data-bs-target="#contactModal">
                    {{ __('booking.contact_form') }}
                    <i class="fas fa-arrow-right ms-2"></i>
                </a>
                @include('partials.product-report.cta', [
                    'reportSourceType' => 'guiding',
                    'reportSourceId' => $guiding->id,
                    'reportedUrl' => url()->current(),
                ])
            </div>
        </div>
    </div>
    @endif
</div>

@include('layouts.partials.guidings-classic-booking-script')
