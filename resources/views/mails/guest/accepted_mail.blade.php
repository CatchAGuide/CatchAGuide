<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Booking Request Accepted</title>
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
        <h1 class="header-title">
            @if(app()->getLocale() == 'en')
            Booking request accepted 
            @elseif(app()->getLocale() == 'de')
            Buchungsanfrage angenommen
            @endif
        </h1>
    </div>
    <div class="content" style="padding-bottom:0px;">
        <div class="content-header">
            <p style="font-size:16px;">@lang('profile.booking-hello') <strong>{{$user->firstname}}</strong>,</p>
            <p>
                @if(app()->getLocale() == 'en')
                we are happy to inform you that your booking request has been accepted. 
                @elseif(app()->getLocale() == 'de')
                wir freuen uns, Dir mitteilen zu können, dass Deine Buchungsanfrage angenommen wurde. 
                @endif
            </p>
            <p>
                @if(app()->getLocale() == 'en')
                Your guide has confirmed your booking request for the selected date. We wish you a lot of fun and success on your tour.
                @elseif(app()->getLocale() == 'de')
                Dein Guide hat Deine Buchungsanfrage zum ausgewählten Datum bestätigt. Wir wünschen viel Spaß und Erfolg bei Deiner Tour.
                @endif
            </p>
            <p>
                @if(app()->getLocale() == 'en')
                Your guiding will take place on {{ Carbon\Carbon::parse($booking->book_date)->format('F j, Y') }}. Please contact your guide to plan your guiding:
                @elseif(app()->getLocale() == 'de')
                Deine Guiding findet am {{ Carbon\Carbon::parse($booking->book_date)->format('F j, Y') }} statt. Bitte nimm Kontakt mit Deinem Guide auf, um Dein Guiding zu planen:
                @endif
            </p>
            <div class="order-details">
                <div style="margin-top:10px;">
                    <p>Name: {{$guiding->user->firstname}} {{$guiding->user->lastname}}</p>
                    <p>@lang('mailing.pNumber'): {{$guiding->user->information['phone']}}</p>
                    <p>@if(app()->getLocale() == 'en') Email: @else E-mail: @endif {{$guiding->user->email}}</p>
                </div>
                <div style="margin-top:10px;">
                    <h3 style="font-style:italic">@if(app()->getLocale() == 'en') Your Guiding: @else Dein Guiding: @endif </h3>
                </div>
                <div style="margin-top:5px;">
                    <p>@if(app()->getLocale() == 'en') Date: @else Datum: @endif  {{ Carbon\Carbon::parse($booking->book_date)->format('F j, Y') }}</p>
                    <p>@if(app()->getLocale() == 'en') Guests: @else Gäste: @endif  {{$booking->count_of_users}}</p>
                    <p>@if(app()->getLocale() == 'en') Duration: @else Dauer: @endif  {{ $guiding->duration }}</p>
                    <p>@if(app()->getLocale() == 'en') Price: @else Preis: @endif <strong class="text-primary">€{{two($booking->price)}} </strong></p>
                </div>
                <div style="margin-top:10px;">
                    <p><strong>@if(app()->getLocale() == 'en') Payment methods: @else Bezahlmethoden: @endif</strong></p>
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
                </div>
            </div>

            <div style="margin-top:20px;">
                <p>
                    @if(app()->getLocale() == 'en')
                    Contact us if you have any questions about your booking or need further assistance with the booking process.
                    @elseif(app()->getLocale() == 'de')
                    Kontaktiere uns , wenn Du Fragen zu Deiner Buchung hast oder weitere Unterstützung beim Buchungsprozess benötigst.
                    @endif
                </p>
                <div style="text-align:center">
                    <a class="btn-theme" href="https://catchaguide.com/contact">@lang('profile.booking-contactus')</a>
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
