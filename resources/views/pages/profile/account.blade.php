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
        
        #imagePreviewContainer {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .current-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #313041;
            display: block;
            margin: 0 auto;
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
        
        /* Header Section Styling */
        .account-header {
            background: linear-gradient(135deg, #313041, #252238);
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .account-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
            opacity: 0.5;
            animation: float 20s infinite linear;
        }
        
        @keyframes float {
            0% { transform: translateX(-100px) translateY(-100px); }
            100% { transform: translateX(100px) translateY(100px); }
        }
        
        .account-header h1 {
            color: white !important;
            font-weight: 700;
            margin-bottom: 0;
            z-index: 1;
            position: relative;
        }
        
        .account-header p {
            color: white !important;
            opacity: 0.9;
            z-index: 1;
            position: relative;
        }
        
        /* Floating Save Button */
        .floating-save-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
            background-color: #e8604c;
            border: none;
            border-radius: 50px;
            padding: 15px 25px;
            color: white;
            font-weight: 600;
            box-shadow: 0 4px 20px rgba(232, 96, 76, 0.3);
            transition: all 0.3s ease;
            display: none;
        }
        
        .floating-save-btn:hover {
            background-color: #d54e37;
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(232, 96, 76, 0.4);
        }
        
        .floating-save-btn.show {
            display: block;
            animation: slideInUp 0.3s ease-out;
        }
        
        @keyframes slideInUp {
            from {
                transform: translateY(100px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Phone input responsive styles */
        @media (max-width: 576px) {
            .d-flex {
                flex-direction: column;
            }
            
            .d-flex > * {
                width: 100% !important;
                max-width: 100% !important;
                margin-bottom: 0.5rem;
                border-radius: 0.25rem !important;
            }
            
            .d-flex > *:not(:last-child) {
                border-right: 1px solid #ced4da;
            }
        }

        @media (min-width: 577px) {
            .d-flex > *:not(:first-child) {
                border-left: none;
            }
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

    <!-- Header Section -->
    <div class="account-header">
        <h1 class="mb-0 text-white">
            <i class="fas fa-user-edit"></i>
            Personal Details
        </h1>
        <p class="mb-0 mt-2 text-white">Manage your profile information and account settings</p>
    </div>

    <form action="{{route('profile.account')}}" method="POST" enctype="multipart/form-data" id="profileForm">
        @csrf
        @method('PUT')
        
        <!-- Profile Image Section -->
        <div class="profile-section">
            <h3 class="section-title">@lang('profile.DPselect')</h3>
            <div class="profile-image-section">
                <div id="imagePreviewContainer">
                    @if(Auth::user()->profil_image)
                        <img src="{{asset('images/'. Auth::user()->profil_image)}}" 
                             alt="Profile Image" class="current-image" id="profileImagePreview">
                    @else
                        <img src="" alt="Profile Image" class="current-image" id="profileImagePreview" style="display: none;">
                    @endif
                </div>
                <div>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*" />
                    <small class="helper-text mt-2">Select an image to see preview before saving</small>
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
                        <div class="d-flex">
                            <select class="form-control rounded w-25 me-2" 
                                    id="countryCode" name="countryCode" style="max-width: 120px;" required>
                                <option value="+49" {{ auth()->user()?->phone_country_code == '+49' ? 'selected' : '' }}>+49 (Germany)</option>
                                <option value="+1" {{ auth()->user()?->phone_country_code == '+1' ? 'selected' : '' }}>+1 (USA/Canada)</option>
                                <option value="+44" {{ auth()->user()?->phone_country_code == '+44' ? 'selected' : '' }}>+44 (UK)</option>
                                <option value="+33" {{ auth()->user()?->phone_country_code == '+33' ? 'selected' : '' }}>+33 (France)</option>
                                <option value="+39" {{ auth()->user()?->phone_country_code == '+39' ? 'selected' : '' }}>+39 (Italy)</option>
                                <option value="+34" {{ auth()->user()?->phone_country_code == '+34' ? 'selected' : '' }}>+34 (Spain)</option>
                                <option value="+81" {{ auth()->user()?->phone_country_code == '+81' ? 'selected' : '' }}>+81 (Japan)</option>
                                <option value="+86" {{ auth()->user()?->phone_country_code == '+86' ? 'selected' : '' }}>+86 (China)</option>
                                <option value="+91" {{ auth()->user()?->phone_country_code == '+91' ? 'selected' : '' }}>+91 (India)</option>
                                <option value="+61" {{ auth()->user()?->phone_country_code == '+61' ? 'selected' : '' }}>+61 (Australia)</option>
                                <option value="+353" {{ auth()->user()?->phone_country_code == '+353' ? 'selected' : '' }}>+353 (Ireland)</option>
                                <option value="+31" {{ auth()->user()?->phone_country_code == '+31' ? 'selected' : '' }}>+31 (Netherlands)</option>
                                <option value="+46" {{ auth()->user()?->phone_country_code == '+46' ? 'selected' : '' }}>+46 (Sweden)</option>
                                <option value="+47" {{ auth()->user()?->phone_country_code == '+47' ? 'selected' : '' }}>+47 (Norway)</option>
                                <option value="+45" {{ auth()->user()?->phone_country_code == '+45' ? 'selected' : '' }}>+45 (Denmark)</option>
                                <option value="+358" {{ auth()->user()?->phone_country_code == '+358' ? 'selected' : '' }}>+358 (Finland)</option>
                                <option value="+32" {{ auth()->user()?->phone_country_code == '+32' ? 'selected' : '' }}>+32 (Belgium)</option>
                                <option value="+41" {{ auth()->user()?->phone_country_code == '+41' ? 'selected' : '' }}>+41 (Switzerland)</option>
                                <option value="+43" {{ auth()->user()?->phone_country_code == '+43' ? 'selected' : '' }}>+43 (Austria)</option>
                                <option value="+48" {{ auth()->user()?->phone_country_code == '+48' ? 'selected' : '' }}>+48 (Poland)</option>
                                <option value="+351" {{ auth()->user()?->phone_country_code == '+351' ? 'selected' : '' }}>+351 (Portugal)</option>
                                <option value="+30" {{ auth()->user()?->phone_country_code == '+30' ? 'selected' : '' }}>+30 (Greece)</option>
                                <option value="+420" {{ auth()->user()?->phone_country_code == '+420' ? 'selected' : '' }}>+420 (Czech Republic)</option>
                                <option value="+36" {{ auth()->user()?->phone_country_code == '+36' ? 'selected' : '' }}>+36 (Hungary)</option>
                                <option value="+7" {{ auth()->user()?->phone_country_code == '+7' ? 'selected' : '' }}>+7 (Russia)</option>
                                <option value="+380" {{ auth()->user()?->phone_country_code == '+380' ? 'selected' : '' }}>+380 (Ukraine)</option>
                                <option value="+90" {{ auth()->user()?->phone_country_code == '+90' ? 'selected' : '' }}>+90 (Turkey)</option>
                                <option value="+20" {{ auth()->user()?->phone_country_code == '+20' ? 'selected' : '' }}>+20 (Egypt)</option>
                                <option value="+27" {{ auth()->user()?->phone_country_code == '+27' ? 'selected' : '' }}>+27 (South Africa)</option>
                                <option value="+55" {{ auth()->user()?->phone_country_code == '+55' ? 'selected' : '' }}>+55 (Brazil)</option>
                                <option value="+52" {{ auth()->user()?->phone_country_code == '+52' ? 'selected' : '' }}>+52 (Mexico)</option>
                                <option value="+54" {{ auth()->user()?->phone_country_code == '+54' ? 'selected' : '' }}>+54 (Argentina)</option>
                                <option value="+56" {{ auth()->user()?->phone_country_code == '+56' ? 'selected' : '' }}>+56 (Chile)</option>
                                <option value="+57" {{ auth()->user()?->phone_country_code == '+57' ? 'selected' : '' }}>+57 (Colombia)</option>
                                <option value="+51" {{ auth()->user()?->phone_country_code == '+51' ? 'selected' : '' }}>+51 (Peru)</option>
                                <option value="+64" {{ auth()->user()?->phone_country_code == '+64' ? 'selected' : '' }}>+64 (New Zealand)</option>
                                <option value="+65" {{ auth()->user()?->phone_country_code == '+65' ? 'selected' : '' }}>+65 (Singapore)</option>
                                <option value="+60" {{ auth()->user()?->phone_country_code == '+60' ? 'selected' : '' }}>+60 (Malaysia)</option>
                                <option value="+66" {{ auth()->user()?->phone_country_code == '+66' ? 'selected' : '' }}>+66 (Thailand)</option>
                                <option value="+62" {{ auth()->user()?->phone_country_code == '+62' ? 'selected' : '' }}>+62 (Indonesia)</option>
                                <option value="+63" {{ auth()->user()?->phone_country_code == '+63' ? 'selected' : '' }}>+63 (Philippines)</option>
                                <option value="+84" {{ auth()->user()?->phone_country_code == '+84' ? 'selected' : '' }}>+84 (Vietnam)</option>
                                <option value="+82" {{ auth()->user()?->phone_country_code == '+82' ? 'selected' : '' }}>+82 (South Korea)</option>
                                <option value="+972" {{ auth()->user()?->phone_country_code == '+972' ? 'selected' : '' }}>+972 (Israel)</option>
                                <option value="+971" {{ auth()->user()?->phone_country_code == '+971' ? 'selected' : '' }}>+971 (UAE)</option>
                                <option value="+966" {{ auth()->user()?->phone_country_code == '+966' ? 'selected' : '' }}>+966 (Saudi Arabia)</option>
                            </select>
                            <input type="tel" class="form-control rounded" 
                                   id="phone" name="phone" 
                                   placeholder="{{translate('Telefonnummer')}}" 
                                   value="{{auth()->user()?->phone ?? ''}}" required>
                        </div>
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


        @endif

        <div class="text-center">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> @lang('profile.save')
            </button>
        </div>
    </form>
    
    <!-- Floating Save Button -->
    <button type="button" class="floating-save-btn" id="floatingSaveBtn" onclick="document.getElementById('profileForm').submit();">
        <i class="fas fa-save"></i> Save Changes
    </button>
@endsection

@section('js_after')
    <script>

        // Image preview functionality
        document.getElementById('image').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('profileImagePreview');
            
            if (file) {
                // Check if the file is an image
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    };
                    
                    reader.readAsDataURL(file);
                } else {
                    alert('Please select a valid image file.');
                    event.target.value = ''; // Clear the file input
                }
            } else {
                // If no file selected, hide preview or show original image
                @if(Auth::user()->profil_image)
                    preview.src = "{{asset('images/'. Auth::user()->profil_image)}}";
                    preview.style.display = 'block';
                @else
                    preview.style.display = 'none';
                @endif
            }
            
            // Show floating save button when image is changed
            showFloatingSaveButton();
        });

        // Floating Save Button functionality
        let formChanged = false;
        const floatingSaveBtn = document.getElementById('floatingSaveBtn');
        
        function showFloatingSaveButton() {
            if (!formChanged) {
                formChanged = true;
                floatingSaveBtn.classList.add('show');
            }
        }
        
        // Show floating button when any form field changes
        const formInputs = document.querySelectorAll('#profileForm input, #profileForm textarea, #profileForm select');
        formInputs.forEach(input => {
            input.addEventListener('input', showFloatingSaveButton);
            input.addEventListener('change', showFloatingSaveButton);
        });
        
        // Show floating button on checkbox changes
        const checkboxes = document.querySelectorAll('#profileForm input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', showFloatingSaveButton);
        });
        
        // Hide floating button on form submit
        document.getElementById('profileForm').addEventListener('submit', function() {
            floatingSaveBtn.classList.remove('show');
        });
        
        // Show floating button on scroll (alternative trigger)
        let ticking = false;
        function updateFloatingButton() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            if (scrollTop > 300 && formChanged) {
                floatingSaveBtn.classList.add('show');
            }
            ticking = false;
        }
        
        window.addEventListener('scroll', function() {
            if (!ticking) {
                requestAnimationFrame(updateFloatingButton);
                ticking = true;
            }
        });
    </script>
@endsection
