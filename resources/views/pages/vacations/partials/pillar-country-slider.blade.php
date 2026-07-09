@props([
    'countries',
    'pillar' => 'camps',
    'sliderId' => 'pillar-countries',
])

@php
    $countrySlides = $countries->filter(fn ($row) => ($row[$pillar] ?? 0) > 0);
    $countryRoute = $pillar === 'trips' ? 'vacations.trips.show' : 'vacations.camps.show';
@endphp

@if($countrySlides->isNotEmpty())
    <section class="vacation-pillar-index__countries mb-4" data-analytics-vacation-rail="country-slider">
        <x-vacation.country-slider
            :title="__('vacations.hub_country_slider_title')"
            :subtitle="__('vacations.hub_country_slider_subtitle')"
            :slider-id="$sliderId"
        >
            @foreach($countrySlides as $row)
                <div class="swiper-slide">
                    <x-vacation.country-slide
                        :row="$row"
                        :href="route($countryRoute, $row['slug'])"
                    />
                </div>
            @endforeach
        </x-vacation.country-slider>
    </section>
@endif
