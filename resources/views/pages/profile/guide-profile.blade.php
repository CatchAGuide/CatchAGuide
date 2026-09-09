@extends('pages.profile.layouts.profile')
@section('title', __('profile.guide_profile'))

@section('profile-content')
    <div class="bookings-header">
        <h1 class="mb-0 text-white"><i class="fas fa-fish"></i> {{ __('profile.guide_profile') }}</h1>
        <p class="mb-0 mt-2 text-white">{{ __('profile.guide_profile_subtitle') }}</p>
    </div>

    @if (session('message'))
        <div class="alert alert-success border-0 mb-4">
            <i class="fas fa-check-circle me-2"></i>{{ session('message') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger border-0 mb-4">
            <div class="d-flex align-items-start">
                <i class="fas fa-exclamation-triangle me-3 text-danger mt-1"></i>
                <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        </div>
    @endif

    <form action="{{ route('profile.guide-profile.update') }}" method="post" enctype="multipart/form-data" class="guide-application-form">
        @csrf
        @method('PUT')

        <div class="form-section">
            <div class="section-header">
                <div class="section-icon"><i class="fas fa-image"></i></div>
                <div class="section-title">
                    <h4>{{ __('profile.profileImage') }}</h4>
                    <p class="text-muted mb-0">{{ __('profile.shownOnPublicProfile') }}</p>
                </div>
            </div>
            <div class="section-content">
                <div class="form-group">
                    <input type="file" name="profil_image" class="form-control" accept="image/*">
                    <small class="form-text text-muted">{{ __('profile.imageRecommendation') }}</small>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="section-header">
                <div class="section-icon"><i class="fas fa-fish"></i></div>
                <div class="section-title">
                    <h4>{{ __('profile.fishingProfile') }}</h4>
                    <p class="text-muted mb-0">{{ __('profile.whatGuestsSee') }}</p>
                </div>
            </div>
            <div class="section-content">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="form-group">
                            <label for="languages" class="form-label">{{ __('guidings.Languages') }} <span class="required">*</span></label>
                            <input type="text" class="form-control" id="languages" name="information[languages]" required
                                placeholder="{{ __('profile.languagesExample') }}"
                                value="{{ old('information.languages', auth()->user()?->information?->languages) }}">
                            <small class="form-text text-muted">{{ __('profile.listLanguages') }}</small>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label for="about_me" class="form-label">{{ __('guidings.About_me') }} <span class="required">*</span></label>
                            <textarea class="form-control" id="about_me" name="information[about_me]" rows="6" required
                                placeholder="{{ __('profile.tellAboutYourself') }}">{{ old('information.about_me', auth()->user()?->information?->about_me) }}</textarea>
                            <small class="form-text text-muted">{{ __('profile.introduceYourself') }}</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="favorite_fish" class="form-label">{{ __('guidings.Favorite_fish') }} <span class="required">*</span></label>
                            <input type="text" class="form-control" id="favorite_fish" name="information[favorite_fish]" required
                                placeholder="{{ __('guidings.Favorite_fish') }}"
                                value="{{ old('information.favorite_fish', auth()->user()?->information?->favorite_fish) }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fishing_start_year" class="form-label">{{ __('profile.fishingExp') }} <span class="required">*</span></label>
                            <input type="number" class="form-control" id="fishing_start_year" name="information[fishing_start_year]" required
                                min="1950" max="{{ date('Y') }}"
                                placeholder="{{ __('profile.yearExample') }}"
                                value="{{ old('information.fishing_start_year', auth()->user()?->information?->fishing_start_year) }}">
                            <small class="form-text text-muted">{{ __('profile.fishingSinceYear') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="section-header">
                <div class="section-icon"><i class="fas fa-credit-card"></i></div>
                <div class="section-title">
                    <h4>{{ __('profile.paymentMethods') }}</h4>
                    <p class="text-muted mb-0">{{ __('profile.howGuestsPay') }}</p>
                </div>
            </div>
            <div class="section-content">
                <div class="payment-info mb-4">
                    <div class="alert alert-info border-0 mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('profile.possiblepaymentmsg') }}
                    </div>
                </div>

                <div class="payment-options">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="payment-option">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="bar_allowed" name="bar_allowed"
                                        @checked(auth()->user()->bar_allowed == 1)>
                                    <label class="form-check-label" for="bar_allowed">
                                        <i class="fas fa-money-bill-wave me-2"></i>
                                        {{ __('profile.barOnSite') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="payment-option">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="banktransfer_allowed" name="banktransfer_allowed"
                                        @checked(auth()->user()->banktransfer_allowed == 1) onchange="displayBankDetails()">
                                    <label class="form-check-label" for="banktransfer_allowed">
                                        <i class="fas fa-university me-2"></i>
                                        {{ __('profile.transfer') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="payment-option">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="paypal_allowed" name="paypal_allowed"
                                        @checked(auth()->user()->paypal_allowed == 1) onchange="displayPaypalDetails()">
                                    <label class="form-check-label" for="paypal_allowed">
                                        <i class="fab fa-paypal me-2"></i>
                                        PayPal
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="payment-details">
                        <div class="form-group mb-4" id="banktransferdetailsdiv" @if(!auth()->user()->banktransfer_allowed) style="display:none" @endif>
                            <label for="banktransferdetails" class="form-label">{{ __('profile.bankdetails') }} <span class="required">*</span></label>
                            <textarea class="form-control" id="banktransferdetails" name="banktransferdetails" rows="3"
                                placeholder="{{ __('profile.ibanForTransfers') }}">{{ old('banktransferdetails', auth()->user()->banktransferdetails) }}</textarea>
                            <small class="form-text text-muted">{{ __('profile.bankdetailsmsg') }}</small>
                        </div>
                        <div class="form-group mb-0" id="paypaldetailsdiv" @if(!auth()->user()->paypal_allowed) style="display:none" @endif>
                            <label for="paypaldetails" class="form-label">{{ __('profile.paypalDetails') }} <span class="required">*</span></label>
                            <textarea class="form-control" id="paypaldetails" name="paypaldetails" rows="2"
                                placeholder="{{ __('profile.paypalEmail') }}">{{ old('paypaldetails', auth()->user()->paypaldetails) }}</textarea>
                            <small class="form-text text-muted">{{ __('profile.paypalAddressHint') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save me-2"></i>{{ __('global.Save') }}
                </button>
            </div>
        </div>
    </form>
@endsection

@section('js_after')
@parent
<script>
function displayBankDetails() {
    const on = document.getElementById('banktransfer_allowed').checked;
    const div = document.getElementById('banktransferdetailsdiv');
    const field = document.getElementById('banktransferdetails');
    div.style.display = on ? 'block' : 'none';
    if (field) field.required = on;
}
function displayPaypalDetails() {
    const on = document.getElementById('paypal_allowed').checked;
    const div = document.getElementById('paypaldetailsdiv');
    const field = document.getElementById('paypaldetails');
    div.style.display = on ? 'block' : 'none';
    if (field) field.required = on;
}
document.addEventListener('DOMContentLoaded', function () {
    displayBankDetails();
    displayPaypalDetails();
});
</script>
@endsection
