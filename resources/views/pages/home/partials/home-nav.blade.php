<header class="cag-home-nav">
    <div class="cag-home-nav__inner">
        <a href="{{ route('welcome') }}" class="cag-home-nav__brand" aria-label="Catch A Guide">
            <img
                src="{{ asset('assets/images/logo/CatchAGuide2_Logo_JPEG.jpg') }}"
                alt="Catch A Guide"
                class="cag-home-nav__logo cag-home-nav__logo--color"
                width="200"
                height="48"
            >
            <img
                src="{{ asset('assets/images/logo/CatchAGuide2_Logo_PNG.png') }}"
                alt="Catch A Guide"
                class="cag-home-nav__logo cag-home-nav__logo--light"
                width="200"
                height="48"
            >
        </a>

        <nav class="cag-home-nav__links d-none d-md-flex" aria-label="Primary">
            <a href="{{ route('guidings.index') }}">@lang('homepage.filter-fishing-near-me')</a>
            <a href="{{ route('vacations.index') }}">@lang('homepage.header-vacations')</a>
        </nav>

        <div class="cag-home-nav__actions">
            <form action="{{ route('language.switch') }}" method="POST" class="cag-home-nav__lang d-none d-md-flex" id="home-language-form">
                @csrf
                <select name="language" class="selectpicker header-language-select" data-width="fit" data-style="btn cag-home-nav__lang-btn" onchange="handleLanguageSwitch(this, 'home-language-form')">
                    @foreach (config('app.locales') as $key => $locale)
                        <option value="{{ $locale }}"
                                data-content='<span class="fi fi-{{$key}}"></span>'
                                {{ app()->getLocale() == $locale ? 'selected' : '' }}>
                        </option>
                    @endforeach
                </select>
            </form>

            @auth
                @php
                    $homeNavUser = Auth::user();
                    $homeNavAvatar = $homeNavUser->profil_image
                        ? asset('images/' . $homeNavUser->profil_image)
                        : asset('images/placeholder_guide.jpg');
                    $homeNavName = trim((string) $homeNavUser->firstname) ?: __('homepage.header-profile');
                @endphp
                <div class="cag-home-nav__profile dropdown d-none d-md-block">
                    <button
                        type="button"
                        class="cag-home-nav__profile-toggle dropdown-toggle"
                        id="homeNavProfileToggle"
                        data-bs-toggle="dropdown"
                        data-bs-auto-close="outside"
                        aria-expanded="false"
                        aria-haspopup="true"
                    >
                        <img
                            src="{{ $homeNavAvatar }}"
                            alt=""
                            class="cag-home-nav__avatar"
                            width="32"
                            height="32"
                        >
                        <span class="cag-home-nav__profile-name">{{ $homeNavName }}</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end cag-home-nav__profile-menu" aria-labelledby="homeNavProfileToggle">
                        <a class="dropdown-item" href="{{ route('profile.index') }}">
                            <i class="fas fa-user" aria-hidden="true"></i>
                            <span>@lang('homepage.header-profile')</span>
                        </a>
                        <a class="dropdown-item" href="{{ route('profile.bookings') }}">
                            <i class="fas fa-calendar-check" aria-hidden="true"></i>
                            <span>@lang('profile.bookings')</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}" class="logout-form">
                            @csrf
                            <button type="submit" class="dropdown-item cag-home-nav__logout">
                                <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                                <span>@lang('homepage.header-logout')</span>
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <a href="#" class="cag-home-nav__login d-none d-md-inline" data-bs-toggle="modal" data-bs-target="#loginModal">@lang('homepage.header-login')</a>
            @endauth

            @if(config('guide_onboarding.new_onboarding_enabled'))
                @auth
                    <a href="{{ route('guide.onboarding') }}" class="cag-home-nav__cta">@lang('homepage.header-become-guide')</a>
                @else
                    <a href="#" class="cag-home-nav__cta" data-bs-toggle="modal" data-bs-target="#guideApplicationModal">@lang('homepage.header-become-guide')</a>
                @endauth
            @else
                <a href="{{ route('login') }}" class="cag-home-nav__cta">@lang('homepage.header-become-guide')</a>
            @endif

            <button type="button" class="cag-home-nav__icon-btn d-md-none" data-bs-toggle="modal" data-bs-target="#mobileMenuModal" aria-label="Menu">
                <i class="fas fa-bars" aria-hidden="true"></i>
            </button>
            @auth
                <button
                    type="button"
                    class="cag-home-nav__icon-btn cag-home-nav__avatar-btn d-md-none"
                    data-bs-toggle="modal"
                    data-bs-target="#mobileMenuModal"
                    aria-label="@lang('homepage.header-profile')"
                >
                    <img
                        src="{{ $homeNavAvatar }}"
                        alt=""
                        class="cag-home-nav__avatar"
                        width="32"
                        height="32"
                    >
                </button>
            @else
                <a href="#" class="cag-home-nav__icon-btn d-md-none" data-bs-toggle="modal" data-bs-target="#loginModal" aria-label="@lang('homepage.header-login')">
                    <i class="far fa-user-circle" aria-hidden="true"></i>
                </a>
            @endauth
        </div>
    </div>
</header>
