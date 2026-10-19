{{--
    Catch a Guide standard gift voucher, A4 portrait, rendered by dompdf (VoucherPdfGenerator).
    dompdf supports CSS 2.1 only: layout is tables, fonts are DejaVu (covers umlauts), images are
    data URIs. Placeholder design until the final branded template is delivered; the dynamic
    fields follow spec section 7. The price is deliberately not printed.

    Expects: $voucher (VoucherOrderPresenter::details), $logo and $guidePhoto (data URI|null).
--}}
@php($copy = 'checkout.voucher.pdf')
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="utf-8">
<title>{{ __($copy.'.title') }} {{ $voucher['code'] }}</title>
<style>
    @page { margin: 0; }
    * { box-sizing: border-box; }
    body { margin: 0; font-family: 'DejaVu Sans', sans-serif; color: #1A1B30; font-size: 11pt; }
    .sheet { padding: 0; }
    .band { background: #313041; padding: 26pt 40pt; }
    .band td { color: #FFFFFF; vertical-align: middle; }
    .band-logo { height: 34pt; }
    .band-label { text-align: right; font-size: 9pt; letter-spacing: 2pt; text-transform: uppercase; color: #F3C7BF; }
    .hero { padding: 40pt 40pt 8pt; text-align: center; }
    .eyebrow { font-size: 10pt; letter-spacing: 3pt; text-transform: uppercase; color: #E8604C; margin: 0 0 8pt; }
    .title { font-size: 34pt; font-weight: bold; margin: 0; color: #313041; }
    .for { font-size: 13pt; color: #5A6478; margin: 18pt 0 4pt; }
    .recipients { font-size: 24pt; font-weight: bold; margin: 0; color: #1A1B30; }
    .tour { margin: 26pt 40pt 0; border: 1.5pt solid #E8604C; border-radius: 10pt; padding: 18pt 22pt; text-align: center; }
    .tour-title { font-size: 16pt; font-weight: bold; margin: 0 0 6pt; }
    .tour-meta { font-size: 10.5pt; color: #5A6478; margin: 0; }
    .message { margin: 22pt 60pt 0; text-align: center; font-style: italic; font-size: 12pt; line-height: 1.5; color: #1A1B30; }
    .from { margin: 10pt 40pt 0; text-align: center; font-size: 11pt; color: #5A6478; }
    .wrap { padding: 0 40pt; }
    .guide { margin-top: 30pt; background: #F6F7F9; border-radius: 10pt; }
    .guide td { padding: 16pt 0 16pt 20pt; }
    .guide td { vertical-align: middle; }
    .guide-photo { width: 58pt; height: 58pt; border-radius: 29pt; }
    .guide-label { font-size: 8.5pt; letter-spacing: 1.5pt; text-transform: uppercase; color: #3B5583; margin: 0 0 4pt; }
    .guide-name { font-size: 13pt; font-weight: bold; margin: 0 0 3pt; }
    .guide-contact { font-size: 10pt; color: #5A6478; margin: 0; }
    .facts { margin-top: 22pt; }
    .facts td { width: 33.33%; padding: 0 6pt; text-align: center; }
    .fact-label { font-size: 8.5pt; letter-spacing: 1.5pt; text-transform: uppercase; color: #5A6478; margin: 0 0 4pt; }
    .fact-value { font-size: 12.5pt; font-weight: bold; margin: 0; }
    .code { font-family: 'DejaVu Sans Mono', monospace; color: #E8604C; }
    .note { margin: 24pt 40pt 0; text-align: center; font-size: 11pt; font-weight: bold; color: #313041; }
    .footer { position: absolute; bottom: 0; left: 0; right: 0; padding: 12pt 40pt; background: #313041; color: #C9CBD6; font-size: 8pt; text-align: center; }
</style>
</head>
<body>
<div class="sheet">
    <table class="band" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td>@if($logo)<img class="band-logo" src="{{ $logo }}" alt="Catch a Guide">@else<strong>Catch a Guide</strong>@endif</td>
            <td class="band-label">{{ __($copy.'.title') }}</td>
        </tr>
    </table>

    <div class="hero">
        <p class="eyebrow">{{ __($copy.'.eyebrow') }}</p>
        <h1 class="title">{{ __($copy.'.title') }}</h1>
        <p class="for">{{ __($copy.'.for') }}</p>
        <p class="recipients">{{ $voucher['recipients'] }}</p>
    </div>

    <div class="tour">
        <p class="tour-title">{{ $voucher['tourTitle'] }}</p>
        <p class="tour-meta">
            {{ implode(' · ', array_filter([$voucher['tourLocation'], $voucher['tourDuration'], $voucher['guests']])) }}
        </p>
    </div>

    @if($voucher['personalMessage'] !== '')
        <p class="message">{{ __('emails.voucher_order_guest.quote', ['message' => $voucher['personalMessage']]) }}</p>
    @endif
    @if($voucher['sender'] !== '')
        <p class="from">{{ __($copy.'.from', ['name' => $voucher['sender']]) }}</p>
    @endif

    <div class="wrap">
    <table class="guide" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            @if($guidePhoto)
                <td style="width: 58pt;"><img class="guide-photo" src="{{ $guidePhoto }}" alt=""></td>
            @endif
            <td style="padding-right: 20pt;">
                <p class="guide-label">{{ __($copy.'.your_guide') }}</p>
                <p class="guide-name">{{ $voucher['guideName'] }}</p>
                <p class="guide-contact">{{ implode('  ·  ', array_filter([$voucher['guidePhone'], $voucher['guideEmail']])) }}</p>
            </td>
        </tr>
    </table>

    <table class="facts" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <p class="fact-label">{{ __($copy.'.code') }}</p>
                <p class="fact-value code">{{ $voucher['code'] }}</p>
            </td>
            <td>
                <p class="fact-label">{{ __($copy.'.issued') }}</p>
                <p class="fact-value">{{ $voucher['purchasedOn'] }}</p>
            </td>
            <td>
                <p class="fact-label">{{ __($copy.'.valid_until') }}</p>
                <p class="fact-value">{{ $voucher['validUntil'] }}</p>
            </td>
        </tr>
    </table>
    </div>

    <p class="note">{{ __($copy.'.redeem_note') }}</p>

    <div class="footer">{{ __($copy.'.footer') }}</div>
</div>
</body>
</html>
