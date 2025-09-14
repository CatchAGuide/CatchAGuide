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
    </div>
    <div class="content" style="padding-bottom:0px;">
        <p style="font-size:16px;">@if($myCurrentLocale == 'de') Hallo @else Hello @endif <strong>{{$name}}</strong>,</p>
        <div class="content-header">
            @if($myCurrentLocale == 'de')
            <p>wir freuen uns über Dein Interesse an einem Angelausflug über Catch A Guide. <br> Unser Team arbeitet bereits daran, Deine Anfrage zu bearbeiten und schnellstmöglich das passende Erlebnis für Dich zu finden.</p>
            <p>Wir melden uns spätestens innerhalb der nächsten 48 Stunden bei Dir für die ersten Informationen. <br> Falls Du in der Zwischenzeit Fragen hast oder weitere Informationen benötigst, zögere nicht, uns zu kontaktieren. <br> Wir stehen Dir gerne jederzeit zur Verfügung! </p>
            @elseif($myCurrentLocale == 'en')
            <p>Thank you for your interest in a fishing trip with Catch A Guide. <br> Our team is already working on processing your request and finding the right experience for you as quickly as possible.</p>
            <p>We will get back to you within the next 48 hours at the latest with initial information. <br> If you have any questions or require further information in the meantime, please do not hesitate to contact us. <br> We are always at your disposal!</p>
            @endif

        </div>
    </div>

    <div class="content">

    </div>

    <div class="footer">
        @if($myCurrentLocale == 'de') 
        <p>Viele Grüße & Petri,<br> Dein Catch A Guide Team</p>
        @else
        <p>Best regards,<br> Your Catch A Guide Team</p>
        @endif
    </div>
</div>

</body>
</html>
