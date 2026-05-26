@include('mails.partials.cag-email-header', ['title' => __('emails.guide_application_approved_title')])

<p style="font-size: 14px; margin-top: 0; color: #313041;">{{ __('emails.guide_application_approved_greeting', ['name' => $user->firstname]) }}</p>
<p style="font-size: 14px; line-height: 1.6; color: #444;">{{ __('emails.guide_application_approved_body') }}</p>

<div style="text-align: center; margin: 28px 0;">
    <a href="{{ route('profile.myguidings') }}" target="_blank" style="background-color: #e8604c; padding: 12px 24px; color: #ffffff !important; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 600; display: inline-block;">
        {{ __('emails.guide_application_approved_cta') }}
    </a>
</div>

<p style="font-size: 14px; color: #444;">{{ __('emails.best_regards') }}</p>
<p style="font-size: 14px; color: #313041; font-weight: 600; margin-bottom: 24px;">{{ __('emails.catchaguide_team') }}</p>

@include('mails.partials.cag-email-footer')
