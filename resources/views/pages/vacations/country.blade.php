@extends('layouts.app-v2')



@php

    $destination = $vm->destination;

    $countryName = translate($destination->name);

@endphp



@section('title', $countryName . ' — ' . __('vacations.hub_breadcrumb'))

@section('header_title', $countryName)

@section('header_sub_title', $destination->sub_title ?? $destination->introduction ?? '')

@section('description', \Illuminate\Support\Str::limit(strip_tags($destination->introduction ?? ''), 155))



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

                    <li class="active">{{ $countryName }}</li>

                </ul>

            </div>

        </div>

    </section>

</div>



<div class="container vacation-country" data-analytics-page="vacation-country" data-country="{{ $destination->slug }}">

    <section class="vacation-country__hero">
        @if(!empty($destination->thumbnail_path) && media_path_usable($destination->thumbnail_path))
            <div class="vacation-country__hero-media">
                <img src="{{ media_url($destination->thumbnail_path) }}" alt="{{ $countryName }}">
            </div>
        @endif
        <div class="vacation-country__hero-body">
            <p class="vacation-country__hero-eyebrow">{{ __('vacations.country_hero_eyebrow') }}</p>
            <p class="vacation-country__hero-lead">
                {{ $destination->sub_title ?? \Illuminate\Support\Str::limit(strip_tags($destination->introduction ?? ''), 160) }}
            </p>
            <div class="vacation-country__hero-stats">
                <span class="vacation-country__hero-stat vacation-country__hero-stat--trip">
                    <strong>{{ $vm->tripsTotal }}</strong>
                    {{ __('vacations.pillar_index_trips_title') }}
                </span>
                <span class="vacation-country__hero-stat vacation-country__hero-stat--camp">
                    <strong>{{ $vm->campsTotal }}</strong>
                    {{ __('vacations.pillar_index_camps_title') }}
                </span>
            </div>
        </div>
    </section>

    @if($destination->introduction)
        <section class="vacation-country__intro">
            {!! translate(nl2br($destination->introduction)) !!}
        </section>
    @endif



    <x-vacation.filters

        :filter="$vm->filter"

        :trips-total="$vm->tripsTotal"

        :camps-total="$vm->campsTotal"

        :species-options="$vm->speciesOptions"

    />



    @if($destination->fish_avail_title && $destination->fish_avail_intro && $vm->fishChart->count() > 0)

        <section class="vacation-country__seasonality mb-4">
            <x-vacation.section-heading :title="translate($destination->fish_avail_title)" />
            <p class="vacation-country__seasonality-intro">{!! translate($destination->fish_avail_intro) !!}</p>
            @include('pages.vacations.partials.fish-chart', ['fish_chart' => $vm->fishChart])
        </section>

    @endif



    @if($vm->tripsSection->visible && $vm->tripsTotal > 0)

        <section class="vacation-country__section vacation-country__section--trip">

            <h2 class="vacation-country__section-header vacation-country__section-header--trip">

                {{ $vm->tripsSection->headerLabel() }}

            </h2>

            <div class="vacation-country__list">

                @foreach($tripRows as $card)

                    <x-vacation.trip-list-row :card="$card" />

                @endforeach

            </div>

            <div class="mt-3">{{ $vm->trips->links('vendor.pagination.default') }}</div>

        </section>

    @endif



    @if($vm->campsSection->visible && $vm->campsTotal > 0)

        <section class="vacation-country__section vacation-country__section--camp">

            <h2 class="vacation-country__section-header vacation-country__section-header--camp">

                {{ $vm->campsSection->headerLabel() }}

            </h2>

            <div class="vacation-country__list">

                @foreach($campRows as $card)

                    <x-vacation.camp-list-row :card="$card" />

                @endforeach

            </div>

            <div class="mt-3">{{ $vm->camps->links('vendor.pagination.default') }}</div>

        </section>

    @endif



    @if($destination->content)

        <section class="vacation-country__seo mb-4 vacation-country__intro">

            {!! translate($destination->content) !!}

        </section>

    @endif



    @if($vm->faq->isNotEmpty())

        <section class="vacation-country__faq mb-5">

            <x-vacation.section-heading :title="__('vacations.hub_faq_title')" />

            <div class="accordion" id="vacationCountryFaq">

                @foreach($vm->faq as $index => $item)

                    <div class="accordion-item">

                        <h3 class="accordion-header">

                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#country-faq-{{ $index }}">

                                {{ translate($item->question ?? $item['question'] ?? '') }}

                            </button>

                        </h3>

                        <div id="country-faq-{{ $index }}" class="accordion-collapse collapse" data-bs-parent="#vacationCountryFaq">

                            <div class="accordion-body">{!! translate($item->answer ?? $item['answer'] ?? '') !!}</div>

                        </div>

                    </div>

                @endforeach

            </div>

        </section>

    @endif

</div>

@endsection

