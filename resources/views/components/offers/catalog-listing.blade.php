@props([
    'vm',
    'showFaq' => true,
    'analyticsPage' => 'offers-catalog',
    'wrapperClass' => 'offers-catalog',
])

@php
    $hasMap = count($vm->mapMarkers) > 0;
    $currentSort = $vm->filter->sortBy ?? '';
    $lockedParams = $vm->lockedScopeParams();
    $sortQuery = array_merge(request()->except(['page', 'sortby']), $lockedParams);
    $showTypeToggles = ! $vm->lockTourScope && ! $vm->lockVacationScope;
@endphp

<div
    class="{{ $wrapperClass }}"
    id="offers-catalog"
    data-analytics-page="{{ $analyticsPage }}"
>
    @if(filled($vm->filter->place))
        <p class="offers-catalog__context" data-offers-place>
            {{ $vm->filter->place }}
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
                :show-type-toggles="$showTypeToggles"
                :locked-params="$lockedParams"
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
                    :locked-params="$lockedParams"
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
                    :show-type-toggles="$showTypeToggles"
                    :locked-params="$lockedParams"
                />
                <form method="get" action="{{ $vm->filterAction() }}" class="offers-catalog__sort" data-offers-sort-form>
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
                    <select id="offers-sortby" name="sortby" class="form-select form-select-sm" data-offers-sort-select>
                        @include('components.offers.partials.sort-options', ['filter' => $vm->filter, 'currentSort' => $currentSort])
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

    @if($showFaq && $vm->faq->isNotEmpty())
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
    :locked-params="$lockedParams"
/>
