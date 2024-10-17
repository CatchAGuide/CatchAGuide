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
        <h1 class="header-title">Booking Accepted</h1>
    </div>
    <div class="content" style="padding-bottom:0px;">
        <div class="content-header">
            <p style="font-size:16px;">@lang('profile.booking-dear') <strong>{{$guide->firstname}}</strong>,</p>
            <p> @if(app()->getLocale() == 'en') 
                You have a new booking. You will receive the full price from your guest. Please contact your guest to complete the guiding planning and payment.
                @else 
                Du hast eine neue Buchung. Du erhältst den vollen Preis von Deinem Gast. Bitte nimm Kontakt zu ihm auf, um die Guiding Planung sowie Bezahlung abzuschließen.
                @endif
            </p>
        </div>
    </div>
    <div class="order-details">
        <div class="booking-details">
            <h3 style="font-style:italic">@if(app()->getLocale() == 'en') Customer data @else Kundendaten @endif</h3>
            <p><strong>@lang('profile.fname') : </strong>{{$user->firstname}} {{$user->lastname}}</p>
            <p><strong>@lang('mailing.pNumber') : </strong>{{$booking->phone}}</p>
            <p><strong>@lang('message.modal-email') : </strong>{{$user->email}}</p>
        </div>
        <div class="booking-details" style="margin-top:30px;">
            <h3 style="font-style:italic">@if(app()->getLocale() == 'en') Info about the booked guiding @else Infos zum gebuchten Guiding @endif</h3>
            <p><strong>@if(app()->getLocale() == 'en') Date: @else Termin: @endif</strong> {{ Carbon\Carbon::parse($booking->blocked_event->from)->format('F j, Y') }}</p>
            <p><strong>@if(app()->getLocale() == 'en') Number of guests: @else Personenanzahl: @endif </strong> {{$booking->count_of_users}}</p>
            <p><strong>@if(app()->getLocale() == 'en') Guiding Price: @else Guiding Preis:@endif</strong> <strong class="text-primary">{{$guiding->getGuidingPriceByPerson($booking->count_of_users)}}€</strong></p>
            <p><strong>Extra</strong></p>

            @if($booking->extras)
            <ul>
                @foreach(unserialize($booking->extras) as $extra)
                        <p><strong>{{ucfirst($extra['extra_name'])}}:</strong> {{$extra['extra_price']}}€</p>    
                        <li>
                            <p>Quantity: {{$extra['extra_quantity']}}</p>
                        </li>
                        <li>
                            <p>@if(app()->getLocale() == 'en') Total: @elseif(app()->getLocale() == 'de') Gesamtpreis:@endif: {{$extra['extra_total_price']}}€</p>
                        </li>
                @endforeach
            </ul>
            @endif
            <p><strong>@if(app()->getLocale() == 'en') Total: @elseif(app()->getLocale() == 'de') Gesamtpreis:@endif</strong>
            <strong class="text-primary"> {{$booking->price}}€</strong>
            </p>
        </div>
    </div>
    <div class="content" style="margin-top:5px;">
        <p>@if(app()->getLocale() == 'en') At the end of each month, Catch A Guide will invoice you the percentage fees on all your arranged Guidings. @else Am Ende jedes Monats stellt Dir Catch A Guide die prozentualen Gebühren auf all Deine vermittelten Guidings gesammelt in Rechnung @endif</p>
    </div>
    <div style="text-align:center" style="margin-top: 8px">
        <p>@if(app()->getLocale() == 'en') Please log in for more details @else Bitte logge Dich ein für weitere Details @endif</p>
        <a class="btn-theme" href="https://catchaguide.com/login">@if(app()->getLocale() == 'en') Login @else Zum Login @endif</a>
    </div>
    <div class="footer">
        <p>@lang('profile.booking-regards'),<br>Catch A Guide</p>
    </div>
</div>

</body>
</html>
