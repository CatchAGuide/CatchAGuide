// Special Offer Card Component - Vanilla JavaScript
(function() {
    'use strict';

    function initSpecialOfferCards() {
        const cards = document.querySelectorAll('[data-special-offer-card]');
        
        cards.forEach(function(card) {
            // Skip if already initialized
            if (card.dataset.initialized) {
                return;
            }
            card.dataset.initialized = 'true';

            let expanded = false;
            let currentImageIndex = 0;
            
            // Get all interactive elements first
            const toggleBtn = card.querySelector('[data-toggle-btn]');
            const toggleText = card.querySelector('[data-toggle-text]');
            const toggleIcon = card.querySelector('[data-toggle-icon]');
            const expandedElements = card.querySelectorAll('[data-expanded-only]');
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
            const modal = card.querySelector('[data-special-offer-modal]');
            const modalImage = modal ? modal.querySelector('.special-offer-gallery-modal__image') : null;
            const modalClose = modal ? modal.querySelector('.special-offer-gallery-modal__close') : null;
            const modalPrev = modal ? modal.querySelector('.special-offer-gallery-modal__prev') : null;
            const modalNext = modal ? modal.querySelector('.special-offer-gallery-modal__next') : null;
            const modalCurrent = modal ? modal.querySelector('.special-offer-gallery-modal__current') : null;
            const modalTotal = modal ? modal.querySelector('.special-offer-gallery-modal__total') : null;

            // Toggle expand/collapse
            const labelMore = (toggleBtn && toggleBtn.dataset.labelMore) || (toggleText ? toggleText.textContent : 'Show More');
            const labelLess = (toggleBtn && toggleBtn.dataset.labelLess) || 'Show Less';

            function toggleExpanded() {
                expanded = !expanded;

                if (expanded) {
                    card.classList.add('special-offer-card--expanded');
                    if (toggleText) toggleText.textContent = labelLess;
                    if (toggleIcon) toggleIcon.textContent = '▲';
                } else {
                    card.classList.remove('special-offer-card--expanded');
                    if (toggleText) toggleText.textContent = labelMore;
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
                    updateModalImage();
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
                    modal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                }
            }
            
            function closeModal() {
                if (modal) {
                    modal.style.display = 'none';
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
                if (images.length > 1) {
                    currentImageIndex = (currentImageIndex + 1) % images.length;
                    updateImage();
                    updateModalImage();
                }
            }
            
            function prevModalImage() {
                if (images.length > 1) {
                    currentImageIndex = (currentImageIndex - 1 + images.length) % images.length;
                    updateImage();
                    updateModalImage();
                }
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
                if (!expanded && !e.target.closest('button') && !e.target.closest('.special-offer-gallery')) {
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
                    if (modal.style.display === 'flex') {
                        if (event.key === 'Escape') {
                            closeModal();
                        } else if (event.key === 'ArrowLeft') {
                            prevModalImage();
                        } else if (event.key === 'ArrowRight') {
                            nextModalImage();
                        }
                    }
                });
            }

            // Anchor point functionality
            const anchorBoxes = card.querySelectorAll('[data-anchor-scroll]');
            anchorBoxes.forEach(function(anchorBox) {
                anchorBox.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const anchorType = anchorBox.dataset.anchorType;
                    const anchorId = anchorBox.dataset.anchorId;
                    const href = anchorBox.getAttribute('href');
                    
                    // Add glowing animation class to the clicked button
                    anchorBox.classList.add('anchor-selected');
                    
                    // Remove animation class after animation completes
                    setTimeout(function() {
                        anchorBox.classList.remove('anchor-selected');
                    }, 1500);
                    
                    // Function to find and scroll to target card
                    function findAndScrollToTarget() {
                        let targetCard = null;
                        
                        // Debug: Log what we're looking for
                        console.log('Looking for:', anchorType, 'ID:', anchorId, 'href:', href);
                        
                        // First, try to find by href (ID selector)
                        if (href) {
                            targetCard = document.querySelector(href);
                            if (targetCard) {
                                console.log('✓ Found target card by href:', href);
                            } else {
                                console.log('✗ Not found by href:', href);
                            }
                        }
                        
                        // If not found by href, try by ID directly
                        if (!targetCard && anchorId) {
                            let idSelector = '';
                            switch(anchorType) {
                                case 'accommodation':
                                    idSelector = '#accommodation-' + anchorId;
                                    break;
                                case 'boat':
                                    idSelector = '#rental-boat-' + anchorId;
                                    break;
                                case 'guiding':
                                    idSelector = '#guiding-' + anchorId;
                                    break;
                            }
                            
                            if (idSelector) {
                                targetCard = document.querySelector(idSelector);
                                if (targetCard) {
                                    console.log('✓ Found target card by ID selector:', idSelector);
                                } else {
                                    console.log('✗ Not found by ID selector:', idSelector);
                                }
                            }
                        }
                        
                        // If still not found, try by data attribute
                        if (!targetCard && anchorId) {
                            let dataSelector = '';
                            switch(anchorType) {
                                case 'accommodation':
                                    dataSelector = '[data-accommodation-id="' + anchorId + '"]';
                                    break;
                                case 'boat':
                                    dataSelector = '[data-rental-boat-id="' + anchorId + '"]';
                                    break;
                                case 'guiding':
                                    dataSelector = '[data-guiding-id="' + anchorId + '"]';
                                    break;
                            }
                            
                            if (dataSelector) {
                                targetCard = document.querySelector(dataSelector);
                                if (targetCard) {
                                    console.log('✓ Found target card by data attribute:', dataSelector);
                                } else {
                                    console.log('✗ Not found by data attribute:', dataSelector);
                                    
                                    // Debug: List all available IDs
                                    if (anchorType === 'accommodation') {
                                        const allCards = document.querySelectorAll('[data-accommodation-id]');
                                        console.log('Available accommodation IDs:');
                                        allCards.forEach(function(card, index) {
                                            const id = card.getAttribute('data-accommodation-id');
                                            const elementId = card.id;
                                            console.log('  Card ' + (index + 1) + ':', 'data-id=' + id, 'element-id=' + elementId);
                                        });
                                    }
                                }
                            }
                        }
                        
                        if (targetCard) {
                            console.log('✓ Scrolling to target card:', targetCard);
                            
                            // Scroll to the target card immediately with smooth behavior
                            targetCard.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center',
                                inline: 'nearest'
                            });
                            
                            // Wait for scroll to complete, then highlight the card
                            setTimeout(function() {
                                highlightCardByAnchor(anchorType, anchorId, targetCard);
                            }, 1000);
                            
                            return true;
                        }
                        
                        return false;
                    }
                    
                    // Try to find and scroll immediately
                    if (!findAndScrollToTarget()) {
                        // If not found, wait a bit and retry (for dynamically loaded content)
                        setTimeout(function() {
                            if (!findAndScrollToTarget()) {
                                console.warn('✗ Target card not found after retry:', anchorType, anchorId, 'href:', href);
                            }
                        }, 500);
                    }
                });
            });
            
            // Toggle show/hide for anchor categories
            const toggleButtons = card.querySelectorAll('[data-toggle-category]');
            toggleButtons.forEach(function(toggleBtn) {
                toggleBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const categoryType = toggleBtn.dataset.toggleCategory;
                    const category = card.querySelector('[data-category-type="' + categoryType + '"]');
                    
                    if (category) {
                        const hiddenBoxes = category.querySelectorAll('.special-offer-card__anchor-box--hidden');
                        const isExpanded = toggleBtn.getAttribute('aria-expanded') === 'true';
                        
                        if (isExpanded) {
                            // Hide items beyond the first 3
                            hiddenBoxes.forEach(function(box) {
                                box.classList.remove('special-offer-card__anchor-box--visible');
                                box.classList.add('special-offer-card__anchor-box--hidden');
                            });
                            toggleBtn.setAttribute('aria-expanded', 'false');
                        } else {
                            // Show all items
                            hiddenBoxes.forEach(function(box) {
                                box.classList.remove('special-offer-card__anchor-box--hidden');
                                box.classList.add('special-offer-card__anchor-box--visible');
                            });
                            toggleBtn.setAttribute('aria-expanded', 'true');
                        }
                    }
                });
            });
        });
    }

    // Function to highlight a specific card component based on anchor type and ID
    function highlightCardByAnchor(anchorType, anchorId, targetCardElement) {
        let targetCard = targetCardElement;
        
        // If target card not provided, try to find it
        if (!targetCard) {
            let idSelector = '';
            let cardSelector = '';
            
            switch(anchorType) {
                case 'accommodation':
                    idSelector = '#accommodation-' + anchorId;
                    cardSelector = '[data-accommodation-id="' + anchorId + '"]';
                    break;
                case 'boat':
                    idSelector = '#rental-boat-' + anchorId;
                    cardSelector = '[data-rental-boat-id="' + anchorId + '"]';
                    break;
                case 'guiding':
                    idSelector = '#guiding-' + anchorId;
                    cardSelector = '[data-guiding-id="' + anchorId + '"]';
                    break;
            }
            
            // Try to find by ID first, then fallback to data attribute
            if (idSelector) {
                targetCard = document.querySelector(idSelector);
            }
            
            if (!targetCard && cardSelector) {
                targetCard = document.querySelector(cardSelector);
            }
        }
        
        if (targetCard) {
            // Remove any existing highlight first
            targetCard.classList.remove('card-highlighted');
            
            // Force a reflow to ensure the class removal is processed
            void targetCard.offsetHeight;
            
            // Ensure the card is visible in viewport before highlighting
            const rect = targetCard.getBoundingClientRect();
            const viewportHeight = window.innerHeight || document.documentElement.clientHeight;
            const viewportWidth = window.innerWidth || document.documentElement.clientWidth;
            
            const isVisible = (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= viewportHeight &&
                rect.right <= viewportWidth
            );
            
            // If not fully visible, scroll to center it
            if (!isVisible || rect.top < 100 || rect.bottom > viewportHeight - 100) {
                targetCard.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center',
                    inline: 'nearest'
                });
                
                // Wait a bit longer if we had to scroll
                setTimeout(function() {
                    targetCard.classList.add('card-highlighted');
                    
                    // Remove highlight after animation completes (2 seconds)
                    setTimeout(function() {
                        if (targetCard) {
                            targetCard.classList.remove('card-highlighted');
                        }
                    }, 2000);
                }, 400);
            } else {
                // Card is already visible, highlight immediately
                targetCard.classList.add('card-highlighted');
                
                // Remove highlight after animation completes (2 seconds)
                setTimeout(function() {
                    if (targetCard) {
                        targetCard.classList.remove('card-highlighted');
                    }
                }, 2000);
            }
        } else {
            console.warn('Could not find target card for anchor:', anchorType, anchorId);
        }
    }

    // Initialize cards when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initSpecialOfferCards();
        });
    } else {
        initSpecialOfferCards();
    }
    
    // Also expose globally for manual re-initialization if needed
    window.initSpecialOfferCards = initSpecialOfferCards;
    
})();


