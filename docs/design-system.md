# Catch A Guide — UI design system

The rules every public page follows for type, color, spacing, headings, alignment and UI wording.
**The homepage is the reference design** (`resources/views/pages/home/landing.blade.php`,
`resources/sass/page/home.scss`, `resources/sass/page/_home-mobile.scss`). When this doc and the homepage
disagree, fix the doc or flag it, don't fork a third style.

- **Tokens (code):** `resources/sass/settings/_tokens.scss`. Variables and mixins only. It emits no CSS, so
  importing it changes nothing until a page uses it.
- **Audit tool:** `node .claude/skills/design-sync-audit/scripts/design-audit.mjs [paths]`. It reports off-token
  colors, sizes, fonts, weights and radii, inline styles, and heading-outline problems. Claude Code runs it through
  the `design-sync-audit` skill. In Cursor, run it in the terminal.
- **Scope:** the public site (`layouts/app`, `app-v2`, `app-v2-1`). Admin (`resources/sass/admin/`), emails
  (`resources/views/mails/`) and PDFs are out of scope. Don't "fix" them toward this doc.

---

## 1. Principles

1. **Use a token, not a literal.** New or changed SCSS uses `$cag-*` variables or `cag-*` mixins. A hex value,
   `px` font-size or one-off radius in page SCSS is drift.
2. **Pick the heading tag for structure and the class for size.** Never choose `<h4>` because it "looks smaller".
3. **Change the outlier, not the scale.** If a page's title is 1.35rem and the scale says 1.5rem, change the page.
   Add a token only when a role really is new, and document it here in the same change.
4. **Migrate as you touch.** No big-bang rewrite. When you edit a component, bring that component onto tokens and
   leave everything else alone.
5. **Mobile-first values with one desktop step at 768px.** That breakpoint is the one the homepage uses
   (`min-width: 768px` / `max-width: 767.98px`).

---

## 2. Color

| Token | Value | Use for | Never for |
|---|---|---|---|
| `$cag-coral` | `#E8604C` | Filled CTAs, text links, active tab/nav, accent squares, focus | Body text, large backgrounds |
| `$cag-coral-hover` | `#E85B40` | Hover color of **text links** only | Button hover (buttons hover to slate) |
| `$cag-coral-tint` | `#F2856D` | Decorative coral on dark bands | Text on white (fails contrast) |
| `$cag-slate` | `#313041` | All headings, strong text, dark bands, filled-button hover | — |
| `$cag-slate-deep` | `#1F1E2A` | Gradient end, darkest band | — |
| `$cag-gray` | `#787780` | Body copy, subtitles, meta, secondary text | Headings |
| `$cag-border` | `#ECE8E0` | Card borders, dividers, input borders | — |
| `$cag-sand` | `#FAF5EE` | Warm surfaces (callouts, highlighted cards) | Page canvas |
| `$cag-bg` | `#F7F9FB` | Page canvas behind homepage-style sections | Card fill (cards are white) |
| `$cag-white` | `#FFFFFF` | Cards, header, listing-catalog canvas | — |
| `$cag-star` | `#FFCE00` | Rating stars | Anything else |
| `$cag-success` / `$cag-danger` | `#22C55E` / `#B91C1C` | Form validation, status | Decoration |
| `$category-tour/camp/trip` | `#313041` / `#0C8A7F` / `#1F6FA8` | Product-type badges, card accent, map pins, filters (`_category-colors.scss`) | CTAs. Coral stays the action color |

On dark backgrounds (hero, dark bands) use `$cag-on-dark` (white) for headings, `$cag-on-dark-muted` (82%) for
body and `$cag-on-dark-subtle` (60%) for eyebrows.

**Known drift to fold in** (from the Nov 2026 census of `resources/sass`):

| Found | Count | Replace with |
|---|---|---|
| `#E85B40` used as the main primary (`$primary-color`, `.btn-orange`) | 60 | `$cag-coral` (keep `#E85B40` for link hover only) |
| `#E85D3B`, `#E5604D` | 31 | `$cag-coral` |
| `#333`, `#444`, `#334155`, `#1E293B`, `#374151`, `#111827`, `#252238` | ~150 | `$cag-slate` for text |
| `#64748B`, `#6B7280`, `#9CA3AF`, `#6C757D`, `#666` | ~180 | `$cag-gray` |
| `#E5E7EB`, `#E2E8F0`, `#DEE2E6`, `#D1D5DB`, `#CED4DA` | ~110 | `$cag-border` |
| `#F9FAFB`, `#F8FAFC`, `#F8F9FA`, `#F1F5F9` | ~100 | `$cag-bg`, or `#fff` on listing catalogs |
| `#3B82F6`, `#667EEA`, `#0EA5E9` (Tailwind/Bootstrap blues) | ~55 | Ask. Usually `$category-trip`, or should be coral |

---

## 3. Typography

### Family and weights

- **One family: DM Sans** (`$cag-font`). Body inherits it from `tevily.css` (`--thm-font`). Don't set
  `font-family` in page SCSS at all unless you are resetting a vendor widget to `inherit`.
- **Loaded weights: 400, 500, 700.** `font-weight: 600` is **not loaded**, so the browser renders it as 700. Use
  `$cag-fw-bold` (700) to keep today's look, or `$cag-fw-medium` (500) if you want lighter emphasis. 300, 800 and
  900 are also not loaded.
- **Not allowed on public pages:** Inter / Archivo (tour + camp checkout: `--tc-font-*`, `--cc-font-*`), Nunito
  (voucher landing via `$font-family-sans-serif`), Arial, Segoe UI, Raleway, Mulish, IBM Plex. These are drift.
  Move them to `$cag-font` when you touch those pages. *Morrison* in emails is out of scope.

### Type scale

Sizes are rem against the 16px root. (Body is 18px from `tevily.css`, so `em` units compound unpredictably. Use rem.)

| Role | Tag | Mobile (<768) | Desktop (≥768) | Weight | Line-height | Color | Token / mixin |
|---|---|---|---|---|---|---|---|
| Hero title | `h1` | 2rem | `clamp(…, 4vw, 3rem)` | 700 | 1.15 | white | `$cag-fs-display`, `cag-text-display` |
| Page title, solid header | `h1` | 1.55rem | `clamp(1.55rem, 3vw, 2.35rem)` | 700 | 1.15 | slate | `$cag-fs-h1-solid` |
| CTA band title | `h2` | 1.65rem | `clamp(…, 3.2vw, 2.6rem)` | 700 | 1.2 | slate | `$cag-fs-display-sm` |
| **Section title** | `h2` | 1.375rem | 1.5rem | 700 | 1.25 | slate | `cag-text-h2` |
| Section subtitle | `p` | 0.9rem | 0.9rem | 400 | 1.5 | gray | `cag-text-subtitle` |
| Block title | `h3` | 1.05rem | 1.15rem | 700 | 1.25 | slate | `cag-text-h3` |
| Card title | `h3` | 1.05rem | 1.05rem | 700 | 1.25, clamp 2 lines | slate | `cag-text-card-title` |
| Lead / hero sub | `p` | 0.95rem | 1.125rem | 400 | 1.55 | gray / on-dark-muted | `$cag-fs-lead` |
| Body | `p` | 0.95rem | 1rem | 400 | 1.55 | gray | `cag-text-body` |
| Small / meta | `p`, `span` | 0.85rem | 0.85rem | 400 | 1.4 | gray | `$cag-fs-small` |
| Button label | — | 0.9rem | 0.9rem | 700 | 1 | — | `cag-button` |
| Button label, small | — | 0.8rem | 0.8rem | 700 | 1 | — | `$cag-fs-button-sm` |
| Caption / card meta | `p` | 0.72rem, UPPERCASE, +0.02em | same | 700 | 1.4 | gray | `cag-text-caption` |
| Hero eyebrow | `p` | 0.7rem, UPPERCASE, +0.16em | 0.75rem | 700 | 1.4 | on-dark-subtle | `$cag-ls-eyebrow` |
| Badge on media | `span` | 0.7rem, +0.02em | same | 700 | 1 | white on category | `$cag-fs-micro` |

**Anything not in this table is drift.** The census found ~180 distinct `font-size` values, 614 of them in `px`,
plus 922 inline `font-size` styles in Blade. Usual mappings: `12–13px → 0.8rem/0.85rem`, `14–15px → 0.9rem`,
`16px → 1rem`, `18px → 1.125rem`, `20px → 1.25rem` (pick the nearest role, not the nearest number).

**Section titles that currently disagree with the 1.5rem `h2`:** `gl-seo__title`/`gl-faq__title` 1.35rem,
`gl-steps__title` 1.55rem, vacations hub `clamp(1.25rem, 2.5vw, 1.6rem)`, magazine
`clamp(1.35rem, 2.4vw, 1.85rem)`, destination pages' `.cag-home-section__title` override 1.125rem,
and the homepage's own `cag-home-season__title` at 1.75rem. Normalise each one when you next touch that page.

---

## 4. Headings and page outline

- **Exactly one `<h1>` per page.** It is the hero/page title. Logos, nav and cards are never `h1`.
- **`h2` per section, `h3` per card or block inside a section.** Don't skip levels (`h2 → h4`).
- Cards in a rail/grid use `h3`. Card meta (location, duration) is `<p>`, not a heading.
- Footer column labels, nav groups, filter labels, modal titles: use `<p>`/`<span>` with a class, or `h2` inside a
  `<dialog>`/`role="dialog"`. They must not compete with the page outline.
- Heading copy always comes from a lang key (`__('file.key')`). Never hardcode it.
- Don't put `!important` on heading colors. The homepage has it only to beat `tevily.css` `h1–h6 { color }`.
  Prefer raising specificity under the page root class (`.cag-home .x__title`).

---

## 5. Layout, spacing and alignment

### Container and section rhythm

- Content width comes from `@include cag-page-container` (max 1440px, gutter `clamp(1.25rem, 3vw, 3rem)`). On
  mobile the homepage tightens it to `$cag-gutter-mobile` (16px). Bootstrap `.container` is already remapped to it
  in `_helpers.scss`.
- Sections use `@include cag-section`: 2rem vertical padding on mobile, 3.5rem on desktop. Never stack two padded
  sections with an extra margin between them.
- Section header → content gap: `$cag-section-header-gap` (1.25rem; 14px on mobile).
- Spacing steps: `$cag-space-1…14` (0.25 → 3.5rem). Grid gaps between cards are 0.85–1rem.

### Section header pattern (from the homepage)

```
Desktop:  [ H2 title                        ]            [ Discover all X → ]   ← link right, bottom-aligned
          [ subtitle (gray, one line)        ]
Mobile:   ■──────────────────────────────   ← .cag-title-rule (coral square + hairline), mobile only
          H2 title
          subtitle
          Discover all X →                  ← .cag-home-section__link--mobile, under the heading
```

- Markup to copy: `resources/views/pages/home/partials/target-species.blade.php` (`.cag-home-section__header`).
- On desktop, title and subtitle stay on one line (`@include cag-desktop-nowrap`). Write copy that fits.

### Text alignment

| Context | Alignment |
|---|---|
| Hero copy (h1, sub, eyebrow, search) | **Left** at every size |
| Section headers, body copy, cards, lists | **Left** |
| Full-width CTA bands (e.g. become-a-guide), testimonial quote cards, empty states, single-action confirmations (thank-you) | **Center** |
| Prices and numeric columns | Right, inside their own cell |
| Never | `text-align: justify`; centered multi-paragraph body text |

---

## 6. Components

| Component | Spec | Reference |
|---|---|---|
| **Primary button** | coral fill, white 0.9rem/700, padding `0.75rem 1.25rem`, radius `$cag-radius-md` (0.5rem), hover to slate | `.cag-home-btn--coral`, `@include cag-button(coral)` |
| Small button | same, padding `0.5rem 0.9rem`, 0.8rem | `.cag-home-btn--sm` |
| Ghost button (on dark) | transparent, white text, 35% white border, hover full white border | `.cag-home-btn--ghost`, `cag-button(ghost-dark)` |
| Outline button (on light) | white fill, slate text, border color, hover slate border | `cag-button(outline)` |
| Icon button | 40×40, pill radius, white, border color, hover `#f2f4f6` | `.cag-home-icon-btn` |
| Text link / "see all" | coral 700, no underline, hover `$cag-coral-hover` + underline | `.cag-home-section__link`, `cag-section-link` |
| **Listing card** | white, 1px border, radius `$cag-radius-xl` (1rem), media `aspect-ratio: 16/10`, body padding `1rem 1rem 1.15rem`, gap 0.4rem; hover lifts 2px with a category-tinted shadow | `.cag-home-offer`, `offer-card.blade.php` |
| Type badge (on media) | top-left `0.75rem`, 0.7rem/700, radius `$cag-radius-sm`, category gradient fill, badge shadow | `.cag-home-offer__badge--{tour,camp,trip}` |
| Rating chip (on media) | dark 72% fill, white, pill radius | `.cag-home-offer__rating-badge` |
| Pill / chip | pill radius (999px), 0.8–0.85rem | `layout/_pill.scss` |
| Search box | white, radius 14px (mobile) / `$cag-radius-lg`, `0 6px 24px rgba(15,18,26,.28)` on hero | `.cag-home-hero__search-box` |

Radius scale: `sm 0.35rem` · `md 0.5rem` · `lg 0.75rem` · `xl 1rem` · `pill 999px` · `50%` for avatars.
Shadow scale: `rest` · `raised` · `overlay` · `badge` (see tokens). The census found 15+ px radii (3, 4, 5, 6, 9, 10,
12, 14, 16, 20px). Map each to the nearest step.

---

## 7. UI wording

Copy lives in `resources/lang/{en,de}/`. Always add **both** keys.

| Rule | EN (homepage) | DE (homepage) |
|---|---|---|
| **Sentence case** for headings, buttons, links, labels. Capitalise only the first word and proper nouns | "Most popular target fish" | German noun capitalisation as normal |
| Section "see all" link: `Discover all {plural}` | "Discover all target fish", "Explore all countries" | "Alle Zielfische entdecken", "Alle Länder entdecken" |
| Rail/module "see all" (short) | "See all" | "Alle sehen" |
| Card CTA | "View offer" / short "View" | "Angebot ansehen" / "Ansehen" |
| Primary signup CTA | "Register now" | "Direkt registrieren" |
| Lists in eyebrows/meta use a middle dot ` · ` | "Day tours · Fishing lodges …" | "Angeltouren · Angelcamps · Angelreisen" |
| Arrows (`→`) come from CSS (`::after`), never from the lang string | — | — |
| Product nouns: tours = *fishing tours / Angeltouren*, camps = *fishing camps / Angelcamps*, trips = *fishing trips / Angelreisen*. Don't mix in "guidings" or "holidays" in user-facing copy | | |
| German form of address: **du** on the homepage and marketing pages | — | "Geschichten, die dich begeistern" |

**Drift to fold in:** Title Case strings ("All Guidings", "Book Another Guiding", "Best Travel Times"); mixed
"View all" / "Show all" / "All X" labels for the same action; arrows baked into strings ("View all target fish →").
`resources/lang/de` has ~1050 *Sie/Ihr* forms against ~450 *du* forms. The homepage is *du*. Checkout, legal and
email copy is mostly *Sie*. **Open decision:** whether *du* goes site-wide or *Sie* stays for checkout/legal.
Don't convert in bulk until that's decided.

---

## 8. Checklist for any UI change

- [ ] Sizes, colors, radii and shadows come from `_tokens.scss` (or `_category-colors.scss`). No new hex or `px` font-size.
- [ ] One `h1`. Sections are `h2`, cards are `h3`. No skipped levels.
- [ ] Section header matches the homepage pattern (left aligned, see-all link right on desktop and below on mobile).
- [ ] Font is inherited DM Sans. Weights are only 400/500/700.
- [ ] No inline `style=""` for type or color in Blade.
- [ ] Copy is in sentence case, uses the standard CTA wording, and has both `en` and `de` keys.
- [ ] Checked at 375px and ≥1280px (`npm run watch` running).
- [ ] `node .claude/skills/design-sync-audit/scripts/design-audit.mjs <changed files>` shows no new findings.
