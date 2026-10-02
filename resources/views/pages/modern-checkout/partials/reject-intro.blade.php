<div class="tc-notice">
    <p class="tc-notice__text">
        {{ $customer !== ''
            ? __('checkout.reject.intro', ['max' => $max, 'customer' => $customer])
            : __('checkout.reject.intro_no_customer', ['max' => $max]) }}
    </p>
</div>
