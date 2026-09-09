@props(['message' => null])
<div {{ $attributes->merge(['class' => 'page-loading-overlay']) }} role="status" aria-live="polite" aria-busy="true">
    <span class="page-loading-overlay__spinner">
        <x-loading.spinner />
    </span>
    <span class="page-loading-overlay__text">{{ $message ?? __('loading.casting') }}</span>
</div>
