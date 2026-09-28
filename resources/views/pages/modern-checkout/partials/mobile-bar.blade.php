{{-- Mobile-only sticky total bar (hidden >= 1024px in CSS; visibility driven by StickyBarWatcher). --}}
<div class="tc-bar" :class="{ 'is-visible': showBar }" :aria-hidden="(!showBar).toString()" aria-hidden="true">
    <div class="tc-bar__info">
        <p class="tc-bar__total tc-mono">{{ __('checkout.tour.total') }} <span x-text="money(total)"></span></p>
        <p class="tc-bar__sub" x-text="barSubline"></p>
    </div>
    <button type="button" class="tc-bar__btn" @click="submit()" :disabled="loading" :tabindex="showBar ? 0 : -1" tabindex="-1">{{ $submitShortLabel ?? __('checkout.tour.submit_short') }}</button>
</div>
