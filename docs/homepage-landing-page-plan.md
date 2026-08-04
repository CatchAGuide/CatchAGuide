# Homepage Landing Page — Build & Design Plan

**Goal:** Turn `/` into a clear product chooser that shows Catch A Guide offers **more than day fishing tours** (Guidings + Vacations), surfaces **country category pages** from the available DB country list, partially showcases a **mixed rail of tours + trips + camps**, improves conversion for non-expert users, and strengthens SEO — without new subdomains or splitting the codebase.

**Out of scope for this phase:** Product subdomains, separate apps/repos, full modular refactor. Stay on path hubs under `catchaguide.com` / `catchaguide.de`.

---

## 1. Problem (today)

| Issue | Current state |
|-------|----------------|
| Product clarity | Homepage is guidings-first; vacations barely surfaced |
| Geographic clarity | Users may not realize offers span **many countries**; country tiles are partly hardcoded, not clearly tied to DB country category pages |
| Conversion | Hero search dumps users into tour filters before they know product types |
| UX | Too many similar sections; decision path is unclear for first-time visitors |
| SEO | Title/meta still tour-centric; weak internal links to `/vacations` and underused links into `/destination/{country}` hubs |
| Nav vs content | Header has Guidings · Vacations; homepage body and footer do not match |

**Primary success questions:**  
> “What can I book here — and which door do I open?”  
> “Where can I go — which countries do you cover?”

---

## 2. Design principles

1. **One decision first** — Day tour vs fishing vacation (optional: “Not sure”).
2. **Brand first** — Catch A Guide logo/name remains the hero signal.
3. **Gray/dark lead, coral accent** — Slate and gray carry the UI; coral/orange is the brand spark on CTAs and highlights only (boss preference).
4. **Fun, not dull** — Energy from photography, contrast, and light motion — not from flooding the page with orange, and not flat corporate gray.
5. **Keep the theme** — Same token set; shift hierarchy (accent vs foundation), do not invent a new palette or font stack.
6. **Stupid-simple paths** — Large tap targets, plain language, max 2–3 choices above the fold; countries as an obvious next step below.
7. **Countries from the DB** — Homepage tiles reflect available countries and deep-link into existing `/destination/{country}` category pages.
8. **SEO without clutter** — Semantic H1/H2, crawlable links to hubs and country pages, richer meta; content below the fold supports keywords without stuffing the hero.

---

## 3. Brand tokens (must keep)

Use existing Catch A Guide theme, with **slate/gray as foundation** and **coral as accent**:

| Token | Value | Use |
|-------|--------|-----|
| Dark (foundation) | `#313041` | Headings, header/footer weight, chooser chrome, dark bands |
| Gray (foundation) | `#787780` | Secondary text, quieter UI |
| Light (foundation) | `#fff` / page `#f8fafc` | Surfaces, section bands |
| Border / warm neutral | `#ece8e0` / soft sand `#faf5ee` | Dividers; sand sparingly |
| Coral accent | `#E8604C` / `#E85B40` | Primary CTAs, thin borders, active/hover accents — not large fills |
| Star | `#ffce00` / rated `#FF9529` | Ratings only |
| Fonts | **Nunito** (UI), **Mulish** / **DM Sans** / **Raleway** as already used | No Inter/Roboto/system swap |
| Logo | Existing `CatchAGuide2_Logo_PNG.png` | Header + hero brand presence |

**Visual mood:** Sophisticated outdoor marketplace — dark slate + gray structure, life from real fishing photos, coral only where you want action. Engaging and confident, never orange-loud and never dull gray.

---

## 4. Information architecture (path-based)

```text
/  (new landing — product chooser + country discovery)
├── /guidings                          → day tours
├── /vacations                         → multi-day hub
│   ├── /vacations/trips
│   └── /vacations/camps
├── /destination                       → all countries index
├── /destination/{country}             → country category page (DB-driven)
│   └── /destination/{country}/{region}/{city?}  → deeper geo SEO
├── /category-page/...                 → fish/method SEO (keep)
└── magazine                           → content (keep)
```

Homepage is the **router**; modules and **country category pages** stay on existing path hubs.

**Country data rule:** Homepage country tiles must come from the **available DB country list** (same source of truth as destination/vacation country listings) — not a hard-coded marketing subset that drifts from what is bookable. Show featured/popular countries on `/`, with a clear “Show all countries” link to `/destination`. Each tile links to its country category page (`/destination/{slug}`).

---

## 5. Page wireframe (section order)

### Above the fold (one composition)

1. **Header** — existing nav (Guidings · Vacations · Magazine); keep.
2. **Hero**
   - Full-bleed fishing atmosphere photo (edge-to-edge).
   - Brand/logo presence + **one H1** (see SEO copy).
   - One short supporting line.
   - **Product chooser** (2 large doors + optional help):
     - **Guided day tour** → `/guidings`  
       Subline: “Half-day or full-day with a local guide”
     - **Fishing vacation** → `/vacations`  
       Subline: “Multi-day trips & camps”
     - Optional text link: “Not sure? Tell us what you need” → contact / assistant
   - Search bar **secondary** (below chooser or collapsed “Search all offers”) — do not make filters the first decision.

**Hero budget:** brand + H1 + one sentence + chooser (+ optional light search). No stats strip, no badge pile, no card grid of 8 categories in the first viewport.

### Below the fold (SEO + discovery)

3. **How it works** — 3 steps max (Search → Book → Fish). Icons ok; short copy.
4. **Trust strip** — reuse USPs (easy booking, support, verified ratings). Compact.
5. **Fish across countries** (country category discovery) — **first-class section, not an afterthought**
   - H2 that states multi-country coverage (Europe-wide / available destinations).
   - Photo-led country tiles driven by **DB available countries** (name, image, link).
   - Each tile → country category page: `/destination/{country}`.
   - “Show all countries” → `/destination` (full index).
   - Optional microcopy: “Day tours and vacations in [N]+ countries” if count is easy from DB.
   - Do not hardcode only DE/SE/ES/… if the DB has more (or fewer) — design for a **dynamic list** (carousel or responsive grid).
6. **Also from Catch A Guide** — dual teaser: Guidings highlight + Vacations (trips/camps) with equal visual weight.
7. **Mixed popular offers** (tours + trips + camps) — **partial showcase carousel/rail** (see §5.1)
8. **Optional slim experience rail** — at most one guiding-theme rail (not seven competing tabs).
9. **Magazine teaser** — keep for content SEO.
10. **Become a guide** — keep CTA block.
11. **Footer** — add Vacations + Destinations/Countries + clear module links (parity with header).

### 5.1 Mixed listings showcase (how it works today → what we build)

#### Current homepage (`/` → `newhome-latest`)

| Rail / block | Source today | Product types |
|--------------|--------------|---------------|
| Experience tabs (Pure action, Sea, Family, Boat, Fly, Shore, Multiday) | `ViewServiceProvider` composers → cached `Guiding` queries (`activeFishing`, `seaFishing`, …) → `pages.partials.slider` / Owl carousel | **Guidings only** |
| Frequently booked | `bookedGuidings` (booking count) | **Guidings only** |
| Recently added | `newGuidings` | **Guidings only** |
| Near you (mostly disabled) | AJAX `WelcomeController@getUserLocation` | **Guidings only** |

`WelcomeController` itself only passes favorite target/method category pages. Listing rails are global view composers — heavy, guidings-centric, no trips/camps.

#### Current vacations hub (`/vacations`)

| Rail / block | Source today | Product types |
|--------------|--------------|---------------|
| Pillar tiles | `VacationHubPageService` → camps vs trips counts/prices | Camps / Trips doors |
| **Popular (mixed)** | `PopularListingSelector::mixedForHub()` — ~half camps + half trips via repos, presenters, **shuffle**, take N | **Trips + Camps mixed** |
| New camps / New trips | Separate Swiper rails (`x-vacation.card-slider` + `camp-card` / `trip-card`) | Single type per rail |
| Country slider | `VacationDestinationRepository::countriesForHubGrid()` | Geo |

This is the pattern to reuse for a **cross-catalog** homepage rail: repositories + presenters + one slider, mixed types with a type badge.

#### Target homepage mixed showcase

**One primary rail** (carousel or horizontal list), e.g. “Popular fishing offers” / “Picked for you”:

- **Combine** a balanced sample of:
  - Guidings (day tours) — from existing popular/new/publiclyVisible guiding queries
  - Trips — via `TripListingRepository` + `TripCardPresenter` (same as hub)
  - Camps — via `CampListingRepository` + `CampCardPresenter` (same as hub)
- **Interleave / shuffle** like `mixedForHub()` (e.g. ~1/3 each, or configurable weights), limit ~8–12 cards.
- Each card shows a clear **type chip**: Tour · Trip · Camp (slate chip, coral accent optional).
- Card content: image, title, location/country, from-price — same information density as guiding slider cards and vacation `product-card` / slider cards.
- Links: guiding PDP / trip PDP (`/vacations/trips/{slug}`) / camp PDP (`/vacations/camps/{slug}`).
- Footer of section: text links “Browse tours”, “Browse trips”, “Browse camps” (or single “Explore vacations”).
- **UI:** prefer Swiper-style rail (as vacations) for consistency; Owl is fine only if reusing homepage CSS short-term.
- **Do not** dump every catalog — this is a **teaser**, not a search results page.
- Optional filter chips above the rail: All | Tours | Trips | Camps (client filter of the mixed set; keep simple).

**Secondary (optional, if space):** one “New on Catch A Guide” mixed or type-split mini-rail — only if it does not recreate the old seven-tab wall.

---

## 6. Interaction enhancements (elegant, not overkill)

| Interaction | Intent | Constraint |
|-------------|--------|------------|
| Chooser card hover | Slight lift + image scale 1.03 + coral border | CSS only; respect `prefers-reduced-motion` |
| Scroll reveal | Fade/slide-up once per section | Intersection Observer; no endless loops |
| Hero | Slow Ken Burns **or** static + subtle gradient overlay | One effect only |
| CTA hover | Primary → dark (`#313041`) as today | Keep existing button language |
| Mobile | Stack chooser as 2 full-width tiles; large tap areas | Same order as desktop |

Avoid: autoplay video backgrounds, parallax stacks, cursor trails, heavy Lottie everywhere.

---

## 7. SEO plan

### Meta / titles (update with EN + DE)

| Locale | Direction |
|--------|-----------|
| EN title | Cover tours **and** vacations, e.g. “Book guided fishing tours & fishing vacations \| Catch A Guide” |
| DE title | Same intent for AngelTouren + Angelurlaub |
| Meta description | Mention day tours + multi-day vacations/camps + **multiple countries / Europe** |
| H1 | One clear H1 matching title intent (not only “guided fishing tour”) |
| H2s | How it works, **Countries / destinations**, **Popular offers (mixed)**, Vacations, Magazine |

### On-page

- Crawlable `<a href>` to `/guidings`, `/vacations`, `/vacations/trips`, `/vacations/camps`, `/destination`, **each shown** `/destination/{country}`, magazine, and mixed-offer PDPs.
- Country section is a major internal-link hub into existing **country category pages** (SEO equity for DestinationCountryController pages).
- Mixed offers rail deep-links into guiding + trip + camp PDPs (crawlable cards, not JS-only).
- Keep Organization JSON-LD (layout); consider `WebSite` + `SearchAction` if not already solid.
- Internal links from footer + chooser + country grid (high priority).
- Image `alt` with country + activity language (e.g. “Guided fishing in Sweden”); lazy-load below fold.
- Do not noindex `/`.

### Measurement

- Events: `homepage_chooser_guidings_click`, `homepage_chooser_vacations_click`, `homepage_country_click`, `homepage_all_countries_click`, `homepage_mixed_offer_click` (with `product_type`: tour|trip|camp), `homepage_search_submit`.
- Compare bounce / CTR to `/vacations`, `/destination/{country}`, and mixed PDP types vs baseline after launch.

---

## 8. Copy direction (EN placeholders — final via lang files)

- **H1:** “Fishing tours and vacations, booked with local guides”
- **Sub:** “Day trips with experts — or multi-day fishing holidays across Europe.”
- **Door A:** “Guided day tour” / “Go fishing for a day”
- **Door B:** “Fishing vacation” / “Trips & camps for several days”
- **Countries H2:** “Fish in destinations across Europe” / “Choose your country”
- **Countries sub:** “Browse country pages for local guides, tours, and fishing vacations.”
- **All countries CTA:** “Show all countries”
- **Mixed offers H2:** “Popular fishing offers” / “Tours, trips & camps”
- **Mixed offers sub:** “A taste of day tours and multi-day vacations — explore more in each category.”
- Mirror in `resources/lang/en/homepage.php` and `resources/lang/de/homepage.php`.

Tone: plain, confident, outdoor — no jargon, no “synergy.”

---

## 9. Build phases (implementation)

### Phase 0 — Align (½–1 day)

- [ ] Stakeholder sign-off on chooser IA + wireframe
- [ ] Run UI generator with companion prompt → pick 1–2 directions
- [ ] Confirm hero photo assets (rights, WebP candidates)

### Phase 1 — Design freeze (1–2 days)

- [ ] Desktop + mobile mock from generator / Figma polish
- [ ] Map sections to Blade partials
- [ ] SEO title/description draft EN/DE approved

### Phase 2 — Build (3–5 days)

- [ ] New Blade structure under `resources/views/pages/` (e.g. partials: `hero-chooser`, `how-it-works`, `country-grid`, `mixed-offers-rail`, keep magazine blocks)
- [ ] Wire `WelcomeController` (prefer explicit data over more global `ViewServiceProvider` composers) for:
  - available countries from DB
  - **mixed offers** (guidings + trips + camps) — mirror `PopularListingSelector::mixedForHub()` with a guiding slice added
- [ ] Reuse vacation presenters/cards where possible; guiding cards can wrap existing slider card fields + type badge
- [ ] Country tiles link to `route('destination.country', …)`; “all” → `route('destination')`
- [ ] Slim or remove the seven experience-tab rails (replace with mixed rail + optional one theme rail)
- [ ] Styles in `resources/sass/page/home.scss` (prefer SCSS over huge inline `<style>`)
- [ ] Lang keys EN/DE
- [ ] Footer Vacations + Destinations/Countries links
- [ ] Analytics events on chooser, country clicks, mixed-card clicks (include `product_type`)

### Phase 3 — SEO & QA (1–2 days)

- [ ] Titles, meta, H1/H2, OG tags
- [ ] Lighthouse / mobile layout check
- [ ] Reduced-motion check
- [ ] Click paths: `/` → guidings, `/` → vacations → trips/camps, `/` → country category → deeper destination
- [ ] Confirm homepage country set matches DB availability (no dead links / ghost countries)
- [ ] DE/EN parity on `.de` / `.com`

### Phase 4 — Launch

- [ ] Feature flag or soft launch if preferred
- [ ] Monitor chooser CTR + vacation landings for 1–2 weeks
- [ ] Iterate copy/order; do not rewrite architecture

---

## 10. File touch map (expected)

| Area | Likely files |
|------|----------------|
| View | `resources/views/pages/newhome-latest.blade.php` (+ partials: country grid, mixed offers) |
| Controller | `app/Http/Controllers/WelcomeController.php` (explicit homepage payload) |
| Mixed listings | New small selector service (e.g. extend/`compose` with `PopularListingSelector` + guiding query) — avoid adding more `View::composer('*')` rails |
| Vacation cards | `CampCardPresenter`, `TripCardPresenter`, `x-vacation.card-slider`, `camp-card`, `trip-card` |
| Guiding cards | Existing `pages.partials.slider` field pattern / featured image helpers |
| Countries data | Same source as `DestinationCountryController` / vacation country grid |
| Country pages | Existing `/destination/{country}` (link only) |
| Legacy composers | `app/Providers/ViewServiceProvider.php` — plan to **stop relying** on homepage-only caches long term |
| Styles | `resources/sass/page/home.scss` (+ vacation rail styles if reused) |
| Copy | `resources/lang/en/homepage.php`, `resources/lang/de/homepage.php` |
| Footer | `resources/views/layouts/partials/footer.blade.php` |

Reuse vacation hub patterns (`pillar-tile`, country-slider, **mixed popular rail**) for chooser, countries, and cross-catalog showcase.

---

## 11. Risks & mitigations

| Risk | Mitigation |
|------|------------|
| Guidings SEO traffic fears | Keep destination + experience sections; broaden title without dropping “fishing tour” keywords |
| Hard-coded countries drift from DB | Drive homepage tiles from available DB list; empty/missing countries never shown |
| Guidings-only rails persist | Replace seven-tab wall with one mixed rail; stop adding homepage data via global `View::composer('*')` |
| Design drift from brand | Lock tokens in §3; reject purple/glass UI-generator defaults |
| Scope creep (rebuild entire site) | Homepage + footer only; country **category pages already exist** — link to them |
| Performance | Optimize hero image; lazy-load rails; limit simultaneous carousels; cap featured countries if list is long |

---

## 12. Definition of done

- First viewport answers product types without scrolling.
- Equal-weight paths to Guidings and Vacations.
- Clear multi-country discovery: DB-driven tiles → country category pages + link to `/destination`.
- Mixed offers rail showcases **tours + trips + camps** (type badges, deep links), not guidings-only.
- Brand colors/fonts unchanged (slate/gray lead, coral accent); motion subtle and optional.
- EN + DE meta/H1 updated; internal links to vacation hubs **and** country pages crawlable.
- Mobile usable with large chooser tiles, country tiles, and offer cards.
- No subdomain / infra changes.

---

## Companion doc

Copy-paste prompt for AI UI tools: [`homepage-ui-generator-prompt.md`](./homepage-ui-generator-prompt.md)
