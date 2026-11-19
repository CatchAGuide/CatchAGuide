<div class="ga-page"
     x-data="{ tab: @js($tab), urls: @js($tabUrls), go(t) { this.tab = t; try { history.replaceState(history.state, '', this.urls[t]); } catch (e) {} } }">
    <header class="ga-page__header">
        <div>
            <h1 class="ga-page__title">{{ __('guide_area.customer.account.title') }}</h1>
            <p class="ga-page__subtitle">{{ __('guide_area.customer.account.subtitle') }}</p>
        </div>
    </header>

    <div class="ga-tabs ga-tabs--underline" role="tablist" aria-label="{{ __('guide_area.customer.account.title') }}">
        @foreach ($tabs as $key)
            <button type="button" role="tab" id="ga-acc-tab-{{ $key }}" aria-controls="ga-acc-panel-{{ $key }}"
                    class="ga-tabs__tab" :class="{ 'is-active': tab === '{{ $key }}' }" :aria-selected="(tab === '{{ $key }}').toString()"
                    @click="go('{{ $key }}')">
                {{ __('guide_area.customer.account.tabs.'.$key) }}
            </button>
        @endforeach
    </div>

    {{-- Personal details --}}
    <div id="ga-acc-panel-details" role="tabpanel" aria-labelledby="ga-acc-tab-details" x-show="tab === 'details'" @if ($tab !== 'details') x-cloak @endif>
        <section class="ga-card" aria-labelledby="ga-acc-photo">
            <h2 id="ga-acc-photo" class="ga-card__title">{{ __('guide_area.customer.account.photo') }}</h2>
            <div class="ga-photo">
                @if ($photoUrl)
                    <img src="{{ $photoUrl }}" alt="" class="ga-avatar ga-avatar--lg ga-avatar--img" width="72" height="72">
                @else
                    <span class="ga-avatar ga-avatar--lg" aria-hidden="true">{{ mb_strtoupper(mb_substr((string) $user->firstname, 0, 1).mb_substr((string) $user->lastname, 0, 1)) }}</span>
                @endif
                <div>
                    <div class="ga-photo__actions">
                        <label class="ga-btn ga-btn--dark" for="ga-acc-photo-input">
                            <span wire:loading.remove wire:target="photo">{{ __('guide_area.profile.photo_upload') }}</span>
                            <span wire:loading wire:target="photo">{{ __('guide_area.common.uploading') }}</span>
                        </label>
                        <input id="ga-acc-photo-input" type="file" wire:model="photo" accept="image/jpeg,image/png,image/webp" class="ga-visually-hidden">
                        @if ($photoUrl)
                            <button type="button" class="ga-btn ga-btn--danger-outline" wire:click="removePhoto" wire:confirm="{{ __('guide_area.profile.photo_remove_confirm') }}">{{ __('guide_area.profile.photo_remove') }}</button>
                        @endif
                    </div>
                    <p class="ga-help">{{ __('guide_area.customer.account.photo_help') }}</p>
                    @error('photo') <p class="ga-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <form wire:submit="saveDetails" novalidate>
            <section class="ga-card" aria-labelledby="ga-acc-personal">
                <h2 id="ga-acc-personal" class="ga-card__title">{{ __('guide_area.customer.account.personal') }}</h2>

                @if ($user->is_company)
                    <div class="ga-field">
                        <label class="ga-label" for="ga-acc-company">{{ __('guide_area.customer.account.fields.companyName') }} <span class="ga-required" aria-hidden="true">*</span></label>
                        <input id="ga-acc-company" type="text" class="ga-input" wire:model="companyName" autocomplete="organization" required>
                        @error('companyName') <p class="ga-error">{{ $message }}</p> @enderror
                    </div>
                @else
                    <div class="ga-grid-2">
                        <div class="ga-field">
                            <label class="ga-label" for="ga-acc-first">{{ __('guide_area.customer.account.fields.firstname') }} <span class="ga-required" aria-hidden="true">*</span></label>
                            <input id="ga-acc-first" type="text" class="ga-input" wire:model="firstname" autocomplete="given-name" required>
                            @error('firstname') <p class="ga-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="ga-field">
                            <label class="ga-label" for="ga-acc-last">{{ __('guide_area.customer.account.fields.lastname') }} <span class="ga-required" aria-hidden="true">*</span></label>
                            <input id="ga-acc-last" type="text" class="ga-input" wire:model="lastname" autocomplete="family-name" required>
                            @error('lastname') <p class="ga-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                @endif

                <div class="ga-grid-2">
                    <div class="ga-field">
                        <label class="ga-label" for="ga-acc-email">{{ __('guide_area.customer.account.fields.email') }}</label>
                        <input id="ga-acc-email" type="email" class="ga-input" value="{{ $user->email }}" readonly aria-describedby="ga-acc-email-help">
                        <p class="ga-help" id="ga-acc-email-help">{{ __('guide_area.customer.account.email_help') }}</p>
                    </div>
                    <div class="ga-field">
                        <label class="ga-label" for="ga-acc-phone">{{ __('guide_area.customer.account.fields.phone') }} <span class="ga-required" aria-hidden="true">*</span></label>
                        <div class="ga-phone">
                            <select class="ga-input ga-phone__code" wire:model="phoneCountryCode" aria-label="{{ __('guide_area.customer.account.fields.phoneCountryCode') }}">
                                @foreach ($countryCodes as $dial => $country)
                                    <option value="{{ $dial }}">{{ $dial }} ({{ $country }})</option>
                                @endforeach
                            </select>
                            <input id="ga-acc-phone" type="tel" class="ga-input" wire:model="phone" autocomplete="tel-national" inputmode="tel" required>
                        </div>
                        @error('phoneCountryCode') <p class="ga-error">{{ $message }}</p> @enderror
                        @error('phone') <p class="ga-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="ga-grid-2">
                    <div class="ga-field">
                        <label class="ga-label" for="ga-acc-birthday">{{ __('guide_area.customer.account.fields.birthday') }} <span class="ga-optional">{{ __('guide_area.common.optional') }}</span></label>
                        <input id="ga-acc-birthday" type="date" class="ga-input" wire:model="birthday" max="{{ now()->subDay()->toDateString() }}" autocomplete="bday">
                        @error('birthday') <p class="ga-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="ga-field">
                        <label class="ga-label" for="ga-acc-language">{{ __('guide_area.customer.account.fields.language') }}</label>
                        <select id="ga-acc-language" class="ga-input" wire:model="language">
                            <option value="de">{{ __('guide_area.customer.account.languages.de') }}</option>
                            <option value="en">{{ __('guide_area.customer.account.languages.en') }}</option>
                        </select>
                        <p class="ga-help">{{ __('guide_area.customer.account.language_help') }}</p>
                        @error('language') <p class="ga-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <section class="ga-card" aria-labelledby="ga-acc-address">
                <h2 id="ga-acc-address" class="ga-card__title">{{ __('guide_area.customer.account.address') }} <span class="ga-optional">{{ __('guide_area.common.optional') }}</span></h2>
                <div class="ga-grid-address">
                    <div class="ga-field">
                        <label class="ga-label" for="ga-acc-street">{{ __('guide_area.customer.account.fields.address') }}</label>
                        <input id="ga-acc-street" type="text" class="ga-input" wire:model="address" autocomplete="address-line1">
                        @error('address') <p class="ga-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="ga-field">
                        <label class="ga-label" for="ga-acc-number">{{ __('guide_area.customer.account.fields.addressNumber') }}</label>
                        <input id="ga-acc-number" type="text" class="ga-input" wire:model="addressNumber">
                        @error('addressNumber') <p class="ga-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="ga-field">
                        <label class="ga-label" for="ga-acc-postal">{{ __('guide_area.customer.account.fields.postal') }}</label>
                        <input id="ga-acc-postal" type="text" class="ga-input" wire:model="postal" autocomplete="postal-code">
                        @error('postal') <p class="ga-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="ga-field">
                        <label class="ga-label" for="ga-acc-city">{{ __('guide_area.customer.account.fields.city') }}</label>
                        <input id="ga-acc-city" type="text" class="ga-input" wire:model="city" autocomplete="address-level2">
                        @error('city') <p class="ga-error">{{ $message }}</p> @enderror
                    </div>
                </div>
                @if ($showTaxId)
                    <div class="ga-field">
                        <label class="ga-label" for="ga-acc-tax">{{ __('guide_area.customer.account.fields.taxId') }} <span class="ga-optional">{{ __('guide_area.common.optional') }}</span></label>
                        <input id="ga-acc-tax" type="text" class="ga-input" wire:model="taxId">
                        @error('taxId') <p class="ga-error">{{ $message }}</p> @enderror
                    </div>
                @endif
            </section>

            <div class="ga-savebar">
                <p>{{ __('guide_area.customer.account.save_hint') }}</p>
                <button type="submit" class="ga-btn ga-btn--dark" wire:loading.attr="disabled" wire:target="saveDetails">
                    <span wire:loading.remove wire:target="saveDetails">{{ __('guide_area.profile.save') }}</span>
                    <span wire:loading wire:target="saveDetails">{{ __('guide_area.common.saving') }}</span>
                </button>
            </div>
        </form>
    </div>

    {{-- Password --}}
    <div id="ga-acc-panel-password" role="tabpanel" aria-labelledby="ga-acc-tab-password" x-show="tab === 'password'" @if ($tab !== 'password') x-cloak @endif>
        <form wire:submit="changePassword" novalidate>
            <section class="ga-card" aria-labelledby="ga-acc-pw">
                <h2 id="ga-acc-pw" class="ga-card__title">{{ __('guide_area.customer.account.password_title') }}</h2>
                <p class="ga-card__subtitle">{{ __('guide_area.customer.account.password_help', ['min' => config('guide_area.customer.password_min', 8)]) }}</p>
                <div class="ga-field ga-field--narrow">
                    <label class="ga-label" for="ga-acc-current">{{ __('guide_area.customer.account.fields.currentPassword') }}</label>
                    <input id="ga-acc-current" type="password" class="ga-input" wire:model="currentPassword" autocomplete="current-password" required>
                    @error('currentPassword') <p class="ga-error">{{ $message }}</p> @enderror
                </div>
                <div class="ga-field ga-field--narrow">
                    <label class="ga-label" for="ga-acc-new">{{ __('guide_area.customer.account.fields.newPassword') }}</label>
                    <input id="ga-acc-new" type="password" class="ga-input" wire:model="newPassword" autocomplete="new-password" minlength="{{ config('guide_area.customer.password_min', 8) }}" required>
                    @error('newPassword') <p class="ga-error">{{ $message }}</p> @enderror
                </div>
                <div class="ga-field ga-field--narrow">
                    <label class="ga-label" for="ga-acc-confirm">{{ __('guide_area.customer.account.fields.newPasswordConfirmation') }}</label>
                    <input id="ga-acc-confirm" type="password" class="ga-input" wire:model="newPasswordConfirmation" autocomplete="new-password" required>
                    @error('newPasswordConfirmation') <p class="ga-error">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="ga-btn ga-btn--dark" wire:loading.attr="disabled" wire:target="changePassword">{{ __('guide_area.customer.account.password_submit') }}</button>
                <p class="ga-help ga-customer-note">
                    {{ __('guide_area.customer.account.forgot') }}
                    <a href="{{ route('password.request') }}">{{ __('guide_area.customer.account.forgot_link') }}</a>
                </p>
            </section>
        </form>
    </div>

    {{-- Cookie settings (the consent script binds the panel once, so Livewire must not redraw it) --}}
    <div id="ga-acc-panel-privacy" role="tabpanel" aria-labelledby="ga-acc-tab-privacy" x-show="tab === 'privacy'" @if ($tab !== 'privacy') x-cloak @endif>
        <section class="ga-card" wire:ignore>
            <h2 class="ga-card__title">@lang('cookie.settings-title')</h2>
            <p class="ga-card__subtitle">@lang('cookie.settings-intro')</p>
            @include('layouts.partials.cookie-settings-panel', ['panelKey' => 'profile'])
        </section>
    </div>
</div>
