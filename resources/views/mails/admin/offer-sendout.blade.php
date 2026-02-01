<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('emails.offer_sendout_title') }}</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f5; -webkit-font-smoothing: antialiased;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f4f4f5;">
    <tr>
        <td align="center" style="padding: 24px 16px;">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); overflow: hidden;">
                {{-- Header --}}
                <tr>
                    <td style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); padding: 20px 24px; text-align: center;">
                        <a href="{{ route('welcome') }}" target="_blank" style="text-decoration: none; display: inline-block; color: #ffffff;">
                            <img src="{{ asset('assets/images/logo/CatchAGuide2_Logo_PNG.png') }}" alt="{{ config('app.name') }}" style="max-width: 160px; height: auto; display: block; margin: 0 auto 12px;" onerror="this.style.display='none';">
                        </a>
                        <h1 style="margin: 12px 0 0; color: #e5e7eb; font-size: 16px; font-weight: 600; letter-spacing: 0.3px;">{{ __('emails.offer_sendout_title') }}</h1>
                    </td>
                </tr>
                {{-- Greeting --}}
                <tr>
                    <td style="padding: 20px 24px 16px;">
                        <p style="margin: 0 0 6px; font-size: 15px; color: #0f172a; font-weight: 600;">{{ __('emails.dear') }} {{ $recipient_name }},</p>
                        <p style="margin: 0; font-size: 14px; color: #475569; line-height: 1.55;">{{ __('emails.offer_sendout_intro') }} {{ __('emails.offer_sendout_intro_secondary') }}</p>
                    </td>
                </tr>
                {{-- Catalog: one block per offer --}}
                @foreach($offers ?? [] as $index => $offer)
                <tr>
                    <td style="padding: 0 24px {{ $loop->last ? '20px' : '12px' }};">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border: 1px solid #e2e8f0; border-radius: 8px; background-color: #fafafa; margin-bottom: {{ $loop->last ? 0 : 12 }}px;">
                            <tr>
                                <td style="padding: 0;">
                                    {{-- Camp as highlight (hero of this offer) --}}
                                    @if(!empty($offer['camp']))
                                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); border-radius: 8px 8px 0 0;">
                                        <tr>
                                            <td style="padding: 16px 16px 14px;">
                                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                                    <tr>
                                                        <td style="vertical-align: top;">
                                                            <h2 style="margin: 0 0 6px; font-size: 19px; font-weight: 700; color: #ffffff; letter-spacing: 0.2px; line-height: 1.3;">
                                                                <a href="{{ route('vacations.show', $offer['camp']->slug) }}" target="_blank" style="color: #ffffff; text-decoration: none;">{{ $offer['camp']->title }}</a>
                                                            </h2>
                                                            @if(!empty($offer['camp_location']))
                                                            <p style="margin: 0; font-size: 13px; color: #cbd5e1; line-height: 1.4;">
                                                                <span style="display: inline-block; margin-right: 4px;">📍</span>{{ $offer['camp_location'] }}
                                                            </p>
                                                            @endif
                                                        </td>
                                                        @if(!empty($offer['price']))
                                                        <td style="vertical-align: top; text-align: right; white-space: nowrap; padding-left: 12px;">
                                                            <div style="background: rgba(232, 96, 76, 0.15); border: 1px solid rgba(232, 96, 76, 0.3); border-radius: 6px; padding: 8px 12px; display: inline-block;">
                                                                <div style="font-size: 20px; color: #ffffff; font-weight: 700; line-height: 1;">€ {{ $offer['price'] }}</div>
                                                            </div>
                                                        </td>
                                                        @endif
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                    @else
                                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f1f5f9; border-radius: 8px 8px 0 0;">
                                        <tr>
                                            <td style="padding: 10px 16px;">
                                                <h2 style="margin: 0; font-size: 15px; font-weight: 600; color: #334155;">{{ __('emails.offer_sendout_offer') }} {{ $index + 1 }}</h2>
                                            </td>
                                        </tr>
                                    </table>
                                    @endif
                                    {{-- Offer details (compact) --}}
                                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding: 12px 16px 14px;">
                                        <tr>
                                            <td>
                                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="font-size: 13px; color: #111827;">
                                                    @if(isset($offer['accommodations']) && $offer['accommodations']->isNotEmpty())
                                                    <tr>
                                                        <td style="padding: 6px 0 4px; font-size: 10px; color: #64748b; text-transform: uppercase; letter-spacing: 0.6px; font-weight: 600;">{{ __('emails.offer_sendout_accommodations') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding: 0 0 12px;">
                                                            @foreach($offer['accommodations'] as $acc)
                                                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background: #fef3f2; border-left: 3px solid #e8604c; margin-bottom: 4px; border-radius: 3px;">
                                                                <tr>
                                                                    <td style="padding: 8px 10px;">
                                                                        <span style="font-size: 14px; color: #0f172a; font-weight: 600;">{{ $acc->title }}</span>
                                                                        @if(!empty($acc->max_occupancy))
                                                                        <span style="font-size: 12px; color: #64748b;"> · {{ __('emails.up_to') }} {{ $acc->max_occupancy }} {{ __('emails.guests') }}</span>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                            @endforeach
                                                        </td>
                                                    </tr>
                                                    @endif
                                                    @if(isset($offer['boats']) && $offer['boats']->isNotEmpty())
                                                    <tr>
                                                        <td style="padding: 6px 0 4px; font-size: 10px; color: #64748b; text-transform: uppercase; letter-spacing: 0.6px; font-weight: 600;">{{ __('emails.offer_sendout_boats') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding: 0 0 12px;">
                                                            @foreach($offer['boats'] as $boat)
                                                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background: #f0f9ff; border-left: 3px solid #0ea5e9; margin-bottom: 4px; border-radius: 3px;">
                                                                <tr>
                                                                    <td style="padding: 8px 10px;">
                                                                        <span style="font-size: 14px; color: #0f172a; font-weight: 600;">{{ $boat->title }}</span>
                                                                        @if(!empty($boat->max_persons))
                                                                        <span style="font-size: 12px; color: #64748b;"> · {{ __('emails.up_to') }} {{ $boat->max_persons }} {{ __('emails.persons') }}</span>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                            @endforeach
                                                        </td>
                                                    </tr>
                                                    @endif
                                                    @if(isset($offer['guidings']) && $offer['guidings']->isNotEmpty())
                                                    <tr>
                                                        <td style="padding: 6px 0 4px; font-size: 10px; color: #64748b; text-transform: uppercase; letter-spacing: 0.6px; font-weight: 600;">{{ __('emails.offer_sendout_guidings') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding: 0 0 12px;">
                                                            @foreach($offer['guidings'] as $guiding)
                                                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background: #f0fdf4; border-left: 3px solid #10b981; margin-bottom: 4px; border-radius: 3px;">
                                                                <tr>
                                                                    <td style="padding: 8px 10px;">
                                                                        <span style="font-size: 14px; color: #0f172a; font-weight: 600;">{{ $guiding->title }}</span>
                                                                        @if(!empty($guiding->duration))
                                                                        <span style="font-size: 12px; color: #64748b;"> · {{ $guiding->duration }}</span>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                            @endforeach
                                                        </td>
                                                    </tr>
                                                    @endif
                                                    {{-- Booking summary: dates, persons, price, additional info in one compact box --}}
                                                    @if(!empty($offer['date_from']) || !empty($offer['date_to']) || !empty($offer['number_of_persons']) || !empty($offer['price']) || !empty($offer['additional_info']))
                                                    <tr>
                                                        <td style="padding: 8px 0 0;">
                                                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden;">
                                                                <tr>
                                                                    <td style="padding: 10px 12px;">
                                                                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                                                                            @if(!empty($offer['date_from']) || !empty($offer['date_to']))
                                                                                            <tr>
                                                                                                <td style="padding: 2px 0; vertical-align: top;">
                                                                                                    <span style="font-size: 10px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">{{ __('emails.offer_sendout_dates') }}</span>
                                                                                                    <div style="font-size: 13px; color: #0f172a; font-weight: 500; margin-top: 2px; white-space: nowrap;">
                                                                                                        <span style="display: inline-block;">{{ $offer['date_from_formatted'] ?? '—' }}</span>@if(!empty($offer['date_to'])) <span style="display: inline-block;">– {{ $offer['date_to_formatted'] ?? $offer['date_to'] }}</span>@endif
                                                                                                    </div>
                                                                                                </td>
                                                                                                @if(!empty($offer['number_of_persons']))
                                                                                                <td style="padding: 2px 0; vertical-align: top; text-align: right;">
                                                                                                    <span style="font-size: 10px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">{{ __('emails.offer_sendout_number_of_persons') }}</span>
                                                                                                    <div style="font-size: 13px; color: #0f172a; font-weight: 500; margin-top: 2px;">{{ $offer['number_of_persons'] }}</div>
                                                                                                </td>
                                                                                                @endif
                                                                                            </tr>
                                                                                            @elseif(!empty($offer['number_of_persons']))
                                                                                            <tr>
                                                                                                <td style="padding: 2px 0; vertical-align: top; text-align: center;">
                                                                                                    <span style="font-size: 10px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">{{ __('emails.offer_sendout_number_of_persons') }}</span>
                                                                                                    <div style="font-size: 13px; color: #0f172a; font-weight: 500; margin-top: 2px;">{{ $offer['number_of_persons'] }}</div>
                                                                                                </td>
                                                                                            </tr>
                                                                                            @endif
                                                                                            @if(!empty($offer['additional_info']))
                                                                                            <tr>
                                                                                                <td colspan="2" style="padding: 8px 0 0; border-top: 1px solid #e2e8f0;">
                                                                                                    <span style="font-size: 10px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">{{ __('emails.offer_sendout_additional_info') }}</span>
                                                                                                    <div style="font-size: 12px; color: #475569; line-height: 1.45; margin-top: 4px;">{!! nl2br(e($offer['additional_info'])) !!}</div>
                                                                                                </td>
                                                                                            </tr>
                                                                                            @endif
                                                                                        </table>
                                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    @endif
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                @endforeach
                @if(!empty($free_text))
                <tr>
                    <td style="padding: 0 24px 16px;">
                        <div style="border-left: 3px solid #1a1a2e; padding: 10px 14px; background-color: #f8fafc; border-radius: 0 6px 6px 0;">
                            <p style="margin: 0; font-size: 13px; color: #475569; line-height: 1.55; font-style: italic;">{!! nl2br(e($free_text)) !!}</p>
                        </div>
                    </td>
                </tr>
                @endif
                {{-- Closing message for confidence --}}
                <tr>
                    <td style="padding: 0 24px 20px;">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background: #f0f9ff; border-radius: 6px; border: 1px solid #bae6fd;">
                            <tr>
                                <td style="padding: 14px 16px;">
                                    <p style="margin: 0 0 6px; font-size: 14px; color: #0c4a6e; font-weight: 600;">{{ __('emails.offer_sendout_closing_title') }}</p>
                                    <p style="margin: 0; font-size: 13px; color: #075985; line-height: 1.5;">{{ __('emails.offer_sendout_closing_message') }} {{ __('emails.offer_sendout_closing_secondary') }}</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                {{-- CTA --}}
                <tr>
                    <td style="padding: 0 24px 24px; text-align: center;">
                        <a href="{{ route('additional.contact') }}" target="_blank" style="display: inline-block; background-color: #e8604c; color: #ffffff !important; padding: 14px 28px; font-size: 15px; font-weight: 600; text-decoration: none; border-radius: 8px;">{{ __('emails.contact_us') }}</a>
                    </td>
                </tr>
                {{-- Footer --}}
                <tr>
                    <td style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); padding: 20px 24px; color: #ffffff; text-align: center;">
                        <table role="presentation" cellspacing="0" cellpadding="0" style="margin: 0 auto; text-align: left;">
                            <tr>
                                {{-- Logo --}}
                                <td style="vertical-align: bottom; padding-right: 32px;">
                                    <img src="{{ asset('assets/images/logo/CatchAGuide2_Logo_PNG.png') }}" alt="{{ config('app.name') }}" style="max-width: 100px; height: auto; display: block;" onerror="this.style.display='none';">
                                    <span style="color: #ffffff; font-size: 16px; font-weight: 700; letter-spacing: 0.3px;">{{ config('app.name') }}</span>
                                </td>
                                {{-- Email (top), Phone (bottom) - bottom-aligned with logo block --}}
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
