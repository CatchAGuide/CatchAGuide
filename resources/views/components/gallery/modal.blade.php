@props([
    'id',
    'images' => [],
    'title' => '',
    'type' => 'tour',
    'badge' => null,
    'location' => null,
    'rating' => null,
    'reviewCount' => 0,
    'specs' => [],
    'pricePrefix' => null,
    'priceDisplay' => null,
    'priceSuffix' => null,
    'priceNote' => null,
    'ctaUrl' => null,
    'ctaLabel' => null,
])

@php
    $images = array_values(array_filter($images ?? []));
    $galleryCount = count($images);
    $ratingValue = isset($rating) ? (float) $rating : null;
    $reviewCount = (int) $reviewCount;
    $specLabels = array_values(array_filter(array_map(function ($spec) {
        if (is_string($spec)) {
            return $spec;
        }
        if (is_array($spec)) {
            return $spec['label'] ?? null;
        }

        return null;
    }, $specs ?? [])));
@endphp

@if($galleryCount > 0)
    <div
        {{ $attributes->class([
            'vacation-gallery-modal',
            'offers-gallery-modal',
            'offers-gallery-modal--'.$type,
        ]) }}
        data-vacation-modal="{{ $id }}"
        role="dialog"
        aria-modal="true"
        aria-label="{{ $title }}"
    >
        <div class="offers-gallery-modal__shell">
            <div class="offers-gallery-modal__top">
                <div class="offers-gallery-modal__top-right">
                    @if($galleryCount > 1)
                        <div class="offers-gallery-modal__top-nav">
                            <button
                                type="button"
                                class="offers-gallery-modal__chip-nav"
                                data-offers-gallery-modal-prev
                                aria-label="{{ __('vacations.gallery_prev') }}"
                            >&#10094;</button>
                            <button
                                type="button"
                                class="offers-gallery-modal__chip-nav"
                                data-offers-gallery-modal-next
                                aria-label="{{ __('vacations.gallery_next') }}"
                            >&#10095;</button>
                        </div>
                    @endif
                    <button
                        type="button"
                        class="offers-gallery-modal__close"
                        data-offers-gallery-modal-close
                        aria-label="{{ __('vacations.gallery_close') }}"
                    >&times;</button>
                </div>
            </div>

            <div class="offers-gallery-modal__stage" data-offers-gallery-stage>
                <div class="offers-gallery-modal__frame">
                    <div
                        class="offers-gallery-modal__loader"
                        data-offers-gallery-loader
                        hidden
                        aria-hidden="true"
                    >
                        <x-loading.inline class="offers-gallery-modal__spinner" />
                        <span class="offers-gallery-modal__loader-text">{{ __('vacations.loading') }}</span>
                    </div>
                    <img
                        class="offers-gallery-modal__image is-ready"
                        data-offers-gallery-modal-image
                        src="{{ $images[0] }}"
                        alt="{{ $title }}"
                        draggable="false"
                        decoding="async"
                    >
                    @if($badge)
                        <span class="offers-gallery-modal__badge">{{ $badge }}</span>
                    @endif
                    @if($galleryCount > 1)
                        <div class="offers-gallery-modal__counter" aria-live="polite">
                            <span class="vacation-gallery-modal__current">1</span>
                            <span aria-hidden="true">/</span>
                            <span class="vacation-gallery-modal__total">{{ $galleryCount }}</span>
                        </div>
                        <button
                            type="button"
                            class="offers-gallery-modal__nav offers-gallery-modal__nav--prev"
                            data-offers-gallery-modal-prev
                            aria-label="{{ __('vacations.gallery_prev') }}"
                        >&#10094;</button>
                        <button
                            type="button"
                            class="offers-gallery-modal__nav offers-gallery-modal__nav--next"
                            data-offers-gallery-modal-next
                            aria-label="{{ __('vacations.gallery_next') }}"
                        >&#10095;</button>
                    @endif
                </div>
            </div>

            <div class="offers-gallery-modal__dock">
                <div class="offers-gallery-modal__info">
                    @if($title !== '')
                        <h3 class="offers-gallery-modal__title">{{ $title }}</h3>
                    @endif
                    <div class="offers-gallery-modal__meta">
                        @if(!empty($location))
                            <span class="offers-gallery-modal__location">
                                <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                                {{ $location }}
                            </span>
                        @endif
                        @if($ratingValue)
                            <span
                                class="offers-gallery-modal__score"
                                @if($reviewCount > 0)
                                    title="{{ trans_choice('offers.reviews_count', $reviewCount, ['count' => $reviewCount]) }}"
                                @endif
                            >
                                <span class="offers-gallery-modal__score-value">{{ number_format($ratingValue, 1) }}</span>
                                @if($reviewCount > 0)
                                    <span class="offers-gallery-modal__score-meta">({{ $reviewCount }})</span>
                                @endif
                            </span>
                        @endif
                    </div>
                    @if(!empty($specLabels))
                        <ul class="offers-gallery-modal__specs">
                            @foreach(array_slice($specLabels, 0, 3) as $specLabel)
                                <li>{{ $specLabel }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <div class="offers-gallery-modal__actions">
                    @if($priceDisplay)
                        <div class="offers-gallery-modal__price{{ $priceNote ? ' offers-gallery-modal__price--guest-total' : '' }}">
                            @if($pricePrefix)
                                <span class="offers-gallery-modal__price-prefix">{{ $pricePrefix }}</span>
                            @endif
                            <span class="offers-gallery-modal__price-amount">{{ $priceDisplay }}</span>
                            @if($priceSuffix)
                                <span class="offers-gallery-modal__price-suffix">{{ $priceSuffix }}</span>
                            @endif
                            @if($priceNote)
                                <span class="offers-gallery-modal__price-note">{{ $priceNote }}</span>
                            @endif
                        </div>
                    @endif
                    @if($ctaUrl && $ctaLabel)
                        <a href="{{ $ctaUrl }}" class="offers-gallery-modal__cta">
                            {{ $ctaLabel }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
