@php
    $copy = 'emails.voucher_paid_guest';
    $font = "font-family:'Inter',Helvetica,Arial,sans-serif;";
    $heading = "font-family:'Archivo','Arial Black',Arial,sans-serif;";
    $label = 'padding:26px 32px 10px;'.$font.'font-size:11px;line-height:1.2;letter-spacing:0.08em;text-transform:uppercase;font-weight:600;color:#3B5583;';
    $kvLabel = $font.'font-size:14px;line-height:1.45;color:#5A6478;';
    $kvValue = $font.'font-size:14px;line-height:1.45;color:#1A1B30;text-align:right;font-weight:500;';
    $guideContact = implode(' · ', array_filter([$guidePhone, $guideEmail]));
    $rows = array_values(array_filter([
        [__($copy.'.code'), $code],
        [__($copy.'.tour'), $tourTitle],
        [__($copy.'.guests'), $guests],
        [__($copy.'.recipients'), $recipients],
        [__($copy.'.guide'), $guideName],
        $guideContact !== '' ? [__($copy.'.guide_contact'), $guideContact] : null,
        [__($copy.'.valid_until'), $validUntil],
    ]));
    // CaG voucher attached, otherwise the guide sends their own.
    $mode = $withPdf ? 'pdf' : 'own';
@endphp
{{-- Header, footer and base styles come from the shared CaG mail shell. --}}
<x-mail.cag-shell :title="__($copy.'.title_'.$mode)" :preheader="__($copy.'.preheader_'.$mode, ['guide' => $guideName])" :reason="__($copy.'.footer_reason', ['site' => $site])">
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
<p style="margin:0;">{{ __($copy.'.intro_'.$mode, ['guide' => $guideName, 'tour' => $tourTitle]) }}</p>
</td></tr>

{{-- No date: any day until the validity ends --}}
<tr><td class="px" style="padding:22px 32px 0;"><table role="presentation" class="bg-note" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#EAF6EE;border-radius:12px;border-collapse:separate;"><tr><td style="padding:16px 18px;">
<p class="t-main" style="margin:0 0 6px;{{ $font }}font-size:15px;line-height:1.4;font-weight:700;color:#1A1B30;">{{ __($copy.'.any_day_title') }}</p>
<p class="t-main" style="margin:0;{{ $font }}font-size:14px;line-height:1.55;color:#1A1B30;">{{ __($copy.'.any_day_text', ['date' => $validUntil]) }}</p>
</td></tr></table></td></tr>

{{-- Voucher summary --}}
<tr><td class="px t-label" style="{{ $label }}">{{ __($copy.'.section_voucher') }}</td></tr>
<tr><td class="px" style="padding:0 32px;"><table role="presentation" class="bd" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #E6E8EC;border-radius:12px;border-collapse:separate;"><tr><td style="padding:6px 18px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
@foreach($rows as $i => [$rowLabel, $rowValue])
<tr><td class="kv-l t-muted bd" valign="top" style="padding:11px 12px 11px 0;{{ $kvLabel }}width:40%;{{ $i ? 'border-top:1px solid #E6E8EC;' : '' }}">{{ $rowLabel }}</td><td class="kv-v t-main bd" valign="top" style="padding:11px 0;{{ $kvValue }}{{ $i ? 'border-top:1px solid #E6E8EC;' : '' }}">{{ $rowValue }}</td></tr>
@endforeach
</table>
</td></tr></table></td></tr>

{{-- Next steps --}}
<tr><td class="px" style="padding:32px 32px 0;">
<h2 class="t-main" style="margin:0 0 16px;{{ $heading }}font-size:17px;line-height:1.3;font-weight:700;color:#1A1B30;">{{ __($copy.'.next_title') }}</h2>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
@foreach(['step_1_'.$mode, 'step_2', 'step_3'] as $stepKey)
<tr><td valign="top" width="36" style="padding:0 0 14px;width:36px;"><table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr><td width="24" height="24" align="center" style="width:24px;height:24px;border-radius:12px;background:#3B5583;{{ $font }}font-size:12px;font-weight:700;line-height:24px;color:#FFFFFF;">{{ $loop->iteration }}</td></tr></table></td><td class="t-main" valign="top" style="padding:2px 0 14px;{{ $font }}font-size:14px;line-height:1.5;color:#1A1B30;">{{ __($copy.'.'.$stepKey, ['guide' => $guideName, 'code' => $code]) }}</td></tr>
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
