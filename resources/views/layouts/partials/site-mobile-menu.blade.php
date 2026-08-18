{{-- Shared mobile menu for cag-site-nav (homepage + migrated pages). --}}
@php
    $contactEmail = 'info.catchaguide@gmail.com';
    $contactNumber = (string) config('cag.contact_num');
    $magazineActive = request()->routeIs('blog.*', 'blogde.*')
        || request()->is('angelmagazin*', 'fishing-magazine*');
    $siteNavUser = Auth::user();
    $siteNavAvatar = $siteNavUser && $siteNavUser->profil_image
        ? asset('images/'.$siteNavUser->profil_image)
        : asset('images/placeholder_guide.jpg');
    $siteNavName = $siteNavUser
        ? trim($siteNavUser->firstname.' '.$siteNavUser->lastname)
        : '';
@endphp
<div class="modal fade" id="mobileMenuModal" tabindex="-1" aria-labelledby="cagSiteMobileMenuTitle">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content cag-site-mobile-menu">
            <div class="modal-header cag-site-mobile-menu__header">
                <img
                    src="{{ asset('assets/images/logo/CatchAGuide2_Logo_JPEG.jpg') }}"
                    alt="Catch A Guide"
                    class="cag-site-mobile-menu__logo"
                    height="32"
                >
                <p class="visually-hidden" id="cagSiteMobileMenuTitle">@lang('homepage.header-menu')</p>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="@lang('homepage.header-close')"></button>
            </div>
            <div class="modal-body cag-site-mobile-menu__body">
                @auth
                    <a href="{{ route('profile.index') }}" class="cag-site-mobile-menu__profile">
                        <img src="{{ $siteNavAvatar }}" alt="" class="cag-site-mobile-menu__avatar" width="44" height="44">
                        <span class="cag-site-mobile-menu__profile-copy">
                            <span class="cag-site-mobile-menu__profile-name">{{ $siteNavName !== '' ? $siteNavName : __('homepage.header-profile') }}</span>
                            <span class="cag-site-mobile-menu__profile-meta">@lang('homepage.header-profile')</span>
                        </span>
                        <i class="fas fa-chevron-right" aria-hidden="true"></i>
                    </a>
                @endauth

                <nav class="cag-site-mobile-menu__group" aria-label="{{ __('homepage.mobile_nav_label') }}">
                    @foreach ($siteBrowseNavLinks as $item)
                        <a
                            href="{{ $item['url'] }}"
                            @class(['cag-site-mobile-menu__item', 'is-active' => $item['active']])
                            @if ($item['active']) aria-current="page" @endif
                        >
                            <i class="{{ $item['icon'] }}" aria-hidden="true"></i>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                    <a
                        href="{{ route($blogPrefix.'.index') }}"
                        @class(['cag-site-mobile-menu__item', 'is-active' => $magazineActive])
                        @if ($magazineActive) aria-current="page" @endif
                    >
                        <i class="fas fa-book-open" aria-hidden="true"></i>
                        <span>@lang('homepage.filter-magazine')</span>
                    </a>
                </nav>

                @auth
                    <nav class="cag-site-mobile-menu__group" aria-label="@lang('homepage.header-profile')">
                        <a href="{{ route('profile.bookings') }}" class="cag-site-mobile-menu__item">
                            <i class="fas fa-calendar-check" aria-hidden="true"></i>
                            <span>@lang('profile.bookings')</span>
                        </a>
                    </nav>
                @endauth

                <div class="cag-site-mobile-menu__group cag-site-mobile-menu__group--muted">
                    <a href="mailto:{{ $contactEmail }}" class="cag-site-mobile-menu__item">
                        <i class="fas fa-envelope" aria-hidden="true"></i>
                        <span>{{ $contactEmail }}</span>
                    </a>
                    @if ($contactNumber !== '')
                        <a href="tel:+49{{ $contactNumber }}" class="cag-site-mobile-menu__item">
                            <i class="fas fa-phone" aria-hidden="true"></i>
                            <span>+49 (0) {{ $contactNumber }}</span>
                        </a>
                    @endif
                    <div class="cag-site-mobile-menu__social">
                        <a href="https://www.facebook.com/CatchAGuide" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                            <i class="fab fa-facebook" aria-hidden="true"></i>
                        </a>
                        @if ($contactNumber !== '')
                            <a href="https://wa.me/+49{{ $contactNumber }}" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp">
                                <i class="fab fa-whatsapp" aria-hidden="true"></i>
                            </a>
                        @endif
                        <a href="https://www.instagram.com/catchaguide_official/" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                            <i class="fab fa-instagram" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>

                <div class="cag-site-mobile-menu__group">
                    <p class="cag-site-mobile-menu__label">@lang('homepage.header-language')</p>
                    <form action="{{ route('language.switch') }}" method="POST" class="cag-site-mobile-menu__langs">
                        @csrf
                        @foreach (config('app.locales') as $key => $locale)
                            <button
                                type="submit"
                                name="language"
                                value="{{ $locale }}"
                                @class(['cag-site-mobile-menu__lang', 'is-active' => app()->getLocale() == $locale])
                            >
                                <span class="fi fi-{{ $key }}"></span>
                                {{ strtoupper($locale) }}
                            </button>
                        @endforeach
                    </form>
                </div>

                <div class="cag-site-mobile-menu__group cag-site-mobile-menu__group--last">
                    @auth
                        <form method="POST" action="{{ route('logout') }}" class="cag-site-mobile-menu__form">
                            @csrf
                            <button type="submit" class="cag-site-mobile-menu__item is-danger">
                                <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                                <span>@lang('homepage.header-logout')</span>
                            </button>
                        </form>
                    @else
                        @if(config('guide_onboarding.new_onboarding_enabled'))
                            <a href="#" class="cag-site-mobile-menu__item" data-bs-toggle="modal" data-bs-target="#guideApplicationModal" data-bs-dismiss="modal">
                                <i class="fas fa-certificate" aria-hidden="true"></i>
                                <span>@lang('homepage.header-become-guide')</span>
                            </a>
                        @endif
                        <a href="#" class="cag-site-mobile-menu__item" data-bs-toggle="modal" data-bs-target="#registerModal" data-bs-dismiss="modal">
                            <i class="fas fa-user-plus" aria-hidden="true"></i>
                            <span>@lang('homepage.header-signup')</span>
                        </a>
                        <a href="#" class="cag-site-mobile-menu__item" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">
                            <i class="fas fa-sign-in-alt" aria-hidden="true"></i>
                            <span>@lang('homepage.header-login')</span>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>
