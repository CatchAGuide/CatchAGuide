@props([
    'images' => [],
    'alt' => '',
    'galleryId' => '',
    'url' => null,
])

@php
    $galleryImages = array_values(array_filter($images));
    $galleryCount = count($galleryImages);
@endphp

<div
    class="vacation-product-card__gallery"
    data-vacation-card-gallery="{{ $galleryId }}"
    data-gallery-images='@json($galleryImages)'
>
    @if($galleryCount > 0)
        @if($url)
            <a href="{{ $url }}" class="vacation-product-card__gallery-link" aria-label="{{ $alt }}">
                <img
                    src="{{ $galleryImages[0] }}"
                    alt="{{ $alt }}"
                    loading="lazy"
                    data-vacation-card-gallery-image
                >
            </a>
        @else
            <img
                src="{{ $galleryImages[0] }}"
                alt="{{ $alt }}"
                loading="lazy"
                data-vacation-card-gallery-image
            >
        @endif

        @if($galleryCount > 1)
            <button
                type="button"
                class="vacation-product-card__gallery-nav vacation-product-card__gallery-nav--prev"
                data-vacation-card-prev
                aria-label="{{ __('vacations.gallery_prev') }}"
            >‹</button>
            <button
                type="button"
                class="vacation-product-card__gallery-nav vacation-product-card__gallery-nav--next"
                data-vacation-card-next
                aria-label="{{ __('vacations.gallery_next') }}"
            >›</button>
            <div class="vacation-product-card__gallery-counter" data-vacation-card-counter>1/{{ $galleryCount }}</div>
        @endif
    @else
        <img src="{{ media_url(null) }}" alt="{{ $alt }}" loading="lazy">
    @endif
</div>
