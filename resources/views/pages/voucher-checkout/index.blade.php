@extends('layouts.app-v2-1')

@section('title', __('checkout.voucher.title'))

@section('meta_robots')
    <meta name="robots" content="noindex,nofollow">
@endsection

@section('custom_style')
    @include('pages.modern-checkout.partials.fonts')
@endsection

@section('content')
    {{--
        Same one-DOM layout as the tour checkout (pages/modern-checkout/partials/layout), with the
        voucher card in the calendar's place. Expects: $checkout (VoucherCheckoutViewModel).
    --}}
    @php($partials = 'pages.modern-checkout.partials')
    <div class="tc tc--voucher" x-data="voucherCheckout">
        <div class="tc__container">
            <div class="tc__grid">
                @include($partials.'.head', [
                    'title' => __('checkout.voucher.title'),
                    'steps' => ['send' => 'checkout.voucher.step_send', 'coin' => 'checkout.voucher.step_pay', 'gift' => 'checkout.voucher.step_voucher'],
                    'intro' => 'pages.voucher-checkout.partials.intro',
                    'introData' => ['guideName' => $checkout->guideName()],
                ])

                @include('pages.voucher-checkout.partials.voucher')

                @include($partials.'.contact', ['checkout' => $checkout])

                <div class="tc__aside">
                    @include($partials.'.product', ['product' => $checkout->product()])

                    <div class="tc__summary">
                        <p class="tc__summary-meta">{{ __('checkout.voucher.summary_meta') }} · <span x-text="participantsLabel"></span></p>

                        @include($partials.'.guests', ['checkout' => $checkout])

                        @include($partials.'.pricing', ['checkout' => $checkout, 'priceNote' => __('checkout.voucher.price_note')])

                        <p class="tc-voucher-note" role="note">
                            @include($partials.'.icon', ['name' => 'info', 'size' => 16, 'stroke' => 1.8])
                            <span>{{ __('checkout.voucher.no_payment_note') }}</span>
                        </p>

                        @include($partials.'.cta', [
                            'submitLabel' => __('checkout.voucher.submit'),
                            'trustItems' => ['checkout.voucher.trust_no_payment', 'checkout.voucher.trust_direct'],
                        ])
                    </div>
                </div>
            </div>
        </div>

        @include($partials.'.mobile-bar', ['submitShortLabel' => __('checkout.voucher.submit_short')])
    </div>

    <script type="application/json" id="voucher-checkout-config">@json($checkout->clientConfig())</script>
@endsection

@push('js_push')
    <script src="{{ mix('js/voucher-checkout.js') }}" defer></script>
@endpush
