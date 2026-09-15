// require("./bootstrap");
import { initSelfSwiper } from "./modules/selfSwiper";
import { initVacationCardGalleries } from "./modules/vacationCardGallery";
import { initVacationCampFishTags } from "./modules/vacationCampFishTags";
import { initListingGalleryModals } from "./modules/listingGalleryModal";
import { initPageLoader } from "./modules/pageLoader";
import { initBottomNavViewport } from "./modules/bottomNavViewport";
import { createIcons, icons } from 'lucide';

window.initListingGalleryModals = initListingGalleryModals;

window.addEventListener("load", () => {
  initSelfSwiper();
});

document.addEventListener('DOMContentLoaded', () => {
  initVacationCardGalleries();
  initVacationCampFishTags();
  initListingGalleryModals();
  initPageLoader();
  initBottomNavViewport();
  createIcons({ icons });
});