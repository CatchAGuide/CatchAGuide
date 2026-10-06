{{--
    Offer / booking confirmation email (spec §8.4). $doc comes from SalesDocumentPresenter.
    Offer: greeting, two lines, validity, "view offer" button, signature.
    Confirmation: greeting, short text, price overview per product, "view booking" button.
--}}
@php
    $font = "font-family:'Inter',Helvetica,Arial,sans-serif;";
    $copy = fn (string $key, array $replace = []) => __('sales.customer.'.$key, $replace, $doc['locale']);
    $stripes = ['tour' => '#2F6FDE', 'camp' => '#2E8B57', 'trip' => '#E07A1F', 'custom' => '#7B4FC9'];
@endphp
<x-mail.cag-shell :title="$doc['title']" :preheader="$doc['mailText']" :reason="__('sales.customer.footer_reason', ['site' => $site], $doc['locale'])" :home-url="$homeUrl" :imprint-url="$imprintUrl" :privacy-url="$privacyUrl">

<tr><td class="px t-main" style="padding:28px 32px 0;{{ $font }}font-size:15px;line-height:1.55;color:#1A1B30;">
<p style="margin:0 0 12px;">{{ $doc['greeting'] }}</p>
<p style="margin:0;">{{ $doc['mailText'] }}</p>
</td></tr>

@if($doc['isOffer'])
@if($doc['validUntil'])
<tr><td class="px t-soft" style="padding:12px 32px 0;{{ $font }}font-size:13px;line-height:1.5;color:#5D6680;">{{ $copy('valid_until', ['date' => $doc['validUntil']]) }}</td></tr>
@endif
@else
<tr><td class="px t-label" style="padding:24px 32px 10px;{{ $font }}font-size:11px;line-height:1.2;letter-spacing:0.08em;text-transform:uppercase;font-weight:600;color:#3B5583;">{{ $copy('selection') }}</td></tr>
@foreach($doc['groups'] as $group)
<tr><td class="px" style="padding:0 32px 10px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="bd" style="border:1px solid #E6E8EC;border-left:4px solid {{ $stripes[$group['type']] ?? '#3B5583' }};border-radius:8px;border-collapse:separate;">
<tr><td style="padding:12px 14px 4px;{{ $font }}">
<span style="font-size:10px;letter-spacing:0.08em;text-transform:uppercase;font-weight:700;color:{{ $stripes[$group['type']] ?? '#3B5583' }};">{{ $group['kind'] }}</span>
<div class="t-main" style="font-size:15px;font-weight:700;color:#1A1B30;margin-top:2px;">{{ $group['title'] }}</div>
@if($group['location'] !== '')
<div class="t-soft" style="font-size:12px;color:#6B7489;">{{ $group['location'] }}</div>
@endif
</td></tr>
@foreach($group['lines'] as $line)
<tr><td style="padding:{{ $line['extra'] ? '4px 14px 4px 28px' : '8px 14px 4px' }};{{ $font }}">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
<td valign="top" class="t-main" style="font-size:13px;line-height:1.4;color:#1A1B30;">{{ $line['extra'] ? '+ ' : '' }}{{ $line['title'] }}@if($line['detail'] !== '')<br><span class="t-soft" style="font-size:12px;color:#6B7489;">{{ $line['detail'] }}</span>@endif</td>
<td valign="top" align="right" class="t-main" style="font-size:13px;line-height:1.4;color:#1A1B30;white-space:nowrap;padding-left:10px;">{{ $line['amount'] }}</td>
</tr></table>
</td></tr>
@endforeach
@if($group['subtotal'])
<tr><td class="bd" style="padding:8px 14px 10px;border-top:1px solid #E6E8EC;{{ $font }}"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
<td class="t-muted" style="font-size:12px;color:#5A6478;">{{ $copy('subtotal') }}</td>
<td align="right" class="t-main" style="font-size:13px;font-weight:600;color:#1A1B30;">{{ $group['subtotal'] }}</td>
</tr></table></td></tr>
@else
<tr><td style="padding:0 0 6px;font-size:0;line-height:0;">&nbsp;</td></tr>
@endif
</table>
</td></tr>
@endforeach
<tr><td class="px" style="padding:4px 32px 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="bg-soft" style="background:#F4F6FA;border-radius:8px;"><tr>
<td class="t-main" style="padding:12px 14px;{{ $font }}font-size:14px;font-weight:700;color:#1A1B30;">{{ $copy('total') }}</td>
<td align="right" class="t-main" style="padding:12px 14px;{{ $font }}font-size:17px;font-weight:700;color:#1A1B30;white-space:nowrap;">{{ $doc['total'] }}</td>
</tr></table>
</td></tr>
@endif

@if($doc['url'])
<tr><td class="px" align="center" style="padding:26px 32px 0;">
<table role="presentation" class="btn-t" cellpadding="0" cellspacing="0" border="0"><tr><td align="center" style="border-radius:10px;background:#E8604C;">
<a class="btn-a" href="{{ $doc['url'] }}" target="_blank" style="display:inline-block;min-width:220px;padding:14px 28px;{{ $font }}font-size:16px;font-weight:700;color:#FFFFFF;text-decoration:none;border-radius:10px;">{{ $copy($doc['isOffer'] ? 'view_offer' : 'view_booking') }} →</a>
</td></tr></table>
</td></tr>
@endif

<tr><td class="px t-main" style="padding:26px 32px 30px;{{ $font }}font-size:14px;line-height:1.55;color:#1A1B30;">
{{ $copy('signature') }}<br>
@if($doc['signature'] !== ''){{ $doc['signature'] }}<br>@endif
Catch A Guide
</td></tr>

</x-mail.cag-shell>
