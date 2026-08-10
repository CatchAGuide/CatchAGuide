@extends('layouts.app-v2')

@php
    $hasMap = count($vm->mapMarkers) > 0;
    $listingTitle = $vm->pageTitle();
    $currentSort = $vm->filter->sortBy ?? '';
    $sortQuery = request()->except(['page', 'sortby']);
@endphp

@section('title', $listingTitle)
@section('header_title', $listingTitle)
@section('header_sub_title', $vm->pageSubtitle())
@section('description', \Illuminate\Support\Str::limit($vm->pageSubtitle(), 155))

@section('content')
<div class="offers-catalog-page">
    <div class="container">
        <section class="page-header page-header--offers-compact">
            <div class="page-header__bottom breadcrumb-container">
                <div class="page-header__bottom-inner">
                    <ul class="thm-breadcrumb list-unstyled">
                        <li><a href="{{ route('welcome') }}">@lang('message.home')</a></li>
                        <li><span><i class="fas fa-solid fa-chevron-right"></i></span></li>
                        <li class="active">{{ __('offers.breadcrumb') }}</li>
                    </ul>
                </div>
            </div>
        </section>
    </div>

    <div
        class="container offers-catalog"
        id="offers-catalog"
        data-analytics-page="offers-catalog"
    >
        @if(filled($vm->filter->place))
            <p class="offers-catalog__context" data-offers-place>
            </p>
        @endif

        @if($hasMap)
            @include('pages.offers.partials.map-modal', [
                'markers' => $vm->mapMarkers,
                'resultCount' => $vm->listingsTotal,
            ])
        @endif

        <div class="row offers-catalog__layout mb-5">
            <div class="col-12 d-block d-sm-none mobile-selection-sfm mb-3">
                <x-offers.filters
                    render-section="mobile"
                    :filter="$vm->filter"
                    :tours-total="$vm->toursTotal"
                    :trips-total="$vm->tripsTotal"
                    :camps-total="$vm->campsTotal"
                    :species-options="$vm->speciesOptions"
                    :countries="$vm->countries"
                    :method-options="$vm->methodOptions"
                    :water-options="$vm->waterOptions"
                    :tour-duration-options="$vm->tourDurationOptions"
                    :trip-duration-options="$vm->tripDurationOptions"
                    :accommodation-type-options="$vm->accommodationTypeOptions"
                    :type-links="$vm->typeToggleUrls()"
                    :vacation-links="$vm->vacationToggleUrls()"
                    :action="$vm->filterAction()"
                    :show-map-button="$hasMap"
                />
            </div>

            <aside class="col-12 col-lg-3 offers-catalog__sidebar d-none d-sm-block">
                @if($hasMap)
                    <div class="offers-catalog__map-card">
                        <x-maps.preview-trigger
                            target="#offersCatalogMapModal"
                            :label="__('offers.show_on_map')"
                            :result-count="$vm->listingsTotal"
                        />
                    </div>
                @endif

                <div class="offers-catalog__sidebar-panel">
                    <x-offers.filters
                        render-section="sidebar"
                        :filter="$vm->filter"
                        :tours-total="$vm->toursTotal"
                        :trips-total="$vm->tripsTotal"
                        :camps-total="$vm->campsTotal"
                        :species-options="$vm->speciesOptions"
                        :countries="$vm->countries"
                        :method-options="$vm->methodOptions"
                        :water-options="$vm->waterOptions"
                        :tour-duration-options="$vm->tourDurationOptions"
                        :trip-duration-options="$vm->tripDurationOptions"
                        :accommodation-type-options="$vm->accommodationTypeOptions"
                        :type-links="$vm->typeToggleUrls()"
                        :vacation-links="$vm->vacationToggleUrls()"
                        :action="$vm->filterAction()"
                        :show-map-button="false"
                        :show-mobile-toolbar="false"
                        :show-type-toggles="false"
                    />
                </div>
            </aside>

            <div class="col-12 col-lg-9 offers-catalog__main" data-offers-list>
                <div class="offers-catalog__toolbar d-none d-sm-flex">
                    <x-offers.filters
                        render-section="toolbar"
                        :filter="$vm->filter"
                        :tours-total="$vm->toursTotal"
                        :trips-total="$vm->tripsTotal"
                        :camps-total="$vm->campsTotal"
                        :type-links="$vm->typeToggleUrls()"
                        :vacation-links="$vm->vacationToggleUrls()"
                        :action="$vm->filterAction()"
                    />
                    <form method="get" action="{{ $vm->filterAction() }}" class="offers-catalog__sort">
                        @foreach($sortQuery as $key => $value)
                            @if(is_array($value))
                                @foreach($value as $v)
                                    <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                                @endforeach
                            @else
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <label for="offers-sortby" class="offers-catalog__sort-label">{{ __('offers.filter_sort') }}</label>
                        <select id="offers-sortby" name="sortby" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="" @selected($currentSort === '' || $currentSort === 'newest')>{{ __('message.newest') }}</option>
                            <option value="price-asc" @selected($currentSort === 'price-asc')>@lang('message.lowprice')</option>
                            <option value="price-desc" @selected($currentSort === 'price-desc')>{{ __('trips.catalog_sort_price_desc') }}</option>
                        </select>
                    </form>
                </div>

                @if($vm->cards->isNotEmpty())
                    <div class="offers-catalog__cards">
                        @foreach($vm->cards as $card)
                            <x-offers.list-row :card="$card" />
                        @endforeach
                    </div>
                    <div class="offers-catalog__pagination mt-4">{{ $vm->listings->links('vendor.pagination.default') }}</div>
                @else
                    <p class="offers-catalog__empty" data-offers-empty>{{ $vm->emptyStateMessage() }}</p>
                @endif

                @if($vm->suggestedCards->isNotEmpty())
                    <section class="offers-catalog__suggested" data-offers-suggested>
                        <h2 class="offers-catalog__suggested-title">
                            {{ __('offers.suggested_near', ['place' => $vm->filter->place ?: __('offers.breadcrumb')]) }}
                        </h2>
                        <p class="offers-catalog__suggested-count">
                            {{ __('offers.suggested_count', ['count' => $vm->suggestedCards->count()]) }}
                        </p>
                        <div class="offers-catalog__cards">
                            @foreach($vm->suggestedCards as $card)
                                <x-offers.list-row :card="$card" />
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>
        </div>

        @if($vm->faq->isNotEmpty())
            <section class="offers-catalog__faq mb-5" data-offers-faq>
                <x-vacation.section-heading :title="__('offers.faq_title')" />
                <div class="accordion" id="offersCatalogFaq">
                    @foreach($vm->faq as $index => $item)
                        <div class="accordion-item">
                            <h3 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#offers-faq-{{ $index }}">
                                    {{ $item->question ?? $item['question'] ?? '' }}
                                </button>
                            </h3>
                            <div id="offers-faq-{{ $index }}" class="accordion-collapse collapse" data-bs-parent="#offersCatalogFaq">
                                <div class="accordion-body">{!! $item->answer ?? $item['answer'] ?? '' !!}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</div>

<x-offers.filters
    render-section="offcanvas"
    :filter="$vm->filter"
    :tours-total="$vm->toursTotal"
    :trips-total="$vm->tripsTotal"
    :camps-total="$vm->campsTotal"
    :species-options="$vm->speciesOptions"
    :countries="$vm->countries"
    :method-options="$vm->methodOptions"
    :water-options="$vm->waterOptions"
    :tour-duration-options="$vm->tourDurationOptions"
    :trip-duration-options="$vm->tripDurationOptions"
    :accommodation-type-options="$vm->accommodationTypeOptions"
    :type-links="$vm->typeToggleUrls()"
    :vacation-links="$vm->vacationToggleUrls()"
    :action="$vm->filterAction()"
    :show-map-button="$hasMap"
/>
@endsection

@section('js_after')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-vacation-gallery]').forEach(function (gallery) {
        const galleryId = gallery.getAttribute('data-vacation-gallery');
        const images = JSON.parse(gallery.getAttribute('data-gallery-images') || '[]');
        const imageEl = gallery.querySelector('[data-vacation-gallery-image]');
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

        function render(index) {
            currentIndex = (index + images.length) % images.length;
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
                modalCurrent.textContent = String(currentIndex + 1);
            }
        }

        gallery.querySelector('[data-vacation-open-modal]')?.addEventListener('click', function () {
            if (modal) {
                modal.classList.add('is-open');
                render(currentIndex);
            }
        });

        modalClose?.addEventListener('click', function () {
            modal?.classList.remove('is-open');
        });
        modalPrev?.addEventListener('click', function () { render(currentIndex - 1); });
        modalNext?.addEventListener('click', function () { render(currentIndex + 1); });
    });
});
</script>
@endsection
