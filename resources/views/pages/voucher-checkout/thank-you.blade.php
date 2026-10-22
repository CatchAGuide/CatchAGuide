@extends('layouts.app-v2-1')

@section('title', __('checkout.voucher.success_title'))

@section('meta_robots')
    <meta name="robots" content="noindex,nofollow">
@endsection

@section('custom_style')
    @include('pages.modern-checkout.partials.fonts')
@endsection

@section('content')
    {{-- Same confirmation design as the trip checkout (cc-done). Expects: $order, $voucher (VoucherOrderPresenter::details). --}}
    <div class="cc cc--trip cc--done">
        <header class="cc-head">
            <div class="cc-head__inner">
                @if ($voucher['tourUrl'])
                    <a href="{{ $voucher['tourUrl'] }}" class="cc-head__back" aria-label="{{ __('checkout.voucher.success_back') }}">
                        @include('pages.modern-checkout.partials.icon', ['name' => 'arrow-left', 'size' => 22, 'stroke' => 2])
                    </a>
                @endif
                <div class="cc-head__text">
                    <p class="cc-head__title">{{ $voucher['tourTitle'] }}</p>
                </div>
            </div>
        </header>

        <div class="cc-done" data-clarity-mask="True">
            <div class="cc-done__icon">
                @include('pages.modern-checkout.partials.icon', ['name' => 'check', 'size' => 34, 'stroke' => 2])
            </div>
            <h1 class="cc-done__title">{{ __('checkout.voucher.success_title') }}</h1>
            <p class="cc-done__text">
                {!! __('checkout.voucher.success_text', [
                    'guide' => '<strong>'.e($voucher['guideFirstName'] ?: $voucher['guideName']).'</strong>',
                    'email' => '<strong>'.e($order->buyer_email).'</strong>',
                ]) !!}
            </p>
            <div class="cc-done__price">
                <span class="cc-done__price-label">{{ __('checkout.voucher.success_code') }}</span>
                <span class="cc-done__price-value cc-mono">{{ $voucher['code'] }}</span>
            </div>
            <div class="cc-done__price">
                <span class="cc-done__price-label">{{ __('checkout.voucher.success_price') }}</span>
                <span class="cc-done__price-value cc-mono">{{ $voucher['price'] }}</span>
            </div>
            <p class="cc-done__text">{{ __('checkout.voucher.success_payment') }}</p>
            @if ($voucher['tourUrl'])
                <a href="{{ $voucher['tourUrl'] }}" class="cc-done__back">{{ __('checkout.voucher.success_back') }}</a>
            @endif
        </div>
    </div>
@endsection
