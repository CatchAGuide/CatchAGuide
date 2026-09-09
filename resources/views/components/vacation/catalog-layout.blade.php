@props([
    'hasMap' => false,
    'filter',
    'tripsTotal' => 0,
    'campsTotal' => 0,
    'speciesOptions' => collect(),
    'accommodationTypeOptions' => collect(),
    'countries' => collect(),
    'action' => null,
    'pillarLinks' => null,
    'omitPillarFromQuery' => true,
    'title' => null,
])

@if(filled($title))
    <h2 class="vacation-country__listing-title d-sm-none">{{ $title }}</h2>
@endif

<div class="row gx-3 vacation-country__layout mb-5">
    <div class="col-12 d-block d-sm-none mobile-selection-sfm mb-3 vacation-country__mobile-toolbar">
        <x-vacation.filters
            render-section="mobile"
            :filter="$filter"
            :trips-total="$tripsTotal"
            :camps-total="$campsTotal"
            :species-options="$speciesOptions"
            :accommodation-type-options="$accommodationTypeOptions"
            :countries="$countries"
            :action="$action"
            :pillar-links="$pillarLinks"
            :omit-pillar-from-query="$omitPillarFromQuery"
            :show-map-button="$hasMap"
        />
    </div>

    <aside class="col-12 col-lg-3 vacation-country__sidebar d-none d-sm-block">
        @if($hasMap)
            <div class="vacation-country__map-card">
                <x-maps.preview-trigger
                    target="#vacationCountryMapModal"
                    :label="__('vacations.show_on_map')"
                />
            </div>
        @endif

        <div class="vacation-country__sidebar-filters">
            <x-vacation.filters
                render-section="sidebar"
                :filter="$filter"
                :trips-total="$tripsTotal"
                :camps-total="$campsTotal"
                :species-options="$speciesOptions"
                :accommodation-type-options="$accommodationTypeOptions"
                :countries="$countries"
                :action="$action"
                :pillar-links="$pillarLinks"
                :omit-pillar-from-query="$omitPillarFromQuery"
                variant="sidebar"
                :show-mobile-toolbar="false"
                :show-map-button="false"
            />
        </div>
    </aside>

    <div class="col-12 col-lg-9 vacation-country__listings country-listing-item ps-lg-2">
        <div class="vacation-country__toolbar d-none d-sm-flex justify-content-between align-items-center w-100">
            @if(filled($title))
                <h2 class="vacation-country__listing-title mb-0">{{ $title }}</h2>
            @endif
            <x-vacation.filters
                render-section="toolbar"
                :filter="$filter"
                :action="$action"
                :omit-pillar-from-query="$omitPillarFromQuery"
                :show-mobile-toolbar="false"
                :show-map-button="false"
            />
        </div>

        {{ $slot }}
    </div>
</div>

<x-vacation.filters
    render-section="offcanvas"
    :filter="$filter"
    :trips-total="$tripsTotal"
    :camps-total="$campsTotal"
    :species-options="$speciesOptions"
    :accommodation-type-options="$accommodationTypeOptions"
    :countries="$countries"
    :action="$action"
    :pillar-links="$pillarLinks"
    :omit-pillar-from-query="$omitPillarFromQuery"
/>
