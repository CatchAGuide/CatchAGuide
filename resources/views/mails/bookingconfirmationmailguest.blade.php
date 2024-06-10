<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body style="margin: 0; padding: 0;">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td>
            <table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
                <tr>
                    <td align="center" bgcolor="#ffffff" style="padding: 40px 0 30px 0;">
{{--                        <img src="{{asset('assets/images/logo.png')}}" alt="Catchaguide" width="150" style="display: block;" />--}}
                    </td>
                </tr>
                <tr>
                    <td bgcolor="#ffffff" style="padding: 40px 30px 40px 30px;">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td style="color: #153643; font-family: Arial, sans-serif; font-size: 24px;">
                                    <b></b>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 20px 0 0 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                    @lang('mailing.hello') {{$user->firstname}}, <br><br>
                                    @lang('mailing.thankBooking')<br>
                                    @lang('mailing.guidingTakePlace')
                                    {{ Carbon\Carbon::parse($booking->blocked_event->from)->format('d.m.Y') }}
                                    @lang('mailing.contactYourGuide')<br> <br>
                                    @lang('mailing.infoBooking'): <br>
                                    <h3>{{ $guiding->title ? translate($guiding->title) : null }}</h3>

                                    <table class="table">
                                        <thead>
                                        <tr width="600">
                                            <th scope="col" width="150">@lang('mailing.date')</th>
                                            <th scope="col" width="100">@lang('mailing.gueast')</th>
                                            <th scope="col" width="100">@lang('mailing.duration')</th>
                                            <th scope="col" width="150">@lang('mailing.youPay')</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <th scope="row" width="150">
                                                @if($booking->blocked_event)
                                                    {{ Carbon\Carbon::parse($booking->blocked_event->from)->format('d-m-Y') }}
                                                @else
                                                    @lang('mailing.cancel')
                                                @endif

                                            </th>
                                            <td width="100" align="center">{{$booking->count_of_users}}</td>
                                            <td width="100" align="center">
                                                {{ $guiding->duration }}
                                                @if($guiding->duration > 1)
                                                    @lang('mailing.hours')
                                                @else
                                                    @lang('mailing.hour')
                                                @endif
                                            </td>
                                            <td width="150" align="center">
                                                €{{$booking->price}} 
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>

                                    @if($guiding->additional_information)
                                        <br>@lang('mailing.other'):</br>
                                        {{ $guiding->additional_information ?  translate($guiding->additional_information) : null }}
                                    @endif

                                        {{ $guiding->description ?  translate($guiding->description) : null }}
                                        
                                </td>
                            </tr>
                            <tr><td style="padding: 20px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                    <ul>
                                        <li>
                                            <div class="icon">
                                                <i class="fa fa-check"></i>
                                            </div>
                                            <div class="text">
                                                <p><b>@lang('mailing.tagetFish'):</b>
                                                    @php
                                                    $guidingTargets = $guiding->guidingTargets->pluck('name')->toArray();
        
                                                    if(app()->getLocale() == 'en'){
                                                        $guidingTargets =  $guiding->guidingTargets->pluck('name_en')->toArray();
                                                    }
                                                    @endphp
                                                    
                                                    @if(!empty($guidingTargets))
                                                        {{ implode(', ', $guidingTargets) }}
                                                    @else
                                                        {{ $guiding->threeTargets() }}
                                                        {{$guiding->target_fish_sonstiges ? " & " . $guiding->target_fish_sonstiges : ""}}
                                                    @endif
                                                </p>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="icon">
                                                <i class="fa fa-check"></i>
                                            </div>
                                            <div class="text">
                                                <p><b>@lang('mailing.fishMethod'):</b>
                                                    @if(app()->getLocale() == 'en')
                                                    {{$guiding->fishingTypes->name_en ? $guiding->fishingTypes->name_en : $guiding->fishingTypes->name }}
                                                    @else
                                                    {{$guiding->fishingTypes->name}}
                                                    @endif
                                                </p>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="icon">
                                                <i class="fa fa-check"></i>
                                            </div>
                                            <div class="text">
                                                <p><b>@lang('mailing.method'):</b>
                                                    @php
                                                    $guidingMethods = $guiding->guidingMethods->pluck('name')->toArray();
        
                                                    if(app()->getLocale() == 'en'){
                                                        $guidingMethods =  $guiding->guidingMethods->pluck('name_en')->toArray();
                                                    }
                                                    @endphp
                                                    
                                                    @if(!empty($guidingMethods))
                                                        {{ implode(', ', $guidingMethods) }}
                                                    @else
                                                    {{ $guiding->threeMethods() }}
                                                    {{$guiding->methods_sonstiges && $guiding->threeMethods() > 0 ? " & " . $guiding->methods_sonstiges : null}}
                                                    @endif
                                                </p>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="icon">
                                                <i class="fa fa-check"></i>
                                            </div>
                                            <div class="text">
                                                <p><b>@lang('mailing.shoreOrBoat'):</b>
                                                    @if($guiding->fishingFrom){{ $guiding->fishingFrom->name}} @else {{$guiding->fishing_from}} @endif
                                                </p>
                                            </div>
                                        </li>
                                        @if($guiding->meeting_point)
                                            <li>
                                                <div class="icon">
                                                    <i class="fa fa-check"></i>
                                                </div>
                                                <div class="text">
                                                    <p><b>@lang('mailing.mettingPlace'):</b>
                                                        {{  translate($guiding->meeting_point) }}
                                                    </p>
                                                </div>
                                            </li>
                                        @endif
                                        @if(count($guiding->inclussions))
                                        <li>
                                            <div class="icon">
                                                <i class="fa fa-check"></i>
                                            </div>
                                            <div class="text">
                                                <p><b>@lang('profile.inclussion'):</b>
                                                    @php
                                                        $guidingInclusion = $guiding->inclussions->pluck('name')->toArray();
                                                    
                                                        if (app()->getLocale() == 'en') {
                                                            $guidingInclusion = $guiding->inclussions->pluck('name_en')->toArray();
                                                    
                                                            // If name_en is empty, fallback to name
                                                            foreach ($guidingInclusion as $index => $name) {
                                                                if (empty($name)) {
                                                                    $guidingInclusion[$index] = $guiding->inclussions[$index]->name;
                                                                }
                                                            }
                                                        }
                                                    @endphp
        
                                                @if (!empty($guidingInclusion))
                                                {{ implode(', ', array_filter($guidingInclusion)) }}
                                                @endif
                                                </p>
                                            </div>
                                        </li>
                                        @endif

                                        @if($booking->extras)

                                        <li>
                                            <div class="icon">
                                                <i class="fa fa-check"></i>
                                            </div>
                                            <div class="text">
                                                <p><b>Extras:</b>
                                                    {{implode(',',unserialize($booking->extras))}};
                                                </p>
                                            </div>
                                        </li>
                                        @endif
                                    </ul>

                                    <h3>@lang('mailing.forQuestion'):</h3>
                                    @lang('mailing.guideInfo'):
                                    <ul>
                                        <li><strong>Name:</strong> {{$guiding->user->firstname}}</li>
                                        {{--<li><strong>Telefonnummer:</strong> {{ $guiding->user->information['phone']}}</li>--}}
                                        <li><strong>@lang('mailing.pNumber'):</strong> {{$phoneFromUser}}</li>
                                        <li><strong>Email:</strong> <a href="mailto:{{$guiding->user->email}}">{{$guiding->user->email}}</a></li>
                                    </ul>
                                    @lang('mailing.infoBooking'):
                                    <ul>
                                        @if($guiding->user->bar_allowed == 0 && $guiding->user->banktransfer_allowed == 0 && $guiding->user->paypal_allowed == 0)
                                            <li>{{ translate('Kontaktiere bitten den Guide!') }} </li>
                                        @endif
                                        @if($guiding->user->bar_allowed == 1)
                                            <li><strong>{{ translate('Barzahlung vor Ort') }}</strong></li>
                                        @endif
                                        @if($guiding->user->banktransfer_allowed == 1 && $guiding->user->banktransferdetails)
                                            <li><strong>{{ translate('Überweisung') }}:</strong> {{$guiding->user->banktransferdetails}}</li>
                                        @endif
                                        @if($guiding->user->paypal_allowed == 1 && $guiding->user->paypaldetails)
                                            <li><strong>PayPal:</strong> {{$guiding->user->paypaldetails}}</li>
                                        @endif
                                    </ul>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 20px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                    <span style="color: red !important; font-size: 12px !important;">*@lang('mailing.ensure')</span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 20px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                    <a href="{{route('login')}}">@lang('mailing.login')</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td bgcolor="#ffffff" style="padding: 30px 30px 30px 30px;">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td style="color: black; font-family: Arial, sans-serif; font-size: 14px;">
                                    &reg; Catchaguide<br/>
                                    <a href="{{route('login')}}" style="color: black;"><font color="black">Login</font></a> @lang('mailing.on') Catchaguide
                                </td>
                                <td align="right">
                                    <table border="0" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td>
                                                <a href="{{url('/')}}">
                                                    <img src="{{asset('assets/images/logo.png')}}" alt="Logo Klein" width="90" style="display: block;" border="0" />
                                                </a>
                                            </td>

                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
