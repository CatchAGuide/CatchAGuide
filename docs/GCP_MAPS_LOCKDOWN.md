# Google Maps / Places — GCP key lockdown (post Leaflet migration)

After deploying free Leaflet maps, Dynamic Maps usage should drop to ~€0. Places Autocomplete remains on Google and loads **only when a search input is focused**.

## Enable / disable APIs on the Catch A Guide key

In [Google Cloud Console](https://console.cloud.google.com/) → APIs & Services → Credentials → your browser key:

### Keep enabled
- **Places API** (or Places API (New) if you migrate later) — header + admin autocomplete
- **Geocoding API** — only if server helpers / artisan commands still need it (`LOCATION_RESOLVER_GOOGLE_FALLBACK` should stay `false` for normal search)

### Restrict / expect unused
- **Maps JavaScript API** — still required as a host for the Places Autocomplete widget, but **do not create `google.maps.Map` instances** in production UI (Leaflet owns map canvases). Dynamic Maps SKU should stay near zero.
- **Maps Embed / Static / Directions / Distance Matrix** — leave disabled unless you add those features

### Application restrictions
- HTTP referrers: production + staging domains only (`*.catchaguide.com`, local Laragon host if needed)
- Do **not** use an unrestricted key in production

## Budgets & alerts
1. Billing → Budgets & alerts → create a monthly budget (e.g. €20–€40)
2. Alert at 50% / 90% / 100%
3. After 1–2 weeks, open Billing → Reports → Group by SKU and confirm **Dynamic Maps** is ~€0 and residual spend is Places-related

## Env checklist
```
MAPS_ENGINE=leaflet
GOOGLE_MAPS_API_KEY=...          # Places only in practice
# GOOGLE_MAPS_MAP_ID no longer required for public maps
LOCATION_RESOLVER_GOOGLE_FALLBACK=false
```

## Rollback
Set `MAPS_ENGINE=google` is reserved for a future flag; current code always serves Leaflet components. To roll back, restore the previous `maps-utils` + sitewide Maps JS from git.
