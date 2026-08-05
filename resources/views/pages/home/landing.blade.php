@extends('layouts.app-v2')

@section('title', __('homepage.meta_title'))
@section('description', __('homepage.meta_description'))

@section('share_tags')
    <meta property="og:title" content="{{ __('homepage.meta_title') }}" />
    <meta property="og:description" content="{{ __('homepage.meta_description') }}" />
    @if(file_exists(public_path('assets/images/logo/CatchAGuide_LogoOnly_JPEG.jpg')))
        <meta property="og:image" content="{{ asset('assets/images/logo/CatchAGuide_LogoOnly_JPEG.jpg') }}"/>
    @endif
@endsection

@section('content')
@include('layouts.modal.loginModal')
@include('layouts.modal.registerModal')
@include('layouts.modal.guideApplicationModal')
@include('pages.home.partials.mobile-menu-modal')

<div class="cag-home" data-analytics-page="homepage">
    @include('pages.home.partials.home-nav')
    @include('pages.home.partials.hero-chooser')
    @include('pages.home.partials.country-grid')
    @include('pages.home.partials.trust-strip')
    @include('pages.home.partials.target-species')
    @include('pages.home.partials.season-module')
    @include('pages.home.partials.mixed-offers-rail')
    @include('pages.home.partials.testimonials')
    @include('pages.home.partials.magazine')
    @include('pages.home.partials.become-guide')
    @include('pages.home.partials.mobile-bottom-nav')
</div>
@endsection

@section('js_after')
<script>
function handleLanguageSwitch(selectElement, formId) {
    var form = document.getElementById(formId);
    if (form) form.submit();
}
function validateSearch(event, inputId) {
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
}
(function () {
    document.querySelectorAll('[data-home-analytics]').forEach(function (el) {
        el.addEventListener('click', function () {
            var name = el.getAttribute('data-home-analytics');
            var detail = { event: name };
            var productType = el.getAttribute('data-product-type');
            if (productType) {
                detail.product_type = productType;
            }
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push(detail);
        });
    });

    var rail = document.querySelector('[data-dest-rail]');
    if (rail) {
        var prev = document.querySelector('[data-dest-prev]');
        var next = document.querySelector('[data-dest-next]');
        var paused = false;
        var resumeTimer = null;
        var pauseTemporarily = function () {
            paused = true;
            if (resumeTimer) clearTimeout(resumeTimer);
            resumeTimer = setTimeout(function () { paused = false; }, 2500);
        };
        var scrollBy = function (dir) {
            pauseTemporarily();
            var step = Math.min(rail.clientWidth * 0.75, 320);
            rail.scrollBy({ left: dir * step, behavior: 'smooth' });
        };
        if (prev) prev.addEventListener('click', function () { scrollBy(-1); });
        if (next) next.addEventListener('click', function () { scrollBy(1); });

        var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (!reduceMotion) {
            var loopWidth = 0;
            var syncLoop = function () {
                // Duplicated tiles: seamless loop at halfway
                loopWidth = Math.floor(rail.scrollWidth / 2);
            };
            syncLoop();
            window.addEventListener('resize', syncLoop);
            if (typeof ResizeObserver !== 'undefined') {
                new ResizeObserver(syncLoop).observe(rail);
            }
            rail.addEventListener('mouseenter', function () { paused = true; });
            rail.addEventListener('mouseleave', function () { paused = false; });
            rail.addEventListener('touchstart', function () { paused = true; }, { passive: true });
            rail.addEventListener('touchend', function () {
                if (resumeTimer) clearTimeout(resumeTimer);
                resumeTimer = setTimeout(function () { paused = false; }, 1500);
            });
            rail.addEventListener('wheel', function () { pauseTemporarily(); }, { passive: true });

            var last = performance.now();
            var tick = function (now) {
                var dt = Math.min(now - last, 64);
                last = now;
                if (!paused && loopWidth > rail.clientWidth) {
                    rail.scrollLeft += (32 * dt) / 1000;
                    if (rail.scrollLeft >= loopWidth) {
                        rail.scrollLeft -= loopWidth;
                    }
                }
                requestAnimationFrame(tick);
            };
            requestAnimationFrame(function (t) {
                last = t;
                requestAnimationFrame(tick);
            });
        }
    }

    document.querySelectorAll('[data-offer-rail]').forEach(function (rail) {
        var type = rail.getAttribute('data-offer-rail');
        var prev = document.querySelector('[data-offer-prev="' + type + '"]');
        var next = document.querySelector('[data-offer-next="' + type + '"]');
        var scrollBy = function (dir) {
            var step = Math.min(rail.clientWidth * 0.8, 320);
            rail.scrollBy({ left: dir * step, behavior: 'smooth' });
        };
        if (prev) prev.addEventListener('click', function () { scrollBy(-1); });
        if (next) next.addEventListener('click', function () { scrollBy(1); });
    });

    var reviewsRail = document.querySelector('[data-reviews-rail] .cag-home-reviews__rail');
    if (reviewsRail) {
        reviewsRail.addEventListener('touchstart', function () {
            reviewsRail.style.animationPlayState = 'paused';
        }, { passive: true });
        reviewsRail.addEventListener('touchend', function () {
            reviewsRail.style.animationPlayState = '';
        });
    }
})();
</script>
@endsection
