@php($icon = 'pages.modern-checkout.partials.icon')
<section class="cc-section" aria-labelledby="cc-stay-title">
    <h2 class="cc-section__title" id="cc-stay-title">{{ __('checkout.camp.stay_title') }}</h2>

    <div class="cc-stay">
        <div class="cc-field cc-stay__date" x-ref="field_date" @click="openArrivalPicker">
            <label class="cc-field__label" for="cc-date">{{ __('checkout.camp.arrival') }}</label>
            <div @class(['cc-date', 'is-empty' => blank($checkout->arrivalDate())]) :class="{ 'is-empty': !arrivalDate, 'is-invalid': errors.date }">
                <input
                    id="cc-date"
                    class="cc-input cc-date__input"
                    x-ref="arrivalInput"
                    type="date"
                    name="arrival_date"
                    min="{{ $checkout->minDate() }}"
                    max="{{ $checkout->maxDate() }}"
                    value="{{ $checkout->arrivalDate() }}"
                    x-model="arrivalDate"
                    @change="clearError('date')"
                    :aria-invalid="Boolean(errors.date).toString()"
                    aria-describedby="cc-date-error"
                    required
                >
                <span class="cc-date__hint" aria-hidden="true">{{ __('checkout.camp.arrival_placeholder') }}</span>
                <span class="cc-date__glyph" aria-hidden="true">
                    @include($icon, ['name' => 'calendar', 'size' => 20, 'stroke' => 1.8])
                </span>
            </div>
            <p class="cc-field__error" id="cc-date-error" x-show="errors.date" x-text="errors.date" x-cloak></p>
        </div>

        <div class="cc-field" x-ref="field_nights">
            <span class="cc-field__label" id="cc-nights-label">{{ __('checkout.camp.nights') }}</span>
            <div class="cc-stepper" role="group" aria-labelledby="cc-nights-label" :class="{ 'is-invalid': errors.nights }">
                <button type="button" class="cc-stepper__btn" @click="changeNights(-1)" :disabled="nights <= minNights" aria-label="{{ __('checkout.camp.fewer_nights') }}">
                    @include($icon, ['name' => 'minus', 'size' => 18, 'stroke' => 2])
                </button>
                <output class="cc-stepper__value cc-mono" x-text="nights" aria-live="polite">{{ $checkout->nights() }}</output>
                <button type="button" class="cc-stepper__btn" @click="changeNights(1)" :disabled="nights >= maxNights" aria-label="{{ __('checkout.camp.more_nights') }}">
                    @include($icon, ['name' => 'plus', 'size' => 18, 'stroke' => 2])
                </button>
            </div>
            <p class="cc-field__error" x-show="errors.nights" x-text="errors.nights" x-cloak></p>
        </div>

        <div class="cc-field" x-ref="field_persons">
            <span class="cc-field__label" id="cc-persons-label">{{ __('checkout.camp.persons') }}</span>
            <div class="cc-stepper" role="group" aria-labelledby="cc-persons-label">
                <button type="button" class="cc-stepper__btn" @click="changePersons(-1)" :disabled="persons <= 1" aria-label="{{ __('checkout.camp.fewer_persons') }}">
                    @include($icon, ['name' => 'minus', 'size' => 18, 'stroke' => 2])
                </button>
                <output class="cc-stepper__value cc-mono" x-text="persons" aria-live="polite">{{ $checkout->persons() }}</output>
                <button type="button" class="cc-stepper__btn" @click="changePersons(1)" :disabled="persons >= maxPersons" aria-label="{{ __('checkout.camp.more_persons') }}">
                    @include($icon, ['name' => 'plus', 'size' => 18, 'stroke' => 2])
                </button>
            </div>
            <p class="cc-field__error" x-show="errors.persons" x-text="errors.persons" x-cloak></p>
        </div>
    </div>

    <p class="cc-hint" x-show="minNights > 1" x-text="minNightsLabel" x-cloak></p>
</section>
