/**
 * Scroll reveal for listing cards and product-page sections.
 *
 * Mark an element with `data-reveal`; it fades/slides in the first time it
 * scrolls into view. Built to stay off the critical path:
 *  - Anything already on screen when the page loads is never hidden, so the
 *    hero/LCP and first cards paint exactly as before (no flash, no delay).
 *  - Only `opacity` + `translate` animate (compositor-only, no CLS), and the
 *    animation class is dropped afterwards so no transform lingers to break
 *    sticky/fixed descendants or existing hover transforms.
 *  - No-op without IntersectionObserver or under prefers-reduced-motion, and
 *    elements already animated by the legacy WOW.js (`.wow`) are skipped, as
 *    are `data-reveal` elements nested inside another one.
 */

const SELECTOR = "[data-reveal]";
const PENDING = "is-reveal-pending";
const REVEALING = "is-revealing";
const STAGGER_MS = 70;
const MAX_STAGGER_STEPS = 4;

let revealObserver = null;

function reveal(entries) {
  let step = 0;

  entries.forEach((entry) => {
    if (!entry.isIntersecting) {
      return;
    }

    const el = entry.target;
    revealObserver.unobserve(el);

    // Cards entering together cascade; a lone section enters immediately.
    el.style.setProperty("--reveal-delay", `${Math.min(step, MAX_STAGGER_STEPS) * STAGGER_MS}ms`);
    step += 1;

    el.addEventListener(
      "animationend",
      () => {
        el.classList.remove(REVEALING);
        el.style.removeProperty("--reveal-delay");
      },
      { once: true }
    );
    el.classList.add(REVEALING);
    el.classList.remove(PENDING);
  });
}

// First pass: decide what is below the fold. Elements visible at load are left alone.
function classify(entries, observer) {
  entries.forEach((entry) => {
    const el = entry.target;
    observer.unobserve(el);

    if (entry.isIntersecting) {
      return;
    }

    el.classList.add(PENDING);
    revealObserver.observe(el);
  });
}

export function initScrollReveal(root = document) {
  if (!("IntersectionObserver" in window)) {
    return;
  }

  if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
    return;
  }

  const targets = Array.from(root.querySelectorAll(SELECTOR)).filter(
    (el) =>
      !el.dataset.revealBound &&
      !el.closest(".wow") &&
      // Nested reveals would stack two animations; the outermost one wins.
      !el.parentElement?.closest(SELECTOR)
  );

  if (!targets.length) {
    return;
  }

  revealObserver =
    revealObserver ||
    new IntersectionObserver(reveal, {
      rootMargin: "0px 0px -8% 0px",
      threshold: 0.08,
    });

  const classifyObserver = new IntersectionObserver(classify);

  targets.forEach((el) => {
    el.dataset.revealBound = "1";
    classifyObserver.observe(el);
  });
}
