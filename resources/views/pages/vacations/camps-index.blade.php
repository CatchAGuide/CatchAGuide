@extends('layouts.app-v2')
@section('title', __('vacations.pillar_index_camps_title'))
@section('header_title', __('vacations.pillar_index_camps_title'))
@section('header_sub_title', __('vacations.pillar_camps_desc'))
@section('description', __('vacations.pillar_camps_desc'))
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
                    <li class="active">{{ __('vacations.pillar_index_camps_title') }}</li>
                </ul>
            </div>
        </div>
    </section>
</div>

<div class="container vacation-pillar-index vacation-pillar-index--camp" data-analytics-page="vacation-camps-index">
    @if($listings->total() > 0)
        <p class="vacation-pillar-index__results-meta mb-3">
            {{ __('vacations.pillar_index_results', ['count' => $listings->total()]) }}
        </p>
    @endif

    <x-vacation.filters
        :filter="$filter"
        :show-pillar-toggles="false"
        :species-options="$speciesOptions"
        :countries="$countries"
        :action="route('vacations.camps.index')"
    />

    <div class="vacation-hub__card-grid mb-4">
        @forelse($cards as $card)
            <x-vacation.camp-card :card="$card" />
        @empty
            <p class="text-muted col-12">{{ __('vacations.empty_state_body_camp', ['country' => __('vacations.all_region')]) }}</p>
        @endforelse
    </div>
    {{ $listings->links('vendor.pagination.default') }}
</div>

@endsection

