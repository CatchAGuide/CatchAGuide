<button type="button" class="cc-submit" @click="submit()" :disabled="loading" :aria-busy="loading.toString()">
    <span x-show="!loading">{{ __('checkout.trip.submit') }}</span>
    <span x-show="loading" x-cloak>{{ __('checkout.processing') }}</span>
    @include('pages.modern-checkout.partials.icon', ['name' => 'arrow-right', 'size' => 18, 'stroke' => 2])
</button>
