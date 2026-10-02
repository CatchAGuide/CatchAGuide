<div class="tc-guests">
    <div class="tc-guests__row">
        <span class="tc-guests__label" id="tc-guests-label">{{ __('checkout.tour.participants') }}</span>
        <div class="tc-stepper" role="group" aria-labelledby="tc-guests-label">
            <button type="button" class="tc-stepper__btn" @click="changePersons(-1)" :disabled="persons <= 1" aria-label="{{ __('checkout.tour.decrease') }}">−</button>
            <output class="tc-stepper__value tc-mono" x-text="persons" aria-live="polite">{{ $checkout->persons() }}</output>
            <button type="button" class="tc-stepper__btn" @click="changePersons(1)" :disabled="atMax" aria-label="{{ __('checkout.tour.increase') }}">+</button>
        </div>
    </div>
    <p class="tc-guests__max" x-show="atMax" x-cloak>{{ __('checkout.tour.max_participants', ['count' => $checkout->maxGuests()]) }}</p>
</div>
