export const LISTING_MOBILE_BOOK_FOOTER_SELECTOR = 'footer.site-footer, footer.cag-footer';
export const LISTING_MOBILE_BOOK_BAR_SELECTOR = '.listing-mobile-book, .guidings-book-mobile';
export const LISTING_MOBILE_BOOK_ABOVE_FOOTER_CLASS = 'is-above-footer';

/**
 * Keep the fixed mobile booking bar flush with the viewport bottom until the
 * site footer enters view, then lift it so it sits just above the footer.
 */
export function listingMobileBookBarBottom(viewportHeight, footerTop) {
  return Math.max(0, Math.round(viewportHeight - footerTop));
}

export function syncListingMobileBookBarWithFooter(bars, footer, viewportHeight) {
  if (!footer) {
    return;
  }

  const bottom = listingMobileBookBarBottom(viewportHeight, footer.getBoundingClientRect().top);
  const pinned = bottom > 0;

  bars.forEach((bar) => {
    if (pinned) {
      bar.style.bottom = `${bottom}px`;
    } else {
      bar.style.removeProperty('bottom');
    }
    bar.classList.toggle(LISTING_MOBILE_BOOK_ABOVE_FOOTER_CLASS, pinned);
  });
}

export function initListingMobileBookBar() {
  const bars = Array.from(document.querySelectorAll(LISTING_MOBILE_BOOK_BAR_SELECTOR));
  if (!bars.length) {
    return;
  }

  const footer = document.querySelector(LISTING_MOBILE_BOOK_FOOTER_SELECTOR);
  if (!footer || footer.getAttribute('data-listing-book-bar-io') === '1') {
    return;
  }

  footer.setAttribute('data-listing-book-bar-io', '1');

  const viewportHeight = () => window.innerHeight;

  const sync = () => syncListingMobileBookBarWithFooter(bars, footer, viewportHeight());

  let frame = 0;
  const requestSync = () => {
    if (frame) {
      return;
    }
    frame = window.requestAnimationFrame(() => {
      frame = 0;
      sync();
    });
  };

  sync();
  window.addEventListener('scroll', requestSync, { passive: true });
  window.addEventListener('resize', requestSync);
}
