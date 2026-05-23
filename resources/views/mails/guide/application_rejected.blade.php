@include('mails.partials.cag-email-header', ['title' => __('emails.guide_application_rejected_title')])

<p style="font-size: 14px; margin-top: 0; color: #313041;">{{ __('emails.guide_application_rejected_greeting', ['name' => $user->firstname]) }}</p>
<p style="font-size: 14px; line-height: 1.6; color: #444;">{{ __('emails.guide_application_rejected_body') }}</p>

@if(!empty($rejectionReason))
<div style="border-left: 4px solid #e8604c; padding: 12px 16px; margin: 20px 0; background: #fff8f6;">
    <p style="font-size: 13px; font-weight: 700; color: #313041; margin: 0 0 6px;">{{ __('emails.guide_application_rejected_reason_label') }}</p>
    <p style="font-size: 14px; line-height: 1.6; color: #444; margin: 0;">{{ $rejectionReason }}</p>
</div>
@else
<p style="font-size: 14px; line-height: 1.6; color: #6c757d;">{{ __('emails.guide_application_rejected_no_reason') }}</p>
@endif

<p style="font-size: 14px; line-height: 1.6; color: #444;">{{ __('emails.guide_application_rejected_reapply') }}</p>

<div style="text-align: center; margin: 28px 0;">
    <a href="{{ route('guide.onboarding') }}" target="_blank" style="background-color: #e8604c; padding: 12px 24px; color: #ffffff !important; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 600; display: inline-block;">
        {{ __('emails.guide_application_rejected_cta') }}
    </a>
</div>

<p style="font-size: 14px; color: #444;">{{ __('emails.best_regards') }}</p>
<p style="font-size: 14px; color: #313041; font-weight: 600; margin-bottom: 24px;">{{ __('emails.catchaguide_team') }}</p>

@include('mails.partials.cag-email-footer')
