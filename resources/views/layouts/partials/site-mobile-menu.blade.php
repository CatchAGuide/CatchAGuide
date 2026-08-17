{{-- Shared mobile menu for cag-site-nav (homepage + migrated pages). --}}
<div class="modal fade" id="mobileMenuModal" tabindex="-1">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header cag-site-mobile-menu__header">
                <div class="d-flex align-items-center gap-2">
                    @auth
                        <img src="{{ Auth::user()->profil_image ? asset('images/'.Auth::user()->profil_image) : asset('images/placeholder_guide.jpg') }}"
                             class="rounded-circle"
                             width="40"
                             height="40"
                             style="object-fit:cover"
                             alt="">
                        <span>{{ Auth::user()->firstname }} {{ Auth::user()->lastname }}</span>
                    @else
                        <img src="{{ asset('assets/images/logo/CatchAGuide2_Logo_PNG.png') }}" alt="Catch A Guide" height="36">
                    @endauth
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="cag-site-mobile-menu__items">
                    @foreach ($sitePrimaryNavLinks as $item)
                        <a
                            href="{{ $item['url'] }}"
                            @class(['cag-site-mobile-menu__item', 'is-active' => $item['active']])
                            @if ($item['active']) aria-current="page" @endif
                        >{{ $item['label'] }}</a>
                    @endforeach
                    <a href="{{ route('destination') }}" class="cag-site-mobile-menu__item">@lang('homepage.footer_destinations')</a>
                    @auth
                        <a href="{{ route('profile.index') }}" class="cag-site-mobile-menu__item">@lang('homepage.header-profile')</a>
                        <a href="{{ route('profile.bookings') }}" class="cag-site-mobile-menu__item">@lang('profile.bookings')</a>
                        <form method="POST" action="{{ route('logout') }}" class="logout-form">
                            @csrf
                            <button type="submit" class="cag-site-mobile-menu__item w-100 text-start border-0 bg-transparent">@lang('homepage.header-logout')</button>
                        </form>
                    @else
                        <a href="#" class="cag-site-mobile-menu__item" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">@lang('homepage.header-login')</a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>
