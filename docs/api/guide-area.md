# Guide Area API (`/api/guide/*`)

Plan decision D-3 (17 Nov 2026): the Guide Area has a REST API next to the Livewire pages.
Both call the same services (`app/Services/Guide/Area/*`), so rules live in one place.

## Auth and access

- **Sanctum.** Either a bearer token (`Authorization: Bearer …`, for a future app) or the site
  session for first-party requests from a stateful domain (`config/sanctum.php` →
  `SANCTUM_STATEFUL_DOMAINS`; both live domains are in the default). Session requests that
  change data need the `X-XSRF-TOKEN` header.
- **Who.** Same gate as the `/guide/*` pages (`guide.area` middleware): a guide with
  `users.guide_area_beta` while `GUIDE_AREA_ENABLED` is on. Otherwise `401` (not signed in) or
  `403` (not a guide / not switched on), as JSON `{message}`.
- **Throttle.** 120 requests per minute.
- **Not built yet.** No endpoint issues tokens; a login/token flow is needed before an app can
  use bearer auth.

## Tours

| Method | Path | Notes |
|---|---|---|
| GET | `/api/guide/tours` | `status` = `live`/`draft`/`deactivated`, `search`, `per_page` (≤ 50). `meta.counts` has per-status totals. |
| POST | `/api/guide/tours/{id}/go-live` | `422 {reason, gaps, gap_labels}` when the guide isn't verified (`not_verified`) or the tour is incomplete (`incomplete`). |
| POST | `/api/guide/tours/{id}/deactivate` | Live → deactivated. Drafts stay drafts. |
| POST | `/api/guide/tours/{id}/duplicate` | `201`, new draft with its own photo copies. |

Tour status is the stored `guidings.status`: `2` draft, `1` live, `0` deactivated
(`App\Enums\TourStatus`). `gaps` are the keys of `Guiding::publishingGaps()`.

## Voucher orders

| Method | Path | Notes |
|---|---|---|
| GET | `/api/guide/voucher-orders` | `filter` = `all`/`todo`/`done`, `search` (matches `G-0001`, code, tour, buyer), `per_page`. |
| GET | `/api/guide/voucher-orders/{id}` | |
| PATCH | `/api/guide/voucher-orders/{id}` | `{is_paid?, is_sent?}`. The admin status follows: both → done (booked), one → in process, none → open. |

## Voucher settings

| Method | Path | Notes |
|---|---|---|
| GET | `/api/guide/voucher-settings` | Design and every tour with `voucher_enabled`. |
| PUT | `/api/guide/voucher-settings` | `{design, enabled_tour_ids: []}`. Tours not listed are switched off. |

Tests: `tests/Feature/Guide/Area/GuideToursTest.php`, `GuideVouchersTest.php`.
