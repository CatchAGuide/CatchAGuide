@props([
    'countries',
    'pillar' => 'camps',
    'sliderId' => 'pillar-countries',
])

@php
    $countrySlides = $countries;
    $countryRoute = $pillar === 'trips' ? 'vacations.trips.show' : 'vacations.camps.show';
@endphp

@if($countrySlides->isNotEmpty())
    <section class="vacation-pillar-index__countries mb-4" data-analytics-vacation-rail="country-slider">
        <x-vacation.country-slider
            :title="__('vacations.hub_country_slider_title')"
            :subtitle="__('vacations.hub_country_slider_subtitle')"
            :link-url="route('vacations.countries', ['pillar' => $pillar])"
            :link-label="__('vacations.hub_country_slider_see_all')"
            :slider-id="$sliderId"
        >
            @foreach([false, true] as $isClone)
                @foreach($countrySlides as $row)
                    <x-vacation.country-slide
                        :row="$row"
                        :href="route($countryRoute, $row['slug'])"
                        :clone="$isClone"
                    />
                @endforeach
            @endforeach
        </x-vacation.country-slider>
    </section>
@endif
