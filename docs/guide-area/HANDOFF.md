# Guide Area redesign: handoff (state on 23 Nov 2026)

Read this first when you pick up Guide Area work. It records what is built, the decisions behind it, how to test it
safely, and what the spec still asks for.

## Sources

| What | Where |
|---|---|
| Product spec v1.0 (10 Nov 2026), 19 chapters | `C:\Users\LESTER ADMIN\Downloads\CatchAGuide_Guide_Area_Implementation_Plan_v1.0.docx` |
| Engineering plan (Claude Doc, ~85k chars) | https://claude.ai/artifact/JgDgCdFLq2AxGr74nTJVnt |
| Design prototype (canvas, boards per page) | https://claude.ai/artifact/SNfBQET5WeddAebpAy1Rm4 |
| API docs | `docs/api/guide-area.md` |

Mockup boards: Overview, Bookings, To-dos, Calendar, Tours, Vouchers, Reviews, Invoices, Guide profile, Account &
security, Contact support (sidebar).

## Branch and PR

- Branch `feature/guide-area-profile`, PR **#528 → `dev`**.
- Full suite: **1661 passed**, 10 risky, 0 failures (18 Nov).
- **Not merged.** The user still has to approve the public availability change (see "Decisions") before it merges
  into `dev`.
- **On hold, don't raise:** production deploy (migrations + `MEDIA_PRIVATE_DISK`) and the GTM `^/guide/` page-type
  row. The user will reopen these.

Commits, oldest first:

| Commit | What |
|---|---|
| 509889b2 | `/guide/profile` page (first behind a beta flag) |
| eff7167a | Entry points: redirect from old guide profile, menu links |
| 6ae65284 | `GuidePublicProfilePresenter` on the tour page: display name, tags, verified documents |
| 71a0db2c | All profile photo uploads go through media storage |
| 13f8fde5 | `/guide/tours`, `/guide/vouchers` (+ settings), `/api/guide/*` REST API, `BookingStatus` enum |
| 3978635d | Stale tests fixed (`phpunit.xml` sets `APP_URL=http://cag.local`), dead relations removed |
| ab61a2f4 | `POST /api/guide/tokens`, `DELETE /tokens/current` (Sanctum bearer tokens, login throttle) |
| c9cf9908 | Guide API follows the visitor's language; session auth on cag.local |
| d338bfa4 | Overview, Bookings, To-dos, Calendar, Reviews, Invoices pages |
| 33b373e7 | `/profile` sends guides to `/guide` |
| 61cbc2ad | Beta check dropped: every guide uses the Guide Area |
| 76b48fe0 | Fixes from the hands-on local test |
| 18cd3136 | Customer pages of `/profile/*` in the Guide Area design |
| (23 Nov) | Account & security for guides (see below) |

## Access and layout

- `config('guide_area.enabled')` (env `GUIDE_AREA_ENABLED`, default true) is the **only switch**. Set it to false
  to send every guide back to the old pages.
- `User::usesGuideArea()` = switch + `canViewGuideTools()`. The `users.guide_area_beta` column still exists but
  nothing reads it, and the admin beta switch was removed.
- `ProfileController::index`: guides go to `/guide`, other users stay on `/profile`.
- `EnsureGuideAreaAccess` sends people without access to the matching old page: overview → `profile.index`,
  bookings → `profile.bookings`, calendar → `profile.calendar`, anything else → `profile.guide-profile`.
- Layout `resources/views/layouts/guide-area.blade.php` extends `layouts.app-v2-1`, so the site header and footer
  stay. The header search band is hidden on `guide.area.*` (`SitePrimaryNav::usesPageHeaderSearch`).
- Sidebar: `app/Support/GuideAreaNav.php`.
  - Badges are bookings (open requests + vouchers to handle), todos and unseen reviews, plus an overdue dot on
    invoices.
  - They update live through the `guide-area-badges` browser event. Toasts use `guide-area-toast`.
- Below 768px the sidebar becomes a Menu button. The save bar and toast are offset above the site's bottom nav.
- Routes are in `routes/web/profile.php` (guide group, prefix `/guide`).
- Livewire components are in `app/Http/Livewire/Guide/`, views in `resources/views/livewire/guide/`.
- SCSS is in `resources/sass/guide-area/` and builds to `guide-area.css`.
- Copy is in `resources/lang/{en,de}/guide_area.php`.

## Pages

| URL | Status | Notes |
|---|---|---|
| `/guide` Overview | Done | Requests in deadline order, vouchers to handle, upcoming tours, to-do score, latest reviews, earnings snippet |
| `/guide/bookings` | Done | See below |
| `/guide/calendar` | Done | See below |
| `/guide/todos` | Done | `GuideOptimizationService` (spec 2.7), 5-minute cache, Fix links |
| `/guide/tours` | Done | Status from stored status, go live / deactivate / duplicate, publishing gaps |
| `/guide/vouchers/settings` | Done | `/guide/vouchers` redirects to `bookings?tab=vouchers` |
| `/guide/reviews` | Done | Summary, 8 per page, "New" until seen (`reviews.seen_by_guide_at`) |
| `/guide/invoices` | Read-only | Per-booking invoices from `finance_items`; View opens the emailed invoice HTML (`GuideInvoiceController`) |
| `/guide/profile` | Done | Includes certificates (private file route) |
| Account & security | Done | Guides: `Guide\Account` on `profile.settings` / `profile.password`, see "Account & security" below |
| Contact support modal | **Not built** | |

**Bookings**
- Filter tiles: all, open, confirmed, completed, closed. Also tour, period and search (name or #id) filters.
- Deep link `?booking=ID&decline=1` opens the booking and scrolls to it.
- Accept: locks the row in a transaction. Refused when the request isn't open, has expired, or the guide already
  has a booking that day.
- Decline: uses the emailed form's rules (`RejectionRequest::unavailableDates`): message at least 50 characters,
  up to 3 alternative dates that are bookable and not the requested day.
- Cancel: needs a reason (weather, illness, equipment, other) and works only before the tour date. It saves
  `cancel_reason`, `cancelled_by = guide` and `cancelled_at`.
- The guest's contact details show only once the booking is accepted.
- Status comes from the `BookingStatus` enum: completed = accepted with a past date; expired = cancelled with the
  status-history reason `expired`.
- Code: `GuideBookingService`, `GuideBookingPresenter`.

**Calendar**
- Code: `GuideCalendarService`, built on `Guiding::getBlockedEvents()` and `BlockedDateRanges::merge`.
- Month grid, a day panel, and per-tour open/closed switches. A switch is locked on a booked day, and the panel
  says why a tour is closed: manual block, declined request, or the tour's season/weekday rules.
- Block days over a period, up to 366 days; booked and pending days are skipped.
- Unblocking a day leaves days imported from iCal in place.
- iCal import (`GuideCalendarSyncService`): URL validation, Sync now, Disconnect. Imported days are stored with
  `calendar_schedule.source = ical` and no event names (busy only).
- iCal export:
  - options for pending requests and manual blocks;
  - entries read "Booked: …" or "Request pending: …";
  - "Create a new link" turns the old link off.

## Customer pages (18 Nov)

Non-guides get the same design on `/profile/*`, showing only what they can use. Guides on the Guide Area get the
same pages inside the guide sidebar.

- Switch: `config('guide_area.customer_enabled')` (env `CUSTOMER_AREA_ENABLED`, default true). `User::usesCustomerArea()`
  = switch && (not a guide, or a guide who uses the Guide Area). Off → the old profile pages come back.
- Routes and names are unchanged. `ProfileController` renders `customer-area.page`, which embeds a Livewire component
  (`app/Http/Livewire/Customer/*`, views in `resources/views/livewire/customer/`) inside `layouts.guide-area`. The
  layout now supports both `$slot` (Livewire pages) and `@yield('area-main')`, and chooses the sidebar with
  `CustomerAreaNav::forUser()`.
- Pages:
  - `/profile` Overview: next trips, requests waiting for an answer, trips to rate, counts per type, favourites,
    a Become a guide box.
  - `/profile/bookings` My bookings, with tabs for tours (status tiles), camps, trips and vouchers they bought.
    The guide's contact details and meeting point show only once the guide confirms. A declined request shows the
    guide's message, suggested dates and the reschedule link; a finished trip shows the rating link. It is
    read-only, like the old page (customers can't cancel there; the old `stornobooking` was never linked).
  - `/profile/favoriteguides` Favourites: live saved tours, with Remove.
  - `/profile/settings` and `/profile/password`: one Account page with Personal details, Password and Cookie
    settings tabs, switched in the browser so the cookie panel's script keeps working. The email is read-only.
    Tax ID is shown to guides only.
- Guides:
  - The sidebar has a new "My bookings" item → `/profile/my-bookings` (new route), and "Account & security" now
    opens the new page.
  - `/profile/bookings` sends Guide Area guides to `/guide/bookings` (with `?id=` → `?booking=`), because guide
    reminder and voucher emails link there.
  - `/profile/showbooking/{id}` redirects to the matching card.
- The header search band is hidden on these routes (`SitePrimaryNav::CUSTOMER_AREA_ROUTES`).
- Code: `CustomerBookingService`, `CustomerFavouriteService`, `CustomerAccountService` (`app/Services/Customer/`),
  `CustomerBookingPresenter`, `CustomerAreaNav`. SCSS is in `guide-area/_customer.scss`. Copy is under
  `guide_area.customer.*`. Tests: `tests/Feature/Customer/CustomerAreaTest.php` (13).
- Demo data: `php artisan db:seed --class=CustomerAreaDemoSeeder` (local only). It creates a non-guide customer
  with every booking type and favourites, plus its own demo guide and tours, and can be rerun. The login details are
  in the seeder. Claude may only type test credentials on a local host such as 127.0.0.1, not on cag.local. So serve
  the app with `php -S 127.0.0.1:8010 ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php`,
  run from `public/`. `artisan serve` loads `.env.local` (Docker DB host `db`) and fails.
- Browser check (18 Nov, as the demo customer): every page, tab, the save toast and phone width passed. Not tried:
  photo upload (it uses the live Spaces bucket). On both the account page and `/cookie-settings`, the Marketing
  switch showed as on after the banner's "necessary only". Rechecked 18 Nov with a clean Playwright browser: "necessary
  only" leaves it off, and turning it off after "Accept all" sticks. Most likely an earlier "Accept all" cookie in that
  browser. Not a bug.

## Account & security (23 Nov)

Spec chapter 13 and the prototype board "Account & security". Guides on the Guide Area get `Guide\Account`
(`resources/views/livewire/guide/account.blade.php`) on `/profile/settings` and `/profile/password`. Customers
keep `Customer\Account`. It is one page with four cards, each with its own save button: Personal details,
Verification & billing, Password and Cookie settings.

- **Data:** the existing columns are reused: `users.guide_type` (private/company), `phone`, `tax_id` (= VAT ID), and
  `user_information` for address, country, birthday, `company_name` and `legal_form`. New (migration
  `2026_11_23_100000`):
  - `users.account_verification_status` / `account_verified_at` / `account_verified_by` /
    `account_rejection_reason`;
  - `account_type_changed_at` and `password_changed_at`;
  - `user_information.vat_not_registered`.

  The migration also backfills the account type (company when a company name or legal form exists) and sets the
  status to verified for verified guides and pending for applicants.
- **Why not `guide_status`:** "pending" there takes the tours offline. A billing re-verification must not (spec
  2.4), so it has its own status (`AccountVerificationStatus`). `GuideStatusService` sets it when an application is
  submitted or approved.
- **Rules:**
  - Any change of the type or a billing field (not the phone) → pending, N-33 to the guide, and N-34 to ops (mail to
    `guide_onboarding.admin_notification_email` plus an admin notification), unless a review was already pending.
  - Individual: date of birth (18+).
  - Company: company name, plus a legal form from `config('guide_area.account.legal_forms')` (grouped by country,
    "Other" = free text, stored as the form's name).
  - Country is an ISO code (`App\Support\CountryList`, PHP intl; old free-text values like "Deutschland" are read
    as codes).
  - VAT ID format per country (`App\Rules\VatId`; the VIES check is P2), or "I am not VAT registered".
- **Type switch:** the other type's fields stay stored and are restored on a switch back. After 30 days
  `guide-area:purge-account-type-data` (daily 03:20, in Kernel + `config/scheduled_tasks.php`) clears them.
- **Password:** `CustomerAccountService::changePassword` (shared with customers) stamps `password_changed_at` and
  sends N-36 (`App\Mail\Account\PasswordChangedMail`) to everyone who changes it on the account page.
- **Admin:** Admin › Guide billing verification (`admin.guide-billing.*`, next to Guide certificates) lists the
  data, with Approve and Reject + reason → N-35 (`GuideAccountVerificationMail`).
- **API:** `GET /api/guide/account`, `PUT /account/personal`, `PUT /account/billing`, `POST /account/password`
  (`docs/api/guide-area.md`).
- **To-dos:** "Billing address" now needs the full address, a valid country and (individual) a date of birth or
  (company) the name and legal form. "VAT" also passes with "not VAT registered". Fix links open `#billing`.
- **Left out on purpose:** bank details for payouts (AC-05). Guides pay Catch A Guide; Catch A Guide never pays out
  (user, 23 Nov). The guest-facing IBAN stays on the profile and still feeds the "Bank details" to-do.
- **Tests:** `tests/Feature/Guide/Area/GuideAccountTest.php` (17), `tests/Unit/Rules/VatIdTest.php`,
  `tests/Unit/Support/CountryListTest.php`.
- **Demo:** `CustomerAreaDemoSeeder` now gives its demo guide a fixed test password (`GUIDE_PASSWORD`), so the page
  can be tried on 127.0.0.1.

## Guest booking access, Phase 1 (23 Nov)

Plan doc "Guest Booking Access: Plan" (Claude Doc, link in memory). Guests without an account get a link to each
booking; nothing here creates an account, and the copy says "your booking", never "account" or "profile".

- **Link:** `/b/{token}` (48 random characters), one per booking, for tours, camps, trips and vouchers
  (`booking_access_tokens`, `bookable_type` = `tour|camp|trip|voucher`, `App\Enums\GuestBookingType`). Made the
  first time an email renders it and reused by every later email (`BookingAccessTokenService::urlFor`). Stored as a
  sha256 for the lookup plus an encrypted copy, so later emails repeat the same link. Never expires.
- **Page:** `/b/{token}` puts the link in the session and 303-redirects to `/your-booking/{id}`, so the token never
  reaches the page URL, GTM/GA4 or a referrer (same pattern as the reschedule link). Opening `/your-booking/{id}`
  in a browser that never clicked the link shows "this link doesn't work here". `no-referrer`, `no-store`, noindex.
  No GTM change is needed for privacy; `^/your-booking/` can get an `RT - Page Type` row later.
- **Rules (GuestBookingPresenter):** the customer area's card. Guide phone, email and meeting point show from
  confirmation through the tour day, hidden after (Q3). From 90 days after the last day the view is reduced (F4).
  Read-only: no cancelling (Q6). "Add to calendar" (.ics, all-day) for confirmed upcoming tours and open camp/trip
  wish dates. Voucher PDF only once the guide marked the voucher sent.
- **Emails with the "View your booking" button (11):** tour request received, accepted, declined, cancelled,
  expired, reminder, review; camp and trip confirmations; voucher order; camp/trip review request. Partial
  `mails.partials.booking-link-button` (renders nothing for unsaved admin-preview samples). There is no
  "voucher sent" email to the buyer (the guide sends the voucher), and no guest invoice PDF exists.
- **Admin (Q12):** link and unlink buttons on the camp, trip and voucher lists, and in the tour list's "…" menu:
  "Send booking link" emails the link to the booking's own address; "Turn link off and send a new one" revokes and
  resends. Logged in `booking_access_events` (created, sent, opened, revoked; admin id; no IP or browser, F5).
- **Not built (later phases):** "Find my booking" email sign-in (Phase 2), guest actions and the message queue
  (Phase 3), customer lookup / merge / view as guest (Phase 4). No backfill command: links are made on demand, so
  any later email for an older booking gets one.
- **Tests:** `tests/Feature/GuestBooking/GuestBookingLinkTest.php` (13).

## Decisions

- **D-2:** the new pages are used on every screen size.
- **D-3:** REST API now. `/api/guide/*` covers the profile, tours, vouchers and tokens; the six newer pages have no
  endpoints yet.
- **Bookings:** today's rules stay. They are extended only where the spec says so.
- **Invoices:** read-only from today's data, because D-1 (monthly vs per-booking invoices, Finance) is still open.
- **Calendar:** reuses today's logic, extended per the spec.
- **Beta dropped:** every guide uses the Guide Area. The rollback is `GUIDE_AREA_ENABLED=false`.
- **Public availability changed in `Guiding::getBlockedEvents`** (this is spec Q-05 as recommended; the user's OK
  is needed before merge):
  - a pending (not expired) or accepted booking on any of the guide's tours blocks that day on all their tours;
  - a declined request keeps its own tour blocked;
  - cancelled and expired bookings free the day;
  - a `custom_schedule` row with `guiding_id` blocks only that tour.
- **Still open:**
  - D-1, monthly invoices (Finance);
  - D-4: Q-03, the response window (spec recommends 48 h). Q-05 is implemented as recommended, pending the OK
    above.

## Migrations added (all run locally, not on production)

- `users.guide_area_beta` (now unused)
- Tour and voucher fields from 13f8fde5:
  - voucher `public_id` G-nnnn;
  - Paid/Sent;
  - `duplicated_from_id`.
- `personal_access_tokens.expires_at`
- `2026_11_18_110000`: `bookings.cancel_reason`, `cancelled_by`, `cancelled_at`
- `2026_11_18_110001`: `calendar_schedule.source` (manual or ical)
- `2026_11_18_110002`: `reviews.seen_by_guide_at`. Existing reviews are backfilled as seen.
- `2026_11_23_100000`: account verification columns (Account & security)
- `2026_11_23_120000`: `booking_access_tokens`, `booking_access_events` (guest booking links)

## Testing

- **Tests:** `tests/Feature/Guide/Area/*`, including Bookings (11), Calendar (8), DashboardPages (7), Profile,
  Tours, Vouchers, ApiTokens and EntryPoints.
  - Tests for the old pages set `config(['guide_area.enabled' => false])`.
  - Tests run against the dev database, so they must `use DatabaseTransactions`.
- **Hands-on test (18 Nov):** done on cag.local as local user 3 ("Catch"). Everything passed after the fixes in
  76b48fe0, and all test data was cleaned up. German copy and phone width were checked.
- **Local safety rules:**
  - Never type credentials on cag.local; the user logs in themselves.
  - Don't upload photos locally. `MEDIA_STORAGE_DISK=do_spaces` is the live bucket.
  - `BookingAcceptedListener` returns early on local, so booking emails are only testable on staging.
  - Avoid controls that open a JS confirm dialog in Chrome automation.
  - If Chrome screenshots time out, the window is probably minimized; DOM clicks through `javascript_tool` still
    work.
  - `npm run dev` rewrites `public/js/app.js`, `public/css/app.css` and their `mix-manifest.json` hashes. Restore
    them before committing; only `guide-area.css` should change.
- **Staging checks still owed:**
  - booking emails after accept, decline and cancel;
  - photo and certificate upload;
  - Go live, Deactivate, Duplicate.

## Known limitations of what's built

- The tours' Fix links on To-dos open the tour editor's first step, not the exact field.
- When accepting fails because the day is taken, the card still shows Accept. There is no "Day no longer available"
  state yet; only an error toast.
- The 2025 invoice list is empty locally, because every existing invoice was emailed in May 2026.
- To-do checks not implemented:
  - "Excluded services": no field exists;
  - "not VAT registered": belongs to Account & security.
- Reviews keep the old scale. There is no 10-point, three-category form and no migration (Q-15).

## Missing compared with the spec (by build-order phase, chapter 16)

**Phase 0, foundations**
- Notification service with templates, a dedupe log (`notifications` table, key = type + entity + step) and
  internal routing. Today's emails are the old ones; none of chapter 3 (N-01 … N-41) has been reviewed or rebuilt.
- Data migration (chapter 15) for account type, `display_name` / `about_text`, the review scale and importing
  blocked days into `calendar_blocks`. The build reuses the existing tables instead (`calendar_schedule`,
  `finance_items`).

**Phase 1, bookings and calendar**
- Response window per Q-03 (48 h, or until 12:00 the day before when the tour is under 72 h away). It is blocked
  on D-4; today's `expires_at` logic is unchanged.
- Background jobs as the spec defines them. Existing scheduled commands overlap: `update:booking-status`,
  `bookings:send-guide-reminders(-12hrs)`, `bookings:send-guest-reviews`, `bookings:create-automatic-reviews` and
  `ical:sync-feeds`. Check each against J-01 … J-06 before adding new ones.
- Calendar "tour rules" drawer and per-date rule overrides.
- Conflict flag when an imported busy day hits a confirmed booking (J-06 / N-27, N-28).
- REST endpoints for bookings, calendar, overview, to-dos, reviews and invoices (D-3).

**Phase 2, vouchers and tours**
- Voucher PDF emailed on ticking "Sent" (Q-11, N-17), and notifications N-13, N-14 and N-16.
- Tour editor redesign is out of scope (chapter 17).

**Phase 3, profile, account and optimization**
- ~~Account & security page (chapter 13)~~: done 23 Nov, see above (payout bank details left out on purpose).
- **Account type model (2.4) end to end:** company profile fields (founded in, crew), "About us" label, company
  optimization checks.
- Certificate admin review flow (Q-04) and certificate expiry job J-11.
- Weekly optimization digest J-09 / N-38 (P2).

**Phase 4, reviews, invoices, sync and support**
- 10-point, three-category guest review form; jobs J-04 and J-05 per Q-01 and Q-02; N-18 … N-23.
- Monthly invoices with PDF/ZIP, job J-07, due and overdue reminders J-08, and N-24 … N-26. All blocked on D-1.
- Google calendar OAuth first, then Outlook (P1). Only iCal URL import and export exist.
- **Contact support modal (chapter 14):**
  - WhatsApp link and support hours;
  - topic, message (at least 20 characters) and up to 3 attachments of 10 MB;
  - ticket number on confirmation;
  - `POST /api/guide/support-tickets`;
  - N-39 and N-40.
  - Q-13 (support inbox/helpdesk, WhatsApp number, hours) needs an answer first.

**Phase 5, launch**
- Email copy sign-off (chapter 3), an end-to-end request → accept → completed → review → invoice test, and
  removing the old dashboard 4 weeks after rollout.
- P1/P2 backlog: CSV export, review replies, N-12, N-15, N-22, N-37, N-38.

**Open product questions that block work:** Q-01, Q-02, Q-03, Q-04, Q-11, Q-13, Q-14 (how long the guest's
contact details stay visible), Q-15, Q-17. D-1 needs Finance.

## Where to start next

Possible work that isn't blocked:
Agreed order (23 Nov): ~~Account & security~~ (done) → ~~guest booking access Phase 1~~ (done) →
REST endpoints for the six newer pages and the "Day no longer available" state → Contact support, once Q-13 is
answered. Still unblocked too: the calendar tour-rules drawer and a notification dedupe log.

