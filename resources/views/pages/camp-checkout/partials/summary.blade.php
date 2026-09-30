{{-- Price overview; rendered in the form on mobile and in the aside on desktop (CSS shows one). --}}
<h2 class="cc-summary__title">{{ __('checkout.camp.summary_title') }}</h2>
<ul class="cc-summary__lines">
    <template x-for="line in lines" :key="line.key">
        <li class="cc-summary__line">
            <span class="cc-summary__line-label">
                <span x-text="line.name"></span><span class="cc-summary__line-detail" x-show="line.detail" x-text="' · ' + line.detail"></span>
            </span>
            <span class="cc-summary__line-amount" x-text="line.amount"></span>
        </li>
    </template>
</ul>
<hr class="cc-summary__divider">
<div class="cc-summary__total">
    <span class="cc-summary__total-label">{{ __('checkout.camp.total') }}</span>
    <span class="cc-summary__total-value" x-text="totalLabel"></span>
</div>
<p class="cc-summary__note">{{ __('checkout.camp.estimate_note') }}</p>
