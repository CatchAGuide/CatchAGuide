{{-- Shared fields for the create-user modal (and any future reuse). --}}
<section class="admin-customer-form__section">
    <h2 class="admin-customer-form__section-title">{{ __('admin.customers.section_type') }}</h2>

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label d-block">{{ __('admin.customers.fields.account_type') }}</label>
            <div class="admin-choice-seg" role="radiogroup" aria-label="{{ __('admin.customers.fields.account_type') }}">
                <label class="admin-choice-seg__item">
                    <input type="radio" name="account_type" value="person" {{ old('account_type', 'person') === 'person' ? 'checked' : '' }}>
                    <span>{{ __('admin.customers.account_person') }}</span>
                </label>
                <label class="admin-choice-seg__item">
                    <input type="radio" name="account_type" value="company" {{ old('account_type') === 'company' ? 'checked' : '' }}>
                    <span>{{ __('admin.customers.account_company') }}</span>
                </label>
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label d-block">{{ __('admin.customers.fields.role') }}</label>
            <div class="admin-choice-seg" role="radiogroup" aria-label="{{ __('admin.customers.fields.role') }}">
                <label class="admin-choice-seg__item">
                    <input type="radio" name="role" value="provider" {{ old('role', 'provider') === 'provider' ? 'checked' : '' }}>
                    <span>{{ __('admin.customers.role_provider') }}</span>
                </label>
                <label class="admin-choice-seg__item">
                    <input type="radio" name="role" value="guide" {{ old('role') === 'guide' ? 'checked' : '' }}>
                    <span>{{ __('admin.customers.role_guide') }}</span>
                </label>
                <label class="admin-choice-seg__item">
                    <input type="radio" name="role" value="customer" {{ old('role') === 'customer' ? 'checked' : '' }}>
                    <span>{{ __('admin.customers.role_customer') }}</span>
                </label>
            </div>
            <div class="form-text mt-2">{{ __('admin.customers.role_hint') }}</div>
        </div>
    </div>
</section>

<section class="admin-customer-form__section">
    <h2 class="admin-customer-form__section-title">{{ __('admin.customers.section_identity') }}</h2>

    <div class="row" data-account-person>
        <div class="col-md-6">
            <div class="form-group">
                <label class="form-label" for="customer_firstname">{{ __('admin.customers.fields.firstname') }}<span class="admin-customer-form__req">*</span></label>
                <input type="text" class="form-control @error('firstname') is-invalid @enderror" id="customer_firstname" name="firstname"
                       value="{{ old('firstname') }}" placeholder="{{ __('admin.customers.fields.firstname') }}">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="form-label" for="customer_lastname">{{ __('admin.customers.fields.lastname') }}<span class="admin-customer-form__req">*</span></label>
                <input type="text" class="form-control @error('lastname') is-invalid @enderror" id="customer_lastname" name="lastname"
                       value="{{ old('lastname') }}" placeholder="{{ __('admin.customers.fields.lastname') }}">
            </div>
        </div>
    </div>

    <div class="row" data-account-company hidden>
        <div class="col-12">
            <div class="form-group mb-0">
                <label class="form-label" for="customer_company_name">{{ __('admin.customers.fields.company_name') }}<span class="admin-customer-form__req">*</span></label>
                <input type="text" class="form-control @error('company_name') is-invalid @enderror" id="customer_company_name" name="company_name"
                       value="{{ old('company_name') }}" placeholder="{{ __('admin.customers.fields.company_name') }}">
            </div>
        </div>
    </div>
</section>

<section class="admin-customer-form__section">
    <h2 class="admin-customer-form__section-title">{{ __('admin.customers.section_contact') }}</h2>

    <div class="row">
        <div class="col-md-7">
            <div class="form-group">
                <label class="form-label" for="customer_email">{{ __('admin.customers.fields.email') }} <span class="admin-customer-form__optional">({{ __('admin.customers.optional') }})</span></label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="customer_email" name="email"
                       value="{{ old('email') }}" placeholder="name@example.com">
            </div>
        </div>
        <div class="col-md-5">
            <div class="form-group">
                <label class="form-label" for="customer_language">{{ __('admin.customers.fields.language') }}<span class="admin-customer-form__req">*</span></label>
                <select class="form-control @error('language') is-invalid @enderror" id="customer_language" name="language" required>
                    <option value="de" {{ old('language', 'de') === 'de' ? 'selected' : '' }}>{{ __('admin.customers.language_de') }}</option>
                    <option value="en" {{ old('language') === 'en' ? 'selected' : '' }}>{{ __('admin.customers.language_en') }}</option>
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="form-group mb-md-0">
                <label class="form-label" for="customer_phone_country_code">{{ __('admin.customers.fields.phone_country_code') }}<span class="admin-customer-form__req">*</span></label>
                <input type="text" class="form-control @error('phone_country_code') is-invalid @enderror" id="customer_phone_country_code"
                       name="phone_country_code" value="{{ old('phone_country_code', '+49') }}" required>
            </div>
        </div>
        <div class="col-md-9">
            <div class="form-group mb-0">
                <label class="form-label" for="customer_phone">{{ __('admin.customers.fields.phone') }}<span class="admin-customer-form__req">*</span></label>
                <input type="text" class="form-control @error('information.phone') is-invalid @enderror" id="customer_phone"
                       name="information[phone]" value="{{ old('information.phone') }}"
                       placeholder="170 1234567" required>
            </div>
        </div>
    </div>
</section>

<section class="admin-customer-form__section">
    <h2 class="admin-customer-form__section-title">{{ __('admin.customers.section_address') }}</h2>

    <div class="row">
        <div class="col-md-9">
            <div class="form-group">
                <label class="form-label" for="customer_address">{{ __('admin.customers.fields.address') }}<span class="admin-customer-form__req">*</span></label>
                <input type="text" class="form-control @error('information.address') is-invalid @enderror" id="customer_address"
                       name="information[address]" value="{{ old('information.address') }}"
                       placeholder="{{ __('admin.customers.fields.address') }}" required>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label class="form-label" for="customer_address_number">{{ __('admin.customers.fields.address_number') }}<span class="admin-customer-form__req">*</span></label>
                <input type="text" class="form-control @error('information.address_number') is-invalid @enderror" id="customer_address_number"
                       name="information[address_number]" value="{{ old('information.address_number') }}"
                       placeholder="12" required>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="form-group mb-md-0">
                <label class="form-label" for="customer_postal">{{ __('admin.customers.fields.postal') }}<span class="admin-customer-form__req">*</span></label>
                <input type="text" class="form-control @error('information.postal') is-invalid @enderror" id="customer_postal"
                       name="information[postal]" value="{{ old('information.postal') }}"
                       placeholder="40213" required>
            </div>
        </div>
        <div class="col-md-5">
            <div class="form-group mb-md-0">
                <label class="form-label" for="customer_city">{{ __('admin.customers.fields.city') }}<span class="admin-customer-form__req">*</span></label>
                <input type="text" class="form-control @error('information.city') is-invalid @enderror" id="customer_city"
                       name="information[city]" value="{{ old('information.city') }}"
                       placeholder="{{ __('admin.customers.fields.city') }}" required>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group mb-0">
                <label class="form-label" for="customer_country">{{ __('admin.customers.fields.country') }} <span class="admin-customer-form__optional">({{ __('admin.customers.optional') }})</span></label>
                <input type="text" class="form-control @error('information.country') is-invalid @enderror" id="customer_country"
                       name="information[country]" value="{{ old('information.country', 'Deutschland') }}"
                       placeholder="{{ __('admin.customers.fields.country') }}">
            </div>
        </div>
    </div>
</section>

<p class="admin-customer-form__footnote">{{ __('admin.customers.create_hint') }}</p>
