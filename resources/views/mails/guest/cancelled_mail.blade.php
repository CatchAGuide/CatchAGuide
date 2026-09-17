<!DOCTYPE html>
<html>
<body style="font-family: 'Morrison', sans-serif; margin: 0; padding: 0;">

<div class="container" style="width: 100%; max-width: 600px; margin: 0 auto; background-color: white; box-shadow: 0 4px 6px 3px rgba(0, 0, 0, 0.1);">
    <div class="header" style="text-align: center; padding: 20px;">
        <a href="{{route('welcome')}}" target="_blank">
            <img class="logo" src="https://catchaguide.com/assets/images/logo/CatchAGuide2_Logo_JPEG.jpg" alt="Catchaguide Logo" style="max-width: 150px; padding-top: 10px;">
        </a>
        <h2 class="header-title" style="font-family: 'Morrison', sans-serif;">@lang('emails.guest_booking_confirmed_cancelled_title')</h2>
    </div>
    <div class="content" style="padding-bottom: 0px;">
        <div class="content-header" style="padding: 20px;">
            <p style="font-size: 14px; font-family: 'Morrison', sans-serif;">@lang('emails.dear') {{$user->firstname ?? __('emails.guest_name')}},</p>
            <p style="font-size: 14px; font-family: 'Morrison', sans-serif;">
                {{ $textNote }}
            </p>
            <p style="font-size: 14px; font-family: 'Morrison', sans-serif;">
                @lang('emails.guest_booking_confirmed_cancelled_text_2')
            </p>
            <p style="font-size: 14px; font-family: 'Morrison', sans-serif;">
                @lang('emails.guest_booking_confirmed_cancelled_text_3')
            </p>
        </div>
        <div style="padding: 0 20px;">
            <p style="font-size: 14px; font-family: 'Morrison', sans-serif;">@lang('emails.guest_booking_confirmed_cancelled_text_4')</p>
        </div>
        <div style="text-align: center; margin: 2rem 0;">
            <a style="background-color: #313041; padding: 10px 20px; color: #fff !important; border: 0; text-decoration: none; margin-top: 30px; font-family: 'Morrison', sans-serif;" href="{{route('additional.contact')}}" target="_blank">@lang('emails.contact_us')</a>
        </div>
    </div>
    <div style="padding: 0 20px;">
        <p style="margin-top: 2rem; margin-bottom: .5rem; font-size: 14px; font-family: 'Morrison', sans-serif;">
        @lang('emails.best_regards')
        </p>
        <p style="margin-top: .5rem; font-size: 14px; font-family: 'Morrison', sans-serif;"> @lang('emails.catchaguide_team')</p>
    </div>

    <div class="footer" style="text-align: center; padding: 20px; color: #fff; background-color: #313041; margin-top: 2rem;">
        <table width="100%">
            <tr>
                <td style="padding: 10px; text-align: left; width: 50%;">
                    <img class="logo" src="https://catchaguide.com/assets/images/logo/CatchAGuide2_Logo_PNG.png" width="100px" alt="Catchaguide Logo">
                    <p style="font-size: 14px; font-family: 'Morrison', sans-serif;">
                        <a href="tel:+49 (0) {{config('cag.contact_num')}}" style="color: #fff; font-size: 14px; text-decoration: none;">+49 (0) {{config('cag.contact_num')}}</a>
                    </p>
                    <p style="font-size: 14px; font-family: 'Morrison', sans-serif;">
                        <a href="mailto:{{config('mail.admin_email')}}" style="color: #fff; font-size: 14px; text-decoration: none;">{{config('mail.admin_email')}}</a>
                    </p>
                </td>
                <td style="padding: 10px;">
                    <a style="color: #fff; text-decoration: none;" href="{{route('additional.contact')}}" target="_blank">
                        <p style="font-size: 14px; font-family: 'Morrison', sans-serif;">@lang('emails.contact_us')</p></a>
                    <p style="margin: .5rem 0; font-size: 14px; font-family: 'Morrison', sans-serif;">@lang('emails.follow_us')</p>
                </td>
            </tr>
        </table>
        <hr>
        <div style="text-align: center;">
            <p style="font-size: 14px; font-family: 'Morrison', sans-serif;">© Catchaguide {{date('Y')}}</p>
        </div>
    </div>
</div>

</body>
</html>
