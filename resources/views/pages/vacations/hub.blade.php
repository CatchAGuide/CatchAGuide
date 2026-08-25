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
@include('pages.vacations.partials.catalog-header', [
    'listingTitle' => __('vacations.hub_hero_title'),
    'listingSubtitle' => __('vacations.hub_hero_lead'),
    'headerEyebrow' => __('vacations.hub_hero_eyebrow'),
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

            <x-vacation.card-slider
                :title="__('vacations.hub_popular_title')"
                :link-url="route('vacations.index')"
                :link-label="__('vacations.view_all_trips')"
                slider-id="popular"
            >

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



    <div class="mb-5">
        <x-vacation.consultation />
    </div>



    @if($hub->showNewListingsRail)

        <section class="vacation-hub__rail vacation-hub__rail--slider vacation-hub__rail--new mb-5" data-analytics-vacation-rail="new-listings">

            <x-vacation.card-slider
                :title="__('vacations.hub_new_listings_title')"
                :link-url="route('vacations.index')"
                :link-label="__('vacations.view_all_holidays')"
                slider-id="new-listings"
            >

                @foreach($hub->newListings as $card)

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

    {{-- <x-vacation.season-picker /> --}}

    <x-vacation.hub-bridge
        :total-camps="$hub->totalCamps"
        :total-trips="$hub->totalTrips"
        :country-count="$hub->countryGrid->count()"
    />



    <x-vacation.cross-sell-banner />

    {{-- Same layout as /guidings' provider-cta + seo-text (cag-home-partner / gl-seo styles from home.scss). --}}
    <div class="cag-home cag-home--embed">

        <x-vacation.provider-cta-banner :country-count="$hub->countryGrid->count()" />

        <section class="vacation-hub__seo gl-seo">
            <div class="cag-home-container">
                <h2 class="gl-seo__title">{{ __('vacations.hub_seo_title') }}</h2>
                <div class="gl-seo__body" data-gl-seo-body>
                    <p>{{ __('vacations.hub_seo_p1') }}</p>
                    <p>{{ __('vacations.hub_seo_p2') }}</p>
                </div>
                <button type="button" class="gl-seo__toggle" data-gl-seo-toggle>{{ __('vacations.hub_seo_more') }}</button>
            </div>
        </section>

    </div>

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

@section('js_after')
<script>
(function () {
    document.querySelectorAll('[data-gl-seo-toggle]').forEach(function (btn) {
        var body = btn.parentElement ? btn.parentElement.querySelector('[data-gl-seo-body]') : null;
        if (!body) return;
        var moreLabel = @json(__('vacations.hub_seo_more'));
        var lessLabel = @json(__('vacations.hub_seo_less'));
        btn.addEventListener('click', function () {
            var expanded = body.classList.toggle('is-expanded');
            btn.textContent = expanded ? lessLabel : moreLabel;
        });
    });
})();
</script>
@endsection

