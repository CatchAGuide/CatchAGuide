export const initSelfSwiper = () => {
  const carousels = document.querySelectorAll(".carousel");
  if (!carousels) return;

  carousels.forEach((carousel) => {
    const prevButton = carousel.querySelector(".carousel-control-prev");
    const nextButton = carousel.querySelector(".carousel-control-next");

    let startingX, movingX;

    function touchStart(event) {
      startingX = event.touches[0].clientX;
    }

    function touchMove(event) {
      movingX = event.touches[0].clientX;
    }

    setTimeout(() => {
      carousel.addEventListener("touchstart", touchStart);

      carousel.addEventListener("touchmove", touchMove);

      carousel.addEventListener("touchend", () => {
        if (startingX + 100 < movingX) {
          prevButton.click();
        } else if (startingX - 100 > movingX) {
          nextButton.click();
        }
      });
    }, 200);
  });
};
