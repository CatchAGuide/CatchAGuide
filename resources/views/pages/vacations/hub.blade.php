@extends('layouts.app-v2')



@section('title', __('vacations.hub_title'))

@section('header_title', __('vacations.hub_header_title'))

@section('header_sub_title', __('vacations.hub_header_subtitle'))

@section('description', __('vacations.hub_header_subtitle'))



@section('content')

<div class="container">

    <section class="page-header">

        <div class="page-header__bottom breadcrumb-container">

            <div class="page-header__bottom-inner">

                <ul class="thm-breadcrumb list-unstyled">

                    <li><a href="{{ route('welcome') }}">@lang('message.home')</a></li>

                    <li><span><i class="fas fa-solid fa-chevron-right"></i></span></li>

                    <li class="active">{{ __('vacations.hub_breadcrumb') }}</li>

                </ul>

            </div>

        </div>

    </section>

</div>



<div class="container vacation-hub" data-analytics-page="vacation-hub">

    <section class="vacation-hub__pillar-fork mb-5" aria-label="{{ __('vacations.hub_fork_eyebrow') }}">
        <div class="vacation-hub__pillar-tiles row g-4">
            <div class="col-md-6">
                <x-vacation.pillar-tile :tile="$hub->campTile" />
            </div>

            <div class="col-md-6">
                <x-vacation.pillar-tile :tile="$hub->tripTile" />
            </div>
        </div>
    </section>



    @if($hub->popularListings->isNotEmpty())

        <section class="vacation-hub__rail mb-5" data-analytics-vacation-rail="popular">

            <x-vacation.section-heading :title="__('vacations.hub_popular_title')" />

            <div class="vacation-hub__card-grid">

                @foreach($hub->popularListings as $card)

                    <x-vacation.product-card :card="$card" />

                @endforeach

            </div>

        </section>

    @endif



    @if($hub->showNewTripsRail && $hub->newTrips->isNotEmpty())

        <section class="vacation-hub__rail vacation-hub__rail--trips mb-5" data-analytics-vacation-rail="new-trips">

            <x-vacation.section-heading

                :title="__('vacations.hub_new_trips_title')"

                :link-url="route('vacations.trips.index')"

                :link-label="__('vacations.view_all_trips')"

            />

            <div class="vacation-hub__card-grid">

                @foreach($hub->newTrips as $card)

                    <x-vacation.trip-card :card="$card" />

                @endforeach

            </div>

        </section>

    @endif



    <section class="vacation-hub__countries mb-5" data-analytics-vacation-rail="country-grid">

        <x-vacation.section-heading :title="__('vacations.hub_country_grid_title')" />

        <div class="row g-3">

            @foreach($hub->countryGrid as $row)

                <div class="col-sm-6 col-lg-4 col-xl-3">

                    <a href="{{ route('vacations.country', $row['slug']) }}" class="vacation-country-tile">

                        <div class="vacation-country-tile__media">

                            @if(!empty($row['thumbnail_path']))

                                <img src="{{ media_url($row['thumbnail_path']) }}" alt="{{ translate($row['name']) }}">

                            @else

                                <div class="vacation-country-tile__placeholder">

                                    <i class="fas fa-map-marked-alt" aria-hidden="true"></i>

                                </div>

                            @endif

                        </div>

                        <div class="vacation-country-tile__body">

                            <h3 class="vacation-country-tile__title">{{ translate($row['name']) }}</h3>

                            <div class="vacation-country-tile__counts">

                                @if($row['trips'] > 0)

                                    <span class="vacation-country-tile__chip vacation-country-tile__chip--trip">

                                        {{ $row['trips'] }} {{ __('vacations.pillar_index_trips_title') }}

                                    </span>

                                @endif

                                @if($row['camps'] > 0)

                                    <span class="vacation-country-tile__chip vacation-country-tile__chip--camp">

                                        {{ $row['camps'] }} {{ __('vacations.pillar_index_camps_title') }}

                                    </span>

                                @endif

                            </div>

                        </div>

                    </a>

                </div>

            @endforeach

        </div>

    </section>



    @if($hub->inspirationTiles->isNotEmpty())

        <section class="vacation-hub__inspiration mb-5">

            <x-vacation.section-heading :title="__('vacations.hub_inspiration_title')" />

            <div class="row g-3">

                @foreach($hub->inspirationTiles as $tile)

                    <div class="col-md-4">

                        <a href="{{ $tile['url'] }}" class="vacation-inspiration-chip">{{ $tile['title'] }}</a>

                    </div>

                @endforeach

            </div>

        </section>

    @endif



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

