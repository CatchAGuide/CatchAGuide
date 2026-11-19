{{-- Guide Area pages sit inside the public site layout, so they keep the site header and footer.
     The customer pages of /profile/* use it too: Livewire pages fill $slot, controller pages
     (customer-area.page) fill the area-main section. The sidebar follows the user. --}}
@extends('layouts.app-v2-1')

@use('App\Support\CustomerAreaNav')

@section('title', $title ?? (auth('web')->user()?->usesGuideArea() ? __('guide_area.title') : __('guide_area.customer.title')))

@section('meta_robots')
    <meta name="robots" content="NOINDEX,NOFOLLOW">
@endsection

@section('css_after')
    <link rel="stylesheet" href="{{ mix('css/guide-area.css') }}" />
@endsection

@section('content')
<div class="ga-body">
@php
    $gaUser = auth('web')->user();
    $gaName = trim(($gaUser?->firstname ?? '').' '.($gaUser?->lastname ?? ''));
    $gaInitials = mb_strtoupper(mb_substr((string) $gaUser?->firstname, 0, 1).mb_substr((string) $gaUser?->lastname, 0, 1));
    $gaSections = CustomerAreaNav::forUser($gaUser);
    $gaBecomeGuideUrl = CustomerAreaNav::becomeGuideUrl($gaUser);
    $gaNavLabel = $gaUser?->usesGuideArea() ? __('guide_area.title') : __('guide_area.customer.title');
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

        <nav aria-label="{{ $gaNavLabel }}">
            @foreach ($gaSections as $section)
                @if ($section['section'])
                    <div class="ga-sidebar__section">{{ $section['section'] }}</div>
                @endif
                <ul class="ga-sidebar__list">
                    @foreach ($section['items'] as $navItem)
                        <li>
                            <a href="{{ $navItem['url'] }}" @class(['ga-sidebar__link', 'is-active' => $navItem['active']]) @if ($navItem['active']) aria-current="page" @endif>
                                <i class="fas {{ $navItem['icon'] }}" aria-hidden="true"></i>
                                <span>{{ $navItem['label'] }}</span>
                                @if (in_array($navItem['key'], ['bookings', 'todos', 'reviews'], true))
                                    {{-- Updated live after actions: event guide-area-badges --}}
                                    <span class="ga-sidebar__badge ga-sidebar__badge--{{ $navItem['badge_tone'] }}" data-ga-badge="{{ $navItem['key'] }}" @unless ($navItem['badge']) hidden @endunless>{{ $navItem['badge'] > 99 ? '99+' : $navItem['badge'] }}</span>
                                @elseif ($navItem['dot'])
                                    <span class="ga-sidebar__dot" title="{{ __('guide_area.invoices.status.overdue') }}"></span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endforeach
        </nav>

        @if ($gaBecomeGuideUrl)
            <div class="ga-sidebar__cta">
                <div class="ga-sidebar__help-title">{{ __('guide_area.customer.become_guide.title') }}</div>
                <p>{{ __('guide_area.customer.become_guide.body') }}</p>
                <a href="{{ $gaBecomeGuideUrl }}" class="ga-btn ga-btn--coral ga-btn--block become-guide-link">
                    <i class="fas fa-fish" aria-hidden="true"></i> {{ __('guide_area.customer.become_guide.cta') }}
                </a>
            </div>
        @endif

        <div class="ga-sidebar__help">
            <div class="ga-sidebar__help-title">{{ __('guide_area.help.title') }}</div>
            <p>{{ __('guide_area.help.body') }}</p>
            <a href="{{ route('additional.contact') }}" class="ga-btn ga-btn--outline ga-btn--block">
                <i class="far fa-comment" aria-hidden="true"></i> {{ __('guide_area.help.cta') }}
            </a>
        </div>
    </aside>

    <main class="ga-main">
        {{ $slot ?? '' }}
        @yield('area-main')
    </main>
</div>

<div class="ga-toast" role="status" aria-live="polite"
     x-data="{ show: false, message: '', error: false, timer: null }"
     x-on:guide-area-toast.window="message = $event.detail.message; error = $event.detail.type === 'error'; show = true; clearTimeout(timer); timer = setTimeout(() => show = false, error ? 6000 : 3500)"
     x-on:guide-area-badges.window="Object.entries($event.detail.badges || {}).forEach(([key, count]) => document.querySelectorAll('[data-ga-badge=' + key + ']').forEach(el => { el.hidden = !(count > 0); el.textContent = count > 99 ? '99+' : count; }))"
     x-on:guide-area-scroll.window="$nextTick(() => setTimeout(() => document.getElementById($event.detail.target)?.scrollIntoView({ behavior: 'smooth', block: 'start' }), 150))"
     x-show="show" x-transition.opacity x-cloak :class="{ 'ga-toast--error': error }">
    <i class="fas" :class="error ? 'fa-exclamation-circle' : 'fa-check-circle'" aria-hidden="true"></i> <span x-text="message"></span>
</div>
</div>
@endsection
