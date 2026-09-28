{{-- Request details + send button (desktop: sticky aside; mobile: after the message). --}}
<div class="tc-request">
    <h2 class="tc-card__title">{{ __('checkout.reject.request_title') }}</h2>
    <dl class="tc-request__list">
        @if ($request['date'])
            <div><dt>{{ __('checkout.reject.requested_date') }}</dt><dd>{{ $request['date'] }}</dd></div>
        @endif
        <div><dt>{{ __('checkout.reject.guests') }}</dt><dd>{{ $request['guests'] }}</dd></div>
        @if ($request['customer'] !== '')
            <div><dt>{{ __('checkout.reject.customer') }}</dt><dd>{{ $request['customer'] }}</dd></div>
        @endif
        <div><dt>{{ __('checkout.reject.total') }}</dt><dd class="tc-mono">{{ $request['total'] }}</dd></div>
    </dl>
</div>

<div class="tc-cta tc-reject-cta" x-ref="cta">
    <p class="tc-reject-cta__count" x-text="countLabel"></p>
    <p class="tc-alert" x-show="formError" x-text="formError" role="alert" x-cloak></p>

    <button type="button" class="tc-submit" x-ref="field_submit" @click="submit()" :disabled="loading" :aria-busy="loading.toString()">
        <span x-show="!loading">{{ __('checkout.reject.submit') }}</span>
        <span x-show="loading" x-cloak>{{ __('checkout.processing') }}</span>
    </button>
    <p class="tc-cta__legal">{{ __('checkout.reject.submit_hint') }}</p>
</div>
