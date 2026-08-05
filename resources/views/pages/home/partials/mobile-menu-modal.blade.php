<div class="modal fade" id="mobileMenuModal" tabindex="-1">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header cag-home-mobile-menu__header">
                <div class="d-flex align-items-center gap-2">
                    @auth
                        <img src="{{ asset('images/'. Auth::user()->profil_image) ?: asset('images/placeholder_guide.jpg') }}"
                             class="rounded-circle"
                             style="width:40px;height:40px;object-fit:cover"
                             alt="">
                        <span>{{ Auth::user()->firstname }} {{ Auth::user()->lastname }}</span>
                    @else
                        <img src="{{ asset('assets/images/logo/CatchAGuide2_Logo_PNG.png') }}" alt="Catch A Guide" style="height:36px">
                    @endauth
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="cag-home-mobile-menu__items">
                    <a href="{{ route('guidings.index') }}" class="cag-home-mobile-menu__item">@lang('homepage.filter-fishing-near-me')</a>
                    <a href="{{ route('vacations.index') }}" class="cag-home-mobile-menu__item">@lang('homepage.header-vacations')</a>
                    <a href="{{ route('destination') }}" class="cag-home-mobile-menu__item">@lang('homepage.footer_destinations')</a>
                    @auth
                        <a href="{{ route('profile.index') }}" class="cag-home-mobile-menu__item">@lang('homepage.header-profile')</a>
                        <a href="{{ route('profile.bookings') }}" class="cag-home-mobile-menu__item">@lang('profile.bookings')</a>
                        <form method="POST" action="{{ route('logout') }}" class="logout-form">
                            @csrf
                            <button type="submit" class="cag-home-mobile-menu__item w-100 text-start border-0 bg-transparent">@lang('homepage.header-logout')</button>
                        </form>
                    @else
                        <a href="#" class="cag-home-mobile-menu__item" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">@lang('homepage.header-login')</a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>
