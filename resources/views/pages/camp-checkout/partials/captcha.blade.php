{{-- Single reCAPTCHA widget for both layouts. Invisible mode hides the badge, which Google permits only with the attribution line. --}}
@use('App\Rules\Recaptcha')
@php($invisibleCaptcha = Recaptcha::active() && Recaptcha::invisibleConfigured())
<div @class(['cc-captcha__widget', 'cc-captcha__widget--invisible' => $invisibleCaptcha])>
    <x-recaptcha id="checkout-recaptcha" invisible />
</div>
<p class="cc-field__error" x-show="errors.captcha" x-text="errors.captcha" role="alert" x-cloak></p>
@if ($invisibleCaptcha)
    <p class="cc-legal cc-legal--start">
        {!! __('checkout.tour.recaptcha_notice', [
            'privacy' => '<a href="https://policies.google.com/privacy" target="_blank" rel="noopener">'.e(__('checkout.tour.recaptcha_privacy')).'</a>',
            'terms' => '<a href="https://policies.google.com/terms" target="_blank" rel="noopener">'.e(__('checkout.tour.recaptcha_terms')).'</a>',
        ]) !!}
    </p>
@endif
