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
                            <tr>
                                <td style="padding: 20px 0 0 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                    Hallo  {{$user->firstname}}, <br><br>
                                    vielen Dank für Deine Buchung über Catch A Guide!<br>
                                    Deine Guiding findet am
                                    {{ Carbon\Carbon::parse($booking->blocked_event->from)->format('d.m.Y') }}
                                    statt. Bitte trete mit Deinem Guide in Kontakt, um Dein Guiding zu planen.<br> <br>
                                    Infos zum Guiding: <br>
                                    <h3>{{$guiding->title}}</h3>

                                    <table class="table">
                                        <thead>
                                        <tr width="600">
                                            <th scope="col" width="150">Datum</th>
                                            <th scope="col" width="100">Gäste</th>
                                            <th scope="col" width="100">Dauer</th>
                                            <th scope="col" width="150">Du zahlst</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <th scope="row" width="150">
                                            {{ Carbon\Carbon::parse($booking->book_date)->format('d.m.Y') }}
                                            </th>
                                            <td width="100" align="center">{{$booking->count_of_users}}</td>
                                            <td width="100" align="center">
                                                {{ $guiding->duration }}
                                                @if($guiding->duration > 1)
                                                    Stunden
                                                @else
                                                    Stunde
                                                @endif
                                            </td>
                                            <td width="150" align="center">
                                            {{$booking->price}} €
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>

                                    @if($guiding->additional_information)
                                        <br>Sonstiges:</br>
                                        {!! $guiding->additional_information !!}
                                    @endif

                                    {!! $guiding->description !!}


                                </td></tr>
                            <tr><td style="padding: 20px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                    <ul>
                                        <li>
                                            <div class="icon">
                                                <i class="fa fa-check"></i>
                                            </div>
                                            <div class="text">
                                                <p><b>Zielfisch/e:</b>
                                                    {{$guiding->threeTargets()}}
                                                    {{$guiding->target_fish_sonstiges ? $guiding->target_fish_sonstiges : ""}}
                                                </p>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="icon">
                                                <i class="fa fa-check"></i>
                                            </div>
                                            <div class="text">
                                                <p><b>Angel Art:</b>
                                                    {{$guiding->fishing_type}}
                                                </p>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="icon">
                                                <i class="fa fa-check"></i>
                                            </div>
                                            <div class="text">
                                                <p><b>Technik:</b>
                                                    {{$guiding->threeMethods()}}
                                                    {{$guiding->methods_sonstiges ? $guiding->methods_sonstiges : ""}}
                                                </p>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="icon">
                                                <i class="fa fa-check"></i>
                                            </div>
                                            <div class="text">
                                                <p><b>Ufer / Boot:</b>
                                                    {{$guiding->fishing_from}}
                                                </p>
                                            </div>
                                        </li>
                                        @if($guiding->meeting_point)
                                            <li>
                                                <div class="icon">
                                                    <i class="fa fa-check"></i>
                                                </div>
                                                <div class="text">
                                                    <p><b>Treffpunkt:</b>
                                                        {{$guiding->meeting_point}}
                                                    </p>
                                                </div>
                                            </li>
                                        @endif

                                    </ul>


                                    <h3>Bei Fragen zu deinem Guiding wende dich bitte direkt an deinen Guide:</h3>
                                    Infos zum Guide:
                                    <ul>
                                        <li>Name: <strong>{{$guiding->user->firstname}}</strong></li>
                                        <li><strong>Telefonnummer: </strong>{{$guiding->user->information['phone']}}</li>
                                        <li><strong>Email: </strong>{{$guiding->user->email}}</li>
                                    </ul>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 20px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                    <span style="color: red !important; font-size: 12px !important;">*Bitte vergewissere Dich selbst, dass du die gültige Angelerlaubnis für das jeweilige Land, Gebiet oder Gewässer besitzt, an dem Du angeln gehen möchtest. Catch A Guide übernimmt keine Haftung möglicher Personen- und/oder Sachschaden während eines Guidings.</span>
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
