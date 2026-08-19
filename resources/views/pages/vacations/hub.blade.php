@extends('layouts.app-v2')



@section('title', __('vacations.hub_title'))

@section('header_title', __('vacations.hub_header_title'))

@section('header_sub_title', __('vacations.hub_header_subtitle'))

@section('description', __('vacations.hub_header_subtitle'))

@php $seoRobots = app(\App\Services\Seo\SeoRobotsPolicy::class); @endphp
@if($seoRobots->shouldNoindexVacations(request()))
@section('meta_robots')
    <meta name="robots" content="{{ $seoRobots->robotsContentForVacations(request()) }}" />
@endsection
@endif

@section('content')
@php
    $hubStatLine = __('vacations.hub_stat_line', [
        'offers' => $hub->totalCamps + $hub->totalTrips,
        'countries' => $hub->countryGrid->count(),
    ]);
@endphp
@include('pages.vacations.partials.catalog-header', [
    'listingTitle' => __('vacations.hub_hero_title'),
    'listingSubtitle' => __('vacations.hub_hero_lead'),
    'headerEyebrow' => __('vacations.hub_hero_eyebrow'),
    'headerStatLine' => $hubStatLine,
    'breadcrumbItems' => [
        ['label' => __('vacations.hub_breadcrumb'), 'url' => null],
    ],
])

<div class="container vacation-hub" data-analytics-page="vacation-hub">

    @if($hub->countryGrid->isNotEmpty())

        <section class="vacation-hub__countries mb-5" data-analytics-vacation-rail="country-slider">

            <x-vacation.country-slider
                :title="__('vacations.hub_country_slider_title')"
                :subtitle="__('vacations.hub_country_slider_subtitle')"
                slider-id="countries"
            >
                @foreach([false, true] as $isClone)
                    @foreach($hub->countryGrid as $row)
                        <x-vacation.country-slide :row="$row" :clone="$isClone" />
                    @endforeach
                @endforeach
            </x-vacation.country-slider>

        </section>

    @endif

    <section class="vacation-hub__pillar-fork mb-5" aria-label="{{ __('vacations.hub_fork_eyebrow') }}">
        <x-vacation.section-heading
            :eyebrow="__('vacations.hub_fork_eyebrow')"
            :title="__('vacations.hub_fork_title')"
            :subtitle="__('vacations.hub_fork_subtitle')"
        />
        <div class="vacation-hub__pillar-tiles row g-3 g-md-4">
            <div class="col-md-6">
                <x-vacation.pillar-tile :tile="$hub->campTile" />
            </div>

            <div class="col-md-6">
                <x-vacation.pillar-tile :tile="$hub->tripTile" />
            </div>
        </div>
    </section>



    @if($hub->popularListings->isNotEmpty())

        <section class="vacation-hub__rail vacation-hub__rail--slider mb-5" data-analytics-vacation-rail="popular">

            <x-vacation.card-slider :title="__('vacations.hub_popular_title')" slider-id="popular">

                @foreach($hub->popularListings as $card)

                    <div class="swiper-slide">

                        @if(($card['type'] ?? 'trip') === 'camp')
                            <x-vacation.camp-card :card="$card" variant="slider" />
                        @else
                            <x-vacation.trip-card :card="$card" variant="slider" />
                        @endif

                    </div>

                @endforeach

            </x-vacation.card-slider>

        </section>

    @endif



    <div class="mb-5">
        <x-vacation.consultation />
    </div>



    @if($hub->showNewCampsRail && $hub->newCamps->isNotEmpty())

        <section class="vacation-hub__rail vacation-hub__rail--slider vacation-hub__rail--camps mb-5" data-analytics-vacation-rail="new-camps">

            <x-vacation.card-slider
                :title="__('vacations.hub_new_camps_title')"
                :link-url="route('vacations.camps.index')"
                :link-label="__('vacations.view_all_camps')"
                slider-id="new-camps"
            >

                @foreach($hub->newCamps as $card)

                    <div class="swiper-slide">

                        <x-vacation.camp-card :card="$card" variant="slider" />

                    </div>

                @endforeach

            </x-vacation.card-slider>

        </section>

    @endif



    @if($hub->showNewTripsRail && $hub->newTrips->isNotEmpty())

        <section class="vacation-hub__rail vacation-hub__rail--slider vacation-hub__rail--trips mb-5" data-analytics-vacation-rail="new-trips">

            <x-vacation.card-slider
                :title="__('vacations.hub_new_trips_title')"
                :link-url="route('vacations.trips.index')"
                :link-label="__('vacations.view_all_trips')"
                slider-id="new-trips"
            >

                @foreach($hub->newTrips as $card)

                    <div class="swiper-slide">

                        <x-vacation.trip-card :card="$card" variant="slider" />

                    </div>

                @endforeach

            </x-vacation.card-slider>

        </section>

    @endif



    @if($hub->testimonials->isNotEmpty())

        <section class="vacation-hub__rail vacation-hub__rail--slider mb-5" data-analytics-vacation-rail="reviews">

            <x-vacation.card-slider
                :eyebrow="__('vacations.hub_reviews_eyebrow')"
                :title="__('vacations.hub_reviews_title')"
                :subtitle="__('vacations.hub_reviews_caption')"
                slider-id="reviews"
            >

                @foreach($hub->testimonials as $review)

                    <div class="swiper-slide">

                        <x-vacation.testimonial-card :review="$review" />

                    </div>

                @endforeach

            </x-vacation.card-slider>

        </section>

    @endif



    @if($hub->targetFishTiles->isNotEmpty())

        <section class="vacation-hub__fish mb-5" data-analytics-vacation-rail="target-fish">

            <x-vacation.country-slider
                :title="__('vacations.hub_target_fish_title')"
                :subtitle="__('vacations.hub_target_fish_subtitle')"
                slider-id="target-fish"
                block-class="vacation-fish-rail"
            >
                @foreach([false, true] as $isClone)
                    @foreach($hub->targetFishTiles as $tile)
                        <x-vacation.fish-slide :tile="$tile" :clone="$isClone" />
                    @endforeach
                @endforeach
            </x-vacation.country-slider>

        </section>

    @endif



    <x-vacation.season-picker />



    <x-vacation.hub-bridge
        :total-camps="$hub->totalCamps"
        :total-trips="$hub->totalTrips"
        :country-count="$hub->countryGrid->count()"
    />



    <x-vacation.cross-sell-banner />

    <section class="vacation-hub__seo mb-5">

        <div class="vacation-hub__seo-copy">
            <h2 class="vacation-hub__seo-copy-title">{{ __('vacations.hub_seo_title') }}</h2>
            <p>{{ __('vacations.hub_seo_p1') }}</p>
            <p>{{ __('vacations.hub_seo_p2') }}</p>
        </div>

        <x-vacation.provider-cta-banner />

    </section>

    @if(!empty($hub->faqItems))

        <section class="vacation-hub__faq mb-5">

            <x-vacation.section-heading :title="__('vacations.hub_faq_title')" />

            <div class="accordion" id="vacationHubFaq">

                @foreach($hub->faqItems as $index => $item)

                    <div class="accordion-item">

                        <h3 class="accordion-header" id="faq-heading-{{ $index }}">

                            <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#faq-collapse-{{ $index }}">

                                {{ $item['question'] }}

                            </button>

                        </h3>

                        <div id="faq-collapse-{{ $index }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" data-bs-parent="#vacationHubFaq">

                            <div class="accordion-body">{!! $item['answer'] !!}</div>

                        </div>

                    </div>

                @endforeach

            </div>

        </section>

    @endif

</div>

@endsection

