@php
    $allTours = $tours['published']->concat($tours['unpublished']);
    $designIcons = ['cag_standard' => 'fa-file-pdf', 'guide_own' => 'fa-pen-fancy'];
@endphp
<div class="ga-page">
    <header class="ga-page__header">
        <div>
            <a href="{{ route('guide.area.bookings', ['tab' => 'vouchers']) }}" class="ga-back"><i class="fas fa-arrow-left" aria-hidden="true"></i> {{ __('guide_area.vouchers.orders_link') }}</a>
            <h1 class="ga-page__title">{{ __('profile.vouchers.title') }}</h1>
            <p class="ga-page__subtitle">{{ __('profile.vouchers.subtitle') }}</p>
        </div>
    </header>

    <ol class="ga-steps">
        @foreach ([1 => 'fa-gift', 2 => 'fa-handshake', 3 => 'fa-paper-plane'] as $step => $icon)
            <li class="ga-steps__item">
                <span class="ga-steps__icon"><i class="fas {{ $icon }}" aria-hidden="true"></i></span>
                <span>
                    <strong>{{ __('profile.vouchers.step_'.$step.'_title') }}</strong>
                    <small>{{ __('profile.vouchers.step_'.$step.'_text') }}</small>
                </span>
            </li>
        @endforeach
    </ol>

    <form wire:submit="save" novalidate>
        <section class="ga-card" aria-labelledby="ga-design-title">
            <h2 id="ga-design-title" class="ga-card__title">{{ __('profile.vouchers.design_title') }}</h2>
            <p class="ga-card__subtitle">{{ __('profile.vouchers.design_text') }}</p>

            <div class="ga-options">
                @foreach ($designs as $option)
                    <label class="ga-option {{ $design === $option->value ? 'is-selected' : '' }}">
                        <input type="radio" class="ga-visually-hidden" wire:model.live="design" value="{{ $option->value }}">
                        <span class="ga-option__icon"><i class="fas {{ $designIcons[$option->value] ?? 'fa-gift' }}" aria-hidden="true"></i></span>
                        <span class="ga-option__name">{{ __('profile.vouchers.design_'.$option->value) }}</span>
                        <span class="ga-option__text">{{ __('profile.vouchers.design_'.$option->value.'_text') }}</span>
                        @if ($option->generatesPdf())
                            <a class="ga-option__link" href="{{ route('profile.vouchers.sample') }}" target="_blank" rel="noopener">
                                <i class="fas fa-eye" aria-hidden="true"></i> {{ __('profile.vouchers.sample_link') }}
                            </a>
                        @endif
                    </label>
                @endforeach
            </div>
            @error('design') <p class="ga-error">{{ $message }}</p> @enderror
        </section>

        <section class="ga-card" aria-labelledby="ga-voucher-tours-title">
            <div class="ga-card__head">
                <div>
                    <h2 id="ga-voucher-tours-title" class="ga-card__title">{{ __('profile.vouchers.tours_title') }}</h2>
                    <p class="ga-card__subtitle">{{ __('profile.vouchers.tours_text') }}</p>
                </div>
                @if ($allTours->isNotEmpty())
                    <div class="ga-bulk">
                        <span class="ga-help">{{ __('profile.vouchers.enabled_count', ['on' => count($enabled), 'total' => $allTours->count()]) }}</span>
                        <button type="button" class="ga-btn ga-btn--outline ga-btn--sm" wire:click="setAll(true)">{{ __('profile.vouchers.all_on') }}</button>
                        <button type="button" class="ga-btn ga-btn--outline ga-btn--sm" wire:click="setAll(false)">{{ __('profile.vouchers.all_off') }}</button>
                    </div>
                @endif
            </div>

            @if ($allTours->isEmpty())
                <p class="ga-help">{{ __('profile.vouchers.no_tours') }}</p>
            @else
                <ul class="ga-toggle-list">
                    @foreach ($allTours as $tour)
                        <li class="ga-toggle-list__item" wire:key="voucher-tour-{{ $tour->id }}">
                            <label class="ga-switch ga-switch--row">
                                <span class="ga-switch__label">
                                    {{ $tour->display_title }}
                                    @if ((int) $tour->status !== 1)
                                        <span class="ga-badge">{{ __('profile.vouchers.tour_inactive') }}</span>
                                    @endif
                                </span>
                                <input type="checkbox" class="ga-switch__input" role="switch" wire:model.live="enabled" value="{{ $tour->id }}">
                                <span class="ga-switch__track" aria-hidden="true"></span>
                            </label>
                            @if ($savedDesign->generatesPdf())
                                <a class="ga-btn ga-btn--ghost ga-btn--sm" href="{{ route('profile.vouchers.preview', $tour->id) }}" target="_blank" rel="noopener">
                                    <i class="fas fa-file-pdf" aria-hidden="true"></i> {{ __('profile.vouchers.preview_pdf') }}
                                </a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
            @error('enabled.*') <p class="ga-error">{{ $message }}</p> @enderror
        </section>

        <div class="ga-savebar">
            <p class="ga-help">{{ __('profile.vouchers.save_hint') }}</p>
            <button type="submit" class="ga-btn ga-btn--dark" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">{{ __('global.Save') }}</span>
                <span wire:loading wire:target="save">{{ __('guide_area.common.saving') }}</span>
            </button>
        </div>
    </form>
</div>
