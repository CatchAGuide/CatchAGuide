<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-vacation-gallery]').forEach(function (gallery) {
        const galleryId = gallery.getAttribute('data-vacation-gallery');
        const images = JSON.parse(gallery.getAttribute('data-gallery-images') || '[]');
        const imageEl = gallery.querySelector('[data-vacation-gallery-image]');
        const counter = gallery.querySelector('[data-vacation-image-counter]');
        const modal = document.querySelector('[data-vacation-modal="' + galleryId + '"]');
        const modalImage = modal ? modal.querySelector('.vacation-gallery-modal__image') : null;
        const modalPrev = modal ? modal.querySelector('.vacation-gallery-modal__prev') : null;
        const modalNext = modal ? modal.querySelector('.vacation-gallery-modal__next') : null;
        const modalClose = modal ? modal.querySelector('.vacation-gallery-modal__close') : null;
        const modalCurrent = modal ? modal.querySelector('.vacation-gallery-modal__current') : null;

        if (images.length === 0) {
            return;
        }

        let currentIndex = 0;

        function render(index) {
            currentIndex = (index + images.length) % images.length;
            if (imageEl) {
                imageEl.src = images[currentIndex];
            }
            if (counter) {
                counter.textContent = (currentIndex + 1) + '/' + images.length;
            }
            if (modalImage) {
                modalImage.src = images[currentIndex];
            }
            if (modalCurrent) {
                modalCurrent.textContent = String(currentIndex + 1);
            }
        }

        function openModal() {
            if (!modal) {
                return;
            }
            // Escape card overflow/transform so fixed overlay covers the viewport
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
            render(currentIndex);
        }

        function closeModal() {
            if (!modal) {
                return;
            }
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }

        gallery.querySelector('[data-vacation-open-modal]')?.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            openModal();
        });

        gallery.querySelector('[data-offers-gallery-prev]')?.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            render(currentIndex - 1);
        });
        gallery.querySelector('[data-offers-gallery-next]')?.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            render(currentIndex + 1);
        });

        modalClose?.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            closeModal();
        });
        modal?.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });
        modalPrev?.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            render(currentIndex - 1);
        });
        modalNext?.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            render(currentIndex + 1);
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') {
            return;
        }
        document.querySelectorAll('.vacation-gallery-modal.show').forEach(function (openModal) {
            openModal.classList.remove('show');
        });
        document.body.style.overflow = '';
    });
});
</script>
