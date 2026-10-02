{{-- Reschedule links that can't be used (any more). Never a dead end: always offer a next step. --}}
@extends('layouts.app-v2-1')

@section('title', __('checkout.reschedule.states.'.$state.'.title'))

@section('meta_robots')
    <meta name="robots" content="noindex,nofollow">
@endsection

@section('custom_style')
    <meta name="referrer" content="no-referrer">
    @include('pages.modern-checkout.partials.fonts')
@endsection

@section('content')
    <div class="tc tc--state">
        <div class="tc__container">
            <section class="tc-card tc-state" aria-labelledby="tc-state-title">
                <span class="tc-steps__icon tc-state__icon">
                    @include('pages.modern-checkout.partials.icon', ['name' => $state === 'used' ? 'check' : 'clock', 'size' => 20])
                </span>
                <h1 class="tc__title tc-state__title" id="tc-state-title">{{ __('checkout.reschedule.states.'.$state.'.title') }}</h1>
                <p class="tc-state__text">{{ __('checkout.reschedule.states.'.$state.'.text') }}</p>

                <div class="tc-state__actions">
                    @if ($tourUrl && $state !== 'used')
                        <a class="tc-submit tc-state__primary" href="{{ $tourUrl }}">{{ __('checkout.reschedule.cta_choose_date') }}</a>
                    @endif
                    <a class="{{ $tourUrl && $state !== 'used' ? 'tc-state__secondary' : 'tc-submit tc-state__primary' }}" href="{{ route('guidings.index') }}">{{ __('checkout.reschedule.cta_browse') }}</a>
                    <a class="tc-link tc-state__link" href="{{ route('additional.contact') }}">{{ __('checkout.reschedule.cta_contact') }}</a>
                </div>

                @if ($tourTitle && $state === 'expired')
                    <p class="tc-muted tc-state__tour">{{ $tourTitle }}</p>
                @endif
            </section>
        </div>
    </div>
@endsection
