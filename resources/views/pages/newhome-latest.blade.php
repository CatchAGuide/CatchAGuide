@extends('layouts.app-v2')

@if(app()->getLocale() == 'en')
    @section('title','Find & book guided fishing trips online')
@else
    @section('title','Geführte Angeltouren finden & online buchen')
@endif
@section('description', __('homepage.header-message'))

@section('share_tags')
    <meta property="og:title" content="{{ __('homepage.header-title') }}" />
    <meta property="og:description" content="{{ __('homepage.header-message') }}" />
    @if(file_exists(public_path(str_replace(asset(''), '', asset('assets/images/logo/CatchAGuide_LogoOnly_JPEG.jpg')))))
        <meta property="og:image" content="{{ asset('assets/images/logo/CatchAGuide_LogoOnly_JPEG.jpg') }}"/>
    @endif
@endsection

@section('content')
<div class="category-hero-page" data-category-hero-page>
    @include('pages.category.partials.hero-header', [
        'listingEyebrow' => __('homepage.landing_hero_eyebrow'),
        'listingTitle' => __('homepage.landing_hero_h1'),
        'showTitleRule' => true,
        'listingSubtitle' => __('homepage.landing_hero_sub', ['tours' => $tourCount, 'countries' => $countryCount]),
        'searchAction' => listing_search_action(),
        'breadcrumbItems' => [
            ['label' => __('homepage.filter-fishing-near-me'), 'url' => null],
        ],
    ])

<div class="cag-home cag-home--embed gl-page" data-analytics-page="guidings-landing">
    @include('pages.guidings.partials.landing.how-it-works')
    @include('pages.home.partials.country-grid', [
        'showAllCountries' => true,
        'allCountriesRoute' => 'guidings.countries',
        'countryRoute' => 'guidings.destination',
        'title' => __('homepage.landing_destinations_title'),
        'subtitle' => __('homepage.landing_destinations_subtitle'),
        'viewAllLabel' => __('homepage.landing_destinations_see_all'),
    ])
    @include('pages.guidings.partials.landing.tour-type-pills')
    @include('pages.guidings.partials.landing.card-rail', [
        'title' => __('homepage.landing_mostbooked_title'),
        'subtitle' => __('homepage.landing_mostbooked_subtitle'),
        'cards' => $mostBooked,
        'railKey' => 'most-booked',
    ])

    <section class="cag-home-section vacation-hub__fish" data-analytics-vacation-rail="guidings-landing-methods">
        <div class="cag-home-container">
            <x-vacation.country-slider
                :title="__('homepage.landing_methods_title')"
                :subtitle="__('homepage.landing_methods_subtitle')"
                :link-url="route('guidings.methods')"
                :link-label="__('homepage.landing_methods_see_all')"
                slider-id="guidings-landing-methods"
                block-class="vacation-fish-rail"
            >
                @foreach([false, true] as $isClone)
                    @foreach($methods as $tile)
                        <x-vacation.fish-slide :tile="$tile" :clone="$isClone" />
                    @endforeach
                @endforeach
            </x-vacation.country-slider>
        </div>
    </section>

    @include('pages.guidings.partials.landing.card-rail', [
        'title' => __('homepage.landing_new_title'),
        'subtitle' => __('homepage.landing_new_subtitle'),
        'cards' => $newTours,
        'railKey' => 'new-tours',
    ])

    <section class="cag-home-section vacation-hub__fish" data-analytics-vacation-rail="guidings-landing-species">
        <div class="cag-home-container">
            <x-vacation.country-slider
                :title="__('homepage.landing_species_title')"
                :subtitle="__('homepage.landing_species_subtitle')"
                :link-url="route('targets.index')"
                :link-label="__('homepage.landing_species_see_all')"
                slider-id="guidings-landing-species"
                block-class="vacation-fish-rail"
            >
                @foreach([false, true] as $isClone)
                    @foreach($targetSpecies as $tile)
                        <x-vacation.fish-slide :tile="$tile" :clone="$isClone" />
                    @endforeach
                @endforeach
            </x-vacation.country-slider>
        </div>
    </section>

    @include('pages.guidings.partials.landing.provider-cta')
    @include('pages.guidings.partials.landing.seo-text')
    @include('pages.guidings.partials.landing.faq')
    @include('pages.guidings.partials.landing.sticky-bar')
</div>
</div>
@endsection

@section('js_after')
@include('layouts.partials.category-hero-header-script')
@include('pages.home.partials.dest-rail-script')
<script>
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
        searchInput.focus();
        return false;
    }
    return true;
}

(function () {
    var root = document.querySelector('.gl-page');
    if (root) {
        root.classList.add('has-reveal-js');
    }

    // First-view entrance animation
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var sections = Array.prototype.slice.call(document.querySelectorAll('[data-cag-reveal]'));
    var activate = function (el) { el.classList.add('is-inview'); };
    if (reduceMotion) {
        sections.forEach(activate);
    } else if ('IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                activate(entry.target);
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -6% 0px' });
        sections.forEach(function (section) { observer.observe(section); });
    } else {
        sections.forEach(activate);
    }

    // Mouse drag-to-scroll for horizontal rails (touch keeps native pan)
    var enableDragScroll = function (el) {
        if (!el || el.getAttribute('data-cag-drag-scroll') === '1') return;
        el.setAttribute('data-cag-drag-scroll', '1');

        var activePointerId = null;
        var startX = 0;
        var startSl = 0;
        var dragging = false;
        var suppressClick = false;
        var threshold = 8;

        el.addEventListener('dragstart', function (e) {
            if (dragging) e.preventDefault();
        });

        el.addEventListener('pointerdown', function (e) {
            if (e.pointerType !== 'mouse' || e.button !== 0 || !e.isPrimary) return;
            if (e.target.closest('button, input, select, textarea, a')) return;
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
            }
            if (dragging) {
                e.preventDefault();
                el.scrollLeft = startSl - dx;
            }
        }, { passive: false });

        el.addEventListener('pointerup', function (e) {
            if (e.pointerId !== activePointerId) return;
            if (dragging) {
                suppressClick = true;
                window.setTimeout(function () { suppressClick = false; }, 200);
            }
            activePointerId = null;
            dragging = false;
            el.classList.remove('is-dragging');
        });

        el.addEventListener('pointercancel', function () {
            activePointerId = null;
            dragging = false;
            el.classList.remove('is-dragging');
        });

        el.addEventListener('click', function (e) {
            if (suppressClick) {
                e.preventDefault();
                e.stopPropagation();
            }
        }, true);
    };
    document.querySelectorAll('[data-offer-rail]').forEach(enableDragScroll);

    // Tour-type pill switching
    document.querySelectorAll('[data-gl-pills]').forEach(function (pillsRoot) {
        pillsRoot.addEventListener('click', function (event) {
            var btn = event.target.closest('[data-gl-pill-btn]');
            if (!btn || !pillsRoot.contains(btn)) return;
            var key = btn.getAttribute('data-gl-pill-btn');
            pillsRoot.querySelectorAll('[data-gl-pill-btn]').forEach(function (b) {
                var active = b === btn;
                b.classList.toggle('is-active', active);
                b.setAttribute('aria-selected', active ? 'true' : 'false');
            });
            pillsRoot.querySelectorAll('[data-gl-pill-panel]').forEach(function (panel) {
                panel.classList.toggle('is-active', panel.getAttribute('data-gl-pill-panel') === key);
            });
        });
    });

    // SEO text expand/collapse
    document.querySelectorAll('[data-gl-seo-toggle]').forEach(function (btn) {
        var body = btn.parentElement ? btn.parentElement.querySelector('[data-gl-seo-body]') : null;
        if (!body) return;
        var moreLabel = @json(__('homepage.landing_seo_more'));
        var lessLabel = @json(__('homepage.landing_seo_less'));
        btn.addEventListener('click', function () {
            var expanded = body.classList.toggle('is-expanded');
            btn.textContent = expanded ? lessLabel : moreLabel;
        });
    });

    // FAQ accordion (single-open)
    document.querySelectorAll('[data-gl-faq]').forEach(function (faqRoot) {
        faqRoot.addEventListener('click', function (event) {
            var btn = event.target.closest('[data-gl-faq-toggle]');
            if (!btn || !faqRoot.contains(btn)) return;
            var item = btn.closest('.gl-faq__item');
            if (!item) return;
            var willOpen = !item.classList.contains('is-open');
            faqRoot.querySelectorAll('.gl-faq__item.is-open').forEach(function (openItem) {
                openItem.classList.remove('is-open');
            });
            if (willOpen) {
                item.classList.add('is-open');
            }
        });
    });
})();
</script>
@endsection
