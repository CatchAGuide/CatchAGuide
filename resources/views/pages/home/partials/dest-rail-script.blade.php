<script>
(function () {
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
    if (!rail || rail.getAttribute('data-cag-dest-rail-init') === '1') return;
    rail.setAttribute('data-cag-dest-rail-init', '1');

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
    if (reduceMotion) return;

    var loopWidth = 0;
    var syncLoop = function () {
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
})();
</script>
