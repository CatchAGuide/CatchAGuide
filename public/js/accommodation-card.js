// Accommodation Card — expand/collapse only (gallery modal via listingGalleryModal.js)
(function() {
    'use strict';

    function initAccommodationCards() {
        const cards = document.querySelectorAll('[data-accommodation-card]');

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
                    card.classList.add('accommodation-card--expanded');
                    if (toggleText) toggleText.textContent = labelLess;
                    if (toggleIcon) toggleIcon.textContent = '▲';
                } else {
                    card.classList.remove('accommodation-card--expanded');
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
                if (!expanded && !e.target.closest('button') && !e.target.closest('.accommodation-gallery')) {
                    toggleExpanded();
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAccommodationCards);
    } else {
        initAccommodationCards();
    }

    window.initAccommodationCards = initAccommodationCards;
})();
