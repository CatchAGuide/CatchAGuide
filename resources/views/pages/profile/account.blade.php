@extends('pages.profile.layouts.profile')
@section('title', __('message.info'))

@section('profile-content')
    <style>
        .abschnitt {
            margin-bottom: 30px;
            margin-top: 30px;
            padding-bottom: 20px;
            border: 1px solid black;
        }
    </style>
    @if($errors)
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    @endif

    <form action="{{route('profile.account')}}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
            <div class="row">
                <div class="form-group col-md-12">
                    @if(Auth::user()->profil_image)
                    <div class="tour-details__review-comment-top-img">
                        <img class="center-block"
                             src="{{asset('images/'. Auth::user()->profil_image)}}"  style="
                                    display: block;
                                    margin-left: auto;
                                    margin-right: auto;
                                    object-fit: cover;" alt="" width="250px" height="250px" height="auto">
                    </div>
                    @endif
                    <label class="form-label" for="customFile">@lang('profile.DPselect')</label>
                    <input type="file" class="form-control" id="image" name="image" />
                </div>
            </div>
            <div class="row abschnitt">
                <div class="form-group col-md-4">
                    <label for="firstname">@lang('profile.fname')<span style="color: #e8604c">*</span></label>
                    <input type="text" class="form-control" id="firstname" name="firstname" placeholder="{{translate('Vorname')}}"
                           value="{{ auth()->user()->firstname }}">
                </div>
                <div class="form-group col-md-4">
                    <label for="lastname">@lang('profile.lname')<span style="color: #e8604c">*</span></label>
                    <input type="text" class="form-control" id="lastname" name="lastname" placeholder="{{translate('Nachname')}}"
                           value=" {{ auth()->user()->lastname }}">
                </div>
                <div class="form-group col-md-4">
                    <label for="birthday">@lang('profile.bday')</label>
                    <input type="date" max="{{ Carbon\Carbon::now()->format('Y-m-d') }}" class="form-control" id="birthday" name="information[birthday]"
                           placeholder="{{translate('Geburtstag')}}" value="{{ auth()->user()?->information?->birthday?->format('Y-m-d') ?? '' }}">
                </div>
                <div class="form-group col-md-4">
                    <label for="address">@lang('profile.street')<span style="color: #e8604c">*</span></label>
                    <input type="text" class="form-control" id="address" placeholder="{{translate('Straße')}}" required
                           name="information[address]" value="{{auth()->user()->information->address ?? ''}}">
                </div>
                <div class="form-group col-md-2">
                    <label for="address_number">@lang('profile.no.')<span style="color: #e8604c">*</span></label>
                    <input type="text" class="form-control" id="address_number" placeholder="Nr."
                           name="information[address_number]"
                           value="{{auth()->user()?->information->address_number ?? ''}}" required>
                </div>

                <div class="form-group col-md-6">
                    <label for="email">@lang('profile.email')</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="E-Mail" disabled
                           value="{{ auth()->user()->email }}">
                </div>
                <div class="form-group col-md-4">
                    <label for="postal">@lang('profile.zip')<span style="color: #e8604c">*</span></label>
                    <input type="text" class="form-control" id="postal" name="information[postal]" placeholder="PLZ"
                           value="{{auth()->user()?->information->postal ?? ''}}" required>
                </div>
                <div class="form-group col-md-8">
                    <label for="city">@lang('profile.city')<span style="color: #e8604c">*</span></label>
                    <input type="text" class="form-control" id="city" name="information[city]" placeholder="{{translate('Stadt')}}"
                           value="{{auth()->user()?->information->city ?? ''}}" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="phone">@lang('profile.pnumber')<span style="color: #e8604c">*</span></label>
                    <input type="text" class="form-control" id="phone" name="phone" placeholder="{{translate('Telefonnummer')}}"
                           value="{{auth()->user()?->phone ?? ''}}" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="phone">Language<span style="color: #e8604c">*</span></label>
                    <select class="form-control form-select" name="language" id="language" required>
                        <option disabled selected>Select Language</option>
                        <option value="en" {{ $authUser->language == 'en' ? 'selected' : '' }}>English</option>
                        <option value="de" {{ $authUser->language == 'de' ? 'selected' : '' }}>German</option>
                    </select>
                </div>

                <div class="form-group col-md-4">
                    <label for="tax_id">@lang('profile.taxIdNum')*</label>
                    <input type="text" class="form-control" id="tax_id" name="information[tax_id]" placeholder="{{translate('Umsatzsteuer-Identifikationsnummer')}}"
                           value="{{auth()->user()?->tax_id ?? ''}}">
                    <span style="font-size: 10px">*@lang('profile.taxNummsg')</span>
                </div>
            </div>
        <input type="hidden" name="paypal_allowed" value=0>
        <input type="hidden" name="banktransfer_allowed" value=0>
        <input type="hidden" name="bar_allowed" value=0>
        @if(auth()->user()->is_guide)
            <div class="row abschnitt">
                <div class="form-group col-md-12">
                    <label for="languages">@lang('profile.language')<span style="color: #e8604c">*</span></label>
                    <input type="text" class="form-control" id="languages" name="information[languages]" placeholder="{{translate('Sprachen')}}"
                           value="{{auth()->user()?->information->languages ?? ''}}" required>
                </div>
                <div class="form-group col-md-12">
                    <label for="postal">@lang('profile.aboutMe')<span style="color: #e8604c; font-size: 12px">*@lang('profile.aboutMemessage')
                    </span></label>
                    <textarea type="text" class="form-control" id="description" placeholder="{{translate('Über mich')}}..." name="information[about_me]" required rows="6">{{auth()->user()?->information->about_me ?? ''}}
                </textarea>
                </div>
                <div class="form-group col-md-12">
                    <label for="favorite_fish">@lang('profile.favFish')<span style="color: #e8604c">*</span></label>
                    <input type="text" class="form-control" id="favorite_fish" name="information[favorite_fish]" placeholder="{{translate('Lieblingsfisch')}}"
                           value="{{auth()->user()?->information->favorite_fish ?? ''}}" required>
                </div>
                <div class="form-group col-md-12">
                    <label for="information['fishing_start_year']">@lang('profile.fishingExp')<span style="color: #e8604c; font-size: 12px">*@lang('profile.fishingExpmssg')</span></label>
                    <input type="number" class="form-control" placeholder="{{translate('Anglererfahrung')}}" id="information['fishing_start_year']" name="information[fishing_start_year]"
                           value="{{auth()->user()?->information->fishing_start_year ?? ''}}" required>
                </div>
            </div>
            <input type="hidden" name="paypal_allowed" value=0>
            <input type="hidden" name="banktransfer_allowed" value=0>
            <input type="hidden" name="bar_allowed" value=0>
                <div class="row abschnitt">
                    <div class="form-group col-md-12">
                        <label for="favoritepaymentmethod">@lang('profile.possiblepayment')<br><span style="color: #e8604c; font-size: 12px">*@lang('profile.possiblepaymentmsg')</span></label>
                    </div>
                    <div class="form-group col-md-3">

                        <input class="form-check-input" {{auth()->user()->bar_allowed ? 'checked' : ''}} type="checkbox" value=1 id="bar_allowed" name="bar_allowed">
                        <label class="form-check-label" for="bar_allowed">
                        @lang('profile.barOnSite')
                        </label>
                    </div>
                    <div class="form-group col-md-3">

                        <input class="form-check-input" {{auth()->user()->banktransfer_allowed ? 'checked' : ''}} onclick="displayBankDetails()" type="checkbox" value=1 id="banktransfer_allowed" name="banktransfer_allowed">
                        <label class="form-check-label" for="banktransfer_allowed">
                        @lang('profile.transfer')
                        </label>
                    </div>
                    <div class="form-group col-md-3">

                        <input class="form-check-input" {{auth()->user()->paypal_allowed == 1 ? 'checked' : ''}} onclick="displayPaypalDetails()" type="checkbox" value=1 id="paypal_allowed" name="paypal_allowed">
                        <label class="form-check-label" for="paypal_allowed">
                        @lang('profile.paypal')
                        </label>
                    </div>
                    <div class="form-group col-md-12" style="display: {{auth()->user()->banktransfer_allowed ? 'block' : 'none'}};" id="banktransferdetails" >
                        <label for="banktransferdetails">@lang('profile.bankdetails')<span style="color: #e8604c; font-size: 12px">*@lang('profile.bankdetailsmsg')
                    </span></label>
                        <textarea type="text" class="form-control" placeholder="IBAN" name="banktransferdetails" rows="1">{{auth()->user()->banktransferdetails}}</textarea>
                    </div>
                    <div class="form-group col-md-12" style="display: {{auth()->user()->paypal_allowed ? 'block' : 'none'}};" id="paypaldetails" >
                        <label for="paypaldetails">@lang('profile.paypaladd')<span style="color: #e8604c; font-size: 12px">*@lang('profile.paypalmsg')
                    </span></label>
                        <textarea type="text" class="form-control" placeholder="Paypal" name="paypaldetails" rows="1">{{auth()->user()->paypaldetails}}</textarea>
                    </div>
                </div>
        @endif
            <hr>
        <div class="new_passwort">
            <div class="alert alert-danger" role="alert">
            @lang('profile.changePassmssg')
            </div>
            <div class="form-group{{ $errors->has('current_password') ? ' has-error' : '' }}">
                <label for="new_password" class="col-md-4 control-label">@lang('profile.currpassword')</label>

                <div class="col-md-6">
                    <input id="current_password" type="password" class="form-control" name="current_password">

                    @if ($errors->has('current_password'))
                        <span class="help-block">
                                    <strong>{{ $errors->first('current_password') }}</strong>
                                </span>
                    @endif
                </div>
            </div>

            <div class="form-group{{ $errors->has('new_password') ? ' has-error' : '' }}">
                <div class="col-md-6">
                    <label for="new_password" class="control-label">@lang('profile.newpass')</label>
                    <input id="new_password" type="password" class="form-control" name="new_password">

                    @if ($errors->has('new_password'))
                        <span class="help-block">
                                    <strong>{{ $errors->first('new_password') }}</strong>
                                </span>
                    @endif
                </div>
                <div class="col-md-6">
                    <label for="new_password_confirm" class="control-label">@lang('profile.confpass')</label>
                    <input id="new_password_confirm" type="password" class="form-control" name="new_password_confirmation">
                </div>
            </div>
        </div>


        <hr>
        <button type="submit" class="btn btn-primary mt-2"
                style="color:#ffffff; background-color: #e8604c; border-color: #e8604c">@lang('profile.save')
        </button>
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
