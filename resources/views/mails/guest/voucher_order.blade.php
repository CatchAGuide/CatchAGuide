@php
    $copy = 'emails.voucher_order_guest';
    $font = "font-family:'Inter',Helvetica,Arial,sans-serif;";
    $heading = "font-family:'Archivo','Arial Black',Arial,sans-serif;";
    $mono = "font-family:'IBM Plex Mono',Menlo,Consolas,monospace;";
    $label = 'padding:26px 32px 10px;'.$font.'font-size:11px;line-height:1.2;letter-spacing:0.08em;text-transform:uppercase;font-weight:600;color:#3B5583;';
    $kvLabel = $font.'font-size:14px;line-height:1.45;color:#5A6478;';
    $kvValue = $font.'font-size:14px;line-height:1.45;color:#1A1B30;text-align:right;font-weight:500;';
    $rows = array_values(array_filter([
        [__($copy.'.tour'), $tourTitle],
        $tourLocation !== '' ? [__($copy.'.location'), $tourLocation] : null,
        [__($copy.'.guests'), $guests],
        [__($copy.'.recipients'), $recipients],
        $sender !== '' ? [__($copy.'.sender'), $sender] : null,
        [__($copy.'.guide'), $guideName],
        [__($copy.'.code'), $code],
        [__($copy.'.valid_until'), $validUntil],
    ]));
@endphp
{{-- Header, footer and base styles come from the shared CaG mail shell. --}}
<x-mail.cag-shell :title="__($copy.'.title')" :preheader="__($copy.'.preheader', ['guide' => $guideName])" :reason="__($copy.'.footer_reason', ['site' => $site])">
<x-slot:styles>
<style>
@media only screen and (max-width:480px){
 .kv-l{padding:12px 0 2px!important;font-size:13px!important;}
 .kv-v{padding:0 0 12px!important;border-top:0!important;text-align:left!important;}
}
</style>
</x-slot:styles>

{{-- Greeting --}}
<tr><td class="px t-main" style="padding:28px 32px 0;{{ $font }}font-size:15px;line-height:1.55;color:#1A1B30;">
<p style="margin:0 0 12px;">{{ __($copy.'.greeting', ['name' => $buyerFirstName]) }}</p>
<p style="margin:0;">{{ __($copy.'.intro', ['tour' => $tourTitle]) }}</p>
</td></tr>

{{-- Payment: made directly to the guide --}}
<tr><td class="px" style="padding:22px 32px 0;"><table role="presentation" class="bg-note" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#FFF4E5;border-radius:12px;border-collapse:separate;"><tr><td style="padding:16px 18px;">
<p class="t-main" style="margin:0 0 6px;{{ $font }}font-size:15px;line-height:1.4;font-weight:700;color:#1A1B30;">{{ __($copy.'.payment_title') }}</p>
<p class="t-main" style="margin:0;{{ $font }}font-size:14px;line-height:1.55;color:#1A1B30;">{{ __($copy.'.payment_text', ['guide' => $guideName]) }}</p>
</td></tr></table></td></tr>

{{-- Order summary --}}
<tr><td class="px t-label" style="{{ $label }}">{{ __($copy.'.section_order') }}</td></tr>
<tr><td class="px" style="padding:0 32px;"><table role="presentation" class="bd" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #E6E8EC;border-radius:12px;border-collapse:separate;"><tr><td style="padding:6px 18px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
@foreach($rows as $i => [$rowLabel, $rowValue])
<tr><td class="kv-l t-muted bd" valign="top" style="padding:11px 12px 11px 0;{{ $kvLabel }}width:40%;{{ $i ? 'border-top:1px solid #E6E8EC;' : '' }}">{{ $rowLabel }}</td><td class="kv-v t-main bd" valign="top" style="padding:11px 0;{{ $kvValue }}{{ $i ? 'border-top:1px solid #E6E8EC;' : '' }}">{{ $rowValue }}</td></tr>
@endforeach
<tr><td class="t-main bd" valign="bottom" style="padding:14px 12px 12px 0;{{ $font }}font-size:14px;line-height:1.4;font-weight:600;color:#1A1B30;border-top:1px solid #E6E8EC;">{{ __($copy.'.price') }}</td><td class="t-main bd" align="right" valign="bottom" style="padding:14px 0 12px;{{ $mono }}font-size:18px;line-height:1.3;font-weight:600;color:#1A1B30;white-space:nowrap;border-top:1px solid #E6E8EC;">{{ $price }}</td></tr>
</table>
</td></tr></table></td></tr>

{{-- Personal message --}}
@if($personalMessage !== '')
<tr><td class="px t-label" style="{{ $label }}">{{ __($copy.'.section_message') }}</td></tr>
<tr><td class="px" style="padding:0 32px;"><table role="presentation" class="bd" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #E6E8EC;border-radius:12px;border-collapse:separate;"><tr><td style="padding:16px 18px;"><p class="t-main" style="margin:0;{{ $font }}font-size:15px;line-height:1.55;font-style:italic;color:#1A1B30;">{!! nl2br(e(__($copy.'.quote', ['message' => $personalMessage]))) !!}</p></td></tr></table></td></tr>
@endif

{{-- Next steps --}}
<tr><td class="px" style="padding:32px 32px 0;">
<h2 class="t-main" style="margin:0 0 16px;{{ $heading }}font-size:17px;line-height:1.3;font-weight:700;color:#1A1B30;">{{ __($copy.'.next_title') }}</h2>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
@foreach([1, 2, 3] as $step)
<tr><td valign="top" width="36" style="padding:0 0 14px;width:36px;"><table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr><td width="24" height="24" align="center" style="width:24px;height:24px;border-radius:12px;background:#3B5583;{{ $font }}font-size:12px;font-weight:700;line-height:24px;color:#FFFFFF;">{{ $step }}</td></tr></table></td><td class="t-main" valign="top" style="padding:2px 0 14px;{{ $font }}font-size:14px;line-height:1.5;color:#1A1B30;">{{ __($copy.'.step_'.$step.($step === 3 ? ($ownDesign ? '_own' : '_pdf') : ''), ['guide' => $guideName]) }}</td></tr>
@endforeach
</table>
</td></tr>

{{-- Responsibilities + contact --}}
<tr><td class="px" style="padding:8px 32px 36px;">
<p class="t-soft" style="margin:0;{{ $font }}font-size:12px;line-height:1.55;color:#6B7489;">{{ __($copy.'.disclaimer') }}</p>
@if($contactEmail !== '')
<p class="t-muted" style="margin:16px 0 0;{{ $font }}font-size:14px;line-height:1.55;color:#5A6478;">{!! __($copy.'.questions', ['email' => '<a href="mailto:'.e($contactEmail).'" style="color:#3B5583;text-decoration:underline;">'.e($contactEmail).'</a>']) !!}</p>
@endif
</td></tr>

</x-mail.cag-shell>
