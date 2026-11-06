@use('App\Rules\Recaptcha')
@php
    $copy = 'guidings.voucher_landing.';
    $icon = 'pages.voucher-landing.partials.icon';
    $preview = $landing->previewDefaults();
    $contactEmail = (string) config('mail.admin_email');
@endphp
<section class="vl-section vl-section--sand vl-form-section" id="gutschein" data-vl-form>
    <div class="vl-container vl-stack">
        <div class="vl-intro vl-intro--wide">
            <h2 class="vl-h2">{{ __($copy.'form_title') }}</h2>
            <p>{{ __($copy.'form_text') }}</p>
        </div>

        <div class="vl-config">
            {{-- Desktop: the full ticket, sticky beside the form. --}}
            <div class="vl-config__preview">
                @include('pages.voucher-landing.partials.card', ['variant' => 'form', 'values' => $preview, 'serial' => $landing->serial()])
                <span class="vl-config__note vl-mono">{{ __($copy.'card_preview_note') }}</span>
            </div>

            {{-- Mobile: a compact sticky strip. --}}
            <div class="vl-strip" :class="pulseClass" aria-hidden="true">
                <span class="vl-strip__amount vl-mono" x-text="amountLabel">{{ $preview['amount'] }}</span>
                <span class="vl-strip__rule"></span>
                <span class="vl-strip__copy">
                    <span class="vl-strip__kicker vl-mono">{{ __($copy.'card_preview') }}</span>
                    <span class="vl-strip__where" x-text="whereText">{{ $preview['where'] }}</span>
                    <span class="vl-strip__for" x-text="forText">{{ $preview['for'] }}</span>
                </span>
            </div>

            <div class="vl-panel">
                @if($success)
                    <div class="vl-success">
                        <div class="vl-confetti" aria-hidden="true">
                            @for($i = 0; $i < 46; $i++)
                                <span style="--x: {{ mt_rand(0, 100) }}%; --w: {{ mt_rand(6, 12) }}px; --h: {{ mt_rand(10, 18) }}px; --d: {{ mt_rand(16, 32) / 10 }}s; --delay: {{ mt_rand(0, 6) / 10 }}s; --r: {{ mt_rand(0, 1) ? '2px' : '99px' }}; --c: var(--vl-{{ ['yellow', 'coral', 'slate', 'yellow'][$i % 4] }})"></span>
                            @endfor
                        </div>
                        <span class="vl-success__check">@include($icon, ['name' => 'check'])</span>
                        <h2 class="vl-success__title">{{ __($copy.'success_title') }}</h2>
                        <p>{!! __($copy.'success_text', ['email' => '<strong>'.e($success['buyerEmail']).'</strong>']) !!}</p>
                        <dl class="vl-summary">
                            <div><dt>{{ __($copy.'summary_amount') }}</dt><dd class="vl-mono">{{ $success['amount'] }}</dd></div>
                            <div><dt>{{ __($copy.'summary_where') }}</dt><dd>{{ $success['where'] }}</dd></div>
                            <div><dt>{{ __($copy.'summary_for') }}</dt><dd>{{ $success['recipientOrDefault'] }}</dd></div>
                            <div><dt>{{ __($copy.'summary_contact') }}</dt><dd>{{ $success['buyerName'] }} · {{ $success['buyerPhone'] }}</dd></div>
                        </dl>
                        <a href="{{ route('guidings.voucher') }}#gutschein" class="vl-btn vl-btn--outline">{{ __($copy.'success_again') }}</a>
                    </div>
                @else
                    <form method="POST" action="{{ route('guidings.voucher.store') }}" class="vl-form" novalidate x-ref="form" @submit.prevent="submit">
                        @csrf

                        <div class="vl-flash" x-show="flash" x-transition x-cloak role="status">
                            <span class="vl-flash__check">@include($icon, ['name' => 'check'])</span>
                            <span x-text="flash"></span>
                        </div>

                        <div class="vl-grid">
                            <label class="vl-field">
                                <span class="vl-label">{{ __($copy.'country') }} <span class="vl-req">*</span></span>
                                <span class="vl-select" :class="{ 'is-error': errors.country, 'is-flash': flash && !errors.country }">
                                    <select name="country" x-model="country" @change="onCountry" required>
                                        <option value="">{{ __($copy.'country_placeholder') }}</option>
                                        <template x-for="option in destinations" :key="option.name">
                                            <option :value="option.name" x-text="option.name" :selected="option.name === country"></option>
                                        </template>
                                        <option value="{{ $landing->openValue() }}">{{ __($copy.'country_open') }}</option>
                                    </select>
                                    @include($icon, ['name' => 'chevron'])
                                </span>
                                <span class="vl-error" x-show="errors.country" x-text="errors.country" @error('country') @else x-cloak @enderror>@error('country'){{ $message }}@enderror</span>
                            </label>

                            <label class="vl-field">
                                <span class="vl-label">{{ __($copy.'region') }} <span class="vl-req" x-show="regionRequired">*</span></span>
                                <span class="vl-select" :class="{ 'is-error': errors.region, 'is-flash': flash && !errors.region, 'is-disabled': regionDisabled }">
                                    <select name="region" x-model="region" @change="onRegion" :disabled="regionDisabled">
                                        <template x-if="!country">
                                            <option value="">{{ __($copy.'region_first') }}</option>
                                        </template>
                                        <template x-if="country && !isOpen(country)">
                                            <option value="">{{ __($copy.'region_placeholder') }}</option>
                                        </template>
                                        <template x-for="option in regionOptions" :key="option.name">
                                            <option :value="option.name" x-text="option.name" :selected="option.name === region"></option>
                                        </template>
                                        <template x-if="country">
                                            <option value="{{ $landing->openValue() }}" :selected="isOpen(region)">{{ __($copy.'open') }}</option>
                                        </template>
                                    </select>
                                    @include($icon, ['name' => 'chevron'])
                                </span>
                                <span class="vl-error" x-show="errors.region" x-text="errors.region" @error('region') @else x-cloak @enderror>@error('region'){{ $message }}@enderror</span>
                            </label>
                        </div>

                        <label class="vl-field">
                            <span class="vl-label">{{ __($copy.'city') }} <span class="vl-optional">{{ __($copy.'optional') }}</span></span>
                            <span class="vl-select" :class="{ 'is-disabled': cityOptions.length === 0, 'is-error': errors.city }">
                                <select name="city" x-model="city" @change="bumpPreview" :disabled="cityOptions.length === 0">
                                    <option value="">{{ __($copy.'city_any') }}</option>
                                    <template x-for="option in cityOptions" :key="option">
                                        <option :value="option" x-text="option" :selected="option === city"></option>
                                    </template>
                                </select>
                                @include($icon, ['name' => 'chevron'])
                            </span>
                            <span class="vl-error" x-show="errors.city" x-text="errors.city" @error('city') @else x-cloak @enderror>@error('city'){{ $message }}@enderror</span>
                        </label>

                        <div class="vl-field">
                            <span class="vl-label" id="vl-amount-label">{{ __($copy.'amount') }} <span class="vl-req">*</span></span>
                            <input type="hidden" name="amount" :value="amount" value="{{ old('amount', $landing->defaultAmount()) }}">
                            <div class="vl-chips" role="group" aria-labelledby="vl-amount-label">
                                @foreach($landing->amountOptions() as $option)
                                    <button type="button" class="vl-chip vl-mono" :class="{ 'is-on': amount === @js($option['value']) }" :aria-pressed="amount === @js($option['value'])" @click="pickAmount(@js($option['value']))">{{ $option['label'] }}</button>
                                @endforeach
                                <button type="button" class="vl-chip" :class="{ 'is-on': amount === 'custom', 'is-error': errors.custom_amount }" :aria-pressed="amount === 'custom'" @click="pickAmount('custom')">{{ __($copy.'amount_custom') }}</button>
                            </div>
                            <label class="vl-custom" x-show="amount === 'custom'" x-cloak>
                                <span class="vl-input-wrap">
                                    <input type="number" name="custom_amount" inputmode="numeric" min="{{ $landing->minAmount() }}" max="{{ $landing->maxAmount() }}" step="1"
                                           placeholder="{{ __($copy.'amount_custom_placeholder') }}" x-model="customAmount" @input="clearError('custom_amount'); bumpPreview()"
                                           :disabled="amount !== 'custom'" class="vl-input vl-mono" :class="{ 'is-error': errors.custom_amount }">
                                    <span class="vl-input-wrap__unit vl-mono">€</span>
                                </span>
                                <span class="vl-hint">{{ __($copy.'amount_min', ['amount' => $landing->money($landing->minAmount())]) }}</span>
                            </label>
                            <span class="vl-error" x-show="errors.custom_amount" x-text="errors.custom_amount" @error('custom_amount') @else x-cloak @enderror>@error('custom_amount'){{ $message }}@enderror</span>
                        </div>

                        <label class="vl-field">
                            <span class="vl-label">{{ __($copy.'recipient') }} <span class="vl-optional">{{ __($copy.'optional') }}</span></span>
                            <input type="text" name="recipient" maxlength="100" class="vl-input" x-model="recipient" @input="bumpPreview" placeholder="{{ __($copy.'recipient_placeholder') }}">
                        </label>

                        <div class="vl-divider"></div>

                        <label class="vl-field">
                            <span class="vl-label">{{ __($copy.'name') }} <span class="vl-req">*</span></span>
                            <input type="text" name="name" maxlength="100" autocomplete="name" class="vl-input" x-model="name" @input="clearError('name')" :class="{ 'is-error': errors.name }" required>
                            <span class="vl-error" x-show="errors.name" x-text="errors.name" @error('name') @else x-cloak @enderror>@error('name'){{ $message }}@enderror</span>
                        </label>

                        <div class="vl-grid vl-grid--wide">
                            <label class="vl-field">
                                <span class="vl-label">{{ __($copy.'phone') }} <span class="vl-req">*</span></span>
                                <span class="vl-phone">
                                    <span class="vl-select vl-select--prefix">
                                        <select name="country_code" x-model="countryCode" aria-label="{{ __($copy.'phone_prefix') }}">
                                            @foreach($landing->countryCodes() as $code => $label)
                                                <option value="{{ $code }}" title="{{ $label }}">{{ $code }}</option>
                                            @endforeach
                                        </select>
                                        @include($icon, ['name' => 'chevron'])
                                    </span>
                                    <input type="tel" name="phone" autocomplete="tel-national" class="vl-input" x-model="phone" @input="clearError('phone')" :class="{ 'is-error': errors.phone }" required>
                                </span>
                                <span class="vl-error" x-show="errors.phone" x-text="errors.phone" @error('phone') @else x-cloak @enderror>@error('phone'){{ $message }}@enderror</span>
                            </label>
                            <label class="vl-field">
                                <span class="vl-label">{{ __($copy.'email') }} <span class="vl-req">*</span></span>
                                <input type="email" name="email" autocomplete="email" class="vl-input" x-model="email" @input="clearError('email')" :class="{ 'is-error': errors.email }" required>
                                <span class="vl-error" x-show="errors.email" x-text="errors.email" @error('email') @else x-cloak @enderror>@error('email'){{ $message }}@enderror</span>
                            </label>
                        </div>

                        <div class="vl-field">
                            <label class="vl-consent">
                                <input type="checkbox" name="consent" value="1" x-model="consent" @change="clearError('consent')">
                                <span>{!! __($copy.'consent', ['privacy' => '<a href="'.e(route('law.data-protection')).'" target="_blank" rel="noopener">'.e(__($copy.'privacy')).'</a>']) !!} <span class="vl-req">*</span></span>
                            </label>
                            <span class="vl-error vl-error--indent" x-show="errors.consent" x-text="errors.consent" @error('consent') @else x-cloak @enderror>@error('consent'){{ $message }}@enderror</span>
                        </div>

                        @if(session('voucher_request_failed') || $errors->has('g-recaptcha-response'))
                            <div class="vl-alert" role="alert">
                                @include($icon, ['name' => 'alert'])
                                <span>{{ $errors->first('g-recaptcha-response') ?: __($copy.'server_error', ['email' => $contactEmail]) }}</span>
                            </div>
                        @endif

                        <x-recaptcha id="voucher-landing-recaptcha" invisible />

                        <div class="vl-submit">
                            <button type="submit" class="vl-btn vl-btn--block" :disabled="submitting" :class="{ 'is-loading': submitting }">
                                <span class="vl-spinner" x-show="submitting" x-cloak aria-hidden="true"></span>
                                <span x-show="submitting" x-cloak>{{ __($copy.'submitting') }}</span>
                                <span x-show="!submitting">{{ __($copy.'submit') }}</span>
                            </button>
                            <p class="vl-submit__note">{{ __($copy.'submit_note') }}</p>
                            @if(Recaptcha::active())
                                <p class="vl-submit__legal">
                                    {!! __('checkout.tour.recaptcha_notice', [
                                        'privacy' => '<a href="https://policies.google.com/privacy" target="_blank" rel="noopener">'.e(__('checkout.tour.recaptcha_privacy')).'</a>',
                                        'terms' => '<a href="https://policies.google.com/terms" target="_blank" rel="noopener">'.e(__('checkout.tour.recaptcha_terms')).'</a>',
                                    ]) !!}
                                </p>
                            @endif
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</section>
