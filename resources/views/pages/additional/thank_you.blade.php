@extends('layouts.app')

@section('title', __('message.thank'))

@section('content')

    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

    <style>
        html,body {
            font-family: 'Raleway', sans-serif;
        }
        .thankyou-page ._header {
            background: var(--thm-primary);
            padding: 100px 30px;
            text-align: center;
            background: var(--thm-primary) url(https://codexcourier.com/images/main_page.jpg) center/cover no-repeat;
        }
        .thankyou-page ._header .logo {
            max-width: 200px;
            margin: 0 auto 50px;
        }
        .thankyou-page ._header .logo img {
            width: 100%;
        }
        .thankyou-page ._header h1 {
            font-weight: 800;
            color: white;
            margin: 0;
        }
        .thankyou-page ._body {
            margin: -70px 0 30px;
        }
        .thankyou-page ._body ._box {
            margin: auto;
            max-width: 80%;
            padding: 50px;
            background: white;
            border-radius: 3px;
            box-shadow: 0 0 35px rgba(10, 10, 10,0.12);
            -moz-box-shadow: 0 0 35px rgba(10, 10, 10,0.12);
            -webkit-box-shadow: 0 0 35px rgba(10, 10, 10,0.12);
        }
        .thankyou-page ._body ._box h2 {
            font-size: 32px;
            font-weight: 600;
            color: var(--thm-primary);
        }
        .thankyou-page ._footer {
            text-align: center;
            padding: 50px 30px;
        }

        .thankyou-page ._footer .btn {
            background: var(--thm-primary);
            color: white;
            border: 0;
            font-size: 14px;
            font-weight: 600;
            border-radius: 0;
            letter-spacing: 0.8px;
            padding: 20px 33px;
            text-transform: uppercase;
        }
    </style>
    <!------ Include the above in your HEAD tag ---------->

    <div class="thankyou-page">
        <div class="_header">
            <div class="logo">
                <img src="https://codexcourier.com/images/banner-logo.png" alt="">
            </div>
            <h1>
                @if(app()->getLocale() == 'en')
                    Thank you for your reservation!
                @elseif(app()->getLocale() == 'de')
                    Danke für Deine Reservierung!
                @endif
            </h1>
        </div>
        <div class="_body">
            <div class="_box text-center">
                <p class="fw-bold">
                    @if(app()->getLocale() == 'en')
                        Your booking request has been successfully received by the guide. You will be notified of the guide's response by email within the next 72 hours.
                    @elseif(app()->getLocale() == 'de')
                        Deine Buchungsanfrage ist erfolgreich beim Guide eingegangen. Du wirst innerhalb der nächsten 72 Stunden über die Antwort des Guides per Mail benachrichtigt.
                    @endif
                </p>
                <p class="text-muted">
                    @if(app()->getLocale() == 'en')
                        Contact us at any time if you have any questions about your booking or tour.
                    @elseif(app()->getLocale() == 'de')
                        Kontaktiere uns jederzeit, sollten Fragen zu Deiner Buchung oder Deiner Tour aufkommen.
                    @endif
                </p>
            </div>
        </div>
        <div class="_footer">
            <p> <a href="{{route('additional.contact')}}"style="color:var(--thm-primary) ">@lang('message.cont')</a> </p>
            <a class="btn" href="{{route('welcome')}}">@lang('message.back')</a>
        </div>
    </div>
@endsection
