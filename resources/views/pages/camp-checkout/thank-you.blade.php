@extends('layouts.app-v2-1')

@section('title', __('checkout.camp.success_title'))

@section('meta_robots')
    <meta name="robots" content="noindex,nofollow">
@endsection

@section('custom_style')
    @include('pages.modern-checkout.partials.fonts')
@endsection

@section('content')
    <div class="cc cc--done">
        <header class="cc-head">
            <div class="cc-head__inner">
                <a href="{{ $campUrl }}" class="cc-head__back" aria-label="{{ __('checkout.camp.back') }}">
                    @include('pages.modern-checkout.partials.icon', ['name' => 'arrow-left', 'size' => 22, 'stroke' => 2])
                </a>
                <div class="cc-head__text">
                    <p class="cc-head__title">{{ $campTitle }}</p>
                </div>
            </div>
        </header>

        <div class="cc-done" data-clarity-mask="True">
            <div class="cc-done__icon">
                @include('pages.modern-checkout.partials.icon', ['name' => 'check', 'size' => 34, 'stroke' => 2])
            </div>
            <h1 class="cc-done__title">{{ __('checkout.camp.success_title') }}</h1>
            <p class="cc-done__text">
                {!! __('checkout.camp.success_text', [
                    'date' => e($arrival),
                    'email' => '<strong>'.e($booking->email).'</strong>',
                ]) !!}
            </p>
            <div class="cc-done__price">
                <span class="cc-done__price-label">{{ __('checkout.camp.success_price') }}</span>
                <span class="cc-done__price-value cc-mono">{{ $total }}</span>
            </div>
            <a href="{{ $campUrl }}" class="cc-done__back">{{ __('checkout.camp.success_back') }}</a>
        </div>
    </div>
@endsection
