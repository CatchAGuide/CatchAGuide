@php
    $name = trim($user->firstname . ' ' . $user->lastname);
    $info = $user->information;
@endphp
@include('mails.partials.cag-email-header', ['title' => __('emails.guide_admin_new_request_title')])

<p style="font-size: 14px; line-height: 1.6; color: #444;">{{ __('emails.guide_admin_new_request_intro') }}</p>

<div style="border: 1px solid #e0e0e0; border-radius: 12px; padding: 16px 20px; margin: 20px 0; background: #f8f9fa;">
    <p style="font-size: 14px; margin: 0 0 8px;"><strong>{{ __('emails.guide_admin_new_request_name') }}</strong> {{ $name ?: '—' }}</p>
    <p style="font-size: 14px; margin: 0 0 8px;"><strong>Email:</strong> <a href="mailto:{{ $user->email }}" style="color: #e8604c;">{{ $user->email }}</a></p>
    <p style="font-size: 14px; margin: 0 0 8px;"><strong>{{ __('emails.guide_admin_new_request_type') }}</strong> {{ ucfirst($user->guide_type ?? 'private') }}</p>
    @if($user->phone || ($info?->phone))
        <p style="font-size: 14px; margin: 0 0 8px;"><strong>{{ __('emails.guide_admin_new_request_phone') }}</strong> {{ $user->phone ?? $info->phone }}</p>
    @endif
    @if($info?->city || $info?->address)
        <p style="font-size: 14px; margin: 0;"><strong>{{ __('emails.guide_admin_new_request_location') }}</strong>
            {{ collect([$info->address, $info->address_number, $info->postal, $info->city])->filter()->implode(', ') }}
        </p>
    @endif
</div>

<div style="text-align: center; margin: 28px 0;">
    <a href="{{ route('admin.guide-requests.index') }}" target="_blank" style="background-color: #e8604c; padding: 12px 24px; color: #ffffff !important; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 600; display: inline-block;">
        {{ __('emails.guide_admin_new_request_cta') }}
    </a>
</div>

@include('mails.partials.cag-email-footer')
