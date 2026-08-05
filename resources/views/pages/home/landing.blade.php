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
    <div class="cag-home-hero-shell">
        @include('pages.home.partials.home-nav')
        @include('pages.home.partials.hero-chooser')
    </div>
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

    // First-view entrance for homepage sections (same idea as target fish)
    var revealReduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var revealSections = Array.prototype.slice.call(document.querySelectorAll('[data-cag-reveal]'));
    var activateReveal = function (section) {
        section.classList.add('is-inview');
    };
    if (revealReduceMotion) {
        revealSections.forEach(activateReveal);
    } else if ('IntersectionObserver' in window) {
        var revealObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                activateReveal(entry.target);
                revealObserver.unobserve(entry.target);
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -6% 0px' });
        revealSections.forEach(function (section) {
            revealObserver.observe(section);
        });
    } else {
        revealSections.forEach(activateReveal);
    }

    // Target fish: staggered entrance + walking spotlight (not a rail auto-scroll)
    var speciesSection = document.querySelector('[data-species-spotlight]');
    if (speciesSection) {
        var speciesViewport = speciesSection.querySelector('[data-species-viewport]');
        var speciesCards = Array.prototype.slice.call(
            speciesSection.querySelectorAll('.cag-home-species__card')
        );
        var speciesReduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var spotlightIndex = 0;
        var spotlightTimer = null;
        var spotlightPaused = false;
        var entranceTimer = null;

        var clearSpotlight = function () {
            speciesCards.forEach(function (card) {
                card.classList.remove('is-spotlight');
            });
        };

        var ensureCardVisible = function (card) {
            if (!speciesViewport || !card) return;
            var viewLeft = speciesViewport.scrollLeft;
            var viewRight = viewLeft + speciesViewport.clientWidth;
            var cardLeft = card.offsetLeft;
            var cardRight = cardLeft + card.offsetWidth;
            var pad = 16;

            if (cardLeft < viewLeft + pad) {
                speciesViewport.scrollTo({ left: Math.max(0, cardLeft - pad), behavior: 'smooth' });
            } else if (cardRight > viewRight - pad) {
                speciesViewport.scrollTo({
                    left: cardRight - speciesViewport.clientWidth + pad,
                    behavior: 'smooth'
                });
            }
        };

        var setSpotlight = function (index) {
            if (!speciesCards.length) return;
            clearSpotlight();
            spotlightIndex = (index + speciesCards.length) % speciesCards.length;
            var card = speciesCards[spotlightIndex];
            card.classList.add('is-spotlight');
            ensureCardVisible(card);
        };

        var stopSpotlight = function () {
            if (spotlightTimer) {
                window.clearInterval(spotlightTimer);
                spotlightTimer = null;
            }
        };

        var startSpotlight = function () {
            if (speciesReduceMotion || speciesCards.length < 2 || spotlightPaused) return;
            stopSpotlight();
            setSpotlight(spotlightIndex);
            spotlightTimer = window.setInterval(function () {
                if (spotlightPaused) return;
                setSpotlight(spotlightIndex + 1);
            }, 2400);
        };

        var pauseSpotlight = function () {
            spotlightPaused = true;
            clearSpotlight();
            stopSpotlight();
        };

        var resumeSpotlight = function () {
            spotlightPaused = false;
            startSpotlight();
        };

        var beginMotion = function () {
            speciesSection.classList.add('is-inview');
            if (entranceTimer) window.clearTimeout(entranceTimer);
            entranceTimer = window.setTimeout(function () {
                speciesSection.classList.add('is-ready');
                startSpotlight();
            }, Math.max(400, speciesCards.length * 70 + 200));
        };

        if ('IntersectionObserver' in window) {
            var speciesObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        beginMotion();
                        speciesObserver.disconnect();
                    }
                });
            }, { threshold: 0.2 });
            speciesObserver.observe(speciesSection);
        } else {
            beginMotion();
        }

        // Pause only while interacting with cards — not the whole section heading.
        speciesCards.forEach(function (card) {
            card.addEventListener('mouseenter', pauseSpotlight);
            card.addEventListener('mouseleave', resumeSpotlight);
            card.addEventListener('focusin', pauseSpotlight);
            card.addEventListener('focusout', resumeSpotlight);
        });

        if (speciesViewport) {
            speciesViewport.addEventListener('touchstart', pauseSpotlight, { passive: true });
            speciesViewport.addEventListener('touchend', function () {
                window.setTimeout(resumeSpotlight, 1200);
            }, { passive: true });
        }
    }

    var reviewsRail = document.querySelector('[data-reviews-rail] .cag-home-reviews__rail');
    if (reviewsRail) {
        reviewsRail.addEventListener('touchstart', function () {
            reviewsRail.style.animationPlayState = 'paused';
        }, { passive: true });
        reviewsRail.addEventListener('touchend', function () {
            reviewsRail.style.animationPlayState = '';
        });
    }

    var hero = document.querySelector('[data-hero-carousel]');
    var homeNav = document.querySelector('.cag-home-hero-shell .cag-home-nav');
    if (homeNav && hero) {
        var syncNavSolid = function () {
            homeNav.classList.toggle('is-solid', hero.getBoundingClientRect().bottom <= homeNav.offsetHeight + 12);
        };
        syncNavSolid();
        window.addEventListener('scroll', syncNavSolid, { passive: true });
        window.addEventListener('resize', syncNavSolid);
    }

    if (hero) {
        var slides = Array.prototype.slice.call(hero.querySelectorAll('.cag-home-hero__image'));
        var dots = Array.prototype.slice.call(hero.querySelectorAll('[data-hero-dot]'));
        var index = 0;
        var timer = null;
        var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        var goTo = function (next) {
            if (!slides.length) return;
            index = (next + slides.length) % slides.length;
            slides.forEach(function (slide, i) {
                slide.classList.toggle('is-active', i === index);
            });
            dots.forEach(function (dot, i) {
                var active = i === index;
                dot.classList.toggle('is-active', active);
                dot.setAttribute('aria-selected', active ? 'true' : 'false');
            });
        };

        var start = function () {
            if (reduceMotion || slides.length < 2) return;
            stop();
            timer = window.setInterval(function () {
                goTo(index + 1);
            }, 6500);
        };
        var stop = function () {
            if (timer) {
                window.clearInterval(timer);
                timer = null;
            }
        };

        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                var target = parseInt(dot.getAttribute('data-hero-dot'), 10);
                if (isNaN(target)) return;
                goTo(target);
                start();
            });
        });

        hero.addEventListener('mouseenter', stop);
        hero.addEventListener('mouseleave', start);
        start();
    }
})();
</script>
@endsection
