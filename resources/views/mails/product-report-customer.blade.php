@php
    $copy = $copyNamespace ?? 'emails.product_report_customer';
    $sourceTitle = $report->getSourceTitle();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __($copy . '.title') }}</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f5; -webkit-font-smoothing: antialiased;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f4f4f5;">
    <tr>
        <td align="center" style="padding: 20px 16px;">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); overflow: hidden;">
                <tr>
                    <td style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); padding: 20px 24px; text-align: center;">
                        <a href="{{ route('welcome') }}" target="_blank" style="text-decoration: none; display: inline-block; color: #ffffff;">
                            <img src="{{ asset('assets/images/logo/CatchAGuide2_Logo_PNG.png') }}" alt="{{ config('app.name') }}" style="max-width: 160px; height: auto; display: block; margin: 0 auto 12px;" onerror="this.style.display='none';">
                        </a>
                        <h1 style="margin: 12px 0 0; color: #e5e7eb; font-size: 16px; font-weight: 600; letter-spacing: 0.3px;">{{ __($copy . '.title') }}</h1>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 16px 20px 12px;">
                        <p style="margin: 0 0 4px; font-size: 14px; color: #0f172a; font-weight: 600;">{{ __($copy . '.greeting', ['name' => $report->name]) }}</p>
                        <p style="margin: 0 0 8px; font-size: 12px; color: #475569; line-height: 1.5;">{{ __($copy . '.intro') }}</p>
                        <p style="margin: 0; font-size: 12px; color: #64748b; line-height: 1.5;">{{ __($copy . '.intro_secondary') }}</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0 20px 12px;">
                        <p style="margin: 0 0 8px; font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">{{ __($copy . '.section_report') }}</p>
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border: 1px solid #e5e7eb; border-radius: 6px; background-color: #fafafa;">
                            <tr>
                                <td style="padding: 12px 14px;">
                                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="font-size: 13px; color: #334155;">
                                        <tr>
                                            <td style="padding: 5px 12px 5px 0; font-weight: 600; color: #64748b; vertical-align: top; width: 38%;">{{ __($copy . '.reason') }}</td>
                                            <td style="padding: 5px 0; color: #0f172a;">{{ $report->reason_label }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 5px 12px 5px 0; font-weight: 600; color: #64748b; vertical-align: top;">{{ __($copy . '.reported_url') }}</td>
                                            <td style="padding: 5px 0;"><a href="{{ $report->reported_url }}" target="_blank" style="color: #e8604c; text-decoration: none; word-break: break-all;">{{ $report->reported_url }}</a></td>
                                        </tr>
                                        @if($sourceTitle)
                                        <tr>
                                            <td style="padding: 5px 12px 5px 0; font-weight: 600; color: #64748b; vertical-align: top;">{{ __($copy . '.listing') }}</td>
                                            <td style="padding: 5px 0; color: #0f172a; font-weight: 600;">{{ $sourceTitle }}</td>
                                        </tr>
                                        @endif
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0 20px 14px;">
                        <p style="margin: 0 0 8px; font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">{{ __($copy . '.section_message') }}</p>
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border: 1px solid #e5e7eb; border-radius: 6px; background-color: #fff;">
                            <tr>
                                <td style="padding: 12px 14px;">
                                    <p style="margin: 0; font-size: 13px; color: #334155; line-height: 1.6; white-space: pre-wrap;">{{ $report->description }}</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0 20px 18px; text-align: center;">
                        <a href="{{ route('welcome') }}" target="_blank" style="display: inline-block; background-color: #e8604c; color: #ffffff !important; padding: 10px 24px; font-size: 13px; font-weight: 600; text-decoration: none; border-radius: 6px; margin-right: 8px;">{{ __($copy . '.visit_website') }}</a>
                        <a href="{{ route('additional.contact') }}" target="_blank" style="display: inline-block; background-color: #1a1a2e; color: #ffffff !important; padding: 10px 24px; font-size: 13px; font-weight: 600; text-decoration: none; border-radius: 6px;">{{ __($copy . '.contact_us') }}</a>
                    </td>
                </tr>
                <tr>
                    <td style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); padding: 20px 24px; color: #ffffff; text-align: center;">
                        <p style="margin: 0; color: #9ca3af; font-size: 12px;">© {{ date('Y') }} {{ config('app.name') }} · {{ __('emails.best_regards') }} {{ __('emails.catchaguide_team') }}</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
