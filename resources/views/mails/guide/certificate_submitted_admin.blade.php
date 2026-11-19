@include('mails.partials.cag-email-header', ['title' => __('guide_area.mail.admin_submitted_title')])

<p style="font-size: 14px; line-height: 1.6; color: #444; margin-top: 0;">
    {{ __('guide_area.mail.admin_submitted_body', ['name' => $name, 'certificate' => $certificate->label()]) }}
</p>

<div style="border: 1px solid #e0e0e0; border-radius: 12px; padding: 16px 20px; margin: 24px 0; background: #f8f9fa; font-size: 14px; color: #444; line-height: 1.7;">
    <div><strong>{{ __('guide_area.mail.label_guide') }}</strong> {{ $name }} ({{ $user->email }})</div>
    <div><strong>{{ __('guide_area.mail.label_certificate') }}</strong> {{ $certificate->label() }}</div>
    @if ($certificate->valid_until)
        <div><strong>{{ __('guide_area.mail.label_valid_until') }}</strong> {{ $certificate->valid_until->format('d.m.Y') }}</div>
    @endif
</div>

<div style="text-align: center; margin: 28px 0;">
    <a href="{{ route('admin.guide-certificates.index') }}" target="_blank" style="background-color: #e8604c; padding: 12px 24px; color: #ffffff !important; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 600; display: inline-block;">
        {{ __('guide_area.mail.admin_submitted_cta') }}
    </a>
</div>

@include('mails.partials.cag-email-footer')
