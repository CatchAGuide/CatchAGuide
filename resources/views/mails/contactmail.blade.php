@php
    $hasSourceSection = !empty($camp_id)
        || (!empty($source_id) && empty($camp_id))
        || !empty($preferred_date)
        || !empty($number_of_persons)
        || !empty($source_title)
        || !empty($source_type);
    $formattedDate = null;
    if (!empty($preferred_date)) {
        try {
            $formattedDate = \Carbon\Carbon::parse($preferred_date)->format('d.m.Y');
        } catch (\Throwable $e) {
            $formattedDate = $preferred_date;
        }
    }
    $sourceTypeLabel = null;
    if (!empty($source_type)) {
        $sourceTypeLabel = \App\Models\ContactSubmission::sourceTypeLabel($source_type);
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('emails.contact_admin.title') }}</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f5; -webkit-font-smoothing: antialiased;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f4f4f5;">
    <tr>
        <td align="center" style="padding: 20px 16px;">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); overflow: hidden;">
                {{-- Header --}}
                <tr>
                    <td style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); padding: 20px 24px; text-align: center;">
                        <a href="{{ route('welcome') }}" target="_blank" style="text-decoration: none; display: inline-block; color: #ffffff;">
                            <img src="{{ asset('assets/images/logo/CatchAGuide2_Logo_PNG.png') }}" alt="{{ config('app.name') }}" style="max-width: 160px; height: auto; display: block; margin: 0 auto 12px;" onerror="this.style.display='none';">
                        </a>
                        <h1 style="margin: 12px 0 0; color: #e5e7eb; font-size: 16px; font-weight: 600; letter-spacing: 0.3px;">{{ __('emails.contact_admin.title') }}</h1>
                    </td>
                </tr>
                {{-- Greeting --}}
                <tr>
                    <td style="padding: 16px 20px 12px;">
                        <p style="margin: 0 0 4px; font-size: 14px; color: #0f172a; font-weight: 600;">{{ __('emails.contact_admin.greeting') }}</p>
                        <p style="margin: 0; font-size: 12px; color: #475569; line-height: 1.5;">{{ __('emails.contact_admin.intro') }}</p>
                    </td>
                </tr>
                {{-- Contact details --}}
                <tr>
                    <td style="padding: 0 20px 12px;">
                        <p style="margin: 0 0 8px; font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">{{ __('emails.contact_admin.section_contact') }}</p>
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border: 1px solid #e5e7eb; border-radius: 6px; background-color: #fafafa;">
                            <tr>
                                <td style="padding: 12px 14px;">
                                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="font-size: 13px; color: #334155;">
                                        <tr>
                                            <td style="padding: 5px 12px 5px 0; font-weight: 600; color: #64748b; vertical-align: top; width: 38%;">{{ __('emails.contact_admin.name') }}</td>
                                            <td style="padding: 5px 0; color: #0f172a;">{{ $name }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 5px 12px 5px 0; font-weight: 600; color: #64748b; vertical-align: top;">{{ __('emails.contact_admin.email') }}</td>
                                            <td style="padding: 5px 0;"><a href="mailto:{{ $email }}" style="color: #e8604c; text-decoration: none;">{{ $email }}</a></td>
                                        </tr>
                                        @if($phone)
                                        <tr>
                                            <td style="padding: 5px 12px 5px 0; font-weight: 600; color: #64748b; vertical-align: top;">{{ __('emails.contact_admin.phone') }}</td>
                                            <td style="padding: 5px 0; color: #0f172a;">{{ trim(($phone_country_code ?? '') . ' ' . $phone) }}</td>
                                        </tr>
                                        @endif
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                {{-- Source & booking --}}
                @if($hasSourceSection)
                <tr>
                    <td style="padding: 0 20px 12px;">
                        <p style="margin: 0 0 8px; font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">{{ __('emails.contact_admin.section_source') }}</p>
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border: 1px solid #e5e7eb; border-radius: 6px; background-color: #f0f9ff;">
                            <tr>
                                <td style="padding: 12px 14px;">
                                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="font-size: 13px; color: #334155;">
                                        @if($sourceTypeLabel)
                                        <tr>
                                            <td style="padding: 5px 12px 5px 0; font-weight: 600; color: #64748b; vertical-align: top; width: 38%;">{{ __('emails.contact_admin.source_type') }}</td>
                                            <td style="padding: 5px 0; color: #0f172a;">{{ $sourceTypeLabel }}</td>
                                        </tr>
                                        @endif
                                        @if(!empty($camp_id))
                                        <tr>
                                            <td style="padding: 5px 12px 5px 0; font-weight: 600; color: #64748b; vertical-align: top;">{{ __('emails.contact_admin.camp_id') }}</td>
                                            <td style="padding: 5px 0; color: #0f172a;">{{ $camp_id }}</td>
                                        </tr>
                                        @endif
                                        @if(!empty($source_id) && empty($camp_id))
                                        <tr>
                                            <td style="padding: 5px 12px 5px 0; font-weight: 600; color: #64748b; vertical-align: top;">{{ __('emails.contact_admin.source_id') }}</td>
                                            <td style="padding: 5px 0; color: #0f172a;">{{ $source_id }}</td>
                                        </tr>
                                        @endif
                                        @if(!empty($source_title))
                                        <tr>
                                            <td style="padding: 5px 12px 5px 0; font-weight: 600; color: #64748b; vertical-align: top;">{{ __('emails.contact_admin.source_title') }}</td>
                                            <td style="padding: 5px 0; color: #0f172a; font-weight: 600;">{{ $source_title }}</td>
                                        </tr>
                                        @endif
                                        @if($formattedDate)
                                        <tr>
                                            <td style="padding: 5px 12px 5px 0; font-weight: 600; color: #64748b; vertical-align: top;">{{ __('emails.contact_admin.requested_date') }}</td>
                                            <td style="padding: 5px 0; color: #0f172a;">{{ $formattedDate }}</td>
                                        </tr>
                                        @endif
                                        @if(!empty($number_of_persons))
                                        <tr>
                                            <td style="padding: 5px 12px 5px 0; font-weight: 600; color: #64748b; vertical-align: top;">{{ __('emails.contact_admin.number_of_persons') }}</td>
                                            <td style="padding: 5px 0; color: #0f172a;">{{ $number_of_persons }}</td>
                                        </tr>
                                        @endif
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                @endif
                {{-- Message --}}
                <tr>
                    <td style="padding: 0 20px 14px;">
                        <p style="margin: 0 0 8px; font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">{{ __('emails.contact_admin.section_message') }}</p>
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border: 1px solid #e5e7eb; border-radius: 6px; background-color: #fff;">
                            <tr>
                                <td style="padding: 12px 14px;">
                                    <p style="margin: 0; font-size: 13px; color: #334155; line-height: 1.6; white-space: pre-wrap;">{{ $contact_message ?? $description }}</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                {{-- CTA --}}
                <tr>
                    <td style="padding: 0 20px 18px; text-align: center;">
                        <a href="{{ route('admin.contact-requests.index') }}" target="_blank" style="display: inline-block; background-color: #e8604c; color: #ffffff !important; padding: 10px 24px; font-size: 13px; font-weight: 600; text-decoration: none; border-radius: 6px; margin-right: 8px;">{{ __('emails.contact_admin.view_requests') }}</a>
                        <a href="{{ route('login') }}" target="_blank" style="display: inline-block; background-color: #1a1a2e; color: #ffffff !important; padding: 10px 24px; font-size: 13px; font-weight: 600; text-decoration: none; border-radius: 6px;">{{ __('emails.contact_admin.login') }}</a>
                    </td>
                </tr>
                {{-- Footer --}}
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
                                    @if(config('cag.contact_num'))
                                    <p style="margin: 0;"><a href="tel:{{ config('cag.contact_num') }}" style="color: #ffffff; text-decoration: none;">{{ config('cag.contact_num') }}</a></p>
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
