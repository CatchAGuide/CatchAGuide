{{--
    The voucher ticket. Used in the hero (teal) and next to the form (dark, with stamp, pulses on
    every change). Text is bound to the voucherLanding component; the server-rendered values are
    the defaults (or the submitted request on the thank-you URL).

    @param string $variant  'hero' | 'form'
    @param bool   $xmas
    @param array  $values   ['amount', 'where', 'city', 'for']
    @param string $serial
--}}
@php($copy = 'guidings.voucher_landing.')
<div @class(['vl-card', 'vl-card--'.$variant]) @if($variant === 'form') :class="pulseClass" @endif>
    <div class="vl-card__bow" aria-hidden="true"><span></span><span></span><span></span></div>
    <div class="vl-card__ticket">
        <div class="vl-card__ribbon" aria-hidden="true"></div>
        <div class="vl-card__perforation" aria-hidden="true"></div>
        <div class="vl-card__side vl-mono" aria-hidden="true">{{ __($copy.'card_side') }}</div>
        @if($variant === 'form')
            <div class="vl-card__stamp" aria-hidden="true">
                @include('pages.voucher-landing.partials.icon', ['name' => 'fish'])
                <span class="vl-mono">{{ __($copy.'card_stamp') }}</span>
            </div>
        @endif
        <div class="vl-card__body">
            <div class="vl-card__brand">
                <span class="vl-card__kicker vl-mono">{{ __($copy.'card_kicker') }}</span>
                <span class="vl-card__name">CATCH A GUIDE</span>
            </div>
            <span class="vl-card__amount vl-mono" x-text="amountLabel">{{ $values['amount'] }}</span>
            <div class="vl-card__meta">
                <span class="vl-card__where" x-text="whereText">{{ $values['where'] }}</span>
                @if($variant === 'form')
                    <span class="vl-card__city" x-show="cityText" x-text="cityText" @if($values['city'] === '') x-cloak @endif>{{ $values['city'] }}</span>
                @endif
                <span class="vl-card__for" x-show="forText" x-text="forText" @if($values['for'] === '') x-cloak @endif>{{ $values['for'] }}</span>
                <span class="vl-card__serial vl-mono">{{ __($copy.'card_number', ['code' => $serial]) }}</span>
            </div>
        </div>
        @if($xmas)
            <span class="vl-card__spark vl-card__spark--a" aria-hidden="true">✦</span>
            <span class="vl-card__spark vl-card__spark--b" aria-hidden="true">✦</span>
        @endif
        <div class="vl-card__shine" aria-hidden="true"></div>
    </div>
</div>
