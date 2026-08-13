<nav class="cag-home-bottom-nav d-md-none" aria-label="{{ __('homepage.mobile_nav_label') }}">
    <a href="{{ route('guidings.landing') }}" class="cag-home-bottom-nav__item is-active">
        <i class="fas fa-search" aria-hidden="true"></i>
        <span>{{ __('homepage.mobile_nav_explore') }}</span>
    </a>
    <a href="{{ auth()->check() ? route('profile.bookings') : '#' }}"
       @guest data-bs-toggle="modal" data-bs-target="#loginModal" @endguest
       class="cag-home-bottom-nav__item">
        <i class="far fa-calendar" aria-hidden="true"></i>
        <span>{{ __('homepage.mobile_nav_bookings') }}</span>
    </a>
    <a href="{{ auth()->check() ? route('profile.favoriteguides') : '#' }}"
       @guest data-bs-toggle="modal" data-bs-target="#loginModal" @endguest
       class="cag-home-bottom-nav__item">
        <i class="far fa-heart" aria-hidden="true"></i>
        <span>{{ __('homepage.mobile_nav_saved') }}</span>
    </a>
    <a href="{{ auth()->check() ? route('profile.index') : '#' }}"
       @guest data-bs-toggle="modal" data-bs-target="#loginModal" @endguest
       class="cag-home-bottom-nav__item">
        <i class="far fa-user" aria-hidden="true"></i>
        <span>{{ __('homepage.mobile_nav_profile') }}</span>
    </a>
</nav>
