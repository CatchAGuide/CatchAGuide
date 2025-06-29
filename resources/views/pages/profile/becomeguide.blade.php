@extends('pages.profile.layouts.profile')
@section('title', translate('Als Guide verifizieren'))



@section('profile-content')
    <!-- Header Section -->
    <div class="bookings-header">
        <h1 class="mb-0 text-white">
            <i class="fas fa-certificate"></i>
            {{translate('Become a Fishing Guide')}}
        </h1>
        <p class="mb-0 mt-2 text-white">{{translate('Join our community of professional fishing guides')}}</p>
    </div>

    {{-- @if(Auth::user()->is_guide === 0)
        <div class="alert alert-info border-0 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-info-circle me-3 text-primary"></i>
                <div>
                    <strong>{{translate('Application Received!')}}</strong><br>
                    {{translate('Wir haben Deine Anfrage erhalten und werden uns innerhalb von 24 Stunden bei Dir melden!')}}
                </div>
            </div>
        </div>
    @endif --}}

    @if ($errors->any())
        <div class="alert alert-danger border-0 mb-4">
            <div class="d-flex align-items-start">
                <i class="fas fa-exclamation-triangle me-3 text-danger mt-1"></i>
                <div>
                    <strong>Please fix the following errors:</strong>
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
                <h3>{{translate('Wieso verifizieren?')}}</h3>
            </div>
            <div class="info-content">
                <p>
                    {{translate('Für die Freigabe zum Guide benötigen wir weitere Informationen über Dich. Deine persönlichen Daten werden nicht auf der Webseite veröffentlicht oder mit dritten geteilt. Zudem hilft uns die Verifizierung sicher zu stellen, dass Du als Guide im Besitz der für Deine Guidings benötigten Angelerlaubnisse bist und somit ein nachhaltiges sowie waidgerechtes Angeln gewährleistest.')}}
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
                    <h4>{{translate('Personal Information')}}</h4>
                    <p class="text-muted mb-0">{{translate('Basic personal details for verification')}}</p>
                </div>
            </div>
            
            <div class="section-content">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="firstname" class="form-label">{{translate('Vorname')}} <span class="required">*</span></label>
                            <input type="text" class="form-control" id="firstname" name="firstname" 
                                   placeholder="{{translate('Vorname')}}" value="{{ auth()->user()->firstname }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="lastname" class="form-label">{{translate('Nachname')}} <span class="required">*</span></label>
                            <input type="text" class="form-control" id="lastname" name="lastname" 
                                   placeholder="{{translate('Nachname')}}" value="{{ auth()->user()->lastname }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="birthday" class="form-label">{{translate('Geburtstag')}}</label>
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
                            <small class="form-text text-muted">{{translate('Email cannot be changed')}}</small>
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
                    <h4>{{translate('Address Information')}}</h4>
                    <p class="text-muted mb-0">{{translate('Your current address for verification')}}</p>
                </div>
            </div>
            
            <div class="section-content">
                <div class="row g-4">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="address" class="form-label">{{translate('Straße')}} <span class="required">*</span></label>
                            <input type="text" class="form-control" id="address" name="information[address]" 
                                   placeholder="{{translate('Straße')}}" value="{{auth()->user()->information->address ?? ''}}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="address_number" class="form-label">{{translate('Nr.')}} <span class="required">*</span></label>
                            <input type="text" class="form-control" id="address_number" name="information[address_number]" 
                                   placeholder="{{translate('Nr.')}}" value="{{auth()->user()?->information->address_number ?? ''}}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="postal" class="form-label">{{translate('PLZ')}} <span class="required">*</span></label>
                            <input type="text" class="form-control" id="postal" name="information[postal]" 
                                   placeholder="{{translate('PLZ')}}" value="{{auth()->user()?->information->postal ?? ''}}" required>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="city" class="form-label">{{translate('Stadt')}} <span class="required">*</span></label>
                            <input type="text" class="form-control" id="city" name="information[city]" 
                                   placeholder="{{translate('Stadt')}}" value="{{auth()->user()?->information->city ?? ''}}" required>
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
                    <h4>{{translate('Contact & Tax Information')}}</h4>
                    <p class="text-muted mb-0">{{translate('Phone and tax details')}}</p>
                </div>
            </div>
            
            <div class="section-content">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="phone" class="form-label">{{translate('Telefonnummer')}} <span class="required">*</span></label>
                            <input type="text" class="form-control" id="phone" name="information[phone]" 
                                   placeholder="{{translate('Telefonnummer')}}" value="{{auth()->user()?->information->phone ?? ''}}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="taxId" class="form-label">{{translate('Umsatzsteuer-Identifikationsnummer')}}</label>
                            <input type="text" class="form-control" id="taxId" name="information[taxId]" 
                                   placeholder="{{translate('Umsatzsteuer-Identifikationsnummer')}}" value="{{auth()->user()?->tax_id ?? ''}}">
                            <small class="form-text text-muted">{{translate('*Falls eine Umsatzsteuer-Identifikationsnummer vorhanden ist, gib diese hier an.')}}</small>
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
                    <h4>{{translate('Fishing Profile')}}</h4>
                    <p class="text-muted mb-0">{{translate('Tell us about your fishing background')}}</p>
                </div>
            </div>
            
            <div class="section-content">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="form-group">
                            <label for="languages" class="form-label">{{translate('Sprachen')}} <span class="required">*</span></label>
                            <input type="text" class="form-control" id="languages" name="information[languages]" 
                                   placeholder="{{translate('e.g., Deutsch, English, Français')}}" value="{{auth()->user()?->information->languages ?? ''}}" required>
                            <small class="form-text text-muted">{{translate('List all languages you can speak during guidings')}}</small>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label for="description" class="form-label">{{translate('Über mich')}} <span class="required">*</span></label>
                            <textarea class="form-control" id="description" name="information[about_me]" rows="6" 
                                      placeholder="{{translate('Tell potential guests about yourself, your fishing background, favorite methods, and what makes you a great guide...')}}" required>{{auth()->user()?->information->about_me ?? ''}}</textarea>
                            <small class="form-text text-muted">{{translate('Bitte schildere hier in einigen Sätzen, wer du bist, etwas zu deinem anglerischen Hintergrund, deine Lieblings-methoden, was für ein Typ du bist, etc. Kurzum, stelle dich vor.')}}</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="favorite_fish" class="form-label">{{translate('Lieblingsfisch')}} <span class="required">*</span></label>
                            <input type="text" class="form-control" id="favorite_fish" name="information[favorite_fish]" 
                                   placeholder="{{translate('Lieblingsfisch')}}" value="{{auth()->user()?->information->favorite_fish ?? ''}}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fishing_start_year" class="form-label">{{translate('Anglererfahrung')}} <span class="required">*</span></label>
                            <input type="number" class="form-control" id="fishing_start_year" name="information[fishing_start_year]" 
                                   placeholder="{{translate('z.B. 2004')}}" value="{{auth()->user()?->information->fishing_start_year ?? ''}}" 
                                   min="1950" max="{{ date('Y') }}" required>
                            <small class="form-text text-muted">{{translate('Das Jahr seit dem Du angelst')}}</small>
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
                    <h4>{{translate('Payment Methods')}}</h4>
                    <p class="text-muted mb-0">{{translate('How guests can pay you directly')}}</p>
                </div>
            </div>
            
            <div class="section-content">
                <div class="payment-info mb-4">
                    <div class="alert alert-info border-0">
                        <i class="fas fa-info-circle me-2"></i>
                        {{translate('Gib hier bitte möglichst viele Zahlungsoptionen an (mindestens Kontodaten für Überweisungen), mit denen Deine Gäste Dich bezahlen können. Die Zahlungsdetails erhält Dein Gast nach erfolgter Buchung. Du bekommst den gesamten Betrag direkt von Deinem Gast. Die entsprechende Vermittlungsgebühr für Catch A Guide wird dir nach dem Stattfinden eines Guidings in Rechnung gestellt.')}}
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
                                        {{translate('Bar vor Ort')}}
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
                                        {{translate('Überweisung')}}
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
                            <label for="banktransferdetails" class="form-label">{{translate('Bankdaten')}} <span class="required">*</span></label>
                            <textarea class="form-control" id="banktransferdetails" name="banktransferdetails" rows="2" 
                                      placeholder="{{translate('IBAN für Banküberweisungen')}}">@if(auth()->user()->banktransferdetails){{auth()->user()->banktransferdetails}}@endif</textarea>
                            <small class="form-text text-muted">{{translate('Gib hier bitte Deine IBAN (für Banküberweisungen) ein.')}}</small>
                        </div>

                        <div class="form-group mb-4" id="paypaldetailsdiv" @if(auth()->user()->paypal_allowed == 0) style="display: none;" @endif>
                            <label for="paypaldetails" class="form-label">{{translate('PayPal Details')}} <span class="required">*</span></label>
                            <textarea class="form-control" id="paypaldetails" name="paypaldetails" rows="2" 
                                      placeholder="{{translate('PayPal E-Mail Adresse')}}">@if(auth()->user()->paypaldetails){{auth()->user()->paypaldetails}}@endif</textarea>
                            <small class="form-text text-muted">{{translate('Gib hier bitte Deine PayPal-Adresse ein.')}}</small>
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
                    <h4>{{translate('Legal Confirmation')}}</h4>
                    <p class="text-muted mb-0">{{translate('Fishing license and legal compliance')}}</p>
                </div>
            </div>
            
            <div class="section-content">
                <div class="legal-confirmation">
                    <div class="form-check legal-check">
                        <input class="form-check-input" type="checkbox" value="1" id="lawcard" name="lawcard" required>
                        <label class="form-check-label" for="lawcard">
                            <strong>{{translate('Fischereierlaubnis')}}</strong>
                        </label>
                    </div>
                    <div class="legal-text">
                        <small class="text-muted">
                            {{translate('Hiermit bestätige ich, dass ich über die für meine Guidings notwendige Angelerlaubnis verfüge und gegen keine Regeln des lokalen Natur- und Tierschutzes verstoße')}}
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
                    {{translate('Cancel')}}
                </a>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-paper-plane me-2"></i>
                    {{translate('Submit Application')}}
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
