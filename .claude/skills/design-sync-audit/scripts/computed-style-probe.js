// Live design-sync probe. Paste the whole file into Claude in Chrome's javascript_tool
// (or DevTools console) on a rendered page; it returns a JSON report string.
// It checks what the static scan can't: computed sizes/fonts/colors after the cascade
// (tevily.css + Bootstrap + app.css), the real heading outline, alignment and overflow.
(() => {
  const REM = 16;
  const SCALE_REM = [3, 2.6, 2.35, 2, 1.65, 1.55, 1.5, 1.375, 1.15, 1.125, 1.05, 1, 0.95, 0.9, 0.85, 0.8, 0.75, 0.72, 0.7];
  const PALETTE = {
    'rgb(232, 96, 76)': 'coral', 'rgb(232, 91, 64)': 'coral-hover', 'rgb(49, 48, 65)': 'slate',
    'rgb(31, 30, 42)': 'slate-deep', 'rgb(120, 119, 128)': 'gray', 'rgb(255, 255, 255)': 'white',
    'rgb(12, 138, 127)': 'camp', 'rgb(31, 111, 168)': 'trip', 'rgb(255, 206, 0)': 'star',
    'rgb(34, 197, 94)': 'success', 'rgb(185, 28, 28)': 'danger',
  };
  const WEIGHTS = new Set(['400', '500', '700']);
  const isMobile = window.innerWidth < 768;
  const visible = (el) => {
    const r = el.getBoundingClientRect();
    const cs = getComputedStyle(el);
    return r.width > 0 && r.height > 0 && cs.visibility !== 'hidden' && cs.display !== 'none';
  };
  const label = (el) => {
    const cls = [...el.classList].slice(0, 2).join('.');
    return el.tagName.toLowerCase() + (el.id ? '#' + el.id : '') + (cls ? '.' + cls : '');
  };
  const opaque = (c) => c.replace(/rgba\((\d+), (\d+), (\d+), 1\)/, 'rgb($1, $2, $3)');
  const onScale = (px) => SCALE_REM.some((r) => Math.abs(r * REM - px) < 0.6);
  // Fluid clamp() titles legitimately land between steps — only check them at the ends.
  const fluid = (el) => /hero__title|page-header__title|partner__title/.test(el.className);

  const issues = [];
  const add = (severity, check, el, msg) => issues.push({ severity, check, el: label(el), text: (el.textContent || '').trim().slice(0, 60), msg });

  // 1. Heading outline
  const headings = [...document.querySelectorAll('h1, h2, h3, h4, h5, h6')].filter(visible);
  const outline = headings.map((h) => ({ level: +h.tagName[1], el: label(h), text: h.textContent.trim().replace(/\s+/g, ' ').slice(0, 70) }));
  const h1s = headings.filter((h) => h.tagName === 'H1');
  if (h1s.length !== 1) issues.push({ severity: 'warn', check: 'heading', el: 'document', text: '', msg: `${h1s.length} visible <h1> (expected exactly 1)` });
  for (let i = 1; i < headings.length; i++) {
    const a = +headings[i - 1].tagName[1];
    const b = +headings[i].tagName[1];
    if (b > a + 1) add('info', 'heading', headings[i], `skips h${a} → h${b}`);
  }

  // 2. Typography + color of text elements
  const textEls = [...document.querySelectorAll('h1,h2,h3,h4,h5,h6,p,a,button,label,li,span,small,strong')]
    .filter((el) => visible(el) && [...el.childNodes].some((n) => n.nodeType === 3 && n.textContent.trim().length > 1))
    .filter((el) => !el.closest('footer, .leaflet-container, .gm-style, iframe, [aria-hidden="true"], .litepicker, .select2-container'));
  const sizeUse = {};
  for (const el of textEls) {
    const cs = getComputedStyle(el);
    const px = parseFloat(cs.fontSize);
    const fam = cs.fontFamily.split(',')[0].replace(/["']/g, '').trim();
    sizeUse[px] = (sizeUse[px] || 0) + 1;
    if (!/DM Sans/i.test(fam) && !/awesome|icon|tevily|flag/i.test(fam)) add('warn', 'font-family', el, `renders in "${fam}"`);
    if (!WEIGHTS.has(cs.fontWeight)) add('warn', 'font-weight', el, `weight ${cs.fontWeight} (DM Sans loads 400/500/700)`);
    if (!onScale(px) && !fluid(el)) add('info', 'font-size', el, `${px}px (${(px / REM).toFixed(3)}rem) is off the type scale`);
    const color = opaque(cs.color);
    if (/^H[1-3]$/.test(el.tagName) && !PALETTE[color]) add('warn', 'color', el, `heading color ${color} is not slate/white/coral`);
    if (/^H[1-3]$/.test(el.tagName) && cs.textAlign === 'center' && !el.closest('[class*="partner"], [class*="cta"], [class*="testimonial"], [class*="review"], [class*="empty"], [class*="thank"], .modal, [role="dialog"]')) {
      add('info', 'alignment', el, 'centered heading outside a CTA band / testimonial / empty state');
    }
    if (cs.textAlign === 'justify') add('warn', 'alignment', el, 'justified text');
  }

  // 3. Role checks on the key heading levels
  const expect = isMobile
    ? { H2: [1.375, 1.65], H3: [1.05] }
    : { H2: [1.5, 2.6], H3: [1.15, 1.05] };
  for (const h of headings) {
    const want = expect[h.tagName];
    if (!want) continue;
    const rem = parseFloat(getComputedStyle(h).fontSize) / REM;
    if (!fluid(h) && !want.some((w) => Math.abs(w - rem) < 0.02)) add('warn', 'heading-size', h, `${h.tagName} at ${rem.toFixed(3)}rem — expected ${want.join(' or ')}rem at this width`);
  }

  // 4. Buttons / CTAs
  for (const b of [...document.querySelectorAll('a[class*="btn"], button[class*="btn"]')].filter(visible)) {
    const cs = getComputedStyle(b);
    const bg = opaque(cs.backgroundColor);
    const [r, g, bl] = (bg.match(/\d+/g) || []).map(Number);
    const coralish = r > 200 && g > 60 && g < 130 && bl < 110;
    if (coralish && !PALETTE[bg]) {
      add('warn', 'button', b, `coral-ish background ${bg} is not $cag-coral`);
    }
    const rad = parseFloat(cs.borderTopLeftRadius);
    if (rad && ![5.6, 8, 12, 16].some((r) => Math.abs(r - rad) < 0.6) && rad < 100) add('info', 'button', b, `radius ${rad}px off scale`);
  }

  // 5. Horizontal overflow (mobile layout breakage)
  const overflow = document.documentElement.scrollWidth - window.innerWidth;
  if (overflow > 1) issues.push({ severity: 'warn', check: 'layout', el: 'document', text: '', msg: `page scrolls horizontally by ${overflow}px` });

  // 6. Inline styles that set type/color at runtime
  const inline = [...document.querySelectorAll('[style*="font-size"], [style*="font-family"], [style*="color:"]')].filter(visible);
  if (inline.length) issues.push({ severity: 'info', check: 'inline-style', el: inline.slice(0, 5).map(label).join(', '), text: '', msg: `${inline.length} visible elements with inline type/color styles` });

  const dedup = {};
  for (const i of issues) {
    const k = i.check + '|' + i.el + '|' + i.msg;
    dedup[k] = dedup[k] ? { ...dedup[k], count: dedup[k].count + 1 } : { ...i, count: 1 };
  }
  return JSON.stringify({
    url: location.href,
    viewport: `${window.innerWidth}x${window.innerHeight}`,
    distinctFontSizes: Object.keys(sizeUse).length,
    fontSizesPx: Object.fromEntries(Object.entries(sizeUse).sort((a, b) => b[0] - a[0])),
    outline,
    issues: Object.values(dedup).sort((a, b) => (a.severity === b.severity ? b.count - a.count : a.severity === 'warn' ? -1 : 1)).slice(0, 80),
  }, null, 1);
})();
