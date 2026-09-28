<section class="tc-card tc-contact" aria-labelledby="tc-contact-title">
    <div class="tc-contact__head">
        <h2 class="tc-card__title" id="tc-contact-title">{{ __('checkout.tour.contact_title') }}</h2>
        @unless ($checkout->isLoggedIn())
            <a href="#" class="tc-link tc-contact__login" @click.prevent="window.openLoginModal && window.openLoginModal()">{{ __('checkout.tour.have_account') }}</a>
        @endunless
    </div>
    <p class="tc-muted tc-contact__privacy">{{ __('checkout.tour.contact_privacy') }}</p>

    <div class="tc-contact__grid">
        @foreach ([
            'firstName' => ['first_name', 'text', 'given-name'],
            'lastName' => ['last_name', 'text', 'family-name'],
            'email' => ['email', 'email', 'email'],
        ] as $field => [$key, $type, $autocomplete])
            <div class="tc-field" x-ref="field_{{ $field }}">
                <label class="tc-field__label" for="tc-{{ $field }}">{{ __('checkout.tour.'.$key) }}</label>
                <input
                    id="tc-{{ $field }}"
                    class="tc-input"
                    type="{{ $type }}"
                    name="{{ $key }}"
                    autocomplete="{{ $autocomplete }}"
                    maxlength="{{ $field === 'email' ? 255 : 100 }}"
                    placeholder="{{ __('checkout.tour.'.$key.'_placeholder') }}"
                    x-model="contact.{{ $field }}"
                    @input="clearError('{{ $field }}')"
                    :class="{ 'is-invalid': errors.{{ $field }} }"
                    :aria-invalid="Boolean(errors.{{ $field }}).toString()"
                    aria-describedby="tc-{{ $field }}-error"
                    required
                >
                <p class="tc-field__error" id="tc-{{ $field }}-error" x-show="errors.{{ $field }}" x-text="errors.{{ $field }}" x-cloak></p>
            </div>
        @endforeach

        <div class="tc-field" x-ref="field_phone">
            <label class="tc-field__label" for="tc-phone">{{ __('checkout.tour.phone') }}</label>
            <div class="tc-phone" :class="{ 'is-invalid': errors.phone }">
                <div class="tc-phone__code">
                    <span class="tc-mono" x-text="contact.countryCode" aria-hidden="true"></span>
                    @include('pages.modern-checkout.partials.icon', ['name' => 'chevron-down', 'size' => 13])
                    <select class="tc-phone__select" x-model="contact.countryCode" aria-label="{{ __('checkout.tour.country_code') }}" autocomplete="tel-country-code">
                        @foreach ($checkout->countryCodes() as $dialCode => $country)
                            <option value="{{ $dialCode }}">{{ $dialCode }} {{ $country }}</option>
                        @endforeach
                    </select>
                </div>
                <span class="tc-phone__divider" aria-hidden="true"></span>
                <input
                    id="tc-phone"
                    class="tc-phone__input"
                    type="tel"
                    name="phone"
                    inputmode="tel"
                    autocomplete="tel-national"
                    maxlength="25"
                    placeholder="{{ __('checkout.tour.phone_placeholder') }}"
                    x-model="contact.phone"
                    @input="clearError('phone')"
                    :aria-invalid="Boolean(errors.phone).toString()"
                    aria-describedby="tc-phone-error tc-phone-help"
                    required
                >
            </div>
            <p class="tc-field__error" id="tc-phone-error" x-show="errors.phone" x-text="errors.phone" x-cloak></p>
            <p class="tc-muted tc-field__help" id="tc-phone-help">{{ __('checkout.tour.phone_help') }}</p>
        </div>
    </div>
</section>
