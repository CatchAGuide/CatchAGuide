<section class="tc-card tc-payment" aria-labelledby="tc-payment-title">
    <h2 class="tc-card__title" id="tc-payment-title">{{ __('checkout.tour.payment_title') }}</h2>
    <p class="tc-payment__text">{{ __('checkout.tour.payment_text') }}</p>

    @if ($checkout->paymentMethods() !== [])
        <ul class="tc-payment__methods">
            @foreach ($checkout->paymentMethods() as $method)
                <li class="tc-chip">
                    @include('pages.modern-checkout.partials.icon', ['name' => $method, 'size' => 15, 'stroke' => 1.5])
                    {{ __('checkout.tour.payment_'.$method) }}
                </li>
            @endforeach
        </ul>
    @endif
</section>
