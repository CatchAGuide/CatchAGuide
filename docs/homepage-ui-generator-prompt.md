# AI UI Generator Prompt — Catch A Guide Homepage

**How to use:** Copy everything inside the box below into your AI UI generator (v0, Lovable, Galileo, Figma AI, Relume, etc.). Attach 1–2 real Catch A Guide screenshots or logo if the tool allows references. Ask for **desktop 1440px** and **mobile 390px**, and for **at least 3–4 distinct layout options** (not minor color tweaks of the same frame).

After you get designs: pick 1 direction (or mix sections), then implement in Blade using the plan in [`homepage-landing-page-plan.md`](./homepage-landing-page-plan.md).

---

## PROMPT (copy from here)

```
Design a conversion-focused homepage landing page for “Catch A Guide” — a European marketplace to book guided fishing experiences.

CONTEXT & GOAL
- Today the homepage feels like it only sells day fishing tours. Many users do not realize we also offer multi-day fishing vacations (trips & camps).
- Users also need to clearly see we operate across **many countries** — each with a dedicated country category page.
- Users should glimpse real inventory: a mixed carousel of **day tours, trips, and camps** (not tours-only).
- Redesign the homepage as a clear PRODUCT CHOOSER first, then strong COUNTRY discovery, then a MIXED OFFERS showcase, then other content below.
- Must feel fun, welcoming, energetic, and outdoor-adventure — never dull, flat, or boring corporate gray.
- Still trustworthy and easy for non-tech users.
- SEO-friendly structure: one clear H1, logical H2 sections, crawlable text links (not only buttons styled as divs) — especially into country pages.
- Same brand/site as existing product pages — do NOT invent a new brand system.

COLOR HIERARCHY (STRICT — BOSS PREFERENCE)
Lead with gray and dark. Coral/orange is an ACCENT only — not the dominant color of the page.

Foundation (most of the UI):
- Dark slate #313041 — headings, header/footer weight, chooser tile chrome, primary dark surfaces, strong type
- Medium gray #787780 — secondary text, labels, quieter UI
- Light gray / off-white #f8fafc, white, soft borders #ece8e0 — section bands, breathing room
- Optional soft sand #faf5ee — rare warm surface, use sparingly

Accent (sparingly — brand spark, not floods of orange):
- Coral #E8604C (also #E85B40) — primary CTA buttons, small accent lines, active states, hover highlights, key link underlines, tiny badges
- Do NOT paint large backgrounds, big hero panels, or whole sections in coral
- Do NOT make the page “orange-first”; a visitor should see slate/gray first, then coral as the invite to act

Energy without orange overload:
- Rich, high-quality fishing photography (color and life come from images)
- Strong dark/light contrast, confident typography, generous but intentional spacing
- Subtle motion and hover feedback
- Occasional coral CTA so the page still feels alive and clickable
- Avoid lifeless flat gray blocks with no photo, no contrast, and no accent

Typography & logo:
- Nunito for UI text; Mulish or DM Sans OK for headings. Do NOT use Inter, Roboto, Arial, or generic “AI SaaS” fonts.
- Logo wordmark: “Catch A Guide” — hero-level brand signal in header/hero, not a tiny nav-only mark.
- Photography: realistic fishing lifestyle — guides on boats, lakes, coasts, anglers with rods, European outdoor scenery. No clipart, no cartoon mascots, no emoji clusters.

WHAT TO AVOID (HARD NO)
- Coral/orange as the main surface color or large filled hero blocks
- Purple / indigo gradients, neon glow, glassmorphism stacks
- Full dark-mode-only site (dark accents + light sections is fine; pure black app UI is not)
- Dull “enterprise gray” boredom: empty gray boxes, weak contrast, no photos, no accent CTAs
- Warm cream + terracotta full rebrand cliché
- Dashboard look, dense admin-style grids
- First viewport packed with stats, schedules, promo chips, floating badges on the hero image
- More than 3 primary choices above the fold
- Product subdomains or multi-brand layouts — this is ONE site: catchaguide.com

INFORMATION ARCHITECTURE
Nav (top): Logo | Guidings | Vacations | Magazine | language | Become a guide | Login
Footer must also list Guidings, Vacations, and Destinations/Countries (and legal/contact). Prefer dark/slate footer with light text; coral only on key links or a small CTA.

Important routes to annotate in the design:
- /guidings — day tours
- /vacations — fishing vacations hub
- /destination — all countries index
- /destination/{country} — country category page (e.g. /destination/sweden, /destination/deutschland)
Country tiles on the homepage are DYNAMIC from the available DB country list (not a fixed marketing-only set). For the mock, show 6–10 example European countries as placeholders, but design the component as a scalable grid/carousel that can grow/shrink with data.

PAGE STRUCTURE

1) HERO (first viewport — ONE composition, full-bleed photo background)
- Edge-to-edge hero image of real fishing/outdoor scene with a soft dark slate gradient overlay for text readability (no floating stickers on the photo).
- Brand + H1 + one short supporting sentence in white / light gray on the photo.
- H1 idea (EN): “Fishing tours and vacations, booked with local guides”
- Subline idea: “Day trips with experts — or multi-day fishing holidays across Europe’s top fishing countries.”
- PRIMARY UI: two large equal “doors” / chooser tiles (NOT tiny cards in a dense grid):
  Tile chrome: dark slate / charcoal glass or solid dark panels with light text; coral only as a thin accent border, icon highlight, or small “Explore” button — not a full orange card fill.
  A) Guided day tour → goes to /guidings
     Helper text: “Half-day or full-day with a local guide”
  B) Fishing vacation → goes to /vacations
     Helper text: “Multi-day trips & camps”
- Optional tertiary text link only: “Not sure? Get help choosing” (light gray text, coral on hover)
- Search can appear as a SECONDARY control under the chooser (simple destination/country search on a dark/light neutral bar), not as the main hero focus. Do not overwhelm with many filters in the hero.
- Mobile: stack the two chooser tiles full-width, huge tap targets, same order.

2) HOW IT WORKS (H2)
- Exactly 3 steps: Find your trip → Book online → Meet your guide & fish
- Simple icons (slate/gray), short copy, airy layout — one job per section. Tiny coral accent on step numbers OK.

3) TRUST / USP STRIP
- 3–4 short trust points (easy booking, support, verified ratings, personal requests). Compact row on light or soft gray band; no big orange cards.

4) FISH ACROSS COUNTRIES / COUNTRY CATEGORY PAGES (H2) — FIRST-CLASS SECTION
- This section must make multi-country coverage obvious at a glance.
- H2 idea: “Fish in destinations across Europe” or “Choose your country”
- Short supporting line: “Browse country pages for local guides, tours, and fishing vacations.”
- Photo-led country tiles (dark title overlay on lifestyle/destination photos). Each tile is a clear link to a country category page (/destination/{country}).
- Design for a DYNAMIC list from the database (variable count). Mock 6–10 countries (e.g. Germany, Sweden, Spain, Netherlands, Croatia, Denmark, Portugal, Italy, Norway, France) as placeholders — do not present them as the only forever list.
- Include a prominent “Show all countries” text/button link → /destination
- Optional small meta line like “Offers in 10+ countries” (placeholder count)
- Layout: responsive grid or horizontal carousel; large enough tiles that country names are readable on mobile
- Same slate/gray chrome + photo energy; coral only on the “Show all” / hover accents

5) EXPLORE BOTH OFFERINGS (H2)
- Two equal teaser panels side by side (dark/photo-led, not coral fills):
  - Guidings / day tours
  - Vacations (mention trips & camps)
- Same visual weight so vacations are not buried. Coral only on CTA labels.

6) MIXED POPULAR OFFERS RAIL (H2) — TOURS + TRIPS + CAMPS SHOWCASE
- One engaging horizontal carousel (preferred) or scrollable card row — a PARTIAL showcase, not a full catalog.
- H2 idea: “Popular fishing offers” / “Tours, trips & camps”
- Subline: “A taste of day tours and multi-day vacations — explore more in each category.”
- Show ~8–12 cards that MIX product types in one rail:
  - Day tour / Guiding
  - Fishing trip (multi-day)
  - Fishing camp
- Each card MUST have a visible type badge/chip (Tour · Trip · Camp) on a dark/slate chip — coral only as a tiny accent if needed.
- Card anatomy (match existing marketplace cards): photo, title, location/country, “from €X” price; optional rating stars.
- Optional simple filter chips above the rail: All | Tours | Trips | Camps (filters the visible mixed set).
- Section footer links: “Browse tours” → /guidings, “Browse trips” → /vacations/trips, “Browse camps” → /vacations/camps (and/or “All vacations” → /vacations).
- Visual language: same slate/gray cards as the rest of the page; photography brings the energy. Do NOT use seven separate tabbed carousels (old homepage pattern) — one mixed rail is clearer and more fun.
- Engineer note for annotations: data is dynamic — guidings from popular/new tour listings; trips/camps from the same style of mixed hub rail used on /vacations (interleaved types).

7) MAGAZINE TEASER (H2)
- Editorial content block — dark/gray type hierarchy, photo-led; still on-brand.

8) BECOME A GUIDE CTA
- Prefer a dark slate band with white text and ONE coral primary button (accent, not a full orange section).

INTERACTION / MOTION (ELEGANT, ENGAGING, NOT OVERKILL)
- Chooser hover: slight lift, soft shadow, 1.03 image scale, thin coral accent border or glow — feels lively, not flashy
- Gentle fade-in on scroll for sections below the fold
- Primary button: coral fill #E8604C → hover to darker coral or dark slate #313041
- Secondary controls: dark/gray with coral accent on hover
- Premium outdoor marketplace energy — no parallax chaos, no autoplay video, no particle effects, no Lottie spam
- Static state must still look complete and inviting if motion is disabled

LAYOUT RULES
- Desktop ~1440px artboard and Mobile ~390px
- Generous whitespace; sections with one headline + one short supporting line
- Prefer large interactive tiles for the chooser; avoid nesting cards inside cards in the hero
- Corner radius: modest (8–16px), not pill-everything
- Primary CTAs: coral #E8604C with white text (use few, make them count)
- Most chrome, type, and surfaces: slate + gray + white + photos

DELIVERABLES (REQUIRED — AT LEAST 3–4 SAMPLE LAYOUTS)
- Produce **at least 3 to 4 distinct full homepage layout concepts** (desktop). Do not submit only one design.
- Also provide a **mobile (~390px) version** for each concept (or at least for the top 2 favorites if the tool limits output).
- Layouts must share the same brand rules (slate/gray foundation, coral accent, same IA sections) but **vary composition meaningfully**, for example:
  1) **Chooser-forward** — large dual doors dominate the hero; search minimal
  2) **Photo-story** — cinematic full-bleed hero with chooser as floating dark panels lower in the hero
  3) **Countries-forward** — after a compact hero chooser, country grid is the visual star before mixed offers
  4) **Marketplace rail-first** — after chooser + short trust, the mixed Tour/Trip/Camp carousel is the main discovery engine (countries still present, slightly tighter)
- For every layout, include optional close-ups of: hero chooser, country tiles, mixed offers carousel (Tour/Trip/Camp badges visible)
- Annotate key links: /guidings, /vacations, /vacations/trips, /vacations/camps, /destination, /destination/{country}, magazine
- Use placeholder real-looking fishing/destination photos (unsplash-style), not abstract gradients as the main visual
- Label country section as “dynamic from DB country list”
- Label offers rail as “mixed dynamic listings: tours + trips + camps”
- Label each concept clearly (Layout A / B / C / D) so stakeholders can compare and pick
- End with a one-line note per layout: what feeling it optimizes (clarity / atmosphere / geography / browsing)

TONE
- Fun, engaging, and adventurous — dark/gray sophistication with coral sparks of action
- Clear that Catch A Guide is multi-product (tours + trips + camps), multi-country, and worth browsing
- NOT dull, muted, or boring; NOT loud orange carnival
- Outdoor marketplace energy, European fishing travel, trustworthy booking site
```

## PROMPT (copy until here)

---

## Optional follow-up prompts (paste after first result)

**If the tool only returned one layout:**
```
Generate 3 more distinct homepage layout variations (Layout B, C, D) using the same brand rules and section IA. Vary hero composition, country section prominence, and mixed offers placement — do not just recolor the first layout.
```

**If the tool makes everything orange/coral:**
```
Reduce coral to accent only. Most UI should be dark slate #313041 and grays. Coral #E8604C only on primary CTAs, thin borders, and hover accents — not large backgrounds or filled sections. Keep the page lively with photography and contrast, not with orange floods.
```

**If the tool makes it dull/boring gray:**
```
Keep the gray/dark foundation but make it more engaging: richer hero photography, stronger contrast, clearer hierarchy, subtle hover motion, and coral accent CTAs so the page feels alive — not flat corporate gray.
```

**If countries are weak or missing:**
```
Strengthen the “Fish across countries” section: make it a first-class H2 with large photo-led country tiles linking to /destination/{country}, plus “Show all countries” → /destination. Design tiles as a dynamic DB-driven list (variable count), not a tiny footer afterthought. Multi-country coverage must be obvious without scrolling forever.
```

**If offers look tours-only or too fragmented:**
```
Replace multiple tour-only carousels with ONE mixed “Popular fishing offers” rail that interleaves day tours, trips, and camps. Every card needs a Tour / Trip / Camp badge. Add footer links to /guidings, /vacations/trips, and /vacations/camps. Keep it as a teaser carousel (~8–12 cards), not a search results wall.
```

**If the tool drifts off-brand:**
```
Revise to dark slate #313041 + grays as the base, coral #E8604C as accent only. Remove purple/glass/neon. Keep Nunito-like typography. Hero must lead with two equal product chooser doors (day tour vs vacation), not a search-first filter bar.
```

**If the first viewport is too busy:**
```
Simplify the hero: only brand, one H1, one sentence, two large chooser tiles, optional secondary search. Keep the country grid below the fold as the main geographic discovery — do not pack all countries into the hero.
```

**If vacations still look secondary:**
```
Make the Guidings and Vacations chooser tiles identical in size and visual weight. Below the fold, add an equal two-panel “Explore both offerings” section.
```

**DE locale note (for copy variants):**
```
Provide a German copy variant for H1/sub/chooser/country labels: emphasize “Geführte Angeltouren”, “Angelurlaub” (Trips & Camps), and “Länder / Reiseziele” with the same layout. Country tiles still link to country category pages.
```

---

## Reference checklist when reviewing AI output

- [ ] At least 3–4 distinct layout concepts delivered (not clones)
- [ ] Slate/gray dominate; coral only as accent (CTAs, thin highlights)
- [ ] Page still feels lively (photos, contrast, motion) — not dull corporate gray
- [ ] Not an orange-first / coral-flooded layout
- [ ] First viewport = chooser, not guidings-only search
- [ ] Vacations visually equal to Guidings
- [ ] Strong country section: DB-style dynamic tiles → `/destination/{country}` + “Show all” → `/destination`
- [ ] Multi-country offering is obvious (not hidden)
- [ ] Mixed offers carousel shows Tours + Trips + Camps with type badges (not guidings-only, not seven tabs)
- [ ] Full-bleed hero photo, no sticker overlays
- [ ] Mobile: big stacked doors + readable country tiles + swipeable offer cards
- [ ] Motion described as subtle only
- [ ] Clear H1 / section H2s for SEO
- [ ] Footer includes Vacations and Destinations/Countries

## Related

- Build plan: [`homepage-landing-page-plan.md`](./homepage-landing-page-plan.md)
