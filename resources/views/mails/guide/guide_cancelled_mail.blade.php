<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>@lang('profile.gdc-cancelled')</title>
    <link href="https://fonts.cdnfonts.com/css/morrison" rel="stylesheet">
</head>
<body style="font-family: 'Morrison', sans-serif; margin: 0; padding: 0;">

<div class="container" style="width: 100%; max-width: 600px; margin: 0 auto; background-color: white; box-shadow: 0 4px 6px 3px rgba(0, 0, 0, 0.1);">
    <div class="header" style="text-align: center; padding: 20px;">
        <a href="{{ route('welcome') }}" target="_blank">
            <img class="logo" src="https://catchaguide.com/assets/images/logo/CatchAGuide2_Logo_JPEG.jpg" alt="Catchaguide Logo" style="max-width: 150px; padding-top: 10px;">
        </a>
        <h2 class="header-title">@lang('profile.gdc-cancelled')</h2>
    </div>
    <div class="content" style="padding-bottom: 0;">
        <div class="content-header" style="padding: 20px;">
            <p style="font-size: 14px; margin-top: 0;">@lang('profile.booking-dear') <strong>{{ $guide->firstname }}</strong>,</p>
            <p style="font-size: 14px;">
                @lang('profile.gdc-inform')
                <strong>{{ $user->firstname }} {{ $user->lastname }}</strong>
                @lang('profile.gdc-inform2')
            </p>
        </div>
    </div>
    <div class="order-details" style="border: 1px solid rgb(132, 132, 132); padding: 10px; border-radius: 12px; margin: 20px;">
        <h4 style="margin-top: 0;">{{ __('emails.guide_booking_accepted_text_2') }}</h4>
        <p style="font-size: 14px;"><strong>{{ __('emails.guide_booking_accepted_text_10') }}</strong>
            <a href="{{ route('guidings.show', [$guiding->id, $guiding->slug]) }}" target="_blank" style="text-decoration: none; font-weight: bold;">{{ translate($guiding->title) }}</a>
        </p>
        <p style="font-size: 14px;"><strong>{{ __('emails.guide_booking_accepted_text_11') }}</strong> {{ $guiding->location }}</p>
        <p style="font-size: 14px;"><strong>{{ __('emails.guide_booking_accepted_text_12') }}</strong> {{ date('d F Y', strtotime($booking->book_date)) }}</p>
        <p style="font-size: 14px;"><strong>{{ __('emails.number_of_guests') }}</strong> {{ $booking->count_of_users }}</p>
        <p style="font-size: 14px;"><strong>{{ __('emails.guide_booking_accepted_text_7') }}</strong> {{ $user->firstname }} {{ $user->lastname }}</p>
        <p style="font-size: 14px;"><strong>{{ __('emails.guide_booking_accepted_text_9') }}</strong> {{ $booking->email ?? $user->email }}</p>
    </div>
    <div class="content" style="padding: 0 20px 20px;">
        <p style="font-size: 14px;">@lang('profile.gn-question')</p>
        <div style="text-align: center; margin-top: 1.5rem;">
            <a class="btn-theme" style="background-color: #e8604c; padding: 10px 20px; color: #fff !important; border: 0; text-decoration: none;" href="{{ route('login') }}" target="_blank">@lang('profile.booking-view')</a>
        </div>
    </div>
    <div class="footer" style="text-align: center; padding: 20px; color: #777777;">
        <p style="font-style: italic; font-size: 14px;">@lang('profile.booking-chossing')</p>
        <p style="font-size: 14px;">@lang('profile.booking-regards'),<br>Catch A Guide</p>
    </div>
</div>

</body>
</html>
