// Guiding Card Component - Vanilla JavaScript
(function() {
    'use strict';

    function initGuidingCards() {
        const cards = document.querySelectorAll('[data-guiding-card]');

        cards.forEach(function(card) {
            // Skip if already initialized
            if (card.dataset.initialized) {
                return;
            }
            card.dataset.initialized = 'true';

            let expanded = false;
            let currentImageIndex = 0;

            // Get all interactive elements
            const toggleBtn = card.querySelector('[data-toggle-btn]');
            const toggleText = card.querySelector('[data-toggle-text]');
            const toggleIcon = card.querySelector('[data-toggle-icon]');
            const galleryImage = card.querySelector('[data-gallery-image]');

            // Get gallery images from data attribute
            const galleryData = card.querySelector('[data-gallery-images]');
            const galleryImages = galleryData ? JSON.parse(galleryData.dataset.galleryImages) : [];
            const thumbnailImage = galleryImage ? galleryImage.src : '';
            // Combine thumbnail with gallery images, removing duplicates
            const images = [thumbnailImage, ...galleryImages].filter((img, index, arr) => img && arr.indexOf(img) === index);
            const imageCounter = card.querySelector('[data-image-counter]');
            const prevBtn = card.querySelector('[data-prev-image]');
            const nextBtn = card.querySelector('[data-next-image]');

            // Modal elements
            const modal = card.querySelector('[data-guiding-modal]');
            const modalImage = modal ? modal.querySelector('.guiding-gallery-modal__image') : null;
            const modalClose = modal ? modal.querySelector('.guiding-gallery-modal__close') : null;
            const modalPrev = modal ? modal.querySelector('.guiding-gallery-modal__prev') : null;
            const modalNext = modal ? modal.querySelector('.guiding-gallery-modal__next') : null;
            const modalCurrent = modal ? modal.querySelector('.guiding-gallery-modal__current') : null;
            const modalTotal = modal ? modal.querySelector('.guiding-gallery-modal__total') : null;

            // Keep the counters in sync with the de-duplicated image list
            if (imageCounter && images.length > 0) {
                imageCounter.textContent = '1/' + images.length;
            }
            if (modalTotal) {
                modalTotal.textContent = images.length;
            }

            // Toggle expand/collapse
            function toggleExpanded() {
                expanded = !expanded;

                if (expanded) {
                    card.classList.add('guiding-card--expanded');
                    if (toggleText) toggleText.textContent = 'Show Less';
                    if (toggleIcon) toggleIcon.textContent = '▲';
                } else {
                    card.classList.remove('guiding-card--expanded');
                    if (toggleText) toggleText.textContent = 'Show More';
                    if (toggleIcon) toggleIcon.textContent = '▼';
                }
            }

            // Update gallery image
            function updateImage() {
                if (images.length > 0 && galleryImage) {
                    galleryImage.src = images[currentImageIndex];
                    if (imageCounter) {
                        imageCounter.textContent = (currentImageIndex + 1) + '/' + images.length;
                    }
                }
            }

            // Navigate to next image
            function nextImage() {
                if (images.length > 1) {
                    currentImageIndex = (currentImageIndex + 1) % images.length;
                    updateImage();
                }
            }

            // Navigate to previous image
            function prevImage() {
                if (images.length > 1) {
                    currentImageIndex = (currentImageIndex - 1 + images.length) % images.length;
                    updateImage();
                }
            }

            // Modal functions
            function openModal() {
                if (modal && images.length > 0) {
                    // Move modal to body to ensure it covers the whole page
                    if (modal.parentNode !== document.body) {
                        document.body.appendChild(modal);
                    }
                    updateModalImage();
                    modal.classList.add('show');
                    document.body.style.overflow = 'hidden';
                }
            }

            function closeModal() {
                if (modal) {
                    modal.classList.remove('show');
                    document.body.style.overflow = 'auto';
                    // Move modal back to card when closed
                    if (modal.parentNode === document.body) {
                        card.appendChild(modal);
                    }
                }
            }

            function updateModalImage() {
                if (modalImage && images.length > 0) {
                    modalImage.src = images[currentImageIndex];
                    if (modalCurrent) {
                        modalCurrent.textContent = currentImageIndex + 1;
                    }
                }
            }

            function nextModalImage() {
                nextImage();
                updateModalImage();
            }

            function prevModalImage() {
                prevImage();
                updateModalImage();
            }

            // Event listeners
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    toggleExpanded();
                });
            }

            // Card click to expand when collapsed
            card.addEventListener('click', function(e) {
                // Only expand if not already expanded and not clicking on buttons or gallery
                if (!expanded && !e.target.closest('button') && !e.target.closest('.guiding-card__gallery')) {
                    toggleExpanded();
                }
            });

            // Gallery navigation
            if (prevBtn) {
                prevBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    prevImage();
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    nextImage();
                });
            }

            // Modal event listeners
            if (galleryImage && galleryImage.hasAttribute('data-open-modal')) {
                galleryImage.addEventListener('click', function(e) {
                    e.stopPropagation();
                    openModal();
                });
            }

            if (modalClose) {
                modalClose.addEventListener('click', function(e) {
                    e.stopPropagation();
                    closeModal();
                });
            }

            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        closeModal();
                    }
                });
            }

            if (modalPrev) {
                modalPrev.addEventListener('click', function(e) {
                    e.stopPropagation();
                    prevModalImage();
                });
            }

            if (modalNext) {
                modalNext.addEventListener('click', function(e) {
                    e.stopPropagation();
                    nextModalImage();
                });
            }

            // Keyboard navigation for modal
            if (modal) {
                document.addEventListener('keydown', function(event) {
                    if (!modal.classList.contains('show')) {
                        return;
                    }

                    if (event.key === 'Escape') {
                        closeModal();
                    } else if (event.key === 'ArrowLeft') {
                        prevModalImage();
                    } else if (event.key === 'ArrowRight') {
                        nextModalImage();
                    }
                });
            }
        });
    }

    // Initialize cards when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initGuidingCards();
        });
    } else {
        initGuidingCards();
    }

    // Also expose globally for manual re-initialization if needed
    window.initGuidingCards = initGuidingCards;

})();
