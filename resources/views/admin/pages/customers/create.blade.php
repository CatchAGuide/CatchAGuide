@extends('admin.layouts.app')

@section('title', __('admin.customers.create_title'))

@section('content')
    <div class="side-app">
        <div class="main-container container-fluid">
            <div class="page-header">
                <h1 class="page-title">@yield('title')</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.customers.index') }}">{{ __('admin.customers.breadcrumb') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">@yield('title')</h3>
                        </div>
                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form action="{{ route('admin.customers.store') }}" method="post">
                                @csrf

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label d-block">{{ __('admin.customers.fields.account_type') }}</label>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="account_type" id="account_type_person" value="person"
                                                   {{ old('account_type', 'person') === 'person' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="account_type_person">{{ __('admin.customers.account_person') }}</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="account_type" id="account_type_company" value="company"
                                                   {{ old('account_type') === 'company' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="account_type_company">{{ __('admin.customers.account_company') }}</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label d-block">{{ __('admin.customers.fields.role') }}</label>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="role" id="role_provider" value="provider"
                                                   {{ old('role', 'provider') === 'provider' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="role_provider">{{ __('admin.customers.role_provider') }}</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="role" id="role_guide" value="guide"
                                                   {{ old('role') === 'guide' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="role_guide">{{ __('admin.customers.role_guide') }}</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="role" id="role_customer" value="customer"
                                                   {{ old('role') === 'customer' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="role_customer">{{ __('admin.customers.role_customer') }}</label>
                                        </div>
                                        <div class="form-text">{{ __('admin.customers.role_hint') }}</div>
                                    </div>
                                </div>

                                <div class="row" data-account-person>
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="firstname">{{ __('admin.customers.fields.firstname') }}<span style="color: #e8604c">*</span></label>
                                            <input type="text" class="form-control @error('firstname') is-invalid @enderror" id="firstname" name="firstname"
                                                   value="{{ old('firstname') }}" placeholder="{{ __('admin.customers.fields.firstname') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="lastname">{{ __('admin.customers.fields.lastname') }}<span style="color: #e8604c">*</span></label>
                                            <input type="text" class="form-control @error('lastname') is-invalid @enderror" id="lastname" name="lastname"
                                                   value="{{ old('lastname') }}" placeholder="{{ __('admin.customers.fields.lastname') }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row" data-account-company style="display: none;">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="company_name">{{ __('admin.customers.fields.company_name') }}<span style="color: #e8604c">*</span></label>
                                            <input type="text" class="form-control @error('company_name') is-invalid @enderror" id="company_name" name="company_name"
                                                   value="{{ old('company_name') }}" placeholder="{{ __('admin.customers.fields.company_name') }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="email">{{ __('admin.customers.fields.email') }} <span class="text-muted">({{ __('admin.customers.optional') }})</span></label>
                                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
                                                   value="{{ old('email') }}" placeholder="{{ __('admin.customers.fields.email') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="language">{{ __('admin.customers.fields.language') }}<span style="color: #e8604c">*</span></label>
                                            <select class="form-control @error('language') is-invalid @enderror" id="language" name="language" required>
                                                <option value="de" {{ old('language', 'de') === 'de' ? 'selected' : '' }}>{{ __('admin.customers.language_de') }}</option>
                                                <option value="en" {{ old('language') === 'en' ? 'selected' : '' }}>{{ __('admin.customers.language_en') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="phone_country_code">{{ __('admin.customers.fields.phone_country_code') }}<span style="color: #e8604c">*</span></label>
                                            <input type="text" class="form-control @error('phone_country_code') is-invalid @enderror" id="phone_country_code"
                                                   name="phone_country_code" value="{{ old('phone_country_code', '+49') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="form-group">
                                            <label for="phone">{{ __('admin.customers.fields.phone') }}<span style="color: #e8604c">*</span></label>
                                            <input type="text" class="form-control @error('information.phone') is-invalid @enderror" id="phone"
                                                   name="information[phone]" value="{{ old('information.phone') }}"
                                                   placeholder="{{ __('admin.customers.fields.phone') }}" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-8">
                                        <label for="address">{{ __('admin.customers.fields.address') }}<span style="color: #e8604c">*</span></label>
                                        <input type="text" class="form-control @error('information.address') is-invalid @enderror" id="address"
                                               name="information[address]" value="{{ old('information.address') }}"
                                               placeholder="{{ __('admin.customers.fields.address') }}" required>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="address_number">{{ __('admin.customers.fields.address_number') }}<span style="color: #e8604c">*</span></label>
                                        <input type="text" class="form-control @error('information.address_number') is-invalid @enderror" id="address_number"
                                               name="information[address_number]" value="{{ old('information.address_number') }}"
                                               placeholder="{{ __('admin.customers.fields.address_number') }}" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label for="postal">{{ __('admin.customers.fields.postal') }}<span style="color: #e8604c">*</span></label>
                                        <input type="text" class="form-control @error('information.postal') is-invalid @enderror" id="postal"
                                               name="information[postal]" value="{{ old('information.postal') }}"
                                               placeholder="{{ __('admin.customers.fields.postal') }}" required>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="city">{{ __('admin.customers.fields.city') }}<span style="color: #e8604c">*</span></label>
                                        <input type="text" class="form-control @error('information.city') is-invalid @enderror" id="city"
                                               name="information[city]" value="{{ old('information.city') }}"
                                               placeholder="{{ __('admin.customers.fields.city') }}" required>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="country">{{ __('admin.customers.fields.country') }} <span class="text-muted">({{ __('admin.customers.optional') }})</span></label>
                                        <input type="text" class="form-control @error('information.country') is-invalid @enderror" id="country"
                                               name="information[country]" value="{{ old('information.country', 'Deutschland') }}"
                                               placeholder="{{ __('admin.customers.fields.country') }}">
                                    </div>
                                </div>

                                <p class="text-muted small mb-0">{{ __('admin.customers.create_hint') }}</p>

                                <div class="card-footer text-end px-0">
                                    <button type="submit" class="btn btn-success my-1">{{ __('admin.customers.save') }}</button>
                                    <a href="{{ route('admin.customers.index') }}" class="btn btn-danger my-1">{{ __('admin.customers.cancel') }}</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    (function () {
        var personFields = document.querySelector('[data-account-person]');
        var companyFields = document.querySelector('[data-account-company]');
        var radios = document.querySelectorAll('input[name="account_type"]');

        function syncAccountType() {
            var type = document.querySelector('input[name="account_type"]:checked');
            var isCompany = type && type.value === 'company';
            if (personFields) personFields.style.display = isCompany ? 'none' : '';
            if (companyFields) companyFields.style.display = isCompany ? '' : 'none';
        }

        radios.forEach(function (radio) {
            radio.addEventListener('change', syncAccountType);
        });
        syncAccountType();
    })();
</script>
@endsection
