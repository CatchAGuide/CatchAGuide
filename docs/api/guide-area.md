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

## Tokens

| Method | Path | Notes |
|---|---|---|
| POST | `/api/guide/tokens` | `{email, password, device_name}` → `201 {token, token_type: "Bearer", expires_at, user}`. No auth needed. Wrong password or unknown email → the same `422`; after 5 misses per email + IP in 5 minutes → `429 {message, retry_after}` (`LoginThrottle`). Right password but not a Guide Area guide → `403` as above. |
| DELETE | `/api/guide/tokens/current` | Signs out: deletes the bearer token used for this request (`204`). |

Tokens are named `guide-area:{device_name}`, carry the ability `guide-area` and expire after
`GUIDE_AREA_API_TOKEN_DAYS` (default 90; `0` = never). Needs the migration that adds
`personal_access_tokens.expires_at`.

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

## Account (Account & security, spec chapter 13)

| Method | Path | Notes |
|---|---|---|
| GET | `/api/guide/account` | `{email, personal, billing, verification: {status, verified_at, rejection_reason}, password_changed_at}`. `billing.country` is an ISO code (old free-text countries are converted). |
| PUT | `/api/guide/account/personal` | `{firstname, lastname, language: en\|de}`. No new verification. |
| PUT | `/api/guide/account/billing` | `{account_type: private\|company, birthday (private, 18+), company_name + legal_form (company), address, address_number, postal, city, country, phone_country_code, phone, vat_id?, vat_not_registered?}`. `vat_id` is checked per country (`App\Rules\VatId`) and must be empty when `vat_not_registered` is true. `meta: {type_changed, review_requested}`: a change of the type or any field but the phone sets the verification to `pending` and sends N-33 (guide) and N-34 (ops). Tours stay online. |
| POST | `/api/guide/account/password` | `{current_password, password, password_confirmation}` → sends N-36. Throttled to 10 per minute. |

Bank details for payouts are not part of it: guides pay Catch A Guide the commission, Catch A
Guide never pays guides (user decision, 23 Nov 2026). The guest-facing IBAN stays on the profile.

Tests: `tests/Feature/Guide/Area/GuideToursTest.php`, `GuideVouchersTest.php`, `GuideApiTokensTest.php`,
`GuideAccountTest.php`.
