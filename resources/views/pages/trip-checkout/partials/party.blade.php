@php($icon = 'pages.modern-checkout.partials.icon')
<section class="cc-section" aria-labelledby="cc-party-title">
    <h2 class="cc-section__title" id="cc-party-title">{{ __('checkout.trip.participants_title') }}</h2>

    <div class="cc-party" x-ref="field_persons">
        <div class="cc-party__text">
            <span class="cc-party__count" x-text="personsLabel">{{ trans_choice('checkout.trip.persons_count', $checkout->persons(), ['count' => $checkout->persons()]) }}</span>
            <span class="cc-party__max">{{ __('checkout.trip.max_group', ['count' => $checkout->maxPersons()]) }}</span>
        </div>
        <div class="cc-stepper cc-party__stepper" role="group" aria-labelledby="cc-party-title">
            <button type="button" class="cc-stepper__btn" @click="changePersons(-1)" :disabled="persons <= 1" aria-label="{{ __('checkout.trip.fewer_persons') }}">
                @include($icon, ['name' => 'minus', 'size' => 18, 'stroke' => 2])
            </button>
            <output class="cc-stepper__value cc-mono" x-text="persons" aria-live="polite">{{ $checkout->persons() }}</output>
            <button type="button" class="cc-stepper__btn" @click="changePersons(1)" :disabled="persons >= maxPersons" aria-label="{{ __('checkout.trip.more_persons') }}">
                @include($icon, ['name' => 'plus', 'size' => 18, 'stroke' => 2])
            </button>
        </div>
    </div>
    <p class="cc-field__error" x-show="errors.persons" x-text="errors.persons" x-cloak></p>

    <div class="cc-warning" role="status" x-show="capacityHint" x-cloak>
        @include($icon, ['name' => 'info', 'size' => 18, 'stroke' => 2])
        <span x-text="capacityHint"></span>
    </div>
</section>
