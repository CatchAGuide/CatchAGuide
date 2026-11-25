@include('mails.partials.cag-email-header', ['title' => __($approved ? 'guide_area.account.mail.approved_title' : 'guide_area.account.mail.rejected_title')])

<p style="font-size: 14px; margin-top: 0; color: #313041;">{{ __('guide_area.mail.greeting', ['name' => $user->firstname]) }}</p>
<p style="font-size: 14px; line-height: 1.6; color: #444;">
    {{ __($approved ? 'guide_area.account.mail.approved_body' : 'guide_area.account.mail.rejected_body') }}
</p>

@if (! $approved && $user->account_rejection_reason)
    <div style="border: 1px solid #e0e0e0; border-radius: 12px; padding: 16px 20px; margin: 24px 0; background: #f8f9fa; font-size: 14px; color: #444; line-height: 1.6;">
        <strong>{{ __('guide_area.mail.label_reason') }}</strong><br>
        {{ $user->account_rejection_reason }}
    </div>
@endif

<div style="text-align: center; margin: 28px 0;">
    <a href="{{ route('profile.settings') }}#billing" target="_blank" style="background-color: #e8604c; padding: 12px 24px; color: #ffffff !important; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 600; display: inline-block;">
        {{ __('guide_area.account.mail.cta_account') }}
    </a>
</div>

<p style="font-size: 14px; color: #444;">{{ __('emails.best_regards') }}</p>
<p style="font-size: 14px; color: #313041; font-weight: 600; margin-bottom: 24px;">{{ __('emails.catchaguide_team') }}</p>

@include('mails.partials.cag-email-footer')
