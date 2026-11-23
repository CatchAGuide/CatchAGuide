@include('mails.partials.cag-email-header', ['title' => __('guide_area.account.mail.admin_title')])

<p style="font-size: 14px; line-height: 1.6; color: #444; margin-top: 0;">
    {{ __($typeChanged ? 'guide_area.account.mail.admin_body_type' : 'guide_area.account.mail.admin_body', ['name' => $name]) }}
</p>

<div style="border: 1px solid #e0e0e0; border-radius: 12px; padding: 16px 20px; margin: 24px 0; background: #f8f9fa; font-size: 14px; color: #444; line-height: 1.7;">
    <div><strong>{{ __('guide_area.mail.label_guide') }}</strong> {{ $name }} ({{ $user->email }})</div>
    <div><strong>{{ __('guide_area.account.mail.label_type') }}</strong> {{ __('guide_area.account.types.'.($user->guide_type === 'company' ? 'company' : 'private')) }}</div>
    @if ($user->guide_type === 'company')
        <div><strong>{{ __('guide_area.account.mail.label_company') }}</strong> {{ $info?->company_name }}{{ $info?->legal_form ? ' ('.$info->legal_form.')' : '' }}</div>
    @endif
    <div><strong>{{ __('guide_area.account.mail.label_vat') }}</strong> {{ $user->tax_id ?: ($info?->vat_not_registered ? __('guide_area.account.vat_not_registered') : '—') }}</div>
</div>

<div style="text-align: center; margin: 28px 0;">
    <a href="{{ route('admin.guide-billing.index') }}" target="_blank" style="background-color: #e8604c; padding: 12px 24px; color: #ffffff !important; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 600; display: inline-block;">
        {{ __('guide_area.account.mail.admin_cta') }}
    </a>
</div>

@include('mails.partials.cag-email-footer')
