@php
    $copy = 'emails.camp_checkout_guest';
    $font = "font-family:'Inter',Helvetica,Arial,sans-serif;";
    $heading = "font-family:'Archivo','Arial Black',Arial,sans-serif;";
    $mono = "font-family:'IBM Plex Mono',Menlo,Consolas,monospace;";
    $label = 'padding:26px 32px 10px;'.$font.'font-size:11px;line-height:1.2;letter-spacing:0.08em;text-transform:uppercase;font-weight:600;color:#3B5583;';
    $kvLabel = $font.'font-size:14px;line-height:1.45;color:#5A6478;';
    $kvValue = $font.'font-size:14px;line-height:1.45;color:#1A1B30;text-align:right;font-weight:500;';
@endphp
{{-- Header, footer and base styles come from the shared CaG mail shell (offer builder spec OQ6). --}}
<x-mail.cag-shell :title="__($copy.'.title')" :preheader="__($copy.'.preheader')" :reason="__($copy.'.footer_reason', ['site' => $site])">
<x-slot:styles>
<style>
@media only screen and (max-width:480px){
 .kv-l{padding:12px 0 2px!important;font-size:13px!important;}
 .kv-v{padding:0 0 12px!important;border-top:0!important;}
}
</style>
</x-slot:styles>

{{-- Greeting --}}
<tr><td class="px t-main" style="padding:28px 32px 0;{{ $font }}font-size:15px;line-height:1.55;color:#1A1B30;">
<p style="margin:0 0 12px;">{{ __($copy.'.greeting', ['name' => $firstName]) }}</p>
<p style="margin:0;">{{ __($copy.'.intro', ['camp' => $campTitle]) }}</p>
</td></tr>

{{-- Stay --}}
<tr><td class="px t-label" style="{{ $label }}">{{ __($copy.'.section_stay') }}</td></tr>
<tr><td class="px" style="padding:0 32px;"><table role="presentation" class="bd" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #E6E8EC;border-radius:12px;border-collapse:separate;"><tr><td style="padding:18px 18px 6px;">
<div class="t-main" style="{{ $font }}font-size:16px;line-height:1.35;font-weight:700;color:#1A1B30;">{{ $campTitle }}</div>
@if($campLocation !== '')
<div class="t-soft" style="{{ $font }}font-size:14px;line-height:1.45;color:#6B7489;margin-top:3px;">{{ $campLocation }}</div>
@endif
<div class="bd" style="height:14px;line-height:14px;font-size:0;border-bottom:1px solid #E6E8EC;">&nbsp;</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
@if($arrival)
<tr><td class="kv-l t-muted" valign="top" style="padding:11px 12px 11px 0;{{ $kvLabel }}width:40%;">{{ __($copy.'.arrival') }}</td><td class="kv-v t-main" valign="top" style="padding:11px 0;{{ $kvValue }}">{{ $arrival }}</td></tr>
@endif
<tr><td colspan="2" class="bd" style="padding:0;{{ $arrival ? 'border-top:1px solid #E6E8EC;' : '' }}"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
<td width="50%" valign="top" style="width:50%;padding:11px 16px 11px 0;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td class="t-muted" style="{{ $kvLabel }}">{{ __($copy.'.nights') }}</td><td class="t-main" style="{{ $kvValue }}">{{ $nights }}</td></tr></table></td>
<td width="50%" valign="top" class="bd" style="width:50%;padding:11px 0 11px 16px;border-left:1px solid #E6E8EC;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td class="t-muted" style="{{ $kvLabel }}">{{ __($copy.'.persons') }}</td><td class="t-main" style="{{ $kvValue }}">{{ $persons }}</td></tr></table></td>
</tr></table></td></tr>
</table>
</td></tr></table></td></tr>

{{-- Selection & estimate --}}
<tr><td class="px t-label" style="{{ $label }}">{{ __($copy.'.section_selection') }}</td></tr>
<tr><td class="px" style="padding:0 32px;"><table role="presentation" class="bg-soft" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F6F7F9;border-radius:12px;border-collapse:separate;"><tr><td style="padding:18px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
@foreach($lines as $line)
<tr><td class="t-muted" style="padding:5px 12px 5px 0;{{ $font }}font-size:14px;line-height:1.45;color:#5A6478;">{{ $line['label'] }}</td><td class="t-main" align="right" style="padding:5px 0;{{ $mono }}font-size:14px;line-height:1.45;color:#1A1B30;white-space:nowrap;">{{ $line['amount'] }}</td></tr>
@endforeach
<tr><td colspan="2" style="padding:10px 0 0;"><div class="rule" style="height:1px;line-height:1px;font-size:0;background:#E3E6EC;">&nbsp;</div></td></tr>
<tr><td class="t-main" valign="bottom" style="padding:12px 12px 0 0;{{ $font }}font-size:14px;line-height:1.4;font-weight:600;color:#1A1B30;">{{ __($copy.'.total') }}</td><td class="t-main" align="right" valign="bottom" style="padding:12px 0 0;{{ $mono }}font-size:18px;line-height:1.3;font-weight:600;color:#1A1B30;white-space:nowrap;">{{ $total }}</td></tr>
</table>
<p class="t-soft" style="margin:12px 0 0;{{ $font }}font-size:12px;line-height:1.5;color:#6B7489;">{{ __($copy.'.estimate_note') }}</p>
</td></tr></table></td></tr>

{{-- Guest message --}}
@if($guestNote !== '')
<tr><td class="px t-label" style="{{ $label }}">{{ __($copy.'.section_message') }}</td></tr>
<tr><td class="px" style="padding:0 32px;"><table role="presentation" class="bd" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #E6E8EC;border-radius:12px;border-collapse:separate;"><tr><td style="padding:16px 18px;"><p class="t-main" style="margin:0;{{ $font }}font-size:15px;line-height:1.55;font-style:italic;color:#1A1B30;">{!! nl2br(e(__($copy.'.quote', ['message' => $guestNote]))) !!}</p></td></tr></table></td></tr>
@endif

{{-- Next steps --}}
<tr><td class="px" style="padding:32px 32px 0;">
<h2 class="t-main" style="margin:0 0 16px;{{ $heading }}font-size:17px;line-height:1.3;font-weight:700;color:#1A1B30;">{{ __($copy.'.next_title') }}</h2>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
@foreach([1, 2, 3] as $step)
<tr><td valign="top" width="36" style="padding:0 0 14px;width:36px;"><table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr><td width="24" height="24" align="center" style="width:24px;height:24px;border-radius:12px;background:#3B5583;{{ $font }}font-size:12px;font-weight:700;line-height:24px;color:#FFFFFF;">{{ $step }}</td></tr></table></td><td class="t-main" valign="top" style="padding:2px 0 14px;{{ $font }}font-size:14px;line-height:1.5;color:#1A1B30;">{{ __($copy.'.step_'.$step) }}</td></tr>
@endforeach
</table>
</td></tr>

{{-- CTA --}}
<tr><td class="px" align="center" style="padding:16px 32px 36px;">
<table role="presentation" class="btn-t" cellpadding="0" cellspacing="0" border="0" align="center"><tr><td align="center" bgcolor="#E8604C" style="background:#E8604C;border-radius:10px;">
<a class="btn-a" href="{{ $campUrl }}" target="_blank" style="display:inline-block;min-width:220px;height:48px;line-height:48px;padding:0 28px;box-sizing:border-box;{{ $font }}font-size:15px;font-weight:600;color:#FFFFFF;text-decoration:none;border-radius:10px;text-align:center;">{{ __($copy.'.cta') }}</a>
</td></tr></table>
@if($contactEmail !== '')
<p class="t-muted" style="margin:18px 0 0;{{ $font }}font-size:14px;line-height:1.55;color:#5A6478;">{!! __($copy.'.questions', ['email' => '<a href="mailto:'.e($contactEmail).'" style="color:#3B5583;text-decoration:underline;">'.e($contactEmail).'</a>']) !!}</p>
@endif
</td></tr>

</x-mail.cag-shell>
