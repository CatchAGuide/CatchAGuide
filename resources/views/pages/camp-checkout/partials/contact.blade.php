@php($icon = 'pages.modern-checkout.partials.icon')
{{-- Shared by the camp and trip checkouts; $copy picks the lang group for the section copy. --}}
@php($copy ??= 'checkout.camp')
<section class="cc-section" aria-labelledby="cc-contact-title">
    <div class="cc-section__head">
        <h2 class="cc-section__title" id="cc-contact-title">{{ __($copy.'.contact_title') }}</h2>
        @unless ($checkout->isLoggedIn())
            <a href="#" class="cc-link" @click.prevent="window.openLoginModal && window.openLoginModal()">{{ __('checkout.tour.have_account') }}</a>
        @endunless
    </div>

    <div class="cc-contact">
        @foreach ([
            'firstName' => ['first_name', 'text', 'given-name', 100],
            'lastName' => ['last_name', 'text', 'family-name', 100],
            'email' => ['email', 'email', 'email', 255],
        ] as $field => [$key, $type, $autocomplete, $max])
            <div @class(['cc-field', 'cc-contact__email' => $field === 'email']) x-ref="field_{{ $field }}">
                <label class="cc-field__label" for="cc-{{ $field }}">{{ __('checkout.tour.'.$key) }}</label>
                <input
                    id="cc-{{ $field }}"
                    class="cc-input"
                    type="{{ $type }}"
                    name="{{ $key }}"
                    autocomplete="{{ $autocomplete }}"
                    @if ($type === 'email') inputmode="email" @endif
                    maxlength="{{ $max }}"
                    @if ($field === 'email') placeholder="{{ __('checkout.tour.email_placeholder') }}" @endif
                    x-model="contact.{{ $field }}"
                    @input="clearError('{{ $field }}')"
                    :class="{ 'is-invalid': errors.{{ $field }} }"
                    :aria-invalid="Boolean(errors.{{ $field }}).toString()"
                    aria-describedby="cc-{{ $field }}-error"
                    required
                >
                <p class="cc-field__error" id="cc-{{ $field }}-error" x-show="errors.{{ $field }}" x-text="errors.{{ $field }}" x-cloak></p>
            </div>
        @endforeach

        <div class="cc-field cc-contact__phone" x-ref="field_phone">
            <label class="cc-field__label" for="cc-phone">{{ __('checkout.tour.phone') }}</label>
            <div class="cc-phone">
                <div class="cc-select cc-phone__code" :class="{ 'is-invalid': errors.phone }">
                    <select class="cc-select__input" x-model="contact.countryCode" @change="clearError('phone')" aria-label="{{ __('checkout.tour.country_code') }}" autocomplete="tel-country-code">
                        @foreach ($checkout->countryCodes() as $dialCode => $country)
                            <option value="{{ $dialCode }}">{{ $dialCode }} {{ $country }}</option>
                        @endforeach
                    </select>
                    @include($icon, ['name' => 'chevron-down', 'size' => 16, 'stroke' => 2, 'class' => 'cc-select__chevron'])
                </div>
                <input
                    id="cc-phone"
                    class="cc-input"
                    type="tel"
                    name="phone"
                    inputmode="tel"
                    autocomplete="tel-national"
                    maxlength="25"
                    placeholder="{{ __('checkout.tour.phone_placeholder') }}"
                    x-model="contact.phone"
                    @input="clearError('phone')"
                    :class="{ 'is-invalid': errors.phone }"
                    :aria-invalid="Boolean(errors.phone).toString()"
                    aria-describedby="cc-phone-error"
                    required
                >
            </div>
            <p class="cc-field__error" id="cc-phone-error" x-show="errors.phone" x-text="errors.phone" x-cloak></p>
        </div>
    </div>

    <div class="cc-field" x-ref="field_message">
        <label class="cc-field__label" for="cc-message">
            {{ __($copy.'.message') }} <span class="cc-field__optional">{{ __($copy.'.optional') }}</span>
        </label>
        <textarea
            id="cc-message"
            class="cc-input cc-input--textarea"
            name="message"
            rows="4"
            maxlength="2000"
            placeholder="{{ __($copy.'.message_placeholder') }}"
            x-model="message"
            @input="clearError('message')"
            :class="{ 'is-invalid': errors.message }"
        ></textarea>
        <p class="cc-field__error" x-show="errors.message" x-text="errors.message" x-cloak></p>
    </div>
</section>
