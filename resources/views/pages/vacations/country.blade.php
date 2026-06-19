@extends('layouts.app-v2')

@php
    $destination = $vm->destination;
    $countryName = translate($destination->name);
    $hasMap = count($vm->mapMarkers) > 0;
    $countryIntro = strip_tags(translate($destination->introduction ?? $destination->sub_title ?? ''));
@endphp

@section('title', $countryName . ' — ' . __('vacations.hub_breadcrumb'))
@section('header_title', $countryName)
@section('header_sub_title', $countryIntro)
@section('description', \Illuminate\Support\Str::limit($countryIntro, 155))

@section('content')
<div class="container">
    <section class="page-header">
        <div class="page-header__bottom breadcrumb-container">
            <div class="page-header__bottom-inner">
                <ul class="thm-breadcrumb list-unstyled">
                    <li><a href="{{ route('welcome') }}">@lang('message.home')</a></li>
                    <li><span><i class="fas fa-solid fa-chevron-right"></i></span></li>
                    <li><a href="{{ route('vacations.index') }}">{{ __('vacations.hub_breadcrumb') }}</a></li>
                    <li><span><i class="fas fa-solid fa-chevron-right"></i></span></li>
                    <li class="active">{{ __('vacations.country_listing_title', ['country' => $countryName]) }}</li>
                </ul>
            </div>
        </div>
    </section>
</div>

<div
    class="container vacation-country"
    id="vacations-category"
    data-analytics-page="vacation-country"
    data-country="{{ $destination->slug }}"
>
    <h2 class="vacation-country__listing-title">{{ __('vacations.country_listing_title', ['country' => $countryName]) }}</h2>

    @if($hasMap)
        @include('pages.vacations.partials.country-map-modal', ['markers' => $vm->mapMarkers])
    @endif

    <div class="row vacation-country__layout mb-5">
        <aside class="col-lg-3 vacation-country__sidebar">
            @if($hasMap)
                <div class="card vacation-country__map-card d-none d-lg-block mb-3">
                    <div id="map-placeholder" class="vacation-country__map-placeholder">
                        <button
                            type="button"
                            class="btn btn-primary show-more-maps"
                            data-bs-target="#vacationCountryMapModal"
                            data-bs-toggle="modal"
                        >
                            @lang('vacations.show_on_map')
                        </button>
                    </div>
                </div>
            @endif

            <div class="vacation-country__sidebar-filters d-none d-lg-block">
                <x-vacation.filters
                    :filter="$vm->filter"
                    :trips-total="$vm->tripsTotal"
                    :camps-total="$vm->campsTotal"
                    :species-options="$vm->speciesOptions"
                    :show-map-button="false"
                    :show-mobile-toolbar="false"
                    variant="sidebar"
                />
            </div>
        </aside>

        <div class="col-lg-9 vacation-country__listings country-listing-item">
            <div class="vacation-country__mobile-toolbar d-lg-none mb-3">
                <x-vacation.filters
                    :filter="$vm->filter"
                    :trips-total="$vm->tripsTotal"
                    :camps-total="$vm->campsTotal"
                    :species-options="$vm->speciesOptions"
                    :show-map-button="$hasMap"
                    :show-desktop="false"
                    variant="mobile"
                />
            </div>

            @if($vm->listingsTotal > 0)
                @foreach($listingRows as $card)
                    <x-vacation.listing-row :card="$card" />
                @endforeach

                <div class="mt-3">{{ $vm->listings->links('vendor.pagination.default') }}</div>
            @else
                <p class="vacation-country__section-empty">
                    @if($vm->filter->pillar === 'trips')
                        {{ __('vacations.empty_state_body_trip', ['country' => $countryName]) }}
                    @else
                        {{ __('vacations.empty_state_body_camp', ['country' => $countryName]) }}
                    @endif
                </p>
            @endif
        </div>
    </div>

    @if($destination->fish_avail_title && $destination->fish_avail_intro && $vm->fishChart->count() > 0)
        <section class="vacation-country__seasonality mb-4">
            <x-vacation.section-heading :title="translate($destination->fish_avail_title)" />
            <p class="vacation-country__seasonality-intro">{!! translate($destination->fish_avail_intro) !!}</p>
            @include('pages.vacations.partials.fish-chart', ['fish_chart' => $vm->fishChart])
        </section>
    @endif

    @if($destination->content)
        <section class="vacation-country__seo mb-4 vacation-country__intro">
            {!! translate($destination->content) !!}
        </section>
    @endif

    @if($vm->faq->isNotEmpty())
        <section class="vacation-country__faq mb-5">
            <x-vacation.section-heading :title="__('vacations.hub_faq_title')" />
            <div class="accordion" id="vacationCountryFaq">
                @foreach($vm->faq as $index => $item)
                    <div class="accordion-item">
                        <h3 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#country-faq-{{ $index }}">
                                {{ translate($item->question ?? $item['question'] ?? '') }}
                            </button>
                        </h3>
                        <div id="country-faq-{{ $index }}" class="accordion-collapse collapse" data-bs-parent="#vacationCountryFaq">
                            <div class="accordion-body">{!! translate($item->answer ?? $item['answer'] ?? '') !!}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection

@section('js_after')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-vacation-gallery]').forEach(function (gallery) {
        const galleryId = gallery.getAttribute('data-vacation-gallery');
        const images = JSON.parse(gallery.getAttribute('data-gallery-images') || '[]');
        const imageEl = gallery.querySelector('[data-vacation-gallery-image]');
        const prevBtn = gallery.querySelector('[data-vacation-prev-image]');
        const nextBtn = gallery.querySelector('[data-vacation-next-image]');
        const counter = gallery.querySelector('[data-vacation-image-counter]');
        const modal = document.querySelector('[data-vacation-modal="' + galleryId + '"]');
        const modalImage = modal ? modal.querySelector('.vacation-gallery-modal__image') : null;
        const modalPrev = modal ? modal.querySelector('.vacation-gallery-modal__prev') : null;
        const modalNext = modal ? modal.querySelector('.vacation-gallery-modal__next') : null;
        const modalClose = modal ? modal.querySelector('.vacation-gallery-modal__close') : null;
        const modalCurrent = modal ? modal.querySelector('.vacation-gallery-modal__current') : null;

        if (images.length === 0) {
            return;
        }

        let currentIndex = 0;

        function updateImage(index) {
            if (index < 0) {
                index = images.length - 1;
            }
            if (index >= images.length) {
                index = 0;
            }
            currentIndex = index;

            if (imageEl) {
                imageEl.src = images[currentIndex];
            }
            if (counter) {
                counter.textContent = (currentIndex + 1) + '/' + images.length;
            }
            if (modalImage) {
                modalImage.src = images[currentIndex];
            }
            if (modalCurrent) {
                modalCurrent.textContent = currentIndex + 1;
            }
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                updateImage(currentIndex - 1);
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                updateImage(currentIndex + 1);
            });
        }

        if (imageEl && modal) {
            imageEl.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                modal.classList.add('show');
                updateImage(currentIndex);
            });
        }

        if (modalPrev) {
            modalPrev.addEventListener('click', function () {
                updateImage(currentIndex - 1);
            });
        }

        if (modalNext) {
            modalNext.addEventListener('click', function () {
                updateImage(currentIndex + 1);
            });
        }

        if (modalClose) {
            modalClose.addEventListener('click', function () {
                modal.classList.remove('show');
            });
        }

        if (modal) {
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    modal.classList.remove('show');
                }
            });
        }
    });
});
</script>

@if($hasMap)
<script>
(function () {
    const MapsManager = window.GoogleMapsManager;
    if (! MapsManager) {
        return;
    }

    const markersData = @json($vm->mapMarkers);
    let map;
    let mapInitialized = false;
    const markers = [];
    const infowindows = [];

    async function initVacationCountryMap() {
        if (mapInitialized || ! markersData.length) {
            if (map) {
                MapsManager.resizeMap(map);
            }
            return;
        }

        const defaultCenter = { lat: markersData[0].lat, lng: markersData[0].lng };
        map = await MapsManager.initMap('vacationCountryMap', {
            zoom: 6,
            center: defaultCenter,
            mapId: @json(config('services.google_maps.map_id', 'DEMO_MAP_ID')),
            mapTypeControl: false,
            streetViewControl: false,
        });

        const bounds = new google.maps.LatLngBounds();

        for (const item of markersData) {
            const position = { lat: item.lat, lng: item.lng };
            bounds.extend(position);

            const marker = await MapsManager.createMarker({ map, position });
            markers.push(marker);

            const infowindow = new google.maps.InfoWindow({
                content: `
                    <div class="vacation-country-map-popup">
                        <a href="${item.url}" class="vacation-country-map-popup__title">${item.title}</a>
                    </div>
                `,
            });
            infowindows.push(infowindow);

            marker.addListener('gmp-click', () => {
                infowindows.forEach((window) => window.close());
                infowindow.open(map, marker);
            });
        }

        if (markersData.length === 1) {
            map.setCenter(bounds.getCenter());
            map.setZoom(10);
        } else {
            map.fitBounds(bounds);
        }

        if (markers.length > 1) {
            MapsManager.createMarkerClusterer({ markers, map });
        }

        mapInitialized = true;
    }

    MapsManager.initMapOnModalShow('vacationCountryMapModal', initVacationCountryMap);
})();
</script>
@endif
@endsection
