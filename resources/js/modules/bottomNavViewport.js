const HIDE_NAV_MQ = '(min-width: 768px)';

/**
 * Pin the mobile tab bar to the visual viewport bottom.
 * iOS Safari keeps position:fixed; bottom:0 at a stale Y when its toolbar
 * collapses, so listing cards show through the gap. CSS overflow fills are
 * also clipped on some iOS versions, so this uses visualViewport coordinates.
 */
export function visualViewportNavTop(offsetTop, visualHeight, navHeight) {
  return Math.round(offsetTop + visualHeight - navHeight);
}

export function syncBottomNavToVisualViewport(nav, viewport, hideNav) {
  if (!nav) {
    return;
  }

  if (hideNav) {
    nav.classList.remove('is-vv-pinned');
    nav.style.removeProperty('--cag-bottom-nav-top');
    return;
  }

  const navHeight = nav.offsetHeight;
  if (!viewport || navHeight <= 0) {
    return;
  }

  nav.style.setProperty(
    '--cag-bottom-nav-top',
    `${visualViewportNavTop(viewport.offsetTop, viewport.height, navHeight)}px`
  );
  nav.classList.add('is-vv-pinned');
}

export function initBottomNavViewport() {
  const nav = document.querySelector('.cag-home-bottom-nav');
  if (!nav || !window.visualViewport || nav.getAttribute('data-cag-vv') === '1') {
    return;
  }

  nav.setAttribute('data-cag-vv', '1');

  const hideNavMq = window.matchMedia(HIDE_NAV_MQ);

  const sync = () => {
    syncBottomNavToVisualViewport(nav, window.visualViewport, hideNavMq.matches);
  };

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
  window.visualViewport.addEventListener('resize', requestSync);
  window.visualViewport.addEventListener('scroll', requestSync);
  window.addEventListener('scroll', requestSync, { passive: true });
  window.addEventListener('orientationchange', requestSync);
  if (typeof hideNavMq.addEventListener === 'function') {
    hideNavMq.addEventListener('change', sync);
  } else if (typeof hideNavMq.addListener === 'function') {
    hideNavMq.addListener(sync);
  }
}
