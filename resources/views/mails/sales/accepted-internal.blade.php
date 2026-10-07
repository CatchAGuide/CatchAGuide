{{-- Internal notice to the creator and the sales inbox: the customer accepted an offer (spec §8.4). --}}
@php
    $font = "font-family:'Inter',Helvetica,Arial,sans-serif;";
    $kvLabel = $font.'font-size:14px;line-height:1.45;color:#5A6478;padding:6px 12px 6px 0;';
    $kvValue = $font.'font-size:14px;line-height:1.45;color:#1A1B30;font-weight:600;padding:6px 0;';
@endphp
<x-mail.cag-shell :title="__('sales.internal.title')">
<tr><td class="px t-main" style="padding:28px 32px 0;{{ $font }}font-size:15px;line-height:1.55;color:#1A1B30;">
<p style="margin:0 0 14px;">{{ __('sales.internal.intro', ['customer' => $customer]) }}</p>
<table role="presentation" cellpadding="0" cellspacing="0" border="0">
<tr><td class="t-muted" style="{{ $kvLabel }}">{{ __('sales.internal.number') }}</td><td class="t-main" style="{{ $kvValue }}">{{ $number }}</td></tr>
<tr><td class="t-muted" style="{{ $kvLabel }}">{{ __('sales.internal.customer') }}</td><td class="t-main" style="{{ $kvValue }}">{{ $customer }} &lt;{{ $email }}&gt;</td></tr>
<tr><td class="t-muted" style="{{ $kvLabel }}">{{ __('sales.internal.total') }}</td><td class="t-main" style="{{ $kvValue }}">{{ $total }}</td></tr>
@if($period)
<tr><td class="t-muted" style="{{ $kvLabel }}">{{ __('sales.internal.period') }}</td><td class="t-main" style="{{ $kvValue }}">{{ $period }}</td></tr>
@endif
</table>
</td></tr>
<tr><td class="px" align="center" style="padding:24px 32px 30px;">
<a href="{{ $builderUrl }}" target="_blank" style="display:inline-block;padding:13px 26px;{{ $font }}font-size:15px;font-weight:700;color:#FFFFFF;background:#1A1B30;text-decoration:none;border-radius:10px;">{{ __('sales.internal.open') }} →</a>
</td></tr>
</x-mail.cag-shell>
