<div class="modal fade" id="guideApplicationModal" tabindex="-1" aria-labelledby="guideApplicationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 py-2">
                <div class="text-center mb-4">
                    <h3 class="fw-bold">@lang('homepage.header-become-guide')</h3>
                    <p class="text-muted mb-0 small">
                        {{ translate('Create your Catch A Guide account and submit your guide application in one step.') }}
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
                                    {{ translate('Vorname') }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="firstname" id="guideAppFirstname"
                                    class="form-control"
                                    placeholder="{{ translate('Vorname') }}"
                                    required
                                    autocomplete="given-name"
                                    data-lpignore="true"
                                    data-form-type="other"
                                    data-1p-ignore="true">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small fw-semibold" for="guideAppLastname">
                                    {{ translate('Nachname') }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="lastname" id="guideAppLastname"
                                    class="form-control"
                                    placeholder="{{ translate('Nachname') }}"
                                    required
                                    autocomplete="family-name"
                                    data-lpignore="true"
                                    data-form-type="other"
                                    data-1p-ignore="true">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold" for="guideAppEmail">
                                    {{ translate('Email') }} <span class="text-danger">*</span>
                                </label>
                                <input type="email" name="email" id="guideAppEmail"
                                    class="form-control"
                                    placeholder="{{ translate('Email') }}"
                                    required
                                    autocomplete="email">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small fw-semibold" for="guideAppPassword">
                                    {{ translate('Password') }} <span class="text-danger">*</span>
                                </label>
                                <input type="password" name="password" id="guideAppPassword"
                                    class="form-control"
                                    placeholder="{{ translate('Password') }}"
                                    required
                                    autocomplete="new-password">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small fw-semibold" for="guideAppPasswordConfirm">
                                    {{ translate('Confirm Password') }} <span class="text-danger">*</span>
                                </label>
                                <input type="password" name="password_confirmation" id="guideAppPasswordConfirm"
                                    class="form-control"
                                    placeholder="{{ translate('Confirm Password') }}"
                                    required
                                    autocomplete="new-password">
                            </div>
                            <div class="col-12">
                                <div class="guide-modal-consent row g-3 align-items-center">
                                    <div class="col-sm-6 guide-modal-consent__text">
                                        <p class="small text-muted mb-0">
                                            {{ translate('By creating an account you confirm that you have read and agree to our terms and privacy policy.') }}
                                        </p>
                                    </div>
                                    <div class="col-sm-6 guide-modal-consent__checks">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="terms" value="1" id="guideAppTerms" required>
                                            <label class="form-check-label small" for="guideAppTerms">
                                                <a href="{{ route('law.agb') }}" target="_blank">{{ translate('AGB') }}</a>
                                                <span class="text-danger">*</span>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="privacy" value="1" id="guideAppPrivacy" required>
                                            <label class="form-check-label small" for="guideAppPrivacy">
                                                <a href="{{ route('law.data-protection') }}" target="_blank">{{ translate('Privacy Policy') }}</a>
                                                <span class="text-danger">*</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="guide-modal-step" data-step-id="type" style="display:none">
                        <p class="text-muted small mb-3">{{ translate('How do you offer your guidings?') }}</p>
                        <div class="guide-modal-type-cards">
                            <label class="guide-modal-type-card">
                                <input type="radio" name="guide_type" value="private" class="guide-modal-type-card__input" checked required>
                                <span class="guide-modal-type-card__body">
                                    <i class="fas fa-user"></i>
                                    <strong>{{ translate('Private Person') }}</strong>
                                    <small>{{ translate('You offer guidings as an individual') }}</small>
                                </span>
                            </label>
                            @if(config('guide_onboarding.company_onboarding_enabled'))
                            <label class="guide-modal-type-card">
                                <input type="radio" name="guide_type" value="company" class="guide-modal-type-card__input">
                                <span class="guide-modal-type-card__body">
                                    <i class="fas fa-building"></i>
                                    <strong>{{ translate('Company') }}</strong>
                                    <small>{{ translate('You represent a company or business') }}</small>
                                </span>
                            </label>
                            @endif
                        </div>
                    </div>

                    <div class="guide-modal-step" data-step-id="details" style="display:none">
                        <p class="text-muted small mb-3">{{ translate('Your current address for verification') }}</p>
                        <div class="row g-3">
                            <div class="col-sm-6 guide-modal-company-only" style="display:none">
                                <label class="form-label small fw-semibold">{{ translate('Company Name') }} <span class="text-danger">*</span></label>
                                <input type="text" name="information[company_name]" class="form-control" autocomplete="organization">
                            </div>
                            <div class="col-sm-6 guide-modal-company-only" style="display:none">
                                <label class="form-label small fw-semibold">{{ translate('Legal Form') }} <span class="text-danger">*</span></label>
                                <select name="information[legal_form]" class="form-select">
                                    <option value="">—</option>
                                    @foreach(['GmbH','UG','GbR','Einzelunternehmen','e.K.','AG','sonstige'] as $form)
                                        <option value="{{ $form }}">{{ $form }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-6 guide-modal-private-only">
                                <label class="form-label small fw-semibold">{{ translate('Geburtstag') }}</label>
                                <input type="date" max="{{ now()->format('Y-m-d') }}" name="information[birthday]" class="form-control" autocomplete="bday">
                            </div>
                            <div class="col-8 col-sm-8">
                                <label class="form-label small fw-semibold">{{ translate('Straße') }} <span class="text-danger">*</span></label>
                                <input type="text" name="information[address]" class="form-control" required autocomplete="address-line1">
                            </div>
                            <div class="col-4 col-sm-4">
                                <label class="form-label small fw-semibold">{{ translate('Nr.') }} <span class="text-danger">*</span></label>
                                <input type="text" name="information[address_number]" class="form-control" required>
                            </div>
                            <div class="col-4 col-sm-4">
                                <label class="form-label small fw-semibold">{{ translate('PLZ') }} <span class="text-danger">*</span></label>
                                <input type="text" name="information[postal]" class="form-control" required autocomplete="postal-code">
                            </div>
                            <div class="col-8 col-sm-8">
                                <label class="form-label small fw-semibold">{{ translate('Stadt') }} <span class="text-danger">*</span></label>
                                <input type="text" name="information[city]" class="form-control" required autocomplete="address-level2">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small fw-semibold">{{ translate('Country') }}</label>
                                <input type="text" name="information[country]" class="form-control" value="DE" autocomplete="country">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small fw-semibold">{{ translate('Telefonnummer') }} <span class="text-danger">*</span></label>
                                <input type="tel" name="information[phone]" class="form-control" required autocomplete="tel" inputmode="tel">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">{{ translate('Umsatzsteuer-Identifikationsnummer') }}</label>
                                <input type="text" name="information[taxId]" class="form-control">
                                <small class="form-text text-muted">
                                    {{ translate('*Falls eine Umsatzsteuer-Identifikationsnummer vorhanden ist, gib diese hier an.') }}
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="guide-modal-step" data-step-id="legal" style="display:none">
                        <p class="text-muted small mb-3">{{ translate('Fishing license and legal compliance') }}</p>
                        <div class="guide-modal-legal">
                            <div class="form-check guide-modal-legal-check mb-3">
                                <input class="form-check-input" type="checkbox" name="lawcard" value="1" id="guideAppLawcard" required>
                                <label class="form-check-label" for="guideAppLawcard">
                                    <strong class="small">{{ translate('Fischereierlaubnis') }}</strong>
                                    <small class="d-block text-muted mt-1">
                                        {{ translate('Hiermit bestätige ich, dass ich über die für meine Guidings notwendige Angelerlaubnis verfüge.') }}
                                    </small>
                                </label>
                            </div>
                            <div class="form-check guide-modal-legal-check mb-3">
                                <input class="form-check-input" type="checkbox" name="lawcard_nature" value="1" id="guideAppLawcardNature" required>
                                <label class="form-check-label" for="guideAppLawcardNature">
                                    <strong class="small">{{ translate('Nature protection laws') }}</strong>
                                    <small class="d-block text-muted mt-1">@lang('profile.lawcard_nature_text')</small>
                                </label>
                            </div>
                            <div class="form-check guide-modal-legal-check">
                                <input class="form-check-input" type="checkbox" name="lawcard_truthful" value="1" id="guideAppLawcardTruthful" required>
                                <label class="form-check-label" for="guideAppLawcardTruthful">
                                    <strong class="small">{{ translate('Truthful data confirmation') }}</strong>
                                    <small class="d-block text-muted mt-1">@lang('profile.lawcard_truthful_text')</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="guide-modal-nav mt-4">
                        <button type="button" class="btn btn-outline-secondary guide-modal-prev" disabled>
                            <i class="fas fa-arrow-left me-2"></i>{{ translate('Zurück') }}
                        </button>
                        <button type="button" class="btn theme-primary guide-modal-next">
                            {{ translate('Weiter') }}<i class="fas fa-arrow-right ms-2"></i>
                        </button>
                        <button type="submit" class="btn theme-primary guide-modal-submit" style="display:none">
                            <span class="normal-state">
                                <i class="fas fa-paper-plane me-2"></i>{{ translate('Submit Application') }}
                            </span>
                            <span class="loading-state d-none">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                {{ translate('Loading...') }}
                            </span>
                        </button>
                    </div>
                </form>

                <div class="text-center mt-3">
                    <p class="small mb-0">
                        {{ translate('Already have an account? ') }}
                        <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">
                            @lang('homepage.header-login')
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
#guideApplicationModal {
    z-index: 1060 !important;
}

#guideApplicationModal .modal-backdrop {
    z-index: 1059 !important;
}

#guideApplicationModal .modal-content {
    border-radius: 8px;
    border: none;
}

#guideApplicationModal .form-control,
#guideApplicationModal .form-select {
    height: 44px;
    border: 1px solid #E8604C;
    border-radius: 4px;
}

#guideApplicationModal textarea.form-control {
    height: auto;
}

#guideApplicationModal .form-control:focus,
#guideApplicationModal .form-select:focus {
    box-shadow: none;
    border-color: #E8604C;
}

#guideApplicationModal .btn-close {
    opacity: 0.5;
}

#guideApplicationModal .btn-close:hover {
    opacity: 0.75;
}

#guideApplicationModal .theme-primary {
    background-color: #E8604C;
    color: white;
    height: 44px;
    font-weight: 500;
    padding: 0 20px;
}

#guideApplicationModal .theme-primary:hover {
    background-color: #313041;
    color: white;
}

#guideApplicationModal .theme-primary:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

#guideApplicationModal a {
    color: #E8604C;
}

#guideApplicationModal a:hover {
    color: #313041;
}

#guideApplicationModal .form-check-input:checked {
    background-color: #E8604C;
    border-color: #E8604C;
}

#guideApplicationModal .form-check-input:focus {
    border-color: #E8604C;
    box-shadow: 0 0 0 0.25rem rgba(232, 96, 76, 0.25);
}

#guideApplicationModal .guide-modal-progress__list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
    list-style: none;
    padding: 0;
    margin: 0;
}

#guideApplicationModal .guide-modal-progress__item {
    flex: 1 1 120px;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.45rem 0.6rem;
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    opacity: 0.55;
    transition: all 0.25s ease;
}

#guideApplicationModal .guide-modal-progress__item.is-active,
#guideApplicationModal .guide-modal-progress__item.is-complete {
    opacity: 1;
    border-color: #E8604C;
    background: #fff;
}

#guideApplicationModal .guide-modal-progress__item.is-active {
    box-shadow: 0 4px 12px rgba(232, 96, 76, 0.18);
}

#guideApplicationModal .guide-modal-progress__badge {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: #E8604C;
    color: #fff;
    font-size: 0.7rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

#guideApplicationModal .guide-modal-progress__label {
    font-size: 0.72rem;
    font-weight: 600;
    color: #313041;
    line-height: 1.15;
}

#guideApplicationModal .guide-modal-type-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 0.75rem;
}

#guideApplicationModal .guide-modal-type-card {
    margin: 0;
    cursor: pointer;
}

#guideApplicationModal .guide-modal-type-card__input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

#guideApplicationModal .guide-modal-type-card__body {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    padding: 1rem;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    background: #f8f9fa;
    height: 100%;
    transition: all 0.2s ease;
}

#guideApplicationModal .guide-modal-type-card__body i {
    font-size: 1.25rem;
    color: #E8604C;
}

#guideApplicationModal .guide-modal-type-card__body strong {
    font-size: 0.9rem;
    color: #313041;
}

#guideApplicationModal .guide-modal-type-card__body small {
    font-size: 0.75rem;
    color: #6c757d;
}

#guideApplicationModal .guide-modal-type-card__input:checked + .guide-modal-type-card__body {
    border-color: #E8604C;
    background: rgba(232, 96, 76, 0.06);
    box-shadow: 0 4px 12px rgba(232, 96, 76, 0.12);
}

#guideApplicationModal .guide-modal-legal-check {
    padding: 0.75rem 0.75rem 0.75rem 2.25rem;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
}

#guideApplicationModal .guide-modal-consent {
    margin: 0;
    padding: 0.85rem 0.5rem;
    background: #fafbfc;
    border: 1px solid #eef0f2;
    border-radius: 8px;
}

#guideApplicationModal .guide-modal-consent__text {
    text-align: right;
    border-right: 1px solid #eef0f2;
    padding-right: 1rem !important;
}

#guideApplicationModal .guide-modal-consent__text p {
    line-height: 1.45;
}

#guideApplicationModal .guide-modal-consent__checks {
    text-align: left;
    padding-left: 1rem !important;
}

#guideApplicationModal .guide-modal-consent__checks .form-check {
    display: flex;
    align-items: center;
    padding: 0;
    margin: 0 0 0.4rem 0;
    min-height: 0;
}

#guideApplicationModal .guide-modal-consent__checks .form-check:last-child {
    margin-bottom: 0;
}

#guideApplicationModal .guide-modal-consent__checks .form-check-input {
    width: 1rem;
    height: 1rem;
    margin: 0 0.5rem 0 0;
    float: none;
    flex-shrink: 0;
    cursor: pointer;
}

#guideApplicationModal .guide-modal-consent__checks .form-check-label {
    margin: 0;
    line-height: 1.25;
    cursor: pointer;
}

#guideApplicationModal .guide-modal-consent__checks .form-check-label a {
    text-decoration: none;
    font-weight: 500;
}

#guideApplicationModal .guide-modal-consent__checks .form-check-label a:hover {
    text-decoration: underline;
}

#guideApplicationModal .form-label {
    margin-bottom: 0.25rem;
    color: #313041;
}

#guideApplicationModal .guide-modal-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.75rem;
    border-top: 1px solid #f0f0f0;
    padding-top: 1rem;
}

#guideApplicationModal .guide-modal-nav .btn {
    white-space: nowrap;
    min-width: 140px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

#guideApplicationModal .guide-modal-prev {
    border: 1px solid #d0d3d6;
    color: #6c757d;
    background: #fff;
    height: 44px;
}

#guideApplicationModal .guide-modal-prev:not(:disabled):hover {
    border-color: #313041;
    color: #313041;
    background: #f8f9fa;
}

#guideApplicationModal .guide-modal-prev:disabled {
    opacity: 0.45;
    cursor: not-allowed;
}

#guideApplicationModal .guide-modal-next,
#guideApplicationModal .guide-modal-submit {
    margin-left: auto;
}

#guideApplicationModal .modal-body {
    -webkit-overflow-scrolling: touch;
}

/* Tablet and below */
@media (max-width: 767.98px) {
    #guideApplicationModal .modal-dialog {
        margin: 0.5rem;
    }
    #guideApplicationModal .modal-body {
        padding-left: 1rem !important;
        padding-right: 1rem !important;
    }
    #guideApplicationModal h3 {
        font-size: 1.25rem;
    }
    #guideApplicationModal .form-control,
    #guideApplicationModal .form-select {
        height: 42px;
        font-size: 0.95rem;
    }
    #guideApplicationModal .theme-primary {
        height: 42px;
        font-size: 0.95rem;
        padding: 0 14px;
    }
    #guideApplicationModal .guide-modal-type-cards {
        grid-template-columns: 1fr;
    }
}

/* Phones */
@media (max-width: 575.98px) {
    #guideApplicationModal .modal-dialog {
        margin: 0;
        max-width: 100%;
        min-height: 100vh;
    }
    #guideApplicationModal .modal-content {
        min-height: 100vh;
        border-radius: 0;
    }
    #guideApplicationModal .modal-body {
        padding-left: 0.9rem !important;
        padding-right: 0.9rem !important;
    }
    #guideApplicationModal .guide-modal-progress__list {
        gap: 0.3rem;
        justify-content: space-between;
    }
    #guideApplicationModal .guide-modal-progress__label {
        display: none;
    }
    #guideApplicationModal .guide-modal-progress__item {
        flex: 1 1 0;
        justify-content: center;
        min-width: 0;
        padding: 0.4rem 0.35rem;
    }
    #guideApplicationModal .guide-modal-progress__item.is-active {
        flex: 2 1 0;
    }
    #guideApplicationModal .guide-modal-progress__item.is-active .guide-modal-progress__label {
        display: inline;
        font-size: 0.7rem;
    }
    #guideApplicationModal .guide-modal-legal-check {
        padding-left: 2rem;
    }
    #guideApplicationModal .guide-modal-consent__text {
        text-align: left;
        border-right: 0;
        border-bottom: 1px solid #eef0f2;
        padding-right: 0.75rem !important;
        padding-bottom: 0.65rem;
    }
    #guideApplicationModal .guide-modal-consent__checks {
        padding-left: 0.75rem !important;
        padding-top: 0.25rem;
    }
    #guideApplicationModal .guide-modal-nav {
        gap: 0.5rem;
    }
    #guideApplicationModal .guide-modal-nav .btn {
        flex: 1 1 0;
        min-width: 0;
        padding: 0 10px;
        font-size: 0.9rem;
    }
    #guideApplicationModal .guide-modal-next,
    #guideApplicationModal .guide-modal-submit {
        margin-left: 0;
    }
}

/* Extra small phones */
@media (max-width: 360px) {
    #guideApplicationModal .guide-modal-progress__badge {
        width: 20px;
        height: 20px;
        font-size: 0.65rem;
    }
}
</style>

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
                const parent = input.closest('.col-md-6, .col-md-4, .col-md-8, .col-12, .form-check, .guide-modal-step') || input.parentNode;
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback d-block small';
                feedback.textContent = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
                input.parentNode.appendChild(feedback);

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
