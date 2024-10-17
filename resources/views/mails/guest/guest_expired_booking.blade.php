<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>New Booking Request</title>
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
        <h1 class="header-title">@lang('profile.gc-cancelled')</h1>
    </div>
    <div class="content" style="padding-bottom:0px;">
        <div class="content-header">
            <p style="font-size:16px;">@lang('profile.booking-dear') <strong>{{$user->firstname}}</strong>,</p>
            <p>@lang('profile.gc-infom')</p>
            @if($myCurrentLocale == 'de')
            <p>Wir bedauern Ihnen mitteilen zu müssen, dass der Guide <strong>{{$guide->firstname}}</strong> in den letzten {{$booking->created_at->diffInHours($booking->expires_at)}}h die Buchungsanfrage zum Guiding <a href="{{route('guidings.show',[$guiding->id,$guiding->slug])}}"><strong>{{$guiding->title}}</strong></a>  am {{ Carbon\Carbon::parse($booking->book_date)->format('F j, Y') }} weder annehmen noch ablehnen konnte. Die Buchungsanfrage ist damit ausgelaufen. Wir bedanken uns für Ihr Verständnis. </p>
            @elseif($myCurrentLocale == 'en')
            <p> We regret to inform you that the guide <strong>{{$guide->firstname}}</strong> could neither accept nor reject the booking request for guiding <a href="{{route('guidings.show',[$guiding->id,$guiding->slug])}}"><strong>{{$guiding->title}}</strong></a> on {{ Carbon\Carbon::parse($booking->book_date)->format('F j, Y') }} in the last {{$booking->created_at->diffInHours($booking->expires_at)}} hours. The booking request has thus expired. Thank you for your understanding.</p>
            @endif
            <div style="margin-top:20px;">
                <p>@lang('profile.gn-question')</p>
                <div style="text-align:center">
                    <a class="btn-theme" href="https://catchaguide.com/contact">@lang('profile.booking-contactus')</a>
                </div>
            </div>
        </div>
    </div>

    <div class="content">

    </div>

    <div class="footer">
        <p style="font-style:italic">@lang('profile.booking-chossing')</p>
        <p>@lang('profile.booking-regards'),<br>Catch A Guide</p>
    </div>
</div>

</body>
</html>
