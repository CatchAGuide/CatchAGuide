<div class="modal fade" id="guideApplicationModal" tabindex="-1" aria-labelledby="guideApplicationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content cag-auth-modal">
            <div class="cag-auth-modal__accent" aria-hidden="true"></div>
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="cag-auth-modal__intro">
                    <p class="cag-auth-modal__eyebrow">@lang('homepage.header-become-guide')</p>
                    <h3 id="guideApplicationModalLabel" class="cag-auth-modal__title">@lang('homepage.header-become-guide')</h3>
                    <p class="cag-auth-modal__sub">
                        {{ __('profile.createAccountAndApply') }}
                    </p>
                </div>

                <nav class="guide-modal-progress mb-4" aria-label="@lang('profile.onboarding_step_details')">
                    <ol class="guide-modal-progress__list">
                        <li class="guide-modal-progress__item" data-progress-step="0">
                            <span class="guide-modal-progress__badge">1</span>
                            <span class="guide-modal-progress__label">@lang('profile.onboarding_step_account')</span>
                        </li>
                        <li class="guide-modal-progress__item" data-progress-step="1">
                            <span class="guide-modal-progress__badge">2</span>
                            <span class="guide-modal-progress__label">@lang('profile.onboarding_step_type')</span>
                        </li>
                        <li class="guide-modal-progress__item" data-progress-step="2">
                            <span class="guide-modal-progress__badge">3</span>
                            <span class="guide-modal-progress__label">@lang('profile.onboarding_step_details')</span>
                        </li>
                        <li class="guide-modal-progress__item" data-progress-step="3">
                            <span class="guide-modal-progress__badge">4</span>
                            <span class="guide-modal-progress__label">@lang('profile.onboarding_step_legal')</span>
                        </li>
                    </ol>
                </nav>

                <form method="POST" action="{{ route('guide.onboarding.store') }}" id="guideApplicationForm" class="guide-modal-form">
                    @csrf
                    <input type="hidden" name="is_fast_lane" value="1">

                    <div class="guide-modal-step" data-step-id="account">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label small fw-semibold" for="guideAppFirstname">
                                    {{ __('checkout.forename') }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="firstname" id="guideAppFirstname"
                                    class="form-control"
                                    placeholder="{{ __('checkout.forename') }}"
                                    required
                                    autocomplete="given-name"
                                    data-lpignore="true"
                                    data-form-type="other"
                                    data-1p-ignore="true">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small fw-semibold" for="guideAppLastname">
                                    {{ __('checkout.surname') }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="lastname" id="guideAppLastname"
                                    class="form-control"
                                    placeholder="{{ __('checkout.surname') }}"
                                    required
                                    autocomplete="family-name"
                                    data-lpignore="true"
                                    data-form-type="other"
                                    data-1p-ignore="true">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold" for="guideAppEmail">
                                    {{ __('global.Email') }} <span class="text-danger">*</span>
                                </label>
                                <input type="email" name="email" id="guideAppEmail"
                                    class="form-control"
                                    placeholder="{{ __('global.Email') }}"
                                    required
                                    autocomplete="email">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small fw-semibold" for="guideAppPassword">
                                    {{ __('global.Password') }} <span class="text-danger">*</span>
                                </label>
                                <input type="password" name="password" id="guideAppPassword"
                                    class="form-control"
                                    placeholder="{{ __('global.Password') }}"
                                    required
                                    autocomplete="new-password">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small fw-semibold" for="guideAppPasswordConfirm">
                                    {{ __('global.Confirm Password') }} <span class="text-danger">*</span>
                                </label>
                                <input type="password" name="password_confirmation" id="guideAppPasswordConfirm"
                                    class="form-control"
                                    placeholder="{{ __('global.Confirm Password') }}"
                                    required
                                    autocomplete="new-password">
                            </div>
                            <div class="col-12">
                                <div class="guide-modal-consent row g-3 align-items-center">
                                    <div class="col-sm-6 guide-modal-consent__text">
                                        <p class="small text-muted mb-0">
                                            {{ __('profile.createAccountConsent') }}
                                        </p>
                                    </div>
                                    <div class="col-sm-6 guide-modal-consent__checks">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="terms" value="1" id="guideAppTerms" required>
                                            <label class="form-check-label small" for="guideAppTerms">
                                                <a href="{{ route('law.agb') }}" target="_blank">{{ __('message.conditions') }}</a>
                                                <span class="text-danger">*</span>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="privacy" value="1" id="guideAppPrivacy" required>
                                            <label class="form-check-label small" for="guideAppPrivacy">
                                                <a href="{{ route('law.data-protection') }}" target="_blank">{{ __('checkout.privacy_policy') }}</a>
                                                <span class="text-danger">*</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="guide-modal-step" data-step-id="type" style="display:none">
                        <p class="text-muted small mb-3">{{ __('profile.howDoYouOffer') }}</p>
                        <div class="guide-modal-type-cards">
                            <label class="guide-modal-type-card">
                                <input type="radio" name="guide_type" value="private" class="guide-modal-type-card__input" checked required>
                                <span class="guide-modal-type-card__body">
                                    <i class="fas fa-user"></i>
                                    <strong>{{ __('profile.privatePerson') }}</strong>
                                    <small>{{ __('profile.offerAsIndividual') }}</small>
                                </span>
                            </label>
                            @if(config('guide_onboarding.company_onboarding_enabled'))
                            <label class="guide-modal-type-card">
                                <input type="radio" name="guide_type" value="company" class="guide-modal-type-card__input">
                                <span class="guide-modal-type-card__body">
                                    <i class="fas fa-building"></i>
                                    <strong>{{ __('profile.company') }}</strong>
                                    <small>{{ __('profile.representCompany') }}</small>
                                </span>
                            </label>
                            @endif
                        </div>
                    </div>

                    <div class="guide-modal-step" data-step-id="details" style="display:none">
                        <p class="text-muted small mb-3">{{ __('profile.currentAddressVerification') }}</p>
                        <div class="row g-3">
                            <div class="col-sm-6 guide-modal-company-only" style="display:none">
                                <label class="form-label small fw-semibold">{{ __('profile.companyName') }} <span class="text-danger">*</span></label>
                                <input type="text" name="information[company_name]" class="form-control" autocomplete="organization">
                            </div>
                            <div class="col-sm-6 guide-modal-company-only" style="display:none">
                                <label class="form-label small fw-semibold">{{ __('profile.legalForm') }} <span class="text-danger">*</span></label>
                                <select name="information[legal_form]" class="form-select">
                                    <option value="">—</option>
                                    @foreach(['GmbH','UG','GbR','Einzelunternehmen','e.K.','AG','sonstige'] as $form)
                                        <option value="{{ $form }}">{{ $form }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-6 guide-modal-private-only">
                                <label class="form-label small fw-semibold">{{ __('profile.bday') }}</label>
                                <input type="date" max="{{ now()->format('Y-m-d') }}" name="information[birthday]" class="form-control" autocomplete="bday">
                            </div>
                            <div class="col-8 col-sm-8">
                                <label class="form-label small fw-semibold">{{ __('forms.street') }} <span class="text-danger">*</span></label>
                                <input type="text" name="information[address]" class="form-control" required autocomplete="address-line1">
                            </div>
                            <div class="col-4 col-sm-4">
                                <label class="form-label small fw-semibold">{{ __('profile.no.') }} <span class="text-danger">*</span></label>
                                <input type="text" name="information[address_number]" class="form-control" required>
                            </div>
                            <div class="col-4 col-sm-4">
                                <label class="form-label small fw-semibold">{{ __('profile.zip') }} <span class="text-danger">*</span></label>
                                <input type="text" name="information[postal]" class="form-control" required autocomplete="postal-code">
                            </div>
                            <div class="col-8 col-sm-8">
                                <label class="form-label small fw-semibold">{{ __('checkout.city') }} <span class="text-danger">*</span></label>
                                <input type="text" name="information[city]" class="form-control" required autocomplete="address-level2">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small fw-semibold">{{ __('global.Country') }}</label>
                                <input type="text" name="information[country]" class="form-control" value="DE" maxlength="3" placeholder="DE" autocomplete="country">
                                <small class="form-text text-muted">{{ __('profile.onboarding_country_hint') }}</small>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small fw-semibold">{{ __('search-request.phone') }} <span class="text-danger">*</span></label>
                                <input type="tel" name="information[phone]" class="form-control" required autocomplete="tel" inputmode="tel">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">{{ __('profile.taxIdNum') }}</label>
                                <input type="text" name="information[taxId]" class="form-control">
                                <small class="form-text text-muted">
                                    {{ __('profile.taxIdNumHint') }}
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="guide-modal-step" data-step-id="legal" style="display:none">
                        <p class="text-muted small mb-3">{{ __('profile.fishingLicenseCompliance') }}</p>
                        <div class="guide-modal-legal">
                            <div class="form-check guide-modal-legal-check mb-3">
                                <input class="form-check-input" type="checkbox" name="lawcard" value="1" id="guideAppLawcard" required>
                                <label class="form-check-label" for="guideAppLawcard">
                                    <strong class="small">{{ __('profile.fishingLicense') }}</strong>
                                    <small class="d-block text-muted mt-1">
                                        {{ __('profile.confirmLicense') }}
                                    </small>
                                </label>
                            </div>
                            <div class="form-check guide-modal-legal-check mb-3">
                                <input class="form-check-input" type="checkbox" name="lawcard_nature" value="1" id="guideAppLawcardNature" required>
                                <label class="form-check-label" for="guideAppLawcardNature">
                                    <strong class="small">{{ __('profile.natureProtectionLaws') }}</strong>
                                    <small class="d-block text-muted mt-1">@lang('profile.lawcard_nature_text')</small>
                                </label>
                            </div>
                            <div class="form-check guide-modal-legal-check">
                                <input class="form-check-input" type="checkbox" name="lawcard_truthful" value="1" id="guideAppLawcardTruthful" required>
                                <label class="form-check-label" for="guideAppLawcardTruthful">
                                    <strong class="small">{{ __('profile.truthfulDataConfirmation') }}</strong>
                                    <small class="d-block text-muted mt-1">@lang('profile.lawcard_truthful_text')</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="guide-modal-nav mt-4">
                        <button type="button" class="btn btn-outline-secondary guide-modal-prev" disabled>
                            <i class="fas fa-arrow-left me-2"></i>{{ __('accommodations.previous') }}
                        </button>
                        <button type="button" class="btn theme-primary guide-modal-next">
                            {{ __('accommodations.next') }}<i class="fas fa-arrow-right ms-2"></i>
                        </button>
                        <button type="submit" class="btn theme-primary guide-modal-submit" style="display:none">
                            <span class="normal-state">
                                <i class="fas fa-paper-plane me-2"></i>{{ __('profile.submitApplication') }}
                            </span>
                            <span class="loading-state d-none">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                {{ __('checkout.loading') }}
                            </span>
                        </button>
                    </div>
                </form>

                <div class="text-center mt-3">
                    <p class="small mb-0">
                        {{ __('profile.alreadyHaveAccount') }}
                        <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">
                            @lang('homepage.header-login')
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('guideApplicationModal');
    if (!modalEl) return;

    const form = document.getElementById('guideApplicationForm');
    const steps = Array.from(modalEl.querySelectorAll('.guide-modal-step'));
    const progressItems = Array.from(modalEl.querySelectorAll('.guide-modal-progress__item'));
    const prevBtn = modalEl.querySelector('.guide-modal-prev');
    const nextBtn = modalEl.querySelector('.guide-modal-next');
    const submitBtn = modalEl.querySelector('.guide-modal-submit');
    let currentStep = 0;

    function updateProgress() {
        progressItems.forEach((item, i) => {
            item.classList.toggle('is-active', i === currentStep);
            item.classList.toggle('is-complete', i < currentStep);
        });
    }

    function toggleCompanyFields() {
        const checked = modalEl.querySelector('input[name="guide_type"]:checked');
        const isCompany = checked && checked.value === 'company';
        modalEl.querySelectorAll('.guide-modal-company-only').forEach(el => {
            el.style.display = isCompany ? '' : 'none';
            el.querySelectorAll('input, select').forEach(inp => {
                inp.required = !!isCompany;
                if (!isCompany) inp.value = '';
            });
        });
        modalEl.querySelectorAll('.guide-modal-private-only').forEach(el => {
            el.style.display = isCompany ? 'none' : '';
        });
    }

    function showStep(index) {
        if (index < 0 || index >= steps.length) return;
        steps.forEach((s, i) => { s.style.display = i === index ? 'block' : 'none'; });
        currentStep = index;
        updateProgress();
        toggleCompanyFields();

        if (prevBtn) prevBtn.disabled = currentStep === 0;

        const isLast = currentStep === steps.length - 1;
        if (nextBtn) nextBtn.style.display = isLast ? 'none' : 'inline-flex';
        if (submitBtn) submitBtn.style.display = isLast ? 'inline-flex' : 'none';

        const modalBody = modalEl.querySelector('.modal-body');
        if (modalBody) modalBody.scrollTop = 0;
    }

    function validateCurrentStep() {
        const step = steps[currentStep];
        if (!step) return true;
        const fields = step.querySelectorAll('input, select, textarea');
        for (const field of fields) {
            if (field.offsetParent === null || field.disabled || field.type === 'hidden') continue;
            if (!field.checkValidity()) {
                field.reportValidity();
                return false;
            }
        }
        return true;
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function () {
            if (!validateCurrentStep()) return;
            if (currentStep < steps.length - 1) showStep(currentStep + 1);
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', function () {
            if (currentStep > 0) showStep(currentStep - 1);
        });
    }

    modalEl.querySelectorAll('input[name="guide_type"]').forEach(r => {
        r.addEventListener('change', toggleCompanyFields);
    });

    function clearErrors() {
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
        form.querySelectorAll('.alert').forEach(el => el.remove());
    }

    function showFieldErrors(errors) {
        let firstErrorStepIndex = null;
        const generalMessages = [];

        Object.keys(errors).forEach(field => {
            let selector = `[name="${field}"]`;
            if (field.indexOf('.') !== -1) {
                const parts = field.split('.');
                selector = `[name="${parts[0]}[${parts.slice(1).join('][')}]"]`;
            }

            const input = form.querySelector(selector);
            if (input) {
                input.classList.add('is-invalid');
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback d-block small';
                feedback.textContent = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
                const wrapper = input.closest('.cag-password-toggle');
                if (wrapper && wrapper.parentNode) {
                    wrapper.parentNode.insertBefore(feedback, wrapper.nextSibling);
                } else {
                    input.parentNode.appendChild(feedback);
                }

                const stepEl = input.closest('.guide-modal-step');
                if (stepEl) {
                    const idx = steps.indexOf(stepEl);
                    if (idx !== -1 && (firstErrorStepIndex === null || idx < firstErrorStepIndex)) {
                        firstErrorStepIndex = idx;
                    }
                }
            } else {
                generalMessages.push(Array.isArray(errors[field]) ? errors[field][0] : errors[field]);
            }
        });

        if (generalMessages.length) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger mb-3';
            errorDiv.innerHTML = generalMessages.join('<br>');
            form.insertBefore(errorDiv, form.firstChild);
        }

        if (firstErrorStepIndex !== null && firstErrorStepIndex !== currentStep) {
            showStep(firstErrorStepIndex);
        }
    }

    form.querySelectorAll('input, select, textarea').forEach(el => {
        el.addEventListener('input', function () {
            this.classList.remove('is-invalid');
            const fb = this.parentNode.querySelector('.invalid-feedback');
            if (fb) fb.remove();
        });
        el.addEventListener('change', function () {
            this.classList.remove('is-invalid');
            const fb = this.parentNode.querySelector('.invalid-feedback');
            if (fb) fb.remove();
        });
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearErrors();

        if (!validateCurrentStep()) return;

        const normalState = submitBtn.querySelector('.normal-state');
        const loadingState = submitBtn.querySelector('.loading-state');
        submitBtn.disabled = true;
        if (normalState) normalState.classList.add('d-none');
        if (loadingState) loadingState.classList.remove('d-none');

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (response.status === 419) {
                window.location.reload();
                return null;
            }
            return response.json().then(data => ({ status: response.status, data }));
        })
        .then(result => {
            if (!result) return;
            const { status, data } = result;

            if (data && data.success) {
                const successDiv = document.createElement('div');
                successDiv.className = 'alert alert-success mb-3';
                successDiv.textContent = data.message || 'Application submitted successfully!';
                form.insertBefore(successDiv, form.firstChild);

                if (data.redirect) {
                    setTimeout(() => { window.location.href = data.redirect; }, 800);
                } else {
                    setTimeout(() => window.location.reload(), 800);
                }
                return;
            }

            submitBtn.disabled = false;
            if (normalState) normalState.classList.remove('d-none');
            if (loadingState) loadingState.classList.add('d-none');

            if (data && data.errors) {
                showFieldErrors(data.errors);
            } else {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger mb-3';
                errorDiv.textContent = (data && data.message) || 'An error occurred. Please try again.';
                form.insertBefore(errorDiv, form.firstChild);
            }
        })
        .catch(() => {
            submitBtn.disabled = false;
            if (normalState) normalState.classList.remove('d-none');
            if (loadingState) loadingState.classList.add('d-none');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger mb-3';
            errorDiv.textContent = 'An error occurred. Please try again.';
            form.insertBefore(errorDiv, form.firstChild);
        });
    });

    modalEl.addEventListener('shown.bs.modal', function () {
        showStep(0);
    });

    showStep(0);
});
</script>
