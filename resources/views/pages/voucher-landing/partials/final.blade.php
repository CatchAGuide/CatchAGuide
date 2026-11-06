@php
    $copy = 'guidings.voucher_landing.';
    $days = $landing->daysToChristmasEve();
@endphp
<section class="vl-final" style="background-image: url('{{ $landing->finalImage() }}')">
    <div class="vl-final__shade" aria-hidden="true"></div>
    <div class="vl-final__inner">
        @if($xmas && $days > 0)
            <span class="vl-pill vl-pill--lg">{{ trans_choice($copy.'countdown', $days, ['count' => $days]) }}</span>
        @endif
        <h2 class="vl-final__title">{{ __($copy.'final_title') }}</h2>
        <a href="#gutschein" class="vl-btn vl-btn--glow vl-btn--lg">
            {{ __($copy.'cta') }}
            @include('pages.voucher-landing.partials.icon', ['name' => 'arrow'])
        </a>
    </div>
</section>
