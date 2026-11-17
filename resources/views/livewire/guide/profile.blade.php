<div class="ga-page">
    <header class="ga-page__header">
        <div>
            <h1 class="ga-page__title">{{ __('guide_area.profile.title') }}</h1>
            <p class="ga-page__subtitle">{{ __('guide_area.profile.subtitle') }}</p>
        </div>
    </header>

    {{-- Profile photo: uploads as soon as a file is picked --}}
    <section class="ga-card" aria-labelledby="ga-photo-title">
        <h2 id="ga-photo-title" class="ga-card__title">{{ __('guide_area.profile.photo') }}</h2>
        <p class="ga-card__subtitle">{{ __('guide_area.profile.photo_help') }}</p>

        <div class="ga-photo">
            @if ($photoUrl)
                <img src="{{ $photoUrl }}" alt="" class="ga-avatar ga-avatar--lg ga-avatar--img" width="72" height="72">
            @else
                <span class="ga-avatar ga-avatar--lg" aria-hidden="true">{{ mb_strtoupper(mb_substr((string) $guide->firstname, 0, 1).mb_substr((string) $guide->lastname, 0, 1)) }}</span>
            @endif

            <div>
                <div class="ga-photo__actions">
                    <label class="ga-btn ga-btn--dark" for="ga-photo-input">
                        <span wire:loading.remove wire:target="photo">{{ __('guide_area.profile.photo_upload') }}</span>
                        <span wire:loading wire:target="photo">{{ __('guide_area.common.uploading') }}</span>
                    </label>
                    <input id="ga-photo-input" type="file" wire:model="photo" accept="image/jpeg,image/png,image/webp" class="ga-visually-hidden">
                    @if ($photoUrl)
                        <button type="button" class="ga-btn ga-btn--danger-outline" wire:click="removePhoto" wire:confirm="{{ __('guide_area.profile.photo_remove_confirm') }}">{{ __('guide_area.profile.photo_remove') }}</button>
                    @endif
                </div>
                <p class="ga-help">{{ __('guide_area.profile.photo_recommendation') }}</p>
                @error('photo') <p class="ga-error">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <form wire:submit="save" novalidate>
        {{-- Fishing profile --}}
        <section class="ga-card" aria-labelledby="ga-fishing-title">
            <div class="ga-card__head">
                <div>
                    <h2 id="ga-fishing-title" class="ga-card__title">{{ __('guide_area.profile.fishing_profile') }}</h2>
                    <p class="ga-card__subtitle">{{ __('guide_area.profile.fishing_profile_help') }}</p>
                </div>
                <div class="ga-type">
                    <span class="ga-badge"><i class="far {{ $isCompany ? 'fa-building' : 'fa-user' }}" aria-hidden="true"></i> {{ __('guide_area.profile.type_'.($isCompany ? 'company' : 'private')) }}</span>
                    <span class="ga-type__hint">{!! __('guide_area.profile.type_change_hint', ['link' => '<a href="'.e(route('profile.account')).'">'.e(__('guide_area.nav.account')).'</a>']) !!}</span>
                </div>
            </div>

            <div class="ga-field">
                <label class="ga-label" for="ga-display-name">{{ __('guide_area.profile.display_name') }} <span class="ga-required" aria-hidden="true">*</span></label>
                <input id="ga-display-name" type="text" class="ga-input" wire:model="displayName" maxlength="{{ config('guide_area.profile.display_name_max') }}" required>
                <p class="ga-help">{{ __('guide_area.profile.display_name_help') }}</p>
                @error('displayName') <p class="ga-error">{{ $message }}</p> @enderror
            </div>

            @include('livewire.guide.partials.chip-input', [
                'model' => 'languages',
                'id' => 'ga-languages',
                'label' => __('guide_area.profile.languages'),
                'placeholder' => __('guide_area.profile.languages_placeholder'),
                'help' => __('guide_area.profile.languages_help'),
                'options' => $languageOptions,
                'allowCustom' => true,
                'required' => true,
            ])

            {{-- Deferred entangle: the counter updates locally, the value syncs on save. --}}
            <div class="ga-field" x-data="{ text: $wire.entangle('aboutMe') }">
                <label class="ga-label" for="ga-about">{{ __('guide_area.profile.about_me') }} <span class="ga-required" aria-hidden="true">*</span></label>
                <textarea id="ga-about" class="ga-input ga-textarea" rows="5" x-model="text" maxlength="{{ $aboutMeMax }}" required></textarea>
                <div class="ga-help ga-help--split">
                    <span>{{ __('guide_area.profile.about_me_help') }}</span>
                    <span x-text="(text || '').length + ' / {{ number_format($aboutMeMax, 0, ',', '.') }}'"></span>
                </div>
                @error('aboutMe') <p class="ga-error">{{ $message }}</p> @enderror
            </div>

            <div class="ga-grid-2">
                <div class="ga-field">
                    <label class="ga-label" for="ga-fish">{{ __('guide_area.profile.favorite_fish') }} <span class="ga-required" aria-hidden="true">*</span></label>
                    <input id="ga-fish" type="text" class="ga-input" wire:model="favoriteFish" maxlength="255" required>
                    @error('favoriteFish') <p class="ga-error">{{ $message }}</p> @enderror
                </div>
                <div class="ga-field">
                    <label class="ga-label" for="ga-since">{{ __('guide_area.profile.fishing_since') }} <span class="ga-required" aria-hidden="true">*</span></label>
                    <input id="ga-since" type="number" inputmode="numeric" class="ga-input" wire:model="fishingSince" min="1940" max="{{ date('Y') }}" required>
                    <p class="ga-help">{{ __('guide_area.profile.fishing_since_help') }}</p>
                    @error('fishingSince') <p class="ga-error">{{ $message }}</p> @enderror
                </div>
            </div>

            @if ($isCompany)
                <div class="ga-grid-2">
                    <div class="ga-field">
                        <label class="ga-label" for="ga-crew">{{ __('guide_area.profile.crew_size') }}</label>
                        <input id="ga-crew" type="number" inputmode="numeric" class="ga-input" wire:model="crewSize" min="1" max="500">
                        <p class="ga-help">{{ __('guide_area.profile.crew_size_help') }}</p>
                        @error('crewSize') <p class="ga-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="ga-field">
                        <label class="ga-label" for="ga-founded">{{ __('guide_area.profile.founded_year') }}</label>
                        <input id="ga-founded" type="number" inputmode="numeric" class="ga-input" wire:model="foundedYear" min="1900" max="{{ date('Y') }}">
                        @error('foundedYear') <p class="ga-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            @endif

            <hr class="ga-divider">

            @include('livewire.guide.partials.chip-input', [
                'model' => 'specialties',
                'id' => 'ga-specialties',
                'label' => __('guide_area.profile.specialties'),
                'placeholder' => __('guide_area.profile.specialties_placeholder'),
                'help' => __('guide_area.profile.tags_help'),
                'options' => $specialtySuggestions,
                'addButton' => true,
            ])

            @include('livewire.guide.partials.chip-input', [
                'model' => 'goodFor',
                'id' => 'ga-good-for',
                'label' => __('guide_area.profile.good_for'),
                'placeholder' => __('guide_area.profile.good_for_placeholder'),
                'help' => __('guide_area.profile.good_for_help'),
                'options' => $goodForSuggestions,
                'variant' => 'soft',
                'addButton' => true,
            ])
        </section>

        {{-- Licences & certificates (uploads are separate from the profile save) --}}
        <section class="ga-card" id="certificates" aria-labelledby="ga-cert-title">
            <h2 id="ga-cert-title" class="ga-card__title">{{ __('guide_area.certificates.title') }}</h2>
            <p class="ga-card__subtitle">{{ __('guide_area.certificates.help') }}</p>

            <ul class="ga-cert-list" role="list">
                @foreach ($certificateRows as $row)
                    @php($certificate = $row['certificate'])
                    <li class="ga-cert" wire:key="cert-{{ $certificate?->id ?? 'empty-'.$row['type'] }}">
                        <span @class(['ga-cert__icon', 'is-verified' => $certificate?->status === \App\Enums\CertificateStatus::Verified]) aria-hidden="true"><i class="fas fa-shield-alt"></i></span>
                        <div class="ga-cert__text">
                            <div class="ga-cert__name">{{ $certificate ? $certificate->label() : __('guide_area.certificates.types.'.$row['type']) }}</div>
                            <div class="ga-cert__meta">
                                @if (! $certificate)
                                    {{ __('guide_area.certificates.recommended_hint') }}
                                @elseif ($certificate->status === \App\Enums\CertificateStatus::Verified && $certificate->valid_until)
                                    {{ __('guide_area.certificates.valid_until_short', ['date' => $certificate->valid_until->translatedFormat('M Y')]) }}
                                @elseif ($certificate->status === \App\Enums\CertificateStatus::Rejected && $certificate->rejection_reason)
                                    {{ __('guide_area.certificates.rejected_reason', ['reason' => $certificate->rejection_reason]) }}
                                @else
                                    {{ __('guide_area.certificates.uploaded_on', ['date' => $certificate->updated_at->translatedFormat('j M Y')]) }}
                                @endif
                                @if ($certificate)
                                    · <a href="{{ route('guide.area.certificates.file', $certificate) }}" target="_blank" rel="noopener">{{ __('guide_area.certificates.view_file') }}</a>
                                @endif
                            </div>
                        </div>
                        <div class="ga-cert__actions">
                            @if (! $certificate)
                                <span class="ga-status ga-status--muted">{{ __('guide_area.certificates.status.not_added') }}</span>
                                <button type="button" class="ga-btn ga-btn--outline ga-btn--sm" wire:click="openCertificateForm('{{ $row['type'] }}')">{{ __('guide_area.certificates.upload') }}</button>
                            @else
                                @if ($certificate->isExpired())
                                    <span class="ga-status ga-status--danger">{{ __('guide_area.certificates.status.expired') }}</span>
                                @else
                                    <span @class([
                                        'ga-status',
                                        'ga-status--success' => $certificate->status === \App\Enums\CertificateStatus::Verified,
                                        'ga-status--warning' => $certificate->status === \App\Enums\CertificateStatus::InReview,
                                        'ga-status--danger' => $certificate->status === \App\Enums\CertificateStatus::Rejected,
                                    ])>{{ __($certificate->status->labelKey()) }}</span>
                                @endif
                                <button type="button" class="ga-btn ga-btn--outline ga-btn--sm" wire:click="openCertificateForm('', {{ $certificate->id }})">{{ __('guide_area.certificates.replace') }}</button>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>

            @if ($certificateFormOpen)
                <div class="ga-panel" wire:key="cert-form">
                    <h3 class="ga-panel__title">
                        {{ $replaceCertificateId ? __('guide_area.certificates.replace_title') : __('guide_area.certificates.add_title') }}
                    </h3>
                    <div class="ga-grid-2">
                        <div class="ga-field">
                            <label class="ga-label" for="ga-cert-type">{{ __('guide_area.certificates.type') }} <span class="ga-required" aria-hidden="true">*</span></label>
                            <select id="ga-cert-type" class="ga-input" wire:model.live="certificateType" @disabled($replaceCertificateId)>
                                <option value="">{{ __('guide_area.certificates.type_placeholder') }}</option>
                                @foreach ($certificateTypes as $type)
                                    <option value="{{ $type }}">{{ __('guide_area.certificates.types.'.$type) }}</option>
                                @endforeach
                            </select>
                            @error('certificateType') <p class="ga-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="ga-field">
                            <label class="ga-label" for="ga-cert-valid">{{ __('guide_area.certificates.valid_until') }}</label>
                            <input id="ga-cert-valid" type="date" class="ga-input" wire:model="certificateValidUntil" min="{{ now()->addDay()->toDateString() }}">
                            <p class="ga-help">{{ __('guide_area.certificates.valid_until_help') }}</p>
                            @error('certificateValidUntil') <p class="ga-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    @if ($certificateType === 'other')
                        <div class="ga-field">
                            <label class="ga-label" for="ga-cert-title">{{ __('guide_area.certificates.custom_title') }} <span class="ga-required" aria-hidden="true">*</span></label>
                            <input id="ga-cert-title" type="text" class="ga-input" wire:model="certificateTitle" maxlength="120">
                            @error('certificateTitle') <p class="ga-error">{{ $message }}</p> @enderror
                        </div>
                    @endif
                    <div class="ga-field">
                        <label class="ga-label" for="ga-cert-file">{{ __('guide_area.certificates.file') }} <span class="ga-required" aria-hidden="true">*</span></label>
                        <input id="ga-cert-file" type="file" class="ga-input" wire:model="certificateFile" accept=".pdf,.jpg,.jpeg,.png,.webp">
                        <p class="ga-help">{{ __('guide_area.certificates.file_help', ['mb' => (int) round(config('guide_area.certificates.max_kb') / 1024)]) }}</p>
                        @error('certificateFile') <p class="ga-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="ga-panel__actions">
                        <button type="button" class="ga-btn ga-btn--ghost" wire:click="closeCertificateForm">{{ __('guide_area.common.cancel') }}</button>
                        <button type="button" class="ga-btn ga-btn--dark" wire:click="uploadCertificate" wire:loading.attr="disabled" wire:target="uploadCertificate,certificateFile">
                            {{ __('guide_area.certificates.submit') }}
                        </button>
                    </div>
                </div>
            @else
                <button type="button" class="ga-btn ga-btn--outline ga-btn--sm ga-cert-add" wire:click="openCertificateForm">
                    <i class="fas fa-plus" aria-hidden="true"></i> {{ __('guide_area.certificates.add') }}
                </button>
            @endif
        </section>

        {{-- Payment methods for guests --}}
        <section class="ga-card" aria-labelledby="ga-pay-title">
            <h2 id="ga-pay-title" class="ga-card__title">{{ __('guide_area.payments.title') }}</h2>
            <p class="ga-card__subtitle">{{ __('guide_area.payments.subtitle') }}</p>

            <div class="ga-note"><i class="fas fa-info-circle" aria-hidden="true"></i> {{ __('guide_area.payments.note') }}</div>

            <div class="ga-methods" role="group" aria-label="{{ __('guide_area.payments.title') }}">
                <label @class(['ga-method', 'is-checked' => $barAllowed])>
                    <input type="checkbox" wire:model.live="barAllowed"> <span>{{ __('guide_area.payments.cash') }}</span>
                </label>
                <label @class(['ga-method', 'is-checked' => $bankAllowed])>
                    <input type="checkbox" wire:model.live="bankAllowed"> <span>{{ __('guide_area.payments.bank') }}</span>
                </label>
                <label @class(['ga-method', 'is-checked' => $paypalAllowed])>
                    <input type="checkbox" wire:model.live="paypalAllowed"> <span>{{ __('guide_area.payments.paypal') }}</span>
                </label>
            </div>
            @error('paymentMethods') <p class="ga-error">{{ $message }}</p> @enderror

            @if ($bankAllowed)
                <div class="ga-subpanel">
                    @if ($legacyBankDetails !== '')
                        <p class="ga-help ga-help--block">{{ __('guide_area.payments.legacy_hint') }} <strong>{{ $legacyBankDetails }}</strong></p>
                    @endif
                    <div class="ga-grid-3">
                        <div class="ga-field">
                            <label class="ga-label" for="ga-holder">{{ __('guide_area.payments.account_holder') }}</label>
                            <input id="ga-holder" type="text" class="ga-input" wire:model="bankHolder" maxlength="120" autocomplete="name">
                            @error('bankHolder') <p class="ga-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="ga-field">
                            <label class="ga-label" for="ga-iban">{{ __('guide_area.payments.iban') }}</label>
                            <input id="ga-iban" type="text" class="ga-input ga-input--mono" wire:model="bankIban" maxlength="42" placeholder="DE00 0000 0000 0000 0000 00" autocomplete="off" spellcheck="false">
                            @error('bankIban') <p class="ga-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="ga-field">
                            <label class="ga-label" for="ga-bic">{{ __('guide_area.payments.bic') }} <span class="ga-optional">({{ __('guide_area.common.optional') }})</span></label>
                            <input id="ga-bic" type="text" class="ga-input ga-input--mono" wire:model="bankBic" maxlength="11" autocomplete="off" spellcheck="false">
                            @error('bankBic') <p class="ga-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            @endif

            @if ($paypalAllowed)
                <div class="ga-subpanel">
                    <div class="ga-field">
                        <label class="ga-label" for="ga-paypal">{{ __('guide_area.payments.paypal_details') }}</label>
                        <input id="ga-paypal" type="text" class="ga-input" wire:model="paypalDetails" maxlength="255" placeholder="{{ __('guide_area.payments.paypal_placeholder') }}">
                        @error('paypalDetails') <p class="ga-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            @endif
        </section>

        <div class="ga-savebar">
            <p>{!! __('guide_area.profile.account_hint', ['link' => '<a href="'.e(route('profile.account')).'">'.e(__('guide_area.nav.account')).'</a>']) !!}</p>
            <button type="submit" class="ga-btn ga-btn--dark" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">{{ __('guide_area.profile.save') }}</span>
                <span wire:loading wire:target="save">{{ __('guide_area.common.saving') }}</span>
            </button>
        </div>
    </form>
</div>
