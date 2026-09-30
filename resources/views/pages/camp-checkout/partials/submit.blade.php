{{-- Desktop submit block in the aside (mobile uses the sticky bar in layout.blade.php). --}}
<div class="cc-cta" x-ref="field_submit">
    <p class="cc-cta__alert" x-show="hasErrors" role="alert" x-cloak>{{ __('checkout.camp.form_errors') }}</p>
    <p class="cc-cta__alert" x-show="formError" x-text="formError" role="alert" x-cloak></p>
    <button type="button" class="cc-submit" @click="submit()" :disabled="loading" :aria-busy="loading.toString()">
        <span x-show="!loading">{{ __('checkout.camp.submit') }}</span>
        <span x-show="loading" x-cloak>{{ __('checkout.processing') }}</span>
        @include('pages.modern-checkout.partials.icon', ['name' => 'arrow-right', 'size' => 18, 'stroke' => 2])
    </button>
    @include('pages.camp-checkout.partials.legal')
</div>
