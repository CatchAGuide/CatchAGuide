@extends('layouts.app')

@section('title', 'Dankeschön')

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
            font-size: 65px;
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
            <h1>Danke für deine Bestellung</h1>
        </div>
        <div class="_body">
            <div class="_box text-center">
                {{--
         <h2>
             @if($status == 'cancelled' || $status == 'expired')
                 <strong>Leider konnte dein Zahlung nicht abgeschlossen werden.</strong>
             @else
                 <strong>Vielen Dank dass du eine Buchung abgeschlossen hast!</strong>
             @endif
         </h2>
         <p>
             Deine Buchungs-ID lautet : {{ $bookingid }}
         </p>
         <p>Der Status deiner Bezahlung ist: {{ $status }}

             @switch($status)
                 @case('pending')
                 <p>
                     Deine Zahlung wird bearbeitet. Bitte habe etwas Geduld.
                 </p>
                 <p>
                     Den aktuellen Stand deiner Zahlung kannst du in deinem Profil unter <a href="{{route('profile.bookings')}}">Buchungen</a> einsehen.
                 </p>
                     @break
                 @case('completed')
                     <p>Deine Zahlung haben wir entgegengenommen.</p>
                     @break
                 @case('reserved')
                     <p>Deine Zahlung haben wir vorgemerkt.</p>
                     <p>
                         Den aktuellen Stand deiner Zahlung kannst du in deinem Profil unter <a href="{{route('profile.bookings')}}">Buchungen</a> einsehen.
                     </p>
                     @break
                 @case('cancelled')
                     <p>Der Bezahlvorgang wurde abgebrochen.</p>
                     <p>Der ausgewählte Termin wurde wieder freigegeben. Bitte starte die Buchung neu.</p>
                     @break
                  @case('failed')
                     <p>Der Bezahlvorgang wurde abgebrochen.</p>
                     <p>Der ausgewählte Termin wurde wieder freigegeben. Bitte starte die Buchung neu.</p>
                     @break
                 @case('expired')
                     <p>Der Bezahlvorgang wurde nicht abgeschlossen.</p>
                 <p>Der ausgewählte Termin wurde wieder freigegeben. Bitte starte die Buchung neu.</p>
                     @break
                 @default
                     <p>Etwas ist schief gegangen mit deiner Bezahlung. Bitte versuche es noch einmal.</p>
             @endswitch
     --}}
                <p>
                    Falls mit deiner Buchung ein Problem sein sollte, kontaktiere uns bitte.
                </p>

{{--                @if($status == 'cancelled' || $status == 'expired')--}}
{{--                    <form action="{{route('transaction')}}" method="GET" enctype="multipart/form-data">--}}
{{--                        <input type="hidden" name="bookingid" value="{{ $bookingid }}" />--}}
{{--                        <button class="thm-btn" type="submit">Zahlung neu starten</button>--}}
{{--                    </form>--}}
{{--                @endif--}}
            </div>
        </div>
        <div class="_footer">
            <p> <a id="contact-footer" href="{{route('additional.contact')}}"style="color:var(--thm-primary) ">Kontakt</a> </p>
            <a class="btn" href="{{route('welcome')}}">Zurück zu Startseite</a>
        </div>
    </div>
@endsection
