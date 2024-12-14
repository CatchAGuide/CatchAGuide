// require("./bootstrap");
import { initSelfSwiper } from "./modules/selfSwiper";
import { createIcons, icons } from 'lucide';

window.addEventListener("load", () => {
  initSelfSwiper();
});

// Initialize Lucide icons
document.addEventListener('DOMContentLoaded', () => {
  createIcons({ icons });
});