@extends('pages.profile.layouts.profile')
@section('title', __('message.info'))

@section('profile-content')
    <style>
        .profile-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 25px;
            border-left: 4px solid #313041;
        }
        
        .section-title {
            color: #313041;
            font-weight: 600;
            margin-bottom: 20px;
            font-size: 1.1rem;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            font-weight: 500;
            color: #333;
            margin-bottom: 8px;
        }
        
        .required {
            color: #e8604c;
        }
        
        .form-control {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 12px;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #313041;
            box-shadow: 0 0 0 0.2rem rgba(49, 48, 65, 0.25);
        }
        
        .profile-image-section {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .current-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #313041;
            margin-bottom: 15px;
        }
        
        .btn-primary {
            background-color: #e8604c;
            border-color: #e8604c;
            padding: 12px 30px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: #d54e37;
            border-color: #d54e37;
        }
        
        .guide-section {
            background: linear-gradient(135deg, #313041, #252238);
            color: white;
            border-left-color: #252238;
        }
        
        .guide-section .section-title {
            color: white;
        }
        
        .guide-section .form-label {
            color: #f8f9fa;
        }
        
        .payment-checkbox {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .payment-checkbox input {
            margin-right: 10px;
        }
        
        .helper-text {
            font-size: 0.85rem;
            color: #666;
            margin-top: 5px;
        }
        
        .alert-info {
            background-color: #e3f2fd;
            border-color: #2196f3;
            color: #1976d2;
        }
    </style>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif

    <form action="{{route('profile.account')}}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <!-- Profile Image Section -->
        <div class="profile-section">
            <h3 class="section-title">@lang('profile.DPselect')</h3>
            <div class="profile-image-section">
                @if(Auth::user()->profil_image)
                    <img src="{{asset('images/'. Auth::user()->profil_image)}}" 
                         alt="Profile Image" class="current-image">
                @endif
                <div>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*" />
                </div>
            </div>
        </div>

        <!-- Personal Information Section -->
        <div class="profile-section">
            <h3 class="section-title">Personal Information</h3>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="firstname">@lang('profile.fname')<span class="required">*</span></label>
                        <input type="text" class="form-control" id="firstname" name="firstname" 
                               placeholder="{{translate('Vorname')}}" value="{{ auth()->user()->firstname }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="lastname">@lang('profile.lname')<span class="required">*</span></label>
                        <input type="text" class="form-control" id="lastname" name="lastname" 
                               placeholder="{{translate('Nachname')}}" value="{{ auth()->user()->lastname }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="birthday">@lang('profile.bday')</label>
                        <input type="date" max="{{ Carbon\Carbon::now()->format('Y-m-d') }}" class="form-control" 
                               id="birthday" name="information[birthday]" placeholder="{{translate('Geburtstag')}}" 
                               value="{{ auth()->user()?->information?->birthday?->format('Y-m-d') ?? '' }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="phone">@lang('profile.pnumber')<span class="required">*</span></label>
                        <input type="text" class="form-control" id="phone" name="phone" 
                               placeholder="{{translate('Telefonnummer')}}" value="{{auth()->user()?->phone ?? ''}}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="email">@lang('profile.email')</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               placeholder="E-Mail" value="{{ auth()->user()->email }}" disabled>
                        <small class="helper-text">Email cannot be changed. Contact support if needed.</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="language">Language<span class="required">*</span></label>
                        <select class="form-control form-select" name="language" id="language" required>
                            <option disabled selected>Select Language</option>
                            <option value="en" {{ $authUser->language == 'en' ? 'selected' : '' }}>English</option>
                            <option value="de" {{ $authUser->language == 'de' ? 'selected' : '' }}>German</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Address Information Section -->
        <div class="profile-section">
            <h3 class="section-title">Address Information</h3>
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label class="form-label" for="address">@lang('profile.street')<span class="required">*</span></label>
                        <input type="text" class="form-control" id="address" placeholder="{{translate('Straße')}}" 
                               name="information[address]" value="{{auth()->user()->information->address ?? ''}}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label" for="address_number">@lang('profile.no.')<span class="required">*</span></label>
                        <input type="text" class="form-control" id="address_number" placeholder="Nr."
                               name="information[address_number]" value="{{auth()->user()?->information->address_number ?? ''}}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label" for="postal">@lang('profile.zip')<span class="required">*</span></label>
                        <input type="text" class="form-control" id="postal" name="information[postal]" 
                               placeholder="PLZ" value="{{auth()->user()?->information->postal ?? ''}}" required>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group">
                        <label class="form-label" for="city">@lang('profile.city')<span class="required">*</span></label>
                        <input type="text" class="form-control" id="city" name="information[city]" 
                               placeholder="{{translate('Stadt')}}" value="{{auth()->user()?->information->city ?? ''}}" required>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="form-label" for="tax_id">@lang('profile.taxIdNum')</label>
                        <input type="text" class="form-control" id="tax_id" name="information[tax_id]" 
                               placeholder="{{translate('Umsatzsteuer-Identifikationsnummer')}}" value="{{auth()->user()?->tax_id ?? ''}}">
                        <small class="helper-text">@lang('profile.taxNummsg')</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden fields for payment methods -->
        <input type="hidden" name="paypal_allowed" value="0">
        <input type="hidden" name="banktransfer_allowed" value="0">
        <input type="hidden" name="bar_allowed" value="0">

        @if(auth()->user()->is_guide)
            <!-- Guide Information Section -->
            <div class="profile-section guide-section">
                <h3 class="section-title">Guide Information</h3>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-label" for="languages">@lang('profile.language')<span class="required">*</span></label>
                            <input type="text" class="form-control" id="languages" name="information[languages]" 
                                   placeholder="{{translate('Sprachen')}}" value="{{auth()->user()?->information->languages ?? ''}}" required>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-label" for="description">@lang('profile.aboutMe')</label>
                            <textarea class="form-control" id="description" placeholder="{{translate('Über mich')}}..." 
                                      name="information[about_me]" rows="4" required>{{auth()->user()?->information->about_me ?? ''}}</textarea>
                            <small class="helper-text">@lang('profile.aboutMemessage')</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="favorite_fish">@lang('profile.favFish')<span class="required">*</span></label>
                            <input type="text" class="form-control" id="favorite_fish" name="information[favorite_fish]" 
                                   placeholder="{{translate('Lieblingsfisch')}}" value="{{auth()->user()?->information->favorite_fish ?? ''}}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="fishing_start_year">@lang('profile.fishingExp')</label>
                            <input type="number" class="form-control" placeholder="{{translate('Anglererfahrung')}}" 
                                   id="fishing_start_year" name="information[fishing_start_year]" 
                                   value="{{auth()->user()?->information->fishing_start_year ?? ''}}" required>
                            <small class="helper-text">@lang('profile.fishingExpmssg')</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Methods Section -->
            <div class="profile-section guide-section">
                <h3 class="section-title">@lang('profile.possiblepayment')</h3>
                <div class="alert alert-info">
                    <small>@lang('profile.possiblepaymentmsg')</small>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="payment-checkbox">
                            <input class="form-check-input" {{auth()->user()->bar_allowed ? 'checked' : ''}} 
                                   type="checkbox" value="1" id="bar_allowed" name="bar_allowed">
                            <label class="form-check-label" for="bar_allowed">@lang('profile.barOnSite')</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="payment-checkbox">
                            <input class="form-check-input" {{auth()->user()->banktransfer_allowed ? 'checked' : ''}} 
                                   onclick="displayBankDetails()" type="checkbox" value="1" id="banktransfer_allowed" name="banktransfer_allowed">
                            <label class="form-check-label" for="banktransfer_allowed">@lang('profile.transfer')</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="payment-checkbox">
                            <input class="form-check-input" {{auth()->user()->paypal_allowed == 1 ? 'checked' : ''}} 
                                   onclick="displayPaypalDetails()" type="checkbox" value="1" id="paypal_allowed" name="paypal_allowed">
                            <label class="form-check-label" for="paypal_allowed">@lang('profile.paypal')</label>
                        </div>
                    </div>
                </div>
                
                <div class="form-group" style="display: {{auth()->user()->banktransfer_allowed ? 'block' : 'none'}};" id="banktransferdetails">
                    <label class="form-label" for="banktransferdetails">@lang('profile.bankdetails')</label>
                    <textarea class="form-control" placeholder="IBAN" name="banktransferdetails" rows="2">{{auth()->user()->banktransferdetails}}</textarea>
                    <small class="helper-text">@lang('profile.bankdetailsmsg')</small>
                </div>
                
                <div class="form-group" style="display: {{auth()->user()->paypal_allowed ? 'block' : 'none'}};" id="paypaldetails">
                    <label class="form-label" for="paypaldetails">@lang('profile.paypaladd')</label>
                    <textarea class="form-control" placeholder="Paypal" name="paypaldetails" rows="2">{{auth()->user()->paypaldetails}}</textarea>
                    <small class="helper-text">@lang('profile.paypalmsg')</small>
                </div>
            </div>
        @endif

        <div class="text-center">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> @lang('profile.save')
            </button>
        </div>
    </form>
@endsection

@section('js_after')
    <script>
        function displayBankDetails() {
            var banktransferCheckBox = document.getElementById('banktransfer_allowed');
            var banktransferDetails = document.getElementById('banktransferdetails');

            if(banktransferCheckBox.checked === true) {
                banktransferDetails.style.display = 'block';
            } else {
                banktransferDetails.style.display = 'none';
            }
        }

        function displayPaypalDetails() {
            var paypaltransferCheckBox = document.getElementById('paypal_allowed');
            var paypaltransferDetails = document.getElementById('paypaldetails');

            if(paypaltransferCheckBox.checked === true) {
                paypaltransferDetails.style.display = 'block';
            } else {
                paypaltransferDetails.style.display = 'none';
            }
        }
    </script>
@endsection
