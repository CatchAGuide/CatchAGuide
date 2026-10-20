@extends('pages.profile.layouts.profile')
@section('title', __('profile.vouchers.title'))

@php
    $allTours = $tours['published']->concat($tours['unpublished']);
    $designIcons = ['cag_standard' => 'fa-file-pdf', 'guide_own' => 'fa-pen-fancy'];
@endphp

@section('profile-content')
    <div class="bookings-header">
        <h1 class="mb-0 text-white"><i class="fas fa-gift"></i> {{ __('profile.vouchers.title') }}</h1>
        <p class="mb-0 mt-2 text-white">{{ __('profile.vouchers.subtitle') }}</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger border-0 mb-4">
            <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- How it works: CaG passes the order on, the guide handles payment and the voucher. --}}
    <ol class="gv-steps">
        @foreach ([1 => 'fa-gift', 2 => 'fa-handshake', 3 => 'fa-paper-plane'] as $step => $icon)
            <li class="gv-steps__item">
                <span class="gv-steps__icon"><i class="fas {{ $icon }}"></i></span>
                <span class="gv-steps__text">
                    <strong>{{ __('profile.vouchers.step_'.$step.'_title') }}</strong>
                    <small>{{ __('profile.vouchers.step_'.$step.'_text') }}</small>
                </span>
            </li>
        @endforeach
    </ol>

    {{-- Voucher orders: the guide confirms each payment here or via the link in their email. Outside
         the settings form, because every row has its own form. --}}
    @if ($orders->isNotEmpty())
        <section class="gv-card" aria-labelledby="gv-orders-title">
            <header class="gv-card__head">
                <h2 class="gv-card__title" id="gv-orders-title">{{ __('profile.vouchers.orders_title') }}</h2>
                <p class="gv-card__text">{{ __('profile.vouchers.orders_text') }}</p>
            </header>
            <ul class="gv-orders">
                @foreach ($orders as $order)
                    <li class="gv-order">
                        <div class="gv-order__main">
                            <p class="gv-order__title">{{ $order->tour_title_snapshot }}</p>
                            <p class="gv-order__meta">
                                <span class="gv-order__code">{{ $order->voucher_code }}</span>
                                · {{ __('profile.vouchers.order_for', ['names' => $order->recipient_names]) }}
                                · {{ $order->buyerName() }} ({{ $order->buyer_email }})
                                · {{ $order->created_at->format(app()->getLocale() === 'de' ? 'd.m.Y' : 'M j, Y') }}
                            </p>
                        </div>
                        @if ($order->isPaid())
                            <span class="gv-order__status gv-order__status--paid">
                                {{ __('profile.vouchers.order_paid', ['date' => $order->paid_at->format(app()->getLocale() === 'de' ? 'd.m.Y' : 'M j, Y')]) }}
                            </span>
                        @else
                            <span class="gv-order__status">{{ __('profile.vouchers.order_open') }}</span>
                            <form class="gv-order__form" method="post" action="{{ route('profile.vouchers.orders.paid', $order) }}"
                                onsubmit="return confirm(@js(__('profile.vouchers.order_confirm_prompt', ['name' => $order->buyerName()])))">
                                @csrf
                                <button type="submit" class="gv-order__btn"><i class="fas fa-check"></i>{{ __('profile.vouchers.order_confirm') }}</button>
                            </form>
                        @endif
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    <form action="{{ route('profile.vouchers.update') }}" method="post" class="gv-form" id="gv-form">
        @csrf
        @method('PUT')

        <section class="gv-card" aria-labelledby="gv-design-title">
            <header class="gv-card__head">
                <h2 class="gv-card__title" id="gv-design-title">{{ __('profile.vouchers.design_title') }}</h2>
                <p class="gv-card__text">{{ __('profile.vouchers.design_text') }}</p>
            </header>

            <div class="gv-designs">
                @foreach ($designs as $option)
                    <div class="gv-design">
                        <input class="gv-design__input" type="radio" name="voucher_design" id="voucher_design_{{ $option->value }}"
                            value="{{ $option->value }}" @checked(old('voucher_design', $design->value) === $option->value)>
                        <label class="gv-design__label" for="voucher_design_{{ $option->value }}">
                            <span class="gv-design__icon"><i class="fas {{ $designIcons[$option->value] ?? 'fa-gift' }}"></i></span>
                            <span class="gv-design__name">{{ __('profile.vouchers.design_'.$option->value) }}</span>
                            <span class="gv-design__text">{{ __('profile.vouchers.design_'.$option->value.'_text') }}</span>
                            <span class="gv-design__check" aria-hidden="true"><i class="fas fa-check"></i></span>
                            @if ($option->generatesPdf())
                                <a class="gv-design__sample" href="{{ route('profile.vouchers.sample') }}" target="_blank" rel="noopener">
                                    <i class="fas fa-eye"></i>{{ __('profile.vouchers.sample_link') }}
                                </a>
                            @endif
                        </label>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="gv-card" aria-labelledby="gv-tours-title">
            <header class="gv-card__head gv-card__head--split">
                <div>
                    <h2 class="gv-card__title" id="gv-tours-title">{{ __('profile.vouchers.tours_title') }}</h2>
                    <p class="gv-card__text">{{ __('profile.vouchers.tours_text') }}</p>
                </div>
                @if ($allTours->isNotEmpty())
                    <div class="gv-bulk">
                        <span class="gv-bulk__count" data-gv-count
                            data-template="{{ __('profile.vouchers.enabled_count', ['on' => ':on', 'total' => ':total']) }}"></span>
                        <button type="button" class="gv-bulk__btn" data-gv-all="1">{{ __('profile.vouchers.all_on') }}</button>
                        <button type="button" class="gv-bulk__btn" data-gv-all="0">{{ __('profile.vouchers.all_off') }}</button>
                    </div>
                @endif
            </header>

            @if ($allTours->isEmpty())
                <p class="gv-empty">{{ __('profile.vouchers.no_tours') }}</p>
            @else
                @if ($tours['published']->isNotEmpty())
                    <ul class="gv-tours">
                        @foreach ($tours['published'] as $tour)
                            @include('pages.profile.partials.voucher-tour-row', ['tour' => $tour, 'published' => true])
                        @endforeach
                    </ul>
                @endif

                @if ($tours['unpublished']->isNotEmpty())
                    {{-- Drafts can't be bought until published; kept here so the setting is ready when they go live. --}}
                    <details class="gv-drafts" @if ($tours['published']->isEmpty()) open @endif>
                        <summary class="gv-drafts__summary">
                            <i class="fas fa-chevron-right"></i>
                            {{ __('profile.vouchers.unpublished_group', ['count' => $tours['unpublished']->count()]) }}
                        </summary>
                        <ul class="gv-tours gv-tours--drafts">
                            @foreach ($tours['unpublished'] as $tour)
                                @include('pages.profile.partials.voucher-tour-row', ['tour' => $tour, 'published' => false])
                            @endforeach
                        </ul>
                    </details>
                @endif
            @endif
        </section>

        <div class="gv-savebar">
            <p class="gv-savebar__hint"><i class="fas fa-info-circle"></i>{{ __('profile.vouchers.save_hint') }}</p>
            <button type="submit" class="gv-savebar__btn">
                <i class="fas fa-save"></i>{{ __('global.Save') }}
            </button>
        </div>
    </form>
@endsection

@section('js_after')
    @parent
    <script>
        (function () {
            var form = document.getElementById('gv-form');
            var count = form && form.querySelector('[data-gv-count]');
            if (!count) return;
            var boxes = form.querySelectorAll('input[name="voucher_enabled[]"]');

            function update() {
                var on = Array.prototype.filter.call(boxes, function (box) { return box.checked; }).length;
                count.textContent = count.dataset.template.replace(':on', on).replace(':total', boxes.length);
            }

            form.querySelectorAll('[data-gv-all]').forEach(function (button) {
                button.addEventListener('click', function () {
                    boxes.forEach(function (box) { box.checked = button.dataset.gvAll === '1'; });
                    update();
                });
            });
            boxes.forEach(function (box) { box.addEventListener('change', update); });
            update();
        })();
    </script>
@endsection
