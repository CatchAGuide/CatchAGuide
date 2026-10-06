@props([
    'title',
    'preheader' => '',
    'reason' => '',
    'imprintUrl' => null,
    'privacyUrl' => null,
    'homeUrl' => null,
    // 'auto' follows the reader's light/dark setting (emails); 'light' keeps the brand look (customer page).
    'colorScheme' => 'auto',
    // Card width: 600 for emails; the customer page uses a wider card on desktop.
    'maxWidth' => 600,
])
{{--
    CaG system-mail shell: navy header with the centered logo and a white title, white card body
    ({{ $slot }} = table rows), navy footer with contact line, reason, legal links and ©.
    Used by the sales offer/confirmation emails and, unchanged, by the customer offer page so
    both look identical.
--}}
@php
    $font = "font-family:'Inter',Helvetica,Arial,sans-serif;";
    $heading = "font-family:'Archivo','Arial Black',Arial,sans-serif;";
    $contactEmail = (string) config('mail.admin_email');
    $contactPhone = config('cag.contact_num') ? '+49 (0) '.config('cag.contact_num') : null;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="x-apple-disable-message-reformatting">
<meta name="color-scheme" content="{{ $colorScheme === 'light' ? 'light' : 'light dark' }}">
<meta name="supported-color-schemes" content="{{ $colorScheme === 'light' ? 'light' : 'light dark' }}">
{{ $head ?? '' }}
<title>{{ $title }}</title>
<style>
body{margin:0;padding:0;}
a:hover{text-decoration:none!important;}
@media only screen and (max-width:480px){
 .wrap{padding:0!important;}
 .card{border-radius:0!important;}
 .px{padding-left:20px!important;padding-right:20px!important;}
 .kv-l,.kv-v{display:block!important;width:100%!important;text-align:left!important;box-sizing:border-box;}
 .btn-t{width:100%!important;}
 .btn-a{display:block!important;min-width:0!important;}
}
@if($colorScheme !== 'light')
@media (prefers-color-scheme:dark){
 .bg-page{background:#0B0C16!important;}
 .card{background:#12131F!important;}
 .t-main{color:#E8ECEC!important;}
 .t-muted{color:#B6BCCB!important;}
 .t-soft{color:#969DB0!important;}
 .t-label{color:#9DB4DE!important;}
 .bd{border-color:#2C2F45!important;}
 .bg-soft{background:#1E2032!important;}
 .rule{background:#2C2F45!important;}
}
@endif
</style>
{{ $styles ?? '' }}
</head>
<body class="bg-page" style="margin:0;padding:0;background:#F1F1F2;">
@if($preheader !== '')
<div style="display:none;max-height:0;overflow:hidden;opacity:0;mso-hide:all;">{{ $preheader }} &#847; &#847; &#847;</div>
@endif
<table role="presentation" class="bg-page" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F1F1F2;"><tr><td class="wrap" align="center" style="padding:32px 16px;">
<table role="presentation" class="card" width="{{ $maxWidth }}" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:{{ $maxWidth }}px;background:#FFFFFF;border-radius:12px;border-collapse:separate;overflow:hidden;">

{{-- Header: logo and title only --}}
<tr><td align="center" style="background:#1A1B30;padding:28px;">
<a href="{{ $homeUrl ?? route('welcome') }}" target="_blank" style="text-decoration:none;display:inline-block;">
<img src="{{ asset('assets/images/logo/CatchAGuide2_Logo_PNG.png') }}" alt="Catch A Guide" width="150" style="display:block;width:150px;max-width:150px;height:auto;margin:0 auto;border:0;">
</a>
<h1 style="margin:18px 0 0;{{ $heading }}font-size:22px;line-height:1.25;font-weight:700;color:#FFFFFF;">{{ $title }}</h1>
</td></tr>

{{ $slot }}

{{-- Footer --}}
<tr><td align="center" class="px" style="background:#1A1B30;padding:28px 32px;{{ $font }}font-size:13px;line-height:1.6;color:rgba(255,255,255,0.72);">
<img src="{{ asset('assets/images/logo/CatchAGuide2_Logo_PNG.png') }}" alt="Catch A Guide" width="96" style="display:block;width:96px;max-width:96px;height:auto;margin:0 auto;border:0;">
<p style="margin:16px 0 0;color:#FFFFFF;">Catch A Guide @if($contactEmail !== '')· <a href="mailto:{{ $contactEmail }}" style="color:#FFFFFF;text-decoration:none;">{{ $contactEmail }}</a>@endif @if($contactPhone)· {{ $contactPhone }}@endif</p>
@if($reason !== '')
<p style="margin:8px 0 0;">{{ $reason }}</p>
@endif
<p style="margin:12px 0 0;"><a href="{{ $imprintUrl ?? route('law.imprint') }}" style="color:#FFFFFF;text-decoration:underline;">{{ __('emails.cag_shell.imprint') }}</a> &nbsp;·&nbsp; <a href="{{ $privacyUrl ?? route('law.data-protection') }}" style="color:#FFFFFF;text-decoration:underline;">{{ __('emails.cag_shell.privacy') }}</a></p>
<p style="margin:12px 0 0;">© {{ date('Y') }} Catch A Guide</p>
</td></tr>

</table>
</td></tr></table>
{{ $scripts ?? '' }}
</body>
</html>
