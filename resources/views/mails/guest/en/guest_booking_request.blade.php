<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Booking Request</title>
    <link href="https://fonts.cdnfonts.com/css/morrison" rel="stylesheet">
    <style>
        body {
            font-family: 'Morrison', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #e8604c !important;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
        }
        .logo {
            max-width: 150px;
        }
        .content {
            padding: 20px 0;
        }
        .booking-details {
            /* padding: 10px 0; */
        }
        .overview {
            text-align: center;
            /* padding: 20px; */

            margin-top: 20px;
        }
        .footer {
            text-align: center;
            padding-top: 20px;
            color: #777777;
        }
        .price-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        .price-label {
            font-size: 16px;
            color: #555555;
            padding: 5px 0;
        }
        .price-value {
            font-size: 18px;
            color: #555555;
            padding: 5px 0;
        }
        .heading-title{
            
        }
        .btn-theme{
            background-color: #e8604c;
            padding:10px 20px;
            color:#fff !important;
            border:0;
            text-decoration: none;
            margin-top:30px;
        }
        p{
            font-size:14px;
        }
        .total-price{
            color: #e8604c;
            font-size: 18px;
        }
        h1{
            margin:0;
        }
        .the-guide{
            font-style: italic;
        }
        .text-primary{
            color: #e8604c;
        }
        .header-title{
            padding-top:10px;
        }
        .content-header{
            padding-bottom: 10px;
        }
        .order-details{
            border:1px solid rgb(132, 132, 132);
            padding:10px;
            border-radius: 12px;
        }
    </style>
</head>
<body bgcolor="#e8604c" style="background-color: #e8604c">

<div class="container">
    <div class="header">
        <img class="logo" src="https://catchaguide.com/assets/images/logo_mobil.jpg" alt="Catchaguide Logo">
        <h1 class="header-title">
                Confirmation of your booking request
        </h1>
    </div>
    <div class="content">
        <div class="content-header">
            <p style="font-size:16px;">@lang('profile.booking-hello') <strong>{{$user->firstname}}</strong>,</p>
            <p>
                Thank you for your booking with Catch A Guide! 
            </p>
            <p>
                Your booking request has been successfully received by the guide. You will receive a response to your request within a maximum of 72 hours.
                As soon as your guide has confirmed your request, you will receive all further information about your tour as well as the contact details of your guide.
            </p>
        </div>
    </div>
    <div class="order-details">
        <div>
            <h1>{{$guiding->title}}</h1>
            <span class="the-guide text-primary">{{$guide->firstname}}</span>
            <p>{!! $guiding->description !!}</p>
        </div>
        <div style="margin-top:30px;">
            <a class="btn-theme" href="{{route('guidings.show',[$guiding->id,$guiding->slug])}}">
                View guiding
            </a>
        </div>
        <div class="booking-details" style="margin-top:50px;">
            <h3 style="font-style:italic">
                Information about your booking
            </h3>
        </div>
        <div class="booking-details">
            <p><strong>@lang('profile.date'):</strong> {{ Carbon\Carbon::parse($booking->blocked_event->from)->format('F j, Y') }}</p>
            <p><strong>@lang('profile.guests'):</strong> {{$booking->count_of_users}}</p>
            <p>
                <strong>@lang('mailing.tagetFish'):</strong> 
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
            
            <p>
                <strong>@lang('mailing.fishMethod'):</strong> 
                @if(app()->getLocale() == 'en')
                {{$guiding->fishingTypes->name_en ? $guiding->fishingTypes->name_en : $guiding->fishingTypes->name }}
                @else
                    {{$guiding->fishingTypes->name}}
                @endif
            </p>


            <p><strong> @lang('mailing.method'):</strong>
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
            <p><strong>@lang('mailing.shoreOrBoat'):</strong>
                @php
                $whereFishing = null;
                if($guiding->fishingFrom){
                    if(app()->getLocale() == 'en'){
                        $whereFishing = $guiding->fishingFrom->name_en;
                    }else{
                       $whereFishing =  $guiding->fishingFrom->name;
                    }
                }
            
                @endphp
                @if($whereFishing) {{$whereFishing}} @else {{$guiding->fishing_from}} @endif
            </p>
            <p><strong>@lang('profile.waterType'):</strong> 
                @php
                $guidingWaters = $guiding->guidingWaters->pluck('name')->toArray();

                if(app()->getLocale() == 'en'){
                    $guidingWaters =  $guiding->guidingWaters->pluck('name_en')->toArray();
                }

                @endphp
                
                @if(!empty($guidingWaters))
                    {{ implode(', ', $guidingWaters) }}
                @else
                {{-- {{ translate($guiding->threeWaters()) }}
                {{$guiding->water_sonstiges ? " & " . translate($guiding->water_sonstiges) : ""}} --}}
                @endif
            </p>
            <p><strong>@lang('profile.meetingPoint'):</strong> {{$guiding->meeting_point}}</p>
            <p><strong>@lang('profile.inclussion'):</strong>  
                @php
                    $guidingInclusion = $guiding->inclussions->pluck('name')->toArray();
                    
                    if (app()->getLocale() == 'en') {
                        $guidingInclusion = $guiding->inclussions->pluck('name_en')->toArray();
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
        <hr>

        <div class="overview" style="text-align: right;">
            <table class="table" style="margin-left:auto;font-size:14px;">
                <tbody>
                    <tr>
                        <td scope="row"></td>
                        <td><strong>
                            Guiding Price:
                            @endif
                        </strong>
                    </td>
                        <td><strong class="text-primary">{{$guiding->getGuidingPriceByPerson($booking->count_of_users)}}€</strong></td>
                        
                    </tr>
                    @if($booking->extras)
                        @foreach(unserialize($booking->extras) as $key => $price)
                        <tr>
                            <td scope="row"></td>
                            <td><strong>{{ucfirst($key)}}:</strong></td>
                            <td><strong class="text-primary">{{$price * $booking->count_of_users}}€</strong></td>
                        </tr>
                        @endforeach
                    @endif
                    <tr class="text-primary" style="font-size:16px">
                        <td scope="row"></td>
                        <td><strong>
                            Total:
                        </strong></td>
                        <td><strong class="text-primary">{{$booking->price}}€</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="content">
        <p>@lang('profile.booking-currentlypending')</p>
        <p>@lang('profile.booking-anyquestion')</p>
    </div>
    <div style="text-align:center">
        <a class="btn-theme" href="https://catchaguide.com/contact">@lang('profile.booking-contactus')</a>
    </div>
    <div class="footer">
        <p>@lang('profile.booking-regards'),<br>Catch A Guide</p>
    </div>
</div>

</body>
</html>
