{{-- Overlay hero header motion + solid nav for destination/targets category pages. --}}
@once
@include('layouts.partials.offers-persons-stepper-script')
<script>
(function () {
    var page = document.querySelector('[data-category-hero-page]');
    var shell = document.querySelector('[data-category-header-shell]');
    var hero = document.querySelector('[data-category-hero]');
    var nav = shell ? shell.querySelector('.cag-site-nav') : null;
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (page && !reduceMotion) {
        page.classList.add('has-offers-motion');
        window.requestAnimationFrame(function () {
            page.classList.add('is-offers-ready');
        });
    } else if (page) {
        page.classList.add('is-offers-ready');
    }

    if (nav && hero) {
        var syncNavSolid = function () {
            nav.classList.toggle('is-solid', hero.getBoundingClientRect().bottom <= nav.offsetHeight + 12);
        };
        syncNavSolid();
        window.addEventListener('scroll', syncNavSolid, { passive: true });
        window.addEventListener('resize', syncNavSolid);
    }
})();
</script>
@endonce
