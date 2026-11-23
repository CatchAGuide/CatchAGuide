@use('App\Enums\AccountVerificationStatus')
@use('App\Http\Livewire\Guide\Account', 'GuideAccount')
<div class="ga-page ga-account">
    <header class="ga-page__header">
        <div>
            <h1 class="ga-page__title">{{ __('guide_area.account.title') }}</h1>
            <p class="ga-page__subtitle">{{ __('guide_area.account.subtitle') }}</p>
        </div>
    </header>

    {{-- Personal details (AC-01) --}}
    <form wire:submit="savePersonal" novalidate>
        <section class="ga-card" id="personal" aria-labelledby="ga-acc-personal">
            <h2 id="ga-acc-personal" class="ga-card__title ga-account__title">{{ __('guide_area.account.personal') }}</h2>

            <div class="ga-grid-2">
                <div class="ga-field">
                    <label class="ga-label" for="ga-acc-first">{{ __('guide_area.account.fields.firstname') }} <span class="ga-required" aria-hidden="true">*</span></label>
                    <input id="ga-acc-first" type="text" class="ga-input" wire:model="personal.firstname" autocomplete="given-name" required>
                    @error('personal.firstname') <p class="ga-error">{{ $message }}</p> @enderror
                </div>
                <div class="ga-field">
                    <label class="ga-label" for="ga-acc-last">{{ __('guide_area.account.fields.lastname') }} <span class="ga-required" aria-hidden="true">*</span></label>
                    <input id="ga-acc-last" type="text" class="ga-input" wire:model="personal.lastname" autocomplete="family-name" required>
                    @error('personal.lastname') <p class="ga-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="ga-grid-2">
                <div class="ga-field">
                    <label class="ga-label" for="ga-acc-email">{{ __('guide_area.account.fields.email') }}</label>
                    <input id="ga-acc-email" type="email" class="ga-input" value="{{ $user->email }}" readonly aria-describedby="ga-acc-email-help">
                    <p class="ga-help" id="ga-acc-email-help">{{ __('guide_area.account.email_help') }}</p>
                </div>
                <div class="ga-field">
                    <label class="ga-label" for="ga-acc-language">{{ __('guide_area.account.fields.language') }}</label>
                    <select id="ga-acc-language" class="ga-input" wire:model="personal.language" aria-describedby="ga-acc-language-help">
                        <option value="en">{{ __('guide_area.customer.account.languages.en') }}</option>
                        <option value="de">{{ __('guide_area.customer.account.languages.de') }}</option>
                    </select>
                    <p class="ga-help" id="ga-acc-language-help">{{ __('guide_area.account.language_help') }}</p>
                    @error('personal.language') <p class="ga-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="ga-account__footer">
                <span></span>
                <button type="submit" class="ga-btn ga-btn--dark" wire:loading.attr="disabled" wire:target="savePersonal">
                    <span wire:loading.remove wire:target="savePersonal">{{ __('guide_area.account.save_personal') }}</span>
                    <span wire:loading wire:target="savePersonal">{{ __('guide_area.common.saving') }}</span>
                </button>
            </div>
        </section>
    </form>

    {{-- Verification & billing (AC-02 … AC-04, spec 2.4) --}}
    <form wire:submit="saveBilling" novalidate>
        <section class="ga-card" id="billing" aria-labelledby="ga-acc-billing">
            <div class="ga-card__head">
                <div>
                    <h2 id="ga-acc-billing" class="ga-card__title">{{ __('guide_area.account.billing') }}</h2>
                    <p class="ga-card__subtitle">{{ __('guide_area.account.billing_subtitle') }}</p>
                </div>
                <span class="ga-status ga-status--{{ $status->tone() }}">
                    {{ __($status->labelKey()) }} · {{ __('guide_area.account.types.'.$storedType) }}
                </span>
            </div>

            @if ($rejectionReason)
                <div class="ga-note ga-note--danger" role="status">
                    <i class="fas fa-times-circle" aria-hidden="true"></i>
                    <span><strong>{{ __('guide_area.account.rejected_note') }}</strong> {{ $rejectionReason }}</span>
                </div>
            @elseif ($status === AccountVerificationStatus::Pending)
                <div class="ga-note" role="status">
                    <i class="fas fa-hourglass-half" aria-hidden="true"></i>
                    <span>{{ __('guide_area.account.pending_note') }}</span>
                </div>
            @endif

            <fieldset class="ga-field">
                <legend class="ga-label">{{ __('guide_area.account.type_question') }}</legend>
                <div class="ga-options ga-account__types">
                    @foreach (['private', 'company'] as $type)
                        <label class="ga-option ga-account__type {{ $billing['account_type'] === $type ? 'is-selected' : '' }}">
                            <input type="radio" class="ga-account__radio" wire:model.live="billing.account_type" value="{{ $type }}">
                            <span>
                                <span class="ga-option__name">{{ __('guide_area.account.types.'.$type) }}</span>
                                <span class="ga-option__text">{{ __('guide_area.account.type_hint.'.$type) }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('billing.account_type') <p class="ga-error">{{ $message }}</p> @enderror
            </fieldset>

            @if ($billing['account_type'] !== $storedType)
                <div class="ga-note ga-note--warning" role="alert">
                    <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                    <span>{{ __('guide_area.account.type_change_warning') }}</span>
                </div>
            @endif

            <p class="ga-help ga-account__lead">{{ __('guide_area.account.address_lead') }}</p>

            @if ($isCompany)
                <div class="ga-grid-2" wire:key="ga-acc-company">
                    <div class="ga-field">
                        <label class="ga-label" for="ga-acc-company">{{ __('guide_area.account.fields.company_name') }} <span class="ga-required" aria-hidden="true">*</span></label>
                        <input id="ga-acc-company" type="text" class="ga-input" wire:model="billing.company_name" autocomplete="organization" placeholder="{{ __('guide_area.account.company_placeholder') }}" required>
                        @error('billing.company_name') <p class="ga-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="ga-field">
                        <label class="ga-label" for="ga-acc-legal">{{ __('guide_area.account.fields.legal_form') }} <span class="ga-required" aria-hidden="true">*</span></label>
                        <select id="ga-acc-legal" class="ga-input" wire:model.live="legalFormChoice" required>
                            <option value="">{{ __('guide_area.account.select') }}</option>
                            @foreach ($legalFormGroups as $group)
                                <optgroup label="{{ $group['label'] }}">
                                    @foreach ($group['forms'] as $value => $form)
                                        <option value="{{ $value }}">{{ $form }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                            <option value="{{ GuideAccount::LEGAL_FORM_OTHER }}">{{ __('guide_area.account.legal_form_other') }}</option>
                        </select>
                        @if ($legalFormChoice === GuideAccount::LEGAL_FORM_OTHER)
                            <input type="text" class="ga-input ga-account__other" wire:model="legalFormOther" maxlength="100"
                                   aria-label="{{ __('guide_area.account.legal_form_other_label') }}" placeholder="{{ __('guide_area.account.legal_form_other_label') }}">
                        @endif
                        @error('billing.legal_form') <p class="ga-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            @else
                <div class="ga-grid-2" wire:key="ga-acc-private">
                    <div class="ga-field">
                        <label class="ga-label" for="ga-acc-birthday">{{ __('guide_area.account.fields.birthday') }} <span class="ga-required" aria-hidden="true">*</span></label>
                        <input id="ga-acc-birthday" type="date" class="ga-input" wire:model="billing.birthday" max="{{ $minBirthday }}" autocomplete="bday" required>
                        @error('billing.birthday') <p class="ga-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            @endif

            <div class="ga-grid-address">
                <div class="ga-field">
                    <label class="ga-label" for="ga-acc-street">{{ __('guide_area.account.fields.address') }} <span class="ga-required" aria-hidden="true">*</span></label>
                    <input id="ga-acc-street" type="text" class="ga-input" wire:model="billing.address" autocomplete="address-line1" required>
                    @error('billing.address') <p class="ga-error">{{ $message }}</p> @enderror
                </div>
                <div class="ga-field">
                    <label class="ga-label" for="ga-acc-number">{{ __('guide_area.account.fields.address_number') }} <span class="ga-required" aria-hidden="true">*</span></label>
                    <input id="ga-acc-number" type="text" class="ga-input" wire:model="billing.address_number" required>
                    @error('billing.address_number') <p class="ga-error">{{ $message }}</p> @enderror
                </div>
                <div class="ga-field">
                    <label class="ga-label" for="ga-acc-postal">{{ __('guide_area.account.fields.postal') }} <span class="ga-required" aria-hidden="true">*</span></label>
                    <input id="ga-acc-postal" type="text" class="ga-input" wire:model="billing.postal" autocomplete="postal-code" required>
                    @error('billing.postal') <p class="ga-error">{{ $message }}</p> @enderror
                </div>
                <div class="ga-field">
                    <label class="ga-label" for="ga-acc-city">{{ __('guide_area.account.fields.city') }} <span class="ga-required" aria-hidden="true">*</span></label>
                    <input id="ga-acc-city" type="text" class="ga-input" wire:model="billing.city" autocomplete="address-level2" required>
                    @error('billing.city') <p class="ga-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="ga-grid-2">
                <div class="ga-field">
                    <label class="ga-label" for="ga-acc-country">{{ __('guide_area.account.fields.country') }} <span class="ga-required" aria-hidden="true">*</span></label>
                    <select id="ga-acc-country" class="ga-input" wire:model="billing.country" autocomplete="country" required>
                        <option value="">{{ __('guide_area.account.select') }}</option>
                        @foreach ($countries as $code => $name)
                            <option value="{{ $code }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('billing.country') <p class="ga-error">{{ $message }}</p> @enderror
                </div>
                <div class="ga-field">
                    <label class="ga-label" for="ga-acc-phone">{{ __('guide_area.account.fields.phone') }} <span class="ga-required" aria-hidden="true">*</span></label>
                    <div class="ga-phone">
                        <select class="ga-input ga-phone__code" wire:model="billing.phone_country_code" aria-label="{{ __('guide_area.account.fields.phone_country_code') }}">
                            @foreach ($countryCodes as $dial => $country)
                                <option value="{{ $dial }}">{{ $dial }} ({{ $country }})</option>
                            @endforeach
                        </select>
                        <input id="ga-acc-phone" type="tel" class="ga-input" wire:model="billing.phone" autocomplete="tel-national" inputmode="tel" required>
                    </div>
                    @error('billing.phone_country_code') <p class="ga-error">{{ $message }}</p> @enderror
                    @error('billing.phone') <p class="ga-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="ga-field">
                <label class="ga-label" for="ga-acc-vat">{{ __('guide_area.account.fields.vat_id') }} <span class="ga-optional">{{ __('guide_area.common.optional') }}</span></label>
                <input id="ga-acc-vat" type="text" class="ga-input" wire:model="billing.vat_id" placeholder="{{ __('guide_area.account.vat_placeholder') }}"
                       aria-describedby="ga-acc-vat-help" @disabled($billing['vat_not_registered'])>
                <p class="ga-help" id="ga-acc-vat-help">{{ __('guide_area.account.vat_help') }}</p>
                @error('billing.vat_id') <p class="ga-error">{{ $message }}</p> @enderror
                <label class="ga-checkline">
                    <input type="checkbox" wire:model.live="billing.vat_not_registered">
                    {{ __('guide_area.account.vat_not_registered') }}
                </label>
            </div>

            <div class="ga-account__footer">
                <p class="ga-help">
                    {!! __('guide_area.account.profile_hint', ['link' => '<a href="'.e(route('guide.area.profile')).'">'.e(__('guide_area.nav.profile')).'</a>']) !!}
                </p>
                <button type="submit" class="ga-btn ga-btn--dark" wire:loading.attr="disabled" wire:target="saveBilling">
                    <span wire:loading.remove wire:target="saveBilling">{{ __('guide_area.account.save_billing') }}</span>
                    <span wire:loading wire:target="saveBilling">{{ __('guide_area.common.saving') }}</span>
                </button>
            </div>
        </section>
    </form>

    {{-- Password (AC-06) --}}
    <section class="ga-card" id="password" aria-labelledby="ga-acc-pw">
        <div class="ga-card__head">
            <div>
                <h2 id="ga-acc-pw" class="ga-card__title">{{ __('guide_area.account.password') }}</h2>
                <p class="ga-card__subtitle ga-account__subtitle-tight">
                    @if ($passwordChangedAt)
                        {{ __('guide_area.account.password_changed_on', ['date' => $passwordChangedAt->locale(app()->getLocale())->isoFormat('LL')]) }}
                    @else
                        {{ __('guide_area.customer.account.password_help', ['min' => config('guide_area.customer.password_min', 8)]) }}
                    @endif
                </p>
            </div>
            @unless ($passwordOpen)
                <button type="button" class="ga-btn ga-btn--outline" wire:click="$set('passwordOpen', true)">{{ __('guide_area.account.password_open') }}</button>
            @endunless
        </div>

        @if ($passwordOpen)
            <form wire:submit="changePassword" novalidate class="ga-account__password">
                <div class="ga-account__password-grid">
                    <div class="ga-field">
                        <label class="ga-label" for="ga-acc-current">{{ __('guide_area.customer.account.fields.currentPassword') }}</label>
                        <input id="ga-acc-current" type="password" class="ga-input" wire:model="currentPassword" autocomplete="current-password" required>
                        @error('currentPassword') <p class="ga-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="ga-field">
                        <label class="ga-label" for="ga-acc-new">{{ __('guide_area.customer.account.fields.newPassword') }}</label>
                        <input id="ga-acc-new" type="password" class="ga-input" wire:model="newPassword" autocomplete="new-password"
                               minlength="{{ config('guide_area.customer.password_min', 8) }}" aria-describedby="ga-acc-new-help" required>
                        <p class="ga-help" id="ga-acc-new-help">{{ __('guide_area.customer.account.password_help', ['min' => config('guide_area.customer.password_min', 8)]) }}</p>
                        @error('newPassword') <p class="ga-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="ga-field">
                        <label class="ga-label" for="ga-acc-confirm">{{ __('guide_area.customer.account.fields.newPasswordConfirmation') }}</label>
                        <input id="ga-acc-confirm" type="password" class="ga-input" wire:model="newPasswordConfirmation" autocomplete="new-password" required>
                        @error('newPasswordConfirmation') <p class="ga-error">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="ga-account__actions">
                    <button type="submit" class="ga-btn ga-btn--dark" wire:loading.attr="disabled" wire:target="changePassword">{{ __('guide_area.account.password_submit') }}</button>
                    <button type="button" class="ga-btn ga-btn--outline" wire:click="cancelPassword">{{ __('guide_area.account.cancel') }}</button>
                </div>
                <p class="ga-help ga-customer-note">
                    {{ __('guide_area.customer.account.forgot') }}
                    <a href="{{ route('password.request') }}">{{ __('guide_area.customer.account.forgot_link') }}</a>
                </p>
            </form>
        @endif
    </section>

    {{-- Cookie settings (AC-07; the consent script binds the panel once, so Livewire must not redraw it) --}}
    <section class="ga-card" id="privacy" wire:ignore>
        <h2 class="ga-card__title">@lang('cookie.settings-title')</h2>
        <p class="ga-card__subtitle">@lang('cookie.settings-intro')</p>
        @include('layouts.partials.cookie-settings-panel', ['panelKey' => 'profile'])
    </section>
</div>
