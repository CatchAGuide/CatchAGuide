{{--
  Generalized site header (homepage layout).
  Legacy dark chrome remains in layouts.partials.newheader for non-migrated pages.

  @param bool   $overlay   Transparent over a hero shell (homepage). Default false (solid).
  @param string $idPrefix  Unique prefix for form/toggle ids. Default "site".
--}}
@php
    $overlay = $overlay ?? false;
    $idPrefix = $idPrefix ?? 'site';
    $navClass = 'cag-site-nav'.($overlay ? ' cag-site-nav--overlay' : ' cag-site-nav--solid');
    $langFormId = $idPrefix.'-language-form';
    $profileToggleId = $idPrefix.'NavProfileToggle';
    $siteNavUser = Auth::user();
    $siteNavAvatar = $siteNavUser && $siteNavUser->profil_image
        ? asset('images/'.$siteNavUser->profil_image)
        : asset('images/placeholder_guide.jpg');
    $siteNavName = $siteNavUser
        ? (trim((string) $siteNavUser->firstname) ?: __('homepage.header-profile'))
        : '';
@endphp
<header class="{{ $navClass }}">
    <div class="cag-site-nav__inner">
        <a href="{{ route('welcome') }}" class="cag-site-nav__brand" aria-label="Catch A Guide">
            <img
                src="{{ asset('assets/images/logo/CatchAGuide2_Logo_JPEG.jpg') }}"
                alt="Catch A Guide"
                class="cag-site-nav__logo cag-site-nav__logo--color"
                width="200"
                height="48"
            >
            <img
                src="{{ asset('assets/images/logo/CatchAGuide2_Logo_PNG.png') }}"
                alt="Catch A Guide"
                class="cag-site-nav__logo cag-site-nav__logo--light"
                width="200"
                height="48"
            >
        </a>

        <nav class="cag-site-nav__links d-none d-md-flex" aria-label="Primary">
            <a
                href="{{ route('guidings.landing') }}"
                class="{{ request()->is('guidings*') ? 'is-active' : '' }}"
            >@lang('homepage.filter-fishing-near-me')</a>
            <a
                href="{{ route('offers.index') }}"
                class="{{ request()->is('offers*') || request()->routeIs('offers.*') ? 'is-active' : '' }}"
            >@lang('offers.nav_label')</a>
            <a
                href="{{ route('vacations.index') }}"
                class="{{ request()->is('vacations*') ? 'is-active' : '' }}"
            >@lang('homepage.header-vacations')</a>
        </nav>

        <div class="cag-site-nav__actions">
            <form action="{{ route('language.switch') }}" method="POST" class="cag-site-nav__lang d-none d-md-flex" id="{{ $langFormId }}">
                @csrf
                <select name="language" class="selectpicker header-language-select" data-width="fit" data-style="btn cag-site-nav__lang-btn" onchange="handleLanguageSwitch(this, '{{ $langFormId }}')">
                    @foreach (config('app.locales') as $key => $locale)
                        <option value="{{ $locale }}"
                                data-content='<span class="fi fi-{{$key}}"></span>'
                                {{ app()->getLocale() == $locale ? 'selected' : '' }}>
                        </option>
                    @endforeach
                </select>
            </form>

            @auth
                <div class="cag-site-nav__profile dropdown d-none d-md-block">
                    <button
                        type="button"
                        class="cag-site-nav__profile-toggle dropdown-toggle"
                        id="{{ $profileToggleId }}"
                        data-bs-toggle="dropdown"
                        data-bs-auto-close="outside"
                        aria-expanded="false"
                        aria-haspopup="true"
                    >
                        <img
                            src="{{ $siteNavAvatar }}"
                            alt=""
                            class="cag-site-nav__avatar"
                            width="32"
                            height="32"
                        >
                        <span class="cag-site-nav__profile-name">{{ $siteNavName }}</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end cag-site-nav__profile-menu" aria-labelledby="{{ $profileToggleId }}">
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
                            <button type="submit" class="dropdown-item cag-site-nav__logout">
                                <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                                <span>@lang('homepage.header-logout')</span>
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <a href="#" class="cag-site-nav__login d-none d-md-inline" data-bs-toggle="modal" data-bs-target="#loginModal">@lang('homepage.header-login')</a>
            @endauth

            @if(config('guide_onboarding.new_onboarding_enabled'))
                @auth
                    <a href="{{ route('guide.onboarding') }}" class="cag-site-nav__cta">@lang('homepage.header-become-guide')</a>
                @else
                    <a href="#" class="cag-site-nav__cta" data-bs-toggle="modal" data-bs-target="#guideApplicationModal">@lang('homepage.header-become-guide')</a>
                @endauth
            @else
                <a href="{{ route('login') }}" class="cag-site-nav__cta">@lang('homepage.header-become-guide')</a>
            @endif

            <button type="button" class="cag-site-nav__icon-btn d-md-none" data-bs-toggle="modal" data-bs-target="#mobileMenuModal" aria-label="Menu">
                <i class="fas fa-bars" aria-hidden="true"></i>
            </button>
            @auth
                <button
                    type="button"
                    class="cag-site-nav__icon-btn cag-site-nav__avatar-btn d-md-none"
                    data-bs-toggle="modal"
                    data-bs-target="#mobileMenuModal"
                    aria-label="@lang('homepage.header-profile')"
                >
                    <img
                        src="{{ $siteNavAvatar }}"
                        alt=""
                        class="cag-site-nav__avatar"
                        width="32"
                        height="32"
                    >
                </button>
            @else
                <a href="#" class="cag-site-nav__icon-btn d-md-none" data-bs-toggle="modal" data-bs-target="#loginModal" aria-label="@lang('homepage.header-login')">
                    <i class="far fa-user-circle" aria-hidden="true"></i>
                </a>
            @endauth
        </div>
    </div>
</header>
@once
<script>
if (typeof window.handleLanguageSwitch !== 'function') {
    window.handleLanguageSwitch = function (selectElement, formId) {
        var form = document.getElementById(formId);
        if (form) form.submit();
    };
}
if (typeof window.validateSearch !== 'function') {
    window.validateSearch = function (event, inputId) {
        var searchInput = document.getElementById(inputId);
        if (!searchInput) return true;
        var form = searchInput.closest('form');
        if (!form) return true;
        var lat = form.querySelector('input[name="placeLat"]');
        var lng = form.querySelector('input[name="placeLng"]');
        if (searchInput.value !== '' && lat && lng && (!lat.value || !lng.value)) {
            event.preventDefault();
            searchInput.focus();
            return false;
        }
        return true;
    };
}
</script>
@endonce
