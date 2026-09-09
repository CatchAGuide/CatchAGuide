# Artisan Commands Audit Report

**Date:** 2026-08-10  
**Scope:** All custom commands under `app/Console/Commands/` (60 classes) plus `inspire` in `routes/console.php`  
**Sources of truth:** command classes, `app/Console/Kernel.php`, `config/scheduled_tasks.php`

---

## Executive summary

| Rating | Meaning | Count (approx.) |
|--------|---------|-----------------|
| **Essential** | Required for current production behavior | ~12 |
| **Useful** | Keep for ops / on-demand / scheduled when ready | ~25 |
| **Optional / Warmup** | Helps performance but not correctness | ~2 |
| **Diagnostic-only** | Dev/staging tooling | ~6 |
| **Migration one-shot** | Run during cutovers; not ongoing cron | ~12 |
| **Legacy / Obsolete** | Safe to retire or avoid re-running | ~10 |

### Current app context (what “useful” means here)

- Listings: tours (guidings), trips, camps, vacations, accommodations, rental boats, special offers  
- Media: DigitalOcean Spaces / CDN via `media_url()` (`MEDIA_URL_SKIP_EXISTS` default true)  
- Locales: EN + DE  
- Catalogs: guidings listing + unified `/offers`  
- Ops: bookings lifecycle, iCal sync, DDoS / threat intelligence, finance invoices (optional)

### Critical scheduling note

`Kernel::schedule()` **hardcodes** tasks. Admin-driven registration is **disabled**:

```php
// app/Console/Kernel.php
/// FOR ADMIN SIDE SCHEDULER CONFIGURATION
// app(ScheduledTaskService::class)->register($schedule);
```

So `config/scheduled_tasks.php` / admin toggles do **not** control runtime until that line is re-enabled. There is also **drift** (e.g. `cache:warm-files` enabled in config but commented out in Kernel).

---

## Actively scheduled today (Kernel)

| Command | Cadence | Usefulness |
|---------|---------|------------|
| `update:booking-status` | Hourly | Essential |
| `bookings:send-guest-reviews` | Hourly | Essential |
| `bookings:create-automatic-reviews` | Daily 02:15 | Essential |
| `bookings:send-guest-tour-reminders` | Hourly | Essential |
| `bookings:send-guide-reminders` | Hourly | Essential |
| `bookings:send-guide-reminders-12hrs` | Hourly | Essential |
| `catalog:generate-filters` | Hourly (no overlap, background) | Essential |
| `generate:sitemap` | Daily (background) | Essential |
| `threat-intelligence:cleanup` | Daily 03:30 | Essential |
| `ical:sync-feeds` | Every 2 hours | Essential |

**Commented out in Kernel (not running):**  
`cache:warm-files`, `images:cleanup --report-only`, `vacation:translate --admin-changes --relations`, `guiding:translate --detect-language`, `finance:auto-send-guide-invoices`.

---

## Schedule vs config drift

| Command | Kernel | `scheduled_tasks.php` default |
|---------|--------|-------------------------------|
| Booking ops + filters + sitemap + threat cleanup + ical | Active | `enabled: true` |
| `cache:warm-files` | Commented out | **`enabled: true`** (drift) |
| `images:cleanup --report-only` | Commented out | `enabled: false` |
| `vacation:translate --admin-changes --relations` | Commented out | `enabled: false` |
| `guiding:translate --detect-language --mismatches-only` | Commented out | `enabled: false` |
| `finance:auto-send-guide-invoices` | Commented out | `enabled: false` |
| `ScheduledTaskService::register()` | Commented out entirely | Config unused at runtime |

Inspect merged config anytime with:

```bash
php artisan scheduled-tasks:inspect
```

---

## Duplicate / overlapping commands

| Group | Commands | Recommendation |
|-------|----------|----------------|
| **Sitemaps** | `generate:sitemap` (canonical) · `generate:ensitemap` / `generate:desitemap` (aliases) · `sitemap:generate` (old Spatie crawl) | Keep `generate:sitemap`; retire aliases + `sitemap:generate` |
| **Guide 24h expiry reminders** | `bookings:send-guide-reminders` (scheduled) · `run:bookreminders` | Keep scheduled one; retire `run:bookreminders` |
| **Guest review emails** | `bookings:send-guest-reviews` (scheduled) · `email:send-review-emails` | Keep scheduled one; retire duplicate |
| **Guide vs guest tour reminders** | Guest 48h (scheduled) · Guide 48h (`bookings:send-guide-upcoming-tour-reminders`, **not** scheduled) | Complementary; consider scheduling guide variant if product wants it |
| **Geo / locations** | `guidings:generatecoordinates` · `guidings:normalize-locations` · `listings:normalize-locations` · `command:generatecountry` | Prefer normalize-* + generatecoordinates; avoid `generatecountry` (Maps cost) |
| **Media → Spaces** | `media:migrate-to-object-storage` · `media:sync-directories` · `media:sync-listing-id` · `media:migrate-guiding-images-to-folders` | Different scopes; all still valid for migration |
| **Translations** | `guiding:translate` · `vacation:translate` · `listing:translate` · `translate:faq` | Intentional split by domain |

---

## Full command inventory

Usefulness ratings: **Essential** · **Useful** · **Optional/Warmup** · **Diagnostic-only** · **Migration one-shot** · **Legacy/Obsolete**

### 1. Bookings & operations

| Command | Usage | Scheduled | Usefulness | Notes |
|---------|-------|-----------|------------|-------|
| `update:booking-status` | Expire pending bookings past `expires_at`; notify guest/guide | Yes (hourly) | **Essential** | Core booking SLA |
| `bookings:send-guest-reviews` | Review request emails ~24h after completed tours | Yes (hourly) | **Essential** | Current guest review funnel |
| `bookings:create-automatic-reviews` | Auto-create reviews 10 days after fishing date if none (`--dry-run`, `--booking=`, `--guiding=`) | Yes (daily 02:15) | **Essential** | Confirm product still wants synthetic reviews |
| `bookings:send-guest-tour-reminders` | Guest reminder 48h before tour | Yes (hourly) | **Essential** | |
| `bookings:send-guide-reminders` | Guide reminder 24h before request expiry | Yes (hourly) | **Essential** | Replaces `run:bookreminders` |
| `bookings:send-guide-reminders-12hrs` | Guide reminder 12h before request expiry | Yes (hourly) | **Essential** | Second nudge |
| `bookings:send-guide-upcoming-tour-reminders` | Guide reminder 48h before booked tour | No | **Useful** | Not scheduled; may be intentional gap |
| `run:bookreminders` | Legacy 24h guide expiry reminders | No | **Legacy/Obsolete** | Overlaps scheduled guide reminders; fragile hour match |
| `email:send-review-emails` | Guest review emails (since Jan 2025) | No | **Legacy/Obsolete** | Near-duplicate of `bookings:send-guest-reviews` |
| `send:bookingconfirmationmail` | Sends confirmation for hardcoded booking | No | **Legacy/Obsolete** | Debug one-off; unsafe if run blindly |
| `email-logs:repair-zero-recipients` | Backfill `email_logs.email` stored as `0` (`--force`) | No | **Migration one-shot** | Supports reminder dedupe; dry-run by default |
| `guide:backfill-status` | Backfill `guide_status` from legacy `is_guide` (`--dry-run`) | No | **Migration one-shot** | Until legacy column is fully retired |

### 2. SEO

| Command | Usage | Scheduled | Usefulness | Notes |
|---------|-------|-----------|------------|-------|
| `generate:sitemap` | Dual-locale sitemap via `SitemapGenerator` (`--lang=all\|en\|de`) | Yes (daily) | **Essential** | Canonical sitemap for current catalog |
| `generate:ensitemap` | Alias → `generate:sitemap --lang=en` | No | **Legacy/Obsolete** | Thin deprecated wrapper |
| `generate:desitemap` | Alias → `generate:sitemap --lang=de` | No | **Legacy/Obsolete** | Thin deprecated wrapper |
| `sitemap:generate` | Spatie crawl of `app.url` → `public/sitemap.xml` | No | **Legacy/Obsolete** | Can overwrite wrong file; not offers/locale-aware |

### 3. Media & storage

| Command | Usage | Scheduled | Usefulness | Notes |
|---------|-------|-----------|------------|-------|
| `media:migrate-to-object-storage` | Upload local listing images to Spaces (`{listing=guiding}`, `--dry-run`, `--limit`) | No | **Migration one-shot** | Guiding-focused; extend for other listing types as needed |
| `media:sync-directories` | Bulk upload local media dirs to Spaces | No | **Useful** | Run on server that still holds files |
| `media:sync-listing-id` | Upload one entity’s images to Spaces | No | **Useful** | Targeted repair |
| `media:make-objects-public` | Set public-read ACL on Spaces objects | No | **Useful** | When objects uploaded private |
| `media:list-folders` | Print sitewide folders + bucket prefix | No | **Diagnostic-only** | Inventory for migration |
| `media:migrate-guiding-images-to-folders` | Move legacy guiding paths → `assets/images/guidings/{id}/` | No | **Migration one-shot** | Local layout cleanup |
| `images:cleanup` | Report/fix missing image refs; optional orphan delete | Config off / Kernel commented | **Useful** | Prefer `--report-only`; `--delete-orphans --no-dry-run` is destructive |
| `cache:warm-files` | Warm local `file_exists_*` cache for guiding images | Kernel commented (config says on) | **Optional/Warmup → effectively obsolete** | Consumers gone; media uses Spaces + `media_url()`. Safe to remove from schedule permanently |
| `generate:images` | Hardcoded guiding gallery import | No | **Legacy/Obsolete** | Not Spaces-aware |
| `command:populateimages` | Download remote images into `public/assets/guides` | No | **Legacy/Obsolete** | Pre-Spaces |
| `update:threads` | Convert Thread images to WebP under `public/blog` | No | **Migration one-shot** | Blog-only; mutates all threads |

### 4. Filters & cache

| Command | Usage | Scheduled | Usefulness | Notes |
|---------|-------|-----------|------------|-------|
| `catalog:generate-filters` | Rebuild guidings + offers filter ID maps (`--only=guidings\|offers`, `--dump`) | Yes (hourly) | **Essential** | Replaces separate guidings/offers generate-filters commands; aliases still delegate |

### 5. Security (DDoS)

| Command | Usage | Scheduled | Usefulness | Notes |
|---------|-------|-----------|------------|-------|
| `threat-intelligence:cleanup` | Delete old threat rows (default 7 days) | Yes (daily 03:30) | **Essential** | Bounds threat table growth |
| `ddos:manage` | Stats / block / reset / threat / honeypot | No | **Useful** | Ops console |
| `ddos:config` | show / validate / reset / backup / restore | No | **Useful** | Live config care required |
| `ddos:alert` | Manage DDoS alert emails | No | **Useful** | |
| `ddos:block-stubborn` | Block persistent attackers (`--dry-run`) | No | **Useful** | Risk of blocking shared IPs |
| `test:ddos-protection` | Hit gemini/search/checkout contexts | No | **Diagnostic-only** | Do not aim at production carelessly |

### 6. Internationalization / translations

| Command | Usage | Scheduled | Usefulness | Notes |
|---------|-------|-----------|------------|-------|
| `guiding:translate` | Translate / detect source language for guidings (EN/DE) | Off | **Useful** | **Billable** when translating; `--detect-language` is cheap audit |
| `vacation:translate` | Gemini vacation (+ relations) translation | Off | **Useful** | **Billable**; prefer `--admin-changes` / IDs |
| `listing:translate` | Translate camps/trips/boats/special offers/accommodations | No | **Useful** | Critical for offers catalog i18n; on-demand |
| `translate:faq` | Translate FAQ rows | No | **Useful** | Billable translate helper |
| `vacation:clear-translation-cache` | Clear vacation relation translation cache | No | **Useful** | Ops when UI shows stale translations |
| `debug:vacation-translation` | Debug one vacation relation translation | No | **Diagnostic-only** | |
| `diagnose:vacation-display` | Diagnose vacation translation display | No | **Diagnostic-only** | |

### 7. Content / listings migration & data quality

| Command | Usage | Scheduled | Usefulness | Notes |
|---------|-------|-----------|------------|-------|
| `destinations:migrate` | Old destinations → Country/Region/City | No | **Migration one-shot** | Re-run only if incomplete |
| `destinations:fix-migration` | Repair destinations cutover (`--rollback`, `--migrate`) | No | **Migration one-shot** | `--rollback` destructive |
| `destinations:fix-relationships` | Fix City/Region/Country links | No | **Migration one-shot** | |
| `trips:import-xlsx` | Import Angelreise XLSX trip templates | No | **Useful** | Active trips content pipeline |
| `trips:audit-xlsx` | Verify XLSX vs DB | No | **Useful** | Safe audit |
| `guidings:normalize-locations` | Backfill lat/lng; normalize place names (EN) | No | **Useful** | Nominatim rate limits; use `--sleep` / `--limit` |
| `listings:normalize-locations` | Same for camps/trips/boats/offers/accommodations | No | **Useful** | Offers catalog geo quality |
| `guidings:generatecoordinates` | Fill null lat/lng (`--limit=10`) | No | **Useful** | May use **billable Google Maps** if configured |
| `command:generatecountry` | Google Places backfill for guidings | No | **Legacy/Obsolete** | High Maps cost; superseded |
| `slugify:start` | Rewrite all guiding slugs from title+location | No | **Legacy/Obsolete** | **Breaks URLs / SEO** if re-run |
| `build:settings` | Mass `save()` on guidings (“Baut die Guidings um”) | No | **Legacy/Obsolete** | Appears inert; observer side effects |
| `migrate:reviews` | Legacy Rating → Review | No | **Migration one-shot** | Historical only |
| `vacation:export-interests` | CSV export of vacation interest signups | No | **Useful** | CRM; writes PII |

### 8. Calendar / iCal

| Command | Usage | Scheduled | Usefulness | Notes |
|---------|-------|-----------|------------|-------|
| `ical:sync-feeds` | Sync external iCal feeds (`--user-id`, `--feed-id`, `--force`) | Yes (every 2h) | **Essential** | Guide availability blocks |
| `migrate:calendar-schedule` | Build blocked dates from availability; optional booking migrate | No | **Migration one-shot / Useful** | File comments about daily schedule are stale; `--force`/`--cleanup` destructive |

### 9. Finance

| Command | Usage | Scheduled | Usefulness | Notes |
|---------|-------|-----------|------------|-------|
| `finance:auto-send-guide-invoices` | Commission invoices after tour (3/7/10 day retries) (`--dry-run`) | Off | **Useful** | Enable when finance ops ready; sends real mail |

### 10. Diagnostics & misc

| Command | Usage | Scheduled | Usefulness | Notes |
|---------|-------|-----------|------------|-------|
| `performance:diagnose` | Guidings listing perf (indexes, filters, timing) | No | **Diagnostic-only** | |
| `test:page-performance` | Benchmark guidings page pieces | No | **Diagnostic-only** | Dev-oriented |
| `scheduled-tasks:inspect` | Print tasks with merged admin config | No | **Useful** | Especially while Kernel vs admin registration is split |
| `inspire` | Laravel quote demo | No | **Legacy/Obsolete** | No product value |

---

## Recommendations (prioritized)

### Keep running (production cron)

1. All currently active Kernel booking reminder / status / review jobs  
2. `catalog:generate-filters`  
3. `generate:sitemap`  
4. `threat-intelligence:cleanup`  
5. `ical:sync-feeds`

### Keep available (manual / optional schedule)

- Media Spaces tooling (`media:sync-*`, `media:make-objects-public`, `images:cleanup --report-only`)  
- Translation commands (`guiding|vacation|listing:translate`) — **cost-bounded**  
- Location normalize / coordinate fill — **rate-limited**  
- `trips:import-xlsx` / `trips:audit-xlsx`  
- `finance:auto-send-guide-invoices` when ready  
- DDoS ops commands  
- `bookings:send-guide-upcoming-tour-reminders` (schedule if product wants guide 48h tour mails)

### Retire or quarantine

| Command | Why |
|---------|-----|
| `cache:warm-files` | Local file-existence cache unused; Spaces + `media_url()` replaced it |
| `sitemap:generate`, `generate:ensitemap`, `generate:desitemap` | Superseded by `generate:sitemap` |
| `run:bookreminders`, `email:send-review-emails` | Duplicate scheduled booking mailers |
| `send:bookingconfirmationmail`, `generate:images`, `command:populateimages` | Hardcoded / pre-Spaces one-offs |
| `command:generatecountry`, `slugify:start`, `build:settings` | Costly or destructive if re-run |
| `inspire` | Framework demo |

### Fix drift

1. Either re-enable `ScheduledTaskService::register($schedule)` **or** treat Kernel as sole truth and set `cache_warm_files.default.enabled` to `false`.  
2. Document that admin scheduled-tasks UI does not apply while register() is commented out.  
3. Align Kernel comments with reality for `migrate:calendar-schedule` / finance / translations.

---

## Quick reference: run examples

```bash
# Essential scheduled (manual trigger)
php artisan update:booking-status
php artisan catalog:generate-filters --dump
# scoped:
php artisan catalog:generate-filters --only=guidings --dump
php artisan catalog:generate-filters --only=offers --dump
php artisan generate:sitemap
php artisan ical:sync-feeds
php artisan threat-intelligence:cleanup --dry-run

# Media (safe first)
php artisan media:list-folders
php artisan images:cleanup --report-only
php artisan media:sync-listing-id guiding 123 --dry-run

# Offers / listings i18n & geo (bounded)
php artisan listing:translate --needs-update --dry-run
php artisan listings:normalize-locations --dry-run --limit=50
php artisan trips:audit-xlsx path/to/file.xlsx

# Scheduler visibility
php artisan scheduled-tasks:inspect
php artisan schedule:list
```

---

## Appendix: file map

Commands live in `app/Console/Commands/`. Registration is via `$this->load(__DIR__.'/Commands')` in `Kernel::commands()` plus `routes/console.php` (`inspire`).

Scheduled task **catalog** (admin UI source): `config/scheduled_tasks.php`.  
Runtime schedule (today): `app/Console/Kernel.php` only.
