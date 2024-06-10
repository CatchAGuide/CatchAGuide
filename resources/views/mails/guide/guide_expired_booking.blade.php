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
        <h1 class="header-title">@lang('profile.gd-cancelled')</h1>
    </div>
    <div class="content" style="padding-bottom:0px;">
        <div class="content-header">
            <p style="font-size:16px;">@lang('profile.booking-dear') <strong>{{$guide->firstname}}</strong>,</p>
            @if(app()->getLocale() == 'en')
            <p>You have let the {{$booking->created_at->diffInHours($booking->expires_at)}} hours to accept or reject the booking request elapse and therefore we regret to inform you that the booking request is automatically rejected. We ask for your understanding, as we need to allow the booker to plan his gudings.</p>
            @elseif(app()->getLocale() == 'de')
            <p>Sie haben die {{$booking->created_at->diffInHours($booking->expires_at)}} Stunden zum Annehmen oder Ablehnen der Buchungsanfrage verstreichen lassen und daher müssen wir Ihnen leider mitteilen, dass die Buchungsanfrage automatisch abgelehnt. Wir bitten um Verständnis, da wir dem Buchenden eine Planbarkeit seines Gudings ermöglichen müssen.</p>
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
