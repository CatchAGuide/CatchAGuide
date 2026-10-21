{{--
    Catch a Guide standard gift voucher, A4 portrait (794 × 1123 px at dompdf's 96 dpi), rendered by
    dompdf (VoucherPdfGenerator). Branded design "Gutschein / Voucher" (Claude Design, Oct 2026).
    dompdf supports CSS 2.1 only, so the design's flex/grid layout is rebuilt with tables and
    absolute positioning; px values are the design's. Fonts are the Barlow TTFs in resources/fonts,
    images are data URIs. The price is deliberately not printed.

    The personal message is capped at three lines (VoucherCheckoutViewModel::MESSAGE_MAX[_LINES]);
    older, longer messages get a smaller size so they still fit. dompdf renders line-heights 4/3
    too tall (35px comes out as ~47px), so the big lines set theirs through $lineHeight().

    Expects: $voucher (VoucherOrderPresenter::details), $logo and $qr (data URI|null), $fontDir.
--}}
@php
    $copy = 'checkout.voucher.pdf';
    $lineHeight = fn (float $px): string => round($px * .75).'px';

    $recipients = $voucher['recipients'];
    $recipientSize = match (true) {
        mb_strlen($recipients) <= 14 => 96,
        mb_strlen($recipients) <= 24 => 64,
        mb_strlen($recipients) <= 40 => 46,
        default => 34,
    };

    $lines = preg_split('/\n/', $voucher['personalMessage']) ?: [];
    $messageLines = count($lines) > 3 ? [implode(' ', $lines)] : $lines;
    $messageLength = mb_strlen($voucher['personalMessage']);
    $messageSize = match (true) {
        $messageLength <= 60 => 30,
        $messageLength <= 100 => 26,
        $messageLength <= 150 => 22,
        default => 18,
    };

    // The card is pinned to the page bottom and grows upwards, so the title stays within two lines
    // (one at 32px) or it runs into the sender line. Roughly 36 / 48 / 58 characters fit per line.
    $tourTitle = Str::limit($voucher['tourTitle'], 112);
    $tourSize = match (true) {
        mb_strlen($tourTitle) <= 34 => 32,
        mb_strlen($tourTitle) <= 90 => 24,
        default => 20,
    };
    $facts = [
        // City only, as in the design: the fact row stays one line high.
        ['location', trim(explode(',', $voucher['tourLocation'])[0])],
        ['duration', $voucher['tourDuration']],
        ['fishing_type', $voucher['tourFishingType']],
        ['persons', (string) $voucher['guestCount']],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="utf-8">
<title>{{ __($copy.'.title') }} {{ $voucher['code'] }}</title>
<style>
    @font-face { font-family: 'Barlow'; font-weight: 400; font-style: normal; src: url('{{ $fontDir }}/Barlow-Regular.ttf') format('truetype'); }
    @font-face { font-family: 'Barlow'; font-weight: 400; font-style: italic; src: url('{{ $fontDir }}/Barlow-Italic.ttf') format('truetype'); }
    @font-face { font-family: 'Barlow'; font-weight: 500; font-style: normal; src: url('{{ $fontDir }}/Barlow-Medium.ttf') format('truetype'); }
    @font-face { font-family: 'Barlow'; font-weight: 600; font-style: normal; src: url('{{ $fontDir }}/Barlow-SemiBold.ttf') format('truetype'); }
    @font-face { font-family: 'Barlow Condensed'; font-weight: 600; font-style: normal; src: url('{{ $fontDir }}/BarlowCondensed-SemiBold.ttf') format('truetype'); }
    @font-face { font-family: 'Barlow Condensed'; font-weight: 700; font-style: normal; src: url('{{ $fontDir }}/BarlowCondensed-Bold.ttf') format('truetype'); }

    @page { margin: 0; }
    * { box-sizing: border-box; }
    body { margin: 0; font-family: 'Barlow', 'DejaVu Sans', sans-serif; color: #2d2b42; background: #fff; }
    p { margin: 0; }
    table { border-collapse: collapse; }
    td { padding: 0; vertical-align: top; }
    .heading { font-family: 'Barlow Condensed', 'DejaVu Sans', sans-serif; }
    .label { font-size: 10px; line-height: 1; font-weight: 500; letter-spacing: .16em; text-transform: uppercase; color: #6b6980; }

    .head { background: #2d2b42; padding: 40px 56px 36px; }
    .head-title { height: 141px; font-size: 176px; line-height: 141px; font-weight: 700; letter-spacing: -.03em; text-transform: uppercase; color: #fff; }
    .head-sub { font-size: 26px; line-height: 1.1; font-weight: 600; letter-spacing: .02em; margin-top: 16px; color: #e5604d; }

    /* Between the 262px header and the ~466px bottom block (card, cut-off part, footer). */
    .hero { position: absolute; top: 286px; left: 56px; width: 682px; }
    .hero td { vertical-align: middle; text-align: center; }
    .for { font-size: 14px; line-height: 10px; font-weight: 600; letter-spacing: .28em; text-transform: uppercase; color: #e5604d; }
    .name { font-weight: 700; letter-spacing: -.01em; margin-top: 14px; }
    .quote-rule { width: 40px; height: 2px; background: #e5604d; }
    .quote-mark { font-size: 30px; line-height: 22px; font-weight: 700; color: #e5604d; padding: 0 14px; }
    .message { font-style: italic; margin: 10px 61px 0; }
    .from { font-size: 14px; line-height: 10px; font-weight: 600; letter-spacing: .24em; text-transform: uppercase; color: #6b6980; margin-top: 16px; }

    .bottom { position: absolute; left: 0; right: 0; bottom: 0; }
    .card-wrap { padding: 0 56px 36px; }
    .card { position: relative; }
    .card table { width: 100%; border: 1px solid #e5604d; }
    .card .tour { padding: 20px 22px; border-bottom: 1px solid #f0c3bb; }
    .card .tour-label { color: #e5604d; }
    .card .tour-title { font-weight: 600; line-height: 1.1; margin-top: 8px; }
    .card .fact { width: 25%; padding: 14px 22px; white-space: nowrap; }
    .card .fact + .fact { border-left: 1px solid #f0c3bb; }
    .card .fact-value { font-size: 21px; line-height: 1.3; font-weight: 600; margin-top: 6px; white-space: nowrap; }
    .card .qr { width: 150px; padding: 14px 16px; border-left: 1px solid #f0c3bb; text-align: center; vertical-align: middle; }
    .card .qr img, .card .qr-empty { width: 104px; height: 104px; }
    .card .qr-empty { border: 1px solid #d9d8e0; margin: 0 auto; }
    .card .qr-label { font-size: 9px; line-height: 1.3; font-weight: 500; letter-spacing: .12em; margin-top: 8px; }
    .corner { position: absolute; width: 11px; height: 11px; }
    .corner .v { position: absolute; left: 5px; top: 0; width: 1px; height: 11px; background: #e5604d; }
    .corner .h { position: absolute; top: 5px; left: 0; width: 11px; height: 1px; background: #e5604d; }
    .tl { top: -6px; left: -6px; } .tr { top: -6px; right: -6px; } .bl { bottom: -6px; left: -6px; } .br { bottom: -6px; right: -6px; }

    .cut { position: relative; border-top: 2px dashed #2d2b42; padding: 30px 56px 28px; }
    .scissors { position: absolute; top: -9px; left: 20px; font-family: 'DejaVu Sans', sans-serif; font-size: 12px; line-height: 1; color: #8a889b; background: #fff; padding: 0 6px; }
    .issuer-label { color: #e5604d; }
    .issuer-name { font-size: 24px; line-height: 1.1; font-weight: 600; margin-top: 7px; }
    .issuer-contact { font-size: 13px; line-height: 1.45; color: #6b6980; margin-top: 4px; }
    .redeem { font-size: 13px; line-height: 1.45; font-weight: 600; margin-top: 12px; }
    .code-box { width: 100%; border: 1px solid #d9d8e0; }
    .code-box .code { padding: 12px 16px; background: #fdf0ee; border-bottom: 1px solid #d9d8e0; }
    .code-box .code-value { font-family: 'DejaVu Sans Mono', monospace; font-size: 19px; line-height: 1.2; font-weight: bold; color: #e5604d; margin-top: 7px; }
    .code-box .date { width: 50%; padding: 10px 16px; }
    .code-box .date + .date { border-left: 1px solid #d9d8e0; }
    .code-box .label { font-size: 9px; }
    .code-box .date-value { font-size: 17px; line-height: 1.2; font-weight: 600; margin-top: 5px; }

    .foot { background: #2d2b42; color: #c9c7d6; padding: 14px 56px; font-size: 10px; line-height: 1.5; }
    .foot td { vertical-align: middle; }
    .foot img { height: 34px; }
</style>
</head>
<body>
    <div class="head heading">
        <div class="head-title">{{ __($copy.'.title') }}</div>
        <div class="head-sub">{{ __($copy.'.subtitle') }}</div>
    </div>

    <div class="hero">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td style="height: 340px;">
                <p class="for heading">{{ __($copy.'.for') }}</p>
                <p class="name heading" style="font-size: {{ $recipientSize }}px; line-height: {{ $lineHeight($recipientSize * .95) }};">{{ $recipients }}</p>

                @if($voucher['personalMessage'] !== '')
                    <table cellpadding="0" cellspacing="0" style="margin: 20px auto 0;">
                        <tr>
                            <td style="vertical-align: middle;"><div class="quote-rule"></div></td>
                            <td class="quote-mark heading">{{ app()->getLocale() === 'de' ? '„' : '“' }}</td>
                            <td style="vertical-align: middle;"><div class="quote-rule"></div></td>
                        </tr>
                    </table>
                    <p class="message" style="font-size: {{ $messageSize }}px; line-height: {{ $lineHeight($messageSize * 1.35) }};">{!! implode('<br>', array_map('e', $messageLines)) !!}</p>
                @endif
                @if($voucher['sender'] !== '')
                    <p class="from heading">{{ __($copy.'.from', ['name' => $voucher['sender']]) }}</p>
                @endif
            </td>
        </tr>
    </table>
    </div>

    <div class="bottom">
        <div class="card-wrap">
            <div class="card">
                <table cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="tour" colspan="4">
                            <p class="label tour-label">{{ __($copy.'.tour') }}</p>
                            <p class="tour-title heading" style="font-size: {{ $tourSize }}px; line-height: {{ $lineHeight($tourSize * 1.1) }};">{{ $tourTitle }}</p>
                        </td>
                        <td class="qr" rowspan="2">
                            @if($qr)
                                <img src="{{ $qr }}" alt="QR">
                            @else
                                <div class="qr-empty"></div>
                            @endif
                            <p class="label qr-label">{{ __($copy.'.qr') }}</p>
                        </td>
                    </tr>
                    <tr>
                        @foreach($facts as [$key, $value])
                            <td class="fact">
                                <p class="label">{{ __($copy.'.'.$key) }}</p>
                                <p class="fact-value heading">{{ filled($value) ? $value : '–' }}</p>
                            </td>
                        @endforeach
                    </tr>
                </table>
                @foreach(['tl', 'tr', 'bl', 'br'] as $corner)
                    <div class="corner {{ $corner }}"><div class="v"></div><div class="h"></div></div>
                @endforeach
            </div>
        </div>

        <div class="cut">
            <span class="scissors">&#9986;</span>
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="width: 381px; padding-right: 28px;">
                        <p class="label issuer-label">{{ __($copy.'.issuer') }}</p>
                        <p class="issuer-name heading">{{ $voucher['guideName'] }}</p>
                        <p class="issuer-contact">{{ implode(' · ', array_filter([$voucher['guidePhone'], $voucher['guideEmail']])) }}</p>
                        <p class="redeem">{{ __($copy.'.redeem_note') }}</p>
                    </td>
                    <td>
                        <table class="code-box" cellpadding="0" cellspacing="0">
                            <tr>
                                <td class="code" colspan="2">
                                    <p class="label">{{ __($copy.'.code') }}</p>
                                    <p class="code-value">{{ $voucher['code'] }}</p>
                                </td>
                            </tr>
                            <tr>
                                <td class="date">
                                    <p class="label">{{ __($copy.'.issued') }}</p>
                                    <p class="date-value heading">{{ $voucher['purchasedOn'] }}</p>
                                </td>
                                <td class="date">
                                    <p class="label">{{ __($copy.'.valid_until') }}</p>
                                    <p class="date-value heading">{{ $voucher['validUntil'] !== '' ? $voucher['validUntil'] : '–' }}</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <table class="foot" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td>{{ __($copy.'.footer') }}</td>
                @if($logo)
                    <td style="width: 106px; padding-left: 20px; text-align: right;"><img src="{{ $logo }}" alt="Catch A Guide"></td>
                @endif
            </tr>
        </table>
    </div>
</body>
</html>
