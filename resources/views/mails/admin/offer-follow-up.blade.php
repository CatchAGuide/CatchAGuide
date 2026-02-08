<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('emails.offer_followup_title') }}</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f5; -webkit-font-smoothing: antialiased;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f4f4f5;">
    <tr>
        <td align="center" style="padding: 20px 16px;">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); overflow: hidden;">
                {{-- Header (same as offer-sendout) --}}
                <tr>
                    <td style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); padding: 20px 24px; text-align: center;">
                        <a href="{{ route('welcome') }}" target="_blank" style="text-decoration: none; display: inline-block; color: #ffffff;">
                            <img src="{{ asset('assets/images/logo/CatchAGuide2_Logo_PNG.png') }}" alt="{{ config('app.name') }}" style="max-width: 160px; height: auto; display: block; margin: 0 auto 12px;" onerror="this.style.display='none';">
                        </a>
                        <h1 style="margin: 12px 0 0; color: #e5e7eb; font-size: 16px; font-weight: 600; letter-spacing: 0.3px;">{{ __('emails.offer_followup_title') }}</h1>
                    </td>
                </tr>
                {{-- Greeting --}}
                <tr>
                    <td style="padding: 16px 20px 12px;">
                        <p style="margin: 0 0 4px; font-size: 14px; color: #0f172a; font-weight: 600;">{{ __('emails.dear') }} {{ $recipient_name }},</p>
                        <p style="margin: 0; font-size: 12px; color: #475569; line-height: 1.5;">{{ __('emails.offer_followup_intro') }}</p>
                    </td>
                </tr>
                {{-- Reminder / context --}}
                <tr>
                    <td style="padding: 0 20px 12px;">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background: #f0f9ff; border-radius: 6px; border: 1px solid #bae6fd;">
                            <tr>
                                <td style="padding: 12px 14px;">
                                    <p style="margin: 0 0 8px; font-size: 12px; color: #0c4a6e; line-height: 1.5;">{{ __('emails.offer_followup_reminder') }}</p>
                                    <p style="margin: 0; font-size: 12px; color: #075985; line-height: 1.5;">{{ __('emails.offer_followup_feedback_ask') }}</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                @if(!empty($offer_summary))
                <tr>
                    <td style="padding: 0 20px 8px;">
                        <p style="margin: 0; font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">{{ __('emails.offer_followup_offer_ref') }}</p>
                        <p style="margin: 4px 0 0; font-size: 12px; color: #334155; line-height: 1.4;">{{ $offer_summary }}</p>
                    </td>
                </tr>
                @endif
                {{-- Closing --}}
                <tr>
                    <td style="padding: 0 20px 14px;">
                        <p style="margin: 0; font-size: 12px; color: #475569; line-height: 1.5;">{{ __('emails.offer_followup_closing') }}</p>
                    </td>
                </tr>
                {{-- CTA --}}
                <tr>
                    <td style="padding: 0 20px 18px; text-align: center;">
                        <a href="{{ route('additional.contact') }}" target="_blank" style="display: inline-block; background-color: #e8604c; color: #ffffff !important; padding: 10px 24px; font-size: 13px; font-weight: 600; text-decoration: none; border-radius: 6px;">{{ __('emails.contact_us') }}</a>
                    </td>
                </tr>
                {{-- Footer (same as offer-sendout) --}}
                <tr>
                    <td style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); padding: 20px 24px; color: #ffffff; text-align: center;">
                        <table role="presentation" cellspacing="0" cellpadding="0" style="margin: 0 auto; text-align: left;">
                            <tr>
                                <td style="vertical-align: bottom; padding-right: 32px;">
                                    <img src="{{ asset('assets/images/logo/CatchAGuide2_Logo_PNG.png') }}" alt="{{ config('app.name') }}" style="max-width: 100px; height: auto; display: block;" onerror="this.style.display='none';">
                                    <span style="color: #ffffff; font-size: 16px; font-weight: 700; letter-spacing: 0.3px;">{{ config('app.name') }}</span>
                                </td>
                                <td style="vertical-align: bottom; font-size: 13px; line-height: 1.6; color: #e5e7eb;">
                                    @if(config('mail.from.address'))
                                    <p style="margin: 0 0 4px;"><a href="mailto:{{ config('mail.from.address') }}" style="color: #ffffff; text-decoration: none;">{{ config('mail.from.address') }}</a></p>
                                    @endif
                                    @if(env('CONTACT_NUM'))
                                    <p style="margin: 0;"><a href="tel:{{ env('CONTACT_NUM') }}" style="color: #ffffff; text-decoration: none;">{{ env('CONTACT_NUM') }}</a></p>
                                    @endif
                                </td>
                            </tr>
                        </table>
                        <p style="margin: 20px 0 0; color: #9ca3af; font-size: 12px;">© {{ date('Y') }} {{ config('app.name') }}</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
