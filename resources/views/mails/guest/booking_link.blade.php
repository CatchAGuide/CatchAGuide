@include('mails.partials.cag-email-header', ['title' => __('guest_bookings.mail.link_title')])

<p style="font-size: 14px; margin-top: 0; color: #313041;">{{ __('guest_bookings.mail.link_greeting') }}</p>
<p style="font-size: 14px; line-height: 1.6; color: #444;">{{ __('guest_bookings.mail.link_body', ['type' => $typeLabel]) }}</p>
@if ($title !== '')
    <p style="font-size: 14px; line-height: 1.6; color: #313041;"><strong>{{ $title }}</strong></p>
@endif

@include('mails.partials.booking-link-button', ['bookable' => $bookable])

<p style="font-size: 13px; line-height: 1.6; color: #787780;">{{ __('guest_bookings.mail.link_not_you') }}</p>

<p style="font-size: 14px; color: #444;">{{ __('emails.best_regards') }}</p>
<p style="font-size: 14px; color: #313041; font-weight: 600; margin-bottom: 24px;">{{ __('emails.catchaguide_team') }}</p>

@include('mails.partials.cag-email-footer')
