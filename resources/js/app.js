// require("./bootstrap");
import { initSelfSwiper } from "./modules/selfSwiper";
import { initVacationCardGalleries } from "./modules/vacationCardGallery";
import { initVacationCampFishTags } from "./modules/vacationCampFishTags";
import { createIcons, icons } from 'lucide';

window.addEventListener("load", () => {
  initSelfSwiper();
});

document.addEventListener('DOMContentLoaded', () => {
  initVacationCardGalleries();
  initVacationCampFishTags();
  createIcons({ icons });
});