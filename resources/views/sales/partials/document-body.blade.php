{{--
    Customer page body inside <x-mail.cag-shell> (spec §8.2). $doc comes from
    SalesDocumentPresenter; $preview disables the accept form (builder preview).
    $acceptAction is the accept URL; $acceptError / $justAccepted come from the request.

    Responsive: one column on phones (and in the narrow builder preview); from 900px wide a
    content column plus a summary column (total, accept, contact) that stays in view.
    Styles live in sales/offer.blade.php (.cp-*).
--}}
@php
    $font = "font-family:'Inter',Helvetica,Arial,sans-serif;";
    $label = 'margin:0 0 8px;'.$font.'font-size:11px;line-height:1.2;letter-spacing:0.08em;text-transform:uppercase;font-weight:600;color:#3B5583;';
    $copy = fn (string $key, array $replace = []) => __('sales.customer.'.$key, $replace, $doc['locale']);
    $preview = $preview ?? false;
@endphp
<tr><td class="cp-wrap" style="padding:28px 32px 30px;{{ $font }}color:#1A1B30;">
<div class="cp-grid">

{{-- Greeting + intro --}}
<div class="cp-intro t-main" style="font-size:15px;line-height:1.55;">
<p style="margin:0 0 12px;font-weight:600;">{{ $doc['greeting'] }}</p>
<p style="margin:0;white-space:pre-line;">{{ $doc['intro'] }}</p>
</div>

{{-- Product cards --}}
<div class="cp-products">
@forelse($doc['groups'] as $group)
<table role="presentation" class="bd" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:18px;border:1px solid #E6E8EC;border-left:4px solid {{ $group['color'] }};border-radius:10px;border-collapse:separate;">
<tr><td style="padding:14px 16px 4px;">
<div style="font-size:11px;letter-spacing:0.08em;text-transform:uppercase;font-weight:700;color:{{ $group['color'] }};">{{ $group['kind'] }}</div>
<div class="t-main" style="font-size:16px;line-height:1.35;font-weight:700;color:#1A1B30;margin-top:4px;">{{ $group['title'] }}</div>
@if($group['location'] !== '')
<div class="t-soft" style="font-size:13px;color:#6B7489;margin-top:2px;">{{ $group['location'] }}</div>
@endif
</td></tr>
@foreach($group['lines'] as $line)
<tr><td style="padding:{{ $line['extra'] ? '6px 16px 6px 30px' : '10px 16px' }};">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
<td valign="top" class="t-main" style="font-size:{{ $line['extra'] ? '13px' : '14px' }};line-height:1.4;color:#1A1B30;">
{{ $line['extra'] ? '+ ' : '' }}{{ $line['title'] }}
@if($line['detail'] !== '')
<div class="t-soft" style="font-size:12px;color:#6B7489;margin-top:2px;">{{ $line['detail'] }}</div>
@endif
</td>
<td valign="top" align="right" class="t-main" style="font-size:14px;line-height:1.4;color:#1A1B30;white-space:nowrap;padding-left:12px;font-weight:{{ $line['extra'] ? '400' : '600' }};">{{ $line['amount'] }}</td>
</tr></table>
</td></tr>
@endforeach
@if($group['subtotal'])
<tr><td class="bd" style="padding:8px 16px;border-top:1px solid #E6E8EC;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
<td class="t-muted" style="font-size:13px;color:#5A6478;">{{ $copy('subtotal') }}</td>
<td align="right" class="t-main" style="font-size:13px;color:#1A1B30;font-weight:600;white-space:nowrap;">{{ $group['subtotal'] }}</td>
</tr></table></td></tr>
@endif
@if($group['inclusions'] !== [])
<tr><td class="t-muted" style="padding:4px 16px 8px;font-size:13px;line-height:1.5;color:#5A6478;"><strong>{{ $copy('included') }}:</strong> {{ implode(', ', $group['inclusions']) }}</td></tr>
@endif
@if($group['url'] && $group['moreLabel'])
<tr><td style="padding:2px 16px 14px;"><a href="{{ $group['url'] }}" target="_blank" rel="noopener" style="font-size:13px;color:{{ $group['color'] }};text-decoration:underline;font-weight:600;">{{ $group['moreLabel'] }} ↗</a></td></tr>
@else
<tr><td style="padding:0 0 6px;font-size:0;line-height:0;">&nbsp;</td></tr>
@endif
</table>
@empty
<p class="t-soft" style="margin:18px 0 0;font-size:14px;color:#6B7489;">{{ $copy('no_products') }}</p>
@endforelse
</div>

{{-- Not included, good to know, payment, host block --}}
<div class="cp-details t-main" style="font-size:14px;line-height:1.55;color:#1A1B30;">
@if($doc['notIncluded'] !== [])
<div style="margin-top:22px;">
<p class="t-label" style="{{ $label }}">{{ $copy('not_included') }}</p>
<ul style="margin:0;padding-left:20px;">
@foreach($doc['notIncluded'] as $line)
<li>{{ $line }}</li>
@endforeach
</ul>
</div>
@endif

@if($doc['goodToKnow'] !== '')
<div style="margin-top:22px;">
<p class="t-label" style="{{ $label }}">{{ $copy('good_to_know') }}</p>
<p style="margin:0;white-space:pre-line;">{{ $doc['goodToKnow'] }}</p>
</div>
@endif

@if($doc['paymentNote'] !== '')
<div style="margin-top:22px;">
<p class="t-label" style="{{ $label }}">{{ $copy('payment') }}</p>
<p style="margin:0;white-space:pre-line;">{{ $doc['paymentNote'] }}</p>
</div>
@endif

@foreach($doc['partners'] as $partner)
<div class="bd" style="margin-top:22px;border:1px solid #E6E8EC;border-radius:10px;padding:14px 16px;">
<p class="t-label" style="{{ $label }}">{{ $copy('host') }}</p>
<div style="font-weight:700;font-size:15px;">{{ $partner['name'] }}</div>
<div>{{ implode(' · ', array_filter([$partner['email'], $partner['phone']])) }}</div>
<div class="t-soft" style="color:#6B7489;margin-top:6px;">{{ $copy('takeover', ['name' => $partner['firstName']]) }}</div>
</div>
@endforeach
</div>

{{-- Summary: total, accept, contact (sticky column on wide screens) --}}
<div class="cp-summary">
<div class="cp-summary__inner">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="bg-soft" style="margin-top:18px;background:#F4F6FA;border-radius:10px;"><tr>
<td class="t-main" style="padding:14px 16px;font-size:15px;font-weight:700;color:#1A1B30;">{{ $copy('total') }}@if($doc['period'])<div class="t-soft" style="font-size:12px;font-weight:400;color:#6B7489;margin-top:2px;">{{ $doc['period'] }}</div>@endif</td>
<td align="right" class="t-main" style="padding:14px 16px;font-size:20px;font-weight:700;color:#1A1B30;white-space:nowrap;">{{ $doc['total'] }}</td>
</tr></table>

@if($doc['isOffer'])
<div style="margin-top:18px;">
@if($doc['state'] === 'accepted' || ($justAccepted ?? false))
<div style="background:#E8F5EE;border-radius:10px;padding:16px;font-size:14px;line-height:1.55;color:#1E5E3B;">{{ $doc['thanks'] }}</div>
@elseif($doc['state'] === 'expired')
<div style="background:#FDF0E6;border-radius:10px;padding:16px;font-size:14px;line-height:1.55;color:#8A4A12;">{{ $copy('expired') }}</div>
@elseif($doc['state'] === 'cancelled')
<div style="background:#FBE9E9;border-radius:10px;padding:16px;font-size:14px;line-height:1.55;color:#8E2424;">{{ $copy('cancelled') }}</div>
@else
<details class="cag-accept" @if($acceptError ?? false) open @endif>
<summary style="list-style:none;cursor:pointer;display:block;text-align:center;">
<span class="cp-accept-btn" style="display:inline-block;background:#E8604C;color:#FFFFFF;border-radius:10px;padding:14px 28px;font-size:16px;font-weight:700;">{{ $copy('accept') }}</span>
@if($doc['validUntil'])
<span class="t-soft" style="display:block;margin-top:8px;font-size:12px;color:#6B7489;">{{ $copy('valid_until', ['date' => $doc['validUntil']]) }}</span>
@endif
</summary>
<form method="POST" action="{{ $preview ? '#' : ($acceptAction ?? '#') }}" class="bd" data-accept-form style="margin:0;border:1px solid #E6E8EC;border-radius:10px;padding:16px;font-size:14px;line-height:1.5;color:#1A1B30;" @if($preview) onsubmit="return false" @endif>
@unless($preview)@csrf @endunless
<div style="font-weight:700;margin-bottom:10px;">{{ $copy('total') }}: {{ $doc['total'] }}@if($doc['period']) · {{ $doc['period'] }}@endif</div>
<label data-accept-terms style="display:flex;gap:8px;align-items:flex-start;padding:8px;border-radius:8px;{{ ($acceptError ?? false) ? 'background:#FBE9E9;color:#8E2424;' : '' }}">
<input type="checkbox" name="terms" value="1" style="margin-top:3px;">
<span>{!! $copy('terms', [
    'terms' => '<a href="'.e($termsUrl ?? '#').'" target="_blank" rel="noopener" style="color:inherit;">'.e($copy('terms_link')).'</a>',
    'policy' => $doc['policies'] !== []
        ? '<a href="#cag-policies" style="color:inherit;">'.e($copy('policy_link')).'</a>'
        : e($copy('policy_link')),
]) !!}</span>
</label>
<div id="cag-policies" style="margin-top:10px;font-size:13px;line-height:1.5;color:#5A6478;">
@forelse($doc['policies'] as $policy)
<details style="margin-top:6px;"><summary style="cursor:pointer;color:#1A1B30;">{{ $copy('policy_of', ['title' => $policy['title']]) }}</summary><p style="margin:6px 0 0;white-space:pre-line;">{{ $policy['text'] }}</p></details>
@empty
<p style="margin:0;">{{ $copy('policy_none') }}</p>
@endforelse
</div>
<div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:12px;">
<button type="submit" style="background:#E8604C;color:#FFFFFF;border:0;border-radius:10px;padding:12px 22px;font-size:15px;font-weight:700;cursor:pointer;">{{ $copy('accept_binding') }}</button>
<button type="button" onclick="this.closest('details').open = false" style="background:transparent;color:#1A1B30;border:1px solid #D5D9E2;border-radius:10px;padding:12px 18px;font-size:15px;cursor:pointer;">{{ $copy('cancel_accept') }}</button>
</div>
</form>
</details>
@endif
</div>
@endif

{{-- Help or questions? --}}
<div style="margin-top:18px;">
@include('sales.partials.contact', ['locale' => $doc['locale']])
</div>
</div>
</div>

{{-- Signature --}}
<div class="cp-sign t-main" style="margin-top:26px;font-size:14px;line-height:1.55;color:#1A1B30;">
{{ $doc['signatureText'] }}<br>
@if($doc['signature'] !== '')<strong>{{ $doc['signature'] }}</strong> · @endif Catch A Guide
</div>

</div>
</td></tr>
