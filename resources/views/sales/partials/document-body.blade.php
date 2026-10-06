{{--
    Customer page body rows inside <x-mail.cag-shell> (spec §8.2). $doc comes from
    SalesDocumentPresenter; $preview disables the accept form (builder preview).
    $acceptAction is the accept URL; $acceptError / $justAccepted come from the request.
--}}
@php
    $font = "font-family:'Inter',Helvetica,Arial,sans-serif;";
    $label = 'margin:0 0 8px;'.$font.'font-size:11px;line-height:1.2;letter-spacing:0.08em;text-transform:uppercase;font-weight:600;color:#3B5583;';
    $copy = fn (string $key, array $replace = []) => __('sales.customer.'.$key, $replace, $doc['locale']);
    $stripes = ['tour' => '#2F6FDE', 'camp' => '#2E8B57', 'trip' => '#E07A1F', 'custom' => '#7B4FC9'];
    $preview = $preview ?? false;
@endphp

<tr><td class="px t-main" style="padding:28px 32px 0;{{ $font }}font-size:15px;line-height:1.55;color:#1A1B30;">
<p style="margin:0 0 12px;font-weight:600;">{{ $doc['greeting'] }}</p>
<p style="margin:0;white-space:pre-line;">{{ $doc['intro'] }}</p>
</td></tr>

@forelse($doc['groups'] as $group)
<tr><td class="px" style="padding:18px 32px 0;">
<table role="presentation" class="bd" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #E6E8EC;border-left:4px solid {{ $stripes[$group['type']] ?? '#3B5583' }};border-radius:10px;border-collapse:separate;">
<tr><td style="padding:14px 16px 4px;">
<div style="{{ $font }}font-size:11px;letter-spacing:0.08em;text-transform:uppercase;font-weight:700;color:{{ $stripes[$group['type']] ?? '#3B5583' }};">{{ $group['kind'] }}</div>
<div class="t-main" style="{{ $font }}font-size:16px;line-height:1.35;font-weight:700;color:#1A1B30;margin-top:4px;">{{ $group['title'] }}</div>
@if($group['location'] !== '')
<div class="t-soft" style="{{ $font }}font-size:13px;color:#6B7489;margin-top:2px;">{{ $group['location'] }}</div>
@endif
</td></tr>
@foreach($group['lines'] as $line)
<tr><td style="padding:{{ $line['extra'] ? '6px 16px 6px 30px' : '10px 16px' }};">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
<td valign="top" class="t-main" style="{{ $font }}font-size:{{ $line['extra'] ? '13px' : '14px' }};line-height:1.4;color:#1A1B30;">
{{ $line['extra'] ? '+ ' : '' }}{{ $line['title'] }}
@if($line['detail'] !== '')
<div class="t-soft" style="font-size:12px;color:#6B7489;margin-top:2px;">{{ $line['detail'] }}</div>
@endif
</td>
<td valign="top" align="right" class="t-main" style="{{ $font }}font-size:14px;line-height:1.4;color:#1A1B30;white-space:nowrap;padding-left:12px;font-weight:{{ $line['extra'] ? '400' : '600' }};">{{ $line['amount'] }}</td>
</tr></table>
</td></tr>
@endforeach
@if($group['inclusions'] !== [])
<tr><td class="t-muted" style="padding:4px 16px 8px;{{ $font }}font-size:13px;line-height:1.5;color:#5A6478;"><strong>{{ $copy('included') }}:</strong> {{ implode(', ', $group['inclusions']) }}</td></tr>
@endif
@if($group['subtotal'])
<tr><td class="bd" style="padding:8px 16px;border-top:1px solid #E6E8EC;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
<td class="t-muted" style="{{ $font }}font-size:13px;color:#5A6478;">{{ $copy('subtotal') }}</td>
<td align="right" class="t-main" style="{{ $font }}font-size:13px;color:#1A1B30;font-weight:600;">{{ $group['subtotal'] }}</td>
</tr></table></td></tr>
@endif
@if($group['url'] && $group['moreLabel'])
<tr><td style="padding:2px 16px 14px;"><a href="{{ $group['url'] }}" target="_blank" rel="noopener" style="{{ $font }}font-size:13px;color:#2F6FDE;text-decoration:none;font-weight:600;">{{ $group['moreLabel'] }} ↗</a></td></tr>
@else
<tr><td style="padding:0 0 6px;font-size:0;line-height:0;">&nbsp;</td></tr>
@endif
</table>
</td></tr>
@empty
<tr><td class="px t-soft" style="padding:18px 32px 0;{{ $font }}font-size:14px;color:#6B7489;">{{ $copy('no_products') }}</td></tr>
@endforelse

{{-- Grand total --}}
<tr><td class="px" style="padding:18px 32px 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="bg-soft" style="background:#F4F6FA;border-radius:10px;"><tr>
<td class="t-main" style="padding:14px 16px;{{ $font }}font-size:15px;font-weight:700;color:#1A1B30;">{{ $copy('total') }}</td>
<td align="right" class="t-main" style="padding:14px 16px;{{ $font }}font-size:20px;font-weight:700;color:#1A1B30;white-space:nowrap;">{{ $doc['total'] }}</td>
</tr></table>
</td></tr>

@if($doc['notIncluded'] !== [])
<tr><td class="px t-main" style="padding:22px 32px 0;{{ $font }}font-size:14px;line-height:1.55;color:#1A1B30;">
<p class="t-label" style="{{ $label }}">{{ $copy('not_included') }}</p>
<ul style="margin:0;padding-left:20px;">
@foreach($doc['notIncluded'] as $line)
<li>{{ $line }}</li>
@endforeach
</ul>
</td></tr>
@endif

@if($doc['goodToKnow'] !== '')
<tr><td class="px t-main" style="padding:22px 32px 0;{{ $font }}font-size:14px;line-height:1.55;color:#1A1B30;">
<p class="t-label" style="{{ $label }}">{{ $copy('good_to_know') }}</p>
<p style="margin:0;white-space:pre-line;">{{ $doc['goodToKnow'] }}</p>
</td></tr>
@endif

@if($doc['paymentNote'] !== '')
<tr><td class="px t-main" style="padding:22px 32px 0;{{ $font }}font-size:14px;line-height:1.55;color:#1A1B30;">
<p class="t-label" style="{{ $label }}">{{ $copy('payment') }}</p>
<p style="margin:0;white-space:pre-line;">{{ $doc['paymentNote'] }}</p>
</td></tr>
@endif

@foreach($doc['partners'] as $partner)
<tr><td class="px" style="padding:22px 32px 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="bd" style="border:1px solid #E6E8EC;border-radius:10px;border-collapse:separate;"><tr><td style="padding:14px 16px;{{ $font }}font-size:14px;line-height:1.55;color:#1A1B30;" class="t-main">
<p class="t-label" style="{{ $label }}">{{ $copy('host') }}</p>
<div style="font-weight:700;font-size:15px;">{{ $partner['name'] }}</div>
<div>{{ implode(' · ', array_filter([$partner['email'], $partner['phone']])) }}</div>
<div class="t-soft" style="color:#6B7489;margin-top:6px;">{{ $copy('takeover', ['name' => $partner['firstName']]) }}</div>
</td></tr></table>
</td></tr>
@endforeach

@if($doc['isOffer'])
<tr><td class="px" style="padding:24px 32px 0;{{ $font }}">
@if($doc['state'] === 'accepted' || ($justAccepted ?? false))
<div style="background:#E8F5EE;border-radius:10px;padding:16px;font-size:14px;line-height:1.55;color:#1E5E3B;">{{ $copy('thanks') }}</div>
@elseif($doc['state'] === 'expired')
<div style="background:#FDF0E6;border-radius:10px;padding:16px;font-size:14px;line-height:1.55;color:#8A4A12;">{{ $copy('expired') }}</div>
@elseif($doc['state'] === 'cancelled')
<div style="background:#FBE9E9;border-radius:10px;padding:16px;font-size:14px;line-height:1.55;color:#8E2424;">{{ $copy('cancelled') }}</div>
@else
<details class="cag-accept" @if($acceptError ?? false) open @endif>
<summary style="list-style:none;cursor:pointer;display:block;text-align:center;">
<span style="display:inline-block;background:#E8604C;color:#FFFFFF;border-radius:10px;padding:14px 28px;font-size:16px;font-weight:700;">{{ $copy('accept') }}</span>
@if($doc['validUntil'])
<span class="t-soft" style="display:block;margin-top:8px;font-size:12px;color:#6B7489;">{{ $copy('valid_until', ['date' => $doc['validUntil']]) }}</span>
@endif
</summary>
<form method="POST" action="{{ $preview ? '#' : ($acceptAction ?? '#') }}" class="bd" data-accept-form style="margin:14px 0 0;border:1px solid #E6E8EC;border-radius:10px;padding:16px;font-size:14px;line-height:1.5;color:#1A1B30;" @if($preview) onsubmit="return false" @endif>
@unless($preview)@csrf @endunless
<div style="font-weight:700;margin-bottom:10px;">{{ $copy('total') }}: {{ $doc['total'] }}@if($doc['period']) · {{ $doc['period'] }}@endif</div>
<label data-accept-terms style="display:flex;gap:8px;align-items:flex-start;padding:8px;border-radius:8px;{{ ($acceptError ?? false) ? 'background:#FBE9E9;color:#8E2424;' : '' }}">
<input type="checkbox" name="terms" value="1" style="margin-top:3px;">
<span>{!! $copy('terms', [
    'terms' => '<a href="'.e($termsUrl ?? '#').'" target="_blank" rel="noopener" style="color:inherit;">'.e($copy('terms_link')).'</a>',
]) !!}</span>
</label>
<div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:12px;">
<button type="submit" style="background:#E8604C;color:#FFFFFF;border:0;border-radius:10px;padding:12px 22px;font-size:15px;font-weight:700;cursor:pointer;">{{ $copy('accept_binding') }}</button>
<button type="button" onclick="this.closest('details').open = false" style="background:transparent;color:#1A1B30;border:1px solid #D5D9E2;border-radius:10px;padding:12px 18px;font-size:15px;cursor:pointer;">{{ $copy('cancel_accept') }}</button>
</div>
</form>
</details>
@endif
</td></tr>
@endif

<tr><td class="px t-main" style="padding:26px 32px 30px;{{ $font }}font-size:14px;line-height:1.55;color:#1A1B30;">
{{ $copy('signature') }}<br>
@if($doc['signature'] !== '')<strong>{{ $doc['signature'] }}</strong> · @endif Catch A Guide
</td></tr>
