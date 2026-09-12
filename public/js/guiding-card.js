// Guiding Card Component — expand/collapse only (gallery modal via listingGalleryModal.js)
(function() {
    'use strict';

    function initGuidingCards() {
        const cards = document.querySelectorAll('[data-guiding-card]');

        cards.forEach(function(card) {
            if (card.dataset.initialized) {
                return;
            }
            card.dataset.initialized = 'true';

            let expanded = false;
            const toggleBtn = card.querySelector('[data-toggle-btn]');
            const toggleText = card.querySelector('[data-toggle-text]');
            const toggleIcon = card.querySelector('[data-toggle-icon]');
            const labelMore = (toggleBtn && toggleBtn.dataset.labelMore) || (toggleText ? toggleText.textContent : 'Show More');
            const labelLess = (toggleBtn && toggleBtn.dataset.labelLess) || 'Show Less';

            function toggleExpanded() {
                expanded = !expanded;
                if (expanded) {
                    card.classList.add('guiding-card--expanded');
                    if (toggleText) toggleText.textContent = labelLess;
                    if (toggleIcon) toggleIcon.textContent = '▲';
                } else {
                    card.classList.remove('guiding-card--expanded');
                    if (toggleText) toggleText.textContent = labelMore;
                    if (toggleIcon) toggleIcon.textContent = '▼';
                }
            }

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    toggleExpanded();
                });
            }

            card.addEventListener('click', function(e) {
                if (!expanded && !e.target.closest('button') && !e.target.closest('.guiding-card__gallery')) {
                    toggleExpanded();
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initGuidingCards);
    } else {
        initGuidingCards();
    }

    window.initGuidingCards = initGuidingCards;
})();
