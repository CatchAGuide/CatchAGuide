@extends('layouts.app-v2')



@section('title', __('vacations.pillar_index_trips_title'))

@section('header_title', __('vacations.pillar_index_trips_title'))

@section('header_sub_title', __('vacations.pillar_trips_desc'))

@section('description', __('vacations.pillar_trips_desc'))



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

                    <li class="active">{{ __('vacations.pillar_index_trips_title') }}</li>

                </ul>

            </div>

        </div>

    </section>

</div>



<div class="container vacation-pillar-index vacation-pillar-index--trip" data-analytics-page="vacation-trips-index">

    <div class="vacation-pillar-index__banner vacation-pillar-index__banner--trip">
        <div class="vacation-pillar-index__banner-icon" aria-hidden="true">
            <i class="fas fa-suitcase-rolling"></i>
        </div>
        <div>
            <h2 class="vacation-pillar-index__banner-title">{{ __('vacations.pillar_index_trips_title') }}</h2>
            <p>{{ __('vacations.pillar_trips_desc') }}</p>
            @if($listings->total() > 0)
                <p class="vacation-pillar-index__banner-meta">
                    {{ __('vacations.pillar_index_results', ['count' => $listings->total()]) }}
                </p>
            @endif
        </div>
    </div>



    <x-vacation.filters

        :filter="$filter"

        :show-pillar-toggles="false"

        :species-options="$speciesOptions"

        :countries="$countries"

        :action="route('vacations.trips.index')"

    />



    <div class="vacation-hub__card-grid mb-4">

        @forelse($cards as $card)

            <x-vacation.trip-card :card="$card" />

        @empty

            <p class="text-muted col-12">{{ __('vacations.empty_state_body_trip', ['country' => __('vacations.all_region')]) }}</p>

        @endforelse

    </div>



    {{ $listings->links('vendor.pagination.default') }}

</div>

@endsection

