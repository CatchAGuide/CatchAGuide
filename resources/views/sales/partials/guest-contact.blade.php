{{--
    Guest / recipient contact on booking confirmations (page + email). Shown so CC'd
    hosts/providers can reach the guest. Needs $doc['guest'] and $doc['locale'].
--}}
@php
    $guest = $doc['guest'] ?? null;
    $locale = $doc['locale'] ?? app()->getLocale();
    $font = "font-family:'Inter',Helvetica,Arial,sans-serif;";
    $copy = fn (string $key, array $replace = []) => __('sales.customer.'.$key, $replace, $locale);
@endphp
@if(is_array($guest))
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="bd bg-soft" style="background:#F7F8FB;border:1px solid #E6E8EC;border-radius:10px;border-collapse:separate;">
<tr><td style="padding:16px 18px;{{ $font }}">
<div class="t-main" style="font-size:15px;line-height:1.35;font-weight:700;color:#1A1B30;">{{ $copy('guest_title') }}</div>
@if(($guest['name'] ?? '') !== '')
<div class="t-main" style="font-size:14px;line-height:1.5;font-weight:600;color:#1A1B30;margin-top:8px;">{{ $guest['name'] }}</div>
@endif
@if(($guest['phone'] ?? '') !== '')
<div style="margin-top:6px;font-size:14px;line-height:1.5;"><a href="tel:{{ preg_replace('/[^\d+]/', '', $guest['phone']) }}" class="t-main" style="color:#1A1B30;text-decoration:none;font-weight:600;">&#9742;&nbsp; {{ $guest['phone'] }}</a></div>
@endif
@if(($guest['email'] ?? '') !== '')
<div style="margin-top:2px;font-size:14px;line-height:1.5;"><a href="mailto:{{ $guest['email'] }}" class="t-main" style="color:#1A1B30;text-decoration:none;font-weight:600;">&#9993;&nbsp; {{ $guest['email'] }}</a></div>
@endif
@if(($guest['travellers'] ?? []) !== [])
<div class="t-soft" style="font-size:13px;line-height:1.5;color:#6B7489;margin-top:8px;">{{ $copy('guest_travellers') }}: {{ implode(', ', $guest['travellers']) }}</div>
@endif
</td></tr>
</table>
@endif
