@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous" />
@endpush

@section('title', $tripView['title'] ?? __('trips.page_title_fallback'))
@section('description', \Illuminate\Support\Str::limit(strip_tags($tripView['description']['full'] ?? ''), 155))

@section('share_tags')
    @if(!empty($canonicalUrl))
        <link rel="canonical" href="{{ $canonicalUrl }}">
    @endif
@endsection

@section('content')
    <div class="trip-offer-page" data-trip-duration-days="{{ $tripView['duration']['days'] ?? '' }}" data-analytics-page="trip-offer">
        {{-- Full-width top: heading, gallery (same width as before), feature cards. Floating card starts after this. --}}
        <div class="trip-offer-page__top">
            <div class="trip-offer-page__hero-heading">
                <h1 class="trip-offer-page__title">
                    {{ $tripView['title'] }}
                </h1>
                <div class="trip-offer-page__location-row">
                    <span class="trip-offer-page__location">
                        {{ implode(', ', array_filter([$tripView['city'] ?? null, $tripView['region'] ?? null, $tripView['country'] ?? null])) }}
                    </span>
                    <button type="button" class="trip-offer-page__map-link" data-trip-scroll-to-map>
                        <i class="fas fa-map-marker-alt"></i>
                        {{ __('vacations.show_on_map') }}
                    </button>
                </div>
            </div>

            <div class="trip-offer-page__gallery-container">
                <div class="camp-gallery">
                    <div class="camp-gallery__main" data-gallery-index="0">
                        @if($primaryImage)
                            <img src="{{ $primaryImage }}" alt="{{ __('trips.gallery_image_alt', ['title' => $tripView['title'] ?? '', 'num' => 1]) }}">
                        @endif
                    </div>
                    <div class="camp-gallery__right">
                        @foreach ($topRightImages as $index => $image)
                            <div class="camp-gallery__thumb" data-gallery-index="{{ $index + 1 }}">
                                <img src="{{ $image }}" alt="{{ __('trips.gallery_image_alt', ['title' => $tripView['title'] ?? '', 'num' => $index + 2]) }}">
                            </div>
                        @endforeach
                    </div>
                    <div class="camp-gallery__bottom">
                        @foreach ($bottomStripImages as $index => $image)
                            <div class="camp-gallery__thumb" data-gallery-index="{{ $index + 3 }}">
                                <img src="{{ $image }}" alt="{{ __('trips.gallery_image_alt', ['title' => $tripView['title'] ?? '', 'num' => $index + 4]) }}">
                                @if($loop->last && $remainingGalleryCount > 0)
                                    <div class="camp-gallery__more">+{{ $remainingGalleryCount }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                @php
                    $mobileCarouselImages = array_slice($galleryImages, 1);
                @endphp
                @if(count($mobileCarouselImages) > 0)
                <div class="camp-gallery__mobile-carousel">
                    <div class="camp-gallery__mobile-carousel-scroll">
                        @foreach($mobileCarouselImages as $index => $image)
                            <div class="camp-gallery__mobile-carousel-item" data-gallery-index="{{ $index + 1 }}">
                                <img src="{{ $image }}" alt="{{ __('trips.gallery_image_alt', ['title' => $tripView['title'] ?? '', 'num' => $index + 2]) }}">
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Feature cards: full-width row below gallery; two-column layout starts below --}}
            <div class="trip-offer-page__feature-cards">
                    @if(!empty($tripView['duration']['days']) || !empty($tripView['duration']['nights']))
                        <div class="trip-offer-page__feature-card">
                            <span class="trip-offer-page__feature-card-icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="28" height="28"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z"/></svg>
                            </span>
                            <p class="trip-offer-page__feature-card-label">{{ __('trips.duration') }}</p>
                            <p class="trip-offer-page__feature-card-value">
                                @if($tripView['duration']['days'])
                                    {{ $tripView['duration']['days'] }} {{ __('trips.duration_days') }}
                                @else
                                    {{ $tripView['duration']['nights'] }} {{ __('trips.duration_nights') }}
                                @endif
                            </p>
                        </div>
                    @endif
                    
                    @if(!empty($tripView['group_size']['min']) || !empty($tripView['group_size']['max']))
                        <div class="trip-offer-page__feature-card">
                            <span class="trip-offer-page__feature-card-icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="28" height="28"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                            </span>
                            <p class="trip-offer-page__feature-card-label">{{ __('trips.group_size') }}</p>
                            <p class="trip-offer-page__feature-card-value">
                                @if($tripView['group_size']['min'] && $tripView['group_size']['max'])
                                    {{ $tripView['group_size']['min'] }}–{{ $tripView['group_size']['max'] }} {{ __('trips.people') }}
                                @elseif($tripView['group_size']['max'])
                                    {{ __('trips.max_people', ['count' => $tripView['group_size']['max']]) }}
                                @else
                                    {{ $tripView['group_size']['min'] ?? $tripView['group_size']['max'] }} {{ __('trips.people') }}
                                @endif
                            </p>
                        </div>
                    @endif
                    @if(!empty($tripView['fishing_style']) || !empty($tripView['fishing_style_formatted']))
                        <div class="trip-offer-page__feature-card">
                            <span class="trip-offer-page__feature-card-icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" fill="currentColor" width="28" height="28" aria-hidden="true"><path d="m51.21838 43.9182a5.68276 5.68276 0 0 1 -2.27838-4.37182v-1.66669a4.62234 4.62234 0 0 0 2.43068-3.45823c-2.01356.001-4.83476-.00075-6.8482 0a4.54967 4.54967 0 0 0 2.42386 3.44954v1.67539a7.66021 7.66021 0 0 0 3.026 5.9279 3.25794 3.25794 0 1 1 -5.282 2.55234.99692.99692 0 0 0 -1.99364.00013 5.25213 5.25213 0 0 0 5.24647 5.24644c4.86883.01037 7.15596-6.33954 3.27521-9.355z"/><path d="m45.43954 11.58419a8.86532 8.86532 0 0 0 1.50522-1.47534v17.69367c-1.59018.71371-2.40655 2.41448-2.5619 4.62527 2.04745.00938 5.07471-.0067 7.12737 0-.10963-2.24287-1.14637-4.0571-2.57182-4.63528v-22.88706a.98582.98582 0 0 0 -.46849-.83736c-27.78892-1.81653-35.80408 33.24944-36.55334 40.95977a32.224 32.224 0 0 1 3.78761-.09984c1.80963-.08131 1.91194 1.40368 2.06333-1.02678 4.35279 5.04562 12.11647-.94038 8.21391-6.41956a12.1353 12.1353 0 0 0 -1.09643-12.43041 9.451 9.451 0 0 0 6.76849-3.38917 10.75521 10.75521 0 0 0 1.76434-7.74537c5.2433.57818 9.28044-.19937 12.02171-2.33254zm-21.02304 24.48206a18.19729 18.19729 0 0 1 -3.02038 4.52558.98454.98454 0 0 1 -.74765.34885 1.00617 1.00617 0 0 1 -.74758-1.65472 17.72778 17.72778 0 0 0 2.64159-3.88762 5.14477 5.14477 0 0 0 -3.27961.59813 48.28413 48.28413 0 0 1 3.788-10.1278 10.06311 10.06311 0 0 1 1.36563 10.19758zm6.53918-17.18525c-.97724 2.61817-3.58389 3.92259-6.35978 4.19656a38.34362 38.34362 0 0 1 6.88807-8.463 9.99966 9.99966 0 0 1 -.52829 4.26644zm3.65832-6.8582a35.77582 35.77582 0 0 1 12.12141-5.85136c-1.42892 5.13942-6.79504 6.31214-12.12141 5.85136z"/><path d="m15.70425 46.92164c.00327-.0144-2.92075 0-2.92075 0a1.96772 1.96772 0 0 0 -1.96372 1.96379v9.14087a1.96968 1.96968 0 0 0 1.96372 1.9737h2.92075a1.96968 1.96968 0 0 0 1.96375-1.9737v-9.14088a1.96841 1.96841 0 0 0 -1.96375-1.96378z"/></svg>
                            </span>
                            <p class="trip-offer-page__feature-card-label">{{ __('trips.fishing_type') }}</p>
                            <p class="trip-offer-page__feature-card-value">{{ $tripView['fishing_style_formatted'] ?? $tripView['fishing_style'] }}</p>
                        </div>
                    @endif
                    @if(!empty($tripView['target_species']))
                        <div class="trip-offer-page__feature-card">
                            <span class="trip-offer-page__feature-card-icon" aria-hidden="true">
                                <i class="fas fa-fish" aria-hidden="true"></i>
                            </span>
                            <p class="trip-offer-page__feature-card-label">{{ __('trips.target_species') }}</p>
                            <p class="trip-offer-page__feature-card-value trip-offer-page__feature-card-value--scrollable">{{ implode(', ', $tripView['target_species']) }}</p>
                        </div>
                    @endif
            </div>
        </div>

        {{-- Layout: main content left; sticky booking card + map right (aligned with About) --}}
        <div class="trip-offer-page__layout">
            <main class="trip-offer-page__main">
                <section class="trip-offer-page__about" id="about">
                    <div class="trip-offer-page__about-card">
                        <h2 class="trip-offer-page__section-title">
                            {{ __('trips.about_this_trip') }}
                        </h2>

                        @if(!empty($tripView['description']['full']))
                            <div class="trip-offer-page__about-description trip-offer-page__description-content" data-trip-description>
                                {!! $tripView['description']['full'] !!}
                            </div>
                            <button type="button" class="trip-offer-page__see-more d-none" data-trip-description-toggle aria-expanded="false">
                                <span data-label-more>{{ __('vacations.see_more') }}</span>
                                <span data-label-less class="d-none">{{ __('vacations.see_less') }}</span>
                            </button>
                        @endif

                    @if(!empty($tripView['target_species']))
                        <div class="trip-offer-page__about-block">
                            <p class="trip-offer-page__about-block-label">{{ __('trips.target_species_label') }}</p>
                            <div class="trip-offer-page__tags trip-offer-page__tags--species">
                                @foreach($tripView['target_species'] as $species)
                                    <span class="trip-offer-page__tag">
                                        <i class="fas fa-fish trip-offer-page__tag-icon" aria-hidden="true"></i>
                                        {{ $species }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if(!empty($tripView['fishing_methods']))
                        <div class="trip-offer-page__about-block">
                            <p class="trip-offer-page__about-block-label">{{ __('trips.fishing_methods_label') }}</p>
                            <div class="trip-offer-page__tags trip-offer-page__tags--methods">
                                @foreach($tripView['fishing_methods'] as $method)
                                    <span class="trip-offer-page__tag trip-offer-page__tag--secondary">
                                        {{ $method }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="trip-offer-page__about-grid">
                        <div class="trip-offer-page__about-grid-col">
                            @if(!empty($tripView['fishing_style']))
                                <div class="trip-offer-page__about-spec">
                                    <p class="trip-offer-page__about-spec-label">{{ __('trips.fishing_style') }}</p>
                                    <p class="trip-offer-page__about-spec-value">{{ $tripView['fishing_style_formatted'] ?? $tripView['fishing_style'] }}</p>
                                </div>
                            @endif
                            @if(!empty($tripView['best_season']['from']) || !empty($tripView['best_season']['to']))
                                <div class="trip-offer-page__about-spec">
                                    <p class="trip-offer-page__about-spec-label">{{ __('trips.best_season') }}</p>
                                    <p class="trip-offer-page__about-spec-value">{{ trim(($tripView['best_season_formatted']['from'] ?? $tripView['best_season']['from'] ?? '') . ' - ' . ($tripView['best_season_formatted']['to'] ?? $tripView['best_season']['to'] ?? ''), ' -') }}</p>
                                </div>
                            @endif
                            @if(!empty($tripView['skill_level']))
                                <div class="trip-offer-page__about-spec">
                                    <p class="trip-offer-page__about-spec-label">{{ __('trips.skill_level') }}</p>
                                    <p class="trip-offer-page__about-spec-value">{{ $tripView['skill_level_formatted'] ?? $tripView['skill_level'] }}</p>
                                </div>
                            @endif
                        </div>
                        <div class="trip-offer-page__about-grid-col">
                            @if(!empty($tripView['water_types']))
                                <div class="trip-offer-page__about-spec">
                                    <p class="trip-offer-page__about-spec-label">{{ __('trips.water_type_label') }}</p>
                                    <p class="trip-offer-page__about-spec-value">{{ implode(' / ', $tripView['water_types']) }}</p>
                                </div>
                            @endif
                            @if(!empty($tripView['catch_success_value']))
                                <div class="trip-offer-page__about-spec">
                                    <p class="trip-offer-page__about-spec-label">{{ __('trips.catch_success_label') }}</p>
                                    <p class="trip-offer-page__about-spec-value"><span class="trip-offer-page__about-spec-bullet">•</span> {{ $tripView['catch_success_value'] }}</p>
                                </div>
                            @endif
                            @if($tripView['catch_and_release_value'] !== null && $tripView['catch_and_release_value'] !== '')
                                <div class="trip-offer-page__about-spec">
                                    <p class="trip-offer-page__about-spec-label">{{ __('trips.catch_and_release_label') }}</p>
                                    <p class="trip-offer-page__about-spec-value">{{ $tripView['catch_and_release_value'] }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    </div>
                </section>

                @if(!empty($tripView['trip_highlights']))
                    <section class="trip-offer-page__highlights" id="highlights">
                        <h2 class="trip-offer-page__section-title">
                            {{ __('trips.trip_highlights') }}
                        </h2>
                        <ul class="trip-offer-page__highlights-list">
                            @foreach($tripView['trip_highlights'] as $highlight)
                                <li class="trip-offer-page__highlights-item">{{ $highlight }}</li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if(!empty($tripScheduleItems))
                    <section class="trip-offer-page__daily-schedule" id="itinerary">
                        <div class="trip-offer-page__daily-schedule-card">
                            <h2 class="trip-offer-page__section-title">
                                {{ __('trips.trip_schedule') }}
                            </h2>

                            <ul class="trip-offer-page__schedule-timeline">
                                @foreach($tripScheduleItems as $index => $item)
                                    @php
                                        $time = $item['time'] ?? null;
                                        $title = $item['day_label'] ?? __('trips.day') . ' ' . ($index + 1);
                                        $description = $item['description'] ?? '';
                                    @endphp
                                    <li class="trip-offer-page__schedule-item">
                                        <span class="trip-offer-page__schedule-dot" aria-hidden="true"></span>
                                        <div class="trip-offer-page__schedule-content">
                                            <p class="trip-offer-page__schedule-headline">
                                                @if($time)
                                                    <span class="trip-offer-page__schedule-time">{{ $time }}</span>
                                                    <span class="trip-offer-page__schedule-sep">—</span>
                                                @endif
                                                <span class="trip-offer-page__schedule-title">{{ $title }}</span>
                                            </p>
                                            @if($description !== '')
                                                <p class="trip-offer-page__schedule-description">{{ $description }}</p>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </section>
                @endif

                @if(!empty($includedItems) || !empty($excludedItems))
                    <section class="trip-offer-page__details-grid" id="details">
                        <div class="trip-offer-page__included-excluded-card">
                            <h2 class="trip-offer-page__section-title trip-offer-page__included-title">
                                {{ __('trips.whats_included_title') }}
                            </h2>

                            @if(!empty($includedItems))
                                <div class="trip-offer-page__included-list-wrap">
                                    <ul class="trip-offer-page__included-list">
                                        @foreach($includedItems as $item)
                                            <li class="trip-offer-page__included-item">
                                                <i class="fas fa-check trip-offer-page__included-icon" aria-hidden="true"></i>
                                                <span>{{ is_array($item) ? ($item['label'] ?? '') : $item }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if(!empty($includedItems) && !empty($excludedItems))
                                <hr class="trip-offer-page__included-excluded-divider">
                            @endif

                            @if(!empty($excludedItems))
                                <h3 class="trip-offer-page__excluded-title">{{ __('trips.not_included_title') }}</h3>
                                <div class="trip-offer-page__excluded-list-wrap">
                                    <ul class="trip-offer-page__excluded-list">
                                        @foreach($excludedItems as $item)
                                            <li class="trip-offer-page__excluded-item">
                                                <i class="fas fa-times trip-offer-page__excluded-icon" aria-hidden="true"></i>
                                                <span class="trip-offer-page__excluded-item-content">
                                                    <span class="trip-offer-page__excluded-label">{{ is_array($item) ? ($item['label'] ?? '') : $item }}</span>
                                                    @if(is_array($item) && !empty($item['subtext']))
                                                        <span class="trip-offer-page__excluded-subtext">{{ $item['subtext'] }}</span>
                                                    @endif
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </section>
                @endif

                {{-- Cards: Accommodation, Your Guide, Boat & Equipment (between What's Included and Additional Information) --}}
                @if($hasAccommodationContent)
                    <section class="trip-offer-page__card-group" id="accommodation-card">
                        <div class="trip-offer-page__card-group-list">
                            <div class="trip-offer-page__card-group-single">
                                {{-- Accommodation --}}
                                <div class="trip-offer-page__info-card-section trip-offer-page__info-card-section--last">
                                    <h2 class="trip-offer-page__info-card-title">{{ __('trips.accommodation_card_title') }}</h2>
                                    <div class="trip-offer-page__info-card-body">
                                        @if(!empty($acc['name']))
                                            <p class="trip-offer-page__info-card-name">{{ $acc['name'] }}</p>
                                        @endif
                                        @if(!empty($acc['description']))
                                            <p class="trip-offer-page__info-card-desc">{{ $acc['description'] }}</p>
                                        @endif
                                        @if(!empty($accRoomTypes))
                                            <div class="trip-offer-page__info-card-tags">
                                                @foreach($accRoomTypes as $rt)
                                                    <span class="trip-offer-page__info-card-tag trip-offer-page__info-card-tag--light">{{ $rt }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                        @if(!empty($accCatering))
                                            <div class="trip-offer-page__info-card-catering">
                                                <p class="trip-offer-page__info-card-label trip-offer-page__info-card-label--inline">{{ strtoupper(__('trips.catering')) }}</p>
                                                <div class="trip-offer-page__info-card-tags trip-offer-page__info-card-tags--catering">
                                                    @foreach($accCatering as $meal)
                                                        <span class="trip-offer-page__info-card-tag trip-offer-page__info-card-tag--meal"><i class="fas fa-utensils trip-offer-page__info-card-tag-icon" aria-hidden="true"></i>{{ $meal }}</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                        <div class="trip-offer-page__info-card-grid">
                                            @if(!empty($acc['distance_to_water']))
                                                <div class="trip-offer-page__info-card-item">
                                                    <p class="trip-offer-page__info-card-label">{{ strtoupper(__('trips.distance_to_water')) }}</p>
                                                    <p class="trip-offer-page__info-card-value">{{ $acc['distance_to_water'] }}</p>
                                                </div>
                                            @endif
                                            @if(!empty($acc['nearest_airport']))
                                                <div class="trip-offer-page__info-card-item">
                                                    <p class="trip-offer-page__info-card-label">{{ strtoupper(__('trips.nearest_airport')) }}</p>
                                                    <p class="trip-offer-page__info-card-value">{{ $acc['nearest_airport'] }}</p>
                                                </div>
                                            @endif
                                            @if(!empty($acc['arrival_day']) || !empty($acc['best_arrival_options']))
                                                <div class="trip-offer-page__info-card-item">
                                                    <p class="trip-offer-page__info-card-label">{{ strtoupper(__('trips.recommended_arrival')) }}</p>
                                                    <p class="trip-offer-page__info-card-value">{{ trim(($acc['arrival_day'] ?? '') . ($acc['best_arrival_options'] ? ', ' . $acc['best_arrival_options'] : ''), ' ,') }}</p>
                                                </div>
                                            @endif
                                        </div>
                                        @if(!empty($acc['meeting_point']))
                                            <div class="trip-offer-page__info-card-callout">
                                                <i class="fas fa-info-circle trip-offer-page__info-card-callout-icon" aria-hidden="true"></i>
                                                <span>{{ $acc['meeting_point'] }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                @endif

                {{-- Pricing Details card --}}
                <section class="trip-offer-page__pricing-details" id="pricing-details">
                    <h2 class="trip-offer-page__section-title trip-offer-page__pricing-details-title">
                        {{ __('trips.pricing_details_title') }}
                    </h2>

                    <div class="trip-offer-page__pricing-main-card">
                        <p class="trip-offer-page__pricing-main-label">{{ __('trips.price_per_person') }}</p>
                        @if($tripView['price']['per_person'])
                            @php $curr = $tripView['price']['currency'] ?? 'EUR'; $sym = $curr === 'EUR' ? '€' : $curr; @endphp
                            <p class="trip-offer-page__pricing-main-amount">{{ $sym }}{{ number_format($tripView['price']['per_person'], 0) }}</p>
                            @if(($tripView['duration']['nights'] ?? null) || ($tripView['duration']['days'] ?? null))
                                <p class="trip-offer-page__pricing-main-inclusions">
                                    {{ __('trips.vat_incl_nights', ['nights' => $tripView['duration']['nights'] ?? '—', 'days' => $tripView['duration']['days'] ?? '—']) }}
                                </p>
                            @endif
                        @else
                            <p class="trip-offer-page__pricing-main-amount">{{ __('trips.pricing_title') }}</p>
                        @endif
                    </div>

                    <div class="trip-offer-page__pricing-grid">
                        <div class="trip-offer-page__pricing-grid-card">
                            <p class="trip-offer-page__pricing-grid-label">{{ strtoupper(__('trips.single_supplement')) }}</p>
                            <p class="trip-offer-page__pricing-grid-value">
                                @if(!empty($tripView['price']['single_room_addition']))
                                    @php $sym = ($tripView['price']['currency'] ?? 'EUR') === 'EUR' ? '€' : ''; @endphp
                                    +{{ $sym }}{{ number_format($tripView['price']['single_room_addition'], 0) }}
                                @else
                                    —
                                @endif
                            </p>
                        </div>
                        @if(!empty($tripView['downpayment_policy']))
                            <div class="trip-offer-page__pricing-grid-card">
                                <p class="trip-offer-page__pricing-grid-label">{{ strtoupper(__('trips.deposit_at_booking')) }}</p>
                                <p class="trip-offer-page__pricing-grid-value">
                                    {{ $tripView['downpayment_policy'] }}
                                </p>
                            </div>
                        @endif
                    </div>

                    @if(!empty($tripView['cancellation_policy']))
                    <div class="trip-offer-page__pricing-cancellation-card">
                        <i class="fas fa-shield-alt trip-offer-page__pricing-cancellation-icon" aria-hidden="true"></i>
                        <div class="trip-offer-page__pricing-cancellation-content">
                            <p class="trip-offer-page__pricing-cancellation-title">{{ __('trips.cancellation_policy') }}</p>
                            <p class="trip-offer-page__pricing-cancellation-text">
                                {{ $tripView['cancellation_policy'] }}
                            </p>
                        </div>
                    </div>
                    @endif
                </section>

                {{-- Guide and Boat card --}}
                @if($hasGuideContent || $hasBoatContent)
                    <section class="trip-offer-page__card-group" id="guide-boat-card">
                        <div class="trip-offer-page__card-group-list">
                            <div class="trip-offer-page__card-group-single">
                            {{-- Your Guide (target: avatar left, name + experience right, then light-blue certification tags) --}}
                            @if($hasGuideContent)
                                <div class="trip-offer-page__info-card-section">
                                    <h2 class="trip-offer-page__info-card-title">{{ __('trips.your_guide_title') }}</h2>
                                    <div class="trip-offer-page__info-card-body trip-offer-page__info-card-body--guide">
                                        <div class="trip-offer-page__guide-two-col">
                                            <div class="trip-offer-page__guide-left">
                                                <div class="trip-offer-page__guide-row">
                                                    @if(!empty($prov['photo']))
                                                        <img src="{{ media_url($prov['photo'] ?? null) }}" alt="{{ $prov['name'] ?? '' }}" class="trip-offer-page__guide-avatar-img">
                                                    @else
                                                        <div class="trip-offer-page__guide-avatar-initials">
                                                            {{ strtoupper(mb_substr(($prov['name'] ?? '?'), 0, 2)) }}
                                                        </div>
                                                    @endif
                                                    <div class="trip-offer-page__guide-meta">
                                                        @if(!empty($prov['name']))
                                                            <p class="trip-offer-page__info-card-name">{{ $prov['name'] }}</p>
                                                        @endif
                                                        @if(!empty($prov['experience']))
                                                            <p class="trip-offer-page__info-card-desc">{{ $prov['experience'] }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                                @if(!empty($provCertifications))
                                                    <div class="trip-offer-page__info-card-tags">
                                                        @foreach($provCertifications as $cert)
                                                            <span class="trip-offer-page__info-card-tag trip-offer-page__info-card-tag--light">{{ $cert }}</span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                            @if(!empty($provGuideLanguages))
                                                @php
                                                    $languagesWithFlags = getLanguagesWithFlags(implode(',', $provGuideLanguages));
                                                @endphp
                                                @if(!empty($languagesWithFlags))
                                                    <div class="trip-offer-page__guide-languages-col">
                                                        <p class="trip-offer-page__info-card-label trip-offer-page__info-card-label--inline">{{ strtoupper(__('trips.guide_languages')) }}</p>
                                                        <div class="trip-offer-page__info-card-tags trip-offer-page__info-card-tags--languages trip-offer-page__info-card-tags--flags">
                                                            @foreach($languagesWithFlags as $language)
                                                                @if($language['has_flag'])
                                                                    <span class="trip-offer-page__language-flag" title="{{ $language['name'] }}">
                                                                        <img src="{{ asset('flags/' . $language['flag_code'] . '.svg') }}" alt="{{ $language['name'] }}" width="24" height="24">
                                                                    </span>
                                                                @else
                                                                    <span class="trip-offer-page__info-card-tag trip-offer-page__info-card-tag--lang">{{ $language['name'] }}</span>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            {{-- Boat & Equipment --}}
                            @if($hasBoatContent)
                                <div class="trip-offer-page__info-card-section trip-offer-page__info-card-section--last">
                                    <h2 class="trip-offer-page__info-card-title">{{ __('trips.boat_equipment_title') }}</h2>
                                    <div class="trip-offer-page__info-card-body">
                                        @if(!empty($boat['boat_type']))
                                            <div class="trip-offer-page__info-card-specs">
                                                <div class="trip-offer-page__info-card-item">
                                                    <p class="trip-offer-page__info-card-label">{{ strtoupper(__('trips.boat_type_label')) }}</p>
                                                    <p class="trip-offer-page__info-card-value">{{ $boat['boat_type'] }}</p>
                                                </div>
                                            </div>
                                        @endif
                                        @if(!empty($boat['boat_staff']))
                                            <div class="trip-offer-page__info-card-boat-staff">
                                                <p class="trip-offer-page__info-card-label">{{ strtoupper(__('trips.boat_staff')) }}</p>
                                                <p class="trip-offer-page__info-card-value"><i class="fas fa-users trip-offer-page__info-card-boat-staff-icon" aria-hidden="true"></i>{{ $boat['boat_staff'] }}</p>
                                            </div>
                                        @endif
                                        @if(!empty($boatFeatures))
                                            <div class="trip-offer-page__info-card-tags">
                                                @foreach($boatFeatures as $feat)
                                                    <span class="trip-offer-page__info-card-tag trip-offer-page__info-card-tag--light">{{ $feat }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                        @if(!empty($boat['boat_information']))
                                            <p class="trip-offer-page__info-card-desc trip-offer-page__info-card-desc--mt">{{ $boat['boat_information'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            </div>
                        </div>
                    </section>
                @endif

                @if(!empty($additionalInfoItems))
                    <section class="trip-offer-page__additional-info-section" id="additional-info">
                        <div class="trip-offer-page__additional-info-card">
                            <h2 class="trip-offer-page__section-title trip-offer-page__additional-info-title">
                                {{ __('trips.additional_info_title') }}
                            </h2>

                            @php
                                $gridItems = array_filter($additionalInfoItems, fn ($i) => !($i['full_width'] ?? false));
                                $fullWidthItems = array_filter($additionalInfoItems, fn ($i) => $i['full_width'] ?? false);
                            @endphp

                            @if(!empty($gridItems))
                                <div class="trip-offer-page__additional-info-grid">
                                    @foreach($gridItems as $item)
                                        <div class="trip-offer-page__additional-info-item">
                                            <p class="trip-offer-page__additional-info-label">{{ strtoupper($item['label']) }}</p>
                                            <p class="trip-offer-page__additional-info-value">{{ $item['value'] }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if(!empty($fullWidthItems))
                                <div class="trip-offer-page__additional-info-full-list">
                                    @foreach($fullWidthItems as $item)
                                        <div class="trip-offer-page__additional-info-full-item">
                                            <p class="trip-offer-page__additional-info-label">{{ strtoupper($item['label']) }}</p>
                                            <p class="trip-offer-page__additional-info-value trip-offer-page__additional-info-value--long">{{ $item['value'] }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </section>
                @endif

                @if(!empty($nonFishingActivities))
                    <section class="trip-offer-page__non-fishing-section" id="non-fishing-activities">
                        <div class="trip-offer-page__non-fishing-card">
                            <h2 class="trip-offer-page__section-title trip-offer-page__non-fishing-title">
                                {{ __('trips.non_fishing_activities') }}
                            </h2>
                            <div class="trip-offer-page__non-fishing-tags">
                                @foreach($nonFishingActivities as $activity)
                                    <span class="trip-offer-page__non-fishing-tag">
                                        <i class="fas fa-umbrella-beach trip-offer-page__non-fishing-tag-icon" aria-hidden="true"></i>
                                        {{ $activity }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </section>
                @endif

                <div class="d-lg-none">
                    @include('pages.trips.partials.contact-card')
                </div>

            </main>

            <aside class="trip-offer-page__sidebar">
                @php
                    $minBookingDate = now()->toDateString();
                    $initialBookingDate = !empty($selectedDate) ? $selectedDate : '';
                @endphp
                <div class="trip-offer-page__booking-card">
                    <div class="trip-offer-page__booking-header">
                        <div class="trip-offer-page__booking-header-grid">
                            <div class="trip-offer-page__booking-price-block">
                                <div class="trip-offer-page__booking-title-row">
                                    <span class="trip-offer-page__booking-label">
                                        {{ __('trips.price_per_person_short') }}
                                    </span>
                                </div>
                                <div class="trip-offer-page__booking-price">
                                    @if($tripView['price']['per_person'])
                                        <span class="trip-offer-page__booking-amount">
                                            € {{ number_format($tripView['price']['per_person'], 0) }}
                                        </span>
                                    @else
                                        <span class="trip-offer-page__booking-amount">
                                            {{ __('trips.pricing_title') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="trip-offer-page__booking-qty-block">
                                <p class="trip-offer-page__booking-field-label">
                                    {{ __('trips.guests_label') }}
                                </p>
                                <div class="trip-offer-page__guest-stepper trip-offer-page__guest-stepper--subtle" data-trip-guests>
                                    <button type="button" class="trip-offer-page__stepper-btn trip-offer-page__stepper-btn--minus trip-offer-page__stepper-btn--subtle" data-trip-guests-minus aria-label="{{ __('trips.decrease_guests') }}">
                                        –
                                    </button>
                                    <span class="trip-offer-page__guest-label trip-offer-page__guest-label--number" data-trip-guests-label>1</span>
                                    <button type="button" class="trip-offer-page__stepper-btn trip-offer-page__stepper-btn--plus trip-offer-page__stepper-btn--subtle" data-trip-guests-plus aria-label="{{ __('trips.increase_guests') }}">
                                        +
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="trip-offer-page__booking-body">
                        <div class="trip-offer-page__booking-field-group">
                            <p class="trip-offer-page__booking-field-label">
                                {{ __('trips.select_date') }}
                            </p>
                            <input
                                type="date"
                                class="trip-offer-page__booking-date-input"
                                name="departure_date"
                                data-trip-selected-date
                                min="{{ $minBookingDate }}"
                                value="{{ $initialBookingDate }}"
                                aria-label="{{ __('trips.select_date') }}"
                                required>
                        </div>

                        <button type="button" class="trip-offer-page__booking-cta" data-analytics-trip-inquiry>
                            {{ __('vacations.request_trip') }}
                        </button>

                        @if(!empty($tripView['cancellation_policy']))
                        <p class="trip-offer-page__booking-footnote">
                            {{ $tripView['cancellation_policy'] }}
                        </p>
                        @endif
                    </div>
                </div>

                <div class="d-none d-lg-block">
                    @include('pages.trips.partials.contact-card')
                </div>

                @if(isset($tripView['coordinates']['lat']) && isset($tripView['coordinates']['lng']) && is_numeric($tripView['coordinates']['lat']) && is_numeric($tripView['coordinates']['lng']))
                    <div class="trip-offer-page__map-wrap">
                        <p class="trip-offer-page__map-label">{{ __('trips.location_on_map') }}</p>
                        <div id="tripOfferMap" class="trip-offer-page__map" data-lat="{{ $tripView['coordinates']['lat'] }}" data-lng="{{ $tripView['coordinates']['lng'] }}" data-title="{{ e($tripView['title'] ?? '') }}" aria-hidden="true"></div>
                    </div>
                @endif
            </aside>
        </div>

        @include('pages.trips.partials.reviews', ['reviewTrust' => $reviewTrust ?? null])

        <!-- Mobile Sticky Request Card (replaces the floating booking card) -->
        <div class="trip-offer-page__mobile-sticky-footer" role="region" aria-label="{{ __('vacations.request_trip') }}">
            <div class="trip-offer-page__mobile-sticky-inner">
                <div class="trip-offer-page__mobile-sticky-simple">
                    <div class="trip-offer-page__mobile-sticky-simple-left">
                        <div class="trip-offer-page__mobile-sticky-simple-price-row">
                            <span class="trip-offer-page__mobile-sticky-simple-amount">
                                @if($tripView['price']['per_person'])
                                    €{{ number_format($tripView['price']['per_person'], 0) }}
                                @else
                                    —
                                @endif
                            </span>
                            <span class="trip-offer-page__mobile-sticky-simple-unit">
                                {{ __('trips.per_person_suffix') }}
                            </span>
                        </div>
                    </div>

                    <button type="button" class="trip-offer-page__mobile-sticky-simple-cta trip-offer-page__booking-cta" data-analytics-trip-inquiry>
                        <span class="trip-offer-page__mobile-sticky-simple-cta-text">{{ __('vacations.request_trip') }}</span>
                        <span class="trip-offer-page__mobile-sticky-simple-cta-arrow" aria-hidden="true">→</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Gallery Modal (matches Camp offer page) -->
        <div id="galleryModal" class="gallery-modal">
            <div class="gallery-modal__content">
                <button type="button" class="gallery-modal__close" aria-label="{{ __('cookie.close-btn') }}">&times;</button>
                <button type="button" class="gallery-modal__prev" aria-label="{{ __('vacations.previous') }}">&#10094;</button>
                <button type="button" class="gallery-modal__next" aria-label="{{ __('vacations.next') }}">&#10095;</button>
                <img id="galleryModalImage" src="" alt="{{ __('trips.gallery_image_alt', ['title' => $tripView['title'] ?? '', 'num' => 1]) }}">
                <div class="gallery-modal__counter">
                    <span id="galleryCurrentIndex">1</span> / <span id="galleryTotalCount">{{ count($galleryImages) }}</span>
                </div>
            </div>
        </div>

        <!-- Contact Modal (Trips) -->
        <div class="modal fade" id="tripContactModal" tabindex="-1" aria-labelledby="tripContactModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="tripContactModalLabel">{{ $contactModalTitle ?? __('contact.shareYourQuestion') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('cookie.close-btn') }}"></button>
                    </div>
                    <div class="modal-body">
                        {{-- reCAPTCHA script is rendered by the component --}}
                        <div id="tripContactFormContainer">
                            <form id="tripContactModalForm">
                                @csrf
                                <input type="hidden" name="source_type" value="trip">
                                <input type="hidden" name="source_id" value="{{ $tripView['id'] ?? '' }}">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <input type="text" class="form-control" placeholder="{{ __('contact.yourName') }}" name="name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <input type="email" class="form-control" placeholder="{{ __('contact.email') }}" name="email" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    @include('includes.forms.phone-input', [
                                        'placeholder' => 'contact.phone',
                                        'required' => true,
                                        'showLabel' => true,
                                        'labelText' => 'contact.phone'
                                    ])
                                </div>

                                <div class="row g-3 mb-3 align-items-end">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="trip_preferred_date" class="form-label">{{ __('trips.select_date') }}</label>
                                            <input
                                                type="date"
                                                class="form-control"
                                                id="trip_preferred_date"
                                                name="preferred_date"
                                                min="{{ $minBookingDate }}"
                                                value="{{ $initialBookingDate }}"
                                                data-trip-selected-date
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="trip_number_of_persons" class="form-label">{{ __('trips.guests_label') }}</label>
                                            <input type="number" class="form-control" id="trip_number_of_persons" name="number_of_persons" min="1" step="1" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <textarea name="description" class="form-control" rows="4" placeholder="{{ __('contact.feedback') }}" required></textarea>
                                </div>

                                <div class="trip-contact-submit-row d-flex justify-content-between align-items-center flex-wrap gap-3">
                                    <div class="trip-contact-captcha-wrap">
                                        <x-recaptcha />
                                    </div>
                                    <div class="trip-contact-submit-wrap">
                                        <button type="button" id="tripContactSubmitBtn" class="btn btn-orange">
                                            {{ __('contact.btnSend') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div id="tripContactLoadingOverlay" style="display: none;">
                            <div class="d-flex justify-content-center align-items-center flex-column p-4">
                                <div class="spinner-border text-orange mb-3" role="status">
                                    <span class="visually-hidden">{{ __('vacations.loading') }}</span>
                                </div>
                                <p class="text-center">{{ __('contact.submitting') }}</p>
                            </div>
                        </div>

                        <div class="alert alert-success mt-3" id="tripContactSuccessMessage" style="display: none;">
                            {{ __('contact.successMessage') }}
                        </div>
                        <div class="alert alert-danger mt-3" id="tripContactError" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- General Contact Modal (Trips) -->
        <div class="modal fade" id="tripGeneralContactModal" tabindex="-1" aria-labelledby="tripGeneralContactModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="tripGeneralContactModalLabel">{{ __('contact.shareYourQuestion') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('cookie.close-btn') }}"></button>
                    </div>
                    <div class="modal-body">
                        <div id="tripGeneralContactFormContainer">
                            <form id="tripGeneralContactModalForm">
                                @csrf
                                <input type="hidden" name="source_type" value="trip">
                                <input type="hidden" name="source_id" value="{{ $tripView['id'] ?? '' }}">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <input type="text" class="form-control" placeholder="{{ __('contact.yourName') }}" name="name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <input type="email" class="form-control" placeholder="{{ __('contact.email') }}" name="email" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    @include('includes.forms.phone-input', [
                                        'placeholder' => 'contact.phone',
                                        'required' => true,
                                        'showLabel' => true,
                                        'labelText' => 'contact.phone'
                                    ])
                                </div>

                                <div class="form-group mb-3">
                                    <textarea name="description" class="form-control" rows="4" placeholder="{{ __('contact.feedback') }}" required></textarea>
                                </div>

                                <div class="trip-contact-submit-row d-flex justify-content-between align-items-center flex-wrap gap-3">
                                    <div class="trip-contact-captcha-wrap">
                                        <x-recaptcha />
                                    </div>
                                    <div class="trip-contact-submit-wrap">
                                        <button type="button" id="tripGeneralContactSubmitBtn" class="btn btn-orange">
                                            {{ __('contact.btnSend') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div id="tripGeneralContactLoadingOverlay" style="display: none;">
                            <div class="d-flex justify-content-center align-items-center flex-column p-4">
                                <div class="spinner-border text-orange mb-3" role="status">
                                    <span class="visually-hidden">{{ __('vacations.loading') }}</span>
                                </div>
                                <p class="text-center">{{ __('contact.submitting') }}</p>
                            </div>
                        </div>

                        <div class="alert alert-success mt-3" id="tripGeneralContactSuccessMessage" style="display: none;">
                            {{ __('contact.successMessage') }}
                        </div>
                        <div class="alert alert-danger mt-3" id="tripGeneralContactError" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js_after')
    <script type="application/json" id="trip-offer-data">{!! json_encode($tripOfferData ?? ['gallery' => [], 'map' => null], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) !!}</script>
    <script>
        var tripOfferPageI18n = @json([
            'mapDefaultTitle' => __('trips.map_marker_fallback'),
            'contactError' => __('contact.errorMessage'),
        ]);
        document.addEventListener('DOMContentLoaded', function () {
            const page = document.querySelector('.trip-offer-page');
            if (!page) return;

            var tripOfferData = { gallery: [], map: null };
            try {
                var dataEl = document.getElementById('trip-offer-data');
                if (dataEl && dataEl.textContent) tripOfferData = JSON.parse(dataEl.textContent);
            } catch (e) {}

            // Gallery modal (matches Camp offer page)
            (function() {
                const galleryImages = tripOfferData.gallery || [];
                let currentGalleryIndex = 0;
                const modal = document.getElementById('galleryModal');

                function openGalleryModal(index) {
                    currentGalleryIndex = index;
                    updateGalleryModal();
                    if (modal) {
                        modal.style.display = 'flex';
                        document.body.style.overflow = 'hidden';
                    }
                }

                function closeGalleryModal() {
                    if (modal) {
                        modal.style.display = 'none';
                    }
                    document.body.style.overflow = 'auto';
                }

                function changeGalleryImage(direction) {
                    currentGalleryIndex += direction;
                    if (currentGalleryIndex < 0) currentGalleryIndex = galleryImages.length - 1;
                    if (currentGalleryIndex >= galleryImages.length) currentGalleryIndex = 0;
                    updateGalleryModal();
                }

                function updateGalleryModal() {
                    const imgEl = document.getElementById('galleryModalImage');
                    const idxEl = document.getElementById('galleryCurrentIndex');
                    if (imgEl && galleryImages[currentGalleryIndex]) {
                        imgEl.src = galleryImages[currentGalleryIndex];
                    }
                    if (idxEl) {
                        idxEl.textContent = currentGalleryIndex + 1;
                    }
                }

                const galleryItems = page.querySelectorAll('[data-gallery-index]');
                galleryItems.forEach(function(item) {
                    item.addEventListener('click', function() {
                        const index = parseInt(this.getAttribute('data-gallery-index'), 10);
                        if (!isNaN(index) && index >= 0 && index < galleryImages.length) {
                            openGalleryModal(index);
                        }
                    });
                });

                const closeBtn = page.querySelector('.gallery-modal__close');
                if (closeBtn) {
                    closeBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        closeGalleryModal();
                    });
                }

                if (modal) {
                    modal.addEventListener('click', function(e) {
                        if (e.target.id === 'galleryModal') {
                            closeGalleryModal();
                        }
                    });
                }

                const prevBtn = page.querySelector('.gallery-modal__prev');
                const nextBtn = page.querySelector('.gallery-modal__next');
                if (prevBtn) {
                    prevBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        changeGalleryImage(-1);
                    });
                }
                if (nextBtn) {
                    nextBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        changeGalleryImage(1);
                    });
                }

                document.addEventListener('keydown', function(event) {
                    if (modal && modal.style.display === 'flex') {
                        if (event.key === 'Escape') {
                            closeGalleryModal();
                        } else if (event.key === 'ArrowLeft') {
                            changeGalleryImage(-1);
                        } else if (event.key === 'ArrowRight') {
                            changeGalleryImage(1);
                        }
                    }
                });
            })();

            const descEl = page.querySelector('[data-trip-description]');
            const descToggle = page.querySelector('[data-trip-description-toggle]');

            // Header "Show on map" -> smooth scroll to map section on all viewports.
            (function () {
                const mapTrigger = page.querySelector('[data-trip-scroll-to-map]');
                if (!mapTrigger) return;

                mapTrigger.addEventListener('click', function () {
                    const mapEl = document.getElementById('tripOfferMap');
                    if (!mapEl) return;

                    const mapSection = mapEl.closest('.trip-offer-page__map-wrap') || mapEl;
                    mapSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            })();

            if (descEl && descToggle) {
                const originalHtml = descEl.innerHTML.trim();
                const plainText = descEl.textContent.replace(/\s+/g, ' ').trim();
                const words = plainText.split(/\s+/).filter(Boolean);
                const wordLimit = 30;

                if (words.length > wordLimit) {
                    const truncatedText = words.slice(0, wordLimit).join(' ') + '...';
                    descEl.textContent = truncatedText;
                    descToggle.classList.remove('d-none');

                    descToggle.addEventListener('click', function () {
                        const isExpanded = descToggle.getAttribute('aria-expanded') === 'true';
                        const labelMore = descToggle.querySelector('[data-label-more]');
                        const labelLess = descToggle.querySelector('[data-label-less]');

                        if (isExpanded) {
                            descEl.textContent = truncatedText;
                            descToggle.setAttribute('aria-expanded', 'false');
                            if (labelMore) labelMore.classList.remove('d-none');
                            if (labelLess) labelLess.classList.add('d-none');
                        } else {
                            descEl.innerHTML = originalHtml;
                            descToggle.setAttribute('aria-expanded', 'true');
                            if (labelMore) labelMore.classList.add('d-none');
                            if (labelLess) labelLess.classList.remove('d-none');
                        }
                    });
                }
            }

            const guestRoots = page.querySelectorAll('[data-trip-guests]');
            if (guestRoots && guestRoots.length > 0) {
                // Shared guest count so all guest controls stay in sync.
                let guests = parseInt(page.dataset.tripGuests || '1', 10) || 1;

                function clampGuests(value) {
                    const parsed = parseInt(value, 10);
                    if (!Number.isFinite(parsed) || parsed < 1) return 1;
                    if (parsed > 20) return 20;
                    return parsed;
                }

                function updateAllGuests() {
                    guestRoots.forEach((guestRoot) => {
                        if (!guestRoot) return;
                        const label = guestRoot.querySelector('[data-trip-guests-label]');
                        if (label) label.textContent = String(guests);
                    });
                    page.dataset.tripGuests = String(guests);
                }

                guestRoots.forEach((guestRoot) => {
                    const minusBtn = guestRoot.querySelector('[data-trip-guests-minus]');
                    const plusBtn = guestRoot.querySelector('[data-trip-guests-plus]');

                    if (minusBtn) {
                        minusBtn.addEventListener('click', function () {
                            guests = clampGuests(guests - 1);
                            updateAllGuests();
                        });
                    }

                    if (plusBtn) {
                        plusBtn.addEventListener('click', function () {
                            guests = clampGuests(guests + 1);
                            updateAllGuests();
                        });
                    }
                });

                updateAllGuests();
            }

            // Date picker sync (desktop booking card + contact modal)
            (function() {
                const dateInputs = page.querySelectorAll('[data-trip-selected-date]');
                if (!dateInputs || dateInputs.length < 2) return;

                dateInputs.forEach((inputEl) => {
                    if (!inputEl) return;
                    inputEl.addEventListener('change', function () {
                        const value = inputEl.value || '';
                        dateInputs.forEach((otherEl) => {
                            if (otherEl && otherEl !== inputEl) {
                                otherEl.value = value;
                            }
                        });
                    });
                });
            })();

            // Floating card "Request now" -> open contact modal and prefill
            (function() {
                const ctas = page.querySelectorAll('.trip-offer-page__booking-cta');
                const modalEl = document.getElementById('tripContactModal');
                if (!ctas || ctas.length === 0 || !modalEl || typeof bootstrap === 'undefined' || !bootstrap.Modal) return;

                const preferredDateInput = document.getElementById('trip_preferred_date');
                const personsInput = document.getElementById('trip_number_of_persons');
                const descInput = modalEl.querySelector('textarea[name="description"]');

                function getModalInstance(el) {
                    // Supports Bootstrap 5 (getInstance / getOrCreateInstance) and older builds (constructor)
                    const Modal = bootstrap.Modal;
                    if (Modal.getInstance) {
                        return Modal.getInstance(el) || (Modal.getOrCreateInstance ? Modal.getOrCreateInstance(el) : new Modal(el));
                    }
                    return new Modal(el);
                }

                function prefillContactForm() {
                    const dateInputs = page.querySelectorAll('[data-trip-selected-date]');
                    const selectedDate = Array.from(dateInputs || [])
                        .map((input) => (input && input.value ? input.value : ''))
                        .filter(Boolean)[0] || '';
                    const guests = parseInt(page.dataset.tripGuests || '2', 10) || 2;

                    if (personsInput) personsInput.value = String(guests);
                    if (preferredDateInput && selectedDate) preferredDateInput.value = selectedDate;
                }

                ctas.forEach((cta) => {
                    cta.addEventListener('click', function () {
                        const bookingDateInput = page.querySelector('.trip-offer-page__booking-date-input[data-trip-selected-date]');
                        if (bookingDateInput && !bookingDateInput.value) {
                            bookingDateInput.reportValidity();
                            bookingDateInput.focus();
                            return;
                        }

                        prefillContactForm();
                        const modal = getModalInstance(modalEl);
                        modal.show();
                    });
                });

                modalEl.addEventListener('shown.bs.modal', function () {
                    prefillContactForm();
                });
            })();

            // Trips contact modal submission (AJAX)
            (function() {
                const submitBtn = document.getElementById('tripContactSubmitBtn');
                const contactForm = document.getElementById('tripContactModalForm');
                if (!submitBtn || !contactForm) return;

                const formContainer = document.getElementById('tripContactFormContainer');
                const loadingOverlay = document.getElementById('tripContactLoadingOverlay');
                const successMessage = document.getElementById('tripContactSuccessMessage');
                const contactError = document.getElementById('tripContactError');

                function setVisible(el, visible) {
                    if (!el) return;
                    el.style.display = visible ? 'block' : 'none';
                }

                async function handleSubmit() {
                    setVisible(contactError, false);
                    setVisible(successMessage, false);

                    if (!contactForm.checkValidity()) {
                        contactForm.reportValidity();
                        return;
                    }

                    const formData = new FormData(contactForm);

                    if (formContainer) formContainer.style.display = 'none';
                    if (loadingOverlay) loadingOverlay.style.display = 'block';

                    try {
                        const tokenEl = contactForm.querySelector('input[name="_token"]');
                        const res = await fetch(@json(route('sendcontactmail')), {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': tokenEl ? tokenEl.value : ''
                            }
                        });
                        const data = await res.json();

                        if (loadingOverlay) loadingOverlay.style.display = 'none';

                        if (data && data.success) {
                            contactForm.reset();
                            setVisible(successMessage, true);

                            setTimeout(() => {
                                const modalEl = document.getElementById('tripContactModal');
                                const modal = (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal)
                                    ? (bootstrap.Modal.getInstance ? (bootstrap.Modal.getInstance(modalEl) || (bootstrap.Modal.getOrCreateInstance ? bootstrap.Modal.getOrCreateInstance(modalEl) : new bootstrap.Modal(modalEl))) : new bootstrap.Modal(modalEl))
                                    : null;
                                if (modal) modal.hide();
                                setVisible(successMessage, false);
                                if (formContainer) formContainer.style.display = 'block';
                            }, 2000);
                        } else {
                            if (formContainer) formContainer.style.display = 'block';
                            setVisible(contactError, true);
                            contactError.innerHTML = (data && data.message) ? data.message : 'An error occurred. Please try again.';
                        }
                    } catch (e) {
                        if (loadingOverlay) loadingOverlay.style.display = 'none';
                        if (formContainer) formContainer.style.display = 'block';
                        setVisible(contactError, true);
                        contactError.innerHTML = e && e.message ? e.message : 'An error occurred. Please try again.';
                    }
                }

                submitBtn.addEventListener('click', handleSubmit);
            })();

            // General contact modal submission (questions without booking details)
            (function() {
                const submitBtn = document.getElementById('tripGeneralContactSubmitBtn');
                const contactForm = document.getElementById('tripGeneralContactModalForm');
                if (!submitBtn || !contactForm) return;

                const formContainer = document.getElementById('tripGeneralContactFormContainer');
                const loadingOverlay = document.getElementById('tripGeneralContactLoadingOverlay');
                const successMessage = document.getElementById('tripGeneralContactSuccessMessage');
                const contactError = document.getElementById('tripGeneralContactError');

                function setVisible(el, visible) {
                    if (!el) return;
                    el.style.display = visible ? 'block' : 'none';
                }

                async function handleSubmit() {
                    setVisible(contactError, false);
                    setVisible(successMessage, false);

                    if (!contactForm.checkValidity()) {
                        contactForm.reportValidity();
                        return;
                    }

                    const formData = new FormData(contactForm);
                    if (loadingOverlay) loadingOverlay.style.display = 'block';
                    if (formContainer) formContainer.style.display = 'none';

                    try {
                        const tokenEl = contactForm.querySelector('input[name="_token"]');
                        const res = await fetch(@json(route('sendcontactmail')), {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                ...(tokenEl ? { 'X-CSRF-TOKEN': tokenEl.value } : {}),
                            },
                        });
                        const data = await res.json().catch(() => ({}));

                        if (res.ok && data && data.success) {
                            contactForm.reset();
                            setVisible(successMessage, true);

                            setTimeout(function () {
                                const modalEl = document.getElementById('tripGeneralContactModal');
                                if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                                    const instance = bootstrap.Modal.getInstance(modalEl);
                                    if (instance) instance.hide();
                                }
                                setVisible(successMessage, false);
                                if (formContainer) formContainer.style.display = 'block';
                            }, 2000);
                        } else {
                            if (loadingOverlay) loadingOverlay.style.display = 'none';
                            if (formContainer) formContainer.style.display = 'block';
                            setVisible(contactError, true);
                            contactError.innerHTML = (data && data.message) ? data.message : (tripOfferPageI18n.contactError || 'An error occurred. Please try again.');
                        }
                    } catch (e) {
                        if (loadingOverlay) loadingOverlay.style.display = 'none';
                        if (formContainer) formContainer.style.display = 'block';
                        setVisible(contactError, true);
                        contactError.innerHTML = e && e.message ? e.message : (tripOfferPageI18n.contactError || 'An error occurred. Please try again.');
                    }
                }

                submitBtn.addEventListener('click', handleSubmit);
            })();

            // Small map below booking card (Leaflet + OSM)
            var mapEl = document.getElementById('tripOfferMap');
            var mapData = tripOfferData.map;
            // Fallback: read from data attributes if JSON map was missing (e.g. escaped payload)
            if (mapEl && !mapData && mapEl.getAttribute('data-lat') != null && mapEl.getAttribute('data-lng') != null) {
                var latNum = parseFloat(mapEl.getAttribute('data-lat'));
                var lngNum = parseFloat(mapEl.getAttribute('data-lng'));
                if (!isNaN(latNum) && !isNaN(lngNum)) {
                    mapData = { lat: latNum, lng: lngNum, title: (mapEl.getAttribute('data-title') || 'Trip location') };
                }
            }
            if (mapData && mapEl) {
                var lat = mapData.lat, lng = mapData.lng, title = mapData.title || 'Trip location';
                function initTripMap() {
                    if (typeof L === 'undefined') return;
                    var map = L.map('tripOfferMap').setView([lat, lng], 11);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                    }).addTo(map);
                    L.marker([lat, lng]).addTo(map).bindPopup(title);
                    setTimeout(function () { map.invalidateSize(); }, 100);
                }
                if (typeof L !== 'undefined') initTripMap();
                else {
                    var s = document.createElement('script');
                    s.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                    s.crossOrigin = 'anonymous';
                    s.onload = initTripMap;
                    document.head.appendChild(s);
                }
            }
        });
    </script>
@endsection

