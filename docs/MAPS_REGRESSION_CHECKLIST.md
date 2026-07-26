# Leaflet maps regression checklist

Use after deploy. Mark each row only when verified on desktop **and** mobile.

## A. Listing maps
- [ ] Guidings index: open map modal from CTA; map fills viewport; pins + clusters
- [ ] Guidings: gray nearby pins when “additional” tours present; fitBounds uses primary only
- [ ] Guidings: marker popup shows image, title link, location, price
- [ ] Guidings: AJAX filters call `updateMapWithGuidings` and markers refresh
- [ ] Vacations index: map modal opens; markers for results
- [ ] Category / destination country: map modal works
- [ ] Vacation country / pillar: country map modal + popups

## B. Product / PDP maps
- [ ] Guiding PDP (`newIndex`): map ~400px, zoom 10, single pin, lazy
- [ ] Legacy guiding `show`: marker click opens Bootstrap modal
- [ ] Camp `vacations/v2`: map renders
- [ ] Vacation `show`: map renders
- [ ] Trip offer: `#tripOfferMap` via `x-maps.product` (no unpkg Leaflet)

## C. Search / Places
- [ ] No `maps.googleapis.com` request on page load until a search field is focused
- [ ] Header desktop/mobile autocomplete fills lat/lng/city/country/region
- [ ] Geosearch hidden fields (`bounds_*`, `country_short`, `place_types`) populate
- [ ] `POST /guidings/search-place-log` still fires on place select
- [ ] Admin/seller location fields that load their own Places script still work

## D. Network / console
- [ ] Map pages: Leaflet + Carto tiles; no Dynamic Map creation
- [ ] Console clean of Google Maps / Leaflet load errors on map pages
