<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Activate Your Account</title>
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
        .footer {
            text-align: center;
            padding-top: 20px;
            color: #777777;
        }
        .btn-theme {
            background-color: #e8604c;
            padding: 10px 20px;
            color: #fff !important;
            border: 0;
            text-decoration: none;
            margin-top: 30px;
            display: inline-block;
            border-radius: 5px;
        }
        p {
            font-size: 14px;
            line-height: 1.6;
        }
        .text-primary {
            color: #e8604c;
        }
        .credentials-box {
            background-color: #f8f8f8;
            border: 1px solid #ddd;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
    </style>
</head>
<body bgcolor="#e8604c" style="background-color: #e8604c">

<div class="container">
    <div class="header">
        <img class="logo" src="https://catchaguide.com/assets/images/logo_mobil.jpg" alt="Catchaguide Logo">
        <h1>
            @if(app()->getLocale() == 'en')
                Activate Your Catch A Guide Account
            @elseif(app()->getLocale() == 'de')
                Aktiviere Dein Catch A Guide Konto
            @endif
        </h1>
    </div>

    <div class="content">
        <p style="font-size:16px;">@lang('profile.booking-hello') <strong>{{$user->firstname}}</strong>,</p>
        
        <p>
            @if(app()->getLocale() == 'en')
                To enhance your experience with Catch A Guide, we've prepared an account for you. By activating your account, you'll gain access to:
                <ul>
                    <li>Personalized booking management</li>
                    <li>Exclusive fishing spots and guides</li>
                    <li>Special offers and updates</li>
                </ul>
            @elseif(app()->getLocale() == 'de')
                Um Dir ein besseres Erlebnis mit Catch A Guide zu bieten, haben wir ein Konto für Dich vorbereitet. Durch die Aktivierung Deines Kontos erhältst Du Zugang zu:
                <ul>
                    <li>Persönliche Buchungsverwaltung</li>
                    <li>Exklusive Angelplätze und Guides</li>
                    <li>Spezielle Angebote und Updates</li>
                </ul>
            @endif
        </p>

        <div class="credentials-box">
            <p>
                @if(app()->getLocale() == 'en')
                    <strong>Your Account Details:</strong>
                    <br>Email: {{$user->email}}
                    <br>Temporary Access Code: {{$tempPassword}}
                @elseif(app()->getLocale() == 'de')
                    <strong>Deine Kontodetails:</strong>
                    <br>Email: {{$user->email}}
                    <br>Vorläufiger Zugangscode: {{$tempPassword}}
                @endif
            </p>
        </div>

        <p>
            @if(app()->getLocale() == 'en')
                <strong>Next Steps:</strong>
                <br>1. Log in using your email and temporary access code
                <br>2. Set your personal password
            @elseif(app()->getLocale() == 'de')
                <strong>Nächste Schritte:</strong>
                <br>1. Melde Dich mit Deiner E-Mail und dem vorläufigen Zugangscode an
                <br>2. Lege Dein persönliches Passwort fest
            @endif
        </p>

        <div style="text-align:center">
            <a class="btn-theme" href="{{ route('login') }}">
                @if(app()->getLocale() == 'en')
                    Activate Account
                @elseif(app()->getLocale() == 'de')
                    Konto Aktivieren
                @endif
            </a>
        </div>
    </div>

    <div class="footer">
        <p>
            @if(app()->getLocale() == 'en')
                If you didn't request this account, you can safely ignore this email.
            @elseif(app()->getLocale() == 'de')
                Falls Du dieses Konto nicht angefordert hast, kannst Du diese E-Mail ignorieren.
            @endif
        </p>
        <p>@lang('profile.booking-regards'),<br>Catch A Guide</p>
    </div>
</div>

</body>
</html>
