<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body style="margin: 0; padding: 0;">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td>
            <table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
                <tr>
                    <td align="center" bgcolor="#ffffff" style="padding: 40px 0 30px 0;">
                        <img src="{{asset('assets/images/logo.png')}}" alt="Catchaguide" width="150" style="display: block;" />
                    </td>
                </tr>
                <tr>
                    <td bgcolor="#ffffff" style="padding: 40px 30px 40px 30px;">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td style="color: #153643; font-family: Arial, sans-serif; font-size: 24px;">
                                    <b></b>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 20px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                    @lang('mailing.hello')Hallo {{$guide->firstname}}, <br><br>
                                    @lang('mailing.youHvae')<br>
                                    <h3>@lang('mailing.custData')Kundendaten:</h3>
                                    <ul>
                                        <li>Name: {{$user->firstname}} {{$user->lastname}}</li>
                                        <li>Telefon: {{$user->phone}}</li>
                                        <li>Email: {{$user->email}}</li>
                                    </ul>
                                    <h3>Infos zum gebuchten Guiding:</h3>
                                    <ul>
                                        <li>Termin: {{ $booking->getFormattedBookingDate('d-m-Y') }}</li>
                                        <li>Guiding: {{$guiding->title}}</li>
                                        <li>Personenanzahl: {{$booking->count_of_users}} Gäste</li>
                                        <li>Guiding-Preis: {{two($booking->price)}} €</li>
                                    </ul>
                                    <p>Am Ende jedes Monats stellt Dir Catch A Guide die prozentualen Gebühren auf all Deine vermittelten Guidings gesammelt in Rechnung.</p>
                                    <p>Die Zahlung steht noch aus. Wir informieren dich, sobald sie durchgeführt worden ist.</p>
                                    <p>Bitte logge Dich ein für weitere Details.</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 20px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                    <a href="{{route('login')}}">Zum Login</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td bgcolor="#ffffff" style="padding: 30px 30px 30px 30px;">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td style="color: black; font-family: Arial, sans-serif; font-size: 14px;">
                                    &reg; Catchaguide<br/>
                                    <a href="{{route('login')}}" style="color: black;"><font color="black">Login</font></a> auf Catchaguide
                                </td>
                                <td align="right">
                                    <table border="0" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td>
                                                <a href="{{url('/')}}">
                                                    <img src="{{asset('assets/images/logo.png')}}" alt="Logo Klein" width="90" style="display: block;" border="0" />
                                                </a>
                                            </td>

                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
