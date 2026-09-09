@props(['label' => null])
<span {{ $attributes->merge(['class' => 'loading-inline']) }} role="status" aria-live="polite">
    <x-loading.spinner />
    @if($label)
        <span class="visually-hidden">{{ $label }}</span>
    @endif
</span>
