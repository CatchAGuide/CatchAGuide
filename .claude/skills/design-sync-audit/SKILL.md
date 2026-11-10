---
name: design-sync-audit
description: Audit Catch A Guide pages, components, SCSS, Blade views or lang copy for drift from the homepage-based design system (docs/design-system.md). It covers font sizes off the type scale, non-DM-Sans fonts, unloaded weights (600), off-palette or near-duplicate colors, h1/h2/h3 outline and size problems, alignment, radii, inline styles, hardcoded heading text and inconsistent UI wording. Use it when the user asks to check, audit, compare or "sync" the look of a page or component, asks why something looks different from the homepage, before or after a UI change, or for a site-wide design-consistency report.
---

# Design sync audit

Finds the components and pages that drift from the design system and says how to bring each one back in line. The
spec is `docs/design-system.md`, the tokens are `resources/sass/settings/_tokens.scss`, and the homepage is the
reference.

**Read `docs/design-system.md` first.** The script encodes it, but your judgment on "is this intentional?" needs the
spec's context (allowed exceptions, open decisions, out-of-scope areas).

## Pick a mode

| User asks about… | Mode |
|---|---|
| "my changes" / a PR / before committing | **A. Static, changed files**: `--changed` |
| A file, component or page area ("the tour card", "checkout styles") | **A. Static, scoped paths** |
| Whole site / "where are we worst" | **A. Static, full**, then **B** on the top 3–5 pages |
| How a page actually renders, visible mismatch, mobile vs desktop | **B. Live probe** (computed styles) |

Static is cheap (≈3s, no browser), so always run it first. Live only shows what the cascade really produces
(`tevily.css` + Bootstrap + `app.css` + inline styles), so use it to confirm before you claim a page "looks wrong".

## A. Static scan

Run from the repo root (Bash tool, or PowerShell with the same command):

```bash
node .claude/skills/design-sync-audit/scripts/design-audit.mjs --changed                 # vs origin/main + uncommitted
node .claude/skills/design-sync-audit/scripts/design-audit.mjs resources/sass/components/_guiding-card.scss resources/views/components/guiding-card.blade.php
node .claude/skills/design-sync-audit/scripts/design-audit.mjs --top=20                  # whole public site
node .claude/skills/design-sync-audit/scripts/design-audit.mjs --only=heading,i18n resources/views/pages
node .claude/skills/design-sync-audit/scripts/design-audit.mjs --format=json > "$SCRATCH/audit.json"   # for diffing / counts
```

Checks: `color`, `font-size`, `font-family`, `font-weight`, `radius`, `important`, `inline-style`, `heading`, `i18n`
(hardcoded heading text), `copy` (EN lang strings: Title Case, "View/Show all", baked-in arrows). `<style>` blocks
inside Blade get the SCSS checks too. By default it skips `settings/` (token definitions), admin, mails, PDFs and
`views/test/`. Pass `--include-admin` only if asked.

Severity: **warn** = clear drift with a concrete replacement. **info** = needs judgment (off-palette color with no
close token, custom clamp, odd radius, Title Case that may be a proper noun).

To find the component behind a page, follow `routes/web/*.php` → controller → `view('…')` → Blade partials → their
class prefix → `grep -rn "\.prefix" resources/sass`. Common ones: homepage `pages/home/*` + `page/home.scss` +
`_home-mobile.scss`; tour PDP `page/guiding.scss` + `components/_tour-details.scss`; listing cards
`components/_guiding-card.scss`; vacations hub `page/_vacations-two-pillar.scss`; trip PDP `page/_trips-offer.scss`;
checkouts `page/_tour-checkout.scss`, `_camp-checkout.scss`, `_trip-checkout.scss`, `modern-checkout.scss`; voucher
`page/_voucher-landing.scss`; magazine `page/magazine.scss`, `blog-single.scss`.

## B. Live probe (computed styles)

1. Load the `claude-in-chrome` skill and its tools in one ToolSearch call (`tabs_context_mcp`, `tabs_create_mcp`,
   `navigate`, `javascript_tool`, `resize_window`, `computer`). Open a **new** tab.
2. Prefer the local site, `http://cag.local` (Laragon). Run `npm run dev` first if SCSS changed. Use production
   (`https://catchaguide.com` / `.de`) only for a read-only comparison. The probe never changes anything.
3. **Baseline first:** probe `/` so differences are measured against the reference, not against the abstract scale.
4. For each page, probe at **desktop (1440×900)** and **mobile (390×844)** via `resize_window`, then reload:
   - Read `scripts/computed-style-probe.js` and pass its full contents to `javascript_tool`. It returns JSON: the
     heading outline, distinct font sizes in use, and issues (`font-family`, `font-weight`, `font-size`,
     `heading-size`, `color`, `alignment`, `button`, `layout` overflow, `inline-style`).
   - Take one screenshot of the worst-looking area only if a visual comparison helps the user.
5. Useful pages: `/`, `/guidings`, `/guidings/alloffers`, a tour PDP (follow the first card), `/guidings/countries`,
   `/guidings/targets`, `/vacations`, `/vacations/camps`, a camp PDP, a trip PDP, `/offers`, `/destination`,
   `/targets`, `/guidings/voucher`, a magazine article, and a checkout step if reachable without booking.
6. Map each live issue back to source: get the element's class from the probe's `el` and grep it in `resources/sass`
   and `resources/views`, so the report points to `file:line`, not just a selector.

## Report format

Lead with the answer: which pages/components are most out of sync and what kind of drift dominates. Then:

```
## <Page or component>  — <one-line verdict>
| Issue | Where (file:line) | Now | Should be (token / rule) |
|---|---|---|---|
| Section h2 too small | resources/sass/page/_guidings-landing.scss:212 | 1.35rem | cag-text-h2 (1.375 → 1.5rem) |
| Unloaded weight | … | 600 (renders 700) | $cag-fw-bold |
```

- Group repeated findings ("41 Tailwind grays → `$cag-gray`/`$cag-slate`") instead of listing every line. The
  script output has the detail if the user wants it.
- Rank by visibility: page `h1`/`h2`, buttons and cards on high-traffic pages first, then body text, then radii/info.
- Call out anything the spec marks as an **open decision** (German *du/Sie*, Tailwind blues) as a question. It is not
  a defect.
- For a site-wide run, publish the report as an Artifact if it's for the team. Otherwise answer in the terminal.

## Fixing (only when asked)

- Change the outlier, not the scale. Replace literals with `$cag-*` tokens or `cag-*` mixins from `_tokens.scss`.
  Keep the visual result where the spec says so (e.g. `600 → $cag-fw-bold`, which is what already renders).
- Edit SCSS under `resources/sass/` only, never `public/css`. Keep fixes inside the component being touched; no
  site-wide find-and-replace of colors without the user's go-ahead.
- Heading tag changes (`h4 → h3`, a second `h1 → h2`) can affect SEO and GTM triggers. Check CLAUDE.md's analytics
  selector list before renaming classes or IDs, and restyle via class so the visual size doesn't jump.
- New copy needs `en` + `de` keys. Hardcoded heading text moves into the matching lang file.
- After fixing: re-run the static scan on the touched files (findings should drop, none added), run `npm run dev`,
  and re-probe the page at both widths. Report before/after counts.
- If the spec itself is wrong or missing a role, update `docs/design-system.md` **and** `_tokens.scss` **and** the
  constants at the top of `scripts/design-audit.mjs` / `computed-style-probe.js` in the same change.
