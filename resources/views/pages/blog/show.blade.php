@extends('layouts.app-v2')

@php
    $title = $thread->title;
    $excerpt = $thread->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($thread->body), 160);
    $articleUrl = route($blogPrefix.'.thread.show', [$thread->slug]);
    $imageUrl = url($thread->getThumbnailPath());
    $categoryName = $thread->category ? getLocalizedValue($thread->category) : null;
    $shareText = rawurlencode($title.' — '.$articleUrl);
    $fbShare = 'https://www.facebook.com/sharer/sharer.php?u='.rawurlencode($articleUrl);
    $waShare = 'https://wa.me/?text='.$shareText;
    $xShare = 'https://twitter.com/intent/tweet?url='.rawurlencode($articleUrl).'&text='.rawurlencode($title);

    $articleJsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $title,
        'description' => strip_tags($excerpt),
        'image' => [$imageUrl],
        'datePublished' => $thread->created_at->toIso8601String(),
        'dateModified' => ($thread->updated_at ?? $thread->created_at)->toIso8601String(),
        'author' => [
            '@type' => 'Person',
            'name' => $thread->author,
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => config('app.name', 'Catch A Guide'),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => asset('assets/images/logo/CatchAGuide2_Logo_PNG.png'),
            ],
        ],
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => $articleUrl,
        ],
        'inLanguage' => app()->getLocale(),
    ];

    if ($categoryName) {
        $articleJsonLd['articleSection'] = $categoryName;
    }

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
            $thread->category ? [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $categoryName,
                'item' => route($blogPrefix.'.categories.show', $thread->category),
            ] : null,
            [
                '@type' => 'ListItem',
                'position' => $thread->category ? 4 : 3,
                'name' => $title,
                'item' => $articleUrl,
            ],
        ])),
    ];
@endphp

@section('title', $title)
@section('description', strip_tags($excerpt))
@section('header_title', $title)
@section('header_sub_title', '')

@section('canonical')
    <link rel="canonical" href="{{ $articleUrl }}" />
@endsection

@section('share_tags')
    <meta property="og:type" content="article" />
    <meta property="og:title" content="{{ $title }}" />
    <meta property="og:description" content="{{ strip_tags($excerpt) }}" />
    <meta property="og:url" content="{{ $articleUrl }}" />
    <meta property="og:image" content="{{ $imageUrl }}" />
    <meta property="article:published_time" content="{{ $thread->created_at->toIso8601String() }}" />
    <meta property="article:author" content="{{ $thread->author }}" />
    @if($categoryName)
        <meta property="article:section" content="{{ $categoryName }}" />
    @endif
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $title }}" />
    <meta name="twitter:description" content="{{ strip_tags($excerpt) }}" />
    <meta name="twitter:image" content="{{ $imageUrl }}" />
    <script type="application/ld+json">{!! json_encode($articleJsonLd, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
    <script type="application/ld+json">{!! json_encode($breadcrumbJsonLd, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endsection

@section('content')
<div
    class="cag-mag cag-mag--article"
    data-analytics-page="magazine-article"
    data-magazine-slug="{{ $thread->slug }}"
    @if($categoryName) data-magazine-category="{{ $categoryName }}" @endif
>
    <div class="cag-mag__container">
        <nav class="cag-mag-breadcrumbs" aria-label="Breadcrumb">
            <ol>
                <li><a href="{{ route('welcome') }}">{{ __('magazine.breadcrumb_home') }}</a></li>
                <li><a href="{{ route($blogPrefix.'.index') }}">{{ __('message.Magazine') }}</a></li>
                @if($thread->category)
                    <li><a href="{{ route($blogPrefix.'.categories.show', $thread->category) }}">{{ $categoryName }}</a></li>
                @endif
                <li aria-current="page">{{ $title }}</li>
            </ol>
        </nav>

        <div class="cag-mag-article">
            <div class="cag-mag-article__main">
                <div class="cag-mag-article__hero" style="background-image: url('{{ $thread->getThumbnailPath() }}');" role="img" aria-label="{{ $title }}"></div>

                <header class="cag-mag-article__header">
                    <div class="cag-mag-article__meta">
                        @if($categoryName)
                            <a class="cag-mag-chip is-active" href="{{ route($blogPrefix.'.categories.show', $thread->category) }}" data-magazine-analytics="magazine_article_category_click" data-magazine-category="{{ $categoryName }}">{{ $categoryName }}</a>
                        @endif
                        <span>{{ __('magazine.min_read', ['count' => $thread->estimatedReadingMinutes()]) }}</span>
                        <time datetime="{{ $thread->created_at->toDateString() }}">{{ __('magazine.published_on', ['date' => $thread->created_at->format('d.m.Y')]) }}</time>
                    </div>
                    <p class="cag-mag-article__author">{{ __('magazine.by_author', ['author' => $thread->author]) }}</p>
                </header>

                <div class="cag-mag-article__body news-details__content">
                    {!! $thread->body !!}
                </div>

                <div class="cag-mag-share">
                    <span class="cag-mag-share__label">{{ __('magazine.share_title') }}</span>
                    <div class="cag-mag-share__actions">
                        <a href="{{ $fbShare }}" target="_blank" rel="noopener noreferrer" aria-label="{{ __('magazine.share_facebook') }}" data-magazine-analytics="magazine_share_click" data-magazine-share="facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="{{ $waShare }}" target="_blank" rel="noopener noreferrer" aria-label="{{ __('magazine.share_whatsapp') }}" data-magazine-analytics="magazine_share_click" data-magazine-share="whatsapp"><i class="fab fa-whatsapp"></i></a>
                        <a href="{{ $xShare }}" target="_blank" rel="noopener noreferrer" aria-label="{{ __('magazine.share_x') }}" data-magazine-analytics="magazine_share_click" data-magazine-share="x"><i class="fab fa-twitter"></i></a>
                        <button type="button" class="cag-mag-share__copy" data-copy-link="{{ $articleUrl }}" data-magazine-analytics="magazine_share_click" data-magazine-share="copy" aria-label="{{ __('magazine.copy_link') }}">
                            <i class="fas fa-link"></i>
                            <span data-copy-label>{{ __('magazine.copy_link') }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <aside class="cag-mag-sidebar">
                <div class="cag-mag-sidebar__block">
                    <h2 class="cag-mag-sidebar__title">{{ __('magazine.all_categories') }}</h2>
                    <ul class="cag-mag-sidebar__list">
                        <li>
                            <a href="{{ route($blogPrefix.'.index') }}" data-magazine-analytics="magazine_category_click" data-magazine-category="all">{{ __('message.allPost') }}</a>
                        </li>
                        @foreach($categories as $category)
                            <li>
                                <a
                                    href="{{ route($blogPrefix.'.categories.show', $category) }}"
                                    data-magazine-analytics="magazine_category_click"
                                    data-magazine-category="{{ getLocalizedValue($category) }}"
                                >{{ getLocalizedValue($category) }} <span>{{ $category->threads_count }}</span></a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </aside>
        </div>

        @if(($recent_threads ?? collect())->isNotEmpty())
            <section class="cag-mag-related">
                <h2 class="cag-mag-related__title">{{ __('magazine.related_title') }}</h2>
                <div class="cag-mag-grid">
                    @foreach($recent_threads as $related)
                        @include('pages.blog.partials.card', ['thread' => $related])
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</div>

@include('pages.blog.partials.analytics')
@endsection
