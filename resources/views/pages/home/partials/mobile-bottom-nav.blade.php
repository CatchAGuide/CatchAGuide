<nav class="cag-home-bottom-nav d-md-none" aria-label="{{ __('homepage.mobile_nav_label') }}">
    @foreach ($siteBottomNavLinks as $item)
        @php
            $navIcon = match ($item['key'] ?? '') {
                'offers' => 'nav-grid',
                'tours' => 'nav-rod',
                'vacations' => 'nav-camp',
                'destinations' => 'nav-pin',
                default => 'nav-user',
            };
        @endphp
        <a href="{{ $item['url'] }}"
           @class(['cag-home-bottom-nav__item', 'is-active' => $item['active']])
           @if ($item['active']) aria-current="page" @endif
           @if ($item['opens_login']) data-bs-toggle="modal" data-bs-target="#loginModal" @endif
        >
            @include('pages.home.partials.cag-icon', ['name' => $navIcon, 'size' => 19])
            <span>{{ $item['label'] }}</span>
        </a>
    @endforeach
</nav>
