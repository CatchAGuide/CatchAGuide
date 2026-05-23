@include('mails.partials.cag-email-header', ['title' => __('emails.guide_application_received_title')])

<p style="font-size: 14px; margin-top: 0; color: #313041;">{{ __('emails.guide_application_received_greeting', ['name' => $user->firstname]) }}</p>
<p style="font-size: 14px; line-height: 1.6; color: #444;">{{ __('emails.guide_application_received_body') }}</p>

<div style="border: 1px solid #e0e0e0; border-radius: 12px; padding: 16px 20px; margin: 24px 0; background: #f8f9fa;">
    <p style="font-size: 14px; font-weight: 700; color: #313041; margin: 0 0 12px;">{{ __('emails.guide_application_received_next_steps') }}</p>
    <ul style="font-size: 14px; line-height: 1.7; color: #444; margin: 0; padding-left: 20px;">
        <li>{{ __('emails.guide_application_received_step_profile') }}</li>
        <li>{{ __('emails.guide_application_received_step_drafts') }}</li>
        <li>{{ __('emails.guide_application_received_step_review') }}</li>
    </ul>
</div>

<div style="text-align: center; margin: 28px 0;">
    <a href="{{ route('profile.guide-profile') }}" target="_blank" style="background-color: #e8604c; padding: 12px 24px; color: #ffffff !important; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 600; display: inline-block; margin: 0 6px 10px;">
        {{ __('emails.guide_application_received_cta_profile') }}
    </a>
    <a href="{{ route('profile.index') }}" target="_blank" style="background-color: #313041; padding: 12px 24px; color: #ffffff !important; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 600; display: inline-block; margin: 0 6px 10px;">
        {{ __('emails.guide_application_received_cta') }}
    </a>
</div>

<p style="font-size: 14px; color: #444;">{{ __('emails.best_regards') }}</p>
<p style="font-size: 14px; color: #313041; font-weight: 600; margin-bottom: 24px;">{{ __('emails.catchaguide_team') }}</p>

@include('mails.partials.cag-email-footer')
