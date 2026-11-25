@include('mails.partials.cag-email-header', ['title' => __('guest_bookings.find.mail.title')])

<p style="font-size: 14px; margin-top: 0; color: #313041;">{{ __('guest_bookings.mail.link_greeting') }}</p>
<p style="font-size: 14px; line-height: 1.6; color: #444;">{{ __('guest_bookings.find.mail.body') }}</p>

<div style="padding: 0 20px;">
    @include('mails.partials.booking-link-button', ['gbLinkUrl' => $url, 'gbLinkLabel' => __('guest_bookings.find.mail.button'), 'gbLinkHint' => __('guest_bookings.find.mail.hint')])
</div>

<p style="font-size: 13px; line-height: 1.6; color: #787780;">{{ __('guest_bookings.find.mail.not_you') }}</p>

<p style="font-size: 14px; color: #444;">{{ __('emails.best_regards') }}</p>
<p style="font-size: 14px; color: #313041; font-weight: 600; margin-bottom: 24px;">{{ __('emails.catchaguide_team') }}</p>

@include('mails.partials.cag-email-footer')
