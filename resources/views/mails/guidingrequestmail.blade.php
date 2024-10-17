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
                    @if($request->fishing_type == 'Fishing Holiday')
                        <p><strong>Guiding Type: </strong>{{ $request->fishing_type }}</p>

                        @if($request->is_guided)
                            <p><strong>Days of Guiding : </strong>{{ $request->days_of_guiding }}</p>
                        @endif
                        
                        @if($request->is_boat_rental)
                            <p><strong>Days of Boat Rental : </strong>{{ $request->days_of_boat_rental }}</p>
                        @endif
                    @endif
                    <p><strong>Total Budget: </strong>{{ $request->total_budget_to_spend }}</p>
                    <p><strong>@lang('request.country'): </strong>{{ $request->country }}</p>
                    <p><strong>Region: </strong>{{ $request->region }}</p>           
                    <p><strong>@lang('request.target'): </strong>{{ $request->target_fish }}</p>
                    <p><strong>@lang('request.numberOfGuest'): </strong> {{$request->number_of_guest}}</p>
                    <p><strong>@lang('request.date') </strong>from: {{ Carbon\Carbon::parse($request->date_from)->format('F j, Y') }} - to: {{ Carbon\Carbon::parse($request->date_to)->format('F j, Y') }}</p>
                    <p><strong>Comments</strong></p>
                    <p>{{$request->comments}}</p>
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
