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

    var initSpeciesSpotlight = function (speciesSection) {
        if (!speciesSection || speciesSection.getAttribute('data-cag-species-init') === '1') return;
        speciesSection.setAttribute('data-cag-species-init', '1');

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

        var ensureCardVisible = function (card, behavior) {
            if (!speciesViewport || !card) return;
            behavior = behavior || 'smooth';
            var viewRect = speciesViewport.getBoundingClientRect();
            var cardRect = card.getBoundingClientRect();
            var pad = 16;
            var delta = 0;

            if (cardRect.left < viewRect.left + pad) {
                delta = cardRect.left - viewRect.left - pad;
            } else if (cardRect.right > viewRect.right - pad) {
                delta = cardRect.right - viewRect.right + pad;
            }

            if (delta !== 0) {
                speciesViewport.scrollBy({ left: delta, behavior: behavior });
            }
        };

        var setSpotlight = function (index) {
            if (!speciesCards.length) return;
            var prevIndex = spotlightIndex;
            clearSpotlight();
            spotlightIndex = (index + speciesCards.length) % speciesCards.length;
            var card = speciesCards[spotlightIndex];
            card.classList.add('is-spotlight');

            var wrappedToStart = prevIndex === speciesCards.length - 1 && spotlightIndex === 0;
            if (wrappedToStart) {
                speciesViewport.scrollTo({ left: 0, behavior: 'smooth' });
            } else {
                ensureCardVisible(card, 'smooth');
            }
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
            if (speciesViewport && speciesViewport.classList.contains('is-dragging')) return;
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

        speciesCards.forEach(function (card) {
            card.addEventListener('mouseenter', pauseSpotlight);
            card.addEventListener('mouseleave', resumeSpotlight);
            card.addEventListener('focusin', pauseSpotlight);
            card.addEventListener('focusout', resumeSpotlight);
        });

        if (speciesViewport) {
            enableDragScroll(speciesViewport, { onInteract: pauseSpotlight });
            speciesViewport.addEventListener('touchstart', pauseSpotlight, { passive: true });
            speciesViewport.addEventListener('touchend', function () {
                window.setTimeout(resumeSpotlight, 1200);
            }, { passive: true });
            speciesViewport.addEventListener('pointerup', function () {
                if (!spotlightPaused) return;
                window.setTimeout(resumeSpotlight, 1200);
            });
        }
    };

    document.querySelectorAll('[data-species-spotlight]').forEach(initSpeciesSpotlight);
})();
</script>
