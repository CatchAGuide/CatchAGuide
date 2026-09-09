@extends('pages.profile.layouts.profile')
@section('title', __('message.verify-guide'))



@section('profile-content')
    <!-- Header Section -->
    <div class="bookings-header">
        <h1 class="mb-0 text-white">
            <i class="fas fa-certificate"></i>
            {{ __('profile.become_guide') }}
        </h1>
        <p class="mb-0 mt-2 text-white">{{ __('profile.join_community') }}</p>
    </div>

    {{-- @if(Auth::user()->is_guide === 0)
        <div class="alert alert-info border-0 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-info-circle me-3 text-primary"></i>
                <div>
                    <strong>{{__('profile.applicationReceived')}}</strong><br>
                    {{__('profile.requestReceived24h')}}
                </div>
            </div>
        </div>
    @endif --}}

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

    <!-- Why Verify Section -->
    <div class="info-section mb-5">
        <div class="info-card">
            <div class="info-header">
                <i class="fas fa-question-circle"></i>
                <h3>{{__('profile.onboarding_why_verify')}}</h3>
            </div>
            <div class="info-content">
                <p>
                    {{__('profile.guideVerificationIntro')}}
                </p>
            </div>
        </div>
    </div>

    <!-- Application Form -->
    <form action="{{route('guide')}}" method="post" enctype="multipart/form-data" class="guide-application-form">
        @csrf
        
        <!-- Personal Information Section -->
        <div class="form-section">
            <div class="section-header">
                <div class="section-icon">
                    <i class="fas fa-user"></i>
                </div>
                <div class="section-title">
                    <h4>{{__('checkout.personal_information')}}</h4>
                    <p class="text-muted mb-0">{{__('profile.basicPersonalDetails')}}</p>
                </div>
            </div>
            
            <div class="section-content">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="firstname" class="form-label">{{__('checkout.forename')}} <span class="required">*</span></label>
                            <input type="text" class="form-control" id="firstname" name="firstname" 
                                   placeholder="{{__('checkout.forename')}}" value="{{ auth()->user()->firstname }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="lastname" class="form-label">{{__('checkout.surname')}} <span class="required">*</span></label>
                            <input type="text" class="form-control" id="lastname" name="lastname" 
                                   placeholder="{{__('checkout.surname')}}" value="{{ auth()->user()->lastname }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="birthday" class="form-label">{{__('profile.bday')}}</label>
                            <input type="date" max="{{ Carbon\Carbon::now()->format('Y-m-d') }}" class="form-control" 
                                   id="birthday" name="information[birthday]" 
                                   value="{{ auth()->user()?->information?->birthday?->format('Y-m-d') ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="{{ auth()->user()->email }}" disabled>
                            <small class="form-text text-muted">{{__('profile.emailCannotBeChanged')}}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Address Information Section -->
        <div class="form-section">
            <div class="section-header">
                <div class="section-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="section-title">
                    <h4>{{__('profile.address_information')}}</h4>
                    <p class="text-muted mb-0">{{__('profile.currentAddressVerification')}}</p>
                </div>
            </div>
            
            <div class="section-content">
                <div class="row g-4">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="address" class="form-label">{{__('forms.street')}} <span class="required">*</span></label>
                            <input type="text" class="form-control" id="address" name="information[address]" 
                                   placeholder="{{__('forms.street')}}" value="{{auth()->user()->information->address ?? ''}}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="address_number" class="form-label">{{__('profile.no.')}} <span class="required">*</span></label>
                            <input type="text" class="form-control" id="address_number" name="information[address_number]" 
                                   placeholder="{{__('profile.no.')}}" value="{{auth()->user()?->information->address_number ?? ''}}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="postal" class="form-label">{{__('profile.zip')}} <span class="required">*</span></label>
                            <input type="text" class="form-control" id="postal" name="information[postal]" 
                                   placeholder="{{__('profile.zip')}}" value="{{auth()->user()?->information->postal ?? ''}}" required>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="city" class="form-label">{{__('checkout.city')}} <span class="required">*</span></label>
                            <input type="text" class="form-control" id="city" name="information[city]" 
                                   placeholder="{{__('checkout.city')}}" value="{{auth()->user()?->information->city ?? ''}}" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Information Section -->
        <div class="form-section">
            <div class="section-header">
                <div class="section-icon">
                    <i class="fas fa-phone"></i>
                </div>
                <div class="section-title">
                    <h4>{{__('profile.contactTaxInformation')}}</h4>
                    <p class="text-muted mb-0">{{__('profile.phoneTaxDetails')}}</p>
                </div>
            </div>
            
            <div class="section-content">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="phone" class="form-label">{{__('search-request.phone')}} <span class="required">*</span></label>
                            <input type="text" class="form-control" id="phone" name="information[phone]" 
                                   placeholder="{{__('search-request.phone')}}" value="{{auth()->user()?->information->phone ?? ''}}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="taxId" class="form-label">{{__('profile.taxIdNum')}}</label>
                            <input type="text" class="form-control" id="taxId" name="information[taxId]" 
                                   placeholder="{{__('profile.taxIdNum')}}" value="{{auth()->user()?->tax_id ?? ''}}">
                            <small class="form-text text-muted">{{__('profile.taxIdNumHint')}}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Information Section -->
        <div class="form-section">
            <div class="section-header">
                <div class="section-icon">
                    <i class="fas fa-fish"></i>
                </div>
                <div class="section-title">
                    <h4>{{__('profile.fishingProfile')}}</h4>
                    <p class="text-muted mb-0">{{__('profile.tellFishingBackground')}}</p>
                </div>
            </div>
            
            <div class="section-content">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="form-group">
                            <label for="languages" class="form-label">{{__('guidings.Languages')}} <span class="required">*</span></label>
                            <input type="text" class="form-control" id="languages" name="information[languages]" 
                                   placeholder="{{__('profile.languagesExample')}}" value="{{auth()->user()?->information->languages ?? ''}}" required>
                            <small class="form-text text-muted">{{__('profile.listLanguages')}}</small>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label for="description" class="form-label">{{__('guidings.About_me')}} <span class="required">*</span></label>
                            <textarea class="form-control" id="description" name="information[about_me]" rows="6" 
                                      placeholder="{{__('profile.tellAboutYourself')}}" required>{{auth()->user()?->information->about_me ?? ''}}</textarea>
                            <small class="form-text text-muted">{{__('profile.introduceYourself')}}</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="favorite_fish" class="form-label">{{__('guidings.Favorite_fish')}} <span class="required">*</span></label>
                            <input type="text" class="form-control" id="favorite_fish" name="information[favorite_fish]" 
                                   placeholder="{{__('guidings.Favorite_fish')}}" value="{{auth()->user()?->information->favorite_fish ?? ''}}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fishing_start_year" class="form-label">{{__('profile.fishingExp')}} <span class="required">*</span></label>
                            <input type="number" class="form-control" id="fishing_start_year" name="information[fishing_start_year]" 
                                   placeholder="{{__('profile.yearExample')}}" value="{{auth()->user()?->information->fishing_start_year ?? ''}}" 
                                   min="1950" max="{{ date('Y') }}" required>
                            <small class="form-text text-muted">{{__('profile.fishingSinceYear')}}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Methods Section -->
        <div class="form-section">
            <div class="section-header">
                <div class="section-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div class="section-title">
                    <h4>{{__('profile.paymentMethods')}}</h4>
                    <p class="text-muted mb-0">{{__('profile.howGuestsPay')}}</p>
                </div>
            </div>
            
            <div class="section-content">
                <div class="payment-info mb-4">
                    <div class="alert alert-info border-0">
                        <i class="fas fa-info-circle me-2"></i>
                        {{__('profile.possiblepaymentmsg')}}
                    </div>
                </div>

                <div class="payment-options">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="payment-option">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="bar_allowed" name="bar_allowed" 
                                           @if(auth()->user()->bar_allowed == 1) checked @endif>
                                    <label class="form-check-label" for="bar_allowed">
                                        <i class="fas fa-money-bill-wave me-2"></i>
                                        {{__('profile.barOnSite')}}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="payment-option">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="banktransfer_allowed" name="banktransfer_allowed" 
                                           @if(auth()->user()->banktransfer_allowed == 1) checked @endif onclick="displayBankDetails()">
                                    <label class="form-check-label" for="banktransfer_allowed">
                                        <i class="fas fa-university me-2"></i>
                                        {{__('profile.transfer')}}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="payment-option">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="paypal_allowed" name="paypal_allowed" 
                                           @if(auth()->user()->paypal_allowed == 1) checked @endif onclick="displayPaypalDetails()">
                                    <label class="form-check-label" for="paypal_allowed">
                                        <i class="fab fa-paypal me-2"></i>
                                        PayPal
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="payment-details">
                        <div class="form-group mb-4" id="banktransferdetailsdiv" @if(auth()->user()->banktransfer_allowed == 0) style="display: none;" @endif>
                            <label for="banktransferdetails" class="form-label">{{__('profile.bankdetails')}} <span class="required">*</span></label>
                            <textarea class="form-control" id="banktransferdetails" name="banktransferdetails" rows="2" 
                                      placeholder="{{__('profile.ibanForTransfers')}}">@if(auth()->user()->banktransferdetails){{auth()->user()->banktransferdetails}}@endif</textarea>
                            <small class="form-text text-muted">{{__('profile.bankdetailsmsg')}}</small>
                        </div>

                        <div class="form-group mb-4" id="paypaldetailsdiv" @if(auth()->user()->paypal_allowed == 0) style="display: none;" @endif>
                            <label for="paypaldetails" class="form-label">{{__('profile.paypalDetails')}} <span class="required">*</span></label>
                            <textarea class="form-control" id="paypaldetails" name="paypaldetails" rows="2" 
                                      placeholder="{{__('profile.paypalEmail')}}">@if(auth()->user()->paypaldetails){{auth()->user()->paypaldetails}}@endif</textarea>
                            <small class="form-text text-muted">{{__('profile.paypalAddressHint')}}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Legal Confirmation Section -->
        <div class="form-section">
            <div class="section-header">
                <div class="section-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="section-title">
                    <h4>{{__('profile.legalConfirmation')}}</h4>
                    <p class="text-muted mb-0">{{__('profile.fishingLicenseCompliance')}}</p>
                </div>
            </div>
            
            <div class="section-content">
                <div class="legal-confirmation">
                    <div class="form-check legal-check">
                        <input class="form-check-input" type="checkbox" value="1" id="lawcard" name="lawcard" required>
                        <label class="form-check-label" for="lawcard">
                            <strong>{{__('profile.fishingLicense')}}</strong>
                        </label>
                    </div>
                    <div class="legal-text">
                        <small class="text-muted">
                            {{__('profile.confirmLicenseAndProtection')}}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Section -->
        <div class="form-actions">
            <div class="d-flex justify-content-end gap-3">
                <a href="{{ route('profile.index') }}" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-arrow-left me-2"></i>
                    {{__('global.Cancel')}}
                </a>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-paper-plane me-2"></i>
                    {{__('profile.submitApplication')}}
                </button>
            </div>
        </div>
    </form>
@endsection



@section('js_after')
    <script>
        function displayBankDetails() {
            var banktransferCheckBox = document.getElementById('banktransfer_allowed');
            var banktransferDetailsDiv = document.getElementById('banktransferdetailsdiv');
            var banktransferDetails = document.getElementById('banktransferdetails');

            if(banktransferCheckBox.checked === true) {
                banktransferDetailsDiv.style.display = 'block';
                banktransferDetails.required = true;
            } else {
                banktransferDetailsDiv.style.display = 'none';
                banktransferDetails.required = false;
                banktransferDetails.value = '';
            }
        }

        function displayPaypalDetails() {
            var paypaltransferCheckBox = document.getElementById('paypal_allowed');
            var paypaltransferDetailsDiv = document.getElementById('paypaldetailsdiv');
            var paypaltransferDetails = document.getElementById('paypaldetails');

            if(paypaltransferCheckBox.checked === true) {
                paypaltransferDetailsDiv.style.display = 'block';
                paypaltransferDetails.required = true;
            } else {
                paypaltransferDetailsDiv.style.display = 'none';
                paypaltransferDetails.required = false;
                paypaltransferDetails.value = '';
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            displayBankDetails();
            displayPaypalDetails();
        });
    </script>
@endsection
