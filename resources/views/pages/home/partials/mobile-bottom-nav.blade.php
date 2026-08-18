<nav class="cag-home-bottom-nav d-md-none" aria-label="{{ __('homepage.mobile_nav_label') }}">
    @foreach ($siteBottomNavLinks as $item)
        <a href="{{ $item['url'] }}"
           @class(['cag-home-bottom-nav__item', 'is-active' => $item['active']])
           @if ($item['active']) aria-current="page" @endif
           @if ($item['opens_login']) data-bs-toggle="modal" data-bs-target="#loginModal" @endif
        >
            <i class="{{ $item['icon'] }}" aria-hidden="true"></i>
            <span>{{ $item['label'] }}</span>
        </a>
    @endforeach
</nav>
