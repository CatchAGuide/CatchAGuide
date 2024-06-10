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
        <h1 class="header-title">@lang('profile.gn-request')</h1>
    </div>
    <div class="content" style="padding-bottom:0px;">
        <div class="content-header">
            <p style="font-size:16px;">@lang('profile.booking-hello') <strong>{{$guide->firstname}}</strong>,</p>
            <p>
                @if(app()->getLocale() == 'en')
                we have received a new booking request for your Guiding <a href="{{route('guidings.show',[$guiding->id,$guiding->slug])}}" style="text-decoration: none;font-weight:bold">{{$guiding->title}}</a>. Please respond to the request within 72 hours so that your booking is not automatically cancelled.
                @elseif(app()->getLocale() == 'de')
                eine neue Buchungsanfrage für Dein Guiding <a href="{{route('guidings.show',[$guiding->id,$guiding->slug])}}" style="text-decoration: none;font-weight:bold">{{$guiding->title}}</a> ist bei uns eingegangen. Bitte beantworte die Anfrage innerhalb von 72 Stunden, damit Deine Buchung nicht automatisch storniert wird.
                @endif
            </p>
        </div>
    </div>
    <div style="margin: 20px 0px;">
        <a class="btn-theme" href="{{route('profile.guidebookings')}}">@lang('profile.booking-view')</a>
    </div>
    <div class="order-details">
        <div class="booking-details">
            <h3 style="font-style:italic">@lang('profile.gn-infobooking')</h3>
            <p><strong>@lang('profile.fname') : </strong>{{$user->firstname}}</p>
            <p><strong>@lang('profile.date') : </strong>{{ Carbon\Carbon::parse($booking->blocked_event->from)->format('F j, Y') }}</p>
            <p><strong>@lang('profile.guests') : </strong>{{$booking->count_of_users}}</p>
        </div>
        <div class="booking-details" style="margin-top:30px;">
            <h3 style="font-style:italic">
                @if(app()->getLocale() == 'en')
                About the booking request
                @elseif(app()->getLocale() == 'de')
                Über die Buchungsanfrage
                @endif
            </h3>
            <p><strong>@lang('profile.guidetitle') : </strong><a href="{{route('guidings.show',[$guiding->id,$guiding->slug])}}" style="text-decoration: none;font-weight:bold">{{$guiding->title}}</a></p>
            <p><strong>@lang('profile.location') : </strong>{{$guiding->location}}</p>
            <p><strong>@lang('profile.meetingPoint') :</strong> {{$guiding->meeting_point}}</p>
        </div>
        <hr>

        <div class="overview" style="text-align: right;">
            <table class="table" style="margin-left:auto;font-size:14px;">
                <tbody>
                    <tr>
                        <td scope="row"></td>
                        <td>
                            <strong>
                                @if(app()->getLocale() == 'en')
                                Guiding Price:
                                @elseif(app()->getLocale() == 'de')
                                Angelguiding:
                                @endif
                            </strong>
                        </td>
                        <td><strong class="text-primary">{{$guiding->getGuidingPriceByPerson($booking->count_of_users)}}€</strong></td>
                        
                    </tr>
                    @if($booking->extras)
                        @foreach(unserialize($booking->extras) as $extra)
                        <tr>
                            <td scope="row"></td>
                            <td><strong>{{ucfirst($extra['extra_name'])}}:</strong></td>
                            <td><strong>{{$extra['extra_price']}}€</strong></td>

                        </tr>
                        <tr>
                            <td scope="row"></td>
                            <td><em>Quantity:</em></td>
                            <td><small>{{$extra['extra_quantity']}}</small></td>
                        </tr>
                        <tr>
                            <td scope="row"></td>
                            <td><em>Total:</em></td>
                            <td><strong class="text-primary">{{$extra['extra_total_price']}}€</strong></td>
                        </tr>
                        @endforeach
                    @endif
                    <tr class="text-primary" style="font-size:16px">
                        <td scope="row"></td>
                        <td><strong> 
                            @if(app()->getLocale() == 'en')
                            Total:
                            @elseif(app()->getLocale() == 'de')
                            Gesamtpreis:
                            @endif
                        </strong></td>
                        <td><strong class="text-primary">{{$booking->price}}€</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top:20px;text-align: center;margin-top:30px;margin-bottom:50px;">
    <p>
        @if(app()->getLocale() == 'en')
        Answer booking request:
        @elseif(app()->getLocale() == 'de')
        Buchungsanfrage beantworten:
        @endif
    </p>
        <a class="btn-theme" href="{{route('booking.accept',$booking->token)}}">@lang('profile.gn-accept')</a>
        <a class="btn-theme" href="{{route('booking.reject',$booking->token)}}">@lang('profile.gn-reject')</a>
    </div>
    <div class="content">
        <p>
            @if(app()->getLocale() == 'en')
            If you have any questions or need further assistance, please don't hesitate to reach out to us.
            @elseif(app()->getLocale() == 'de')
            Kontaktiere uns , wenn Du Fragen zu Deiner Buchung hast oder weitere Unterstützung beim Buchungsprozess benötigst.
            @endif
        </p>
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
