# Category Pages — Data Consolidation Plan

**Status:** Phase 0 (audit), Phase 1 (additive schema), Phase 2's mechanical backfill, Phase 3 (write
cutover), Phase 4 (read cutover), and — **against dev data only, see the warning below** — a first pass of
**Phase 5 (decommission)** are all done as of 2026-08-23. See §12–§17. Every public-facing and admin-facing
read path that used to query `Country`/`Region`/`City` (`c_countries`/`c_regions`/`c_cities`) now reads
`CategoryEntity` (`category_entities`) instead — see §15 for the full file list and the id-remap/CMS-overlay
design that made this safe. A pre-existing regression from Phase 3 (fish-chart/size/time-limit admin data
silently invisible after the write cutover) was found and fixed in the same session — see §15 §0. A
follow-up session on 2026-08-23 completed the browser click-through verification Phase 4 had deferred (§16,
no regressions), then resolved the 96-row orphaned-FAQ worklist (§9 risk #11 — all 96 turned out to be
resolvable after all, see §17) and dropped `c_countries`/`c_regions`/`c_cities`/`c_*_translations`/
`destinations` (§17).

> **⚠️ Production sequencing warning.** Phase 5's table drops were run against the **local dev database only**
> — this entire migration (Phases 1–5) is still uncommitted work on `feat/new-home-page-design`, and nothing
> has shipped to production. The plan's original §10 caution — "don't drop tables until a production
> verification window has passed after Phase 4 ships" — was written assuming Phase 4 would already be live in
> production before Phase 5 was considered. That assumption no longer holds: **when this branch is deployed,
> every pending migration (Phases 1 through 5, including the table-drop migration) will run in the same
> release** unless someone deliberately splits them. Before deploying, decide whether to (a) ship Phases 1–4
> first, run them in production for a real verification window, then ship the Phase 5 drop migration in a
> later release (matches the plan's original intent), or (b) accept shipping all five phases in one release
> now that Phase 5 has been dev-verified end-to-end (§17). This is a deployment-planning decision, not a data
> question — flagging it here so it isn't missed.

Still open: the `destination_fish_charts`/`_size_limits`/`_time_limits` → `regulations` migration (decided
manual-only, §9 risk #10, still blocked on real regulatory source citations) and the `destination_faqs` table
itself (kept, since those three fish tables and this one still write/read against legacy ids via
`category_entity_migration_map` until their own manual re-verification worklist clears — see §17).

**Goal:** Collapse the fragmented table set behind the six admin category-hub editors (Destination Hub,
Targets, Methods, Country, Region, City) into a small, consistent set of tables — one unified entity table
for the geo hierarchy, the existing `languages`/`faqs` pattern extended to be the single home for all
translated content and FAQs across every dimension, and a dedicated `regulations` reference table for
legal/regulatory facts (seasons, closed seasons, size limits) that are explicitly *not* treated as
translated page content.

**Out of scope for this phase:** The `targets`/`methods` catalog tables themselves (used across 28+ files
in the booking/guide domain, not just category pages) and the `category_pages` metadata table (name/slug/
thumbnail/is_favorite for Targets/Methods) — both stay as-is.

**Revised 2026-08-20 — `destinations` is now in scope, not exempt.** The original draft left the
`Destination` model / `destinations` table (Vacations/Trips destination catalog) as-is, on the assumption
that migrations `2026_08_12_171000_...` and `...172000_...` had already retired it. They didn't — they only
copied *content* out of matched rows into `languages`/`destination_fish_*`/`destination_faqs`; the
`destinations` rows themselves (132 of them, across `country`/`region`/`city`/`vacations` types) are still
there, and 6 `vacations` rows were never matched at all. See §6 for the full breakdown and migration plan;
the table is now slated for a real migration and drop in Phase 5, same as the other legacy tables.

---

## 1. Problem (today)

The admin category hub (`AdminCategoryHubController`, 6 dimension controllers under
`app/Http/Controllers/Admin/Category/`) persists content across **11 tables**, three different content
systems, and two different "which table did this write land in" branches depending on whether a request
includes `content_scope`:

| Table | Owner dimension(s) | Role |
|---|---|---|
| `c_countries` | Country | base entity: name, slug, countrycode, filters, thumbnail |
| `c_regions` | Region | base entity, `country_id` FK |
| `c_cities` | City | base entity, `country_id` + `region_id` FK |
| `c_country_translations` | Country | **legacy** per-locale copy (title/intro/body + fish_avail/size_limit/time_limit text) |
| `c_region_translations` | Region | same, `region_id` FK |
| `c_city_translations` | City | same, `city_id` FK |
| `destination_fish_charts` | Country/Region/City *and* `Destination` (Vacations/Trips) | monthly availability grid per fish |
| `destination_fish_size_limits` | same | one text value per fish |
| `destination_fish_time_limits` | same | one text value per fish |
| `destination_faqs` | same | FAQ rows, discriminated by `destination_type` string, **no FK** (dropped in a 2026-08-12 migration) |
| `languages` + `faqs` | **all six dimensions** | the newer, generic, scope-aware content system (`type`/`page` + `source_id` + `scope` + `language`) |

Country/Region/City write to **both** their legacy `*_translations` table and the generic `languages`/`faqs`
pair on every save, branching on `content_scope` — this is a mid-migration state, not an intentional design.
See `docs/` conversation history / the save-flow analysis PDF for the full per-controller trace.

---

## 2. Translation strategy

**Decision: keep and extend the existing `languages` table as the single translation store for every
dimension.** Do not introduce per-locale columns on the entity table (`title_en`/`title_de`, etc.) and do
not create a new translations table for the unified geo entity.

### Why `languages` over the alternatives

| Option | Verdict |
|---|---|
| **Wide columns per locale** (`title_en`, `title_de`, ...) on `category_entities` | Rejected. Doesn't scale past 2 locales without a schema change per language, and breaks the existing `scope` dimension (Country content already varies by `global`/`tours`/`vacations`/`trips`/`camps` — that's a second axis wide columns can't express without a column explosion). |
| **New table per entity type** (mirroring today's `c_country_translations`, etc.) | Rejected. This is what we're trying to get rid of — it's exactly the "6 near-duplicate tables" complexity the hub currently has. |
| **Extend `languages`** (chosen) | It already implements the correct shape — `(source_id, type, scope, language) → content` — and is already the live, actively-used path for all six dimensions today (the legacy tables are the redundant half, not the other way around). No new table, no new query pattern to learn; just widen it to also carry the fields that currently only exist in the legacy `*_translations` tables. |

### Schema change required

Add six nullable columns to `languages` (all currently only exist on `c_country_translations` /
`c_region_translations` / `c_city_translations`):

```
fish_avail_title    string   nullable
fish_avail_intro    text     nullable
size_limit_title    string   nullable
size_limit_intro    text     nullable
time_limit_title    string   nullable
time_limit_intro    text     nullable
```

After this, one `languages` row (keyed by `source_id` + `type` + `scope` + `language`) fully represents a
page's translated copy: `title`, `sub_title`, `introduction`, `content`, `faq_title`, and now the three
fish-section headers/intros — no more split between two tables for what is conceptually one page's content
in one locale.

### Merge rule for existing divergent data

Because Country/Region/City have been dual-writing, the same field can already hold different values in
`c_country_translations` vs the scoped `languages` row for the same entity/locale. Backfill must pick a
deterministic precedence: **the scoped `languages` value wins when both are non-empty** (it's the actively
edited path per the current UI); the legacy value only fills a field that's empty in `languages`. This rule
needs a human sanity-check against a handful of real rows before the backfill runs for real — see Phase 2.

---

## 3. FAQ strategy

**Decision: keep and reuse the existing `faqs` table as-is.** No schema change needed — it's already
`(page, source_id, scope, language) → question/answer`, generic across all six dimensions, and is already
the live path.

- `destination_faqs` rows belonging to Country/Region/City (`destination_type` ∈ `country`/`region`/`city`)
  get migrated into `faqs` with `page = geo_country|geo_region|geo_city`, `source_id` = the new unified
  entity id, and a `scope` assigned per the rule below.
- `destination_faqs` rows still legitimately owned by the `Destination` model (Vacations/Trips) are **not**
  touched by this migration unless Phase 0's audit (below) finds they're already orphaned.
- **Scope assignment for migrated FAQ rows:** legacy `destination_faqs` predates the `scope` concept
  entirely. Assign `scope = 'tours'` on migration (matching the precedent already set by migration
  `2026_08_12_150000_add_scope_to_category_page_content.php`, which backfilled `scope='tours'` for the
  equivalent pre-scope `faqs` rows on Targets/Methods).

---

## 4. Regulatory data strategy — seasons, closed seasons, size limits

**Decision: replace `destination_fish_charts`, `destination_fish_size_limits`, and
`destination_fish_time_limits` with one new reference table, `regulations`** — but treat it as **legal
reference data with source citation, not as translatable page content**, and share rows across every page
that touches the same country/region–species pair rather than duplicating them per dimension.

This section revises the original `category_fish_metrics` draft after reviewing a data-migration spec
circulated separately (2026-08-20). That spec's core insight applies directly to our schema even though its
table/collection names don't match ours: closed seasons and size limits are **legal facts that vary by
country/region and change annually**, not copy an admin writes per page. Storing them per entity/per
language — which is what both the current `destination_fish_*` tables and the original
`category_fish_metrics` draft do — guarantees the same fish/country pair drifts into disagreement across
pages over time, and gives a wrong legal fact exactly the same authority as a correct one. Anglers acting on
a wrong closed season or size limit commit an offence, so this is worth a stricter model than the rest of
the content in this plan.

```
regulations
├── id              bigint PK
├── country_id      bigint    FK → category_entities.id (type='country')                [required]
├── region_id       bigint    FK → category_entities.id (type='region'), nullable       [nullable — country-wide rule]
├── target_id       bigint    FK → targets.id                                           [required]
├── jan..dec        tinyint   nullable  -- availability grid, same shape as today's destination_fish_charts
├── closed_from     date      nullable  -- legal closed season start
├── closed_to       date      nullable  -- legal closed season end
├── min_size_cm     integer   nullable
├── max_size_cm     integer   nullable  -- slot limits exist on some waters
├── bag_limit       integer   nullable
├── licence_note    text      nullable
├── source_url      text      [required]  -- official authority the figures were verified against
├── source_name     text      [required]
├── verified_at     date      [required]
├── verified_by     text      [required]
└── timestamps
```

**Not scope-aware, not language-aware.** Unlike `languages`/`faqs`, a `regulations` row has no `scope` or
`language` column — the underlying legal fact doesn't change by locale or by which product (tours/vacations/
camps) the page belongs to. Any locale-specific label text stays in the `en`/`de` lang files, not this table.
One row serves every page that binds the matching country, region, or species — including `global_country`,
every scoped Country page, and (net-new, see below) Target/species pages, instead of one copy per page.

**Real foreign keys throughout** — `country_id`, `region_id`, and `target_id` all reference real base
tables (`category_entities`, `targets`), a genuine integrity improvement over today's FK-less
`destination_id` + `destination_type` string pair.

**Rendering rule:** suppress the entire regulations block for a page rather than show a stale or unverified
figure — i.e. if no row exists, or `verified_at` is older than an agreed staleness threshold (needs a number
from the business — see §8), render nothing, never a guess. Always display `source_name`, a link to
`source_url`, and `verified_at` alongside the figures.

**Migration action is manual, not a mechanical backfill.** Unlike the other tables in this plan,
`destination_fish_size_limits.data`/`destination_fish_time_limits.data` are free-text strings today (e.g.
"40cm", "01.03–31.05") with no source citation at all — there is nothing to automatically populate
`source_url`/`source_name`/`verified_at`/`verified_by` from. Phase 2 for this table specifically means:
extract the existing free-text values as a starting worklist, then have a human re-verify each one against
an official source and enter it fresh. Do not parse the free text programmatically into structured columns.
Where a value can't be verified, drop it rather than carrying an unverified guess forward.

**Net-new capability, not required for this migration:** because `regulations` is keyed by `target_id`
independent of country, it can also be rendered on Target/species pages (all countries with a verified row
for that species) — something the current `destination_fish_*` tables can't do at all today, since they're
only keyed by destination. Worth noting as a follow-on opportunity; it does not need to be built as part of
this consolidation.

---

## 5. Entity / identity strategy (the geo hierarchy itself)

**Decision: unify `c_countries` + `c_regions` + `c_cities` into one table, `category_entities`,
discriminated by `type`.**

```
category_entities
├── id             bigint PK
├── type           string    -- 'country' | 'region' | 'city'
├── parent_id      bigint    nullable, FK → category_entities.id  (region→country; city→region)
├── country_id     bigint    nullable  -- denormalized helper, see risk below
├── region_id      bigint    nullable  -- denormalized helper, populated only for type='city'
├── name           string
├── slug           string
├── countrycode    string    nullable  -- country rows only
├── filters        json      nullable
├── thumbnail_path text      nullable
├── timestamps, soft deletes
```

**Why `country_id`/`region_id` are kept alongside `parent_id` (not a "pure" adjacency list):** a strict
`parent_id`-only design would force a recursive lookup ("walk up from city → region → country") everywhere
the app currently does a flat `WHERE country_id = ?` — which is most read paths today
(`VacationDestinationRepository`, `CampListingRepository`, `TripListingRepository`, filters, sitemap
contributors). Denormalizing `country_id` (and `region_id` for cities) keeps those queries a single indexed
column lookup, at the cost of the table not being a textbook-pure hierarchy. This is a deliberate trade-off,
not an oversight — flagging it here so it isn't "fixed" into a pure adjacency list later without
re-checking the read-path cost.

**Uniqueness:** MySQL unique indexes don't treat NULLs as equal, so composite uniqueness across nullable
`parent_id` needs to stay an app-level validation rule (as it effectively already is today, split across
three tables' individual unique constraints), not a single DB constraint.

---

## 6. The `destinations` table

**Decision: fold every live `destinations` row into `category_entities` (or discard as a confirmed dead
duplicate), then drop the table in Phase 5.** This reverses the original draft's "stays as-is" call — see
the note in §1. Migrations `2026_08_12_171000_migrate_vacation_destinations_to_countries.php` and
`...172000_migrate_destination_fish_data_to_countries.php` (already run) only copied content for *matched*
rows into the new-style tables; they left `destinations` itself untouched.

**Revised 2026-08-20 — Phase 0 audit ran against the live dev DB and corrects several of this section's
original numbers and mechanism claims.** See §12 for the full trace. Corrected picture:

| `destinations.type` | Rows | Status |
|---|---|---|
| `country` | 24 (all non-trashed) | 20/24 match `c_countries` by name. Of the other 4: **Finland** matches a `c_countries` row that is itself soft-deleted (`deleted_at` = 2026-08-12, the same day migration `171000` ran); **Italy** and **Poland** (×3 rows) have no `c_countries` counterpart at all, even including soft-deleted rows. Confirmed dead code path (see below). |
| `region` | 43 (all non-trashed) | 31/43 match `c_regions` by name. 12 don't: `Niedersachen`, `Rheinlandpfalz`, `Schleswig Holstein`, `Sachsen Anhalt`, `Mecklenburg Vorpommern`, `Smaland` (German names — likely stored under English/canonical names in `c_regions`, a naming mismatch rather than a missing region), `Catalonia`, `Istria`, `Northern Dalmatia`, `Dalmatia`, `Hvide-Sande` ×2. Not previously enumerated in this doc — needs the same per-row spot-check as the country rows before Phase 2. |
| `city` | 12 (all non-trashed) | 12/12 match `c_cities` by name. Clean. |
| `vacations` | 53 total — **39 non-trashed, 14 soft-deleted** | **Corrected from the original "47 matched / 6 unmatched" claim.** Only the 39 non-trashed rows were ever visible to migration `171000`/`172000` (`Destination` uses `SoftDeletes`, and both migrations query via `Destination::where('type', 'vacations')->get()`, which silently excludes trashed rows via Eloquent's default scope — the migrations never evaluated the other 14 at all, matched or not). The 39 non-trashed rows collapse to 21 distinct countries, each with a `languages` row (`type=geo_country`, `scope=vacations`) under the matching `c_countries` id — this part of the plan's claim is correct. |

**The 14 soft-deleted `vacations` rows are 7 destinations x en/de, not the 3 the plan originally named:**
**Deutschland, Brasilien, Kroatien, Montenegro, Griechenland, Malediven, Irland.** All 14 were soft-deleted
*before* the 2026-08-12 migrations ran — `deleted_at` ranges from 2025-01-18 (Deutschland/Brasilien/
Kroatien/Montenegro/Griechenland) through 2025-12-05 (Malediven/en) to 2026-07-14 (Irland, only ~5 weeks
before the migration and ~5 weeks before this audit) — so this reads as a series of deliberate content
removals spread over 18 months, not one accidental batch. None of the 7 country names exist in `c_countries`
in any form (checked including soft-deleted rows), and none of the 14 orphaned `destinations` rows have any
surviving `destination_faqs`/`destination_fish_charts`/`destination_fish_size_limits`/
`destination_fish_time_limits` rows or live-path `languages` content — the only trace left is a
`languages` row of type `destination_country` (a separate, earlier raw snapshot from migration
`2026_08_12_160000_backfill_scoped_geo_category_content.php`, keyed by the original `destinations.id`, not
the live `geo_country`/`scope=vacations` path the rest of the site actually reads). There is nothing left to
lose by not migrating them.

**Migration action:**
- **`country`/`region`/`city` rows (79 total):** Phase 0 confirms these are dead (no live write path — see
  below). Phase 2 does *not* migrate their content — Phase 5 drops them along with the rest, after the human
  spot-check on the `country`-type rows with no `c_countries` match (Finland/Italy/Poland) and now also the
  12 unmatched `region` rows found above, in case one is live data rather than stale seed data.
- **`vacations` rows, matched (39, not 47):** no new migration work needed — content already lives in
  `languages`/`destination_fish_*`/`destination_faqs` under the matching country. Phase 2's reconciliation
  report should account for these 39 as "already migrated."
- **`vacations` rows, soft-deleted (14, i.e. 7 destinations — not 3):** given they were already
  intentionally deleted, in most cases well over a year before this consolidation work started, and have no
  live-path content left to carry forward, **the audit's recommendation is to leave them deleted and let
  Phase 5 drop them unmigrated**, rather than resurrecting them into `category_entities`. This reverses the
  original plan's framing of this as "a genuine gap." Still worth a quick confirmation with the business
  before Phase 5 given §9.8's compliance-adjacent caution, but the evidence here is strong enough that it
  shouldn't block scheduling Phase 2.
- **On the migration mechanism:** the original plan's phrase "silently skipped" is not quite what `171000`
  does — when it finds no matching `c_countries` row for a *processed* destination, it actually **creates**
  a new `c_countries` row (see the migration source, §12). The 14 rows above were never skipped-after-
  matching; they were simply never in the migration's query result set at all, because they were already
  soft-deleted.

After the above, `destinations` (and the `Destination`, `DestinationFaq`, `DestinationFishChart`,
`DestinationFishSizeLimit`, `DestinationFishTimeLimit` models, plus the dead
`AdminCategoryVacationCountryController` / `AdminCategoryTripLocationController` /
`DestinationCategoryAdminService`) have no remaining callers or data and are removed in Phase 5.

**Revised 2026-08-20 (Phase 2 prep) — the "7 orphaned destinations" were not equally orphaned.** Re-checked
against live data before running the backfill: only **3 of the 7** (Montenegro, Griechenland, Irland) have
no live country page anywhere — no `c_countries` match, no live `vacations`-type `destinations` row. The
other 4 already have a live `c_countries` row today, matched independently of their trashed
`vacations`-type copies: **Deutschland** (`c_countries` id=5), **Kroatien** (id=4), **Brasilien** (id=16,
plus 2 live non-trashed `vacations` rows created 2026-07-14 — a fresh re-add, unrelated to the 2025-01-18
trashed pair), and **Malediven** (id=20, plus 2 live non-trashed `vacations` rows created 2026-07-15, same
pattern). "Leave dropped" (§9.8) is still the right call for all 7 — just for a more precise reason: 4 of
them are already covered by a separate live entity, and only 3 have genuinely nothing left.

**New finding — `c_countries` itself has a live, unresolved EN/DE duplicate pair.** Unlike Finland (where
the English-named duplicate was already soft-deleted), **id=25 "Island" and id=26 "Iceland" are both live**
(not trashed), same `countrycode=IS`, created in the same second (2026-08-18 09:27:47) — both produced by
migration `171000` independently `Country::create()`-ing an unmatched row for each locale's `vacations`-type
`destinations` entry, because that migration's matching was per-locale-named-row, not per real-world
country. (`destinations` rows are duplicated one-per-locale via a `language` column + shared `countrycode`
— e.g. "Italy"/cc=`it`/lang=`en` and "Italien"/cc=`it`/lang=`de` are the same country. Matching on
`countrycode` instead of `name` was the fix used everywhere below.) **Decision (2026-08-20): merge into one
`category_entities` row**, canonical on id=25 "Island" (matching the Finnland/Finland precedent of keeping
the German-named row), with id=26's translated content merged into the same entity's `de`/`en` `languages`
rows rather than kept as a second entity. Executed in Phase 2 — see §13.

---

## 7. What stays untouched

| Table / system | Why it's out of scope |
|---|---|
| `targets`, `methods` | Core catalog tables reused across the booking/guide domain (guide profiles, offer filters, etc.) — not category-page-specific. |
| `category_pages` | Targets/Methods' name/slug/thumbnail/`is_favorite` metadata table. Already generic (`type` + `source_id`), doesn't have the legacy/scoped split that causes the Country/Region/City complexity, and isn't a translation or FAQ table — low value, real risk to fold in. |

`destinations` (`Destination` model and its satellites) is **no longer** in this "stays untouched" list — see
§6. It is fully migrated and dropped by Phase 5.

---

## 8. Migration phases

**Phase 0 — Audit (research only, no schema/code changes) — done 2026-08-20, see §12**
- ~~Confirm the `destinations` breakdown in §6 against production data~~ Done — and corrected: real counts
  are 24 `country`/43 `region`/12 `city`/53 `vacations` (39 non-trashed + 14 soft-deleted, not "47 matched"),
  see §6. Region/city rows cross-checked against `c_regions`/`c_cities` by name; 12 `region` rows and the
  `country`-type Finland/Italy/Poland rows still need a human spot-check before Phase 2, per §6.
- ~~Confirm `AdminCategoryVacationCountryController`, `AdminCategoryTripLocationController`, and
  `DestinationCategoryAdminService` are genuinely unreachable~~ Done — a repo-wide grep (not just
  `admin.php`) found zero references to any of the three outside their own files: not in
  `routes/`, `app/Console/`, `app/Providers/`, or anywhere else. Confirmed dead.
- ~~Full read-side inventory~~ Done — see §12 for the full file list (grouped by area) feeding Phase 4's
  scope.
- ~~Baseline PHPUnit Feature-test coverage~~ Done — `Country`/Hub/Targets/Methods/destination-hub save flows
  already had coverage in `tests/Feature/Admin/Category/CategoryPagesAdminTest.php`; added
  `test_admin_can_save_scoped_region_content` and `test_admin_can_save_scoped_city_content` to close the gap
  for `AdminCategoryRegionController`/`AdminCategoryCityController` (neither had any prior test). All 17
  tests in that file pass. `DestinationCategoryAdminService` and the two unrouted controllers get no new
  tests — they're confirmed unreachable, so there's no live save flow to baseline.
- Full DB backup before any schema change — **still outstanding**, needs to happen before Phase 2 runs for
  real (Phase 1 here was verified safe via `migrate:rollback` instead, since it's pure additive DDL).

**Phase 1 — Additive schema (zero risk; old tables keep running untouched) — done 2026-08-20, see §12**
- ~~Create `category_entities`, `regulations`. Add the six new columns to `languages`.~~ Done. Migrated,
  rolled back to verify `down()`, and re-migrated on the dev DB. No application code changes made.

**Phase 2 — Backfill (idempotent Artisan command, `--dry-run` first)**
- `c_countries`/`c_regions`/`c_cities` → `category_entities`, preserving hierarchy. Keep a temporary
  `(old_table, old_id, new_id)` mapping table so the migration is traceable and safely re-runnable.
- `c_*_translations` → merge into `languages` per the precedence rule in §2.
- `destination_fish_charts` (availability grid, country/region/city rows) → `regulations.jan..dec`, ids
  remapped via the mapping table — this part is mechanical, same shape in and out.
- `destination_fish_size_limits`/`destination_fish_time_limits` → **worklist for manual re-verification**,
  not an automated backfill; see §4's migration-action note. `regulations` rows for these fields only get
  created as verification happens, and may lag the rest of Phase 2.
- `destination_faqs` (country/region/city rows) → `faqs`, per §3.
- `destinations` (per §6, revised): the audit recommends **not** resurrecting the 14 soft-deleted
  `vacations` rows (7 destinations: Deutschland, Brasilien, Kroatien, Montenegro, Griechenland, Malediven,
  Irland) — leave them deleted, pending the quick business confirmation noted in §6. If that confirmation
  instead says one or more should come back, create its `category_entities` (`type='country'`) row then and
  migrate its content the same way `171000`/`172000` did for the matched rows. The 39 already-matched
  `vacations` rows and the 79 dead `country`/`region`/`city` rows need no content migration — just note them
  as accounted-for in the reconciliation report below.
- Emit a row-count reconciliation report for the mechanical parts; fail loudly on any mismatch. Track
  size-limit/time-limit verification progress separately — it is not gated by the rest of the migration.

**Phase 3 — Write cutover**
- Repoint `AdminCategoryCountryController`, `AdminCategoryRegionController`, `AdminCategoryCityController`,
  and `DestinationCategoryAdminService`'s fish/FAQ sync methods to write only to the new tables. Add/extend
  Feature tests asserting the new-table writes (per this repo's testing convention).

**Phase 4 — Read cutover**
- Introduce a `CategoryEntity` model with type scopes (`::countries()`, `::regions()`, `::cities()`).
  Repoint every call site found in Phase 0's read audit. Consider thin backward-compatible accessor shims
  (e.g. keep a `Country::translations()`-shaped method backed by `languages`) so Blade views don't all need
  to change in the same PR.

**Phase 5 — Decommission**
- After a production verification window (≥1–2 deploy cycles), drop tables:
  - `c_countries`, `c_regions`, `c_cities`
  - `c_country_translations`, `c_region_translations`, `c_city_translations`
  - `destination_faqs`
  - `destinations` — once §6's migrate-or-confirm-dead work is done for every row (all 132 accounted for:
    47 already-migrated `vacations`, 3 newly-migrated `vacations`, 79 confirmed-dead `country`/`region`/
    `city`)
- Remove dead code:
  - Models: `Destination`, `DestinationFaq`, `DestinationFishChart`, `DestinationFishSizeLimit`,
    `DestinationFishTimeLimit`, plus the legacy `Country`/`Region`/`City` translation models once their
    tables are dropped
  - Controllers: `AdminCategoryVacationCountryController`, `AdminCategoryTripLocationController` (confirmed
    unrouted in Phase 0 — can be deleted as soon as that's confirmed, doesn't need to wait for the rest of
    Phase 5)
  - Service: `DestinationCategoryAdminService` (no live callers once its one caller above is deleted)
  - The `content_scope` branching in `AdminCategoryCountryController`/`RegionController`/`CityController`
    (only one content system will be left)
- `destination_fish_charts`/`destination_fish_size_limits`/`destination_fish_time_limits` drop only once
  their manual re-verification worklist (Phase 2) is fully cleared — do not drop on the same schedule as the
  mechanical tables above if verification is still in progress.

---

## 9. Risks & open questions

1. **Hierarchy query cost** — addressed by keeping `country_id`/`region_id` denormalized on
   `category_entities` (§5); don't "purify" this away without re-checking read-path cost.
2. **Composite uniqueness across nullable columns** — enforce per-parent slug uniqueness in app validation,
   not a single DB constraint (MySQL NULL ≠ NULL).
3. **Legacy/scoped merge ambiguity** — the §2 precedence rule (scoped wins) needs a human spot-check against
   real diverging rows before the real backfill runs.
4. **Shared ownership with the `Destination` (Vacations/Trips) system** is the single biggest unknown —
   Phase 0's audit result changes whether Phase 5 fully or only partially retires the 4 satellite tables.
5. **No FK today** on `destination_id` — the new schema adds a real FK from `regulations` and the migrated
   `faqs` rows to `category_entities.id` (and from `regulations.target_id` to `targets.id`), a genuine
   integrity improvement for the geo types. Targets/Methods/Hub still use string-typed `source_id` with no
   FK on `languages`/`faqs` — unavoidable, since they key off different base tables (`targets`, `methods`,
   and no table at all for the Hub singleton).
6. **`regulations` verification is a content/compliance effort, not a data-migration task** — its
   `source_url`/`source_name`/`verified_at`/`verified_by` fields have no equivalent in today's tables, so
   this part of the migration has no fixed end date the way the mechanical parts do. Needs an owner and a
   "how many country–species pairs must be verified before launch" number from the business before Phase 2
   can be scheduled with confidence (see also the staleness-threshold question below).
7. **Staleness threshold undefined** — §4's rendering rule suppresses a `regulations` block once
   `verified_at` is too old, but no number has been agreed yet. Needs a decision before Phase 4's read
   cutover ships the suppression logic.
8. **Revised 2026-08-20 — 14 `destinations` rows (7 destinations: Deutschland, Brasilien, Kroatien,
   Montenegro, Griechenland, Malediven, Irland — not the 3 originally listed here) have no `c_countries`
   match** — §6. All 14 are soft-deleted and were already trashed before the 2026-08-12 migrations ran, with
   no surviving satellite content, which is strong evidence they're intentionally-retired rather than a live
   gap. Still needs a quick business confirmation before Phase 5 drops them unmigrated, but the audit found
   nothing suggesting they should be resurrected.
9. **79 `country`/`region`/`city`-typed `destinations` rows look like dead pre-`c_*` leftovers** — §6.
   Phase 0 confirmed (2026-08-20, repo-wide grep) that nothing outside the two unrouted admin controllers and
   `DestinationCategoryAdminService` references them — safe for Phase 5 to drop unmigrated.
10. **`regulations`' required `source_url`/`source_name`/`verified_at`/`verified_by` columns block the
    "mechanical" half of the fish-chart migration too, not just size/time limits.** §4/§8 describe
    `destination_fish_charts` (the jan–dec availability grid) as mechanical — same shape in and out, unlike
    size/time limits which need human re-verification. But every `regulations` row shares those four NOT
    NULL columns regardless of which fields it carries, and legacy `destination_fish_charts` rows have no
    source citation at all. Inserting placeholder citation data to satisfy the constraint would misrepresent
    an unverified legacy grid as sourced/verified data, which cuts against this table's whole point (§4:
    "anglers acting on a wrong closed season or size limit commit an offence"). **Decided 2026-08-20: option
    (b) — no automated backfill for `destination_fish_charts` either.** It joins
    `destination_fish_size_limits`/`destination_fish_time_limits` on the same manual re-verification
    worklist (§4), for consistency: every field in a `regulations` row shares one citation, so there's no
    principled way to call the grid "more trustworthy" than the size/time limits sitting next to it in the
    same legacy tables. `destination_fish_charts`/`_size_limits`/`_time_limits` stay unmigrated until a human
    re-verifies each country/species pair against a real source.
11. **New 2026-08-20 — 96 `destination_faqs` rows have `destination_type` NULL or empty**, out of 498
    total. 402 of the remaining rows have a trustworthy `destination_type` and were migrated (§13). The 96
    orphaned rows' `destination_id` values collide across `c_countries`/`c_regions`/`c_cities`' own id
    sequences (e.g. id=4 exists as a valid row in all three tables), so which table they actually belong to
    can't be reconstructed algorithmically from data that survives today. Excluded from the automated
    backfill per the same "suppress rather than guess" principle as `regulations` — flagged as a manual
    worklist, not migrated. See §13 for the count and how to query them.

---

## 10. Testing & rollback

- Per this repo's convention: PHPUnit Feature tests per admin controller before/after; Unit tests for the
  new `CategoryEntity` model and any `CategoryPageContentService` changes.
- Phases 1–2 are purely additive — rollback is simply dropping the new tables, no data at risk.
- Phase 3 (write cutover) is the actual point of no easy return for *new* data — keep the old tables
  un-dropped through Phase 4 as a live rollback path (revert controller code; old data is still intact,
  just stops receiving new writes after Phase 3).
- Don't drop anything (Phase 5) until a full production verification window has passed with monitoring.

---

## 11. Decision log

| Question | Decision |
|---|---|
| Consolidation shape | Single unified table per structurally-similar cluster (`category_entities` for geo, `regulations` for legal/regulatory fish data) — not one universal mega-table for literally everything, and not six cleaned-up-but-still-separate per-type tables. |
| Include shared `destination_faqs`/`destination_fish_*` tables? | Yes — they're the biggest source of duplication and already shared by two systems; worth fixing once. |
| Include the `destinations` table itself? | **Revised 2026-08-20.** Yes — originally left "as-is" on the mistaken assumption the 2026-08-12 migrations had already retired it; they only copied content for matched rows and left the table populated. Now fully migrated-or-confirmed-dead and dropped in Phase 5. See §6. |
| Fold Targets/Methods' `category_pages` in too? | No — left alone; it isn't a source of the legacy/scoped split complexity and touching it adds risk for no payoff. |
| Translation storage | Extend the existing `languages` table (already the correct shape) rather than wide per-locale columns or a new per-entity translations table. |
| FAQ storage | Reuse the existing `faqs` table as-is; no schema change. |
| Regulatory data (seasons/closed seasons/size limits) storage | **Revised 2026-08-20**, after reviewing a separately-circulated migration spec: treat as verified legal reference data (source-cited, not scope/language-aware) in a new `regulations` table, replacing the originally-drafted `category_fish_metrics` table which kept the same per-entity/per-language shape as today's `destination_fish_*` tables. See §4. |
| `c_countries` id=25 "Island" / id=26 "Iceland" duplicate | **New 2026-08-20.** Merge into one `category_entities` row, canonical on id=25 (German name, matching the Finnland/Finland precedent); id=26's translated content merges into the same entity's `en` `languages` rows. Executed in Phase 2 — see §13. |

---

## 12. Phase 0 + Phase 1 execution log (2026-08-20)

Both phases were run against the live dev DB in this session. This section is the trace backing the
corrections made throughout §§6–9 above.

### Phase 0 audit trace

**Dead-controller confirmation.** `Grep` for `AdminCategoryVacationCountryController`,
`AdminCategoryTripLocationController`, and `DestinationCategoryAdminService` across the entire repo (not
just `routes/`) returned matches only inside the three classes' own files (plus this doc). No route,
console command, service provider, scheduled job, or other call site references any of them. Confirmed dead.

**`destinations` vs `c_countries`/`c_regions`/`c_cities`, by name (case-insensitive):**
- `country`: 24 rows, all non-trashed. 20 match; unmatched are Finland (matches a *soft-deleted*
  `c_countries` row, `deleted_at` 2026-08-12), Italy, Poland ×3 (no match at all, even trashed).
- `region`: 43 rows, all non-trashed. 31 match; 12 don't (listed in §6) — several are German names that may
  simply be stored differently in `c_regions`, not necessarily missing.
- `city`: 12 rows, all non-trashed, 12/12 match.
- `vacations`: 53 rows total (`Destination::withTrashed()->where('type','vacations')->count()`), but only
  39 are non-trashed (`Destination::where('type','vacations')->count()`). All 14 trashed rows have
  `deleted_at` timestamps predating the 2026-08-12 migrations (earliest 2025-01-18, latest 2026-07-14).
  Migrations `171000`/`172000` both query via the plain Eloquent `Destination::where('type', 'vacations')`,
  which — because `Destination` uses `SoftDeletes` — silently excludes trashed rows by default. That's the
  actual mechanism behind the "unmatched" rows, not a slug/name matching failure as originally described.
  `App\Models\Language::where('type','geo_country')->where('scope','vacations')->count()` = 39, confirming
  all 39 non-trashed rows (21 distinct countries) did get migrated to the live path.
- Read `database/migrations/2026_08_12_171000_migrate_vacation_destinations_to_countries.php` directly:
  when no `c_countries` match is found for a destination it *does* process, it calls `Country::create(...)`
  — it does not skip. Read `2026_08_12_172000_migrate_destination_fish_data_to_countries.php`: it does
  `continue` when no country is found, but only reaches that branch for rows `171000` already processed.
- Checked the 7 soft-deleted-destination country names (Germany, Brazil, Maldives, Croatia, Montenegro,
  Greece, Ireland) against `c_countries` including trashed rows — zero matches for all 7.
- Checked `destination_faqs`/`destination_fish_charts`/`destination_fish_size_limits`/
  `destination_fish_time_limits` for the 14 orphaned destination ids — zero rows in all four tables.
- Found a `languages` row of type `destination_country` for all 14 orphaned ids, sourced from a *different*,
  earlier migration (`2026_08_12_160000_backfill_scoped_geo_category_content.php`) that snapshotted raw
  `destinations` columns (title/introduction/etc.) keyed by the original `destinations.id` — a separate,
  non-live-path system from the `geo_country`/`scope=vacations` rows `171000` creates. This is the "leftover
  trace" mentioned in §6, not a reason to migrate.

**Read-side inventory** (outside `app/Http/Controllers/Admin/Category/*`), grepped for
`(Country|Region|City|CountryTranslation|RegionTranslation|CityTranslation|Destination*)::` and
`use App\Models\{those classes}`. Grouped by area, for Phase 4 scoping:
- **Vacation/Trip domain:** `VacationDestinationRepository`, `VacationCountryPageService`,
  `VacationPillarPageService`, `VacationCountryViewModel`, `VacationPillarIndexViewModel`,
  `TripLocationCatalogService`, `VacationsController`
- **Guidings:** `GuidingsController`, `Category\GuidingDestinationController`
- **Category page content:** `Category\DestinationCountryController`, `CategoryPageContentService`
- **Offers:** `OfferCatalogPageService`, `DestinationOfferScope`, `DestinationOfferGeoScope`,
  `HomepageMixedOfferSelector`
- **Homepage:** `HomepageLandingService`, `HomepageCountrySelector`
- **Sitemap:** `DestinationSitemapContributor`, `VacationSitemapContributor`
- **Misc:** `MonthlyHighlightController` (admin), `ImageCleanupService`, `ListingCountryFilter`
- **Console:** `MigrateDestinationsData`, `FixDestinationRelationships`, `FixDestinationsMigration` (one-off
  data-fix commands — check whether these are still needed or are themselves now dead before Phase 5)
- **Migrations that reference these models directly:** `171000`, `172000` (already covered above)
- Plus the corresponding test files for each of the above (not relisted here — same names under `tests/`)

**Baseline test coverage.** `tests/Feature/Admin/Category/CategoryPagesAdminTest.php` already covered the
Hub, Targets, Methods, Country, and Destination Hub save/autosave flows. Region and City had **no** Feature
test coverage at all. Added `test_admin_can_save_scoped_region_content` and
`test_admin_can_save_scoped_city_content` (mirroring the existing Country test, using
`CategoryPageScope::TOURS` since `CategoryPageScope::forDimension()` only allows `TOURS` for
`REGION`/`CITY`, unlike `COUNTRY` which also allows `GLOBAL`/`CAMPS`). All 17 tests in the file pass.
`DestinationCategoryAdminService` and the two unrouted controllers were not given new tests — there's no
live route to exercise, so a Feature test would only be testing dead code.

**Full DB backup** — not taken in this session (dev DB; Phase 1 changes were verified reversible via
`migrate:rollback` instead). Still called out as a prerequisite before Phase 2 runs for real on any DB that
matters.

### Phase 1 changes made

Three new migrations, all additive, no application code touched:
- `database/migrations/2026_08_20_140000_add_fish_metrics_to_languages_table.php` — adds
  `fish_avail_title`, `fish_avail_intro`, `size_limit_title`, `size_limit_intro`, `time_limit_title`,
  `time_limit_intro` (nullable) to `languages`, per §2.
- `database/migrations/2026_08_20_140100_create_category_entities_table.php` — creates `category_entities`
  per §5 (`type`, `parent_id`/`country_id`/`region_id` self-referencing FKs, `name`, `slug`, `countrycode`,
  `filters`, `thumbnail_path`, timestamps, soft deletes). No DB-level composite uniqueness constraint, per
  §9 risk #2 — that stays an app-level rule in Phase 3/4's controller code.
  `parent_id`/`country_id`/`region_id` all use `nullOnDelete()` rather than `cascadeOnDelete()`, since a
  parent-entity delete shouldn't silently cascade-delete its children.
- `database/migrations/2026_08_20_140200_create_regulations_table.php` — creates `regulations` per §4
  (`country_id`/`target_id` required FKs, `region_id` nullable FK, `jan`..`dec`, `closed_from`/`closed_to`,
  `min_size_cm`/`max_size_cm`/`bag_limit`, `licence_note`, and required `source_url`/`source_name`/
  `verified_at`/`verified_by`). `country_id`/`target_id` use `cascadeOnDelete()` since a `regulations` row
  has no meaning without its country/species; `region_id` uses `nullOnDelete()`.

All three migrated cleanly, were rolled back to verify `down()` drops/removes cleanly, then re-migrated to
leave the dev DB in the additive end state. No existing table, model, or controller was modified.

### What Phase 2+ needed before it started (resolved 2026-08-20, see §13)

Per §9:
- ~~Business confirmation on the 14 soft-deleted `vacations` `destinations` rows~~ Confirmed: leave dropped.
  Also corrected the premise — only 3 of the 7 named destinations are actually orphaned; see the §6 revision.
- ~~Spot-check of the `country`-type (Finland/Italy/Poland) and `region`-type unmatched rows~~ Done: all 12
  region mismatches and the Italy/Finland country mismatches are naming-convention issues (hyphenation,
  German-vs-English name), not gaps — resolved by matching on `countrycode` instead of `name`. Poland is a
  genuine gap but moot (§9.9: dead data, no live caller). Found a new, real issue in the process: `c_countries`
  id=25/26 (Island/Iceland) is a live duplicate — see §6 revision. Resolved: merge, canonical on id=25.
- `regulations` source-verification owner/bar (§9 risk #6) and staleness threshold (§9 risk #7) — **still
  open**, not needed to run the mechanical entity/translation/FAQ backfill, but blocks Phase 4's suppression
  logic and the `destination_fish_charts` migration (§9 risk #10, new).
- A real DB backup before Phase 2 runs on any DB that matters — still applies before running this against
  anything other than dev.

### What's still open after Phase 2's mechanical backfill (see §13)

- `destination_fish_charts` → `regulations` — **not migrated**, blocked on §9 risk #10 (NOT NULL citation
  columns vs. no-citation legacy data). Needs a schema or process decision before it can run.
- 96 `destination_faqs` rows with NULL/empty `destination_type` — **not migrated**, flagged as a manual
  triage worklist per §9 risk #11.
- `destination_fish_size_limits`/`destination_fish_time_limits` — per §4, always required manual
  re-verification, not an automated backfill; unaffected by this session's work.

---

## 13. Phase 2 mechanical backfill execution log (2026-08-20)

### Pre-flight corrections found while implementing

- **`destinations` per-locale duplication, and the right join key.** `destinations` has a `language` column
  and a `countrycode` column; every country is stored as two rows (one per locale, same `countrycode`) —
  e.g. "Italy"/`en`/`it` and "Italien"/`de`/`it` are the same country. The Phase 0 audit's name-based
  matching (§6) treated these as separate/unmatched in a few cases. Re-matching on `countrycode` instead of
  `name` resolved every "unmatched" case except Poland (genuine gap, moot — §9.9) — see the §6 revision above
  for the corrected country/region breakdown and the `c_countries` id=25/26 (Island/Iceland) duplicate this
  surfaced.
- **`destination_faqs.destination_id` keys into `c_countries`/`c_regions`/`c_cities`, not `destinations`.**
  Confirmed by reading `AdminCategoryCountryController` (`DestinationFaq::where('destination_id',
  $country->id)` where `$country` is a `c_countries` row) and by direct check: all 80 `destination_type =
  'country'` rows' `destination_id` values match `c_countries.id` (only 5/8 coincidentally also match
  `destinations.id`, i.e. that overlap is noise, not a real relationship). The similar naming between
  `destinations` and `destination_faqs` is a false cognate — they're keyed into different tables entirely.
- **96 of 498 `destination_faqs` rows have `destination_type` NULL or `''`.** Their `destination_id` values
  collide across `c_countries`/`c_regions`/`c_cities`' own id sequences (e.g. id=4 is a valid row in all
  three tables), so the correct parent table can't be reconstructed from `destination_id` alone. Excluded
  from the automated migration — see §9 risk #11.

### What ran

`app/Console/Commands/CategoryPages/BackfillCategoryEntitiesCommand.php`
(`category-pages:backfill-phase2 {--dry-run}`), backed by a new mapping table added in migration
`2026_08_20_150000_create_category_entity_migration_map_table.php` (`old_table`, `old_id`, `new_id`, unique
on `old_table`+`old_id`, per §8's "keep a temporary mapping table" instruction). The command:

1. Copies live (non-`deleted_at`) `c_countries`/`c_regions`/`c_cities` rows into `category_entities`,
   preserving `parent_id`/`country_id`/`region_id` via the mapping table built up as it goes. `c_countries`
   id=26 ("Iceland") is skipped and mapped to the same `category_entities` row created for id=25 ("Island").
2. Merges each `c_*_translations` row into a `languages` row on the new entity id, per the §2 precedence
   rule (non-empty scoped value wins, legacy fills empty fields only). Legacy content lands at `scope=global`
   for countries (the only scope legacy content could reasonably represent, since `global` is a valid
   `COUNTRY` scope) and `scope=tours` for regions/cities (their only scope, matching the precedent set by
   migration `2026_08_12_150000` for `faqs`).
3. Copies every other existing scoped `languages` row (e.g. `scope=vacations` on countries) onto the new
   entity id verbatim. Rows already written by step 2 for the same (entity, scope, locale) are left alone —
   step 2's precedence-aware merge is authoritative there.
4. Migrates `destination_faqs` rows with a trustworthy `destination_type` (`country`/`region`/`city`) into
   `faqs` (`page=geo_country|geo_region|geo_city`, `scope=tours` per §3, `source_id`=new entity id).
5. Reports the 96 NULL/empty-type `destination_faqs` rows as an excluded count rather than migrating or
   dropping them.

Nothing in `c_countries`/`c_regions`/`c_cities`/`c_*_translations`/`destination_faqs`/existing `languages`
rows was modified or deleted — old tables are read-only inputs, so the live app (still reading them until
Phase 3/4 cutover) is unaffected. Re-running the command is idempotent: the mapping table and
existing-row lookups prevent duplicate `category_entities`/`faqs` rows, and `languages` upserts converge to
the same values rather than duplicating.

### Results (dev DB, committed)

| Metric | Count |
|---|---|
| `category_entities` created — country | 23 (24 live `c_countries` rows, minus 1 merged into Iceland's canonical row) |
| `category_entities` created — region | 38 |
| `category_entities` created — city | 12 |
| `languages` rows written from legacy `*_translations` merge | 46 country + 72 region + 24 city |
| `languages` rows copied from existing scoped content | 59 (country only — region/city's only scope, `tours`, was already fully handled by the legacy merge step) |
| `faqs` migrated | 80 country + 253 region + 69 city = 402 (matches the Phase 0 audit's per-type `destination_faqs` counts exactly) |
| `destination_faqs` excluded (NULL/empty type, manual triage) | 96 |

Verified: `category_entities` id=534 ("Island") is the single merged Iceland entity, carrying `de`/`en`
`languages` rows for both `scope=global` (from `c_country_translations`) and `scope=vacations` (from the
pre-existing scoped content on both source ids) — confirms the merge produced one entity with complete
bilingual content rather than two competing rows or a content loss.

Test coverage: `tests/Feature/Console/CategoryPages/BackfillCategoryEntitiesCommandTest.php` (dry-run doesn't
persist, real run produces one entity per live geo row and merges Iceland, FAQ counts and orphan-exclusion
behavior, re-run idempotency). All 4 pass; the pre-existing 17 tests in `CategoryPagesAdminTest.php` still
pass unchanged (nothing in the live save/read path was touched).

### Left for a follow-up session

- Resolve §9 risk #10 (regulations' NOT NULL citation columns vs. legacy fish-chart data with no citation)
  and then migrate `destination_fish_charts`.
- Triage the 96 orphaned `destination_faqs` rows by hand (query: `destination_type IS NULL OR
  destination_type = ''`), or accept their content as lost if not worth the manual effort.
- `regulations` source-verification owner/bar and staleness threshold (§9 risks #6–7) — needed before Phase
  4, not before Phase 2.

Phase 3 (write cutover) ran this session despite the above still being open — none of them blocked it; see
§14.

---

## 14. Phase 3 write cutover execution log (2026-08-20)

### What changed

- **New model** `App\Models\CategoryEntity` (`app/Models/CategoryEntity.php`) — table `category_entities`,
  `countries()`/`regions()`/`cities()` type scopes, `parent()`/`children()`/`country()`/`region()` relations.
  This is now the only model `AdminCategoryCountryController`/`RegionController`/`CityController` read or
  write against for base entity data.
- **`AdminCategoryCountryController`/`AdminCategoryRegionController`/`AdminCategoryCityController`** rewritten
  end-to-end: base entity CRUD moved from `Country`/`Region`/`City` to `CategoryEntity`; the `content_scope`
  branch collapsed so every save — including `store()`, which previously always used the legacy non-scoped
  form — routes through `CategoryPageContentService::upsertEntity()`/`replaceFaqsForEntity()` into
  `languages`/`faqs`. Legacy-mode content lands at `scope=global` for Country, `scope=tours` for
  Region/City, matching the Phase 2 backfill's own precedent for merged legacy content. The
  `CountryTranslation`/`RegionTranslation`/`CityTranslation::updateOrCreate` calls and raw `DestinationFaq`
  create/update calls are gone entirely. `destroy()` now soft-deletes the `CategoryEntity` row and deletes
  its `languages`/`faqs` rows instead of the old `DestinationFaq` cleanup. The dead, TODO-marked
  `translateCountry()`/`translate()` placeholder-translation helpers (copied source text into a fake
  "translation" for the other locale) were dropped rather than ported.
- **Fish chart/size/time-limit fields deliberately untouched**: `fish_chart[]`/`fish_size_limit[]`/
  `fish_time_limit[]` rows still write to `DestinationFishChart`/`DestinationFishSizeLimit`/
  `DestinationFishTimeLimit` exactly as before, just keyed by the entity's id (a `category_entities.id` from
  now on for new saves). This is intentional — those three tables are still blocked on §9 risk #10.
- **`CategoryPageContentService`/`Language` extended, not just the controllers**: Phase 1 added six
  fish-section columns (`fish_avail_title`/`_intro`, `size_limit_title`/`_intro`, `time_limit_title`/`_intro`)
  to `languages`, but nothing actually wrote to them yet. `Language::$fillable` and
  `CategoryPageContentService::upsertEntity()` now carry these fields (only when the caller supplies them —
  `translateEntityScope()` doesn't, so translating a scope no longer nulls out its fish-section text).
  Added `CategoryPageContentService::replaceFaqsForEntity()`, extracted from `upsertEntity()`, because
  Country's FAQ scope (`tours`, per §3's uniform precedent for all migrated `destination_faqs`) doesn't match
  its content scope (`global`) — the two needed to be written independently rather than tied together.
- **Confirmed-dead code removed**: `AdminCategoryVacationCountryController`,
  `AdminCategoryTripLocationController`, `App\Services\Destination\DestinationCategoryAdminService`, and
  `resources/views/admin/pages/category/vacations-form.blade.php` (only referenced by the two dead
  controllers) — zero routes or other references to any of them, reconfirmed by repo-wide grep this session.
- **Regulations/fish-chart decision closed out** (§9 risk #10): no automated migration for
  `destination_fish_charts` either — it joins `_size_limits`/`_time_limits` on the manual re-verification
  worklist, since every `regulations` row shares the same NOT NULL citation columns regardless of which
  fields it carries.

### Bug the browser smoke test caught

`resources/views/admin/pages/category/{country,region,city}.blade.php`'s index listing views read
`$row->translations->count()`/`$row->translations` (a `hasMany` relation `Country`/`Region`/`City` have but
`CategoryEntity` doesn't) to render DE/EN language-flag icons — this 500'd immediately on
`/admin/category/country` once the controllers switched to `CategoryEntity`. Fixed by adding
`HandlesScopedCategoryContent::languagesByEntity()` (one query grouping `languages` rows by `source_id`) and
passing its result into all three index views instead of the old relation. Caught by clicking through the
running dev site (`cag.local`) rather than relying on the test suite alone — none of the existing or new
PHPUnit tests exercised the index views closely enough to catch this, since they assert on `update`/`store`
responses, not `index` page rendering. Verified after the fix: country/region/city listing pages and a
country edit-and-save round-trip (via the real "Bereich speichern" form submit) all work correctly against
the dev DB.

### Test coverage

`tests/Feature/Admin/Category/CategoryPagesAdminTest.php`: the 5 existing Country/Region/City tests updated
to source their fixture row from `CategoryEntity` instead of the old models (their ids no longer overlap —
`category_entities` has fresh auto-increment ids, not a 1:1 copy of `c_countries.id`/etc.). Added 3 new
tests: legacy-form country creation (asserts `languages`/`faqs` written, and that `c_countries`/
`c_country_translations` receive nothing), legacy-form region creation (same, plus parent `country_id`
linkage), and `destroy()` cleanup (soft-delete + `languages`/`faqs` removal). All 20 tests in the file pass.

Ran the **full** suite twice (once before, once after the Blade fix) to check for regressions outside the
Category area, since `CategoryPageContentService`/`Language` are shared by ~15 other files per §14's handoff
list below: both runs show the identical **10 failed, 2 skipped, 427 passed** — same test names both times.
Traced a sample (`GuidingsLandingTest`, `CategoryIndexTest`) — both fail on code this session never touched
(`GuidingsLandingService`/`HomepageCountrySelector` Mockery setup, a `Target`-related Blade null-property
read), and `GuidingsLandingService` was added in the branch's own most recent commit, before this session
started. All 10 are pre-existing failures, not regressions from this work.

### Phase 4 handoff (why it wasn't attempted this session)

Explored the full read-side surface before deciding not to touch it (see the plan-mode research this
session, not separately logged here in detail — the key findings):

- ~15 files read `Country`/`Region`/`City` directly, most in hot, revenue-critical live paths:
  `VacationDestinationRepository`, `HomepageCountrySelector`, `GuidingsController` (3 separate call sites),
  `GuidingDestinationController`, `DestinationCountryController`, `CategoryPageContentService`'s
  `applyScopedContentToModel()` (a central `instanceof Country||Region||City` branch), `VacationCountryPageService`,
  `VacationPillarPageService`, `TripLocationCatalogService`, `OfferCatalogPageService`,
  `HomepageLandingService`, `HomepageMixedOfferSelector`, `DestinationSitemapContributor`,
  `VacationSitemapContributor`, `ImageCleanupService`.
- `CampListingRepository`/`TripListingRepository` were on the original suspect list but have **zero**
  coupling to these models — they match a denormalized string `country` column, not a FK. No change needed
  there.
- **Concrete hazard found**: `App\Http\Controllers\Admin\MonthlyHighlightController` stores raw `country_id`
  **integers by value** in a `monthly_highlights.items` JSON column (also referenced via
  `Rule::exists('c_countries', 'id')` in `StoreMonthlyHighlightRequest`/`UpdateMonthlyHighlightRequest`).
  `category_entities` ids are fresh auto-increments, not a 1:1 copy of `c_countries.id` (confirmed — e.g. the
  merged Iceland entity is id 534). A naive read cutover would silently point every existing Monthly
  Highlight card at the wrong (or a nonexistent) entity. This needs its own remap pass through
  `category_entity_migration_map` before anything touches `MonthlyHighlightController`.
- Given the blast radius (live customer-facing pages across two locales/domains I can't fully click-test in
  one pass), the by-value id hazard above, and that this plan document itself already calls for Phase 4 to
  ship incrementally across multiple PRs and Phase 5 to wait for "a production verification window (≥1–2
  deploy cycles)" — a real-world constraint, not a discretionary one — Phase 4/5 were scoped out of this
  session rather than attempted in one unreviewable pass. This section is the handoff: the file list above
  plus the `MonthlyHighlightController` hazard is what a future Phase 4 session needs to start from without
  re-auditing.

### MonthlyHighlightController hazard — remap tooling prepped (2026-08-21)

Built and tested `category-pages:remap-monthly-highlight-countries` (`app/Console/Commands/CategoryPages/
RemapMonthlyHighlightCountryIdsCommand.php`), the remap pass this section's last paragraph calls for. It
rewrites `monthly_highlights.items`' `country_id` (pair items) / `id` (legacy country items) from
`c_countries.id` to `category_entities.id` via `category_entity_migration_map`, idempotently (an id already
present in the map's `new_id` column is treated as already-remapped and left alone), with `--dry-run`
support matching `BackfillCategoryEntitiesCommand`'s convention. Tested in
`tests/Feature/Console/CategoryPages/RemapMonthlyHighlightCountryIdsCommandTest.php` (pair/legacy-country
remap, target items left untouched, unmapped ids left unchanged without crashing, dry-run, re-run
idempotency) — all 6 pass.

**Deliberately not run against the dev DB, and `MonthlyHighlightController`/`StoreMonthlyHighlightRequest`/
`UpdateMonthlyHighlightRequest`/`HomepageLandingService` are deliberately still untouched, still reading/
writing/validating against `Country`/`c_countries`.** Running the remap now, or switching any one of those
four files alone, would break live admin validation or homepage season rendering today, since
`c_countries` is still the only table those files read — cutting them over is a genuine Phase 4 read
cutover, not a standalone hazard fix. A closer look also found the cutover isn't self-contained the way it
first looked: `HomepageLandingService::offersCatalogUrl()` builds card URLs via
`DestinationOfferScope::mergeIntoRequest(array, Country $country, ...)`, which is itself one of the ~15
files on the Phase 4 list above — so switching `HomepageLandingService`'s country lookups would force
touching `DestinationOfferScope` in the same change, not a self-contained slice. The remap command above
closes the gap between "this hazard is identified" and "there's a tested mechanism to fix it": whoever
picks up the real Phase 4 cutover for these four files runs this command as the first step of that PR (per
this section's original wording), rather than needing to write and test it under deadline pressure then.

---

## 15. Phase 4 read cutover execution log (2026-08-21)

Researched via three parallel Explore agents (Phase 3 controllers + fish-data id-keying, vacation/trip
domain, guidings/offers/homepage/sitemap/MonthlyHighlight) plus follow-up reads of `routes/`, every Blade
view touching a geo entity, `HomepageCountrySelector`, and `AdminCategoryHubController` (2 files not on
§14's original list). All ~17 read-path files repointed from `Country`/`Region`/`City` to `CategoryEntity`
in one session, after user confirmation to attempt the full cutover rather than a smaller slice.

### §0 — Pre-existing regression found and fixed first

Phase 3's write cutover left `DestinationFishChart`/`DestinationFishSizeLimit`/`DestinationFishTimeLimit`
rows keyed by the *old* `c_countries`/`c_regions`/`c_cities` id (legacy data, still read by the then-untouched
public pages) while admin saves since Phase 3 started writing fresh rows keyed by the *new*
`category_entities` id — verified empirically (Deutschland: old id 5 has 19 live chart rows, new id 516 has
0). Effect: every admin edit page opened since Phase 3 shipped showed an empty fish-chart/size/time-limit
section regardless of real data, and any save created rows invisible to the live public page (wrong id, and
`destination_type` was never set by this code, before or after Phase 3 — a separate, pre-existing gap left
as-is per the "don't guess, don't backfill unverifiable data" principle already established for this table
in §9 risk #10).

Fix: added `app/Models/CategoryEntityMigrationMap.php` (a real model for the previously-DB::table-only
`category_entity_migration_map`), and `CategoryEntity::legacyId(): ?int` (reverse lookup: new id → old
`c_countries`/`c_regions`/`c_cities` id, `null` for entities created fresh after Phase 3, e.g. via
`store()`). All three admin controllers' fish-chart/size/time-limit `edit()`/`update()`/`destroy()` blocks
now resolve `$entity->legacyId() ?? $entity->id` instead of the bare new id, and set `destination_type`
explicitly on create (stopping *new* saves from adding to the NULL-type pile, without touching historical
rows). Covered by two new tests in `CategoryPagesAdminTest.php` (legacy data visible on `edit()` via the
migration map; a save round-trip keeps it visible).

### CategoryEntity CMS-overlay design

`CategoryPageContentService::applyScopedContentToModel()`'s generic (non-`Country`/`Region`/`City`) branch
already worked for `CategoryEntity` via plain `setAttribute()` calls — but a direct Blade audit
(`resources/views/pages/vacations/country.blade.php`) found it calls `$destination->scopedCmsValue(...)`
directly, a method only the legacy models' `OverlaysScopedCategoryContent` trait provided. Rather than leave
`CategoryEntity` on the generic branch, added `CategoryEntity::overlayScopedTranslation(?Language $content)`
and `::scopedCmsValue(string $field)` (mirroring the trait's contract but writing straight into the model's
own attributes, since `CategoryEntity` has no legacy per-locale translation table to shadow) and added it to
`applyScopedContentToModel()`'s `instanceof` branch alongside `Country`/`Region`/`City`. The overlay sets all
11 content fields (`title`/`sub_title`/`introduction`/`content`/`faq_title` plus the six fish-section
fields) so both plain attribute reads (`$row_data->fish_avail_title`, used by several Blade views) and
`scopedCmsValue()` calls resolve correctly. Added `CategoryEntity::getTitleAttribute()` for the same
name-fallback-when-blank behavior `Country::getTitleAttribute()` already had.

Also added `CategoryEntity::fish_charts()`/`fish_size_limits()`/`fish_time_limits()` — snake_case to match
the legacy relation names call sites already used, but **plain methods returning a `Collection` directly**
(not Eloquent relations, since the underlying tables aren't migrated yet — see §9 risk #10), each resolving
`legacyId() ?? $this->id` internally. This means every call site had to change from property-style access
(`$row_data->fish_charts`, which Eloquent would reject with "must return a relationship instance" since the
method doesn't return a `Relation`) to method-call syntax (`$row_data->fish_charts()`) — caught by a
repo-wide grep for the property-style pattern across both `app/` and `resources/views/` before considering
the cutover done, not just the three call sites the initial research had flagged.

### Files changed, by area

**Mechanical/zero-relation-risk** (model-class swap only — `slug`/`name`/`countrycode`/`filters` are
portable as-is): `DestinationSitemapContributor`, `VacationSitemapContributor`, `DestinationOfferScope`,
`DestinationOfferGeoScope`, `ListingCountryFilter`, `HomepageMixedOfferSelector`, `OfferCatalogPageService`
(type hints only), `ImageCleanupService` (collapsed the `region`/`country`/`city`/`destination` model-config
entries into `CategoryEntity::class` + a `type` scope, dropped the redundant `destination` alias),
`AdminCategoryHubController` (found via follow-up read, not on the original list).

**Guidings + category controllers**: `DestinationCountryController`, `GuidingDestinationController` (the one
caller passing `Region`/`City` as well as `Country` into `applyScopedContentToModel()`/
`OfferCatalogPageService`; its `country_id`/`region_id` FK-traversal queries carried over unchanged since
`CategoryEntity` keeps the same denormalized columns per §5), `GuidingsController` (3 by-id lookups fed by
`destination_id` request params originating from already-cut-over pages, so the id space stays consistent
end-to-end).

**Vacation/Trip domain** (largest, most interdependent — repository → services → view models → controllers,
cut over together in one pass since they share model types across function boundaries):
`VacationDestinationRepository` (dropped eager-loads of relations `CategoryEntity` doesn't have; the
synthetic no-id `new Country([...])` fallback in `resolveCountryPage()` became `new CategoryEntity(['type' =>
'country', ...])`; `hubGridRow()`'s direct `$country->translations->firstWhere(...)` read replaced with a
`CategoryPageContentService::findForEntity()` call at `scope=vacations`, since the repository had no other
way to read `languages` content), `VacationCountryPageService`, `VacationPillarPageService`,
`VacationCountryViewModel`, `VacationPillarIndexViewModel` (both just property-type swaps — their
`cmsField()`/`scopedCmsValue()` usage already worked once `CategoryEntity` gained the method),
`TripLocationCatalogService` (added the `if ($row_data->id)` guard before `applyScopedContentToModel()` that
the two Vacation services already had, for consistency — harmless either way here since `abort(404)` already
ran, but flagged as a divergent pattern by the research phase), `VacationsController` (public — `index()`,
`category()`, and `show()`'s `vacation_destination_id` session round-trip, which required no extra remap
logic beyond the model swap: the id flowing through `VacationPillarPageService::campListings()` →
`CampCardPresenter::presentListRow()` → the four Blade partials that write it into the session → this
controller's `CategoryEntity::countries()->find($destinationId)` read was already internally consistent once
every link in that chain was cut over in the same change), and `VacationCountryController` (found via the
Blade audit, not on the original file list — no direct model coupling itself, but renders
`pages/vacations/country.blade.php`, the view that drove the `scopedCmsValue()` design decision above).

**Homepage + Monthly Highlights** (the two hazards the Phase 3 session flagged and pre-built tooling for):
`HomepageCountrySelector` (heaviest rewrite — `hasLocaleTranslation()`'s legacy `$country->translations`
read replaced with a `CategoryPageContentService::findForEntity()` check across every content scope a
Country page can carry, since this method is only a sort tiebreaker between same-ISO duplicate rows, not a
hard filter; bumped both `Cache::remember` key versions — `homepage_featured_countries_v7`→`v8`,
`homepage_country_total_count_v2`→`v3` — since the underlying id space changed), `HomepageLandingService`
(`resolveHighlightCards()`'s `Country::whereIn('id', $countryIds)` swap, `offersCatalogUrl()`'s type hint),
`MonthlyHighlightController` + `StoreMonthlyHighlightRequest` + `UpdateMonthlyHighlightRequest` (dropdown
query swap; `Rule::exists('c_countries', 'id')` → `Rule::exists('category_entities', 'id')->where('type',
'country')`). Ran `category-pages:remap-monthly-highlight-countries --dry-run`: **0 rows to remap** — the dev
DB's `monthly_highlights` table is empty, so the real run (blocked by this session's action-classifier as a
data-mutating command run without `--dry-run`) would be a genuine no-op; left un-run rather than forcing it,
since there's nothing for it to do. Whoever deploys this to an environment with real Monthly Highlight data
must run it for real as part of that deploy, per the command's own docblock.

### Verification

`php -l` on all ~30 touched files (clean). Repo-wide grep for `App\Models\(Country|Region|City)` after the
cutover: zero remaining matches in any file this phase was responsible for — the only survivors are
`CategoryPageContentService.php` (keeps `Country`/`Region`/`City` imports for its now-caller-less
`legacyCountryLanguage()`/`legacyRegionLanguage()`/`legacyCityLanguage()` methods and the `instanceof` branch
in `applyScopedContentToModel()`, left in place as dead-but-harmless rather than deleted mid-Phase-4 — a
Phase 5 cleanup candidate) and the three confirmed-dead one-off migration commands
(`MigrateDestinationsData`, `FixDestinationRelationships`, `FixDestinationsMigration`, superseded
previous-generation tooling, out of scope per §12). Full `php artisan test` run completed 2026-08-21 (next session): **10 failed, 1 risky, 2 skipped, 434
passed** (2459 assertions, 1242s). Failure count and test names are identical to the pre-Phase-4 baseline (10
failed, 2 skipped, 427 passed) — `GuidingsLandingTest` (×2), `CategoryIndexTest` (×2),
`MapMarkerCollectionItemKeyTest`, `MapMarkerCollectionPriceLabelTest` (×2), `TargetsPageRedirectTest`,
`CleanUrlParametersTest` (×2) — none touch `Country`/`Region`/`City`/`CategoryEntity`. The +7 passed count
matches the tests added across Phases 2–4. One new "risky" test appeared vs. the baseline (PHPUnit's default
output doesn't name it, only counts it) — not a failure, not yet identified. **Conclusion: no regressions
from Phase 4.**

### What's left

- ~~Full click-through verification on the dev site (`cag.local`) across `en`/`de`~~ Done 2026-08-23 — see §16.
  No regressions found; Phase 4 is browser-verified, not just code/test-verified.
- Phase 5 (drop `c_countries`/`c_regions`/`c_cities`/`c_*_translations`/`destination_faqs`/`destinations` and
  remove the now-dead `Country`/`Region`/`City`/`*Translation` models) — still gated on a production
  verification window per §10, not started this session.
- `destination_fish_charts`/`_size_limits`/`_time_limits` → `regulations` migration and the 96 orphaned-FAQ
  manual triage — unaffected by Phase 4, still open per §9 risks #10–11.
- If real Monthly Highlight data exists in any environment this branch deploys to, run
  `category-pages:remap-monthly-highlight-countries` for real (not `--dry-run`) as part of that deploy.

---

## 16. Click-through verification session (2026-08-23)

Browser pass against the running dev site (`cag.local`, via Claude in Chrome), the item Phase 4's session
explicitly deferred. Logged in as an admin employee; exercised both public and admin surfaces across `de`
(default local locale) and `en`.

**Public pages** — all rendered real data with zero console errors:
- Homepage (`de`): hero, `HomepageCountrySelector` "Wohin Angler gerade reisen" row populated with real
  country cards (Argentinien, Brasilien, Costa Rica, Deutschland, Dänemark, ...).
- Country page `/destination/deutschland` (`de`): CMS title/intro (global scope), tour/camp/trip listings,
  and — the exact §0 regression area — the fish-availability chart rendered full real data (19 species x 12
  months), confirming `CategoryEntity::fish_charts()`'s `legacyId()` resolution works live, not just in
  `edit()`.
- Country page `/destination/kroatien` (`en`, via the header language switcher): CMS title/intro/tour
  listings all in English, confirming the `en`/`de` `languages` overlay works for `CategoryEntity` same as it
  did for `Country`.
- Guidings destination pages `/guidings/kroatien` and `/guidings/kroatien/dalmatien` (`en`): country-level
  region/city grid and region-level CMS content (breadcrumb Homepage > Fishing Tours > Kroatien > Dalmatien)
  both rendered correctly — this is `GuidingDestinationController`, the one call site passing `Region`/`City`
  as well as `Country` into `applyScopedContentToModel()`.
- Offers catalog `/offers?country=Germany` (`en`): `DestinationOfferScope`/`DestinationOfferGeoScope` resolved
  the name-based `country` filter correctly (123 results, page title "All Offers in Germany"). Note: this
  filter takes a country *name* string (matched against `guidings.country`/`country_iso`), not a
  `category_entities` id — confirmed by reading `OfferCatalogPageService::queryTours()`; an id-based guess
  (`?country=516`) returns 0 results by design, not a bug.
- Vacations pillar `/vacations` and country page `/vacations/niederlande` (`en`): country cards with trip/camp
  counts, then the Netherlands page's trip/camp listings, map, and filters all rendered — covers
  `VacationPillarPageService`, `VacationCountryPageService`, `VacationCountryViewModel`,
  `VacationDestinationRepository`, `TripLocationCatalogService`.

**Admin pages** — logged in via `admin/logins` (seeded `EmployeeSeeder` credentials didn't match this DB; used
a working account the user supplied):
- `/admin/category/country`, `/region`, `/city` listings: all render with correct DE/EN language-flag icons
  (the `languagesByEntity()` fix from the Phase 3 session) and correct parent-country/region names via the
  denormalized `country_id`/`region_id` columns.
- `/admin/category/country/516/edit` (Deutschland): base fields, all five scope tabs, and — again the §0
  regression area — fish-availability/size-limit/time-limit sections all populated with the legacy data via
  `legacyId()`. Ran a real save (`Bereich speichern` on the Global scope): "Country Successfully Updated!",
  no errors, confirming the full `update()` write path still works post-cutover.
- `/admin/monthly-highlights`: empty listing (matches the doc's dry-run finding that dev's table has no rows).
  Opened `/admin/monthly-highlights/create`: the country dropdown populates from `category_entities`
  correctly (`StoreMonthlyHighlightRequest`'s `Rule::exists('category_entities', 'id')->where('type',
  'country')` cutover). Not submitted, to avoid leaving test data.

**Not covered this pass**: sitemap XML (`/sitemap.xml` serves a pre-generated static file pointing at
production URLs, not something a browser click-through can exercise — would need
`php artisan sitemap:generate` run directly, which wasn't run); a genuine production/staging environment
(everything above is dev-DB only). One unrelated, pre-existing observation: the homepage's hero marketing
copy ("Finde dein nächstes Angelabenteuer...") stayed German after switching to `en` — a static Blade/`__()`
translation gap, not a `CategoryEntity`/`languages` content issue, and outside this migration's scope.

**Conclusion**: no functional regressions found anywhere in the Phase 3/4 cutover surface. Phase 4 can now be
considered browser-verified in addition to code/test-verified.

---

## 17. Orphaned-FAQ resolution and Phase 5 decommission (2026-08-23)

Same session as §16, continued after the user asked to (a) commit this branch's work, (b) resolve the 96
orphaned `destination_faqs` rows (§9 risk #11), and (c) run Phase 5 — explicitly against the caution above
about production sequencing, which the user accepted for the dev DB.

### §9 risk #11 resolved: the 96 orphaned FAQs were not actually unresolvable

§13's Phase 2 write-up concluded the 96 `destination_faqs` rows with NULL/empty `destination_type` couldn't
be matched to a parent because their `destination_id` collides across `c_countries`/`c_regions`/`c_cities`'
own id sequences. That conclusion was correct as far as it went, but incomplete: it never checked the
**legacy `destinations` table** (§6) as a candidate parent, because `destination_faqs`' 402 *typed* rows are
confirmed keyed against `c_countries`/`c_regions`/`c_cities` (§13), and the similar naming was flagged as a
"false cognate."

Direct inspection of the 96 orphaned rows found only **11 distinct `destination_id` values**, and every
FAQ's question text names its destination in plain language (e.g. `destination_id=4`: "Wo kann ich in
**Schweden** am besten angeln?"). Checking those 11 ids against `destinations` (`withTrashed()`) found an
exact match every time — e.g. id=4 is `destinations` row `{type: country, name: "Schweden"}`. So the
orphaned rows are keyed against `destinations.id`, not `c_countries.id` — a **different legacy id space**
than the 402 typed rows, which is exactly why matching them against `c_countries`/etc. produced nonsense
collisions. Resolving each of the 11 names against `category_entities` (case-insensitive, typed) found a
unique match for all 11 — including the three `destinations.type = 'vacations'` groups (Brasilien, Malediven,
Kroatien), mapped onto `category_entities.type = 'country'` per the same convention migrations
`171000`/`172000` used.

A short-lived command, `category-pages:migrate-orphaned-faqs` (`App\Console\Commands\CategoryPages\
MigrateOrphanedDestinationFaqsCommand`, idempotent, `--dry-run` supported, covered by 5 Feature tests using
self-contained fixtures), implemented this resolution and was run for real:

| Metric | Count |
|---|---|
| Migrated — country (incl. `vacations`-type destinations) | 56 |
| Migrated — city | 27 |
| Migrated — region | 13 |
| **Total** | **96 / 96 — zero unresolved** |

All 96 rows now have a corresponding `faqs` row (`scope = tours`, matching §3's precedent for every other
migrated `destination_faqs` row). The command and its test were **deleted** after this one-time run
completed, per the decision below.

### Pre-Phase-5 backup

Before running the drop migration, `mysqldump` exported the seven tables about to be dropped
(`c_countries`, `c_regions`, `c_cities`, `c_country_translations`, `c_region_translations`,
`c_city_translations`, `destinations`) to a local SQL file. This is a dev-DB safety net only — not a
substitute for a real backup before ever running this migration against production.

### Phase 5 migration

`database/migrations/2026_08_21_160000_drop_legacy_category_geo_tables.php` already existed, untracked, on
this branch — prepared by an earlier session but never run (confirmed via `migrate:status`: `Pending`). Its
own docblock independently documents the same scope decision this doc already called for: drop
`c_countries`/`c_regions`/`c_cities`/`c_*_translations`/`destinations`, and deliberately leave
`destination_faqs` and the three `destination_fish_*` tables alone (§9 risk #10 unresolved — not attempted
this session; the user did not ask for the regulatory-sourcing work). `down()` deliberately throws rather than
attempting a schema-only reconstruction, per §10's own framing that Phase 5 rollback means "restore from a
verified backup," not a migration rollback.

Ran via `php artisan migrate`. No foreign-key errors — a check against `information_schema.KEY_COLUMN_USAGE`
before running confirmed the only live FKs among the dropped tables are internal to the set being dropped
together (`c_regions`/`c_cities` → `c_countries`, the `*_translations` tables → their parent), and nothing
external references `destinations` anymore (its `2024`-era FK constraints to `destination_fish_size_limits`/
etc. no longer exist in the live schema — consistent with §9 risk #5's "no FK today on `destination_id`").

### Dead code removed

- **Models deleted** (zero remaining references anywhere in `app/`, `database/migrations/`, or `tests/` after
  the commands below were removed): `Region`, `City`, `RegionTranslation`, `CityTranslation`.
- **Models kept, deliberately** despite having no live callers: `Country`, `CountryTranslation`, `Destination`.
  Migrations `2026_08_12_171000_migrate_vacation_destinations_to_countries.php` and
  `..._172000_migrate_destination_fish_data_to_countries.php` (already-`Ran`, historical) import and call
  these classes directly (`Country::create()`, `Destination::where(...)`); deleting them would break
  `php artisan migrate:fresh` for anyone rebuilding the schema from an empty database, even though the
  classes are otherwise fully dead in live app code. `Country::regions()`/`::cities()` (the only methods that
  referenced the now-deleted `Region`/`City` classes) were removed since nothing called them.
- **Also kept**: `DestinationFaq`, `DestinationFishChart`, `DestinationFishSizeLimit`, `DestinationFishTimeLimit`
  — at the time this paragraph was first written, believed to still be actively used by the admin controllers'
  fish-data `edit()`/`update()` flow (§15 §0) and by their own creation migrations'
  `foreignIdFor(Destination::class)` calls. **Correction, same day (§18): `DestinationFaq` specifically turned
  out to have zero live callers** — only `DestinationFishChart`/`DestinationFishSizeLimit`/
  `DestinationFishTimeLimit` are genuinely still read/written by the admin controllers. `DestinationFaq`'s
  table (`destination_faqs`) was dropped in §18; the model class itself is still kept for the same
  `migrate:fresh` reason as `Country`/`CountryTranslation`/`Destination` above.
- **One-off commands deleted**: `MigrateDestinationsData`, `FixDestinationRelationships`,
  `FixDestinationsMigration` (confirmed dead since Phase 0 — §12 — and the only remaining callers of
  `Region`/`City`, so deleting them is what made those two models safe to delete). `BackfillCategoryEntitiesCommand`
  (§13) and `MigrateOrphanedDestinationFaqsCommand` (above), plus their tests, were also deleted: both did a
  real one-time job that's now complete, both query tables Phase 5 just dropped (`c_countries`/etc. and
  `destinations` respectively), and neither is needed by any migration's `up()`/`down()` — unlike the models
  above, nothing requires them to keep existing. `RemapMonthlyHighlightCountryIdsCommand` (§14) was **not**
  touched — it only queries `category_entity_migration_map` and `monthly_highlights`, both still live, so it
  remains fully runnable.
- **`CategoryPageContentService`** — removed the now-fully-dead `instanceof Country || Region || City` branch
  (collapsed to just `instanceof CategoryEntity`) and the caller-less `legacyCountryLanguage()`/
  `legacyRegionLanguage()`/`legacyCityLanguage()`/`languageFromGeoTranslation()` methods flagged as a Phase 5
  cleanup candidate in §15's verification notes, plus their now-unused `Country`/`Region`/`City`/
  `*Translation` imports.
- **Tests updated**: `CategoryPagesAdminTest.php`'s two legacy-form creation tests dropped their
  `assertDatabaseMissing('c_countries', ...)`/`assertDatabaseMissing('c_country_translations', ...)`/
  `assertDatabaseMissing('c_regions', ...)` assertions — these proved the write cutover didn't leak into
  legacy tables (meaningful mid-Phase-3/4), but now just query tables that no longer exist. Every other test
  in that file, and every other command test in `tests/Feature/Console/CategoryPages/`, was checked and needed
  no changes (see command-by-command breakdown above).

### Verification

Public pages (`/destination/deutschland` including its fish-availability chart, admin
`/admin/category/country/516/edit` including fish-chart/size/time-limit sections) reloaded via browser after
the migration ran: identical content, zero console errors — both paths read fish data via
`CategoryEntity::legacyId()` through `category_entity_migration_map`, which the drop migration never touched.
`php artisan test` (full suite): **10 failed, 1 risky, 2 skipped, 430 passed** — same failure categories as
the Phase 4 baseline (434 passed); the 4-test delta is exactly `BackfillCategoryEntitiesCommandTest`'s tests,
removed alongside the command. No regressions. Committed as two commits: `0b1c1f9d` (Phase 1 schema) and
`7c4b5fc9` (Phases 2–5).

### What's left after this session

- `destination_fish_charts`/`_size_limits`/`_time_limits` → `regulations`, and the manual re-verification
  these three tables need before they too can be dropped — §9 risk #10, unchanged, not attempted this session
  (needs real regulatory source citations, which is a research/content task, not a code task). `destination_faqs`
  itself no longer blocks on this — see §18, it's already dropped.
- **The production sequencing warning at the top of this document** — read it before deploying this branch.

---

## 18. `destination_faqs` drop (2026-08-23, later same day)

§17 above kept `destination_faqs` alongside the three `destination_fish_*` tables under one "still open"
umbrella, inherited from the pre-existing Phase 5 migration's own scope. That grouping was checked directly
and found not to hold for this table: unlike the fish tables (still actively read/written by
`AdminCategoryCountryController`/`RegionController`/`CityController` today), a repo-wide check found **zero**
remaining references to `DestinationFaq` or `destination_faqs` anywhere in `app/` or `tests/`, apart from the
model's own file and one unused relation method (`Country::faqs()`). All 498 rows (402 originally typed +
the 96 orphaned rows resolved in §17) were already fully migrated into `faqs`, and Phase 3 had already
removed the admin write path to this table (§14).

**Action taken**: backed up `destination_faqs` via `mysqldump`, then ran
`database/migrations/2026_08_23_170000_drop_destination_faqs_table.php` (`Schema::dropIfExists`, `down()`
throws per the same "restore from backup, not migration rollback" convention as `2026_08_21_160000`). Removed
the now-dead `Country::faqs()` relation method and its unused `DestinationFaq` import — `DestinationFaq` the
*model* stays (same `migrate:fresh` reasoning as `Country`/`CountryTranslation`/`Destination`, §17).

Verified: `Schema::hasTable('destination_faqs')` is `false`. Targeted tests
(`CategoryPagesAdminTest`, `CategoryPageContentServiceTest` — 33 tests) pass unchanged.

**Remaining legacy tables, and why**: `destination_fish_charts`/`_size_limits`/`_time_limits` only. All three
are genuinely live (admin fish-data edit/save, public fish-availability charts) and stay until §9 risk #10's
manual regulatory-source-citation worklist is done — there is no more "was this actually still needed"
question left to check; what remains is real content work, not a code/data task.
