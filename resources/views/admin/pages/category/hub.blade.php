@extends('admin.layouts.app')

@section('title', __('admin.category_pages.hub_title'))

@section('custom_style')
<link rel="stylesheet" href="{{ asset('css/admin-category-pages.css') }}">
@endsection

@section('content')
<div class="side-app category-pages-hub">
    <div class="main-container container-fluid">
        <div class="category-pages-hub__hero">
            <div>
                <p class="category-pages-hub__eyebrow">Content</p>
                <h1 class="category-pages-hub__title">{{ __('admin.category_pages.hub_title') }}</h1>
                <p class="category-pages-hub__intro">{{ __('admin.category_pages.hub_intro') }}</p>
            </div>
        </div>

        <div class="row g-4">
            @foreach($cards as $card)
                <div class="col-xl-4 col-lg-6">
                    <article class="category-pages-card @if(!empty($card['legacy'])) category-pages-card--legacy @endif">
                        <div class="category-pages-card__head">
                            <h2 class="category-pages-card__title">{{ $card['label'] }}</h2>
                            @if(!empty($card['legacy']))
                                <span class="category-pages-card__badge">{{ __('admin.category_pages.legacy') }}</span>
                            @endif
                        </div>
                        <p class="category-pages-card__count">{{ number_format($card['count']) }} entries</p>
                        <div class="category-pages-card__scopes">
                            <span class="category-pages-card__scopes-label">{{ __('admin.category_pages.scopes_label') }}</span>
                            @foreach($card['scopes'] as $scope)
                                <span class="category-pages-card__chip">{{ \App\Domain\CategoryPage\CategoryPageScope::label($scope) }}</span>
                            @endforeach
                        </div>
                        <a href="{{ route($card['route']) }}" class="btn btn-primary category-pages-card__cta">
                            {{ __('admin.category_pages.manage') }}
                        </a>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
