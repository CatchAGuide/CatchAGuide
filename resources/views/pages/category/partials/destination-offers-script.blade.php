<script>
(function () {
    var root = document.querySelector('.cag-dest-offers-wrap');
    if (!root) return;

    root.classList.add('has-reveal-js');

    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var enableDragScroll = function (el) {
        if (!el || el.getAttribute('data-cag-drag-scroll') === '1') return;
        el.setAttribute('data-cag-drag-scroll', '1');

        var activePointerId = null;
        var startX = 0;
        var startSl = 0;
        var dragging = false;
        var suppressClick = false;

        var cleanup = function () {
            if (dragging && activePointerId !== null) {
                try { el.releasePointerCapture(activePointerId); } catch (err) {}
            }
            activePointerId = null;
            dragging = false;
            el.classList.remove('is-dragging');
        };

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
            if (!dragging && Math.abs(dx) > 8) {
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

    var revealSections = Array.prototype.slice.call(root.querySelectorAll('[data-cag-reveal]'));
    var activateReveal = function (section) {
        section.classList.add('is-inview');
    };
    if (reduceMotion) {
        revealSections.forEach(activateReveal);
    } else if ('IntersectionObserver' in window) {
        var revealObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                activateReveal(entry.target);
                revealObserver.unobserve(entry.target);
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });
        revealSections.forEach(function (section) {
            revealObserver.observe(section);
        });
    } else {
        revealSections.forEach(activateReveal);
    }

    root.querySelectorAll('[data-offer-rail]').forEach(function (rail, railIndex) {
        var type = rail.getAttribute('data-offer-rail');
        var prev = root.querySelector('[data-offer-prev="' + type + '"]');
        var next = root.querySelector('[data-offer-next="' + type + '"]');
        var cards = Array.prototype.slice.call(rail.querySelectorAll('.cag-home-offer'));
        var paused = false;
        var spotlight = 0;
        var resumeTimer = null;

        var pauseTemporarily = function () {
            paused = true;
            if (resumeTimer) clearTimeout(resumeTimer);
            resumeTimer = setTimeout(function () { paused = false; }, 2800);
        };

        var scrollByDir = function (dir) {
            pauseTemporarily();
            var step = Math.min(rail.clientWidth * 0.8, 320);
            rail.scrollBy({ left: dir * step, behavior: 'smooth' });
        };

        if (prev) prev.addEventListener('click', function () { scrollByDir(-1); });
        if (next) next.addEventListener('click', function () { scrollByDir(1); });
        enableDragScroll(rail);

        var clearSpotlight = function () {
            cards.forEach(function (card) { card.classList.remove('is-spotlight'); });
        };

        var ensureVisible = function (card) {
            if (!card) return;
            var viewRect = rail.getBoundingClientRect();
            var cardRect = card.getBoundingClientRect();
            if (cardRect.left < viewRect.left + 12 || cardRect.right > viewRect.right - 12) {
                rail.scrollTo({
                    left: rail.scrollLeft + (cardRect.left - viewRect.left) - 16,
                    behavior: 'smooth'
                });
            }
        };

        var setSpotlight = function (index) {
            if (!cards.length) return;
            spotlight = ((index % cards.length) + cards.length) % cards.length;
            clearSpotlight();
            cards[spotlight].classList.add('is-spotlight');
            ensureVisible(cards[spotlight]);
        };

        rail.addEventListener('mouseenter', function () { paused = true; });
        rail.addEventListener('mouseleave', function () {
            if (!rail.classList.contains('is-dragging')) paused = false;
        });
        rail.addEventListener('touchstart', function () { paused = true; }, { passive: true });
        rail.addEventListener('wheel', pauseTemporarily, { passive: true });

        if (!reduceMotion && cards.length) {
            window.setTimeout(function () {
                setSpotlight(0);
            }, 420 + railIndex * 180);

            window.setInterval(function () {
                if (paused || cards.length < 2) return;
                setSpotlight(spotlight + 1);
            }, 3200 + railIndex * 400);
        }
    });
})();
</script>
