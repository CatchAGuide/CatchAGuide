@extends('admin.layouts.app')

@section('title', 'Consolidated listings')

@section('css_after')
{{-- Styles live in `resources/sass/admin/_consolidated-listings.scss` (compiled into `public/css/admin-layout.css`). --}}
@endsection

@section('content')
<div class="side-app consolidated-listings">
    <div class="main-container container-fluid">
        <div class="page-header">
            <h1 class="page-title">@yield('title')</h1>
            <div>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Listings</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                </ol>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 w-100">
                            <div>
                                <div class="fw-bold">All listings in one table</div>
                                <div class="text-muted small">Guidings, accommodations, camps and trips (normalized columns + export)</div>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-primary" type="button" id="btnExportCsv">
                                    <i class="fa fa-download me-1"></i> Export CSV
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row g-2 align-items-end mb-3 consolidated-filters" id="consolidatedFilters">
                            <div class="col-12 col-md-3">
                                <label class="form-label small text-muted mb-1">Search</label>
                                <input type="text" class="form-control" id="filterText" placeholder="Title, description...">
                            </div>
                            <div class="col-12 col-md-2">
                                <label class="form-label small text-muted mb-1">Location</label>
                                <input type="text" class="form-control" id="filterLocation" placeholder="City, region, country">
                            </div>
                            <div class="col-12 col-md-2">
                                <label class="form-label small text-muted mb-1">Owner</label>
                                <input type="text" class="form-control" id="filterOwner" list="ownerSuggestions" placeholder="Guide/provider">
                                <datalist id="ownerSuggestions"></datalist>
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label small text-muted mb-1">Status</label>
                                <select class="form-select" id="filterStatus">
                                    <option value="" selected>Any</option>
                                    <option value="active">Active</option>
                                    <option value="draft">Draft</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label small text-muted mb-1">Bookings</label>
                                <select class="form-select" id="filterBookings">
                                    <option value="" selected>Any</option>
                                    <option value="has">Has bookings</option>
                                    <option value="none">No bookings</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label small text-muted mb-1">Types</label>
                                <div class="d-flex flex-wrap gap-2 consolidated-type-toggles" id="typeToggles">
                                    @foreach($typeOptions as $typeKey => $typeLabel)
                                        <input type="checkbox" class="btn-check type-toggle" id="type_{{ $typeKey }}" autocomplete="off" value="{{ $typeKey }}" checked>
                                        <label class="btn btn-outline-primary btn-sm" for="type_{{ $typeKey }}">{{ $typeLabel }}</label>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label small text-muted mb-1">Min price</label>
                                <input type="number" step="0.01" class="form-control" id="filterPriceMin" placeholder="0">
                            </div>
                            <div class="col-6 col-md-2 d-flex gap-2">
                                <div class="w-100">
                                    <label class="form-label small text-muted mb-1">Max price</label>
                                    <input type="number" step="0.01" class="form-control" id="filterPriceMax" placeholder="9999">
                                </div>
                                <button class="btn btn-reset align-self-end" type="button" id="btnResetFilters" title="Reset filters">
                                    <i class="fa fa-undo"></i>
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="consolidated-datatable" class="table align-middle mb-0">
                                <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Created</th>
                                    <th>StatusKey</th>
                                    <th>BookingsKey</th>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                    <th>Owner</th>
                                    <th>Location</th>
                                    <th>Meta</th>
                                    <th>Manage</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($rows as $r)
                                    @php
                                        $type = $r['type'] ?? '';
                                        $typeClass = match($type) {
                                            'guiding' => 'badge-type-guiding',
                                            'accommodation' => 'badge-type-accommodation',
                                            'camp' => 'badge-type-camp',
                                            'trip' => 'badge-type-trip',
                                            default => 'badge-type-guiding',
                                        };
                                        $desc = (string) ($r['description'] ?? '');
                                        $priceLow = $r['price_low'];
                                        $priceHigh = $r['price_high'];
                                        $currency = $r['currency'] ?? 'EUR';
                                        $st = (string) ($r['status'] ?? '');
                                        $statusClass = $st === 'active'
                                            ? 'status-pill--active'
                                            : ($st === 'draft' ? 'status-pill--draft' : 'status-pill--inactive');
                                    @endphp
                                    <tr data-type="{{ $type }}" data-status="{{ $st }}">
                                        <td class="type-key">{{ $type }}</td>
                                        <td class="created-ts">{{ (int) ($r['created_at_ts'] ?? 0) }}</td>
                                        <td class="status-key">{{ $st }}</td>
                                        <td class="bookings-key">{{ (int) ($r['bookings_count'] ?? 0) }}</td>
                                        <td class="mono">
                                            <div class="id-stack">
                                                <div class="id-value">{{ $r['id'] }}</div>
                                            </div>
                                        </td>
                                        <td style="min-width: 220px;">
                                            <div class="fw-bold">{{ $r['title'] ?: '—' }}</div>
                                            @if(!empty($r['location']))
                                                <div class="title-subline">
                                                    <i class="fa fa-map-marker-alt"></i>
                                                    <span>{{ $r['location'] }}</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="desc-cell">
                                            @if($desc !== '')
                                                <div class="desc-text is-collapsed"
                                                     data-full="{{ e($desc) }}"
                                                     data-truncated="{{ e(\Illuminate\Support\Str::limit($desc, 160)) }}">
                                                    {{ \Illuminate\Support\Str::limit($desc, 160) }}
                                                </div>
                                                <button type="button" class="btn btn-link btn-sm p-0 mt-1 btn-toggle-desc" data-expanded="0">More</button>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($priceLow !== null)
                                                @php
                                                    $isRange = $priceHigh !== null && (float) $priceHigh > (float) $priceLow;
                                                    $lowText = $currency . ' ' . number_format((float) $priceLow, 2);
                                                    $highText = $currency . ' ' . number_format((float) $priceHigh, 2);
                                                @endphp
                                                <div class="price-card {{ $isRange ? 'is-range' : 'is-single' }}">
                                                    {{-- Keep plain numeric text in DOM for DataTables price filtering --}}
                                                    <span class="visually-hidden">{{ $priceLow }}</span>
                                                    <div class="price-card__top">
                                                        <div class="price-card__label">From</div>
                                                        <div class="price-card__value">{{ $lowText }}</div>
                                                    </div>
                                                    @if($isRange)
                                                        <div class="price-card__bottom">
                                                            <div class="price-card__label">To</div>
                                                            <div class="price-card__value price-card__value--muted">{{ $highText }}</div>
                                                        </div>
                                                    @else
                                                        <div class="price-card__bottom">
                                                            <div class="price-card__meta">Fixed price</div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="price-card price-card--na">
                                                    <div class="price-card__top">
                                                        <div class="price-card__label">Price</div>
                                                        <div class="price-card__value">n/a</div>
                                                    </div>
                                                    <div class="price-card__bottom">
                                                        <div class="price-card__meta">Not set</div>
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $ownerName = (string) ($r['owner_name'] ?? '');
                                                $ownerPhoto = $r['owner_photo_url'] ?? null;
                                                $initials = '';
                                                if ($ownerName !== '') {
                                                    $parts = preg_split('/\s+/', trim($ownerName)) ?: [];
                                                    if (!empty($parts[0])) {
                                                        $initials .= mb_substr($parts[0], 0, 1);
                                                    }
                                                    if (count($parts) > 1) {
                                                        $initials .= mb_substr(end($parts), 0, 1);
                                                    }
                                                }
                                                $initials = $initials ?: '—';
                                            @endphp
                                            <div class="owner-cell">
                                                @if(!empty($ownerPhoto))
                                                    <img src="{{ $ownerPhoto }}" alt="{{ $ownerName }}" class="owner-avatar">
                                                @else
                                                    <span class="owner-avatar owner-avatar--placeholder">{{ strtoupper($initials) }}</span>
                                                @endif
                                                <span class="owner-name">
                                                    <span class="owner-name__title">{{ $ownerName ?: '—' }}</span>
                                                    @php
                                                        $ownerCity = (string) ($r['owner_city'] ?? '');
                                                        $ownerEmail = (string) ($r['owner_email'] ?? '');
                                                        $ownerPhone = (string) ($r['owner_phone'] ?? '');
                                                    @endphp
                                                    @if($ownerCity || $ownerEmail || $ownerPhone)
                                                        <small class="owner-meta">
                                                            @if($ownerCity)
                                                                <span class="owner-meta__item"><i class="fa fa-map-marker-alt me-1"></i>{{ $ownerCity }}</span>
                                                            @endif
                                                            @if($ownerEmail)
                                                                <span class="owner-meta__item"><i class="fa fa-envelope me-1"></i>{{ $ownerEmail }}</span>
                                                            @endif
                                                            @if($ownerPhone)
                                                                <span class="owner-meta__item"><i class="fa fa-phone me-1"></i>{{ $ownerPhone }}</span>
                                                            @endif
                                                        </small>
                                                    @endif
                                                </span>
                                            </div>
                                        </td>
                                        <td style="max-width: 220px;">
                                            <div class="text-truncate" title="{{ $r['location'] ?? '' }}">{{ $r['location'] ?: '—' }}</div>
                                        </td>
                                        <td class="meta-cell">
                                            @php $meta = $r['meta'] ?? []; @endphp
                                            @if(is_array($meta) && count($meta))
                                                <div class="meta-stack">
                                                    @foreach(array_slice($meta, 0, 5) as $m)
                                                        @php
                                                            $icon = is_array($m) ? ($m['icon'] ?? null) : null;
                                                            $tooltip = is_array($m) ? ($m['tooltip'] ?? '') : '';
                                                            $text = is_array($m) ? ($m['text'] ?? '') : (string) $m;
                                                            $tone = is_array($m) ? ($m['tone'] ?? 'slate') : 'slate';
                                                        @endphp
                                                        <span class="meta-icon-pill meta-icon-pill--{{ $tone }}" title="{{ $tooltip }}">
                                                            @if($icon)
                                                                <i class="fa {{ $icon }}"></i>
                                                            @else
                                                                <i class="fa fa-info-circle"></i>
                                                            @endif
                                                            <span class="meta-icon-pill__text">{{ $text }}</span>
                                                        </span>
                                                    @endforeach
                                                    @if(count($meta) > 5)
                                                        <span class="meta-icon-pill meta-icon-pill--more" title="More details">
                                                            <i class="fa fa-ellipsis-h"></i>
                                                            <span class="meta-icon-pill__text">+{{ count($meta) - 5 }}</span>
                                                        </span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="manage-cell">
                                            <div class="manage-stack">
                                                <div class="manage-pills">
                                                    <span class="badge-type {{ $typeClass }}">{{ $r['type_label'] ?? $type }}</span>
                                                    <span class="status-pill {{ $statusClass }}">{{ $r['status_label'] ?? ucfirst($st) }}</span>
                                                </div>
                                                @php $bCount = (int) ($r['bookings_count'] ?? 0); @endphp
                                                <div class="manage-bookings-card {{ $bCount > 0 ? 'has-bookings' : '' }}" title="Bookings / requests">
                                                    <div class="manage-bookings-card__icon">
                                                        <i class="fa fa-calendar-check"></i>
                                                    </div>
                                                    <div class="manage-bookings-card__content">
                                                        <div class="manage-bookings-card__label">Bookings</div>
                                                        <div class="manage-bookings-card__value">{{ $bCount }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js_after')
<script>
    $(function () {
        function debounce(fn, wait) {
            var t = null;
            return function() {
                var ctx = this, args = arguments;
                clearTimeout(t);
                t = setTimeout(function() { fn.apply(ctx, args); }, wait);
            };
        }

        var $table = $('#consolidated-datatable');

        var dt = $table.DataTable({
            // Sort by created timestamp desc (hidden)
            order: [[1, 'desc']],
            pageLength: 50,
            columnDefs: [
                // 0: type key (hidden, used for filtering)
                // 1: created timestamp (hidden, used for ordering)
                // 2: status key (hidden, used for filtering)
                // 3: bookings count (hidden, used for filtering)
                { targets: [0, 1, 2, 3], visible: false, searchable: true }
            ]
        });

        $.fn.dataTable.ext.search.push(function(settings, data) {
            if (settings.nTable !== $table.get(0)) return true;

            var min = parseFloat($('#filterPriceMin').val());
            var max = parseFloat($('#filterPriceMax').val());
            if (isNaN(min)) min = null;
            if (isNaN(max)) max = null;

            // Price column index: 7 (type + created + status + bookings are hidden)
            var priceText = (data[7] || '').toString();
            var price = null;
            var m = priceText.replace(',', '.').match(/(\d+(\.\d+)?)/);
            if (m && m[1]) price = parseFloat(m[1]);

            if (min !== null && (price === null || price < min)) return false;
            if (max !== null && (price !== null && price > max)) return false;

            var bookingsMode = ($('#filterBookings').val() || '').toString();
            if (bookingsMode) {
                // Bookings key column index: 3
                var b = parseInt((data[3] || '0').toString(), 10);
                if (isNaN(b)) b = 0;
                if (bookingsMode === 'has' && b <= 0) return false;
                if (bookingsMode === 'none' && b > 0) return false;
            }

            return true;
        });

        function selectedTypes() {
            return $('#typeToggles .type-toggle:checked').map(function() { return this.value; }).get();
        }

        function updateOwnerSuggestions() {
            var types = selectedTypes();
            var owners = new Set();
            dt.rows().every(function() {
                var node = this.node();
                if (!node) return;
                var rowType = $(node).data('type');
                if (types.length && types.indexOf(rowType) === -1) return;
                var data = this.data();
                // Owner column index: 8
                var owner = (data[8] || '').toString().trim();
                if (owner) owners.add(owner);
            });
            var $dl = $('#ownerSuggestions').empty();
            Array.from(owners).sort().forEach(function(o) {
                $dl.append($('<option>').attr('value', o));
            });
        }

        function applyFilters() {
            var types = selectedTypes();
            var typeRegex = types.length ? '^(' + types.map(function(t){ return t.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); }).join('|') + ')$' : '';
            dt.column(0).search(typeRegex, true, false);

            dt.search(($('#filterText').val() || '').toString());
            // Location column index: 9
            dt.column(9).search(($('#filterLocation').val() || '').toString());
            // Owner column index: 8
            dt.column(8).search(($('#filterOwner').val() || '').toString());

            var status = ($('#filterStatus').val() || '').toString();
            if (status) {
                // Status key column index: 2
                dt.column(2).search('^' + status + '$', true, false);
            } else {
                dt.column(2).search('');
            }

            dt.draw();
        }

        var applyFiltersDebounced = debounce(applyFilters, 250);

        $('#filterText').on('input', applyFiltersDebounced);
        $('#filterLocation').on('input', applyFiltersDebounced);
        $('#filterOwner').on('input', applyFiltersDebounced);
        $('#filterStatus').on('change', applyFiltersDebounced);
        $('#filterBookings').on('change', applyFiltersDebounced);
        $('#filterPriceMin').on('input', applyFiltersDebounced);
        $('#filterPriceMax').on('input', applyFiltersDebounced);
        $('#typeToggles .type-toggle').on('change', function() {
            updateOwnerSuggestions();
            applyFiltersDebounced();
        });

        $('#btnResetFilters').on('click', function() {
            $('#filterText').val('');
            $('#filterLocation').val('');
            $('#filterOwner').val('');
            $('#filterStatus').val('');
            $('#filterBookings').val('');
            $('#filterPriceMin').val('');
            $('#filterPriceMax').val('');
            $('#typeToggles .type-toggle').prop('checked', true);
            updateOwnerSuggestions();
            applyFilters();
        });

        $(document).on('click', '.btn-toggle-desc', function () {
            var $btn = $(this);
            var $cell = $btn.closest('td');
            var $text = $cell.find('.desc-text');
            var full = ($text.data('full') || '').toString();
            var truncated = ($text.data('truncated') || '').toString();
            var expanded = $btn.data('expanded') === 1;

            if (!expanded) {
                $text.removeClass('is-collapsed').text(full);
                $btn.text('Less').data('expanded', 1);
            } else {
                $text.addClass('is-collapsed').text(truncated);
                $btn.text('More').data('expanded', 0);
            }
        });

        $('#btnExportCsv').on('click', function() {
            var rows = dt.rows({ search: 'applied' }).data().toArray();
            var header = ['ID','Type','Title','Description','Owner','Location','Price','Meta','Status'];
            var csv = [];
            csv.push('\uFEFF' + header.join(','));

            rows.forEach(function(r) {
                var typeKey = (r[0] || '').toString();
                // r[1]=created (hidden), r[2]=statusKey (hidden), r[3]=bookingsKey (hidden), r[4]=ID cell
                var id = $('<div>').html(r[4] || '').find('.id-value').text().trim() || $('<div>').html(r[4] || '').text().trim();
                var title = $('<div>').html(r[5] || '').text().trim();
                var desc = $('<div>').html(r[6] || '').text().trim();
                var price = $('<div>').html(r[7] || '').text().trim();
                var owner = $('<div>').html(r[8] || '').text().trim();
                var location = $('<div>').html(r[9] || '').text().trim();
                var meta = $('<div>').html(r[10] || '').text().trim();
                var status = (r[2] || '').toString();

                function esc(v) {
                    v = (v || '').toString().replace(/"/g, '""');
                    return '"' + v + '"';
                }

                csv.push([esc(id), esc(typeKey), esc(title), esc(desc), esc(owner), esc(location), esc(price), esc(meta), esc(status)].join(','));
            });

            var blob = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
            var url = URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = 'consolidated_listings_filtered_' + new Date().toISOString().slice(0,19).replace(/[:T]/g,'-') + '.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        });

        (function initLocationAutocomplete() {
            var input = document.getElementById('filterLocation');
            if (!input) return;
            if (typeof window.google === 'undefined' || !google.maps || !google.maps.places || !google.maps.places.Autocomplete) {
                return;
            }
            try {
                var ac = new google.maps.places.Autocomplete(input, { types: ['(regions)'] });
                ac.addListener('place_changed', function () {
                    applyFiltersDebounced();
                });
            } catch (e) {}
        })();

        updateOwnerSuggestions();
        applyFilters();
    });
</script>
@endsection

