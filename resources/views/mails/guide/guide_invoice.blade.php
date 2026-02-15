<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>{{ __('emails.guide_invoice_title') }}</title>
    <link href="https://fonts.cdnfonts.com/css/morrison" rel="stylesheet">
</head>
<body style="font-family: 'Morrison', sans-serif; margin: 0; padding: 0;">

<div class="container" style="width: 100%; max-width: 600px; margin: 0 auto; background-color: white; box-shadow: 0 4px 6px 3px rgba(0, 0, 0, 0.1);">
    <div class="header" style="text-align: center; padding: 20px;">
        <a href="{{ route('welcome') }}" target="_blank">
            <img class="logo" src="https://catchaguide.com/assets/images/logo/CatchAGuide2_Logo_JPEG.jpg" alt="Catchaguide Logo" style="max-width: 150px; padding-top: 10px;">
        </a>
        <h2 class="header-title">{{ __('emails.guide_invoice_title') }}</h2>
    </div>

    <div style="padding: 0 20px;">
        <p style="font-size: 14px; margin-top: 0;">{{ __('emails.dear') }} {{ $guide->firstname }},</p>
        <p style="font-size: 14px;">{{ __('emails.guide_invoice_text_1') }}</p>
        <p style="font-size: 14px;">{{ __('emails.guide_invoice_text_2') }}</p>
    </div>

    <div class="order-details" style="border: 1px solid rgb(132, 132, 132); padding: 10px; border-radius: 12px; margin: 20px;">
        <h4>{{ __('emails.guide_invoice_summary_title') }}</h4>
        <p style="font-size: 14px;"><strong>{{ __('emails.guide_invoice_booking_id') }}</strong> {{ $booking->id }}</p>
        <p style="font-size: 14px;"><strong>{{ __('emails.guide_invoice_tour') }}</strong> {{ translate($guiding->title) }}</p>
        <p style="font-size: 14px;"><strong>{{ __('emails.guide_invoice_location') }}</strong> {{ $guiding->location }}</p>
        <p style="font-size: 14px;"><strong>{{ __('emails.guide_invoice_date') }}</strong> {{ $booking->getFormattedBookingDate('d.m.Y') }}</p>
        <p style="font-size: 14px;"><strong>{{ __('emails.guide_invoice_guest') }}</strong> {{ $user->firstname ?? '' }} {{ $user->lastname ?? '' }}</p>
        <hr>
        <p style="font-size: 14px;"><strong>{{ __('emails.guide_invoice_total_price') }}</strong> {{ two($booking->price) }} &euro;</p>
        <p style="font-size: 14px;"><strong>{{ __('emails.guide_invoice_guide_share') }}</strong> {{ two($booking->price - $booking->cag_percent) }} &euro;</p>
        <p style="font-size: 14px;"><strong>{{ __('emails.guide_invoice_cag_commission') }}</strong> {{ two($booking->cag_percent) }} &euro;</p>
    </div>

    <div style="padding: 0 20px;">
        <p style="font-size: 14px;">{{ __('emails.guide_invoice_text_3') }}</p>
        <p style="font-size: 14px;">
            {{ __('emails.best_regards') }}<br>
            {{ __('emails.catchaguide_team') }}
        </p>
        <div style="text-align: center; margin: 2rem 0;">
            <a href="{{ route('additional.contact') }}" target="_blank" style="background-color: #e8604c; padding: 10px 20px; color: #fff !important; border: 0; text-decoration: none;">@lang('emails.contact_us')</a>
        </div>
    </div>

    <div class="footer" style="text-align: center; padding: 20px; color: #fff; background-color: #313041; margin-top: 2rem;">
        <table width="100%">
            <tr>
                <td style="padding: 10px; text-align: left; width: 50%;">
                    <img class="logo" src="https://catchaguide.com/assets/images/logo/CatchAGuide2_Logo_PNG.png" width="100px" alt="Catchaguide Logo">
                    <p>
                        <a href="tel:+49 (0) {{ env('CONTACT_NUM') }}" style="color: #fff; font-size: 14px; text-decoration: none;">+49 (0) {{ env('CONTACT_NUM') }}</a>
                    </p>
                    <p>
                        <a href="mailto:{{ env('TO_CEO') }}" style="color: #fff; font-size: 14px; text-decoration: none;">{{ env('TO_CEO') }}</a>
                    </p>
                </td>
                <td style="padding: 10px;">
                    <a href="{{ route('additional.contact') }}" target="_blank" style="color: #fff; text-decoration: none;">
                        <p style="font-size: 14px;">@lang('emails.contact_us')</p></a>
                    <p style="margin: .5rem 0; font-size: 14px;">@lang('emails.follow_us')</p>
                </td>
            </tr>
        </table>
        <hr>
        <div style="text-align: center;">
            <p style="font-size: 14px;">&copy; Catchaguide {{ date('Y') }}</p>
        </div>
    </div>
</div>

</body>
</html>
