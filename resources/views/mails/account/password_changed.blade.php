@include('mails.partials.cag-email-header', ['title' => __('guide_area.account.mail.password_title')])

<p style="font-size: 14px; margin-top: 0; color: #313041;">{{ __('guide_area.mail.greeting', ['name' => $user->firstname]) }}</p>
<p style="font-size: 14px; line-height: 1.6; color: #444;">
    {{ __('guide_area.account.mail.password_body', ['date' => $changedAt]) }}
</p>

<div style="border: 1px solid #e0e0e0; border-radius: 12px; padding: 16px 20px; margin: 24px 0; background: #f8f9fa; font-size: 14px; color: #444; line-height: 1.6;">
    <strong>{{ __('guide_area.account.mail.not_you_title') }}</strong><br>
    {{ __('guide_area.account.mail.password_not_you') }}
    <a href="{{ route('password.request') }}" target="_blank">{{ __('guide_area.account.mail.reset_password') }}</a>
    · <a href="{{ route('additional.contact') }}" target="_blank">{{ __('guide_area.account.mail.contact_support') }}</a>
</div>

<p style="font-size: 14px; color: #444;">{{ __('emails.best_regards') }}</p>
<p style="font-size: 14px; color: #313041; font-weight: 600; margin-bottom: 24px;">{{ __('emails.catchaguide_team') }}</p>

@include('mails.partials.cag-email-footer')
