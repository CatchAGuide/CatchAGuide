// Rental boat card — expand/collapse only (gallery modal via listingGalleryModal.js)
(function() {
    'use strict';

    function initRentalBoatCards() {
        const cards = document.querySelectorAll('[data-rental-boat-card]');

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
                    card.classList.add('rental-boat-card--expanded');
                    if (toggleText) toggleText.textContent = labelLess;
                    if (toggleIcon) toggleIcon.textContent = '▲';
                } else {
                    card.classList.remove('rental-boat-card--expanded');
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
                if (!expanded && !e.target.closest('button') && !e.target.closest('.rental-boat-card__gallery')) {
                    toggleExpanded();
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initRentalBoatCards);
    } else {
        initRentalBoatCards();
    }

    window.initRentalBoatCards = initRentalBoatCards;
})();
