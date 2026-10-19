@php
    $copy = 'emails.vacation_review_request';
    $font = "font-family:'Inter',Helvetica,Arial,sans-serif;";
    $label = 'padding:26px 32px 10px;'.$font.'font-size:11px;line-height:1.2;letter-spacing:0.08em;text-transform:uppercase;font-weight:600;color:#3B5583;';
    $kvLabel = $font.'font-size:14px;line-height:1.45;color:#5A6478;';
    $kvValue = $font.'font-size:14px;line-height:1.45;color:#1A1B30;text-align:right;font-weight:500;';
@endphp
<x-mail.cag-shell :title="__($copy.'.title')" :preheader="__($copy.'.preheader')" :reason="__($copy.'.footer_reason', ['site' => $site])"
    :home-url="$homeUrl" :imprint-url="$imprintUrl" :privacy-url="$privacyUrl">

{{-- Greeting --}}
<tr><td class="px t-main" style="padding:28px 32px 0;{{ $font }}font-size:15px;line-height:1.55;color:#1A1B30;">
<p style="margin:0 0 12px;">{{ __($copy.'.greeting', ['name' => $firstName]) }}</p>
<p style="margin:0 0 12px;">{{ __($copy.'.intro', ['listing' => $listingTitle]) }}</p>
<p style="margin:0;">{{ __($copy.'.ask') }}</p>
</td></tr>

{{-- Stay --}}
<tr><td class="px t-label" style="{{ $label }}">{{ __($copy.'.section_stay') }}</td></tr>
<tr><td class="px" style="padding:0 32px;"><table role="presentation" class="bd" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #E6E8EC;border-radius:12px;border-collapse:separate;"><tr><td style="padding:18px;">
<div class="t-main" style="{{ $font }}font-size:16px;line-height:1.35;font-weight:700;color:#1A1B30;">{{ $listingTitle }}</div>
@if($listingLocation !== '')
<div class="t-soft" style="{{ $font }}font-size:14px;line-height:1.45;color:#6B7489;margin-top:3px;">{{ $listingLocation }}</div>
@endif
@if($dates || $persons)
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:8px;">
@if($dates)
<tr><td class="kv-l t-muted" valign="top" style="padding:8px 12px 0 0;{{ $kvLabel }}width:40%;">{{ __($copy.'.dates') }}</td><td class="kv-v t-main" valign="top" style="padding:8px 0 0;{{ $kvValue }}">{{ $dates }}</td></tr>
@endif
@if($persons)
<tr><td class="kv-l t-muted" valign="top" style="padding:8px 12px 0 0;{{ $kvLabel }}width:40%;">{{ __($copy.'.persons') }}</td><td class="kv-v t-main" valign="top" style="padding:8px 0 0;{{ $kvValue }}">{{ $persons }}</td></tr>
@endif
</table>
@endif
</td></tr></table></td></tr>

{{-- CTA --}}
<tr><td class="px" align="center" style="padding:32px 32px 36px;">
<p class="t-muted" style="margin:0 0 18px;{{ $font }}font-size:14px;line-height:1.55;color:#5A6478;">{{ __($copy.'.duration') }}</p>
<table role="presentation" class="btn-t" cellpadding="0" cellspacing="0" border="0" align="center"><tr><td align="center" bgcolor="#E8604C" style="background:#E8604C;border-radius:10px;">
<a class="btn-a" href="{{ $reviewUrl }}" target="_blank" style="display:inline-block;min-width:220px;height:48px;line-height:48px;padding:0 28px;box-sizing:border-box;{{ $font }}font-size:15px;font-weight:600;color:#FFFFFF;text-decoration:none;border-radius:10px;text-align:center;">{{ __($copy.'.cta') }}</a>
</td></tr></table>
<p class="t-main" style="margin:24px 0 0;{{ $font }}font-size:14px;line-height:1.55;color:#1A1B30;">{{ __($copy.'.thanks') }}</p>
@if($contactEmail !== '')
<p class="t-muted" style="margin:12px 0 0;{{ $font }}font-size:14px;line-height:1.55;color:#5A6478;">{!! __($copy.'.questions', ['email' => '<a href="mailto:'.e($contactEmail).'" style="color:#3B5583;text-decoration:underline;">'.e($contactEmail).'</a>']) !!}</p>
@endif
</td></tr>

</x-mail.cag-shell>
