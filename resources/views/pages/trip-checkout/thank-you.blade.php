@extends('layouts.app-v2-1')

@section('title', __('checkout.trip.success_title'))

@section('meta_robots')
    <meta name="robots" content="noindex,nofollow">
@endsection

@section('custom_style')
    @include('pages.modern-checkout.partials.fonts')
@endsection

@section('content')
    <div class="cc cc--trip cc--done">
        <header class="cc-head">
            <div class="cc-head__inner">
                <a href="{{ $tripUrl }}" class="cc-head__back" aria-label="{{ __('checkout.trip.back') }}">
                    @include('pages.modern-checkout.partials.icon', ['name' => 'arrow-left', 'size' => 22, 'stroke' => 2])
                </a>
                <div class="cc-head__text">
                    <p class="cc-head__title">{{ $tripTitle }}</p>
                </div>
            </div>
        </header>

        <div class="cc-done" data-clarity-mask="True">
            <div class="cc-done__icon">
                @include('pages.modern-checkout.partials.icon', ['name' => 'check', 'size' => 34, 'stroke' => 2])
            </div>
            <h1 class="cc-done__title">{{ __('checkout.trip.success_title') }}</h1>
            <p class="cc-done__text">
                @if ($fixedDate && $date !== '')
                    {!! __('checkout.trip.success_text_fixed', [
                        'date' => e($date),
                        'email' => '<strong>'.e($booking->email).'</strong>',
                    ]) !!}
                @else
                    {!! __('checkout.trip.success_text_request', [
                        'email' => '<strong>'.e($booking->email).'</strong>',
                    ]) !!}
                @endif
            </p>
            <div class="cc-done__price">
                <span class="cc-done__price-label">{{ __('checkout.trip.success_price') }}</span>
                <span class="cc-done__price-value cc-mono">{{ $total }}</span>
            </div>
            <a href="{{ $tripUrl }}" class="cc-done__back">{{ __('checkout.trip.success_back') }}</a>
        </div>
    </div>
@endsection
