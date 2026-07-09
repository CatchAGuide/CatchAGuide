export const initVacationCardGalleries = () => {
  document.querySelectorAll("[data-vacation-card-gallery]").forEach((gallery) => {
    if (gallery.dataset.galleryInit === "1") {
      return;
    }

    const images = JSON.parse(gallery.getAttribute("data-gallery-images") || "[]");
    const imageEl = gallery.querySelector("[data-vacation-card-gallery-image]");
    const prevBtn = gallery.querySelector("[data-vacation-card-prev]");
    const nextBtn = gallery.querySelector("[data-vacation-card-next]");
    const counter = gallery.querySelector("[data-vacation-card-counter]");

    if (!imageEl || images.length === 0) {
      return;
    }

    gallery.dataset.galleryInit = "1";

    let currentIndex = 0;

    const updateImage = (index) => {
      if (index < 0) {
        index = images.length - 1;
      }
      if (index >= images.length) {
        index = 0;
      }

      currentIndex = index;
      imageEl.src = images[currentIndex];

      if (counter) {
        counter.textContent = `${currentIndex + 1}/${images.length}`;
      }
    };

    prevBtn?.addEventListener("click", (event) => {
      event.preventDefault();
      event.stopPropagation();
      updateImage(currentIndex - 1);
    });

    nextBtn?.addEventListener("click", (event) => {
      event.preventDefault();
      event.stopPropagation();
      updateImage(currentIndex + 1);
    });
  });
};
