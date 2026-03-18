@extends('admin.layouts.app')

@section('title', 'Finance – Invoice Overview')

@section('content')
    <div class="side-app">
        <div class="main-container container-fluid">
            <div class="page-header">
                <h1 class="page-title">@yield('title')</h1>
            </div>

            <div class="row row-sm">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex flex-wrap gap-2 align-items-end justify-content-between mb-3">
                                <form class="d-flex flex-wrap gap-2 align-items-end" method="GET" action="{{ route('admin.finance.invoices') }}" id="finance-filter-form">
                                    <div>
                                        <label class="form-label mb-1 small text-muted">Month</label>
                                        <select class="form-select form-select-sm" id="finance-filter-month" name="month" style="min-width: 160px;">
                                            <option value="">All months</option>
                                            <option value="1" {{ request('month') == '1' ? 'selected' : '' }}>January</option>
                                            <option value="2" {{ request('month') == '2' ? 'selected' : '' }}>February</option>
                                            <option value="3" {{ request('month') == '3' ? 'selected' : '' }}>March</option>
                                            <option value="4" {{ request('month') == '4' ? 'selected' : '' }}>April</option>
                                            <option value="5" {{ request('month') == '5' ? 'selected' : '' }}>May</option>
                                            <option value="6" {{ request('month') == '6' ? 'selected' : '' }}>June</option>
                                            <option value="7" {{ request('month') == '7' ? 'selected' : '' }}>July</option>
                                            <option value="8" {{ request('month') == '8' ? 'selected' : '' }}>August</option>
                                            <option value="9" {{ request('month') == '9' ? 'selected' : '' }}>September</option>
                                            <option value="10" {{ request('month') == '10' ? 'selected' : '' }}>October</option>
                                            <option value="11" {{ request('month') == '11' ? 'selected' : '' }}>November</option>
                                            <option value="12" {{ request('month') == '12' ? 'selected' : '' }}>December</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="form-label mb-1 small text-muted">Year</label>
                                        <select class="form-select form-select-sm" id="finance-filter-year" name="year" style="min-width: 120px;">
                                            <option value="">All years</option>
                                        </select>
                                    </div>

                                    <div class="ms-2">
                                        <label class="form-label mb-1 small text-muted">Quarter</label>
                                        <div class="btn-group btn-group-sm d-flex flex-wrap" role="group" aria-label="Quarter filters">
                                            @php
                                                $q = request('quarter');
                                            @endphp
                                            <a class="btn btn-outline-primary {{ $q === null || $q === '' ? 'active' : '' }}"
                                               href="{{ route('admin.finance.invoices', array_filter(['year' => request('year'), 'month' => request('month')])) }}">All</a>
                                            <a class="btn btn-outline-primary {{ $q == '1' ? 'active' : '' }}"
                                               href="{{ route('admin.finance.invoices', array_filter(['year' => request('year') ?: now()->format('Y'), 'quarter' => 1])) }}">Q1</a>
                                            <a class="btn btn-outline-primary {{ $q == '2' ? 'active' : '' }}"
                                               href="{{ route('admin.finance.invoices', array_filter(['year' => request('year') ?: now()->format('Y'), 'quarter' => 2])) }}">Q2</a>
                                            <a class="btn btn-outline-primary {{ $q == '3' ? 'active' : '' }}"
                                               href="{{ route('admin.finance.invoices', array_filter(['year' => request('year') ?: now()->format('Y'), 'quarter' => 3])) }}">Q3</a>
                                            <a class="btn btn-outline-primary {{ $q == '4' ? 'active' : '' }}"
                                               href="{{ route('admin.finance.invoices', array_filter(['year' => request('year') ?: now()->format('Y'), 'quarter' => 4])) }}">Q4</a>
                                        </div>
                                    </div>
                                    <input type="hidden" name="quarter" value="{{ request('quarter') }}">
                                </form>

                                <div class="text-muted small">
                                    Filters apply to <strong>Reservation date</strong>.
                                </div>
                            </div>

                            <div class="table-responsive" style="overflow-x: auto;">
                                <table class="table table-bordered text-nowrap border-bottom mb-0" id="finance-invoices-datatable">
                                    <thead>
                                    <tr>
                                        <th>Booking #</th>
                                        <th>Booking date</th>
                                        <th>Guest</th>
                                        <th>Guide</th>
                                        <th class="text-end">Price</th>
                                        <th class="text-end">Provision</th>
                                        <th class="text-end">Tax (19%)</th>
                                        <th>Invoice sent</th>
                                        <th>Paid status</th>
                                        <th>Reservation date</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($rows as $row)
                                        @php
                                            $price = $row['price'];
                                            $provision = $row['provision'];
                                            $tax = $row['tax'];
                                            $invoiceSent = (bool) ($row['invoice_sent'] ?? false);
                                            $paidStatus = $row['paid_status'] ?? 'unpaid';

                                            $routeSource = $row['source_key'] === 'booking'
                                                ? 'booking'
                                                : ($row['source_key'] === 'trip' ? 'trip' : 'camp_vacation');

                                            $manageUrlInvoice = route('admin.finance.update-invoice', ['source' => $routeSource, 'id' => $row['id']]);
                                            $manageUrlPaid = route('admin.finance.update-paid', ['source' => $routeSource, 'id' => $row['id']]);

                                            $sourceListUrl = match ($routeSource) {
                                                'booking' => route('admin.bookings.index'),
                                                'trip' => route('admin.trip-bookings.index'),
                                                default => route('admin.camp-vacation-bookings.index'),
                                            };
                                        @endphp
                                        <tr>
                                            <td class="fw-semibold">
                                                <div class="d-flex flex-column gap-1">
                                                    <span>#{{ $row['id'] }}</span>
                                                    @php
                                                        $badgeText = $row['source'] ?? '—';
                                                        $badgeClass = 'bg-secondary';
                                                        if ($routeSource === 'booking') {
                                                            $badgeClass = 'bg-primary';
                                                        } elseif ($routeSource === 'trip') {
                                                            $badgeClass = 'bg-info';
                                                        } else {
                                                            $badgeLower = strtolower((string) $badgeText);
                                                            $badgeClass = str_contains($badgeLower, 'vacation')
                                                                ? 'bg-success'
                                                                : 'bg-warning text-dark';
                                                        }
                                                    @endphp
                                                    <span class="badge {{ $badgeClass }}" style="font-size: 0.7rem; font-weight: 600;">
                                                        {{ $badgeText }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td>{{ $row['booking_date'] ?? '—' }}</td>
                                            <td>
                                                <div class="fw-semibold">{{ $row['guest_name'] ?? '—' }}</div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $row['guide_name'] ?? '—' }}</div>
                                            </td>
                                            <td class="text-end">
                                                @if($price !== null)
                                                    <span class="js-money" data-raw="{{ $price }}">{{ two($price) }} €</span>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @if($provision !== null)
                                                    <span class="js-money" data-raw="{{ $provision }}">{{ two($provision) }} €</span>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @if($tax !== null)
                                                    <span class="js-money" data-raw="{{ $tax }}">{{ two($tax) }} €</span>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td>
                                                <select class="form-select form-select-sm js-finance-status"
                                                        data-kind="invoice"
                                                        data-url="{{ $manageUrlInvoice }}">
                                                    <option value="0" {{ $invoiceSent ? '' : 'selected' }}>Not sent</option>
                                                    <option value="1" {{ $invoiceSent ? 'selected' : '' }}>Sent</option>
                                                </select>
                                                <div class="small text-muted mt-1 js-finance-status-meta" style="display:none;"></div>
                                            </td>
                                            <td>
                                                <select class="form-select form-select-sm js-finance-status"
                                                        data-kind="paid"
                                                        data-url="{{ $manageUrlPaid }}">
                                                    <option value="unpaid" {{ $paidStatus === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                                    <option value="paid" {{ $paidStatus === 'paid' ? 'selected' : '' }}>Paid</option>
                                                </select>
                                                <div class="small text-muted mt-1 js-finance-status-meta" style="display:none;"></div>
                                            </td>
                                            <td>
                                                @php
                                                    $resDate = $row['reservation_date'] ?? '—';
                                                    $resIso = ($resDate && $resDate !== '—') ? $resDate : '';
                                                @endphp
                                                <span class="js-reservation-date" data-date="{{ $resIso }}">{{ $resDate }}</span>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group btn-group-sm">
                                                    <a class="btn btn-outline-secondary" href="{{ $sourceListUrl }}" title="Open source list">
                                                        <i class="fa fa-external-link-alt"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center text-muted py-4">
                                                No finance rows found.
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                    <tfoot>
                                    <tr class="table-light">
                                        <th colspan="4" class="text-end">Total (current page)</th>
                                        <th class="text-end"><span id="finance-total-price">0,00 €</span></th>
                                        <th class="text-end"><span id="finance-total-provision">0,00 €</span></th>
                                        <th class="text-end"><span id="finance-total-tax">0,00 €</span></th>
                                        <th colspan="4"></th>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div class="mt-3 text-muted" style="font-size: 0.9rem;">
                                Provision tiers: 10% (≤ €350), 7.5% (€350–€1,500), 3% (> €1,500). Tax is provision × 19%.
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
        (function () {
            if (!window.jQuery || !jQuery.fn || !jQuery.fn.DataTable) return;

            var $table = jQuery('#finance-invoices-datatable');
            if (!$table.length) return;

            if (jQuery.fn.dataTable.isDataTable($table)) {
                return;
            }

            function parseEuro(val) {
                if (val === null || val === undefined) return 0;
                var s = String(val);
                // Strip HTML tags
                s = s.replace(/<[^>]*>/g, '');
                // Keep digits, comma, dot, minus
                s = s.replace(/[^\d,.\-]/g, '').trim();
                if (!s) return 0;
                // de-DE format: 1.234,56
                if (s.indexOf(',') !== -1) {
                    s = s.replace(/\./g, '').replace(',', '.');
                }
                var n = parseFloat(s);
                return isNaN(n) ? 0 : n;
            }

            function formatEuro(n) {
                try {
                    return new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n) + ' €';
                } catch (e) {
                    return (Math.round(n * 100) / 100).toFixed(2).replace('.', ',') + ' €';
                }
            }

            var dt = $table.DataTable({
                order: [],
                // Using CSS overflow scrolling instead of DataTables scrollX
                // to avoid rare infinite width recalculation loops with Bootstrap wrappers.
                scrollX: false,
                autoWidth: false,
                deferRender: true,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/de-DE.json"
                },
                columnDefs: [
                    { targets: -1, orderable: false }
                ]
            });

            function updateTotals() {
                function sumColumn(colIdx) {
                    return dt
                        .column(colIdx, { search: 'applied', page: 'current' })
                        .data()
                        .reduce(function (acc, v) {
                            return acc + parseEuro(v);
                        }, 0);
                }

                var totalPrice = sumColumn(4);
                var totalProvision = sumColumn(5);
                var totalTax = sumColumn(6);

                var elPrice = document.getElementById('finance-total-price');
                var elProv = document.getElementById('finance-total-provision');
                var elTax = document.getElementById('finance-total-tax');
                if (elPrice) elPrice.textContent = formatEuro(totalPrice);
                if (elProv) elProv.textContent = formatEuro(totalProvision);
                if (elTax) elTax.textContent = formatEuro(totalTax);
            }

            updateTotals();
            $table.on('draw.dt', function () {
                updateTotals();
            });

            // Populate year dropdown from current rows (server already filtered)
            (function populateYears() {
                var yearEl = document.getElementById('finance-filter-year');
                if (!yearEl) return;

                // keep first option ("All years")
                var existing = new Set();
                Array.from(yearEl.options).forEach(function (o) { existing.add(o.value); });

                // Reservation date column index (0-based):
                // Booking#, BookingDate, Guest, Guide, Price, Provision, Tax, InvoiceSent, PaidStatus, ReservationDate, Actions
                var reservationColIdx = 9;

                var years = new Set();
                dt.column(reservationColIdx).nodes().each(function (td) {
                    var span = td.querySelector('.js-reservation-date');
                    var v = span?.getAttribute('data-date') || '';
                    var m = /^(\d{4})-/.exec(v);
                    if (m) years.add(parseInt(m[1], 10));
                });

                var sorted = Array.from(years).sort(function (a, b) { return b - a; });
                sorted.forEach(function (y) {
                    if (existing.has(String(y))) return;
                    var opt = document.createElement('option');
                    opt.value = String(y);
                    opt.textContent = String(y);
                    yearEl.appendChild(opt);
                });

                var selectedYear = "{{ request('year') }}";
                if (selectedYear) {
                    yearEl.value = selectedYear;
                }
            })();

            // Submit filters only on real user changes (prevents refresh loops)
            (function wireFilterAutoSubmit() {
                var form = document.getElementById('finance-filter-form');
                if (!form) return;

                var monthEl = document.getElementById('finance-filter-month');
                var yearEl = document.getElementById('finance-filter-year');
                if (!monthEl && !yearEl) return;

                function onChange(e) {
                    // Only submit when the change was initiated by the user.
                    if (e && e.isTrusted === false) return;
                    form.submit();
                }

                if (monthEl) monthEl.addEventListener('change', onChange);
                if (yearEl) yearEl.addEventListener('change', onChange);
            })();

            function getCsrfToken() {
                return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            }

            function setMeta($select, message, isError) {
                var $meta = $select.closest('td').find('.js-finance-status-meta');
                if (!$meta.length) return;
                $meta.text(message);
                $meta.toggleClass('text-danger', !!isError);
                $meta.toggleClass('text-muted', !isError);
                $meta.show();
                setTimeout(function () { $meta.fadeOut(400); }, 1800);
            }

            $table.on('change', '.js-finance-status', function () {
                var $select = jQuery(this);
                var kind = $select.data('kind');
                var url = $select.data('url');
                var value = $select.val();
                var prev = $select.data('prev');

                $select.prop('disabled', true);
                setMeta($select, 'Saving…', false);

                var body;
                if (kind === 'invoice') {
                    body = 'invoice_sent=' + encodeURIComponent(value);
                } else {
                    body = 'paid_status=' + encodeURIComponent(value);
                }

                fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: body
                }).then(function (res) {
                    if (!res.ok) throw res;
                    return res.json();
                }).then(function () {
                    $select.data('prev', value);
                    setMeta($select, 'Saved', false);
                }).catch(function () {
                    if (prev !== undefined) {
                        $select.val(prev);
                    }
                    setMeta($select, 'Failed to save', true);
                }).finally(function () {
                    $select.prop('disabled', false);
                });
            });

            // initialize previous values for rollback
            $table.find('.js-finance-status').each(function () {
                jQuery(this).data('prev', jQuery(this).val());
            });
        })();
    </script>
@endsection

