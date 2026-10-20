@extends('layouts.app-v2-1')

@section('title', __('checkout.voucher.payment.title'))

@section('meta_robots')
    <meta name="robots" content="noindex,nofollow">
@endsection

@section('custom_style')
    @include('pages.modern-checkout.partials.fonts')
@endsection

@php($status = session('voucher_payment'))

@section('content')
    {{-- Guide confirms the buyer's payment (VoucherPaymentController). Same design as the voucher thank-you page.
         Expects: $order, $voucher (VoucherOrderPresenter::details). --}}
    <div class="cc cc--trip cc--done">
        <header class="cc-head">
            <div class="cc-head__inner">
                <div class="cc-head__text">
                    <p class="cc-head__title">{{ $voucher['tourTitle'] }}</p>
                </div>
            </div>
        </header>

        <div class="cc-done" data-clarity-mask="True">
            @if ($order->isPaid())
                <div class="cc-done__icon">
                    @include('pages.modern-checkout.partials.icon', ['name' => 'check', 'size' => 34, 'stroke' => 2])
                </div>
                <h1 class="cc-done__title">{{ __('checkout.voucher.payment.'.($status === 'confirmed' ? 'done_title' : 'already_title')) }}</h1>
                <p class="cc-done__text">
                    {{ __('checkout.voucher.payment.'.($order->hasPdf() ? 'done_text_pdf' : 'done_text_own'), ['email' => $order->buyer_email]) }}
                </p>
                <div class="cc-done__price">
                    <span class="cc-done__price-label">{{ __('checkout.voucher.payment.paid_on') }}</span>
                    <span class="cc-done__price-value cc-mono">{{ $order->paid_at->format(app()->getLocale() === 'de' ? 'd.m.Y' : 'M j, Y') }}</span>
                </div>
            @else
                <h1 class="cc-done__title">{{ __('checkout.voucher.payment.title') }}</h1>
                <p class="cc-done__text">
                    {!! __('checkout.voucher.payment.text', [
                        'buyer' => '<strong>'.e($voucher['buyerName']).'</strong>',
                        'price' => '<strong>'.e($voucher['price']).'</strong>',
                    ]) !!}
                </p>
                <div class="cc-done__price">
                    <span class="cc-done__price-label">{{ __('checkout.voucher.success_code') }}</span>
                    <span class="cc-done__price-value cc-mono">{{ $voucher['code'] }}</span>
                </div>
                <div class="cc-done__price">
                    <span class="cc-done__price-label">{{ __('checkout.voucher.payment.recipients') }}</span>
                    <span class="cc-done__price-value">{{ $voucher['recipients'] }}</span>
                </div>
                <p class="cc-done__text">{{ __('checkout.voucher.payment.'.($order->design_type?->generatesPdf() ? 'next_pdf' : 'next_own')) }}</p>
                <form method="post" action="{{ route('voucher-payment.confirm', ['token' => $order->payment_token]) }}">
                    @csrf
                    <button type="submit" class="cc-done__back cc-done__confirm">{{ __('checkout.voucher.payment.button') }}</button>
                </form>
            @endif
        </div>
    </div>
@endsection
