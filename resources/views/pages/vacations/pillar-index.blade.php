@extends('layouts.app-v2')

@php
    $hasMap = count($vm->mapMarkers) > 0;
@endphp

@section('title', $vm->pageTitle())
@section('header_title', $vm->pageTitle())
@section('header_sub_title', $vm->headerSubtitle())
@section('description', $vm->metaDescription())

@php $seoRobots = app(\App\Services\Seo\SeoRobotsPolicy::class); @endphp
@if($seoRobots->shouldNoindexVacations(request()))
@section('meta_robots')
    <meta name="robots" content="{{ $seoRobots->robotsContentForVacations(request()) }}" />
@endsection
@endif

@section('content')
@php
    $vacationsHeaderCrumbs = [
        ['label' => __('vacations.hub_breadcrumb'), 'url' => route('vacations.index')],
    ];
    if ($vm->isCountryPage()) {
        $vacationsHeaderCrumbs[] = [
            'label' => __($vm->pillar->indexTitleKey()),
            'url' => route($vm->pillar->indexRouteName()),
        ];
        $vacationsHeaderCrumbs[] = [
            'label' => $vm->pageTitle(),
            'url' => null,
        ];
    } else {
        $vacationsHeaderCrumbs[] = [
            'label' => __($vm->pillar->indexTitleKey()),
            'url' => null,
        ];
    }
@endphp
@include('pages.vacations.partials.catalog-header', [
    'listingTitle' => $vm->pageTitle(),
    'listingSubtitle' => $vm->headerSubtitle(),
    'currentVacationCountry' => $vm->isCountryPage() ? ($vm->destination->slug ?? null) : null,
    'breadcrumbItems' => $vacationsHeaderCrumbs,
])

<div
    class="container vacation-pillar-index vacation-pillar-index--{{ $vm->pillar->cssModifier() }}{{ $vm->isCountryPage() ? ' vacation-pillar-country' : '' }}"
    id="vacations-category"
    data-analytics-page="{{ $vm->pillar->analyticsPage($vm->isCountryPage()) }}"
    @if($vm->isCountryPage()) data-country="{{ $vm->destination->slug }}" @endif
>
    @unless($vm->isCountryPage())
        @include('pages.vacations.partials.pillar-country-slider', [
            'countries' => $vm->countries,
            'pillar' => $vm->pillar->value,
            'sliderId' => $vm->pillar->sliderId(),
        ])
    @endunless

    @if($vm->isCountryPage() && filled($vm->introductionHtml()))
        <div id="page-main-intro" class="mb-3">
            <div class="page-main-intro-text mb-1">{!! clean_html(translate(nl2br($vm->introductionHtml()))) !!}</div>
        </div>
    @endif

    @if($hasMap)
        @include('pages.vacations.partials.country-map-modal', ['markers' => $vm->mapMarkers])
    @endif

    <x-vacation.catalog-layout
        :has-map="$hasMap"
        :filter="$vm->filter"
        :trips-total="$vm->tripsTotal"
        :camps-total="$vm->campsTotal"
        :species-options="$vm->speciesOptions"
        :accommodation-type-options="$vm->accommodationTypeOptions"
        :countries="$vm->filterCountries()"
        :action="$vm->filterAction()"
        :pillar-links="$vm->pillarToggleUrls()"
        :title="$vm->pageTitle()"
    >
        @if($vm->listings->total() > 0)
            @foreach($vm->cards as $card)
                @if($vm->pillar === \App\Domain\Vacation\VacationPillar::Camps)
                    <x-vacation.camp-list-row :card="$card" />
                @else
                    <x-vacation.trip-list-row :card="$card" />
                @endif
            @endforeach

            <div class="mt-3">{{ $vm->listings->links('vendor.pagination.default') }}</div>
        @else
            <p class="vacation-country__section-empty">{{ $vm->emptyStateMessage() }}</p>
        @endif
    </x-vacation.catalog-layout>

    @if($vm->isCountryPage() && filled($vm->bodyContentHtml()))
        <div class="mb-4">{!! clean_html(translate($vm->bodyContentHtml())) !!}</div>
    @endif
</div>

@if($vm->faq->isNotEmpty())
    <x-vacation.faq
        class="vacation-pillar-index__faq"
        :title="$vm->faqTitle()"
        :items="$vm->faq->map(fn ($item) => [
            'question' => $item->question ?? $item['question'] ?? '',
            'answer' => translate($item->answer ?? $item['answer'] ?? ''),
        ])"
    />
@endif

@endsection
