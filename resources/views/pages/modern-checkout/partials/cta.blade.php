@use('App\Rules\Recaptcha')
@php($invisibleCaptcha = ($withCaptcha ?? true) && Recaptcha::active() && Recaptcha::invisibleConfigured())
<div class="tc-cta" x-ref="cta">
    @if ($withCaptcha ?? true)
        {{-- Invisible mode: no checkbox; the badge is hidden, which Google permits only with the
             attribution line below. --}}
        <div @class(['tc-cta__captcha', 'tc-cta__captcha--invisible' => $invisibleCaptcha]) x-ref="field_captcha">
            <x-recaptcha id="checkout-recaptcha" invisible />
        </div>
    @endif
    <p class="tc-field__error" x-show="errors.captcha" x-text="errors.captcha" role="alert" x-cloak></p>
    <p class="tc-alert" x-show="formError" x-text="formError" role="alert" x-cloak></p>

    <button type="button" class="tc-submit" x-ref="field_submit" @click="submit()" :disabled="loading" :aria-busy="loading.toString()">
        <span x-show="!loading">{{ $submitLabel ?? __('checkout.tour.submit') }}</span>
        <span x-show="loading" x-cloak>{{ __('checkout.processing') }}</span>
    </button>

    <p class="tc-cta__legal">
        {!! __('checkout.tour.legal', [
            'terms' => '<a href="'.e(route('law.agb')).'" target="_blank" rel="noopener">'.e(__('checkout.tour.legal_terms')).'</a>',
            'privacy' => '<a href="'.e(route('law.data-protection')).'" target="_blank" rel="noopener">'.e(__('checkout.tour.legal_privacy')).'</a>',
        ]) !!}
    </p>

    @if ($invisibleCaptcha)
        <p class="tc-cta__legal tc-cta__recaptcha">
            {!! __('checkout.tour.recaptcha_notice', [
                'privacy' => '<a href="https://policies.google.com/privacy" target="_blank" rel="noopener">'.e(__('checkout.tour.recaptcha_privacy')).'</a>',
                'terms' => '<a href="https://policies.google.com/terms" target="_blank" rel="noopener">'.e(__('checkout.tour.recaptcha_terms')).'</a>',
            ]) !!}
        </p>
    @endif

    {{-- $trustItems (optional): translation keys for the two check items; the SSL item always follows. --}}
    <ul class="tc-trust">
        @foreach ($trustItems ?? ['checkout.tour.trust_free', 'checkout.tour.trust_non_binding'] as $trustKey)
            <li>@include('pages.modern-checkout.partials.icon', ['name' => 'check', 'size' => 13, 'stroke' => 2]){{ __($trustKey) }}</li>
        @endforeach
        <li>@include('pages.modern-checkout.partials.icon', ['name' => 'lock', 'size' => 13, 'stroke' => 1.7]){{ __('checkout.tour.trust_ssl') }}</li>
    </ul>
</div>
