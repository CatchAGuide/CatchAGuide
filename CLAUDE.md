# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Catch A Guide — a Laravel booking marketplace for fishing guides, vacations (trips/camps/accommodations/rental
boats), and special offers. Laravel 13 / PHP 8.5 / Livewire 3. Served on two live domains sharing one codebase:
`catchaguide.com` (English) and `catchaguide.de` (German) — locale is resolved from the host in
`App\Http\Middleware\SetLocale`, with a `session('locale')` override from the language switcher.

## Commands

Use the pinned Laragon PHP 8.5 binary for all PHP/Artisan/PHPUnit commands — bare `php` on PATH is often 8.1 and
wrong for this repo. Do not run PHP-discovery commands (`Get-ChildItem …\laragon\bin\php`, `where.exe php`,
`Get-Command php*`) or switch to an older Laragon PHP install.

```
E:\Programs\laragon\bin\php\php-8.5.3-Win32-vs17-x64\php.exe
```

```powershell
# Artisan
& "E:\Programs\laragon\bin\php\php-8.5.3-Win32-vs17-x64\php.exe" artisan <command>

# Full test suite (Unit + Feature, PHPUnit 11)
& "E:\Programs\laragon\bin\php\php-8.5.3-Win32-vs17-x64\php.exe" artisan test

# Single test / filtered suite
& "E:\Programs\laragon\bin\php\php-8.5.3-Win32-vs17-x64\php.exe" artisan test --filter=GuideStatus
& "E:\Programs\laragon\bin\php\php-8.5.3-Win32-vs17-x64\php.exe" artisan test tests/Unit/Guide/GuideStatusServiceTest.php

# Frontend assets (Laravel Mix / webpack, not Vite)
npm run dev        # development build
npm run watch       # rebuild on change — needed after any resources/sass edit
npm run prod        # production build

# E2E (Playwright) — critical happy paths only, sparingly
npx playwright test
npx playwright test e2e/smoke.spec.js
npm run test:e2e:ui
```

Test env (`phpunit.xml`): array cache/session/mail, sync queue, `BCRYPT_ROUNDS=4`, Telescope disabled. DB connection
is whatever `.env`/`phpunit.xml` currently has configured (sqlite in-memory lines are present but commented out) —
check before assuming an isolated test DB.

## Architecture

### Domain-per-listing-type services

Vacation-side listing types — `Vacation`, `Trip`, `Camp`, `Accommodation`, `RentalBoat`, `SpecialOffer` — each get
their own service cluster under `app/Services/{Type}/`, typically a `*CacheService`, `*DataProcessor`, and often a
`*SeoService`/`*PricingProcessor`/`*ExtrasProcessor`. When touching one listing type's behavior, check whether the
sibling types need the equivalent change — the pattern is intentionally repeated per type rather than shared through
inheritance.

### Contracts + implementation split

`app/Contracts/` holds interfaces for cross-cutting systems (Assistant, Media, Storage, Sitemap); concrete
implementations live in the matching `app/Services/**` namespace and are bound in `app/Providers/`. Follow this
pattern (interface in `Contracts`, impl in `Services`) when adding a new pluggable system rather than depending on
concrete classes directly.

### Sitemap contributors

`app/Services/Sitemap/Contributors/*` each implement `SitemapContributorInterface` (`key()`, `fileName()`,
`entries()`) and are aggregated by `SitemapGenerator`. Adding a new sitemap section means adding a new contributor,
not branching inside an existing one.

### Media storage

All listing image uploads flow through `app/Services/Media/` (`ListingImageUploadService`,
`ConfigurableListingMediaStorage`, `ListingMediaPathBuilder`, `MediaUrlResolver`) onto the disk configured in
`config/media_storage.php` (`MEDIA_STORAGE_DISK`, default `do_spaces`) — never write new uploads to
`public/`/`storage/app` directly. `config/media_storage.php` also encodes legacy path migration: `listing_folders`
vs `legacy_listing_folders` vs `sitewide_folders` describe old flat-folder layouts still being migrated to
`{folder}/{entity-id}/{filename}.webp`; read it before writing anything that walks media paths.

### Repositories

`app/Repositories/Vacation/` (`VacationDestinationRepository`, `CampListingRepository`, `TripListingRepository`)
implement `Contracts/ListingRepositoryInterface` and centralize catalog/destination lookups for the vacation pillar.
Route logic that needs to know "is this slug a known country" (see `SitePrimaryNav::isProductDetailPage`) goes
through the repository, not ad hoc queries.

### View models / presenters

`app/Domain/**/ViewModels/` (e.g. `VacationHubViewModel`, `OfferCatalogViewModel`) assemble page-level data for
Blade; `app/Presenters/**` (e.g. `TripCardPresenter`, `TourCardPresenter`) format individual listing cards. Prefer
extending these over building view-shaping logic in controllers or Blade.

### Routing

`routes/web.php` only requires domain files from `routes/web/` in a specific, load-order-sensitive sequence
(documented in the file's header comment): `core` → `bookings` → `profile` → `checkout` → `catalog` → `content` →
`auth` → `admin` → `catch-all`. `profile` must register before catalog's destination catch-alls, and
`catch-all.php` (plus `category.thread`) must stay last. Add new routes to the matching domain file, not to
`web.php` directly, and be careful with ordering if a new route could collide with a catch-all.

### Site nav / layout state

`app/Support/SitePrimaryNav.php` is the single source of truth for header/nav state: which catalog section is
"active" for the current route, whether a route uses the overlay hero header vs. the solid layout header, whether
the mobile bottom nav shows, and whether a route counts as checkout/product-detail. Layout Blade partials read from
here rather than re-deriving `routeIs()`/`is()` checks — extend this class instead of duplicating route-matching
logic in views.

### Livewire

Components live under `app/Http/Livewire/` (not the Livewire-3-default `app/Livewire/`), with views under
`resources/views/livewire/`.

### Guide status

`app/Enums/GuideStatus.php` + `app/Services/Guide/GuideStatusService.php` (with a `HasGuideStatus` trait) model the
guide lifecycle; see `tests/Unit/Guide/` for the expected transitions before changing this.

## Project-specific conventions (from `.cursor/rules/`)

These apply to all new/changed code in this repo, not just Cursor sessions.

- **Laravel/SOLID**: thin controllers — no business rules, queries, or formatting in them; extract Services/Actions/
  Traits. Prefer Eloquent (relationships, scopes, eager `with()`) over `DB::`/raw SQL; justify raw SQL when used.
  Always `use` classes at the top — never inline fully-qualified `\App\Models\X::...`. Search for an existing
  Service/Trait/scope/Livewire component/Blade partial before adding a new one.
- **i18n**: locales are `resources/lang/{en,de}/`. Never hardcode user-facing text (Blade, Livewire, controllers,
  validation messages, flashes, emails) — use `__('file.key')` / `@lang` / `trans()`. Adding a copy key means adding
  it to **both** `en` and `de`. Prefer extending an existing lang file over creating a new one.
- **Styles**: only edit SCSS under `resources/sass/`. Never edit compiled `public/css/*.css` (e.g. `app.css`,
  `maps.css`) — it's regenerated/overwritten by `npm run dev`/Mix and edits there are lost. New partials get
  `@import`ed from the relevant entry (usually `resources/sass/app.scss`); reuse existing variables/breakpoints from
  `resources/sass/settings/` instead of hardcoding values.
- **Security/cost**: never hardcode secrets — read via `.env`/`config()` only, never expose to frontend/logs, never
  dump config/env/request payloads that may contain credentials. Treat Maps/Translate/SMS/email/storage APIs as
  cost-sensitive — avoid unbounded loops/retries/bulk calls; call out any change likely to increase paid API volume.
- **Testing/definition of done**: every meaningful change needs PHPUnit coverage (Unit for
  services/traits/pure logic, Feature for HTTP/Livewire/auth flows) — run the relevant filtered suite and fix
  failures before calling something done. Use Playwright E2E sparingly, only for critical happy paths. New
  user-facing copy needs both `en` and `de` keys. Watch for N+1s and unbounded payloads (page-speed regressions).
- When requirements, edge cases, or the correct reuse target are unclear, ask before implementing rather than
  guessing.
