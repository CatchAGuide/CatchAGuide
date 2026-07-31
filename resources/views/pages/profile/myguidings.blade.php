@extends('pages.profile.layouts.profile')
@section('title', __('profile.myGuides'))

@section('profile-content')

@php
    $statusCounts = $statusCounts ?? ['all' => count($guidings), 'active' => 0, 'inactive' => 0, 'draft' => 0];
    $activeStatus = $activeStatus ?? 'all';
    $searchTerm = $searchTerm ?? '';
    $statusTabs = [
        'all' => ['label' => __('profile.all_tours'), 'icon' => 'fa-layer-group'],
        'active' => ['label' => __('profile.active'), 'icon' => 'fa-check-circle'],
        'inactive' => ['label' => __('profile.inactive'), 'icon' => 'fa-pause-circle'],
        'draft' => ['label' => __('profile.draft'), 'icon' => 'fa-pencil-ruler'],
    ];
    $hasFilters = $activeStatus !== 'all' || $searchTerm !== '';
@endphp

<style>
    /* Hide the inherited breadcrumb header of the profile layout (as before) */
    .page-header {
        display: none !important;
    }

    .guidings-page {
        --gp-ink: #313041;
        --gp-ink-soft: #5b5a6b;
        --gp-muted: #8a8a99;
        --gp-accent: #e8604c;
        --gp-line: #e8eaef;
        --gp-surface: #ffffff;
        --gp-canvas: #f6f7f9;
        color: var(--gp-ink);
    }

    /* ---------- Page header ---------- */
    .guidings-page .gp-header {
        background: linear-gradient(135deg, #313041 0%, #252238 100%);
        border-radius: 14px;
        padding: 26px 28px;
        margin-bottom: 18px;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
    }

    .guidings-page .gp-header h1 {
        color: #fff;
        font-size: 1.6rem;
        font-weight: 700;
        margin: 0 0 6px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .guidings-page .gp-header h1 i {
        color: var(--gp-accent);
        font-size: 1.35rem;
    }

    .guidings-page .gp-header p {
        margin: 0;
        color: rgba(255, 255, 255, .72);
        font-size: .95rem;
    }

    .guidings-page .gp-create-btn {
        background: var(--gp-accent);
        color: #fff;
        border-radius: 8px;
        padding: 11px 20px;
        font-weight: 600;
        font-size: .9rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
        transition: background .2s ease, transform .2s ease;
    }

    .guidings-page .gp-create-btn:hover {
        background: #d54e37;
        color: #fff;
        transform: translateY(-1px);
    }

    /* ---------- Toolbar ---------- */
    .guidings-page .gp-toolbar {
        background: var(--gp-surface);
        border: 1px solid var(--gp-line);
        border-radius: 12px;
        padding: 14px 16px;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
    }

    .guidings-page .gp-tabs {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .guidings-page .gp-tab {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 14px;
        border-radius: 8px;
        border: 1px solid var(--gp-line);
        background: var(--gp-canvas);
        color: var(--gp-ink-soft);
        font-size: .84rem;
        font-weight: 600;
        text-decoration: none;
        transition: all .2s ease;
    }

    .guidings-page .gp-tab:hover {
        border-color: #cfd3dc;
        color: var(--gp-ink);
    }

    .guidings-page .gp-tab.is-active {
        background: var(--gp-ink);
        border-color: var(--gp-ink);
        color: #fff;
    }

    .guidings-page .gp-tab-count {
        background: rgba(49, 48, 65, .08);
        border-radius: 20px;
        padding: 1px 8px;
        font-size: .75rem;
        font-weight: 700;
        min-width: 24px;
        text-align: center;
    }

    .guidings-page .gp-tab.is-active .gp-tab-count {
        background: rgba(255, 255, 255, .2);
    }

    .guidings-page .gp-search {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
    }

    .guidings-page .gp-search-field {
        position: relative;
    }

    .guidings-page .gp-search-field i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gp-muted);
        font-size: .82rem;
    }

    .guidings-page .gp-search input {
        border: 1px solid var(--gp-line);
        border-radius: 8px;
        padding: 9px 12px 9px 34px;
        font-size: .86rem;
        min-width: 230px;
        background: var(--gp-canvas);
        color: var(--gp-ink);
        outline: none;
        transition: border-color .2s ease, background .2s ease;
    }

    .guidings-page .gp-search input:focus {
        border-color: var(--gp-ink);
        background: #fff;
    }

    .guidings-page .gp-search-submit {
        border: 1px solid var(--gp-ink);
        background: var(--gp-ink);
        color: #fff;
        border-radius: 8px;
        padding: 9px 16px;
        font-size: .84rem;
        font-weight: 600;
        transition: background .2s ease;
    }

    .guidings-page .gp-search-submit:hover {
        background: #252238;
    }

    .guidings-page .gp-search-reset {
        color: var(--gp-muted);
        font-size: .84rem;
        text-decoration: none;
        padding: 0 4px;
    }

    .guidings-page .gp-search-reset:hover {
        color: var(--gp-accent);
    }

    /* ---------- Card ---------- */
    .guidings-page .gp-card {
        display: grid;
        grid-template-columns: 260px minmax(0, 1fr) 210px;
        background: var(--gp-surface);
        border: 1px solid var(--gp-line);
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 14px;
        transition: box-shadow .2s ease, border-color .2s ease;
    }

    .guidings-page .gp-card:hover {
        border-color: #d8dce4;
        box-shadow: 0 6px 20px rgba(31, 33, 46, .08);
    }

    .guidings-page .gp-card.is-draft {
        border-left: 4px solid var(--gp-accent);
    }

    /* Media */
    .guidings-page .gp-media {
        position: relative;
        min-height: 210px;
        background: #eceef2;
        overflow: hidden;
    }

    .guidings-page .gp-media .carousel,
    .guidings-page .gp-media .carousel-inner,
    .guidings-page .gp-media .carousel-item {
        height: 100%;
    }

    .guidings-page .gp-media .carousel {
        position: absolute;
        inset: 0;
    }

    .guidings-page .gp-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        display: block;
    }

    .guidings-page .gp-media-empty {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 6px;
        color: #a9adb8;
        font-size: .78rem;
    }

    .guidings-page .gp-media-empty i {
        font-size: 1.6rem;
    }

    .guidings-page .gp-media .carousel-control-prev,
    .guidings-page .gp-media .carousel-control-next {
        width: 30px;
        height: 30px;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(20, 20, 30, .55);
        border-radius: 50%;
        opacity: 0;
        transition: opacity .2s ease;
    }

    .guidings-page .gp-media .carousel-control-prev { left: 8px; }
    .guidings-page .gp-media .carousel-control-next { right: 8px; }

    .guidings-page .gp-card:hover .carousel-control-prev,
    .guidings-page .gp-card:hover .carousel-control-next,
    .guidings-page .gp-media .carousel-control-prev:focus,
    .guidings-page .gp-media .carousel-control-next:focus {
        opacity: 1;
    }

    .guidings-page .gp-media .carousel-control-prev-icon,
    .guidings-page .gp-media .carousel-control-next-icon {
        width: 12px;
        height: 12px;
    }

    .guidings-page .gp-status {
        position: absolute;
        top: 12px;
        left: 12px;
        z-index: 3;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 11px;
        border-radius: 20px;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .3px;
        text-transform: uppercase;
        color: #fff;
        backdrop-filter: blur(2px);
    }

    .guidings-page .gp-status i { font-size: .6rem; }
    .guidings-page .gp-status.is-active { background: rgba(33, 136, 56, .92); }
    .guidings-page .gp-status.is-inactive { background: rgba(90, 92, 105, .92); }
    .guidings-page .gp-status.is-draft { background: rgba(232, 96, 76, .95); }

    .guidings-page .gp-price {
        position: absolute;
        bottom: 12px;
        left: 12px;
        z-index: 3;
        background: rgba(20, 20, 30, .78);
        color: #fff;
        border-radius: 8px;
        padding: 6px 11px;
        font-size: .8rem;
        font-weight: 600;
        line-height: 1.2;
    }

    .guidings-page .gp-price span {
        font-size: .68rem;
        font-weight: 500;
        opacity: .8;
        text-transform: uppercase;
        letter-spacing: .3px;
        display: block;
    }

    /* Body */
    .guidings-page .gp-body {
        padding: 18px 20px;
        min-width: 0;
    }

    .guidings-page .gp-title {
        font-size: 1.08rem;
        font-weight: 700;
        line-height: 1.35;
        margin: 0 0 4px;
    }

    .guidings-page .gp-title a {
        color: var(--gp-ink);
        text-decoration: none;
    }

    .guidings-page .gp-title a:hover { color: var(--gp-accent); }

    .guidings-page .gp-location {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: .84rem;
        color: var(--gp-muted);
        margin: 0 0 14px;
    }

    .guidings-page .gp-location i { color: var(--gp-accent); }

    .guidings-page .gp-meta {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px 18px;
        margin: 0 0 14px;
        padding: 0;
        list-style: none;
    }

    .guidings-page .gp-meta li {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        font-size: .84rem;
        color: var(--gp-ink-soft);
        min-width: 0;
    }

    .guidings-page .gp-meta img {
        width: 15px;
        height: 15px;
        margin-top: 2px;
        opacity: .7;
        flex-shrink: 0;
    }

    .guidings-page .gp-meta-value {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .guidings-page .gp-inclusions {
        border-top: 1px solid var(--gp-line);
        padding-top: 12px;
    }

    .guidings-page .gp-inclusions-label {
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: var(--gp-muted);
        margin-bottom: 8px;
    }

    .guidings-page .gp-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .guidings-page .gp-tag {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: var(--gp-canvas);
        border: 1px solid var(--gp-line);
        border-radius: 6px;
        padding: 4px 9px;
        font-size: .76rem;
        color: var(--gp-ink-soft);
    }

    .guidings-page .gp-tag i {
        color: #28a745;
        font-size: .66rem;
    }

    .guidings-page .gp-tag.is-more {
        background: rgba(232, 96, 76, .08);
        border-color: rgba(232, 96, 76, .25);
        color: var(--gp-accent);
        font-weight: 600;
    }

    /* Actions */
    .guidings-page .gp-actions {
        padding: 18px;
        border-left: 1px solid var(--gp-line);
        background: #fbfbfc;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 8px;
    }

    .guidings-page .gp-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        width: 100%;
        padding: 9px 12px;
        border-radius: 8px;
        font-size: .82rem;
        font-weight: 600;
        text-decoration: none;
        border: 1px solid transparent;
        white-space: nowrap;
        transition: all .2s ease;
    }

    .guidings-page .gp-btn i { font-size: .8rem; }

    .guidings-page .gp-btn-primary {
        background: var(--gp-ink);
        border-color: var(--gp-ink);
        color: #fff;
    }

    .guidings-page .gp-btn-primary:hover {
        background: #252238;
        color: #fff;
    }

    .guidings-page .gp-btn-ghost {
        background: #fff;
        border-color: #d8dce4;
        color: var(--gp-ink);
    }

    .guidings-page .gp-btn-ghost:hover {
        border-color: var(--gp-ink);
        background: var(--gp-canvas);
        color: var(--gp-ink);
    }

    .guidings-page .gp-btn-accent {
        background: var(--gp-accent);
        border-color: var(--gp-accent);
        color: #fff;
    }

    .guidings-page .gp-btn-accent:hover {
        background: #d54e37;
        color: #fff;
    }

    .guidings-page .gp-btn-success {
        background: #ffffff;
        border-color: #28a745;
        color: #1e7e34;
    }

    .guidings-page .gp-btn-success:hover {
        background: #28a745;
        color: #fff;
    }

    .guidings-page .gp-btn-danger {
        background: #fff;
        border-color: #e2b5b9;
        color: #b02a37;
    }

    .guidings-page .gp-btn-danger:hover {
        background: #dc3545;
        border-color: #dc3545;
        color: #fff;
    }

    /* ---------- Empty state & pagination ---------- */
    .guidings-page .gp-empty {
        background: var(--gp-surface);
        border: 1px solid var(--gp-line);
        border-radius: 12px;
        padding: 56px 24px;
        text-align: center;
    }

    .guidings-page .gp-empty-icon {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: rgba(232, 96, 76, .1);
        color: var(--gp-accent);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 16px;
    }

    .guidings-page .gp-empty h4 {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--gp-ink);
        margin-bottom: 8px;
    }

    .guidings-page .gp-empty p {
        color: var(--gp-muted);
        font-size: .92rem;
        margin-bottom: 22px;
    }

    .guidings-page .gp-pagination {
        display: flex;
        justify-content: center;
        margin-top: 24px;
    }

    .guidings-page .gp-result-count {
        font-size: .82rem;
        color: var(--gp-muted);
        margin-bottom: 12px;
    }

    /* ---------- Responsive ---------- */
    @media (max-width: 991px) {
        .guidings-page .gp-card {
            grid-template-columns: 200px minmax(0, 1fr);
        }

        .guidings-page .gp-actions {
            grid-column: 1 / -1;
            border-left: none;
            border-top: 1px solid var(--gp-line);
            flex-direction: row;
            padding: 14px 18px;
        }

        .guidings-page .gp-meta {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767px) {
        .guidings-page .gp-header {
            padding: 22px 20px;
        }

        .guidings-page .gp-header h1 { font-size: 1.35rem; }

        .guidings-page .gp-create-btn { width: 100%; justify-content: center; }

        .guidings-page .gp-card {
            grid-template-columns: minmax(0, 1fr);
        }

        .guidings-page .gp-media { min-height: 180px; }

        .guidings-page .gp-body { padding: 16px; }

        .guidings-page .gp-toolbar { flex-direction: column; align-items: stretch; }

        .guidings-page .gp-tabs { justify-content: space-between; }

        .guidings-page .gp-tab { flex: 1; justify-content: center; }

        .guidings-page .gp-search { flex-wrap: wrap; }

        .guidings-page .gp-search-field { flex: 1; }

        .guidings-page .gp-search input { min-width: 0; width: 100%; }

        .guidings-page .gp-actions { flex-wrap: wrap; }

        .guidings-page .gp-btn { flex: 1 1 140px; }
    }
</style>

<div class="guidings-page">
    @if(session('message'))
        <div class="alert alert-warning border-0 mb-3" role="alert">
            <i class="fas fa-info-circle me-2"></i>{{ session('message') }}
        </div>
    @endif

    @if(request('notice') === 'pending_publish')
        <div class="alert alert-warning border-0 mb-3" role="alert">
            <i class="fas fa-hourglass-half me-2"></i>
            {{ __('profile.guiding_saved_as_draft_pending_guide') }}
        </div>
    @endif

    <div id="guiding-form-notice-container"></div>

    <div class="gp-header">
        <div>
            <h1><i class="fas fa-fish"></i>@lang('profile.myGuides')</h1>
            <p>{{ __('profile.manage_fishing_experiences') }}</p>
        </div>
        @if(auth()->user()->canAccessGuideDashboard())
            <a href="{{ route('profile.newguiding') }}" class="gp-create-btn">
                <i class="fas fa-plus"></i>{{ __('profile.create_new_tour') }}
            </a>
        @endif
    </div>

    @if($statusCounts['all'] > 0)
        <div class="gp-toolbar">
            <div class="gp-tabs">
                @foreach($statusTabs as $key => $tab)
                    <a class="gp-tab {{ $activeStatus === $key ? 'is-active' : '' }}"
                       href="{{ route('profile.myguidings', array_filter(['status' => $key === 'all' ? null : $key, 'search' => $searchTerm ?: null])) }}">
                        <i class="fas {{ $tab['icon'] }}"></i>
                        <span>{{ $tab['label'] }}</span>
                        <span class="gp-tab-count">{{ $statusCounts[$key] ?? 0 }}</span>
                    </a>
                @endforeach
            </div>

            <form class="gp-search" method="GET" action="{{ route('profile.myguidings') }}">
                @if($activeStatus !== 'all')
                    <input type="hidden" name="status" value="{{ $activeStatus }}">
                @endif
                <div class="gp-search-field">
                    <i class="fas fa-search"></i>
                    <input type="search" name="search" value="{{ $searchTerm }}"
                           placeholder="{{ __('profile.search_tours') }}" aria-label="{{ __('profile.search_tours') }}">
                </div>
                <button type="submit" class="gp-search-submit">{{ __('message.Search') }}</button>
                @if($searchTerm !== '')
                    <a class="gp-search-reset" href="{{ route('profile.myguidings', $activeStatus === 'all' ? [] : ['status' => $activeStatus]) }}">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </form>
        </div>
    @endif

    @if(count($guidings))
        <div class="gp-result-count">
            {{ trans_choice('profile.tours_count', $guidings->total(), ['count' => $guidings->total()]) }}
        </div>

        @foreach($guidings as $guiding)
            @php
                $isDraft = $guiding->status == 2;
                $isActive = $guiding->status == 1;
                $galleryImages = get_galleries_image_link($guiding);
                $targetFish = collect($guiding->getTargetFishNames())->pluck('name')->filter()->all();
                $inclusions = $guiding->getInclusionNames() ?: [];
                $maxToShow = 3;
            @endphp

            <article class="gp-card {{ $isDraft ? 'is-draft' : '' }}">
                <div class="gp-media">
                    <span class="gp-status {{ $isDraft ? 'is-draft' : ($isActive ? 'is-active' : 'is-inactive') }}">
                        <i class="fas fa-circle"></i>
                        {{ $isDraft ? __('profile.draft') : ($isActive ? __('profile.active') : __('profile.inactive')) }}
                    </span>

                    @if(count($galleryImages))
                        <div id="guidingGallery-{{ $guiding->id }}" class="carousel slide" data-bs-ride="carousel" data-bs-interval="false">
                            <div class="carousel-inner">
                                @foreach($galleryImages as $index => $gallery_image_link)
                                    <div class="carousel-item @if($index == 0) active @endif">
                                        <img src="{{ $gallery_image_link }}" alt="{{ $guiding->title }}">
                                    </div>
                                @endforeach
                            </div>

                            @if(count($galleryImages) > 1)
                                <button class="carousel-control-prev" type="button" data-bs-target="#guidingGallery-{{ $guiding->id }}" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#guidingGallery-{{ $guiding->id }}" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            @endif
                        </div>
                    @else
                        <div class="gp-media-empty">
                            <i class="fas fa-image"></i>
                        </div>
                    @endif

                    <div class="gp-price">
                        <span>@lang('message.from')</span>
                        {{ $guiding->getLowestPrice() }}&euro;
                    </div>
                </div>

                <div class="gp-body">
                    <h2 class="gp-title">
                        <a href="{{ route('guidings.show', [$guiding->id, $guiding->slug]) }}">{{ $guiding->title }}</a>
                    </h2>

                    <p class="gp-location">
                        <i class="fas fa-map-marker-alt"></i>{{ translate($guiding->location) }}
                    </p>

                    <ul class="gp-meta">
                        <li>
                            <img src="{{ asset('assets/images/icons/clock-new.svg') }}" alt="" />
                            <span class="gp-meta-value">{{ $guiding->duration }} @lang('guidings.hours')</span>
                        </li>
                        <li>
                            <img src="{{ asset('assets/images/icons/user-new.svg') }}" alt="" />
                            <span class="gp-meta-value">{{ $guiding->max_guests }} @lang('message.persons')</span>
                        </li>
                        <li>
                            <img src="{{ asset('assets/images/icons/fish-new.svg') }}" alt="" />
                            <span class="gp-meta-value" title="{{ implode(', ', $targetFish) }}">
                                {{ !empty($targetFish) ? implode(', ', $targetFish) : 'Various fish' }}
                            </span>
                        </li>
                        <li>
                            <img src="{{ asset('assets/images/icons/fishing-tool-new.svg') }}" alt="" />
                            <span class="gp-meta-value">
                                {{ $guiding->is_boat ? ($guiding->boatType && $guiding->boatType->name !== null ? $guiding->boatType->name : __('guidings.boat')) : __('guidings.shore') }}
                            </span>
                        </li>
                    </ul>

                    @if(!empty($inclusions))
                        <div class="gp-inclusions">
                            <div class="gp-inclusions-label">@lang('guidings.Whats_Included')</div>
                            <div class="gp-tags">
                                @foreach($inclusions as $index => $inclusion)
                                    @if($index < $maxToShow)
                                        <span class="gp-tag">
                                            <i class="fa fa-check"></i>{{ $inclusion['name'] }}
                                        </span>
                                    @endif
                                @endforeach

                                @if(count($inclusions) > $maxToShow)
                                    <span class="gp-tag is-more">
                                        +{{ count($inclusions) - $maxToShow }} {{ __('profile.more') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <div class="gp-actions">
                    <a class="gp-btn {{ $isDraft ? 'gp-btn-accent' : 'gp-btn-primary' }}" href="{{ route('guidings.edit_newguiding', $guiding->id) }}">
                        <i class="fas {{ $isDraft ? 'fa-pencil-ruler' : 'fa-edit' }}"></i>{{ $isDraft ? __('profile.finalize_tour') : __('profile.edit') }}
                    </a>

                    <a class="gp-btn gp-btn-ghost" href="{{ route('guidings.show', [$guiding->id, $guiding->slug]) }}">
                        <i class="fas fa-eye"></i>@lang('profile.view')
                    </a>

                    @if(! $isDraft)
                        @if($isActive)
                            <a class="gp-btn gp-btn-danger" href="{{ route('profile.guiding.deactivate', $guiding) }}">
                                <i class="fas fa-pause"></i>@lang('profile.deactivateGuide')
                            </a>
                        @else
                            <a class="gp-btn gp-btn-success" href="{{ route('profile.guiding.activate', $guiding) }}">
                                <i class="fas fa-play"></i>@lang('profile.activateGuide')
                            </a>
                        @endif
                    @endif
                </div>
            </article>
        @endforeach

        <div class="gp-pagination">
            {!! $guidings->links('vendor.pagination.default') !!}
        </div>
    @elseif($hasFilters)
        <div class="gp-empty">
            <div class="gp-empty-icon"><i class="fas fa-search"></i></div>
            <h4>@lang('profile.no_tours_found')</h4>
            <p>@lang('profile.adjust_search_filters')</p>
            <a href="{{ route('profile.myguidings') }}" class="gp-btn gp-btn-ghost d-inline-flex w-auto">
                <i class="fas fa-undo"></i>@lang('profile.all_tours')
            </a>
        </div>
    @else
        <div class="gp-empty">
            <div class="gp-empty-icon"><i class="fas fa-fish"></i></div>
            <h4>@lang('profile.notcreated')</h4>
            <p>@lang('profile.lets-change')</p>
            <a href="{{ route('profile.newguiding') }}" class="gp-btn gp-btn-accent d-inline-flex w-auto">
                <i class="fas fa-plus"></i>@lang('profile.creategiud')
            </a>
        </div>
    @endif
</div>
@endsection

@section('js_after')
@parent
<script>
document.addEventListener('DOMContentLoaded', function () {
    try {
        const message = sessionStorage.getItem('guidingFormNotice');
        if (!message) return;
        sessionStorage.removeItem('guidingFormNotice');
        const container = document.getElementById('guiding-form-notice-container');
        if (!container) return;
        container.innerHTML = '<div class="alert alert-warning border-0 mb-4" role="alert"><i class="fas fa-hourglass-half me-2"></i>' + message + '</div>';
    } catch (e) { /* ignore */ }
});
</script>
@endsection
