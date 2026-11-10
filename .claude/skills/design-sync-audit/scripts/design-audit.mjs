#!/usr/bin/env node
// Design-sync audit for Catch A Guide.
// Flags SCSS / Blade / lang values that drift from docs/design-system.md
// (tokens in resources/sass/settings/_tokens.scss). Read-only; no dependencies.
//
// Usage (from anywhere inside the repo):
//   node .claude/skills/design-sync-audit/scripts/design-audit.mjs [paths...] [options]
//
// Options:
//   --changed              only files changed vs main (committed + uncommitted)
//   --base=<ref>           base ref for --changed (default: main)
//   --only=a,b             checks: color,font-size,font-family,font-weight,radius,important,
//                          inline-style,heading,i18n,copy
//   --format=md|json       output (default md)
//   --top=<n>              files listed in the ranking (default 25)
//   --per-file=<n>         findings printed per file (default 30, 0 = all)
//   --include-admin        also scan admin / mail / pdf views and admin SCSS

import fs from 'node:fs';
import path from 'node:path';
import { execSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';

process.stdout.on('error', (err) => { if (err.code === 'EPIPE' || err.code === 'EOF') process.exit(0); throw err; });

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../../../..');

// ---------------------------------------------------------------------------
// Design tokens (mirror of _tokens.scss / docs/design-system.md)
// ---------------------------------------------------------------------------

const PALETTE = {
  '#e8604c': '$cag-coral',
  '#e85b40': '$cag-coral-hover',
  '#f2856d': '$cag-coral-tint',
  '#313041': '$cag-slate',
  '#1f1e2a': '$cag-slate-deep',
  '#787780': '$cag-gray',
  '#ece8e0': '$cag-border',
  '#faf5ee': '$cag-sand',
  '#f7f9fb': '$cag-bg',
  '#ffffff': '$cag-white',
  '#ffce00': '$cag-star',
  '#22c55e': '$cag-success',
  '#b91c1c': '$cag-danger',
  '#0c8a7f': '$category-camp',
  '#086b63': '$category-camp-end',
  '#1f6fa8': '$category-trip',
  '#165887': '$category-trip-end',
};
// Explicit remaps from the Nov 2026 census (docs §2) — beats nearest-color guessing.
const REMAP = {
  '#e85d3b': '$cag-coral', '#e5604d': '$cag-coral',
  '#333333': '$cag-slate', '#444444': '$cag-slate', '#334155': '$cag-slate', '#1e293b': '$cag-slate',
  '#374151': '$cag-slate', '#111827': '$cag-slate', '#252238': '$cag-slate', '#222222': '$cag-slate',
  '#343a40': '$cag-slate', '#495057': '$cag-slate', '#475569': '$cag-slate', '#4b5563': '$cag-slate',
  '#64748b': '$cag-gray', '#6b7280': '$cag-gray', '#9ca3af': '$cag-gray', '#6c757d': '$cag-gray',
  '#666666': '$cag-gray', '#94a3b8': '$cag-gray', '#777777': '$cag-gray', '#888888': '$cag-gray',
  '#e5e7eb': '$cag-border', '#e2e8f0': '$cag-border', '#dee2e6': '$cag-border', '#d1d5db': '$cag-border',
  '#ced4da': '$cag-border', '#cbd5e1': '$cag-border', '#e9ecef': '$cag-border', '#eeeeee': '$cag-border',
  '#f9fafb': '$cag-bg', '#f8fafc': '$cag-bg', '#f8f9fa': '$cag-bg', '#f1f5f9': '$cag-bg', '#f5f5f5': '$cag-bg',
  '#f7f7f8': '$cag-bg',
};

const FS_SCALE = [
  [3, 'display (desktop max)'], [2.6, 'CTA band (desktop max)'], [2.35, 'solid h1 (desktop max)'],
  [2, 'display (mobile)'], [1.65, 'CTA band (mobile)'], [1.55, 'solid h1 (mobile)'],
  [1.5, 'h2 section (desktop)'], [1.375, 'h2 section (mobile)'], [1.15, 'h3 block (desktop)'],
  [1.125, 'lead'], [1.05, 'card title / h3 mobile'], [1, 'body (desktop)'], [0.95, 'body (mobile)'],
  [0.9, 'subtitle / button'], [0.85, 'small / meta'], [0.8, 'small button'], [0.75, 'eyebrow (desktop)'],
  [0.72, 'caption'], [0.7, 'badge / eyebrow'],
];
const FS_ALLOWED_CLAMPS = [
  'clamp(2rem,4vw,3rem)', 'clamp(1.5rem,4vw,3rem)', 'clamp(1.65rem,3.2vw,2.6rem)', 'clamp(1.55rem,3vw,2.35rem)',
];
const RADIUS_REM = [0, 0.35, 0.5, 0.75, 1];
const FONT_OK = /dm sans|inherit|initial|unset|var\(--thm-font\)|\$cag-font|font ?awesome|tevily|icomoon|reey|flag-icons|monospace|bootstrap-icons/i;
const WEIGHT_OK = new Set(['400', '500', '700', 'normal', 'bold', 'inherit', 'initial', 'unset']);

const SEVERITY_WEIGHT = { warn: 3, info: 1 };

// ---------------------------------------------------------------------------
// CLI
// ---------------------------------------------------------------------------

const args = process.argv.slice(2);
const opt = (name, def) => {
  const hit = args.find((a) => a === `--${name}` || a.startsWith(`--${name}=`));
  if (!hit) return def;
  return hit.includes('=') ? hit.split('=').slice(1).join('=') : true;
};
const only = opt('only', null) ? new Set(String(opt('only')).split(',').map((s) => s.trim())) : null;
const format = opt('format', 'md');
const topN = Number(opt('top', 25));
const perFile = Number(opt('per-file', 30));
const includeAdmin = Boolean(opt('include-admin', false));
const enabled = (check) => !only || only.has(check);

const EXCLUDE = includeAdmin
  ? [/node_modules/, /vendor\//, /resources\/sass\/settings\//]
  : [
      /node_modules/, /vendor\//, /resources\/sass\/settings\//, /resources\/sass\/admin\//,
      /resources\/views\/(admin|mails?|emails?|pdfs?|vendor|test)\//, /resources\/views\/.*\/(mail|email|pdf)s?\//,
      /\.pdf\.blade\.php$/,
    ];

const rel = (p) => path.relative(ROOT, path.resolve(p)).split(path.sep).join('/');

function walk(target, out) {
  const abs = path.resolve(target);
  if (!fs.existsSync(abs)) return;
  const st = fs.statSync(abs);
  if (st.isDirectory()) {
    for (const entry of fs.readdirSync(abs)) walk(path.join(abs, entry), out);
  } else out.push(abs);
}

function collectFiles() {
  let files = [];
  if (opt('changed', false)) {
    const run = (cmd) => {
      try { return execSync(cmd, { cwd: ROOT, encoding: 'utf8', stdio: ['ignore', 'pipe', 'ignore'] }).split('\n'); } catch { return []; }
    };
    const base = opt('base', run('git rev-parse --verify --quiet origin/main').filter(Boolean).length ? 'origin/main' : 'main');
    const names = new Set([
      ...run(`git diff --name-only ${base}...HEAD`),
      ...run('git diff --name-only'),
      ...run('git diff --name-only --cached'),
      ...run('git ls-files --others --exclude-standard'),
    ]);
    files = [...names]
      .filter((n) => n && n.startsWith('resources/'))
      .map((n) => path.join(ROOT, n))
      .filter((p) => fs.existsSync(p));
  } else {
    const targets = args.filter((a) => !a.startsWith('--'));
    const list = targets.length ? targets : ['resources/sass', 'resources/views', 'resources/lang/en'];
    for (const t of list) walk(path.isAbsolute(t) ? t : path.resolve(targets.length ? process.cwd() : ROOT, t), files);
  }
  return files
    .filter((f) => /\.(scss|blade\.php)$/.test(f) || /resources[\\/]lang[\\/]en[\\/].*\.php$/.test(f))
    .filter((f) => !EXCLUDE.some((re) => re.test(rel(f))));
}

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

const normHex = (h) => {
  let x = h.toLowerCase();
  if (x.length === 4) x = '#' + [...x.slice(1)].map((c) => c + c).join('');
  if (x.length === 9) x = x.slice(0, 7);
  return x;
};
const rgb = (h) => [1, 3, 5].map((i) => parseInt(h.slice(i, i + 2), 16));
const dist = (a, b) => Math.hypot(...rgb(a).map((v, i) => v - rgb(b)[i]));

function nearestColor(hex) {
  let best = null;
  for (const [h, token] of Object.entries(PALETTE)) {
    const d = dist(hex, h);
    if (!best || d < best.d) best = { h, token, d };
  }
  return best;
}

function nearestSize(rem) {
  return FS_SCALE.reduce((a, b) => (Math.abs(b[0] - rem) < Math.abs(a[0] - rem) ? b : a));
}

function stripComments(lines) {
  let inBlock = false;
  return lines.map((line) => {
    let out = '';
    let i = 0;
    while (i < line.length) {
      if (inBlock) {
        const end = line.indexOf('*/', i);
        if (end === -1) { i = line.length; } else { inBlock = false; i = end + 2; }
      } else {
        const start = line.indexOf('/*', i);
        const slash = line.slice(i).search(/(^|[^:])\/\//);
        const slashAt = slash === -1 ? -1 : i + slash + (line[i + slash] === '/' ? 0 : 1);
        if (slashAt !== -1 && (start === -1 || slashAt < start)) { out += line.slice(i, slashAt); i = line.length; }
        else if (start !== -1) { out += line.slice(i, start); inBlock = true; i = start + 2; }
        else { out += line.slice(i); i = line.length; }
      }
    }
    return out;
  });
}

// ---------------------------------------------------------------------------
// Checks
// ---------------------------------------------------------------------------

function checkDeclaration(prop, value, push) {
  const v = value.replace(/!important/g, '').trim();

  if (enabled('color') && !/^(--|\$)/.test(prop)) {
    for (const m of v.matchAll(/#([0-9a-fA-F]{8}|[0-9a-fA-F]{6}|[0-9a-fA-F]{3})\b/g)) {
      const hex = normHex(m[0]);
      if (PALETTE[hex]) continue;
      if (REMAP[hex]) { push('warn', 'color', `\`${m[0]}\` is a known drift color → use \`${REMAP[hex]}\``); continue; }
      const n = nearestColor(hex);
      if (n.d < 40) push('warn', 'color', `\`${m[0]}\` is a near-duplicate of \`${n.token}\` (${n.h}) → use the token`);
      else push('info', 'color', `\`${m[0]}\` is off-palette (nearest \`${n.token}\`) — use a token or add one to the doc`);
    }
  }

  if (enabled('font-size') && prop === 'font-size') {
    const compact = v.replace(/\s+/g, '');
    if (/\$cag-fs|var\(--|^(inherit|initial|unset|smaller|larger|100%)$/.test(compact) || /^[0-9.]+em$/.test(compact)) {
      // ok: token, custom property or relative-to-parent em
    } else if (compact.startsWith('clamp(')) {
      if (!FS_ALLOWED_CLAMPS.includes(compact)) push('info', 'font-size', `custom \`${v}\` — use \`$cag-fs-display\`, \`$cag-fs-display-sm\` or \`$cag-fs-h1-solid\``);
    } else if (compact.startsWith('$')) {
      push('info', 'font-size', `\`${v}\` is a local variable, not a design token → map to a \`$cag-fs-*\` role`);
    } else {
      const m = compact.match(/^([0-9]*\.?[0-9]+)(px|rem)$/);
      if (m) {
        const rem = m[2] === 'px' ? Number(m[1]) / 16 : Number(m[1]);
        const [scale, role] = nearestSize(rem);
        const onScale = Math.abs(scale - rem) < 0.001;
        if (m[2] === 'px') push('warn', 'font-size', `\`${v}\` in px → \`${scale}rem\` (${role})`);
        else if (!onScale) push('warn', 'font-size', `\`${v}\` is off the type scale → \`${scale}rem\` (${role})`);
      } else push('info', 'font-size', `unrecognised font-size \`${v}\``);
    }
  }

  if (enabled('font-family') && prop === 'font-family' && /^var\(--/.test(v) && !FONT_OK.test(v)) {
    push('info', 'font-family', `\`${v}\` — check the custom property resolves to DM Sans (checkout/voucher vars resolve to Inter/Archivo/Nunito)`);
  } else if (enabled('font-family') && prop === 'font-family' && !FONT_OK.test(v)) {
    push('warn', 'font-family', `\`${v}\` — public pages use inherited DM Sans; drop the declaration or use \`inherit\``);
  }

  if (enabled('font-weight') && prop === 'font-weight' && !/^\$cag-fw|var\(--/.test(v) && !WEIGHT_OK.has(v)) {
    const msg = v === '600'
      ? '`600` is not a loaded DM Sans weight and renders as 700 → `$cag-fw-bold` (keep look) or `$cag-fw-medium`'
      : `\`${v}\` is not a loaded DM Sans weight (400/500/700)`;
    push('warn', 'font-weight', msg);
  }

  if (enabled('radius') && /^border(-(top|bottom)-(left|right))?-radius$/.test(prop) && !/\$cag-radius|var\(--|inherit/.test(v)) {
    const parts = v.split(/[\s/]+/).filter(Boolean);
    const bad = parts.filter((p) => {
      if (/^(0|50%|100%|999px|9999px|99px|100px|50rem|999rem)$/.test(p)) return false;
      const m = p.match(/^([0-9]*\.?[0-9]+)(px|rem)$/);
      if (!m) return true;
      const rem = m[2] === 'px' ? Number(m[1]) / 16 : Number(m[1]);
      return !RADIUS_REM.some((r) => Math.abs(r - rem) < 0.001);
    });
    if (bad.length) push('info', 'radius', `\`${v}\` off the radius scale (sm .35 · md .5 · lg .75 · xl 1rem · pill)`);
  }
}

function scanCss(lines, offset, push) {
  const clean = stripComments(lines);
  let important = 0;
  clean.forEach((line, idx) => {
    const lineNo = idx + 1 + offset;
    if (/!important/.test(line)) important++;
    if (/\{\s*$/.test(line) && !/:\s*[^{]*;/.test(line)) return; // selector line
    for (const m of line.matchAll(/(?:^|[;{\s])([a-z-]+)\s*:\s*([^;{}]+?)\s*(?:;|$|\})/g)) {
      const prop = m[1];
      if (/^(hover|focus|active|before|after|not|nth-child|first-child|last-child|root|is|where|has)$/.test(prop)) continue;
      checkDeclaration(prop, m[2], (sev, check, msg) => push(sev, check, msg, lineNo));
    }
  });
  return important;
}

function scanScss(file, text, push) {
  const important = scanCss(text.split(/\r?\n/), 0, push);
  if (enabled('important') && important >= 5) {
    push('info', 'important', `${important}× \`!important\` in this file — raise specificity under the page root instead`, 1);
  }
}

function scanBlade(file, text, push) {
  const lines = text.split(/\r?\n/);

  // <style> blocks get the SCSS checks
  let styleStart = -1;
  lines.forEach((line, i) => {
    if (styleStart === -1 && /<style\b[^>]*>/i.test(line)) styleStart = i;
    if (styleStart !== -1 && /<\/style>/i.test(line)) {
      const block = lines.slice(styleStart, i + 1);
      block[0] = block[0].replace(/.*<style\b[^>]*>/i, '');
      block[block.length - 1] = block[block.length - 1].replace(/<\/style>.*/i, '');
      const imp = scanCss(block, styleStart, push);
      if (enabled('important') && imp >= 5) push('info', 'important', `${imp}× \`!important\` in a <style> block`, styleStart + 1);
      styleStart = -1;
    }
  });

  const headings = [];
  lines.forEach((line, i) => {
    const lineNo = i + 1;

    if (enabled('inline-style')) {
      for (const m of line.matchAll(/style\s*=\s*"([^"]*)"/g)) {
        const css = m[1];
        const props = [...css.matchAll(/(font-size|font-family|font-weight|line-height|letter-spacing|(?<![-\w])color|background(?:-color)?)\s*:\s*([^;]+)/g)]
          .filter((p) => !(p[1].startsWith('background') && !/#[0-9a-f]{3,8}\b|rgb/i.test(p[2])));
        if (props.length) {
          push('warn', 'inline-style', `inline style sets ${[...new Set(props.map((p) => p[1]))].join(', ')} → move to a SCSS class using tokens`, lineNo);
        }
      }
    }

    for (const m of line.matchAll(/<h([1-6])\b([^>]*)>([^<]*)/gi)) {
      headings.push({ level: Number(m[1]), lineNo });
      const textAfter = m[3].trim();
      if (enabled('i18n') && textAfter && /[A-Za-zÄÖÜäöüß]{3,}/.test(textAfter) && !/^(\{\{|\{!!|@|\$)/.test(textAfter)) {
        push('warn', 'i18n', `hardcoded heading text "${textAfter.slice(0, 50)}" → use a lang key in en + de`, lineNo);
      }
    }
  });

  if (enabled('heading') && headings.length) {
    const h1 = headings.filter((h) => h.level === 1);
    if (h1.length > 1) push('warn', 'heading', `${h1.length} <h1> in one view (lines ${h1.map((h) => h.lineNo).join(', ')}) — a page has exactly one`, h1[1].lineNo);
    if (h1.length && /\/partials?\/|\/components?\/|card/.test(rel(file)) && !/hero|header/.test(rel(file))) {
      push('info', 'heading', '<h1> inside a partial/component — make sure the page has no other h1', h1[0].lineNo);
    }
    for (let i = 1; i < headings.length; i++) {
      const prev = headings[i - 1];
      const cur = headings[i];
      if (cur.level > prev.level + 1) {
        push('info', 'heading', `heading jumps h${prev.level} → h${cur.level} (line ${prev.lineNo} → ${cur.lineNo}); don't skip levels`, cur.lineNo);
      }
    }
  }
}

function scanLang(file, text, push) {
  if (!enabled('copy')) return;
  text.split(/\r?\n/).forEach((line, i) => {
    const m = line.match(/^\s*['"]([^'"]+)['"]\s*=>\s*'((?:[^'\\]|\\.)*)'/);
    if (!m) return;
    const [, key, value] = m;
    const lineNo = i + 1;
    if (/[→»]\s*$/.test(value)) push('info', 'copy', `"${value}" — arrows come from CSS ::after, not the string (${key})`, lineNo);
    if (/^(view|show|see) all\b/i.test(value) && value.toLowerCase() !== 'see all') {
      push('info', 'copy', `"${value}" — section links use "Discover all …", short rail links "See all" (${key})`, lineNo);
    }
    const words = value.split(/\s+/).filter(Boolean);
    if (words.length >= 2 && words.length <= 6 && !/[.:!?]$/.test(value) && !/[:<{,]/.test(value)) {
      const longWords = words.slice(1).filter((w) => w.length > 3 && /^[A-Za-z]/.test(w));
      if (longWords.length >= 2 && longWords.every((w) => /^[A-Z][a-z]/.test(w))) {
        push('info', 'copy', `"${value}" is Title Case → sentence case (${key})`, lineNo);
      }
    }
  });
}

// ---------------------------------------------------------------------------
// Run
// ---------------------------------------------------------------------------

const files = collectFiles();
const results = [];
for (const file of files) {
  const text = fs.readFileSync(file, 'utf8');
  const findings = [];
  const push = (severity, check, message, line = 1) => findings.push({ severity, check, message, line });
  const r = rel(file);
  if (r.endsWith('.scss')) scanScss(file, text, push);
  else if (r.endsWith('.blade.php')) scanBlade(file, text, push);
  else scanLang(file, text, push);
  if (findings.length) {
    findings.sort((a, b) => a.line - b.line);
    const score = findings.reduce((s, f) => s + SEVERITY_WEIGHT[f.severity], 0);
    results.push({ file: r, score, findings });
  }
}
results.sort((a, b) => b.score - a.score);

const totals = {};
for (const r of results) for (const f of r.findings) {
  totals[f.check] ??= { warn: 0, info: 0, files: new Set() };
  totals[f.check][f.severity]++;
  totals[f.check].files.add(r.file);
}

if (format === 'json') {
  const out = {
    scanned: files.length,
    totals: Object.fromEntries(Object.entries(totals).map(([k, v]) => [k, { warn: v.warn, info: v.info, files: v.files.size }])),
    results,
  };
  process.stdout.write(JSON.stringify(out, null, 2) + '\n');
  process.exit(0);
}

const o = [];
o.push('# Design sync audit', '');
o.push(`Scanned ${files.length} files against \`docs/design-system.md\`. ${results.length} files have findings.`, '');
if (!results.length) {
  o.push('No drift found. ✅');
  process.stdout.write(o.join('\n') + '\n');
  process.exit(0);
}
o.push('## Summary', '', '| Check | Warn | Info | Files |', '|---|---:|---:|---:|');
for (const [check, v] of Object.entries(totals).sort((a, b) => b[1].warn - a[1].warn)) {
  o.push(`| ${check} | ${v.warn} | ${v.info} | ${v.files.size} |`);
}
o.push('', `## Most out-of-sync files (top ${Math.min(topN, results.length)})`, '', '| File | Score | Warn | Info | Main issues |', '|---|---:|---:|---:|---|');
for (const r of results.slice(0, topN)) {
  const by = {};
  r.findings.forEach((f) => { by[f.check] = (by[f.check] || 0) + 1; });
  const main = Object.entries(by).sort((a, b) => b[1] - a[1]).slice(0, 3).map(([k, n]) => `${k} ${n}`).join(', ');
  o.push(`| \`${r.file}\` | ${r.score} | ${r.findings.filter((f) => f.severity === 'warn').length} | ${r.findings.filter((f) => f.severity === 'info').length} | ${main} |`);
}
o.push('', '## Findings', '');
for (const r of results.slice(0, topN)) {
  o.push(`### \`${r.file}\``);
  const list = perFile > 0 ? r.findings.slice(0, perFile) : r.findings;
  for (const f of list) o.push(`- \`${r.file}:${f.line}\` **${f.severity}** [${f.check}] ${f.message}`);
  if (list.length < r.findings.length) o.push(`- … ${r.findings.length - list.length} more (use \`--per-file=0\`)`);
  o.push('');
}
if (results.length > topN) o.push(`_${results.length - topN} more files with findings — pass \`--top=${results.length}\` or narrow the paths._`);
process.stdout.write(o.join('\n') + '\n');
