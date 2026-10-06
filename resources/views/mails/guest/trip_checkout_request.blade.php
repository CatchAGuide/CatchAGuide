@php
    $copy = 'emails.trip_checkout_guest';
    $font = "font-family:'Inter',Helvetica,Arial,sans-serif;";
    $heading = "font-family:'Archivo','Arial Black',Arial,sans-serif;";
    $mono = "font-family:'IBM Plex Mono',Menlo,Consolas,monospace;";
    $label = 'margin:32px 0 10px;'.$font.'font-size:11px;line-height:1.2;letter-spacing:0.08em;text-transform:uppercase;font-weight:600;color:#3A4466;';
@endphp
{{-- Header, footer and base styles come from the shared CaG mail shell (offer builder spec OQ6). --}}
<x-mail.cag-shell :title="__($copy.'.title')" :preheader="$preheader" :reason="__($copy.'.footer_reason', ['site' => $site])">
<x-slot:styles>
<style>
@media only screen and (max-width:480px){
 .kv-l{font-size:13px!important;}
 .kv-v{padding-top:2px!important;font-size:15px!important;}
}
@media (prefers-color-scheme:dark){
 .bg-box{background:#202336!important;}
 .t-accent{color:#A9B3E0!important;}
 .step{background:#A9B3E0!important;color:#0E0F1A!important;}
}
</style>
</x-slot:styles>

<tr><td class="px" style="padding:36px 40px 40px;{{ $font }}">

{{-- Greeting --}}
<p class="t-main" style="margin:0 0 12px;font-size:16px;line-height:1.55;color:#1F2230;">{{ __($copy.'.greeting', ['name' => $firstName]) }}</p>
<p class="t-main" style="margin:0;font-size:16px;line-height:1.55;color:#1F2230;">{{ $intro }}</p>

{{-- Trip --}}
<div class="t-accent" style="{{ $label }}">{{ __($copy.'.section_trip') }}</div>
<table role="presentation" class="bd bg-box" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #E4E6EA;border-radius:10px;border-collapse:separate;background:#FFFFFF;">
<tr><td style="padding:16px;">
<div class="t-main" style="font-size:16px;line-height:1.35;font-weight:700;color:#1F2230;">{{ $tripTitle }}</div>
@if($tripLocation !== '')
<div class="t-muted" style="margin-top:4px;font-size:14px;line-height:1.4;color:#6B7080;">{{ $tripLocation }}</div>
@endif
</td></tr>
@foreach($rows as $row)
<tr><td class="bd" style="padding:12px 16px;border-top:1px solid #ECEDF0;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
<td class="kv-l t-label" valign="top" style="font-size:14px;line-height:1.4;color:#4A4F5E;">{{ $row['label'] }}</td>
<td class="kv-v t-main" valign="top" align="right" style="font-size:14px;line-height:1.4;font-weight:500;color:#1F2230;text-align:right;">{{ $row['value'] }}</td>
</tr></table></td></tr>
@endforeach
</table>

{{-- Price overview --}}
<div class="t-accent" style="{{ $label }}">{{ __($copy.'.section_price') }}</div>
<table role="presentation" class="bg-box" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F4F5F7;border-radius:10px;border-collapse:separate;">
@if($price)
<tr><td style="padding:16px 18px 14px;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
<td class="t-main" valign="top" style="padding-right:12px;font-size:14px;line-height:1.45;color:#1F2230;">{{ $price['label'] }} <span style="{{ $mono }}white-space:nowrap;">{{ $price['unit'] }}</span></td>
<td class="t-main" valign="top" align="right" style="{{ $mono }}font-size:14px;line-height:1.45;color:#1F2230;white-space:nowrap;">{{ $price['subtotal'] }}</td>
</tr></table></td></tr>
<tr><td style="padding:0 18px;"><div class="rule" style="height:1px;line-height:1px;font-size:0;background:#E1E3E8;">&nbsp;</div></td></tr>
@endif
<tr><td style="padding:{{ $price ? '14px' : '16px' }} 18px 16px;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
<td class="t-main" style="font-size:15px;line-height:1.4;font-weight:600;color:#1F2230;">{{ __($copy.'.total') }}</td>
<td class="t-accent" align="right" style="{{ $mono }}font-size:18px;line-height:1.3;font-weight:600;color:#1B1D36;white-space:nowrap;">{{ $total }}</td>
</tr></table></td></tr>
</table>
<p class="t-muted" style="margin:10px 0 0;font-size:13px;line-height:1.5;color:#6B7080;">{{ $note }}</p>

{{-- Guest message --}}
@if($guestNote !== '')
<div class="t-accent" style="{{ $label }}">{{ __($copy.'.section_message') }}</div>
<table role="presentation" class="bd bg-box" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #E4E6EA;border-radius:10px;border-collapse:separate;background:#FFFFFF;">
<tr><td class="t-main" style="padding:16px;font-size:15px;line-height:1.55;font-style:italic;color:#1F2230;">{!! nl2br(e(__($copy.'.quote', ['message' => $guestNote]))) !!}</td></tr>
</table>
@endif

{{-- Next steps --}}
<h2 class="t-main" style="margin:40px 0 18px;{{ $heading }}font-size:20px;line-height:1.25;font-weight:700;color:#1F2230;">{{ __($copy.'.next_title') }}</h2>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
@foreach($steps as $i => $step)
<tr>
<td width="28" valign="top" style="width:28px;padding:0 0 16px;"><table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr><td class="step" width="28" height="28" align="center" style="width:28px;height:28px;border-radius:14px;background:#2F3A5C;{{ $font }}font-size:14px;font-weight:600;line-height:28px;color:#FFFFFF;">{{ $i + 1 }}</td></tr></table></td>
<td class="t-main" valign="top" style="padding:3px 0 16px 14px;font-size:15px;line-height:1.5;color:#1F2230;">{{ $step }}</td>
</tr>
@endforeach
</table>

{{-- CTA --}}
<table role="presentation" class="btn-t" cellpadding="0" cellspacing="0" border="0" align="center" style="margin:20px auto 0;"><tr><td align="center" bgcolor="#E25C45" style="background:#E25C45;border-radius:10px;">
<a class="btn-a" href="{{ $tripUrl }}" target="_blank" style="display:inline-block;min-width:220px;height:48px;line-height:48px;padding:0 32px;box-sizing:border-box;{{ $font }}font-size:16px;font-weight:600;color:#FFFFFF;text-decoration:none;border-radius:10px;text-align:center;">{{ __($copy.'.cta') }}</a>
</td></tr></table>
@if($contactEmail !== '')
<p class="t-label" style="margin:20px 0 0;font-size:14px;line-height:1.55;color:#4A4F5E;text-align:center;">{!! __($copy.'.questions', ['email' => '<a href="mailto:'.e($contactEmail).'" class="t-accent" style="color:#3A4466;text-decoration:underline;font-weight:500;">'.e($contactEmail).'</a>']) !!}</p>
@endif

</td></tr>

</x-mail.cag-shell>
