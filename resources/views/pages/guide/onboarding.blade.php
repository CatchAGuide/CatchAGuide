@extends($inProfile ? 'pages.profile.layouts.profile' : 'layouts.app-v2-1')

@if($inProfile)
@section('title', __('profile.onboarding_intro_title'))
@endif

@section($inProfile ? 'profile-content' : 'content')
@if(!$inProfile)
<div class="profile-dashboard">
    <div class="container profile-wrapper py-4">
@endif

    <div class="bookings-header mb-4">
        <h1 class="mb-0 text-white">
            <i class="fas fa-certificate"></i>
            {{ __('profile.onboarding_intro_title') }}
        </h1>
        <p class="mb-0 mt-2 text-white">{{ __('profile.onboarding_intro_subtitle') }}</p>
    </div>

    <div class="alert alert-light border mb-4" role="status">
        <i class="fas fa-info-circle text-primary me-2"></i>
        {{ __('profile.onboarding_after_submit') }}
        <span class="d-block mt-1 small text-muted">{{ __('profile.onboarding_payment_note') }}</span>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger border-0 mb-4">
            <div class="d-flex align-items-start">
                <i class="fas fa-exclamation-triangle me-3 text-danger mt-1"></i>
                <div>
                    <strong>{{ __('profile.fix_following_errors') }}</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="info-section mb-4">
        <div class="info-card">
            <div class="info-header">
                <i class="fas fa-question-circle"></i>
                <h3>{{ __('profile.onboarding_why_verify') }}</h3>
            </div>
            <div class="info-content">
                <p class="mb-0">
                    {{ __('profile.guideVerificationIntro') }}
                </p>
            </div>
        </div>
    </div>

    <nav class="guide-wizard-progress mb-4" aria-label="{{ __('profile.onboarding_step_details') }}">
        <ol class="guide-wizard-progress__list">
            @foreach($wizardSteps as $index => $step)
                <li class="guide-wizard-progress__item" data-progress-step="{{ $index }}">
                    <span class="guide-wizard-progress__badge">{{ $index + 1 }}</span>
                    <span class="guide-wizard-progress__label">{{ $step['label'] }}</span>
                </li>
            @endforeach
        </ol>
    </nav>

    <form method="POST" action="{{ route('guide.onboarding.store') }}" id="guide-onboarding-form" class="guide-application-form">
        @csrf
        <input type="hidden" name="is_fast_lane" value="{{ $isFastLane ? '1' : '0' }}">

        @if($isFastLane)
        <div class="wizard-step" data-step-id="account">
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon"><i class="fas fa-user-plus"></i></div>
                    <div class="section-title">
                        <h4>{{ __('profile.onboarding_step_account') }}</h4>
                        <p class="text-muted mb-0">{{ __('profile.createAccount') }}</p>
                    </div>
                </div>
                <div class="section-content">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('checkout.forename') }} <span class="required">*</span></label>
                            <input type="text" name="firstname" class="form-control" value="{{ old('firstname') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('checkout.surname') }} <span class="required">*</span></label>
                            <input type="text" name="lastname" class="form-control" value="{{ old('lastname') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Email <span class="required">*</span></label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autocomplete="email">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('global.Password') }} <span class="required">*</span></label>
                            <input type="password" name="password" class="form-control" required autocomplete="new-password">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('global.Confirm Password') }} <span class="required">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
                        </div>
                        <div class="col-12">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="terms" value="1" id="terms" required @checked(old('terms'))>
                                <label class="form-check-label" for="terms">AGB <span class="required">*</span></label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="privacy" value="1" id="privacy" required @checked(old('privacy'))>
                                <label class="form-check-label" for="privacy">{{ __('checkout.privacy_policy') }} <span class="required">*</span></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @include('pages.guide.partials.wizard-nav', ['showBack' => false])
        </div>
        @endif

        <div class="wizard-step" data-step-id="type" @if($isFastLane) style="display:none" @endif>
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon"><i class="fas fa-id-card"></i></div>
                    <div class="section-title">
                        <h4>{{ __('profile.onboarding_step_type') }}</h4>
                        <p class="text-muted mb-0">{{ __('profile.howDoYouOffer') }}</p>
                    </div>
                </div>
                <div class="section-content">
                    <div class="guide-type-cards">
                        <label class="guide-type-card">
                            <input type="radio" name="guide_type" value="private" class="guide-type-card__input"
                                {{ old('guide_type', 'private') === 'private' ? 'checked' : '' }} required>
                            <span class="guide-type-card__body">
                                <i class="fas fa-user"></i>
                                <strong>{{ __('profile.privatePerson') }}</strong>
                                <small>{{ __('profile.offerAsIndividual') }}</small>
                            </span>
                        </label>
                        @if($companyEnabled)
                        <label class="guide-type-card">
                            <input type="radio" name="guide_type" value="company" class="guide-type-card__input"
                                {{ old('guide_type') === 'company' ? 'checked' : '' }}>
                            <span class="guide-type-card__body">
                                <i class="fas fa-building"></i>
                                <strong>{{ __('profile.company') }}</strong>
                                <small>{{ __('profile.representCompany') }}</small>
                            </span>
                        </label>
                        @endif
                    </div>
                </div>
            </div>
            @include('pages.guide.partials.wizard-nav', ['showBack' => $isFastLane])
        </div>

        <div class="wizard-step" data-step-id="details" style="display:none">
            @if($user)
            <div class="form-section mb-4">
                <div class="section-header">
                    <div class="section-icon"><i class="fas fa-user-check"></i></div>
                    <div class="section-title">
                        <h4>{{ __('profile.onboarding_account_summary') }}</h4>
                        <p class="text-muted mb-0">{{ __('profile.onboarding_account_summary_hint') }}</p>
                    </div>
                </div>
                <div class="section-content">
                    <div class="account-summary-panel">
                        <div class="account-summary-panel__avatar" aria-hidden="true">
                            <i class="fas fa-user"></i>
                        </div>
                        <dl class="account-summary-panel__details mb-0">
                            <div class="account-summary-panel__row">
                                <dt>{{ __('global.Name') }}</dt>
                                <dd>{{ trim($user->firstname . ' ' . $user->lastname) }}</dd>
                            </div>
                            <div class="account-summary-panel__row">
                                <dt>Email</dt>
                                <dd>{{ $user->email }}</dd>
                            </div>
                        </dl>
                    </div>
                    <p class="text-muted small mb-0 mt-3">
                        <i class="fas fa-lock me-1"></i>{{ __('profile.onboarding_account_summary_hint') }}
                    </p>
                </div>
            </div>
            @endif

            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div class="section-title">
                        <h4>{{ __('profile.address_information') }}</h4>
                        <p class="text-muted mb-0">{{ __('profile.currentAddressVerification') }}</p>
                    </div>
                </div>
                @php
                    $selectedGuideType = old('guide_type', 'private');
                    $isCompanySelected = $selectedGuideType === 'company';
                    $countryPrefill = old('information.country', $user?->information?->country ?? 'DE');
                    if (is_string($countryPrefill) && strlen($countryPrefill) > 3) {
                        $countryPrefill = 'DE';
                    }
                @endphp
                <div class="section-content">
                    <div class="row g-4">
                        <div class="col-md-6 company-only" @if(!$isCompanySelected) style="display:none" @endif>
                            <label class="form-label">{{ __('profile.companyName') }} <span class="required">*</span></label>
                            <input type="text" name="information[company_name]" class="form-control"
                                @if($isCompanySelected) required @endif
                                value="{{ old('information.company_name', $user?->information?->company_name) }}">
                        </div>
                        <div class="col-md-6 company-only" @if(!$isCompanySelected) style="display:none" @endif>
                            <label class="form-label">{{ __('profile.legalForm') }} <span class="required">*</span></label>
                            <select name="information[legal_form]" class="form-select" @if($isCompanySelected) required @endif>
                                <option value="">—</option>
                                @foreach(['GmbH','UG','GbR','Einzelunternehmen','e.K.','AG','sonstige'] as $form)
                                    <option value="{{ $form }}" @selected(old('information.legal_form', $user?->information?->legal_form) === $form)>{{ $form }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 private-only" @if($isCompanySelected) style="display:none" @endif>
                            <label class="form-label">{{ __('profile.bday') }}</label>
                            <input type="date" max="{{ now()->format('Y-m-d') }}" name="information[birthday]" class="form-control"
                                value="{{ old('information.birthday', optional($user?->information?->birthday)->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">{{ __('forms.street') }} <span class="required">*</span></label>
                            <input type="text" name="information[address]" class="form-control" required
                                value="{{ old('information.address', $user?->information?->address) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('profile.no.') }} <span class="required">*</span></label>
                            <input type="text" name="information[address_number]" class="form-control" required
                                value="{{ old('information.address_number', $user?->information?->address_number) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('profile.zip') }} <span class="required">*</span></label>
                            <input type="text" name="information[postal]" class="form-control" required
                                value="{{ old('information.postal', $user?->information?->postal) }}">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">{{ __('checkout.city') }} <span class="required">*</span></label>
                            <input type="text" name="information[city]" class="form-control" required
                                value="{{ old('information.city', $user?->information?->city) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('global.Country') }}</label>
                            <input type="text" name="information[country]" class="form-control"
                                maxlength="3" placeholder="DE" autocomplete="country"
                                value="{{ $countryPrefill }}">
                            <small class="form-text text-muted">{{ __('profile.onboarding_country_hint') }}</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('search-request.phone') }} <span class="required">*</span></label>
                            <input type="text" name="information[phone]" class="form-control" required
                                value="{{ old('information.phone', $user?->information?->phone ?? $user?->phone) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('profile.taxIdNum') }}</label>
                            <input type="text" name="information[taxId]" class="form-control"
                                value="{{ old('information.taxId', $user?->tax_id) }}">
                            <small class="form-text text-muted">{{ __('profile.taxIdNumHint') }}</small>
                        </div>
                    </div>
                </div>
            </div>

            @include('pages.guide.partials.wizard-nav', ['showBack' => true])
        </div>

        <div class="wizard-step" data-step-id="legal" style="display:none">
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon"><i class="fas fa-shield-alt"></i></div>
                    <div class="section-title">
                        <h4>{{ __('profile.legalConfirmation') }}</h4>
                        <p class="text-muted mb-0">{{ __('profile.fishingLicenseCompliance') }}</p>
                    </div>
                </div>
                <div class="section-content">
                    <div class="legal-confirmation">
                        <div class="form-check legal-check mb-3">
                            <input class="form-check-input" type="checkbox" name="lawcard" value="1" id="lawcard" required @checked(old('lawcard'))>
                            <label class="form-check-label" for="lawcard">
                                <strong>{{ __('profile.fishingLicense') }}</strong>
                                <small class="d-block text-muted mt-1">
                                    {{ __('profile.confirmLicense') }}
                                </small>
                            </label>
                        </div>
                        <div class="form-check legal-check mb-3">
                            <input class="form-check-input" type="checkbox" name="lawcard_nature" value="1" id="lawcard_nature" required @checked(old('lawcard_nature'))>
                            <label class="form-check-label" for="lawcard_nature">
                                <strong>{{ __('profile.natureProtectionLaws') }}</strong>
                                <small class="d-block text-muted mt-1">{{ __('profile.lawcard_nature_text') }}</small>
                            </label>
                        </div>
                        <div class="form-check legal-check">
                            <input class="form-check-input" type="checkbox" name="lawcard_truthful" value="1" id="lawcard_truthful" required @checked(old('lawcard_truthful'))>
                            <label class="form-check-label" for="lawcard_truthful">
                                <strong>{{ __('profile.truthfulDataConfirmation') }}</strong>
                                <small class="d-block text-muted mt-1">{{ __('profile.lawcard_truthful_text') }}</small>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <div class="d-flex justify-content-between flex-wrap gap-3">
                    <button type="button" class="btn btn-outline-secondary btn-lg wizard-prev">
                        <i class="fas fa-arrow-left me-2"></i>{{ __('accommodations.previous') }}
                    </button>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-paper-plane me-2"></i>{{ __('profile.submitApplication') }}
                    </button>
                </div>
            </div>
        </div>
    </form>

@if(!$inProfile)
    </div>
</div>
@endif
@endsection

@section('css_after')
@parent
<style>
.guide-wizard-progress__list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    list-style: none;
    padding: 0;
    margin: 0;
}
.guide-wizard-progress__item {
    flex: 1 1 140px;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.65rem 0.85rem;
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    opacity: 0.55;
    transition: all 0.25s ease;
}
.guide-wizard-progress__item.is-active,
.guide-wizard-progress__item.is-complete {
    opacity: 1;
    border-color: #313041;
    background: #fff;
}
.guide-wizard-progress__item.is-active {
    box-shadow: 0 4px 12px rgba(49, 48, 65, 0.12);
}
.guide-wizard-progress__badge {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #313041;
    color: #fff;
    font-size: 0.8rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.guide-wizard-progress__label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #313041;
}
.guide-type-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
}
.guide-type-card { margin: 0; cursor: pointer; }
.guide-type-card__input { position: absolute; opacity: 0; pointer-events: none; }
.guide-type-card__body {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    padding: 1.25rem;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    background: #f8f9fa;
    height: 100%;
    transition: all 0.2s ease;
}
.guide-type-card__body i { font-size: 1.5rem; color: #313041; }
.guide-type-card__input:checked + .guide-type-card__body {
    border-color: #313041;
    background: rgba(49, 48, 65, 0.06);
    box-shadow: 0 4px 12px rgba(49, 48, 65, 0.1);
}
.guide-wizard-nav { margin-top: 0.5rem; }
.account-summary-panel {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    padding: 1.25rem 1.5rem;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 10px;
}
.account-summary-panel__avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: #313041;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    flex-shrink: 0;
}
.account-summary-panel__details { flex: 1; min-width: 0; }
.account-summary-panel__row {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem 1rem;
    padding: 0.35rem 0;
}
.account-summary-panel__row:not(:last-child) {
    border-bottom: 1px solid #e9ecef;
}
.account-summary-panel__row dt {
    font-weight: 600;
    color: #6c757d;
    margin: 0;
    min-width: 4rem;
}
.account-summary-panel__row dd {
    margin: 0;
    font-weight: 600;
    color: #313041;
}
</style>
@endsection

@section('js_after')
@parent
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('guide-onboarding-form');
    if (!form) return;

    const steps = Array.from(form.querySelectorAll('.wizard-step'));
    const progressItems = Array.from(document.querySelectorAll('.guide-wizard-progress__item'));
    const hasServerErrors = {{ $errors->any() ? 'true' : 'false' }};
    let current = 0;

    function updateProgress() {
        progressItems.forEach((item, i) => {
            item.classList.toggle('is-active', i === current);
            item.classList.toggle('is-complete', i < current);
        });
    }

    function showStep(index) {
        if (index < 0 || index >= steps.length) return;
        steps.forEach((s, i) => { s.style.display = i === index ? 'block' : 'none'; });
        current = index;
        updateProgress();
        toggleCompanyFields();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function toggleCompanyFields() {
        // Scope to this form — the page also embeds guideApplicationModal with its own guide_type radios.
        const isCompany = form.querySelector('input[name="guide_type"]:checked')?.value === 'company';
        form.querySelectorAll('.company-only').forEach(el => {
            el.style.display = isCompany ? '' : 'none';
            el.querySelectorAll('input,select').forEach(inp => inp.required = !!isCompany);
        });
        form.querySelectorAll('.private-only').forEach(el => {
            el.style.display = isCompany ? 'none' : '';
        });
    }

    function validateStep(step) {
        const fields = step.querySelectorAll('input, select, textarea');
        for (const field of fields) {
            const companyOnly = field.closest('.company-only');
            const privateOnly = field.closest('.private-only');
            if (companyOnly && companyOnly.style.display === 'none') continue;
            if (privateOnly && privateOnly.style.display === 'none') continue;
            if (field.disabled || field.type === 'hidden') continue;
            if (!field.checkValidity()) {
                showStep(steps.indexOf(step));
                field.reportValidity();
                return false;
            }
        }
        return true;
    }

    function validateCurrentStep() {
        return validateStep(steps[current]);
    }

    function validateAllSteps() {
        for (const step of steps) {
            if (!validateStep(step)) return false;
        }
        return true;
    }

    function initialStepIndex() {
        if (!hasServerErrors) return 0;
        const detailsIndex = steps.findIndex(s => s.dataset.stepId === 'details');
        return detailsIndex >= 0 ? detailsIndex : 0;
    }

    form.querySelectorAll('.wizard-next').forEach(btn => {
        btn.addEventListener('click', () => {
            if (!validateCurrentStep()) return;
            if (current < steps.length - 1) showStep(current + 1);
        });
    });
    form.querySelectorAll('.wizard-prev').forEach(btn => {
        btn.addEventListener('click', () => { if (current > 0) showStep(current - 1); });
    });
    form.querySelectorAll('input[name="guide_type"]').forEach(r => {
        r.addEventListener('change', toggleCompanyFields);
    });

    form.addEventListener('submit', function (e) {
        if (!validateAllSteps()) {
            e.preventDefault();
        }
    });

    showStep(initialStepIndex());
});
</script>
@endsection
