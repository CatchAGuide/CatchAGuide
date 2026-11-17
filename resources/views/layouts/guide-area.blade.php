<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="NOINDEX,NOFOLLOW">
    <title>{{ $title ?? __('guide_area.title') }} · {{ config('app.name', 'Catch A Guide') }}</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @include('layouts.partials.tag-manager-head')

    <link rel="stylesheet" href="{{ asset('assets/vendors/fontawesome/css/all.min.css') }}" />
    <link rel="stylesheet" href="{{ mix('css/guide-area.css') }}" />
</head>
<body class="ga-body">
@php
    $gaUser = auth('web')->user();
    $gaName = trim(($gaUser?->firstname ?? '').' '.($gaUser?->lastname ?? ''));
    $gaInitials = mb_strtoupper(mb_substr((string) $gaUser?->firstname, 0, 1).mb_substr((string) $gaUser?->lastname, 0, 1));
@endphp

<div class="ga-shell" x-data="{ navOpen: false }">
    <button type="button" class="ga-nav-toggle" @click="navOpen = !navOpen" :aria-expanded="navOpen.toString()" aria-controls="ga-sidebar">
        <i class="fas fa-bars" aria-hidden="true"></i>
        <span>{{ __('guide_area.nav.menu') }}</span>
    </button>

    <aside id="ga-sidebar" class="ga-sidebar" :class="{ 'is-open': navOpen }">
        <div class="ga-sidebar__user">
            <span class="ga-avatar ga-avatar--sm" aria-hidden="true">{{ $gaInitials }}</span>
            <div class="ga-sidebar__user-text">
                <div class="ga-sidebar__name">{{ $gaName }}</div>
                <a href="{{ route('welcome') }}" class="ga-sidebar__home">{{ __('guide_area.nav.back_to_site') }}</a>
            </div>
        </div>

        <nav aria-label="{{ __('guide_area.title') }}">
            @foreach (\App\Support\GuideAreaNav::sections() as $section)
                @if ($section['section'])
                    <div class="ga-sidebar__section">{{ $section['section'] }}</div>
                @endif
                <ul class="ga-sidebar__list">
                    @foreach ($section['items'] as $navItem)
                        <li>
                            <a href="{{ $navItem['url'] }}" @class(['ga-sidebar__link', 'is-active' => $navItem['active']]) @if ($navItem['active']) aria-current="page" @endif>
                                <i class="fas {{ $navItem['icon'] }}" aria-hidden="true"></i>
                                <span>{{ $navItem['label'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endforeach
        </nav>

        <div class="ga-sidebar__help">
            <div class="ga-sidebar__help-title">{{ __('guide_area.help.title') }}</div>
            <p>{{ __('guide_area.help.body') }}</p>
            <a href="{{ route('additional.contact') }}" class="ga-btn ga-btn--outline ga-btn--block">
                <i class="far fa-comment" aria-hidden="true"></i> {{ __('guide_area.help.cta') }}
            </a>
        </div>
    </aside>

    <main class="ga-main">
        {{ $slot }}
    </main>
</div>

<div class="ga-toast" role="status" aria-live="polite"
     x-data="{ show: false, message: '', timer: null }"
     x-on:guide-area-toast.window="message = $event.detail.message; show = true; clearTimeout(timer); timer = setTimeout(() => show = false, 3500)"
     x-show="show" x-transition.opacity x-cloak>
    <i class="fas fa-check-circle" aria-hidden="true"></i> <span x-text="message"></span>
</div>
</body>
</html>
