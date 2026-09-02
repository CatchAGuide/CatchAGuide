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
    <div class="cag-home-hero-shell cag-site-nav-shell">
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
    if (String(searchInput.value || '').trim() === '') {
        if (typeof window.stripEmptyLocationSearchFromForm === 'function') {
            window.stripEmptyLocationSearchFromForm(form);
        }
        return true;
    }
    if (lat && lng && (!lat.value || !lng.value)) {
        event.preventDefault();

        if (window.bootstrap && typeof window.bootstrap.Tooltip === 'function') {
            var existing = window.bootstrap.Tooltip.getInstance(searchInput);
            if (existing) existing.dispose();

            var tooltip = new window.bootstrap.Tooltip(searchInput, {
                title: @json(__('checkout.location_suggestion_hint')),
                placement: 'bottom',
                trigger: 'manual'
            });
            tooltip.show();
            setTimeout(function () { tooltip.dispose(); }, 3000);
        }

        searchInput.classList.add('shake');
        setTimeout(function () { searchInput.classList.remove('shake'); }, 500);

        searchInput.focus();
        return false;
    }
    return true;
}
(function () {
    var homeRoot = document.querySelector('.cag-home');
    if (homeRoot) {
        homeRoot.classList.add('has-reveal-js');
    }

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

    // Mouse drag-to-scroll for horizontal rails (touch keeps native pan).
    // Capture only after the drag threshold so plain clicks still hit <a> cards.
    var enableDragScroll = function (el, opts) {
        if (!el || el.getAttribute('data-cag-drag-scroll') === '1') return;
        el.setAttribute('data-cag-drag-scroll', '1');
        opts = opts || {};

        var activePointerId = null;
        var startX = 0;
        var startSl = 0;
        var dragging = false;
        var suppressClick = false;
        var threshold = 8;

        var cleanup = function () {
            if (dragging && activePointerId !== null) {
                try { el.releasePointerCapture(activePointerId); } catch (err) {}
            }
            activePointerId = null;
            dragging = false;
            el.classList.remove('is-dragging');
        };

        el.addEventListener('dragstart', function (e) {
            // Prevent browser ghost-dragging images/links while we may scroll.
            if (dragging) e.preventDefault();
        });

        el.addEventListener('pointerdown', function (e) {
            if (e.pointerType !== 'mouse' || e.button !== 0 || !e.isPrimary) return;
            if (e.target.closest('button, input, select, textarea')) return;
            activePointerId = e.pointerId;
            startX = e.clientX;
            startSl = el.scrollLeft;
            dragging = false;
            suppressClick = false;
        });

        el.addEventListener('pointermove', function (e) {
            if (e.pointerId !== activePointerId || e.pointerType !== 'mouse') return;
            var dx = e.clientX - startX;
            if (!dragging && Math.abs(dx) > threshold) {
                dragging = true;
                el.classList.add('is-dragging');
                try { el.setPointerCapture(activePointerId); } catch (err) {}
                if (typeof opts.onInteract === 'function') opts.onInteract();
            }
            if (dragging) {
                e.preventDefault();
                el.scrollLeft = startSl - dx;
                if (typeof opts.onInteract === 'function') opts.onInteract();
            }
        }, { passive: false });

        el.addEventListener('pointerup', function (e) {
            if (e.pointerId !== activePointerId) return;
            if (dragging) {
                suppressClick = true;
                window.setTimeout(function () { suppressClick = false; }, 200);
            }
            cleanup();
        });

        el.addEventListener('pointercancel', cleanup);

        el.addEventListener('click', function (e) {
            if (suppressClick) {
                e.preventDefault();
                e.stopPropagation();
            }
        }, true);
    };

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

        enableDragScroll(rail, { onInteract: pauseTemporarily });

        var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (!reduceMotion) {
            var loopWidth = 0;
            var syncLoop = function () {
                // Duplicated tiles: seamless loop at halfway
                loopWidth = Math.floor(rail.scrollWidth / 2);
            };
            var wrapLoop = function () {
                if (loopWidth <= 0) return;
                while (rail.scrollLeft >= loopWidth) {
                    rail.scrollLeft -= loopWidth;
                }
                while (rail.scrollLeft < 0) {
                    rail.scrollLeft += loopWidth;
                }
            };
            syncLoop();
            window.addEventListener('resize', syncLoop);
            if (typeof ResizeObserver !== 'undefined') {
                new ResizeObserver(syncLoop).observe(rail);
            }
            rail.addEventListener('mouseenter', function () { paused = true; });
            rail.addEventListener('mouseleave', function () {
                if (!rail.classList.contains('is-dragging')) paused = false;
            });
            rail.addEventListener('touchstart', function () { paused = true; }, { passive: true });
            rail.addEventListener('touchend', function () {
                if (resumeTimer) clearTimeout(resumeTimer);
                resumeTimer = setTimeout(function () { paused = false; }, 1500);
            });
            rail.addEventListener('wheel', function () { pauseTemporarily(); }, { passive: true });
            rail.addEventListener('scroll', wrapLoop, { passive: true });

            var last = performance.now();
            var tick = function (now) {
                var dt = Math.min(now - last, 64);
                last = now;
                if (!paused && loopWidth > rail.clientWidth) {
                    rail.scrollLeft += (32 * dt) / 1000;
                    wrapLoop();
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
        enableDragScroll(rail);
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

    var reviewsRail = document.querySelector('[data-reviews-rail]');
    if (reviewsRail) {
        var reviewsPrev = document.querySelector('[data-reviews-prev]');
        var reviewsNext = document.querySelector('[data-reviews-next]');
        var reviewsPaused = false;
        var reviewsResumeTimer = null;
        var pauseReviewsTemporarily = function () {
            reviewsPaused = true;
            if (reviewsResumeTimer) clearTimeout(reviewsResumeTimer);
            reviewsResumeTimer = setTimeout(function () { reviewsPaused = false; }, 2500);
        };
        var scrollReviewsBy = function (dir) {
            pauseReviewsTemporarily();
            var step = Math.min(reviewsRail.clientWidth * 0.75, 356);
            reviewsRail.scrollBy({ left: dir * step, behavior: 'smooth' });
        };
        if (reviewsPrev) reviewsPrev.addEventListener('click', function () { scrollReviewsBy(-1); });
        if (reviewsNext) reviewsNext.addEventListener('click', function () { scrollReviewsBy(1); });

        enableDragScroll(reviewsRail, { onInteract: pauseReviewsTemporarily });

        var reviewsReduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (!reviewsReduceMotion) {
            var reviewsLoopWidth = 0;
            var syncReviewsLoop = function () {
                reviewsLoopWidth = Math.floor(reviewsRail.scrollWidth / 2);
            };
            var wrapReviewsLoop = function () {
                if (reviewsLoopWidth <= 0) return;
                while (reviewsRail.scrollLeft >= reviewsLoopWidth) {
                    reviewsRail.scrollLeft -= reviewsLoopWidth;
                }
                while (reviewsRail.scrollLeft < 0) {
                    reviewsRail.scrollLeft += reviewsLoopWidth;
                }
            };
            syncReviewsLoop();
            window.addEventListener('resize', syncReviewsLoop);
            if (typeof ResizeObserver !== 'undefined') {
                new ResizeObserver(syncReviewsLoop).observe(reviewsRail);
            }
            reviewsRail.addEventListener('touchstart', function () { pauseReviewsTemporarily(); }, { passive: true });
            reviewsRail.addEventListener('wheel', function () { pauseReviewsTemporarily(); }, { passive: true });
            reviewsRail.addEventListener('scroll', wrapReviewsLoop, { passive: true });

            var reviewsLast = performance.now();
            var tickReviews = function (now) {
                var dt = Math.min(now - reviewsLast, 64);
                reviewsLast = now;
                if (!reviewsPaused && reviewsLoopWidth > 0) {
                    reviewsRail.scrollLeft += (32 * dt) / 1000;
                    wrapReviewsLoop();
                }
                requestAnimationFrame(tickReviews);
            };
            requestAnimationFrame(function (t) {
                reviewsLast = t;
                requestAnimationFrame(tickReviews);
            });
        }
    }

    var hero = document.querySelector('[data-hero-carousel]');
    var homeNav = document.querySelector('.cag-site-nav-shell .cag-site-nav');
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
@include('pages.home.partials.species-spotlight-script')
@include('layouts.partials.offers-persons-stepper-script')
@endsection
