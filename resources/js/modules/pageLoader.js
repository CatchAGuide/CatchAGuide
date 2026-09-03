const OVERLAY_ID = "page-loading-overlay";
const WATCHDOG_MS = 8000;

let watchdogTimer = null;

function getOverlay() {
  return document.getElementById(OVERLAY_ID);
}

function show() {
  const overlay = getOverlay();
  if (!overlay) {
    return;
  }

  overlay.hidden = false;
  document.body.style.overflow = "hidden";

  clearTimeout(watchdogTimer);
  watchdogTimer = setTimeout(hide, WATCHDOG_MS);
}

function hide() {
  const overlay = getOverlay();
  if (!overlay) {
    return;
  }

  clearTimeout(watchdogTimer);
  overlay.hidden = true;
  document.body.style.overflow = "";
}

function isModifiedClick(event) {
  return event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey;
}

function isSameOrigin(url) {
  try {
    return new URL(url, window.location.href).origin === window.location.origin;
  } catch (e) {
    return false;
  }
}

function shouldSkipLink(link, href) {
  if (!href || href.startsWith("#") || href.startsWith("mailto:") || href.startsWith("tel:") || href.startsWith("javascript:")) {
    return true;
  }

  if (link.hasAttribute("download") || link.closest("[data-no-loader]")) {
    return true;
  }

  const target = link.getAttribute("target");
  if (target && target !== "_self") {
    return true;
  }

  return !isSameOrigin(href);
}

function onDocumentClick(event) {
  if (isModifiedClick(event)) {
    return;
  }

  const link = event.target.closest("a[href]");
  if (!link) {
    return;
  }

  const href = link.getAttribute("href");
  if (shouldSkipLink(link, href)) {
    return;
  }

  show();
}

function onDocumentSubmit(event) {
  if (event.defaultPrevented) {
    return;
  }

  const form = event.target;
  if (!(form instanceof HTMLFormElement) || form.closest("[data-no-loader]")) {
    return;
  }

  show();
}

function onPageShow() {
  hide();
}

export function initPageLoader() {
  if (!getOverlay()) {
    return;
  }

  document.addEventListener("click", onDocumentClick);
  document.addEventListener("submit", onDocumentSubmit);
  window.addEventListener("beforeunload", show);
  window.addEventListener("pageshow", onPageShow);

  window.PageLoader = { show, hide };
}
