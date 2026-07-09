@props([
    'title' => null,
    'subtitle' => null,
    'linkUrl' => null,
    'linkLabel' => null,
    'sliderId' => 'vacation-slider',
])

@php
    $swiperClass = 'vacation-card-slider__swiper--' . $sliderId;
@endphp

<div class="vacation-card-slider" data-vacation-card-slider data-slider-id="{{ $sliderId }}">
    @if($title)
        <div class="vacation-card-slider__header">
            <div class="vacation-card-slider__intro">
                <h2 class="vacation-card-slider__title">{{ $title }}</h2>
                @if($subtitle)
                    <p class="vacation-card-slider__subtitle">{{ $subtitle }}</p>
                @endif
            </div>

            <div class="vacation-card-slider__actions">
                @if($linkUrl && $linkLabel)
                    <a href="{{ $linkUrl }}" class="vacation-card-slider__link">{{ $linkLabel }}</a>
                @endif
                <div class="vacation-card-slider__nav">
                    <button
                        type="button"
                        class="vacation-card-slider__btn vacation-card-slider__btn--prev"
                        data-slider-prev="{{ $sliderId }}"
                        aria-label="{{ __('vacations.slider_prev') }}"
                    >
                        <i class="fas fa-chevron-left" aria-hidden="true"></i>
                    </button>
                    <button
                        type="button"
                        class="vacation-card-slider__btn vacation-card-slider__btn--next"
                        data-slider-next="{{ $sliderId }}"
                        aria-label="{{ __('vacations.slider_next') }}"
                    >
                        <i class="fas fa-chevron-right" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <div class="swiper vacation-card-slider__swiper {{ $swiperClass }}">
        <div class="swiper-wrapper">
            {{ $slot }}
        </div>
    </div>
</div>

@once
    @push('js_push')
        <script>
            (function () {
                function initVacationCardSliders() {
                    if (typeof Swiper === 'undefined') {
                        return;
                    }

                    document.querySelectorAll('[data-vacation-card-slider]').forEach(function (root) {
                        if (root.dataset.sliderInit === '1') {
                            return;
                        }

                        var sliderId = root.dataset.sliderId;
                        var swiperEl = root.querySelector('.vacation-card-slider__swiper--' + sliderId);

                        if (!swiperEl) {
                            return;
                        }

                        root.dataset.sliderInit = '1';

                        new Swiper(swiperEl, {
                            slidesPerView: 1.08,
                            spaceBetween: 12,
                            watchOverflow: true,
                            grabCursor: true,
                            breakpoints: {
                                576: { slidesPerView: 1.45, spaceBetween: 14 },
                                768: { slidesPerView: 2.1, spaceBetween: 16 },
                                992: { slidesPerView: 2.75, spaceBetween: 18 },
                                1200: { slidesPerView: 3.25, spaceBetween: 20 },
                            },
                            navigation: {
                                nextEl: root.querySelector('[data-slider-next="' + sliderId + '"]'),
                                prevEl: root.querySelector('[data-slider-prev="' + sliderId + '"]'),
                            },
                        });
                    });
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initVacationCardSliders);
                } else {
                    initVacationCardSliders();
                }
            })();
        </script>
    @endpush
@endonce
