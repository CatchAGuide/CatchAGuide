@extends('admin.layouts.app')

@section('title', 'Kunde #' . $customer->id . ' editieren')

@section('content')
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">@yield('title')</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Verwaltung</a></li>
                        <li class="breadcrumb-item"><a href="#">Kunden</a></li>
                        <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                    </ol>
                </div>

            </div>
            <!-- PAGE-HEADER END -->
            <!-- Row -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">@yield('title')</h3>
                        </div>
                        <div class="card-body">
                            <form action="{{route('admin.customers.update', $customer->id)}}" method="post" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                
                                <!-- Profile Image -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label for="profile_image">Profilbild auswählen</label>
                                        <input type="file" class="form-control" id="profile_image" name="profile_image">
                                    </div>
                                    <div class="col-md-6">
                                        @if($customer->profile_image)
                                            <div class="text-center">
                                                <label>Aktuelles Profilbild</label>
                                                <div>
                                                    <img src="{{ asset('uploads/profile_images/' . $customer->profile_image) }}" 
                                                         alt="Profilbild" class="img-fluid rounded" style="max-height: 150px;">
                                                </div>
                                            </div>
                                        @else
                                            <div class="text-center">
                                                <label>Kein Profilbild vorhanden</label>
                                                <div>
                                                    <img src="{{ asset('assets/images/default-profile.png') }}" 
                                                         alt="Standard Profilbild" class="img-fluid rounded" style="max-height: 150px;">
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Personal Information -->
                                <div class="row">
                                    <div class="col-lg-4 col-md-12">
                                        <div class="form-group">
                                            <label for="firstname">Vorname<span style="color: #e8604c">*</span></label>
                                            <input type="text" class="form-control" id="firstname" name="firstname"
                                                   placeholder="Vorname" value="{{ $customer->firstname }}" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-12">
                                        <div class="form-group">
                                            <label for="lastname">Nachname<span style="color: #e8604c">*</span></label>
                                            <input type="text" class="form-control" id="lastname" name="lastname"
                                                   placeholder="Nachname" value="{{ $customer->lastname }}" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-12">
                                        <div class="form-group">
                                            <label for="birthday">Geburtstag</label>
                                            <input type="date" class="form-control" id="birthday" name="information[birthday]"
                                                   placeholder="Geburtstag" value="{{ $customer->information?->birthday?->format('Y-m-d') ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Address -->
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label for="address">Straße<span style="color: #e8604c">*</span></label>
                                        <input type="text" class="form-control" id="address" placeholder="Straße" required
                                               name="information[address]" value="{{$customer->information->address ?? ''}}">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="address_number">Nr.<span style="color: #e8604c">*</span></label>
                                        <input type="text" class="form-control" id="address_number" placeholder="Nr."
                                               name="information[address_number]"
                                               value="{{$customer->information->address_number ?? ''}}" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="email">Email<span style="color: #e8604c">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email" placeholder="Email"
                                               value="{{$customer->email ?? ''}}" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label for="postal">PLZ<span style="color: #e8604c">*</span></label>
                                        <input type="text" class="form-control" id="postal" name="information[postal]" placeholder="PLZ"
                                               value="{{$customer->information->postal ?? ''}}" required>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="city">Stadt<span style="color: #e8604c">*</span></label>
                                        <input type="text" class="form-control" id="city" name="information[city]" placeholder="Stadt"
                                               value="{{$customer->information->city ?? ''}}" required>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="phone">Telefonnummer<span style="color: #e8604c">*</span></label>
                                        <input type="text" class="form-control" id="phone" name="information[phone]" placeholder="Telefonnummer"
                                               value="{{$customer->information->phone ?? ''}}" required>
                                    </div>
                                </div>
                                
                                <!-- Language and Tax ID -->
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="language">Sprache<span style="color: #e8604c">*</span></label>
                                        <select class="form-control" id="language" name="language" required>
                                            <option value="Deutsch" {{ $customer->language == 'Deutsch' ? 'selected' : '' }}>Deutsch</option>
                                            <option value="English" {{ $customer->language == 'English' ? 'selected' : '' }}>English</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="tax_id">Umsatzsteuer-Identifikationsnummer</label>
                                        <input type="text" class="form-control" id="tax_id" name="tax_id" placeholder="Umsatzsteuer-Identifikationsnummer"
                                               value="{{$customer->tax_id}}">
                                        <small class="text-muted">Falls eine Umsatzsteuer-Identifikationsnummer vorhanden ist, gib diese hier ein.</small>
                                    </div>
                                </div>
                                
                                <!-- Languages and About -->
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label for="languages">Sprachen</label>
                                        <input type="text" class="form-control" id="languages" name="information[languages]" 
                                               placeholder="z.B. Deutsch, Englisch" value="{{$customer->information->languages ?? ''}}">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label for="about_me">Über mich</label>
                                        <textarea class="form-control" id="about_me" name="information[about_me]" rows="4" 
                                                  placeholder="Bitte schildere hier in einigen Sätzen, wer du bist, etwas zu deinem anglerischen Hintergrund, deine Lieblings-methoden, was für ein Typ du bist, etc.">{{$customer->information->about_me ?? ''}}</textarea>
                                    </div>
                                </div>
                                
                                <!-- Fishing Interests -->
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label for="favorite_fish">Lieblingsfisch</label>
                                        <input type="text" class="form-control" id="favorite_fish" name="information[favorite_fish]" 
                                               placeholder="z.B. Barsch, Meerforelle, Zander und vieles mehr!" value="{{$customer->information->favorite_fish ?? ''}}">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label for="fishing_experience">Anglererfahrung</label>
                                        <input type="text" class="form-control" id="fishing_experience" name="information[fishing_experience]" 
                                               placeholder="Bitte gib das Jahr an seit dem Du angelst (z.B. 2004)" value="{{$customer->information->fishing_experience ?? ''}}">
                                    </div>
                                </div>
                                
                                <!-- Payment Methods -->
                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <h4>Mögliche Bezahlmethoden</h4>
                                        <p class="text-muted">Bitte hier mögliche viele Zahlungsoptionen an (mindestens Kontodaten für Überweisungen), mit denen Deine Gäste Dich bezahlen können. Die Zahlungsdetails erhält Dein Gast nach erfolgter Buchung. Du bekommst den gesamten Betrag direkt von Deinem Gast. Die entsprechende Vermittlungsgebühr wird dir nach dem Stattfinden eines Guidings in Rechnung gestellt.</p>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="bar_allowed" name="bar_allowed" value="1" 
                                                  {{ $customer->bar_allowed ? 'checked' : '' }}>
                                            <label class="form-check-label" for="bar_allowed">Bar vor Ort</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="banktransfer_allowed" name="banktransfer_allowed" value="1" 
                                                  {{ $customer->banktransfer_allowed ? 'checked' : '' }}>
                                            <label class="form-check-label" for="banktransfer_allowed">Überweisung</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="paypal_allowed" name="paypal_allowed" value="1" 
                                                  {{ $customer->paypal_allowed ? 'checked' : '' }}>
                                            <label class="form-check-label" for="paypal_allowed">Paypal</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Bank Details -->
                                <div class="row mt-3" id="banktransfer_details" style="{{ $customer->banktransfer_allowed ? '' : 'display: none;' }}">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="banktransferdetails">Bankverbindung</label>
                                            <textarea class="form-control" id="banktransferdetails" name="banktransferdetails" rows="3" 
                                                     placeholder="IBAN, BIC, Kontoinhaber">{{ $customer->banktransferdetails ?? '' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- PayPal Details -->
                                <div class="row mt-3" id="paypal_details" style="{{ $customer->paypal_allowed ? '' : 'display: none;' }}">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="paypaldetails">PayPal Details</label>
                                            <textarea class="form-control" id="paypaldetails" name="paypaldetails" rows="3" 
                                                     placeholder="PayPal Email oder Link">{{ $customer->paypaldetails ?? '' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-footer text-end">
                                    <button type="submit" class="btn btn-success my-1">Speichern</button>
                                    <a href="{{ route('admin.customers.index') }}" class="btn btn-danger my-1">Abbrechen</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Row -->
        </div>
        <!-- CONTAINER CLOSED -->

    </div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Toggle bank transfer details visibility
        $('#banktransfer_allowed').change(function() {
            if($(this).is(':checked')) {
                $('#banktransfer_details').show();
            } else {
                $('#banktransfer_details').hide();
            }
        });
        
        // Toggle PayPal details visibility
        $('#paypal_allowed').change(function() {
            if($(this).is(':checked')) {
                $('#paypal_details').show();
            } else {
                $('#paypal_details').hide();
            }
        });
    });
</script>
@endsection
