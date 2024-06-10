<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
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
                        <img src="{{asset('assets/images/logo.png')}}" alt="Catchaguide" width="150"
                             style="display: block;"/>
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
                                <td style="padding: 20px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                    @lang('mailing.hello'){{$guide->firstname}}, <br><br>
                                    @lang('mailing.youHvae')<br>
                                    <h3>@lang('mailing.custData'):</h3>
                                    <ul>
                                        <li>Name: {{$user->firstname}} {{$user->lastname}}</li>
                                        <li>@lang('mailing.pNumber'): {{$phoneFromUser}}</li>
                                        <li>Email: {{$user->email}}</li>
                                    </ul>
                                    <h3>@lang('mailing.bookingInfo'):</h3>
                                    <ul>
                                        <li>@lang('mailing.date'): {{ Carbon\Carbon::parse($booking->blocked_event->from)->format('d-m-Y') }}</li>
                                        <li>Guiding: <a href="{{route('guidings.show',[$guiding->id,$guiding->slug])}}">{{$guiding->title}}</a></li>
                                        <li>@lang('mailing.GuestNum'): {{$booking->count_of_users}}@lang('mailing.gueast')</li>
                                        <li>@lang('mailing.price'): €{{two($booking->price)}} </li>
                                        <li>
                                            @lang('mailing.tagetFish'):
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
                                        </li>
                                        <li>
                                            @lang('mailing.fishMethod'):
                                            @if(app()->getLocale() == 'en')
                                            {{$guiding->fishingTypes->name_en ? $guiding->fishingTypes->name_en : $guiding->fishingTypes->name }}
                                            @else
                                            {{$guiding->fishingTypes->name}}
                                            @endif
                                        </li>
                                        <li>
                                            @lang('mailing.method')
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
                                        </li>
                                        <li>
                                            @lang('mailing.shoreOrBoat'):
                                            @if($guiding->fishingFrom){{ $guiding->fishingFrom->name}} @else {{$guiding->fishing_from}} @endif
                                        </li>
                                        @if($guiding->meeting_point)
                                        <li>
                                            @lang('mailing.mettingPlace'):  {{  translate($guiding->meeting_point) }}
                                        </li>
                                        @endif
                                        <li>@lang('profile.inclussion'): 
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

                                        </li>
                                        <li>Extras: {{$booking->extras ? implode(',',unserialize($booking->extras)) : null}}</li>
                                    </ul>
                                    <p>@lang('mailing.atTheEnd').</p>

                                    @lang('mailing.pleaseLogIn')
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
                                    <a href="{{route('login')}}" style="color: black;"><font color="black">Login</font></a>
                                    @lang('mailing.on') Catchaguide
                                </td>
                                <td align="right">
                                    <table border="0" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td>
                                                <a href="{{url('/')}}">
                                                    <img src="{{asset('assets/images/logo.png')}}" alt="Logo Klein"
                                                         width="90" style="display: block;" border="0"/>
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
