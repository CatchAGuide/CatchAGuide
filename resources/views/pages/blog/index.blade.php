@extends('layouts.app-v2')

@php
    $metaTitle = __('message.magazine_meta_title');
    $metaDescription = __('message.magazine_meta_description');

    if (!empty($search)) {
        $metaTitle = __('magazine.search_meta_title', ['query' => $search]);
    } elseif (!empty($activeCategory)) {
        $catLabel = getLocalizedValue($activeCategory);
        $metaTitle = __('magazine.category_meta_title', ['category' => $catLabel]);
        $metaDescription = __('magazine.category_meta_description', ['category' => $catLabel]);
    }

    $ogImage = null;
    if (!empty($featured)) {
        $ogImage = url($featured->getThumbnailPath());
    } elseif ($threads->isNotEmpty()) {
        $ogImage = url($threads->first()->getThumbnailPath());
    }

    $listItems = collect();
    if (!empty($featured)) {
        $listItems->push($featured);
    }
    $listItems = $listItems->concat($threads->items())->values();

    $collectionJsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        'name' => $metaTitle,
        'description' => $metaDescription,
        'url' => url()->current(),
        'isPartOf' => [
            '@type' => 'WebSite',
            'name' => config('app.name', 'Catch A Guide'),
            'url' => url('/'),
        ],
        'mainEntity' => [
            '@type' => 'ItemList',
            'numberOfItems' => $totalCount ?? $listItems->count(),
            'itemListElement' => $listItems->take(20)->values()->map(function ($thread, $index) use ($blogPrefix) {
                return [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'url' => route($blogPrefix.'.thread.show', [$thread->slug]),
                    'name' => $thread->title,
                ];
            })->all(),
        ],
    ];

    $breadcrumbJsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => array_values(array_filter([
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => __('magazine.breadcrumb_home'),
                'item' => route('welcome'),
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => __('message.Magazine'),
                'item' => route($blogPrefix.'.index'),
            ],
            !empty($activeCategory) ? [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => getLocalizedValue($activeCategory),
                'item' => route($blogPrefix.'.categories.show', $activeCategory),
            ] : null,
        ])),
    ];
@endphp

@section('title', $metaTitle)
@section('description', $metaDescription)
@section('header_title', __('message.Magazine'))
@section('header_sub_title', !empty($activeCategory) ? getLocalizedValue($activeCategory) : __('message.Magazine_subtitle'))

@section('canonical')
    <link rel="canonical" href="{{ strtok(url()->current(), '?') }}" />
@endsection

@section('share_tags')
    <meta property="og:type" content="website" />
    <meta property="og:title" content="{{ $metaTitle }}" />
    <meta property="og:description" content="{{ $metaDescription }}" />
    <meta property="og:url" content="{{ strtok(url()->current(), '?') }}" />
    @if($ogImage)
        <meta property="og:image" content="{{ $ogImage }}" />
    @endif
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $metaTitle }}" />
    <meta name="twitter:description" content="{{ $metaDescription }}" />
    @if($ogImage)
        <meta name="twitter:image" content="{{ $ogImage }}" />
    @endif
    <script type="application/ld+json">{!! json_encode($collectionJsonLd, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
    <script type="application/ld+json">{!! json_encode($breadcrumbJsonLd, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endsection

@section('content')
<div class="cag-mag" data-analytics-page="magazine-index">
    <div class="cag-mag__container">
        <nav class="cag-mag-breadcrumbs" aria-label="Breadcrumb">
            <ol>
                <li><a href="{{ route('welcome') }}">{{ __('magazine.breadcrumb_home') }}</a></li>
                <li><a href="{{ route($blogPrefix.'.index') }}">{{ __('message.Magazine') }}</a></li>
                @if(!empty($activeCategory))
                    <li aria-current="page">{{ getLocalizedValue($activeCategory) }}</li>
                @endif
            </ol>
        </nav>

        @include('pages.blog.partials.filters')

        @if(!empty($featured))
            @include('pages.blog.partials.featured', ['featured' => $featured])
        @endif

        @if($threads->isNotEmpty())
            <div class="cag-mag-grid">
                @foreach($threads as $thread)
                    @include('pages.blog.partials.card', ['thread' => $thread])
                @endforeach
            </div>
            <div class="cag-mag-pagination">
                {{ $threads->links('vendor.pagination.default') }}
            </div>
        @elseif(empty($featured))
            <div class="cag-mag-empty">
                <h2>{{ __('magazine.empty_title') }}</h2>
                <p>{{ __('magazine.empty_text') }}</p>
                <a href="{{ route($blogPrefix.'.index') }}" class="cag-mag-empty__btn" data-magazine-analytics="magazine_empty_reset">
                    {{ __('magazine.empty_reset') }}
                </a>
            </div>
        @endif
    </div>
</div>

@include('pages.blog.partials.analytics')
@endsection
