@props([
    'title' => null,
    'subtitle' => null,
    'linkUrl' => null,
    'linkLabel' => null,
    'sliderId' => 'countries',
])

@php
    $swiperClass = 'vacation-country-slider__swiper--' . $sliderId;
@endphp

<div class="vacation-country-slider" data-vacation-country-slider data-slider-id="{{ $sliderId }}">
    @if($title)
        <div class="vacation-country-slider__header">
            <div class="vacation-country-slider__intro">
                <h2 class="vacation-country-slider__title">{{ $title }}</h2>
                @if($subtitle)
                    <p class="vacation-country-slider__subtitle">{{ $subtitle }}</p>
                @endif
            </div>

            <div class="vacation-country-slider__actions">
                @if($linkUrl && $linkLabel)
                    <a href="{{ $linkUrl }}" class="vacation-country-slider__link">
                        {{ $linkLabel }}
                        <i class="fas fa-arrow-right" aria-hidden="true"></i>
                    </a>
                @endif

                <div class="vacation-country-slider__nav">
                    <button
                        type="button"
                        class="vacation-country-slider__btn vacation-country-slider__btn--prev"
                        data-country-slider-prev="{{ $sliderId }}"
                        aria-label="{{ __('vacations.slider_prev') }}"
                    >
                        <i class="fas fa-chevron-left" aria-hidden="true"></i>
                    </button>
                    <button
                        type="button"
                        class="vacation-country-slider__btn vacation-country-slider__btn--next"
                        data-country-slider-next="{{ $sliderId }}"
                        aria-label="{{ __('vacations.slider_next') }}"
                    >
                        <i class="fas fa-chevron-right" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <div class="swiper vacation-country-slider__swiper {{ $swiperClass }}">
        <div class="swiper-wrapper">
            {{ $slot }}
        </div>
    </div>
</div>

@once
    @push('js_push')
        <script>
            (function () {
                function initVacationCountrySliders() {
                    if (typeof Swiper === 'undefined') {
                        return;
                    }

                    document.querySelectorAll('[data-vacation-country-slider]').forEach(function (root) {
                        if (root.dataset.sliderInit === '1') {
                            return;
                        }

                        var sliderId = root.dataset.sliderId;
                        var swiperEl = root.querySelector('.vacation-country-slider__swiper--' + sliderId);

                        if (!swiperEl) {
                            return;
                        }

                        root.dataset.sliderInit = '1';

                        new Swiper(swiperEl, {
                            slidesPerView: 1.15,
                            spaceBetween: 14,
                            watchOverflow: true,
                            grabCursor: true,
                            breakpoints: {
                                576: { slidesPerView: 1.65, spaceBetween: 16 },
                                768: { slidesPerView: 2.35, spaceBetween: 18 },
                                992: { slidesPerView: 3.15, spaceBetween: 20 },
                                1200: { slidesPerView: 4, spaceBetween: 22 },
                            },
                            navigation: {
                                nextEl: root.querySelector('[data-country-slider-next="' + sliderId + '"]'),
                                prevEl: root.querySelector('[data-country-slider-prev="' + sliderId + '"]'),
                            },
                        });
                    });
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initVacationCountrySliders);
                } else {
                    initVacationCountrySliders();
                }
            })();
        </script>
    @endpush
@endonce
