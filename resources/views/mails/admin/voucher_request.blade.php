@php
    $copy = 'emails.voucher_request_admin';
    $font = "font-family:'Inter',Helvetica,Arial,sans-serif;";
    $label = 'padding:26px 32px 10px;'.$font.'font-size:11px;line-height:1.2;letter-spacing:0.08em;text-transform:uppercase;font-weight:600;color:#3B5583;';
    $kvLabel = $font.'font-size:14px;line-height:1.45;color:#5A6478;';
    $kvValue = $font.'font-size:14px;line-height:1.45;color:#1A1B30;text-align:right;font-weight:500;';
    $sections = [
        'section_request' => array_values(array_filter([
            [__($copy.'.code'), e($code)],
            [__($copy.'.amount'), e($amount)],
            [__($copy.'.where'), e($where)],
            $recipient !== '' ? [__($copy.'.recipient'), e($recipient)] : null,
            [__($copy.'.requested_on'), e($requestedOn)],
        ])),
        'section_buyer' => [
            [__($copy.'.name'), e($buyerName)],
            [__($copy.'.email'), '<a href="mailto:'.e($buyerEmail).'" style="color:#3B5583;">'.e($buyerEmail).'</a>'],
            [__($copy.'.phone'), '<a href="tel:'.e(preg_replace('/[^0-9+]/', '', $buyerPhone)).'" style="color:#3B5583;">'.e($buyerPhone).'</a>'],
            [__($copy.'.language'), e($language)],
        ],
    ];
@endphp
{{-- Header, footer and base styles come from the shared CaG mail shell. --}}
<x-mail.cag-shell :title="__($copy.'.title')" :preheader="__($copy.'.preheader', ['name' => $buyerName, 'amount' => $amount])" :reason="__($copy.'.footer_reason', ['site' => $site])">
<x-slot:styles>
<style>
@media only screen and (max-width:480px){
 .kv-l{padding:12px 0 2px!important;font-size:13px!important;}
 .kv-v{padding:0 0 12px!important;border-top:0!important;text-align:left!important;}
}
</style>
</x-slot:styles>

<tr><td class="px t-main" style="padding:28px 32px 0;{{ $font }}font-size:15px;line-height:1.55;color:#1A1B30;">
<p style="margin:0;">{{ __($copy.'.intro') }}</p>
</td></tr>

@foreach($sections as $section => $rows)
<tr><td class="px t-label" style="{{ $label }}">{{ __($copy.'.'.$section) }}</td></tr>
<tr><td class="px" style="padding:0 32px;"><table role="presentation" class="bd" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #E6E8EC;border-radius:12px;border-collapse:separate;"><tr><td style="padding:6px 18px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
@foreach($rows as $i => [$rowLabel, $rowValue])
<tr><td class="kv-l t-muted bd" valign="top" style="padding:11px 12px 11px 0;{{ $kvLabel }}width:40%;{{ $i ? 'border-top:1px solid #E6E8EC;' : '' }}">{{ $rowLabel }}</td><td class="kv-v t-main bd" valign="top" style="padding:11px 0;{{ $kvValue }}{{ $i ? 'border-top:1px solid #E6E8EC;' : '' }}">{!! $rowValue !!}</td></tr>
@endforeach
</table>
</td></tr></table></td></tr>
@endforeach

<tr><td class="px" style="padding:28px 32px 36px;">
<table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr><td style="border-radius:999px;background:#E8604C;"><a href="{{ $adminUrl }}" style="display:inline-block;padding:12px 22px;{{ $font }}font-size:14px;font-weight:700;color:#FFFFFF;text-decoration:none;">{{ __($copy.'.open_admin') }}</a></td></tr></table>
</td></tr>

</x-mail.cag-shell>
