/**
 * Shared listing gallery lightbox (offers dock pattern).
 * Hooks: [data-vacation-gallery] + [data-vacation-modal] + offers gallery data attrs.
 */
export function initListingGalleryModals() {
  let activeModal = null;
  const imageCache = new Map();
  const isCoarsePointer =
    window.matchMedia("(pointer: coarse)").matches ||
    window.matchMedia("(max-width: 767.98px)").matches;

  function preloadImage(src) {
    if (!src) {
      return Promise.resolve(null);
    }
    if (imageCache.has(src)) {
      return imageCache.get(src);
    }
    const promise = new Promise(function (resolve, reject) {
      const img = new Image();
      img.decoding = "async";
      img.onload = function () {
        resolve(img);
      };
      img.onerror = function () {
        imageCache.delete(src);
        reject(new Error("gallery image failed"));
      };
      img.src = src;
    });
    imageCache.set(src, promise);
    return promise;
  }

  function cardPrevSelector(gallery) {
    return (
      gallery.querySelector("[data-offers-gallery-prev]") ||
      gallery.querySelector("[data-vacation-prev-image]") ||
      gallery.querySelector("[data-prev-image]")
    );
  }

  function cardNextSelector(gallery) {
    return (
      gallery.querySelector("[data-offers-gallery-next]") ||
      gallery.querySelector("[data-vacation-next-image]") ||
      gallery.querySelector("[data-next-image]")
    );
  }

  document.querySelectorAll("[data-vacation-gallery]").forEach(function (gallery) {
    if (gallery.dataset.listingGalleryInit === "1") {
      return;
    }
    gallery.dataset.listingGalleryInit = "1";

    const galleryId = gallery.getAttribute("data-vacation-gallery");
    const images = JSON.parse(gallery.getAttribute("data-gallery-images") || "[]");
    const imageEl =
      gallery.querySelector("[data-vacation-gallery-image]") ||
      gallery.querySelector("[data-gallery-image]");
    const counter = gallery.querySelector("[data-vacation-image-counter]") ||
      gallery.querySelector("[data-image-counter]");
    const modal = document.querySelector('[data-vacation-modal="' + galleryId + '"]');
    const modalImage = modal
      ? modal.querySelector("[data-offers-gallery-modal-image]")
      : null;
    const modalPrevButtons = modal
      ? modal.querySelectorAll("[data-offers-gallery-modal-prev]")
      : [];
    const modalNextButtons = modal
      ? modal.querySelectorAll("[data-offers-gallery-modal-next]")
      : [];
    const modalClose = modal
      ? modal.querySelector("[data-offers-gallery-modal-close]")
      : null;
    const modalCurrent = modal
      ? modal.querySelector(".vacation-gallery-modal__current")
      : null;
    const stage = modal ? modal.querySelector("[data-offers-gallery-stage]") : null;
    const loader = modal ? modal.querySelector("[data-offers-gallery-loader]") : null;
    const cardPrev = cardPrevSelector(gallery);
    const cardNext = cardNextSelector(gallery);
    const openTriggers = gallery.querySelectorAll(
      "[data-vacation-open-modal], [data-open-modal], [data-guiding-open-modal]"
    );

    if (images.length === 0) {
      return;
    }

    let currentIndex = 0;
    let touchStartX = 0;
    let touchStartY = 0;
    let loadToken = 0;

    function warmNeighbors(index) {
      if (images.length < 2) {
        return;
      }
      preloadImage(images[(index + 1) % images.length]);
      if (!isCoarsePointer) {
        preloadImage(images[(index - 1 + images.length) % images.length]);
      }
    }

    function setLoader(visible) {
      if (!loader || !modal) {
        return;
      }
      if (visible) {
        loader.hidden = false;
        loader.setAttribute("aria-hidden", "false");
        modal.classList.add("is-loading");
      } else {
        loader.hidden = true;
        loader.setAttribute("aria-hidden", "true");
        modal.classList.remove("is-loading");
      }
    }

    function updateCounters(index) {
      if (counter) {
        counter.textContent = index + 1 + "/" + images.length;
      }
      if (modalCurrent) {
        modalCurrent.textContent = String(index + 1);
      }
    }

    function updateCardThumb(index) {
      if (imageEl) {
        imageEl.src = images[index];
      }
    }

    function showModalImage(src, index, options) {
      const opts = options || {};
      const animate = opts.animate !== false;
      const token = ++loadToken;

      updateCounters(index);
      updateCardThumb(index);

      if (!modalImage) {
        currentIndex = index;
        warmNeighbors(index);
        return Promise.resolve();
      }

      const alreadyShowing =
        modalImage.getAttribute("src") === src &&
        modalImage.classList.contains("is-ready") &&
        modalImage.complete;

      if (alreadyShowing && !opts.force) {
        currentIndex = index;
        setLoader(false);
        warmNeighbors(index);
        return Promise.resolve();
      }

      if (animate) {
        modalImage.classList.remove("is-ready");
        modalImage.classList.add("is-fading");
      }
      setLoader(true);

      return preloadImage(src)
        .catch(function () {
          return null;
        })
        .then(function () {
          if (token !== loadToken) {
            return;
          }
          modalImage.src = src;
          if (imageEl && imageEl.alt) {
            modalImage.alt = imageEl.alt;
          }

          const reveal = function () {
            if (token !== loadToken) {
              return;
            }
            requestAnimationFrame(function () {
              modalImage.classList.remove("is-fading");
              modalImage.classList.add("is-ready");
              setLoader(false);
              currentIndex = index;
              warmNeighbors(index);
            });
          };

          if (modalImage.complete) {
            reveal();
          } else {
            modalImage.addEventListener("load", reveal, { once: true });
            modalImage.addEventListener(
              "error",
              function () {
                if (token !== loadToken) {
                  return;
                }
                setLoader(false);
                modalImage.classList.remove("is-fading");
                modalImage.classList.add("is-ready");
                currentIndex = index;
                warmNeighbors(index);
              },
              { once: true }
            );
          }
        });
    }

    function goTo(index, options) {
      const nextIndex = (index + images.length) % images.length;
      return showModalImage(images[nextIndex], nextIndex, options);
    }

    function syncIndexFromCard() {
      if (!imageEl || !imageEl.src) {
        return;
      }
      const currentSrc = imageEl.currentSrc || imageEl.src;
      const match = images.findIndex(function (src) {
        return currentSrc.indexOf(src) !== -1 || src.indexOf(currentSrc) !== -1;
      });
      if (match >= 0) {
        currentIndex = match;
      }
    }

    function openModal(startIndex) {
      if (!modal) {
        return;
      }
      if (modal.parentElement !== document.body) {
        document.body.appendChild(modal);
      }
      if (typeof startIndex === "number" && startIndex >= 0) {
        currentIndex = startIndex % images.length;
      } else {
        syncIndexFromCard();
      }
      modal.classList.add("show");
      document.body.style.overflow = "hidden";
      activeModal = {
        modal: modal,
        next: function () {
          goTo(currentIndex + 1, { fromNav: true });
        },
        prev: function () {
          goTo(currentIndex - 1, { fromNav: true });
        },
        close: closeModal,
        hasMultiple: images.length > 1,
      };
      preloadImage(images[currentIndex]);
      goTo(currentIndex, {
        force: false,
        animate: !(
          modalImage &&
          modalImage.complete &&
          modalImage.classList.contains("is-ready") &&
          modalImage.getAttribute("src") === images[currentIndex]
        ),
      });
    }

    function closeModal() {
      if (!modal) {
        return;
      }
      loadToken += 1;
      setLoader(false);
      modal.classList.remove("show");
      document.body.style.overflow = "";
      if (activeModal && activeModal.modal === modal) {
        activeModal = null;
      }
    }

    openTriggers.forEach(function (trigger, triggerIndex) {
      trigger.addEventListener("click", function (event) {
        event.preventDefault();
        event.stopPropagation();
        openModal(openTriggers.length > 1 ? triggerIndex : undefined);
      });
    });

    gallery.querySelectorAll("[data-gallery-index]").forEach(function (thumb) {
      if (thumb.matches("[data-vacation-open-modal], [data-open-modal], [data-guiding-open-modal]")) {
        return;
      }
      thumb.addEventListener("click", function (event) {
        event.preventDefault();
        event.stopPropagation();
        const index = parseInt(thumb.getAttribute("data-gallery-index"), 10);
        if (Number.isNaN(index)) {
          return;
        }
        openModal(index);
      });
    });

    if (cardPrev) {
      cardPrev.addEventListener("click", function (event) {
        event.preventDefault();
        event.stopPropagation();
        currentIndex = (currentIndex - 1 + images.length) % images.length;
        updateCardThumb(currentIndex);
        updateCounters(currentIndex);
      });
    }
    if (cardNext) {
      cardNext.addEventListener("click", function (event) {
        event.preventDefault();
        event.stopPropagation();
        currentIndex = (currentIndex + 1) % images.length;
        updateCardThumb(currentIndex);
        updateCounters(currentIndex);
      });
    }

    if (modalClose) {
      modalClose.addEventListener("click", function (event) {
        event.preventDefault();
        event.stopPropagation();
        closeModal();
      });
    }
    if (modal) {
      modal.addEventListener("click", function (event) {
        if (
          event.target === modal ||
          event.target.classList.contains("offers-gallery-modal__shell")
        ) {
          closeModal();
        }
      });
    }
    modalPrevButtons.forEach(function (btn) {
      btn.addEventListener("click", function (event) {
        event.preventDefault();
        event.stopPropagation();
        goTo(currentIndex - 1, { fromNav: true });
      });
    });
    modalNextButtons.forEach(function (btn) {
      btn.addEventListener("click", function (event) {
        event.preventDefault();
        event.stopPropagation();
        goTo(currentIndex + 1, { fromNav: true });
      });
    });

    if (stage && images.length > 1) {
      stage.addEventListener(
        "touchstart",
        function (event) {
          if (!event.changedTouches || !event.changedTouches[0]) {
            return;
          }
          touchStartX = event.changedTouches[0].clientX;
          touchStartY = event.changedTouches[0].clientY;
        },
        { passive: true }
      );

      stage.addEventListener(
        "touchend",
        function (event) {
          if (!event.changedTouches || !event.changedTouches[0]) {
            return;
          }
          const deltaX = event.changedTouches[0].clientX - touchStartX;
          const deltaY = event.changedTouches[0].clientY - touchStartY;
          if (Math.abs(deltaX) < 48 || Math.abs(deltaX) < Math.abs(deltaY)) {
            return;
          }
          if (deltaX < 0) {
            goTo(currentIndex + 1, { fromNav: true });
          } else {
            goTo(currentIndex - 1, { fromNav: true });
          }
        },
        { passive: true }
      );
    }
  });

  if (!window.__listingGalleryKeydownBound) {
    window.__listingGalleryKeydownBound = true;
    document.addEventListener("keydown", function (event) {
      if (!activeModal) {
        return;
      }
      if (event.key === "Escape") {
        activeModal.close();
        return;
      }
      if (!activeModal.hasMultiple) {
        return;
      }
      if (event.key === "ArrowRight") {
        event.preventDefault();
        activeModal.next();
      } else if (event.key === "ArrowLeft") {
        event.preventDefault();
        activeModal.prev();
      }
    });
  }
}
