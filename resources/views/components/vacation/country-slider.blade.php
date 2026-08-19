@props([
    'title' => null,
    'subtitle' => null,
    'linkUrl' => null,
    'linkLabel' => null,
    'sliderId' => 'countries',
    'blockClass' => 'vacation-country-rail',
])

<div class="{{ $blockClass }}" data-vacation-country-rail data-block-class="{{ $blockClass }}" data-slider-id="{{ $sliderId }}">
    @if($title)
        <div class="{{ $blockClass }}__header">
            <div class="{{ $blockClass }}__intro">
                <h2 class="{{ $blockClass }}__title">{{ $title }}</h2>
                @if($subtitle)
                    <p class="{{ $blockClass }}__subtitle">{{ $subtitle }}</p>
                @endif
            </div>

            <div class="{{ $blockClass }}__tools">
                @if($linkUrl && $linkLabel)
                    <a href="{{ $linkUrl }}" class="{{ $blockClass }}__link d-none d-md-inline">
                        {{ $linkLabel }}
                    </a>
                @endif

                <div class="{{ $blockClass }}__nav d-none d-md-flex">
                    <button
                        type="button"
                        class="{{ $blockClass }}__btn"
                        data-vac-dest-prev="{{ $sliderId }}"
                        aria-label="{{ __('vacations.slider_prev') }}"
                    >
                        <i class="fas fa-chevron-left" aria-hidden="true"></i>
                    </button>
                    <button
                        type="button"
                        class="{{ $blockClass }}__btn"
                        data-vac-dest-next="{{ $sliderId }}"
                        aria-label="{{ __('vacations.slider_next') }}"
                    >
                        <i class="fas fa-chevron-right" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <div class="{{ $blockClass }}__viewport" data-vac-dest-rail="{{ $sliderId }}">
        <div class="{{ $blockClass }}__track" role="list">
            {{ $slot }}
        </div>
    </div>
</div>

@once
    @push('js_push')
        <script>
            (function () {
                function initVacationCountryRails() {
                    document.querySelectorAll('[data-vacation-country-rail]').forEach(function (root) {
                        if (root.dataset.railInit === '1') {
                            return;
                        }

                        var sliderId = root.dataset.sliderId;
                        var rail = root.querySelector('[data-vac-dest-rail="' + sliderId + '"]');
                        if (!rail) {
                            return;
                        }

                        root.dataset.railInit = '1';

                        var prev = root.querySelector('[data-vac-dest-prev="' + sliderId + '"]');
                        var next = root.querySelector('[data-vac-dest-next="' + sliderId + '"]');
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
                        if (reduceMotion) {
                            return;
                        }

                        var loopWidth = 0;
                        var syncLoop = function () {
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
                    });
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initVacationCountryRails);
                } else {
                    initVacationCountryRails();
                }
            })();
        </script>
    @endpush
@endonce
