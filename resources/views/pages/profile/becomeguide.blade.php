@extends('pages.profile.layouts.profile')
@section('title', translate('Als Guide verifizieren'))

@section('profile-content')
    <style>
        .abschnitt {
            margin-bottom: 30px;
            margin-top: 30px;
            padding-bottom: 20px;
            border: 1px solid black;
        }
    </style>
    @if(Auth::user()->is_guide === 0)
        <div class="alert alert-danger" role="alert">
            {{translate('Wir haben Deine Anfrage erhalten und werden uns innerhalb von 24 Stunden bei Dir melden!')}}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <h3>{{translate('Wieso verifizieren?')}}</h3>
    <p>
        {{translate('Für die Freigabe zum Guide benötigen wir weitere Informationen über Dich. Deine persönlichen Daten werden nicht auf der Webseite veröffentlicht oder mit dritten geteilt. Zudem hilft uns die Verifizierung sicher zu stellen, dass Du als Guide im Besitz der für Deine Guidings benötigten Angelerlaubnisse bist und somit ein nachhaltiges sowie waidgerechtes Angeln gewährleistest.')}}
    </p>
    <form action="{{route('guide')}}" method="post" enctype="multipart/form-data">
            @csrf
        <div class="row abschnitt">
            <div class="form-group col-md-4">
                <label for="firstname">{{translate('Vorname')}}<span style="color: #e8604c">*</span></label>
                <input type="text" class="form-control" id="firstname" name="firstname" placeholder="{{translate('Vorname')}}"
                       value="{{ auth()->user()->firstname }}">
            </div>
            <div class="form-group col-md-4">
                <label for="lastname">{{translate('Nachname')}}<span style="color: #e8604c">*</span></label>
                <input type="text" class="form-control" id="lastname" name="lastname" placeholder="Nachname{{translate('Nachname')}}"
                       value=" {{ auth()->user()->lastname }}">
            </div>
            <div class="form-group col-md-4">
                <label for="birthday">{{translate('Geburtstag')}}</label>
                <input type="date" max="{{ Carbon\Carbon::now()->format('Y-m-d') }}" class="form-control" id="birthday" name="information[birthday]" placeholder="{{translate('Geburtstag')}}" value="{{ auth()->user()?->information?->birthday?->format('Y-m-d') ?? '' }}">
            </div>
            <div class="form-group col-md-4">
                <label for="address">{{translate('Straße')}}<span style="color: #e8604c">*</span></label>
                <input type="text" class="form-control" id="address" placeholder="{{translate('Straße')}}" required
                       name="information[address]" value="{{auth()->user()->information->address ?? ''}}">
            </div>
            <div class="form-group col-md-2">
                <label for="address_number">Nr.<span style="color: #e8604c">*</span></label>
                <input type="text" class="form-control" id="address_number" placeholder="Nr."
                       name="information[address_number]"
                       value="{{auth()->user()?->information->address_number ?? ''}}" required>
            </div>

            <div class="form-group col-md-6">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="E-Mail" disabled
                       value="{{ auth()->user()->email }}">
            </div>
            <div class="form-group col-md-4">
                <label for="postal">PLZ<span style="color: #e8604c">*</span></label>
                <input type="text" class="form-control" id="postal" name="information[postal]" placeholder="PLZ"
                       value="{{auth()->user()?->information->postal ?? ''}}" required>
            </div>
            <div class="form-group col-md-8">
                <label for="city">Stadt<span style="color: #e8604c">*</span></label>
                <input type="text" class="form-control" id="city" name="information[city]" placeholder="Stadt"
                       value="{{auth()->user()?->information->city ?? ''}}" required>
            </div>
            <div class="form-group col-md-6">
                <label for="phone">{{translate('Telefonnummer')}}<span style="color: #e8604c">*</span></label>
                <input type="text" class="form-control" id="phone" name="information[phone]" placeholder="{{translate('Telefonnummer')}}"
                       value="{{auth()->user()?->information->phone ?? ''}}" required>
            </div>
            <div class="form-group col-md-6">
                <label for="taxId">{{translate('Umsatzsteuer-Identifikationsnummer')}}*</label>
                <input type="text" class="form-control" id="taxId" name="information[taxId]" placeholder="{{translate('Umsatzsteuer-Identifikationsnummer')}}"
                       value="{{auth()->user()?->tax_id ?? ''}}">
                <span style="font-size: 10px">{{translate('*Falls eine Umsatzsteuer-Identifikationsnummer vorhanden ist, gib diese hier an.')}}</span>
            </div>
        </div>
        <div class="row abschnitt">
            <div class="form-group col-md-12">
                <label for="languages">{{translate('Sprachen')}}<span style="color: #e8604c">*</span></label>
                <input type="text" class="form-control" id="languages" name="information[languages]" placeholder="{{translate('Sprachen')}}"
                       value="{{auth()->user()?->information->languages ?? ''}}" required>
            </div>
            <div class="form-group col-md-12">
                <label for="postal">{{translate('Über mich')}}<span style="color: #e8604c; font-size: 12px">*Bitte schildere hier in einigen Sätzen, wer du bist, etwas zu deinem anglerischen Hintergrund, deine Lieblings-methoden, was für ein Typ du bist, etc. Kurzum, stelle dich vor.
                    </span></label>
                <textarea type="text" class="form-control" id="description" placeholder="{{translate('Über mich')}}..." name="information[about_me]" required rows="6">{{auth()->user()?->information->about_me ?? ''}}
                </textarea>
            </div>
            <div class="form-group col-md-12">
                <label for="favorite_fish">{{translate('Lieblingsfisch')}}<span style="color: #e8604c">*</span></label>
                <input type="text" class="form-control" id="favorite_fish" name="information[favorite_fish]" placeholder="{{translate('Lieblingsfisch')}}"
                       value="{{auth()->user()?->information->favorite_fish ?? ''}}" required>
            </div>
            <div class="form-group col-md-12">
                <label for="information['fishing_start_year']">{{translate('Anglererfahrung')}}<span style="color: #e8604c; font-size: 12px">{{translate('*bitte gib das Jahr an seit dem Du angelst z.B. 2004')}}</span></label>
                <input type="number" class="form-control" placeholder="{{translate('Anglererfahrung')}}" id="information['fishing_start_year']" name="information[fishing_start_year]"
                       value="{{auth()->user()?->information->fishing_start_year ?? ''}}" required>
            </div>
        </div>

        <div class="row abschnitt">
            <div class="form-group col-md-12">
                <label for="favoritepaymentmethod">{{translate('Mögliche Bezahlmethoden')}}<br><span style="color: #e8604c; font-size: 12px">{{translate('*Gib hier bitte möglichst viele Zahlungsoptionen an (mindestens Kontodaten für Überweisungen), mit denen Deine Gäste Dich bezahlen können. Die Zahlungsdetails erhält Dein Gast nach erfolgter Buchung. Du bekommst den gesamten Betrag direkt von Deinem Gast. Die entsprechende Vermittlungsgebühr für Catch A Guide wird dir nach dem Stattfinden eines Guidings in Rechnung gestellt.')}}</span></label>
            </div>
            <div class="form-group col-md-3">
                <input class="form-check-input" type="checkbox" value="1" @if(auth()->user()->bar_allowed == 1) checked @endif id="bar_allowed" name="bar_allowed">
                <label class="form-check-label" for="bar_allowed">
                    {{translate('Bar vor Ort')}}
                </label>
            </div>
            <div class="form-group col-md-3">
                <input class="form-check-input" onclick="displayBankDetails()" type="checkbox" @if(auth()->user()->banktransfer_allowed == 1) checked @endif value="1" id="banktransfer_allowed" name="banktransfer_allowed">
                <label class="form-check-label" for="banktransfer_allowed">
                    {{translate('Überweisung')}}
                </label>
            </div>
            <div class="form-group col-md-3">
                <input class="form-check-input" onclick="displayPaypalDetails()"  @if(auth()->user()->paypal_allowed == 1) checked @endif type="checkbox" value="1" id="paypal_allowed" name="paypal_allowed">
                <label class="form-check-label" for="paypal_allowed">
                    Paypal
                </label>
            </div>
            <div class="form-group col-md-12" @if(auth()->user()->banktransfer_allowed == 0) style="display: none;" @endif id="banktransferdetailsdiv" >
                <label for="banktransferdetails">{{translate('Bankdaten')}}<span style="color: #e8604c; font-size: 12px">{{translate('*Gib hier bitte Deine IBAN (für Banküberweisungen) ein.')}}
                    </span></label>
                <textarea type="text" class="form-control" placeholder="IBAN" name="banktransferdetails" id="banktransferdetails" rows="1">@if(auth()->user()->banktransferdetails){{auth()->user()->banktransferdetails}}@endif</textarea>
            </div>
            <div class="form-group col-md-12" @if(auth()->user()->paypal_allowed == 0) style="display: none;" @endif id="paypaldetailsdiv" >
                <label for="paypaldetails">{{translate('Paypaladresse')}}<span style="color: #e8604c; font-size: 12px">{{translate('*Gib hier bitte Deine Paypaladresse ein.')}}
                    </span></label>
                <textarea type="text" class="form-control" placeholder="Paypal" id="paypaldetails" name="paypaldetails" rows="1">@if(auth()->user()->paypaldetails){{auth()->user()->paypaldetails}}@endif</textarea>
            </div>
        </div>

        <div class="row abschnitt">
            <div class="form-group col-md-12">
                <div class="mb-3">
                    <input class="form-check-input" type="checkbox" value="1" id="lawcard" name="lawcard" required>
                    <label class="form-check-label" for="lawcard">
                        {{translate('Fischereierlaubnis')}}</label>
                    <br><span style="color: #e8604c; font-size: 12px">{{translate('*Hiermit bestätige ich, dass ich über die für meine Guidings notwendige Angelerlaubnis verfüge und gegen keine Regeln des lokalen Natur- und Tierschutzes verstoße')}}</span>

                </div>
            </div>
        </div>


        <button type="submit" class="btn btn-primary mt-2"
                style="color:#ffffff; background-color: #e8604c; border-color: #e8604c">Absenden
        </button>
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
            }
        }
    </script>
@endsection
