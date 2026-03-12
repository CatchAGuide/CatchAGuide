@extends('layouts.app')

@section('title', $tripView['title'] ?? 'Trip')
@section('description', \Illuminate\Support\Str::limit($tripView['description']['full'] ?? '', 155))

@section('content')
    <div class="trip-offer-page">
        <div class="trip-offer-page__hero-wrapper">
            <div class="trip-offer-page__hero-heading">
                <h1 class="trip-offer-page__title">
                    {{ $tripView['title'] }}
                </h1>
                <div class="trip-offer-page__location-row">
                    <span class="trip-offer-page__location">
                        {{ $tripView['city'] }},
                        {{ $tripView['region'] }},
                        {{ $tripView['country'] }}
                    </span>
                    <button type="button" class="trip-offer-page__map-link">
                        <i class="fas fa-map-marker-alt"></i>
                        {{ __('vacations.show_on_map') }}
                    </button>
                </div>
            </div>

            @php
                $primaryImage = $gallery['primaryImage'] ?? null;
                $topRightImages = $gallery['topRightImages'] ?? [];
                $bottomStripImages = $gallery['bottomStripImages'] ?? [];
                $allImages = $gallery['all'] ?? [];
                $remainingGalleryCount = $gallery['remainingGalleryCount'] ?? 0;
            @endphp

            <div class="trip-offer-page__hero-grid" data-trip-gallery="{{ $tripView['id'] }}"
                 data-trip-gallery-images='@json($allImages)'>
                <div class="trip-offer-page__hero-main">
                    @if($primaryImage)
                        <img
                            src="{{ $primaryImage }}"
                            alt="{{ $tripView['title'] }}"
                            class="trip-offer-page__hero-main-img"
                            data-trip-gallery-image
                        >
                    @endif

                        @if(!empty($tripView['target_species']) || !empty($tripView['fishing_methods']) || !empty($tripView['group_size']['max']) || $tripView['skill_level'])
                        <div class="trip-offer-page__glance-overlay">
                            <div class="trip-offer-page__glance-grid">
                                @if(!empty($tripView['target_species']))
                                    <div class="trip-offer-page__glance-card">
                                        <span class="trip-offer-page__glance-icon material-symbols-outlined">
                                            set_meal
                                        </span>
                                        <p class="trip-offer-page__glance-label">
                                            {{ __('trips.target_species') }}
                                        </p>
                                        <p class="trip-offer-page__glance-value">
                                            {{ implode(', ', $tripView['target_species']) }}
                                        </p>
                                    </div>
                                @endif

                                @if(!empty($tripView['fishing_methods']))
                                    <div class="trip-offer-page__glance-card">
                                        <span class="trip-offer-page__glance-icon material-symbols-outlined">
                                            directions_boat
                                        </span>
                                        <p class="trip-offer-page__glance-label">
                                            {{ __('trips.fishing_methods') }}
                                        </p>
                                        <p class="trip-offer-page__glance-value">
                                            {{ implode(', ', $tripView['fishing_methods']) }}
                                        </p>
                                    </div>
                                @endif

                                @if(!empty($tripView['group_size']['max']))
                                    <div class="trip-offer-page__glance-card">
                                        <span class="trip-offer-page__glance-icon material-symbols-outlined">
                                            groups
                                        </span>
                                        <p class="trip-offer-page__glance-label">
                                            {{ __('trips.group_size_max') }}
                                        </p>
                                        <p class="trip-offer-page__glance-value">
                                            {{ $tripView['group_size']['min'] ?? 1 }}–{{ $tripView['group_size']['max'] }} {{ __('trips.group_size_max') }}
                                        </p>
                                    </div>
                                @endif

                                @if($tripView['skill_level'])
                                    <div class="trip-offer-page__glance-card">
                                        <span class="trip-offer-page__glance-icon material-symbols-outlined">
                                            fitness_center
                                        </span>
                                        <p class="trip-offer-page__glance-label">
                                            {{ __('trips.skill_level') }}
                                        </p>
                                        <p class="trip-offer-page__glance-value">
                                            {{ \Illuminate\Support\Str::title(str_replace('_', ' ', $tripView['skill_level'])) }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <div class="trip-offer-page__hero-side">
                    @foreach($topRightImages as $sideImage)
                        <div class="trip-offer-page__hero-side-item" data-trip-gallery-thumb>
                            <img src="{{ $sideImage }}" alt="{{ $tripView['title'] }}">
                        </div>
                    @endforeach
                </div>

                <div class="trip-offer-page__hero-bottom">
                    @foreach($bottomStripImages as $index => $thumb)
                        <div class="trip-offer-page__hero-bottom-item" data-trip-gallery-thumb>
                            <img src="{{ $thumb }}" alt="{{ $tripView['title'] }}">
                            @if($loop->last && $remainingGalleryCount > 0)
                                <div class="trip-offer-page__hero-bottom-more">
                                    +{{ $remainingGalleryCount }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="trip-offer-page__layout">
            <main class="trip-offer-page__main">
                <section class="trip-offer-page__about" id="about">
                        <h2 class="trip-offer-page__section-title">
                            {{ __('trips.description_title') }}
                    </h2>

                    <p class="trip-offer-page__about-intro">
                        {!! nl2br(e($tripView['description']['intro'] ?? '')) !!}
                    </p>

                    @if(!empty($tripView['description']['rest']))
                        <p class="trip-offer-page__about-rest" data-trip-description-rest>
                            {!! nl2br(e($tripView['description']['rest'])) !!}
                        </p>
                        <button type="button" class="trip-offer-page__see-more" data-trip-description-toggle>
                            <span data-label-more>{{ __('vacations.see_more') }}</span>
                            <span data-label-less class="d-none">{{ __('vacations.see_less') }}</span>
                        </button>
                    @endif

                    <div class="trip-offer-page__tags">
                        @foreach($tripView['target_species'] as $species)
                            <span class="trip-offer-page__tag">
                                {{ $species }}
                            </span>
                        @endforeach
                        @foreach($tripView['fishing_methods'] as $method)
                            <span class="trip-offer-page__tag trip-offer-page__tag--secondary">
                                {{ $method }}
                            </span>
                        @endforeach
                        @foreach($tripView['water_types'] as $water)
                            <span class="trip-offer-page__tag trip-offer-page__tag--muted">
                                {{ $water }}
                            </span>
                        @endforeach
                    </div>

                    <div class="trip-offer-page__tech-grid">
                        @if($tripView['fishing_style'])
                            <div class="trip-offer-page__tech-item">
                                <p class="trip-offer-page__tech-label">
                                    {{ __('trips.fishing_style') }}
                                </p>
                                <p class="trip-offer-page__tech-value">
                                    {{ $tripView['fishing_style'] }}
                                </p>
                            </div>
                        @endif
                        @if($tripView['duration']['days'] || $tripView['duration']['nights'])
                            <div class="trip-offer-page__tech-item">
                                <p class="trip-offer-page__tech-label">
                                    {{ __('trips.duration_days') }}/{{ __('trips.duration_nights') }}
                                </p>
                                <p class="trip-offer-page__tech-value">
                                    @if($tripView['duration']['days'])
                                        {{ $tripView['duration']['days'] }} {{ __('trips.duration_days') }}
                                    @endif
                                    @if($tripView['duration']['nights'])
                                        / {{ $tripView['duration']['nights'] }} {{ __('trips.duration_nights') }}
                                    @endif
                                </p>
                            </div>
                        @endif
                        @if($tripView['best_season']['from'] || $tripView['best_season']['to'])
                            <div class="trip-offer-page__tech-item">
                                <p class="trip-offer-page__tech-label">
                                    {{ __('trips.best_season') }}
                                </p>
                                <p class="trip-offer-page__tech-value">
                                    {{ $tripView['best_season']['from'] }} – {{ $tripView['best_season']['to'] }}
                                </p>
                            </div>
                        @endif
                    </div>
                </section>

                @if(!empty($tripView['trip_highlights']))
                    <section class="trip-offer-page__highlights" id="highlights">
                        <h2 class="trip-offer-page__section-title">
                            {{ __('trips.trip_highlights') }}
                        </h2>
                        <ul class="trip-offer-page__highlights-list">
                            @foreach($tripView['trip_highlights'] as $index => $highlight)
                                @php
                                    if (is_array($highlight)) {
                                        $label = $highlight['name'] ?? ($highlight['value'] ?? (isset($highlight[0]) ? $highlight[0] : json_encode($highlight)));
                                    } else {
                                        $label = is_scalar($highlight) ? $highlight : json_encode($highlight);
                                    }
                                @endphp
                                <li class="trip-offer-page__highlights-item">
                                    <span class="trip-offer-page__highlights-badge">
                                        {{ $loop->iteration }}
                                    </span>
                                    <p>{{ $label }}</p>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if(!empty($tripView['trip_schedule']))
                    <section class="trip-offer-page__itinerary" id="itinerary">
                        <h2 class="trip-offer-page__section-title">
                            {{ __('trips.trip_schedule') }}
                        </h2>

                        <div class="trip-offer-page__itinerary-grid">
                            @foreach($tripView['trip_schedule'] as $index => $day)
                                <article class="trip-offer-page__itinerary-item">
                                    <div class="trip-offer-page__itinerary-step">
                                        {{ $index + 1 }}
                                    </div>
                                    <div class="trip-offer-page__itinerary-body">
                                        <h3 class="trip-offer-page__itinerary-title">
                                            {{ $day['day_label'] ?? __('Day') . ' ' . ($index + 1) }}
                                        </h3>
                                        <p class="trip-offer-page__itinerary-text">
                                            {{ $day['description'] ?? '' }}
                                        </p>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section class="trip-offer-page__availability" id="availability">
                    <div class="trip-offer-page__availability-header">
                        <h2 class="trip-offer-page__section-title">
                            {{ __('trips.availability_title') }}
                        </h2>
                    </div>

                    <div class="trip-offer-page__availability-strip" data-trip-availability>
                        @foreach($availabilityCards as $index => $card)
                            @php
                                $classes = ['trip-offer-page__availability-card'];
                                if ($index === 0) {
                                    $classes[] = 'trip-offer-page__availability-card--selected';
                                } elseif ($card['is_limited']) {
                                    $classes[] = 'trip-offer-page__availability-card--limited';
                                }
                            @endphp
                            <div class="{{ implode(' ', $classes) }}">
                                <span class="trip-offer-page__availability-month">
                                    {{ $card['month'] }}
                                </span>
                                <span class="trip-offer-page__availability-day">
                                    {{ $card['day'] }}
                                </span>
                                <span class="trip-offer-page__availability-weekday">
                                    {{ $card['weekday'] }}
                                </span>
                                @if($card['is_limited'])
                                    <span class="trip-offer-page__availability-badge">
                                        {{ $card['spots_available'] }}
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <p class="trip-offer-page__availability-note">
                        <i class="fas fa-info-circle"></i>
                        {{ __('trips.cancellation_policy') }}
                    </p>
                </section>

                <section class="trip-offer-page__details-grid" id="details">
                    <div class="trip-offer-page__details-column">
                        @if(!empty($tripView['included']))
                            <div class="trip-offer-page__details-block">
                                <h3 class="trip-offer-page__details-title">
                                    {{ __('trips.included') }}
                                </h3>
                                <ul class="trip-offer-page__details-list">
                                    @foreach($tripView['included'] as $item)
                                        @php
                                            $label = is_array($item) && isset($item['name']) ? $item['name'] : $item;
                                        @endphp
                                        <li>
                                            <i class="fa fa-check"></i>
                                            <span>{{ $label }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if(!empty($tripView['excluded']))
                            <div class="trip-offer-page__details-block">
                                <h3 class="trip-offer-page__details-title">
                                    {{ __('trips.excluded') }}
                                </h3>
                                <ul class="trip-offer-page__details-list">
                                    @foreach($tripView['excluded'] as $item)
                                        @php
                                            $label = is_array($item) && isset($item['name']) ? $item['name'] : $item;
                                        @endphp
                                        <li>
                                            <i class="fa fa-times"></i>
                                            <span>{{ $label }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    <div class="trip-offer-page__details-column">
                        @if(!empty($tripView['additional_info']))
                            <div class="trip-offer-page__details-block">
                                <h3 class="trip-offer-page__details-title">
                                    {{ __('trips.additional_info_title') }}
                                </h3>
                                <ul class="trip-offer-page__details-list">
                                    @foreach($tripView['additional_info'] as $info)
                                        @php
                                            $label = is_array($info)
                                                ? ($info['name'] ?? ($info['value'] ?? (isset($info[0]) ? $info[0] : json_encode($info))))
                                                : $info;
                                        @endphp
                                        <li>
                                            <i class="fa fa-info-circle"></i>
                                            <span>{{ $label }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="trip-offer-page__details-block">
                            <h3 class="trip-offer-page__details-title">
                                {{ __('trips.cancellation_policy') }}
                            </h3>
                            <p class="trip-offer-page__details-text">
                                {{ $tripView['cancellation_policy'] ?: __('trips.cancellation_policy') }}
                            </p>
                        </div>
                    </div>
                </section>
            </main>

            <aside class="trip-offer-page__sidebar">
                <div class="trip-offer-page__booking-card">
                    <div class="trip-offer-page__booking-header">
                        <div class="trip-offer-page__booking-price-row">
                            <span class="trip-offer-page__booking-label">
                                {{ __('trips.price_per_person') }}
                            </span>
                            <div class="trip-offer-page__booking-price">
                                @if($tripView['price']['per_person'])
                                    <span class="trip-offer-page__booking-amount">
                                        €{{ number_format($tripView['price']['per_person'], 0) }}
                                    </span>
                                    <span class="trip-offer-page__booking-caption">
                                        {{ __('trips.price_per_person') }}
                                    </span>
                                @else
                                    <span class="trip-offer-page__booking-amount">
                                        {{ __('trips.pricing_title') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <span class="trip-offer-page__booking-badge">
                            Instant booking
                        </span>
                    </div>

                    <div class="trip-offer-page__booking-body">
                        <div class="trip-offer-page__booking-field-group">
                                <p class="trip-offer-page__booking-field-label">
                                    {{ __('trips.duration_days') }}/{{ __('trips.duration_nights') }}
                            </p>
                            <div class="trip-offer-page__duration-pill-row">
                                @if($tripView['duration']['days'])
                                    <button type="button" class="trip-offer-page__duration-pill trip-offer-page__duration-pill--active">
                                        {{ $tripView['duration']['days'] }} {{ __('trips.duration_days') }}
                                    </button>
                                @endif
                                @if($tripView['duration']['nights'])
                                    <button type="button" class="trip-offer-page__duration-pill">
                                        {{ $tripView['duration']['nights'] }} {{ __('trips.duration_nights') }}
                                    </button>
                                @endif
                            </div>
                        </div>

                        <div class="trip-offer-page__booking-field-group">
                                <p class="trip-offer-page__booking-field-label">
                                    Select date
                            </p>
                            <button type="button" class="trip-offer-page__booking-input trip-offer-page__booking-input--date">
                                    <span data-trip-selected-date>
                                    Choose date
                                </span>
                                <i class="far fa-calendar"></i>
                            </button>
                        </div>

                        <div class="trip-offer-page__booking-field-group">
                                <p class="trip-offer-page__booking-field-label">
                                    Guests
                            </p>
                            <div class="trip-offer-page__guest-stepper" data-trip-guests>
                                <button type="button" class="trip-offer-page__stepper-btn" data-trip-guests-minus>
                                    –
                                </button>
                                <span class="trip-offer-page__guest-label" data-trip-guests-label>
                                    2 {{ __('vacations.persons') }}
                                </span>
                                <button type="button" class="trip-offer-page__stepper-btn" data-trip-guests-plus>
                                    +
                                </button>
                            </div>
                        </div>

                            <button type="button" class="trip-offer-page__booking-cta">
                            Book now
                        </button>

                        <p class="trip-offer-page__booking-footnote">
                            Free cancellation up to 30 days before departure
                        </p>

                        @if(!empty($tripView['provider']['name']))
                            <div class="trip-offer-page__guide">
                                <div class="trip-offer-page__guide-meta">
                                    @if($tripView['provider']['photo'])
                                        <img
                                            src="{{ $tripView['provider']['photo'] }}"
                                            alt="{{ $tripView['provider']['name'] }}"
                                            class="trip-offer-page__guide-avatar"
                                        >
                                    @endif
                                    <div>
                                        <p class="trip-offer-page__guide-name">
                                            {{ $tripView['provider']['name'] }}
                                        </p>
                                        @if($tripView['provider']['experience'])
                                            <p class="trip-offer-page__guide-experience">
                                                {{ $tripView['provider']['experience'] }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                <button type="button" class="trip-offer-page__guide-contact">
                                    {{ __('trips.additional_info_title') }}
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </aside>
        </div>

        <div class="trip-offer-page__gallery-modal" data-trip-gallery-modal>
            <div class="trip-offer-page__gallery-modal-content">
                <button type="button" class="trip-offer-page__gallery-modal-close" data-trip-gallery-close>
                    &times;
                </button>
                <button type="button" class="trip-offer-page__gallery-modal-prev" data-trip-gallery-prev>
                    &#10094;
                </button>
                <img src="" alt="{{ $tripView['title'] }}" data-trip-gallery-modal-image>
                <button type="button" class="trip-offer-page__gallery-modal-next" data-trip-gallery-next>
                    &#10095;
                </button>
                <div class="trip-offer-page__gallery-modal-counter">
                    <span data-trip-gallery-current>1</span>
                    /
                    <span data-trip-gallery-total>{{ count($allImages) }}</span>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js_after')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const page = document.querySelector('.trip-offer-page');
            if (!page) {
                return;
            }

            const galleryRoot = page.querySelector('[data-trip-gallery]');
            const galleryImages = galleryRoot
                ? JSON.parse(galleryRoot.getAttribute('data-trip-gallery-images') || '[]')
                : [];
            const mainImage = galleryRoot ? galleryRoot.querySelector('[data-trip-gallery-image]') : null;
            const thumbs = Array.from(page.querySelectorAll('[data-trip-gallery-thumb] img'));
            const modal = page.querySelector('[data-trip-gallery-modal]');
            const modalImage = modal ? modal.querySelector('[data-trip-gallery-modal-image]') : null;
            const modalPrev = modal ? modal.querySelector('[data-trip-gallery-prev]') : null;
            const modalNext = modal ? modal.querySelector('[data-trip-gallery-next]') : null;
            const modalClose = modal ? modal.querySelector('[data-trip-gallery-close]') : null;
            const modalCurrent = modal ? modal.querySelector('[data-trip-gallery-current]') : null;
            const modalTotal = modal ? modal.querySelector('[data-trip-gallery-total]') : null;

            let currentIndex = 0;

            function updateGallery(index) {
                if (!galleryImages.length) {
                    return;
                }
                if (index < 0) {
                    index = galleryImages.length - 1;
                }
                if (index >= galleryImages.length) {
                    index = 0;
                }
                currentIndex = index;

                if (mainImage) {
                    mainImage.src = galleryImages[currentIndex];
                }
                if (modal && modalImage) {
                    modalImage.src = galleryImages[currentIndex];
                }
                if (modalCurrent) {
                    modalCurrent.textContent = currentIndex + 1;
                }
                if (modalTotal) {
                    modalTotal.textContent = galleryImages.length;
                }
            }

            if (mainImage && modal) {
                mainImage.addEventListener('click', function () {
                    if (!galleryImages.length) {
                        return;
                    }
                    modal.classList.add('trip-offer-page__gallery-modal--visible');
                    updateGallery(currentIndex);
                });
            }

            thumbs.forEach(function (thumb, index) {
                thumb.addEventListener('click', function () {
                    if (!galleryImages.length) {
                        return;
                    }
                    const imgIndex = index + 1;
                    updateGallery(imgIndex);
                    if (modal) {
                        modal.classList.add('trip-offer-page__gallery-modal--visible');
                    }
                });
            });

            if (modalPrev) {
                modalPrev.addEventListener('click', function () {
                    updateGallery(currentIndex - 1);
                });
            }

            if (modalNext) {
                modalNext.addEventListener('click', function () {
                    updateGallery(currentIndex + 1);
                });
            }

            if (modalClose) {
                modalClose.addEventListener('click', function () {
                    modal.classList.remove('trip-offer-page__gallery-modal--visible');
                });
            }

            if (modal) {
                modal.addEventListener('click', function (event) {
                    if (event.target === modal) {
                        modal.classList.remove('trip-offer-page__gallery-modal--visible');
                    }
                });
            }

            const descRest = page.querySelector('[data-trip-description-rest]');
            const descToggle = page.querySelector('[data-trip-description-toggle]');

            if (descRest && descToggle) {
                descRest.classList.add('trip-offer-page__about-rest--collapsed');

                descToggle.addEventListener('click', function () {
                    const isCollapsed = descRest.classList.contains('trip-offer-page__about-rest--collapsed');
                    const labelMore = descToggle.querySelector('[data-label-more]');
                    const labelLess = descToggle.querySelector('[data-label-less]');

                    if (isCollapsed) {
                        descRest.classList.remove('trip-offer-page__about-rest--collapsed');
                        if (labelMore) labelMore.classList.add('d-none');
                        if (labelLess) labelLess.classList.remove('d-none');
                    } else {
                        descRest.classList.add('trip-offer-page__about-rest--collapsed');
                        if (labelMore) labelMore.classList.remove('d-none');
                        if (labelLess) labelLess.classList.add('d-none');
                    }
                });
            }

            const guestRoot = page.querySelector('[data-trip-guests]');
            if (guestRoot) {
                const minusBtn = guestRoot.querySelector('[data-trip-guests-minus]');
                const plusBtn = guestRoot.querySelector('[data-trip-guests-plus]');
                const label = guestRoot.querySelector('[data-trip-guests-label]');
                let guests = 2;

                function updateGuests() {
                    if (!label) {
                        return;
                    }
                    const key = guests === 1 ? 'person' : 'persons';
                    label.textContent = guests + ' ' + key;
                }

                if (minusBtn) {
                    minusBtn.addEventListener('click', function () {
                        if (guests > 1) {
                            guests -= 1;
                            updateGuests();
                        }
                    });
                }

                if (plusBtn) {
                    plusBtn.addEventListener('click', function () {
                        guests += 1;
                        updateGuests();
                    });
                }

                updateGuests();
            }
        });
    </script>
@endsection

