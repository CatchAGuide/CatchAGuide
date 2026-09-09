@extends('layouts.app-v2')

@php
    $listingTitle = $vm->pageTitle();
    $listingSubtitle = $vm->pageSubtitle();
    $hasGuidingsSearchFilter = filled($vm->filter->place)
        || filled($vm->filter->city)
        || filled($vm->filter->region)
        || filled($vm->filter->country)
        || $vm->filter->speciesIds !== []
        || $vm->filter->speciesNames !== [];
    $guidingsBreadcrumbItems = [
        [
            'label' => __('homepage.filter-fishing-near-me'),
            'url' => $hasGuidingsSearchFilter ? route('guidings.index') : null,
        ],
    ];
    if ($hasGuidingsSearchFilter) {
        $guidingsBreadcrumbItems[] = ['label' => $listingTitle, 'url' => null];
    }
@endphp

@section('title', $listingTitle)
@section('description', \Illuminate\Support\Str::limit($listingSubtitle, 155))

@section('canonical')
    <link rel="canonical" href="{{ route('guidings.index') }}" />
@endsection

@section('header_title', $listingTitle)
@section('header_sub_title', '')

@php
    $seoRobots = app(\App\Services\Seo\SeoRobotsPolicy::class);
@endphp
@if($seoRobots->shouldNoindexGuidings(request()))
    @section('meta_robots')
    <meta name="robots" content="{{ $seoRobots->robotsContentForGuidings(request()) }}" />
    @endsection
@endif

@section('css_after')
<style>
    .tours-list {
        position: relative;
        padding: 30px 0;
    }

    @media only screen and (max-width: 767px) {
        .tours-list {
            padding: 15px 0;
        }
    }
</style>
@endsection

@section('custom_style')
@include('layouts.schema.listings')

<!-- Structured Data for Search Results -->
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "ItemList",
    "name": @json($listingTitle),
    "description": @json($listingSubtitle),
    "url": @json(request()->url()),
    "numberOfItems": {{ $vm->listingsTotal }},
    "itemListElement": [
        @foreach($vm->cards as $index => $card)
        {
            "@@type": "ListItem",
            "position": {{ ($vm->listings->currentPage() - 1) * $vm->listings->perPage() + $index + 1 }},
            "item": {
                "@@type": "TouristAttraction",
                "name": @json($card['title'] ?? ''),
                "url": @json($card['url'] ?? ''),
                "location": {
                    "@@type": "Place",
                    "name": @json($card['location'] ?? '')
                },
                "offers": {
                    "@@type": "Offer",
                    "price": @json($card['price'] ?? null),
                    "priceCurrency": "EUR"
                }
            }
        }@if(!$loop->last),@endif
        @endforeach
    ]
}
</script>
@endsection

@section('content')
<div class="category-hero-page" data-category-hero-page>
    @include('pages.category.partials.hero-header', [
        'listingTitle' => $listingTitle,
        'listingSubtitle' => '',
        'searchAction' => listing_search_action(),
        'breadcrumbItems' => $guidingsBreadcrumbItems,
    ])

    <section class="tours-list">
        <div class="container">
            <x-offers.catalog-listing
                :vm="$vm"
                analytics-page="guidings-catalog"
            />
        </div>
    </section>
</div>
@endsection

@section('js_after')
@include('layouts.partials.category-hero-header-script')
@include('components.offers.partials.gallery-script')
@endsection
