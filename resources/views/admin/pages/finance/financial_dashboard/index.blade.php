@extends('admin.layouts.app')

@php($t = __('admin.financial_dashboard'))

@section('title', $t['title'])

@section('custom_style')
<style>
    .fdash-wrap { --fd-primary: #4f46e5; --fd-muted: #64748b; --fd-border: rgba(148,163,184,.45); --fd-good: #059669; --fd-bad: #dc2626; }
    .fdash-wrap .fd-filter-row { display:flex; flex-wrap:wrap; gap:.5rem; align-items:flex-end; margin-bottom:1rem; }
    .fdash-wrap .fd-filter-row label { font-size:.72rem; text-transform:uppercase; letter-spacing:.06em; color:var(--fd-muted); display:block; margin-bottom:.2rem; }
    .fdash-wrap .fd-pills { display:flex; flex-wrap:wrap; gap:.35rem; margin-top:.5rem; }
    .fdash-wrap .fd-pill { font-size:.78rem; border:1px solid var(--fd-border); border-radius:999px; padding:.15rem .55rem; background:#f8fafc; }
    .fdash-wrap .fd-kpi-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(140px,1fr)); gap:.75rem; margin-bottom:1.25rem; }
    .fdash-wrap .fd-kpi { border:1px solid var(--fd-border); border-radius:12px; padding:.85rem; background:#fff; }
    .fdash-wrap .fd-kpi label { font-size:.68rem; text-transform:uppercase; letter-spacing:.06em; color:var(--fd-muted); margin:0; }
    .fdash-wrap .fd-kpi .val { font-size:1.15rem; font-weight:700; font-variant-numeric:tabular-nums; }
    .fdash-wrap .fd-kpi .mom { font-size:.75rem; margin-top:.25rem; }
    .fdash-wrap .fd-kpi .mom.up-good { color: var(--fd-good); }
    .fdash-wrap .fd-kpi .mom.down-good { color: var(--fd-good); }
    .fdash-wrap .fd-kpi .mom.up-bad { color: var(--fd-bad); }
    .fdash-wrap .fd-kpi .mom.down-bad { color: var(--fd-bad); }
    .fdash-wrap .fd-panel { border:1px solid var(--fd-border); border-radius:12px; padding:1rem; background:#fff; margin-bottom:1rem; }
    .fdash-wrap .fd-panel h3 { font-size:.95rem; margin-bottom:.75rem; }
    .fdash-wrap .fd-bar-row { margin-bottom:.5rem; }
    .fdash-wrap .fd-bar-row .meta { display:flex; justify-content:space-between; font-size:.8rem; margin-bottom:.15rem; }
    .fdash-wrap .fd-bar-bg { height:8px; border-radius:999px; background:#f1f5f9; overflow:hidden; }
    .fdash-wrap .fd-bar-fill { height:100%; border-radius:999px; background:var(--fd-primary); }
    .fdash-wrap table.fd-table { font-size:.85rem; }
    .fdash-wrap table.fd-table th { white-space:nowrap; cursor:pointer; user-select:none; }
    .fdash-wrap .fd-muted { color:var(--fd-muted); font-size:.8rem; }
</style>
@endsection

@section('content')
<div class="side-app fdash-wrap" x-data="financialTrackingDashboard({
    apiUrl: @js($apiUrl),
    exportUrl: @js($exportUrl),
    period: @js($initialPeriod ?? 'month'),
    year: {{ $initialYear }},
    month: {{ $initialMonth }},
    sort: 'created_at',
    direction: 'desc',
    page: 1,
})" x-init="init()">
    <div class="main-container container-fluid">
        <div class="page-header mb-3">
            <h1 class="page-title">{{ $t['title'] }}</h1>
            <p class="fd-muted mb-0">{{ $t['subtitle'] }}</p>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="fd-filter-row">
                    <div>
                        <label>{{ $t['filter_period'] }}</label>
                        <select class="form-select form-select-sm" x-model="period" @change="queueReload()">
                            <option value="month">{{ $t['period_month'] }}</option>
                            <option value="all">{{ $t['period_all'] }}</option>
                        </select>
                    </div>
                    <div>
                        <label>{{ $t['filter_year'] }}</label>
                        <select class="form-select form-select-sm" x-model.number="year" @change="queueReload()" :disabled="period === 'all'">
                            @for($y = now()->year + 1; $y >= 2020; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label>{{ $t['filter_month'] }}</label>
                        <select class="form-select form-select-sm" x-model.number="month" @change="queueReload()" :disabled="period === 'all'">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}">{{ \Carbon\Carbon::createFromDate((int) date('Y'), $m, 1)->translatedFormat('F') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>{{ $t['filter_country'] }}</label>
                        <select class="form-select form-select-sm" x-model="filters.country" @change="queueReload()">
                            <option value="">{{ $t['filter_all'] }}</option>
                            <template x-for="c in (filtersAvailable.countries || [])" :key="c">
                                <option :value="c" x-text="c"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label>{{ $t['filter_product'] }}</label>
                        <select class="form-select form-select-sm" x-model="filters.product_type" @change="queueReload()">
                            <option value="">{{ $t['filter_all'] }}</option>
                            <option value="tour">{{ $t['product_tour'] }}</option>
                            <option value="vacation">{{ $t['product_vacation'] }}</option>
                        </select>
                    </div>
                    <div>
                        <label>{{ $t['filter_fish'] }}</label>
                        <select class="form-select form-select-sm" x-model="filters.target_fish_id" @change="queueReload()">
                            <option value="">{{ $t['filter_all'] }}</option>
                            <template x-for="f in (filtersAvailable.fish_species || [])" :key="f.id">
                                <option :value="f.id" x-text="f.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label>{{ $t['filter_tier'] }}</label>
                        <select class="form-select form-select-sm" x-model="filters.price_tier" @change="queueReload()">
                            <option value="">{{ $t['filter_all'] }}</option>
                            <option value="budget">{{ $t['tier_budget'] }}</option>
                            <option value="standard">{{ $t['tier_standard'] }}</option>
                            <option value="premium">{{ $t['tier_premium'] }}</option>
                        </select>
                    </div>
                    <div>
                        <label>{{ $t['filter_lead'] }}</label>
                        <select class="form-select form-select-sm" x-model="filters.lead_time" @change="queueReload()">
                            <option value="">{{ $t['filter_all'] }}</option>
                            <option value="short">{{ $t['lead_short'] }}</option>
                            <option value="mid">{{ $t['lead_mid'] }}</option>
                            <option value="long">{{ $t['lead_long'] }}</option>
                        </select>
                    </div>
                    <div>
                        <label>{{ $t['filter_guide'] }}</label>
                        <select class="form-select form-select-sm" x-model="filters.guide_id" @change="queueReload()">
                            <option value="">{{ $t['filter_all'] }}</option>
                            <template x-for="g in (filtersAvailable.guides || [])" :key="g.id">
                                <option :value="g.id" x-text="g.name"></option>
                            </template>
                        </select>
                    </div>
                    <div class="ms-auto">
                        <button type="button" class="btn btn-sm btn-outline-secondary" @click="clearFilters()" x-show="hasActiveFilters">{{ $t['clear_all'] }}</button>
                    </div>
                </div>
                <div class="fd-pills" x-show="hasActiveFilters">
                    <template x-if="filters.country"><span class="fd-pill"><span x-text="filters.country"></span></span></template>
                    <template x-if="filters.product_type"><span class="fd-pill" x-text="filters.product_type"></span></template>
                    <template x-if="filters.target_fish_id"><span class="fd-pill" x-text="'Fish #' + filters.target_fish_id"></span></template>
                    <template x-if="filters.price_tier"><span class="fd-pill" x-text="filters.price_tier"></span></template>
                    <template x-if="filters.lead_time"><span class="fd-pill" x-text="filters.lead_time"></span></template>
                    <template x-if="filters.guide_id"><span class="fd-pill" x-text="'Guide #' + filters.guide_id"></span></template>
                </div>
                <p class="fd-muted small mb-0 mt-2" x-show="period === 'all'" x-cloak>{{ $t['trend_all_hint'] }}</p>
                <p class="fd-muted mb-0 mt-2" x-show="loading">{{ $t['loading'] }}</p>
                <p class="text-danger small mb-0 mt-2" x-show="error" x-text="error"></p>
            </div>
        </div>

        <div class="fd-kpi-grid">
            <template x-for="card in kpiCards" :key="card.key">
                <div class="fd-kpi">
                    <label x-text="card.label"></label>
                    <div class="val" x-text="card.display"></div>
                    <div class="mom" :class="card.momClass" x-show="card.momStr" x-text="card.momStr"></div>
                </div>
            </template>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-lg-8">
                <div class="fd-panel">
                    <h3>{{ $t['chart_trend'] }}</h3>
                    <canvas id="fd-trend-chart" height="100"></canvas>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="fd-panel">
                    <h3>{{ $t['chart_country'] }}</h3>
                    <canvas id="fd-country-chart"></canvas>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <div class="fd-panel">
                    <h3>{{ $t['breakdown_fish'] }}</h3>
                    <template x-for="row in (payload.breakdowns?.by_fish || []).slice(0,8)" :key="row.name">
                        <div class="fd-bar-row">
                            <div class="meta"><span x-text="row.name"></span><span class="fd-muted" x-text="fmtEuro(row.gmv)"></span></div>
                            <div class="fd-bar-bg"><div class="fd-bar-fill" :style="'width:' + barPct(row.gmv, fishMax) + '%'"></div></div>
                        </div>
                    </template>
                </div>
            </div>
            <div class="col-md-6">
                <div class="fd-panel">
                    <h3>{{ $t['breakdown_tier'] }}</h3>
                    <template x-for="row in (payload.breakdowns?.by_price_tier || [])" :key="row.tier">
                        <div class="fd-bar-row">
                            <div class="meta"><span x-text="row.tier"></span><span class="fd-muted"><span x-text="row.count"></span> · <span x-text="fmtEuro(row.gmv)"></span></span></div>
                            <div class="fd-bar-bg"><div class="fd-bar-fill" :style="'width:' + barPct(row.gmv, tierMax) + '%'"></div></div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div class="fd-panel">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <h3 class="mb-0">{{ $t['table_title'] }}</h3>
                <a class="btn btn-sm btn-outline-primary" :href="exportHref" target="_blank" rel="noopener">{{ $t['export_csv'] }}</a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm fd-table">
                    <thead>
                        <tr>
                            <th @click="setSort('id')">ID</th>
                            <th @click="setSort('created_at')">{{ $t['col_booking_date'] }}</th>
                            <th @click="setSort('book_date')">{{ $t['col_tour_date'] }}</th>
                            <th>{{ $t['col_guide'] }}</th>
                            <th>{{ $t['col_country'] }}</th>
                            <th>{{ $t['col_fish'] }}</th>
                            <th>{{ $t['col_product'] }}</th>
                            <th @click="setSort('price')">{{ $t['col_price'] }}</th>
                            <th @click="setSort('commission')">{{ $t['col_commission'] }}</th>
                            <th>{{ $t['col_tier'] }}</th>
                            <th>{{ $t['col_lead'] }}</th>
                            <th>{{ $t['col_status'] }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="row in (payload.bookings?.data || [])" :key="row.id">
                            <tr>
                                <td class="font-monospace" x-text="row.id"></td>
                                <td x-text="fmtIsoDate(row.booking_date)"></td>
                                <td x-text="row.tour_date || '—'"></td>
                                <td x-text="row.guide_name"></td>
                                <td x-text="row.country"></td>
                                <td x-text="row.target_fish"></td>
                                <td x-text="row.product_type"></td>
                                <td x-text="fmtEuro(row.price)"></td>
                                <td x-text="fmtEuro(row.commission)"></td>
                                <td x-text="row.price_tier"></td>
                                <td x-text="row.lead_time_days != null ? row.lead_time_days : '—'"></td>
                                <td x-text="row.status"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-2" x-show="payload.bookings?.last_page > 1">
                <button type="button" class="btn btn-sm btn-light border" :disabled="page <= 1 || loading" @click="page--; reload()">{{ $t['prev'] }}</button>
                <span class="small fd-muted" x-text="'Page ' + (payload.bookings?.current_page || 1) + ' / ' + (payload.bookings?.last_page || 1)"></span>
                <button type="button" class="btn btn-sm btn-light border" :disabled="page >= (payload.bookings?.last_page || 1) || loading" @click="page++; reload()">{{ $t['next'] }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js_after')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
<script>
function financialTrackingDashboard(cfg) {
    let trendChart = null;
    let countryChart = null;
    let debounce = null;

    return {
        apiUrl: cfg.apiUrl,
        exportUrl: cfg.exportUrl,
        period: cfg.period || 'month',
        year: cfg.year,
        month: cfg.month,
        sort: cfg.sort,
        direction: cfg.direction,
        page: cfg.page,
        filters: {
            country: '',
            product_type: '',
            target_fish_id: '',
            price_tier: '',
            lead_time: '',
            guide_id: '',
        },
        filtersAvailable: {},
        payload: {},
        loading: false,
        error: '',
        get hasActiveFilters() {
            return !!(this.filters.country || this.filters.product_type || this.filters.target_fish_id
                || this.filters.price_tier || this.filters.lead_time || this.filters.guide_id);
        },
        get exportHref() {
            const p = this.buildParams();
            // Export ignores pagination — full filtered set
            p.delete('page');
            p.delete('per_page');
            return this.exportUrl + '?' + p.toString();
        },
        get kpiCards() {
            const k = this.payload.kpis || {};
            const p = (k.prev_month && typeof k.prev_month === 'object') ? k.prev_month : {};
            const fmt = {
                euro: (v) => '€' + Number(v || 0).toLocaleString(undefined, { maximumFractionDigits: 0 }),
                pct: (v) => Number(v || 0).toFixed(1) + '%',
                num: (v) => Number(v || 0).toLocaleString(),
            };
            const mom = (cur, prev, lowerBetter) => {
                if (prev == null || isNaN(Number(prev))) return { momStr: '', momClass: '' };
                const c = Number(cur), pp = Number(prev);
                if (pp === 0 && c === 0) return { momStr: '', momClass: '' };
                const diff = c - pp;
                const pct = pp !== 0 ? (100 * diff / pp) : null;
                const momStr = pct === null ? ('Δ ' + diff.toFixed(0)) : ((diff > 0 ? '↑' : (diff < 0 ? '↓' : '')) + ' ' + Math.abs(pct).toFixed(1) + '% MoM');
                let good = diff >= 0;
                if (lowerBetter) good = diff <= 0;
                const momClass = diff === 0 ? '' : (good ? 'mom up-good' : 'mom up-bad');
                return { momStr, momClass };
            };
            const row = (key, label, display, cur, prev, lowerBetter) => ({ key, label, display, ...mom(cur, prev, lowerBetter) });
            return [
                row('gross_gmv', @json($t['kpi_gross_gmv']), fmt.euro(k.gross_gmv), k.gross_gmv, p.gross_gmv, false),
                row('net_gmv', @json($t['kpi_net_gmv']), fmt.euro(k.net_gmv), k.net_gmv, p.net_gmv, false),
                row('revenue', @json($t['kpi_revenue']), fmt.euro(k.revenue), k.revenue, p.revenue, false),
                row('take_rate', @json($t['kpi_take_rate']), fmt.pct(k.take_rate), k.take_rate, p.take_rate, false),
                row('avg_booking_value', @json($t['kpi_abv']), fmt.euro(k.avg_booking_value), k.avg_booking_value, p.avg_booking_value, false),
                row('booking_count', @json($t['kpi_bookings']), fmt.num(k.booking_count), k.booking_count, p.booking_count, false),
                row('cancellation_rate', @json($t['kpi_cancel_rate']), fmt.pct(k.cancellation_rate), k.cancellation_rate, p.cancellation_rate, true),
            ];
        },
        get fishMax() {
            const rows = this.payload.breakdowns?.by_fish || [];
            return Math.max(1, ...rows.map((r) => r.gmv));
        },
        get tierMax() {
            const rows = this.payload.breakdowns?.by_price_tier || [];
            return Math.max(1, ...rows.map((r) => r.gmv));
        },
        barPct(v, max) {
            return max > 0 ? Math.min(100, Math.round((v / max) * 100)) : 0;
        },
        fmtEuro(n) {
            if (n == null || isNaN(n)) return '—';
            return '€' + Number(n).toLocaleString(undefined, { maximumFractionDigits: 0 });
        },
        fmtIsoDate(iso) {
            if (!iso) return '—';
            return String(iso).slice(0, 10);
        },
        buildParams() {
            const p = new URLSearchParams();
            p.set('period', this.period);
            if (this.period !== 'all') {
                p.set('year', this.year);
                p.set('month', this.month);
            }
            p.set('page', this.page);
            p.set('per_page', '25');
            p.set('sort', this.sort);
            p.set('direction', this.direction);
            if (this.filters.country) p.set('country', this.filters.country);
            if (this.filters.product_type) p.set('product_type', this.filters.product_type);
            if (this.filters.target_fish_id) p.set('target_fish_id', this.filters.target_fish_id);
            if (this.filters.price_tier) p.set('price_tier', this.filters.price_tier);
            if (this.filters.lead_time) p.set('lead_time', this.filters.lead_time);
            if (this.filters.guide_id) p.set('guide_id', this.filters.guide_id);
            return p;
        },
        async reload() {
            this.loading = true;
            this.error = '';
            try {
                const res = await fetch(this.apiUrl + '?' + this.buildParams().toString(), {
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                this.payload = await res.json();
                this.filtersAvailable = this.payload.filters_available || {};
                this.$nextTick(() => this.renderCharts());
            } catch (e) {
                this.error = e.message || 'Error';
            } finally {
                this.loading = false;
            }
        },
        queueReload() {
            this.page = 1;
            clearTimeout(debounce);
            debounce = setTimeout(() => this.reload(), 250);
        },
        clearFilters() {
            this.filters = { country: '', product_type: '', target_fish_id: '', price_tier: '', lead_time: '', guide_id: '' };
            this.queueReload();
        },
        setSort(col) {
            if (this.sort === col) {
                this.direction = this.direction === 'asc' ? 'desc' : 'asc';
            } else {
                this.sort = col;
                this.direction = col === 'id' ? 'desc' : 'desc';
            }
            this.queueReload();
        },
        renderCharts() {
            const trend = this.payload.trend || [];
            const labels = trend.map((t) => t.year + '-' + String(t.month).padStart(2, '0'));
            const gross = trend.map((t) => t.gross_gmv);
            const net = trend.map((t) => t.net_gmv);

            const ctx = document.getElementById('fd-trend-chart');
            if (ctx && window.Chart) {
                if (trendChart) trendChart.destroy();
                if (!trend.length) {
                    trendChart = null;
                } else {
                    trendChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels,
                            datasets: [
                                { label: @json($t['legend_gross']), data: gross, backgroundColor: 'rgba(79,70,229,.35)', borderColor: '#4f46e5', borderWidth: 1 },
                                { label: @json($t['legend_net']), data: net, backgroundColor: 'rgba(5,150,105,.35)', borderColor: '#059669', borderWidth: 1 },
                            ],
                        },
                        options: {
                            responsive: true,
                            scales: { x: { stacked: false }, y: { beginAtZero: true } },
                            plugins: { legend: { position: 'bottom' } },
                        },
                    });
                }
            }

            const bc = this.payload.breakdowns?.by_country || [];
            const ctx2 = document.getElementById('fd-country-chart');
            if (ctx2 && window.Chart) {
                if (countryChart) countryChart.destroy();
                if (!bc.length) {
                    countryChart = null;
                } else {
                    countryChart = new Chart(ctx2, {
                    type: 'doughnut',
                    data: {
                        labels: bc.map((r) => r.name),
                        datasets: [{ data: bc.map((r) => r.gmv), backgroundColor: ['#4f46e5','#059669','#d97706','#e11d48','#0284c7','#7c3aed','#0d9488','#ea580c'] }],
                    },
                    options: {
                        plugins: { legend: { position: 'bottom' } },
                    },
                });
                }
            }
        },
        init() {
            this.reload();
        },
    };
}
</script>
@endpush
