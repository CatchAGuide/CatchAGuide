<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Booking Request Accepted - Guide Confirmed</title>
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
            padding-bottom: 0px;
        }
        .logo {
            max-width: 150px;
        }
        .content {
            padding: 10px 0;
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

        .btn-reject{
            background-color: #1668ab;
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
        <h1 class="header-title">Guiding Request</h1>
        <p>We're reaching out to let you know that a new search request has been recieved.</p>
    </div>
    <div class="content" style="padding-bottom:0px;">
        <div class="content-header">
            <div class="order-details" style="margin-top:10px;">
                <div class="booking-details">
                    <h3 style="font-style:italic">Personal Info:</h3>
                    <p><strong>@lang('request.name'):</strong>{{ $request->name }}</p>
                    <p><strong>@lang('request.phone'):</strong>{{ $request->phone }}</p>
                    <p><strong>@lang('request.email') : </strong>{{ $request->email }}</p>
                </div>
                <div class="booking-details" style="margin-top:30px;">
                    <h3 style="font-style:italic">Request Details:</h3>
                    <p><strong>Guiding Type: </strong>{{ $request->guide_type }}</p>
                    @if($request->accomodation)
                        <p><strong>Accommodation: </strong>{{ $request->accomodation === "fish alone" ? __('request.fishAlone') : __('request.fishingVacation') }}</p>
                        <p><strong>@lang('request.daysOfFishing'): </strong>{{ $request->days_of_fishing }}</p>
                        <p><strong>@lang('request.rentaboat'): </strong>{{ $request->rentaboat }}</p>
                    @else
                        <p><strong>Fishing Duration: </strong>{{ $request->fishing_duration === "half day" ? __('request.halfDay') : __('request.fullDay') }}</p>
                        <p><strong>Fishing From: </strong> {{ $request->fishing_duration === "shore" ? __('request.land') : __('request.boat') }}</p>
                    @endif


                    <p><strong>@lang('request.country'): </strong>{{ $request->country }}</p>
                    <p><strong>@lang('request.city'): </strong>{{ $request->city }}</p>           
                    <p><strong>@lang('request.target'): </strong> 
                        <ul>
                        @foreach(json_decode($request->targets) as $target)
                            <li>{{ $target }}</li>
                        @endforeach
                        </ul>
                    </p>
                    <p><strong>@lang('request.method'): </strong> 
                        <ul>
                            @foreach(json_decode($request->methods) as $method)
                            <li>{{ $method }}</li>
                            @endforeach 
                        </ul>
                    </p>
                    @if($request->fishing_from)
                    <p><strong>Type of Fishing: </strong> {{$request->fishing_from}}</p>
                    @endif
                    <p><strong>@lang('request.numberOfGuest'): </strong> {{$request->number_of_guest}}</p>
                    <p><strong>@lang('request.date') </strong>from: {{ Carbon\Carbon::parse($request->from_date)->format('F j, Y') }} - to: {{ Carbon\Carbon::parse($request->to_date)->format('F j, Y') }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="footer">
        <p>@lang('profile.booking-regards'),<br>Catch A Guide</p>
    </div>
</div>

</body>
</html>
