{{-- Price overview; rendered in the form on mobile and in the aside on desktop (CSS shows one). --}}
<h2 class="cc-summary__title">{{ __('checkout.trip.summary_title') }}</h2>
<p class="cc-summary__empty" x-show="!hasDate">{{ __('checkout.trip.summary_empty') }}</p>
<div x-show="hasDate" x-cloak>
    <ul class="cc-summary__lines">
        <li class="cc-summary__line">
            <span class="cc-summary__line-label" x-text="lineLabel"></span>
            <span class="cc-summary__line-amount" x-text="lineAmount"></span>
        </li>
    </ul>
    <hr class="cc-summary__divider">
    <div class="cc-summary__total">
        <span class="cc-summary__total-label">{{ __('checkout.trip.total') }}</span>
        <span class="cc-summary__total-value" x-text="totalLabel"></span>
    </div>
    <p class="cc-summary__note" x-text="note"></p>
</div>
