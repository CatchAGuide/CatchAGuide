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

    @if($vm->faq->isNotEmpty())
        <section class="vacation-pillar-index__faq mb-5">
            <x-vacation.section-heading :title="$vm->faqTitle()" />
            <div class="accordion" id="vacationPillarFaq">
                @foreach($vm->faq as $index => $item)
                    <div class="accordion-item">
                        <h3 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pillar-faq-{{ $index }}">
                                {{ $item->question ?? $item['question'] ?? '' }}
                            </button>
                        </h3>
                        <div id="pillar-faq-{{ $index }}" class="accordion-collapse collapse" data-bs-parent="#vacationPillarFaq">
                            <div class="accordion-body">{!! clean_html(translate($item->answer ?? $item['answer'] ?? '')) !!}</div>
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
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-vacation-gallery]').forEach(function (gallery) {
        const galleryId = gallery.getAttribute('data-vacation-gallery');
        const images = JSON.parse(gallery.getAttribute('data-gallery-images') || '[]');
        const imageEl = gallery.querySelector('[data-vacation-gallery-image]');
        const prevBtn = gallery.querySelector('[data-vacation-prev-image]');
        const nextBtn = gallery.querySelector('[data-vacation-next-image]');
        const counter = gallery.querySelector('[data-vacation-image-counter]');
        const modal = document.querySelector('[data-vacation-modal="' + galleryId + '"]');
        const modalImage = modal ? modal.querySelector('.vacation-gallery-modal__image') : null;
        const modalPrev = modal ? modal.querySelector('.vacation-gallery-modal__prev') : null;
        const modalNext = modal ? modal.querySelector('.vacation-gallery-modal__next') : null;
        const modalClose = modal ? modal.querySelector('.vacation-gallery-modal__close') : null;
        const modalCurrent = modal ? modal.querySelector('.vacation-gallery-modal__current') : null;

        if (images.length === 0) {
            return;
        }

        let currentIndex = 0;

        function updateImage(index) {
            if (index < 0) {
                index = images.length - 1;
            }
            if (index >= images.length) {
                index = 0;
            }
            currentIndex = index;

            if (imageEl) {
                imageEl.src = images[currentIndex];
            }
            if (counter) {
                counter.textContent = (currentIndex + 1) + '/' + images.length;
            }
            if (modalImage) {
                modalImage.src = images[currentIndex];
            }
            if (modalCurrent) {
                modalCurrent.textContent = currentIndex + 1;
            }
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                updateImage(currentIndex - 1);
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                updateImage(currentIndex + 1);
            });
        }

        if (imageEl && modal) {
            imageEl.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                modal.classList.add('show');
                updateImage(currentIndex);
            });
        }

        if (modalPrev) {
            modalPrev.addEventListener('click', function () {
                updateImage(currentIndex - 1);
            });
        }

        if (modalNext) {
            modalNext.addEventListener('click', function () {
                updateImage(currentIndex + 1);
            });
        }

        if (modalClose) {
            modalClose.addEventListener('click', function () {
                modal.classList.remove('show');
            });
        }

        if (modal) {
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    modal.classList.remove('show');
                }
            });
        }
    });
});
</script>

@endsection
