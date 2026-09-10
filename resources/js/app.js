// require("./bootstrap");
import { initSelfSwiper } from "./modules/selfSwiper";
import { initVacationCardGalleries } from "./modules/vacationCardGallery";
import { initVacationCampFishTags } from "./modules/vacationCampFishTags";
import { initPageLoader } from "./modules/pageLoader";
import { initBottomNavViewport } from "./modules/bottomNavViewport";
import { createIcons, icons } from 'lucide';

window.addEventListener("load", () => {
  initSelfSwiper();
});

document.addEventListener('DOMContentLoaded', () => {
  initVacationCardGalleries();
  initVacationCampFishTags();
  initPageLoader();
  initBottomNavViewport();
  createIcons({ icons });
});