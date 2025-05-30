<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>@lang('emails.guest_tour_review_title')</title>
    <link href="https://fonts.cdnfonts.com/css/morrison" rel="stylesheet">
</head>
<body style="font-family: 'Morrison', sans-serif; margin: 0; padding: 0;">

<div class="container" style="width: 100%; max-width: 600px; margin: 0 auto; background-color: white; box-shadow: 0 4px 6px 3px rgba(0, 0, 0, 0.1);">
    <div class="header" style="text-align: center; padding: 20px;">
        <a href="{{route('welcome')}}" target="_blank">
            <img class="logo" src="https://catchaguide.com/assets/images/logo/CatchAGuide2_Logo_JPEG.jpg" alt="Catchaguide Logo" style="max-width: 150px;padding-top: 10px;">
        </a>
        <h2 class="header-title">
            @lang('emails.guest_tour_review_title')
        </h2>
    </div>
    <div class="content" style="padding-bottom:0px;">
        <div class="content-header" style="padding: 20px;">
            <p style="font-size:14px;">{{__('emails.dear')}} {{$userName}},</p>
            <p style="font-size: 14px;">
                {!! str_replace(['[Guide Name]', '[Location]'], [$guideName, $location], __('emails.guest_tour_review_text_1')) !!}
            </p>
            <div style="margin: 1.5rem 0;">
                <p style="text-align: center; font-size: 14px;">@lang('emails.guest_tour_review_text_2')</p>
                {{-- <p style="text-align: center; font-size: .5em;">
                    <a  style="margin: 0 .5rem;" href="{}"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 512 512"><path d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zm176.5 64.3C196.1 302.1 223.8 288 256 288s59.9 14.1 79.5 32.3C354.5 338.1 368 362 368 384c0 5.4-2.7 10.4-7.2 13.4s-10.2 3.4-15.2 1.3l-17.2-7.5c-22.8-10-47.5-15.1-72.4-15.1s-49.6 5.2-72.4 15.1l-17.2 7.5c-4.9 2.2-10.7 1.7-15.2-1.3s-7.2-8-7.2-13.4c0-22 13.5-45.9 32.5-63.7zm-43-173.6l89.9 47.9c10.7 5.7 10.7 21.1 0 26.8l-89.9 47.9c-7.9 4.2-17.5-1.5-17.5-10.5c0-2.8 1-5.5 2.8-7.6l36-43.2-36-43.2c-1.8-2.1-2.8-4.8-2.8-7.6c0-9 9.6-14.7 17.5-10.5zM396 157.1c0 2.8-1 5.5-2.8 7.6l-36 43.2 36 43.2c1.8 2.1 2.8 4.8 2.8 7.6c0 9-9.6 14.7-17.5 10.5l-89.9-47.9c-10.7-5.7-10.7-21.1 0-26.8l89.9-47.9c7.9-4.2 17.5 1.5 17.5 10.5z"/></svg></a>
                    <a  style="margin: 0 .5rem;" href="https://catchaguide.com/guide_review"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 512 512"><path d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zM174.6 384.1c-4.5 12.5-18.2 18.9-30.7 14.4s-18.9-18.2-14.4-30.7C146.9 319.4 198.9 288 256 288s109.1 31.4 126.6 79.9c4.5 12.5-2 26.2-14.4 30.7s-26.2-2-30.7-14.4C328.2 358.5 297.2 336 256 336s-72.2 22.5-81.4 48.1zM144.4 208a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zm192-32a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/></svg></a>
                    <a  style="margin: 0 .5rem;" href="https://catchaguide.com/guide_review"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 512 512"><path d="M464 256A208 208 0 1 1 48 256a208 208 0 1 1 416 0zM256 0a256 256 0 1 0 0 512A256 256 0 1 0 256 0zM176.4 240a32 32 0 1 0 0-64 32 32 0 1 0 0 64zm192-32a32 32 0 1 0 -64 0 32 32 0 1 0 64 0zM184 328c-13.3 0-24 10.7-24 24s10.7 24 24 24l144 0c13.3 0 24-10.7 24-24s-10.7-24-24-24l-144 0z"/></svg></a>
                    <a  style="margin: 0 .5rem;" href="https://catchaguide.com/guide_review"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 512 512"><path d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zm177.6 62.1C192.8 334.5 218.8 352 256 352s63.2-17.5 78.4-33.9c9-9.7 24.2-10.4 33.9-1.4s10.4 24.2 1.4 33.9c-22 23.8-60 49.4-113.6 49.4s-91.7-25.5-113.6-49.4c-9-9.7-8.4-24.9 1.4-33.9s24.9-8.4 33.9 1.4zM144.4 208a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zm192-32a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/></svg></a>
                    <a  style="margin: 0 .5rem;" href="https://catchaguide.com/guide_review"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 512 512"><path d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zm130.7 57.9c-4.2-13.6 7.1-25.9 21.3-25.9l212.5 0c14.2 0 25.5 12.4 21.3 25.9C369 368.4 318.2 408 258.2 408s-110.8-39.6-127.5-94.1zM144.4 192a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zm192-32a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/></svg></a>
                </p> --}}
            </div>
            <div style="text-align:center;margin-top: 2rem;">
                {{-- <p style="margin-bottom:2rem; font-size: 14px;">@lang('emails.guest_tour_review_text_3')</p> --}}
                <a  class="btn-theme" style="background-color: #e8604c;padding:10px 20px;color:#fff !important;border:0;text-decoration: none;margin-top:30px;" href="{{$reviewUrl}}">@lang('emails.guest_tour_review_text_6')</a>
            </div>
            <div>
            </div>
            <div style="margin-top:3rem;">
                <p style="font-size: 14px;">
                </p>
                <p style="font-size: 14px;">
                    @lang('emails.guest_tour_review_text_4')
                </p>
                <p style="margin-top: 2rem; margin-bottom: .5rem; font-size: 14px;">
                    @lang('emails.best_regards')
                </p>
                <p style="margin-top: .5rem; font-size: 14px;">@lang('emails.catchaguide_team')</p>
            </div>
        </div>
    </div>
    <div class="footer" style="text-align: center; padding: 20px; color: #fff; background-color: #313041; margin-top: 2rem;">
        <table width="100%">
            <tr>
                <td style="padding: 10px;text-align: left;width: 50%;">
                    <img class="logo" src="https://catchaguide.com/assets/images/logo/CatchAGuide2_Logo_PNG.png" width="100px" alt="Catchaguide Logo">
                    <p>
                        <a href="tel:+49 (0) {{env('CONTACT_NUM')}}" style="color:#fff; font-size: 14px; text-decoration: none;">+49 (0) {{env('CONTACT_NUM')}}</a>

                    </p>
                    <p>
                        <a href="mailto:{{env('TO_CEO')}}" style="color:#fff; font-size: 14px; text-decoration: none;">{{env('TO_CEO')}}</a>
                    </p>
                </td>
                <td style="padding: 10px;">
                    <a style="color: #fff; text-decoration: none;" href="{{route('additional.contact')}}" target="_blank">
                        <p>@lang('emails.contact_us')</p></a>
                    <p style="margin: .5rem 0">@lang('emails.follow_us')</p>
                    <div class="social-icons">
                        <a  href="https://www.facebook.com/CatchAGuide" target="_blank">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 512 512" fill="#fff"><path d="M512 256C512 114.6 397.4 0 256 0S0 114.6 0 256C0 376 82.7 476.8 194.2 504.5V334.2H141.4V256h52.8V222.3c0-87.1 39.4-127.5 125-127.5c16.2 0 44.2 3.2 55.7 6.4V172c-6-.6-16.5-1-29.6-1c-42 0-58.2 15.9-58.2 57.2V256h83.6l-14.4 78.2H287V510.1C413.8 494.8 512 386.9 512 256h0z"/></svg>
                        </a>
                        <a style="padding-left: .5rem;" href="https://wa.me/+4915155495574" target="_blank">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 448 512" fill="#fff"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7 .9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg>
                        </a>
                        <a style="padding-left: .5rem;" href="https://www.instagram.com/catchaguide_official/" target="_blank">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"  viewBox="0 0 448 512" fill="#fff"><path d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"/></svg>
                        </a>
                    </div>
                </td>
            </tr>
        </table> 
        <hr>
        <div style="text-align: center;">
            <p>© Catchaguide {{date('Y')}}</p>
        </div>       
    </div>
</div>

</body>
</html>
