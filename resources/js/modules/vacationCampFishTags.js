function clampFishTags(container) {
  const tags = [...container.querySelectorAll("[data-vacation-fish-tag]")];
  const more = container.querySelector("[data-vacation-fish-tag-more]");

  if (!tags.length || !more) {
    return;
  }

  tags.forEach((tag) => {
    tag.style.display = "";
  });
  more.style.display = "none";
  more.hidden = true;

  const fits = () => container.scrollWidth <= container.clientWidth + 1;

  if (fits()) {
    return;
  }

  let visible = tags.length;

  while (visible > 0) {
    visible -= 1;
    tags[visible].style.display = "none";

    const hidden = tags.length - visible;
    more.textContent = `+${hidden}`;
    more.style.display = "";
    more.hidden = false;

    if (fits()) {
      break;
    }
  }
}

export function initVacationCampFishTags() {
  const containers = document.querySelectorAll("[data-vacation-fish-tags]");

  if (!containers.length) {
    return;
  }

  const clampAll = () => {
    containers.forEach(clampFishTags);
  };

  clampAll();

  if (typeof ResizeObserver !== "undefined") {
    const observer = new ResizeObserver(() => clampAll());
    containers.forEach((container) => observer.observe(container));
  } else {
    window.addEventListener("resize", clampAll);
  }
}
