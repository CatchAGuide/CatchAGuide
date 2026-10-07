{{--
    "Help or questions?" box with the CaG phone number and email. Table markup so the same
    partial works on the customer page and in the offer/confirmation emails. Needs $locale.
--}}
@php
    $font = "font-family:'Inter',Helvetica,Arial,sans-serif;";
    $contactEmail = (string) config('mail.admin_email');
    $contactNumber = (string) config('cag.contact_num');
    $contactPhone = $contactNumber !== '' ? '+49 (0) '.$contactNumber : null;
    $contactTel = $contactNumber !== '' ? '+49'.preg_replace('/\D/', '', ltrim($contactNumber, '0')) : null;
@endphp
@if($contactEmail !== '' || $contactPhone)
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="bd bg-soft" style="background:#F7F8FB;border:1px solid #E6E8EC;border-radius:10px;border-collapse:separate;">
<tr><td style="padding:16px 18px;{{ $font }}">
<div class="t-main" style="font-size:15px;line-height:1.35;font-weight:700;color:#1A1B30;">{{ __('sales.customer.contact_title', [], $locale) }}</div>
<div class="t-soft" style="font-size:13px;line-height:1.5;color:#6B7489;margin-top:2px;">{{ __('sales.customer.contact_text', [], $locale) }}</div>
<div class="t-label" style="font-size:11px;line-height:1.2;letter-spacing:0.08em;text-transform:uppercase;font-weight:600;color:#3B5583;margin-top:12px;">{{ __('sales.customer.contact_label', [], $locale) }}</div>
@if($contactPhone)
<div style="margin-top:6px;font-size:14px;line-height:1.5;"><a href="tel:{{ $contactTel }}" class="t-main" style="color:#1A1B30;text-decoration:none;font-weight:600;">&#9742;&nbsp; {{ $contactPhone }}</a></div>
@endif
@if($contactEmail !== '')
<div style="margin-top:2px;font-size:14px;line-height:1.5;"><a href="mailto:{{ $contactEmail }}" class="t-main" style="color:#1A1B30;text-decoration:none;font-weight:600;">&#9993;&nbsp; {{ $contactEmail }}</a></div>
@endif
</td></tr>
</table>
@endif
