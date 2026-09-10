<nav class="cag-home-bottom-nav d-md-none" aria-label="{{ __('homepage.mobile_nav_label') }}">
    @foreach ($siteBottomNavLinks as $item)
        @php
            $navIcon = match ($item['key'] ?? '') {
                'home' => 'nav-home',
                'offers' => 'nav-grid',
                'tours' => 'nav-rod',
                'vacations' => 'nav-camp',
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
<script>
(function () {
    var nav = document.querySelector('.cag-home-bottom-nav');
    if (!nav || !window.visualViewport || nav.getAttribute('data-cag-vv') === '1') {
        return;
    }
    nav.setAttribute('data-cag-vv', '1');

    var hideNav = window.matchMedia('(min-width: 768px)');
    var frame = 0;

    function pin() {
        if (hideNav.matches) {
            nav.classList.remove('is-vv-pinned');
            nav.style.removeProperty('--cag-bottom-nav-top');
            return;
        }
        var height = nav.offsetHeight;
        if (height <= 0) {
            return;
        }
        var vv = window.visualViewport;
        nav.style.setProperty(
            '--cag-bottom-nav-top',
            Math.round(vv.offsetTop + vv.height - height) + 'px'
        );
        nav.classList.add('is-vv-pinned');
    }

    function requestPin() {
        if (frame) {
            return;
        }
        frame = window.requestAnimationFrame(function () {
            frame = 0;
            pin();
        });
    }

    pin();
    window.visualViewport.addEventListener('resize', requestPin);
    window.visualViewport.addEventListener('scroll', requestPin);
    window.addEventListener('scroll', requestPin, { passive: true });
    window.addEventListener('orientationchange', requestPin);
})();
</script>
