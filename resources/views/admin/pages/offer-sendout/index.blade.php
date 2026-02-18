@extends('admin.layouts.app')

@section('title', 'Offer Sendout')

@section('custom_style')
<link href="{{ asset('css/admin-offer-sendout.css') }}" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify@4.17.9/dist/tagify.css" rel="stylesheet">
@endsection

@section('content')
<div class="side-app offer-sendout-page">
    <div class="main-container container-fluid">

        <!-- PAGE-HEADER -->
        <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h1 class="page-title mb-1">Offer Sendout</h1>
                <p class="text-muted small mb-0">Build a package offer and send a beautiful email to the customer (CC admin)</p>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <a href="{{ route('admin.offer-sendout.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fe fe-arrow-left me-1"></i>Back to List
                </a>
            </div>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.offer-sendout.index') }}">Custom Camp Offers</a></li>
                <li class="breadcrumb-item active" aria-current="page">Create Offer</li>
            </ol>
        </div>
        <!-- PAGE-HEADER END -->

        <div class="row g-4">
            {{-- Left: Form --}}
            <div class="col-12 col-lg-5 col-xl-4">
                <div class="card border-0 shadow-sm offer-sendout-form-card">
                    <div class="card-header border-0 bg-transparent py-3">
                        <h5 class="card-title mb-0"><i class="fe fe-edit-2 me-2"></i>Offer details</h5>
                    </div>
                    <div class="card-body">
                        <form id="offer-sendout-form" class="offer-sendout-form" autocomplete="off">
                            @csrf

                            {{-- Recipient + Language in one row --}}
                            <div class="row g-2 mb-4 align-items-end">
                                <div class="col-12 col-md-7">
                                    <label class="form-label fw-semibold mb-1">Recipient</label>
                                    <div class="btn-group w-100 mb-2" role="group">
                                        <input type="radio" class="btn-check" name="recipient_type" id="recipient_customer" value="customer" checked>
                                        <label class="btn btn-outline-primary btn-sm" for="recipient_customer">Select customer</label>
                                        <input type="radio" class="btn-check" name="recipient_type" id="recipient_manual" value="manual">
                                        <label class="btn btn-outline-primary btn-sm" for="recipient_manual">Enter contact</label>
                                    </div>
                                    <div id="recipient-customer-wrap" class="recipient-wrap">
                                        <select name="customer_id" id="customer_id" class="form-select form-select-sm">
                                            <option value="">— Select customer —</option>
                                            @foreach($customers as $c)
                                            <option value="{{ $c->id }}" data-name="{{ $c->firstname }} {{ $c->lastname }}" data-email="{{ $c->email }}">{{ $c->firstname }} {{ $c->lastname }} ({{ $c->email }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div id="recipient-manual-wrap" class="recipient-wrap d-none">
                                        <input type="text" name="manual_name" id="manual_name" class="form-control form-control-sm mb-2" placeholder="Name">
                                        <input type="email" name="manual_email" id="manual_email" class="form-control form-control-sm mb-2" placeholder="Email *">
                                        <input type="text" name="manual_phone" id="manual_phone" class="form-control form-control-sm" placeholder="Phone">
                                    </div>
                                </div>
                                <div class="col-12 col-md-5">
                                    <label class="form-label fw-semibold mb-1">{{ __('admin.offer_sendout_language') }}</label>
                                    <select name="locale" id="locale" class="form-select form-select-sm">
                                        <option value="en" {{ app()->getLocale() === 'en' ? 'selected' : '' }}>🇬🇧 English</option>
                                        <option value="de" {{ app()->getLocale() === 'de' ? 'selected' : '' }}>🇩🇪 Deutsch</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Introduction text --}}
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Introduction text</label>
                                <textarea name="introduction_text" id="introduction_text" class="form-control" rows="5" placeholder="{{ __('emails.offer_sendout_intro') }} {{ __('emails.offer_sendout_intro_secondary') }}"></textarea>
                                <small class="text-muted">Leave empty to use default translation.</small>
                            </div>


                            <div id="offer-blocks-container">
                                {{-- Offer 1 --}}
                                <div class="offer-block card border mb-3" data-offer-index="0">
                                    <div class="card-header py-2 bg-light d-flex justify-content-between align-items-center">
                                        <span class="fw-semibold small">Offer 1</span>
                                        <button type="button" class="btn-remove-offer btn btn-outline-danger btn-sm d-none" aria-label="Remove offer">Remove</button>
                                    </div>
                                    <div class="card-body py-3">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold small">Camp (optional)</label>
                                            <select name="offers[0][camp_id]" id="offer_0_camp_id" class="form-select form-select-sm offer-camp-select">
                                                <option value="">— Camp (optional) —</option>
                                                @foreach($camps as $camp)
                                                <option value="{{ $camp->id }}">{{ $camp->title }}</option>
                                                @endforeach
                                            </select>
                                            <label class="form-label small text-muted mb-1 mt-2">Accommodations</label>
                                            <input type="text" id="offer_0_accommodation_tagify" class="form-control form-control-sm mb-1" placeholder="Select accommodations" aria-label="Accommodations">
                                            <input type="hidden" name="offers[0][accommodation_ids]" id="offer_0_accommodation_ids" value="">
                                            <div id="offer_0_accommodation_prices" class="component-prices-container mb-2"></div>
                                            <label class="form-label small text-muted mb-1 mt-2">Boats</label>
                                            <input type="text" id="offer_0_boat_tagify" class="form-control form-control-sm mb-1" placeholder="Select boats" aria-label="Boats">
                                            <input type="hidden" name="offers[0][boat_ids]" id="offer_0_boat_ids" value="">
                                            <div id="offer_0_boat_prices" class="component-prices-container mb-2"></div>
                                            <label class="form-label small text-muted mb-1 mt-2">Guidings</label>
                                            <input type="text" id="offer_0_guiding_tagify" class="form-control form-control-sm mb-1" placeholder="Select guidings" aria-label="Guidings">
                                            <input type="hidden" name="offers[0][guiding_ids]" id="offer_0_guiding_ids" value="">
                                            <div id="offer_0_guiding_prices" class="component-prices-container mb-2"></div>
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label fw-semibold small">Details</label>
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <label class="form-label small text-muted mb-0">Date from</label>
                                                    <input type="date" name="offers[0][date_from]" id="offer_0_date_from" class="form-control form-control-sm">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small text-muted mb-0">Date to</label>
                                                    <input type="date" name="offers[0][date_to]" id="offer_0_date_to" class="form-control form-control-sm">
                                                </div>
                                                <div class="col-6">
                                                    <input type="text" name="offers[0][number_of_persons]" id="offer_0_number_of_persons" class="form-control form-control-sm" placeholder="No. of persons">
                                                </div>
                                            </div>
                                            <textarea name="offers[0][additional_info]" id="offer_0_additional_info" class="form-control form-control-sm mt-2" rows="2" placeholder="Additional information"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" id="add-offer-btn" class="btn btn-outline-primary btn-sm mb-4">
                                <i class="fe fe-plus me-1"></i>Add another offer
                            </button>

                            <template id="offer-block-template">
                                <div class="offer-block card border mb-3" data-offer-index="__INDEX__">
                                    <div class="card-header py-2 bg-light d-flex justify-content-between align-items-center">
                                        <span class="fw-semibold small">Offer __NUM__</span>
                                        <button type="button" class="btn-remove-offer btn btn-outline-danger btn-sm" aria-label="Remove offer">Remove</button>
                                    </div>
                                    <div class="card-body py-3">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold small">Camp (optional)</label>
                                            <select name="offers[__INDEX__][camp_id]" id="offer___INDEX___camp_id" class="form-select form-select-sm offer-camp-select">
                                                <option value="">— Camp (optional) —</option>
                                                @foreach($camps as $camp)
                                                <option value="{{ $camp->id }}">{{ $camp->title }}</option>
                                                @endforeach
                                            </select>
                                            <label class="form-label small text-muted mb-1 mt-2">Accommodations</label>
                                            <input type="text" id="offer___INDEX___accommodation_tagify" class="form-control form-control-sm mb-1" placeholder="Select accommodations" aria-label="Accommodations">
                                            <input type="hidden" name="offers[__INDEX__][accommodation_ids]" id="offer___INDEX___accommodation_ids" value="">
                                            <div id="offer___INDEX___accommodation_prices" class="component-prices-container mb-2"></div>
                                            <label class="form-label small text-muted mb-1 mt-2">Boats</label>
                                            <input type="text" id="offer___INDEX___boat_tagify" class="form-control form-control-sm mb-1" placeholder="Select boats" aria-label="Boats">
                                            <input type="hidden" name="offers[__INDEX__][boat_ids]" id="offer___INDEX___boat_ids" value="">
                                            <div id="offer___INDEX___boat_prices" class="component-prices-container mb-2"></div>
                                            <label class="form-label small text-muted mb-1 mt-2">Guidings</label>
                                            <input type="text" id="offer___INDEX___guiding_tagify" class="form-control form-control-sm mb-1" placeholder="Select guidings" aria-label="Guidings">
                                            <input type="hidden" name="offers[__INDEX__][guiding_ids]" id="offer___INDEX___guiding_ids" value="">
                                            <div id="offer___INDEX___guiding_prices" class="component-prices-container mb-2"></div>
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label fw-semibold small">Details</label>
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <label class="form-label small text-muted mb-0">Date from</label>
                                                    <input type="date" name="offers[__INDEX__][date_from]" id="offer___INDEX___date_from" class="form-control form-control-sm">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small text-muted mb-0">Date to</label>
                                                    <input type="date" name="offers[__INDEX__][date_to]" id="offer___INDEX___date_to" class="form-control form-control-sm">
                                                </div>
                                                <div class="col-6">
                                                    <input type="text" name="offers[__INDEX__][number_of_persons]" id="offer___INDEX___number_of_persons" class="form-control form-control-sm" placeholder="No. of persons">
                                                </div>
                                                <div class="col-6">
                                                    <input type="text" name="offers[__INDEX__][price]" id="offer___INDEX___price" class="form-control form-control-sm" placeholder="Price (e.g. 1,500 €)">
                                                </div>
                                            </div>
                                            <textarea name="offers[__INDEX__][additional_info]" id="offer___INDEX___additional_info" class="form-control form-control-sm mt-2" rows="2" placeholder="Additional information"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            {{-- Free text --}}
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Message (free text)</label>
                                <textarea name="free_text" id="free_text" class="form-control" rows="4" placeholder="Personal message to the customer…"></textarea>
                            </div>

                            <button type="submit" id="offer-send-btn" class="btn btn-primary w-100">
                                <i class="fe fe-send me-2"></i>Send offer email (CC admin)
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Right: Live email preview --}}
            <div class="col-12 col-lg-7 col-xl-8">
                <div class="card border-0 shadow-sm offer-sendout-preview-card h-100">
                    <div class="card-header border-0 bg-transparent py-3 d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0"><i class="fe fe-mail me-2"></i>Email preview</h5>
                        <span class="badge bg-success-subtle text-success small">Live</span>
                    </div>
                    <div class="card-body p-0 d-flex flex-column flex-grow-1 overflow-hidden">
                        <div id="offer-preview-wrapper" class="offer-preview-wrapper flex-grow-1 overflow-auto bg-light">
                            <div id="offer-preview-content" class="offer-preview-content mx-auto">
                                <p class="text-muted text-center py-5 px-3">Fill in the form to see the email preview.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="offer-sendout-toast" class="toast align-items-center text-bg-success border-0 position-fixed bottom-0 end-0 m-3" role="alert" aria-live="assertive" aria-atomic="true" style="z-index: 1060;">
    <div class="d-flex">
        <div class="toast-body" id="offer-sendout-toast-body">Offer sent successfully.</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
</div>

{{-- Confirmation modal: avoid accidental send --}}
<div class="modal fade" id="offer-send-confirm-modal" tabindex="-1" aria-labelledby="offerSendConfirmLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="offerSendConfirmLabel">
                    <i class="fe fe-send me-2"></i>Send offer email
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Send this offer to <strong id="offer-send-confirm-email"></strong>?</p>
                <p class="text-muted small mb-0 mt-2">A copy will be sent to the admin (CC).</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="offer-send-confirm-btn">
                    <i class="fe fe-send me-2"></i>Send email
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js_after')
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify@4.17.9/dist/tagify.min.js"></script>
<script>
(function () {
    'use strict';

    const form = document.getElementById('offer-sendout-form');
    const previewContent = document.getElementById('offer-preview-content');
    const previewUrl = '{{ route("admin.offer-sendout.preview") }}';
    const sendUrl = '{{ route("admin.offer-sendout.send") }}';
    const campOptionsUrl = '{{ route("admin.offer-sendout.camp-options", ["camp" => "__CAMP__"]) }}';
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || form?.querySelector('input[name="_token"]')?.value;

    const componentData = {
        accommodations: @json($accommodationsData ?? []),
        boats: @json($boatsData ?? []),
        guidings: @json($guidingsData ?? [])
    };

    const fullOptions = {
        accommodations: @json($fullOptionsAccommodations),
        boats: @json($fullOptionsBoats),
        guidings: @json($fullOptionsGuidings)
    };

    let previewDebounce = null;
    let nextOfferIndex = 1;
    const tagifyByBlock = {};

    function syncTagifyHidden(tagify, hiddenEl) {
        if (!hiddenEl || !tagify) return;
        const ids = (tagify.value || []).map(function (t) { return t.id || t.value; }).filter(Boolean);
        hiddenEl.value = ids.join(',');
    }

    function updateComponentPrices(blockIndex, type, tagify, whitelist) {
        const container = document.getElementById('offer_' + blockIndex + '_' + type + '_prices');
        if (!container) return;
        container.innerHTML = '';
        const tags = tagify && tagify.value ? tagify.value : [];
        const list = type === 'accommodation' ? fullOptions.accommodations : (type === 'boat' ? fullOptions.boats : fullOptions.guidings);
        const dataList = type === 'accommodation' ? componentData.accommodations : (type === 'boat' ? componentData.boats : componentData.guidings);
        tags.forEach(function (tag) {
            const id = tag.id || (tag.value && String(tag.value).match(/^#?\d+/) ? String(tag.value).replace(/^#?(\d+).*/, '$1') : null) || tag.value;
            const title = tag.value || (list && list.find(function (i) { return String(i.id) === String(id); }))?.value || 'Item ' + id;
            const displayTitle = typeof title === 'string' ? title.replace(/^\(\d+\)\s*\|\s*/, '').trim() : (title || '');
            
            // Get component data (price and capacity) - check whitelist first, then componentData
            let componentInfo = null;
            let defaultPrice = 0;
            let capacity = 0;
            
            // Check whitelist first (for camp-filtered items)
            if (tagify && tagify.settings && tagify.settings.whitelist) {
                const whitelistItem = tagify.settings.whitelist.find(function (i) { return String(i.id) === String(id); });
                if (whitelistItem) {
                    defaultPrice = whitelistItem.price || 0;
                    capacity = whitelistItem.capacity || 0;
                    componentInfo = whitelistItem;
                }
            }
            
            // Fallback to componentData
            if (!componentInfo) {
                componentInfo = dataList.find(function (item) { return String(item.id) === String(id); });
                if (componentInfo) {
                    defaultPrice = componentInfo.price || 0;
                    capacity = componentInfo.capacity || 0;
                }
            }
            
            const row = document.createElement('div');
            row.className = 'input-group input-group-sm mb-1 component-price-row';
            row.setAttribute('data-component-id', id);
            row.setAttribute('data-title', displayTitle || '');
            row.setAttribute('data-capacity', capacity);
            row.innerHTML = ''
                + '<label class="input-group-text flex-grow-0 text-truncate" style="max-width: 140px;"'
                + ' title="' + (displayTitle || '').replace(/"/g, '&quot;') + '">'
                + (displayTitle || 'Price')
                + '</label>'
                + '<span class="input-group-text">€</span>'
                + '<input type="number" class="form-control form-control-sm component-price-input"'
                + ' data-id="' + id + '" data-type="' + type + '"'
                + ' data-title="' + (displayTitle || '').replace(/"/g, '&quot;') + '"'
                + ' step="0.01" min="0" placeholder="0.00" value="' + defaultPrice + '">'
                + '<span class="input-group-text">Qty</span>'
                + '<input type="number" class="form-control form-control-sm component-qty-input"'
                + ' data-id="' + id + '" data-type="' + type + '"'
                + ' data-capacity="' + capacity + '"'
                + ' step="1" min="1" placeholder="1" value="1">'
                + '<span class="input-group-text">Days</span>'
                + '<input type="number" class="form-control form-control-sm component-days-input"'
                + ' data-id="' + id + '" data-type="' + type + '"'
                + ' step="1" min="1" placeholder="1" value="1">';
            container.appendChild(row);
        });
        container.querySelectorAll('.component-price-input, .component-qty-input, .component-days-input').forEach(function (el) {
            el.addEventListener('input', updatePreview);
            el.addEventListener('change', updatePreview);
        });
        
        // Auto-calculate quantities based on number of persons if set
        autoCalculateQuantities(blockIndex);
    }
    
    function autoCalculateQuantities(blockIndex) {
        const block = document.querySelector('.offer-block[data-offer-index="' + blockIndex + '"]');
        if (!block) return;
        
        const personsInput = block.querySelector('input[name*="[number_of_persons]"]');
        if (!personsInput) return;
        
        const numberOfPersons = parseInt(personsInput.value) || 0;
        if (numberOfPersons <= 0) return;
        
        // Update quantities for all components in this block
        block.querySelectorAll('.component-qty-input').forEach(function (qtyInput) {
            const capacity = parseInt(qtyInput.getAttribute('data-capacity')) || 0;
            if (capacity > 0) {
                // Calculate required quantity: ceil(numberOfPersons / capacity)
                const requiredQty = Math.ceil(numberOfPersons / capacity);
                qtyInput.value = requiredQty;
            }
        });
    }

    function syncAllTagify() {
        Object.keys(tagifyByBlock).forEach(function (idx) {
            const t = tagifyByBlock[idx];
            const block = document.querySelector('.offer-block[data-offer-index="' + idx + '"]');
            if (!block) return;
            if (t.acc) syncTagifyHidden(t.acc, block.querySelector('input[name*="[accommodation_ids]"]'));
            if (t.boat) syncTagifyHidden(t.boat, block.querySelector('input[name*="[boat_ids]"]'));
            if (t.guiding) syncTagifyHidden(t.guiding, block.querySelector('input[name*="[guiding_ids]"]'));
        });
    }

    function getFormData() {
        syncAllTagify();
        const obj = {
            recipient_type: form.querySelector('[name="recipient_type"]:checked')?.value || 'customer',
            customer_id: form.querySelector('[name="customer_id"]')?.value || '',
            manual_name: form.querySelector('[name="manual_name"]')?.value || '',
            manual_email: form.querySelector('[name="manual_email"]')?.value || '',
            manual_phone: form.querySelector('[name="manual_phone"]')?.value || '',
            locale: form.querySelector('[name="locale"]')?.value || 'en',
            introduction_text: form.querySelector('[name="introduction_text"]')?.value || '',
            free_text: form.querySelector('[name="free_text"]')?.value || '',
            offers: []
        };
        document.querySelectorAll('#offer-blocks-container .offer-block').forEach(function (block) {
            var idx = block.getAttribute('data-offer-index');
            var campEl = block.querySelector('select[name*="[camp_id]"]');
            var accEl = block.querySelector('input[name*="[accommodation_ids]"]');
            var boatEl = block.querySelector('input[name*="[boat_ids]"]');
            var guidingEl = block.querySelector('input[name*="[guiding_ids]"]');
            var dateFromEl = block.querySelector('input[name*="[date_from]"]');
            var dateToEl = block.querySelector('input[name*="[date_to]"]');
            var personsEl = block.querySelector('input[name*="[number_of_persons]"]');
            var priceEl = block.querySelector('input[name*="[price]"]');
            var additionalEl = block.querySelector('textarea[name*="[additional_info]"]');
            var accPrices = [];
            var boatPrices = [];
            var guidingPrices = [];

            function collectComponentPrices(containerId, targetArray) {
                var rows = block.querySelectorAll('#offer_' + idx + '_' + containerId + ' .component-price-row');
                rows.forEach(function (row) {
                    var priceEl = row.querySelector('.component-price-input');
                    var qtyEl = row.querySelector('.component-qty-input');
                    var daysEl = row.querySelector('.component-days-input');
                    if (!priceEl) return;
                    var title = priceEl.dataset.title || row.getAttribute('data-title') || '';
                    var id = priceEl.dataset.id || row.getAttribute('data-component-id') || '';
                    var price = parseFloat(priceEl.value) || 0;
                    var qty = parseFloat(qtyEl && qtyEl.value ? qtyEl.value : 1) || 1;
                    var days = parseFloat(daysEl && daysEl.value ? daysEl.value : 1) || 1;
                    if (id) {
                        targetArray.push({
                            id: id,
                            title: title,
                            price: price,
                            qty: qty,
                            days: days
                        });
                    }
                });
            }

            collectComponentPrices('accommodation_prices', accPrices);
            collectComponentPrices('boat_prices', boatPrices);
            collectComponentPrices('guiding_prices', guidingPrices);
            obj.offers.push({
                camp_id: (campEl && campEl.value) ? campEl.value : null,
                accommodation_ids: accEl ? accEl.value : '',
                boat_ids: boatEl ? boatEl.value : '',
                guiding_ids: guidingEl ? guidingEl.value : '',
                accommodation_prices: accPrices,
                boat_prices: boatPrices,
                guiding_prices: guidingPrices,
                date_from: dateFromEl ? dateFromEl.value : '',
                date_to: dateToEl ? dateToEl.value : '',
                number_of_persons: personsEl ? personsEl.value : '',
                price: priceEl ? priceEl.value : '',
                additional_info: additionalEl ? additionalEl.value : ''
            });
        });
        return obj;
    }

    function updatePreview() {
        clearTimeout(previewDebounce);
        previewDebounce = setTimeout(function () {
            fetch(previewUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(getFormData())
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.html) {
                    previewContent.innerHTML = data.html;
                    previewContent.querySelectorAll('a').forEach(function (a) { a.setAttribute('target', '_blank'); });
                }
            })
            .catch(function () {
                previewContent.innerHTML = '<p class="text-muted text-center py-5 px-3">Preview could not be loaded.</p>';
            });
        }, 300);
    }
    window.updatePreview = updatePreview;

    function showToast(message, success) {
        const toastEl = document.getElementById('offer-sendout-toast');
        const body = document.getElementById('offer-sendout-toast-body');
        if (!toastEl || !body) return;
        body.textContent = message;
        toastEl.classList.remove('text-bg-success', 'text-bg-danger');
        toastEl.classList.add(success ? 'text-bg-success' : 'text-bg-danger');
        const toast = new window.bootstrap.Toast(toastEl);
        toast.show();
    }

    function buildTagifyDropdownItem(item) {
        var isConnected = item.connected === true;
        var cls = 'tagify__dropdown__item' + (isConnected ? ' tagify__dropdown__item--connected' : '');
        var safeValue = (item.value || '').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        var idHtml = item.id
            ? '<span class="tfi-id">' + item.id + '</span><span class="tfi-sep">|</span>'
            : '';
        var nameHtml = '<span class="tfi-name"><strong>' + (item.value || '') + '</strong></span>';
        var campHtml = item.camp_name
            ? '<span class="tfi-sep">|</span><span class="tfi-camp">' + item.camp_name + '</span>'
            : '';
        return '<div class="' + cls + '" data-value="' + safeValue + '">' + idHtml + nameHtml + campHtml + '</div>';
    }

    function initTagify(inputEl, hiddenEl, whitelist, blockIndex, type) {
        if (!inputEl || typeof Tagify === 'undefined') return null;
        var t = new Tagify(inputEl, {
            whitelist: whitelist || [],
            enforceWhitelist: true,
            dropdown: {
                enabled: 1,
                position: 'all',
                maxItems: 100,
                closeOnSelect: false,
                highlightFirst: true,
                mapValueTo: 'value',
                searchKeys: ['value', 'id', 'camp_name'],
            },
            templates: {
                dropdownItem: buildTagifyDropdownItem
            },
            duplicates: false,
            tagTemplate: function (tagData) {
                return '<tag title="' + (tagData.value || '').replace(/"/g, '&quot;') + '">' + (tagData.value || tagData.title || '') + ' <x title="remove"></x></tag>';
            },
            transformTag: function (tagData) {
                tagData.id = tagData.id || tagData.value;
            }
        });
        t.on('focus', function () {
            if (t.settings.whitelist && t.settings.whitelist.length && t.dropdown && typeof t.dropdown.show === 'function') t.dropdown.show();
        });
        t.on('click', function () {
            if (t.settings.whitelist && t.settings.whitelist.length && t.dropdown && typeof t.dropdown.show === 'function') t.dropdown.show();
        });
        t.on('change', function () {
            syncTagifyHidden(t, hiddenEl);
            if (blockIndex !== undefined && type) updateComponentPrices(blockIndex, type, t, whitelist);
            updatePreview();
        });
        return t;
    }

    function setTagifyWhitelist(tagify, items, fallbackOptions) {
        if (!tagify) return;
        var list = (items || []).map(function (i) {
            var campName = i.camp_name || '';
            // For unconnected items, fall back to the fullOptions camp_name
            // so items that belong to OTHER camps still show their camp name.
            if (!campName && fallbackOptions) {
                var fb = fallbackOptions.find(function (o) { return String(o.id) === String(i.id); });
                if (fb) campName = fb.camp_name || '';
            }
            return { id: i.id, value: i.value, connected: i.connected === true, price: i.price || 0, capacity: i.capacity || 0, camp_name: campName };
        });
        tagify.settings.whitelist = list;
        if (typeof tagify.removeAllTags === 'function') tagify.removeAllTags();
    }
    
    function updateComponentDataFromWhitelist(blockIndex, type, tagify) {
        if (!tagify || !tagify.settings || !tagify.settings.whitelist) return;
        
        const dataList = type === 'accommodation' ? componentData.accommodations : (type === 'boat' ? componentData.boats : componentData.guidings);
        const whitelist = tagify.settings.whitelist;
        
        // Update componentData with whitelist data (for camp-filtered items)
        whitelist.forEach(function (item) {
            const existing = dataList.find(function (d) { return String(d.id) === String(item.id); });
            if (!existing && item.id) {
                dataList.push({
                    id: item.id,
                    title: item.value,
                    price: item.price || 0,
                    capacity: item.capacity || 0
                });
            } else if (existing && item.price !== undefined) {
                existing.price = item.price || 0;
                existing.capacity = item.capacity || 0;
            }
        });
    }

    function initBlockTagify(blockIndex) {
        var block = document.querySelector('.offer-block[data-offer-index="' + blockIndex + '"]');
        if (!block || typeof Tagify === 'undefined') return;
        var accInput = document.getElementById('offer_' + blockIndex + '_accommodation_tagify');
        var accHidden = document.getElementById('offer_' + blockIndex + '_accommodation_ids');
        var boatInput = document.getElementById('offer_' + blockIndex + '_boat_tagify');
        var boatHidden = document.getElementById('offer_' + blockIndex + '_boat_ids');
        var guidingInput = document.getElementById('offer_' + blockIndex + '_guiding_tagify');
        var guidingHidden = document.getElementById('offer_' + blockIndex + '_guiding_ids');
        if (!accInput || !accHidden || !boatInput || !boatHidden || !guidingInput || !guidingHidden) return;
        if (tagifyByBlock[blockIndex]) {
            tagifyByBlock[blockIndex].acc.destroy();
            tagifyByBlock[blockIndex].boat.destroy();
            tagifyByBlock[blockIndex].guiding.destroy();
        }
        tagifyByBlock[blockIndex] = {
            acc: initTagify(accInput, accHidden, fullOptions.accommodations, blockIndex, 'accommodation'),
            boat: initTagify(boatInput, boatHidden, fullOptions.boats, blockIndex, 'boat'),
            guiding: initTagify(guidingInput, guidingHidden, fullOptions.guidings, blockIndex, 'guiding')
        };
        updateComponentPrices(blockIndex, 'accommodation', tagifyByBlock[blockIndex].acc, fullOptions.accommodations);
        updateComponentPrices(blockIndex, 'boat', tagifyByBlock[blockIndex].boat, fullOptions.boats);
        updateComponentPrices(blockIndex, 'guiding', tagifyByBlock[blockIndex].guiding, fullOptions.guidings);
    }

    function wireDateFromTo(blockIndex) {
        var block = document.querySelector('.offer-block[data-offer-index="' + blockIndex + '"]');
        if (!block) return;
        var dateFromEl = block.querySelector('input[name*="[date_from]"]');
        var dateToEl = block.querySelector('input[name*="[date_to]"]');
        if (!dateFromEl || !dateToEl || dateFromEl._dateWire) return;
        dateFromEl._dateWire = true;

        function updateDateToMin() {
            var val = dateFromEl.value;
            if (!val) {
                dateToEl.removeAttribute('min');
                return;
            }
            var fromDate = new Date(val + 'T12:00:00');
            if (isNaN(fromDate.getTime())) return;
            fromDate.setDate(fromDate.getDate() + 1);
            var minStr = fromDate.toISOString().slice(0, 10);
            dateToEl.setAttribute('min', minStr);
            if (dateToEl.value && dateToEl.value < minStr) {
                dateToEl.value = minStr;
                updatePreview();
            }
        }

        dateFromEl.addEventListener('change', updateDateToMin);
        dateFromEl.addEventListener('input', updateDateToMin);
        updateDateToMin();
    }

    function wireCampChange(blockIndex) {
        var campSelect = document.getElementById('offer_' + blockIndex + '_camp_id');
        if (!campSelect || campSelect._offerCampWired) return;
        campSelect._offerCampWired = true;
        campSelect.addEventListener('change', function () {
            var campId = this.value;
            var t = tagifyByBlock[blockIndex];
            if (!t) return;
            if (!campId) {
                setTagifyWhitelist(t.acc, fullOptions.accommodations);
                setTagifyWhitelist(t.boat, fullOptions.boats);
                setTagifyWhitelist(t.guiding, fullOptions.guidings);
                updatePreview();
                return;
            }
            fetch(campOptionsUrl.replace('__CAMP__', campId), { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    setTagifyWhitelist(t.acc, data.accommodations || [], fullOptions.accommodations);
                    setTagifyWhitelist(t.boat, data.boats || [], fullOptions.boats);
                    setTagifyWhitelist(t.guiding, data.guidings || [], fullOptions.guidings);
                    updateComponentDataFromWhitelist(blockIndex, 'accommodation', t.acc);
                    updateComponentDataFromWhitelist(blockIndex, 'boat', t.boat);
                    updateComponentDataFromWhitelist(blockIndex, 'guiding', t.guiding);
                    updatePreview();
                })
                .catch(function () {
                    setTagifyWhitelist(t.acc, fullOptions.accommodations);
                    setTagifyWhitelist(t.boat, fullOptions.boats);
                    setTagifyWhitelist(t.guiding, fullOptions.guidings);
                    updatePreview();
                });
        });
    }

    if (typeof Tagify !== 'undefined') {
        initBlockTagify(0);
        wireCampChange(0);
    }
    wireDateFromTo(0);
    
    // Wire up number_of_persons for initial offer block
    (function() {
        const initialBlock = document.querySelector('.offer-block[data-offer-index="0"]');
        if (initialBlock) {
            const personsInput = initialBlock.querySelector('input[name*="[number_of_persons]"]');
            if (personsInput) {
                personsInput.addEventListener('input', function() {
                    autoCalculateQuantities(0);
                });
                personsInput.addEventListener('change', function() {
                    autoCalculateQuantities(0);
                });
            }
        }
    })();

    document.getElementById('add-offer-btn').addEventListener('click', function () {
        var template = document.getElementById('offer-block-template');
        if (!template || !template.content) return;
        var index = nextOfferIndex++;
        var html = template.innerHTML.replace(/__INDEX__/g, index).replace(/__NUM__/g, index + 1);
        var wrap = document.createElement('div');
        wrap.innerHTML = html.trim();
        var clone = wrap.firstChild;
        document.getElementById('offer-blocks-container').appendChild(clone);
        initBlockTagify(index);
        wireCampChange(index);
        wireDateFromTo(index);
        clone.querySelectorAll('input, select, textarea').forEach(function (el) {
            el.addEventListener('input', updatePreview);
            el.addEventListener('change', updatePreview);
            
            // Add auto-calculation for number_of_persons
            if (el.name && el.name.indexOf('[number_of_persons]') !== -1) {
                el.addEventListener('input', function() {
                    autoCalculateQuantities(index);
                });
                el.addEventListener('change', function() {
                    autoCalculateQuantities(index);
                });
            }
        });
        clone.querySelector('.btn-remove-offer').addEventListener('click', function () {
            var block = this.closest('.offer-block');
            var idx = block.getAttribute('data-offer-index');
            if (tagifyByBlock[idx]) {
                if (tagifyByBlock[idx].acc) tagifyByBlock[idx].acc.destroy();
                if (tagifyByBlock[idx].boat) tagifyByBlock[idx].boat.destroy();
                if (tagifyByBlock[idx].guiding) tagifyByBlock[idx].guiding.destroy();
                delete tagifyByBlock[idx];
            }
            block.remove();
            updatePreview();
        });
        updatePreview();
    });

    if (form) {
        form.querySelectorAll('input, select, textarea').forEach(function (el) {
            if (el.name && (el.name.indexOf('offers[') === 0 || el.name === 'recipient_type' || el.name === 'customer_id' || el.name === 'manual_name' || el.name === 'manual_email' || el.name === 'manual_phone' || el.name === 'locale' || el.name === 'introduction_text' || el.name === 'free_text')) {
                el.addEventListener('input', updatePreview);
                el.addEventListener('change', updatePreview);
                
                // Add auto-calculation for number_of_persons
                if (el.name && el.name.indexOf('[number_of_persons]') !== -1) {
                    el.addEventListener('input', function() {
                        const block = el.closest('.offer-block');
                        if (block) {
                            const blockIndex = block.getAttribute('data-offer-index');
                            autoCalculateQuantities(blockIndex);
                        }
                    });
                    el.addEventListener('change', function() {
                        const block = el.closest('.offer-block');
                        if (block) {
                            const blockIndex = block.getAttribute('data-offer-index');
                            autoCalculateQuantities(blockIndex);
                        }
                    });
                }
            }
        });

        // Prevent form submit on Enter (allow Enter in textareas for new lines)
        form.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
                e.preventDefault();
            }
        });

        const confirmModal = document.getElementById('offer-send-confirm-modal');
        const confirmEmailEl = document.getElementById('offer-send-confirm-email');
        const confirmBtn = document.getElementById('offer-send-confirm-btn');

        function getRecipientEmail() {
            const type = form.querySelector('input[name="recipient_type"]:checked');
            if (type && type.value === 'customer') {
                const sel = document.getElementById('customer_id');
                const opt = sel && sel.options[sel.selectedIndex];
                return opt && opt.dataset && opt.dataset.email ? opt.dataset.email : '';
            }
            const manual = document.getElementById('manual_email');
            return manual ? (manual.value || '').trim() : '';
        }

        function doSend() {
            const btn = document.getElementById('offer-send-btn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending…';
            }
            if (confirmBtn) confirmBtn.disabled = true;
            fetch(sendUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(getFormData())
            })
            .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
            .then(function (result) {
                if (result.ok && result.data.success) {
                    showToast(result.data.message || 'Offer sent successfully.', true);
                } else {
                    showToast(result.data.message || 'Failed to send offer.', false);
                }
            })
            .catch(function () {
                showToast('Network error. Please try again.', false);
            })
            .finally(function () {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fe fe-send me-2"></i>Send offer email (CC admin)';
                }
                if (confirmBtn) confirmBtn.disabled = false;
                if (typeof bootstrap !== 'undefined' && confirmModal) {
                    const modal = bootstrap.Modal.getInstance(confirmModal);
                    if (modal) modal.hide();
                }
            });
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const email = getRecipientEmail();
            if (!email) {
                showToast('Please select a customer or enter a valid email address.', false);
                return;
            }
            if (confirmEmailEl) confirmEmailEl.textContent = email;
            if (typeof bootstrap !== 'undefined' && confirmModal) {
                const modal = bootstrap.Modal.getOrCreateInstance(confirmModal);
                modal.show();
            } else {
                doSend();
            }
        });

        if (confirmBtn && confirmModal) {
            confirmBtn.addEventListener('click', function () {
                doSend();
            });
        }
    }

    document.getElementById('recipient_customer').addEventListener('change', function () {
        document.getElementById('recipient-customer-wrap').classList.remove('d-none');
        document.getElementById('recipient-manual-wrap').classList.add('d-none');
        updatePreview();
    });
    document.getElementById('recipient_manual').addEventListener('change', function () {
        document.getElementById('recipient-customer-wrap').classList.add('d-none');
        document.getElementById('recipient-manual-wrap').classList.remove('d-none');
        updatePreview();
    });

    // Initial preview
    updatePreview();
})();
</script>
@endpush
