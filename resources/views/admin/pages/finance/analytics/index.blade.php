@extends('admin.layouts.app')

@php($t = __('admin.finance_analytics'))

@section('title', 'Finance Analytics')

@section('custom_style')
<style>
    .finance-kpi-dashboard {
        --fkpi-primary: #4f46e5;
        --fkpi-primary-soft: rgba(79, 70, 229, 0.12);
        --fkpi-accent: #d97706;
        --fkpi-accent-soft: rgba(217, 119, 6, 0.12);
        --fkpi-success: #059669;
        --fkpi-success-soft: rgba(5, 150, 105, 0.12);
        --fkpi-danger: #dc2626;
        --fkpi-muted: #6b7280;
        --fkpi-border: rgba(148, 163, 184, 0.45);
        --fkpi-card: #fff;
        --fkpi-radius: 14px;
    }
    .finance-kpi-dashboard .fkpi-header {
        display: flex; flex-wrap: wrap; gap: 1rem; justify-content: space-between; align-items: flex-start;
        margin-bottom: 1.25rem;
    }
    .finance-kpi-dashboard .fkpi-tabs {
        display: flex; flex-wrap: wrap; gap: 0.25rem; border-bottom: 1px solid var(--fkpi-border); padding-bottom: 0;
    }
    .finance-kpi-dashboard .fkpi-tab {
        background: none; border: none; padding: 0.55rem 1rem 0.75rem; font-size: 0.9rem; font-weight: 500;
        color: var(--fkpi-muted); cursor: pointer; border-bottom: 2px solid transparent; transition: color .15s, border-color .15s;
        border-radius: 8px 8px 0 0;
    }
    .finance-kpi-dashboard .fkpi-tab:hover { color: #111827; background: rgba(79, 70, 229, 0.06); }
    .finance-kpi-dashboard .fkpi-tab.is-active {
        color: var(--fkpi-primary); border-bottom-color: var(--fkpi-primary); font-weight: 600;
    }
    .finance-kpi-dashboard .fkpi-year-pills { display: flex; flex-wrap: wrap; gap: 0.35rem; align-items: center; }
    .finance-kpi-dashboard .fkpi-year-pill {
        border: 1px solid var(--fkpi-border); background: #fff; border-radius: 999px; padding: 0.25rem 0.75rem;
        font-size: 0.78rem; cursor: pointer; transition: all .15s; color: #374151;
    }
    .finance-kpi-dashboard .fkpi-year-pill:hover { border-color: var(--fkpi-primary); color: var(--fkpi-primary); }
    .finance-kpi-dashboard .fkpi-year-pill.is-active {
        background: linear-gradient(135deg, #a5b4fc, #4f46e5); color: #fff; border-color: #4f46e5;
    }
    .finance-kpi-dashboard .fkpi-stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.75rem; }
    .finance-kpi-dashboard .fkpi-stat {
        background: var(--fkpi-card); border: 1px solid var(--fkpi-border); border-radius: var(--fkpi-radius);
        padding: 1rem 1.1rem; transition: transform .12s ease, box-shadow .12s ease;
    }
    .finance-kpi-dashboard .fkpi-stat:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(15, 23, 42, 0.08); }
    .finance-kpi-dashboard .fkpi-stat-label { font-size: 0.65rem; letter-spacing: 0.08em; text-transform: uppercase; color: var(--fkpi-muted); font-weight: 600; }
    .finance-kpi-dashboard .fkpi-stat-value { font-size: 1.35rem; font-weight: 700; margin-top: 0.2rem; line-height: 1.15; }
    .finance-kpi-dashboard .fkpi-panel {
        background: var(--fkpi-card); border: 1px solid var(--fkpi-border); border-radius: var(--fkpi-radius);
        padding: 1.1rem 1.25rem; margin-bottom: 0.85rem;
    }
    .finance-kpi-dashboard .fkpi-panel-title { font-size: 0.65rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--fkpi-muted); font-weight: 700; margin-bottom: 0.75rem; }
    .finance-kpi-dashboard .fkpi-bar-row { display: flex; align-items: flex-end; gap: 3px; height: 88px; overflow-x: auto; padding-bottom: 4px; }
    .finance-kpi-dashboard .fkpi-bar-col { display: flex; flex-direction: column; align-items: center; gap: 4px; flex-shrink: 0; min-width: 26px; }
    .finance-kpi-dashboard .fkpi-bar {
        width: 18px; border-radius: 4px 4px 0 0; transition: height 0.25s ease, filter 0.15s ease; cursor: pointer;
    }
    .finance-kpi-dashboard .fkpi-bar:hover { filter: brightness(1.08); }
    .finance-kpi-dashboard .fkpi-chip {
        display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.2rem 0.65rem; border-radius: 999px;
        font-size: 0.72rem; font-weight: 600; cursor: pointer; border: 1px solid var(--fkpi-border); background: #f9fafb; color: #4b5563;
        transition: all .15s;
    }
    .finance-kpi-dashboard .fkpi-chip.is-on { background: var(--fkpi-primary-soft); border-color: rgba(79, 70, 229, 0.35); color: var(--fkpi-primary); }
    .finance-kpi-dashboard .fkpi-country-btn {
        border: 1px solid var(--fkpi-border); background: #fff; border-radius: 999px; padding: 0.35rem 0.85rem;
        font-size: 0.82rem; cursor: pointer; transition: all .15s;
    }
    .finance-kpi-dashboard .fkpi-country-btn:hover { border-color: var(--fkpi-primary); }
    .finance-kpi-dashboard .fkpi-country-btn.is-active { border-color: var(--fkpi-primary); background: var(--fkpi-primary-soft); color: var(--fkpi-primary); font-weight: 600; }
    .finance-kpi-dashboard .fkpi-kpi-card {
        border: 1px solid var(--fkpi-border); border-radius: var(--fkpi-radius); padding: 1rem 1.1rem; cursor: pointer;
        transition: border-color .15s, box-shadow .15s; background: #fff;
    }
    .finance-kpi-dashboard .fkpi-kpi-card:hover { border-color: rgba(79, 70, 229, 0.45); box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06); }
    .finance-kpi-dashboard .fkpi-kpi-card.is-open { border-color: var(--fkpi-success); box-shadow: 0 0 0 2px var(--fkpi-success-soft); }
    .finance-kpi-dashboard .fkpi-spark { display: block; overflow: visible; }
    .finance-kpi-dashboard .fkpi-table-wrap { overflow-x: auto; }
    .finance-kpi-dashboard table.fkpi-table { width: 100%; font-size: 0.82rem; border-collapse: collapse; }
    .finance-kpi-dashboard .fkpi-table th { text-align: left; padding: 0.5rem 0.65rem; color: var(--fkpi-muted); font-weight: 600; font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 1px solid var(--fkpi-border); }
    .finance-kpi-dashboard .fkpi-table td { padding: 0.55rem 0.65rem; border-bottom: 1px solid rgba(148, 163, 184, 0.25); }
    .finance-kpi-dashboard .fkpi-table tbody tr { cursor: pointer; transition: background .12s; }
    .finance-kpi-dashboard .fkpi-table tbody tr:hover { background: rgba(191, 219, 254, 0.35); }
    .finance-kpi-dashboard .fkpi-pill-grid { display: flex; flex-wrap: wrap; gap: 0.5rem; }
    .finance-kpi-dashboard .fkpi-pill { flex: 1; min-width: 100px; border-radius: 10px; padding: 0.65rem 0.85rem; }
    .finance-kpi-dashboard .fkpi-mono { font-variant-numeric: tabular-nums; font-feature-settings: "tnum"; }
    .finance-kpi-dashboard [x-cloak] { display: none !important; }
</style>
@endsection

@section('content')
    <div class="side-app finance-kpi-dashboard">
        <div class="main-container container-fluid" x-data="financeAnalyticsDashboard(@js($analytics))">
            <div class="page-header fkpi-header">
                <div>
                    <h1 class="page-title mb-1">@yield('title')</h1>
                    <p class="text-muted mb-0 small">
                        {{ $t['page_subtitle'] }}
                        <span class="ms-2 badge rounded-pill bg-light text-dark border">
                            @if(($analytics['date_basis'] ?? 'reservation') === 'booking')
                                {{ app()->getLocale() === 'de' ? 'Basis: Buchungsdatum (created_at)' : 'Basis: Booking date (created_at)' }}
                            @else
                                {{ app()->getLocale() === 'de' ? 'Basis: Reservierungsdatum (Tourdatum)' : 'Basis: Reservation date (tour date)' }}
                            @endif
                        </span>
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2 justify-content-end">
                    <div class="fkpi-year-pills">
                    <span class="small text-muted text-uppercase fw-bold me-1" style="font-size:0.65rem;letter-spacing:0.08em;">{{ $t['time_range'] }}</span>
                    <template x-for="y in yearOptionsList" :key="y.key">
                        <button type="button" class="fkpi-year-pill" :class="{ 'is-active': year === y.key }" @click="year = y.key">
                            <span x-text="y.label"></span>
                        </button>
                    </template>
                </div>
                    <div class="d-flex gap-1 align-items-center">
                        <a class="fkpi-year-pill {{ ($analytics['date_basis'] ?? 'reservation') === 'reservation' ? 'is-active' : '' }}"
                           href="{{ route('admin.finance.analytics', ['date_basis' => 'reservation']) }}">
                            {{ app()->getLocale() === 'de' ? 'Reservierung' : 'Reservation' }}
                        </a>
                        <a class="fkpi-year-pill {{ ($analytics['date_basis'] ?? 'reservation') === 'booking' ? 'is-active' : '' }}"
                           href="{{ route('admin.finance.analytics', ['date_basis' => 'booking']) }}">
                            {{ app()->getLocale() === 'de' ? 'Buchung' : 'Booking' }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-none bg-transparent">
                <div class="card-body p-0">
                    <div class="fkpi-tabs mb-3">
                        <button type="button" class="fkpi-tab" :class="{ 'is-active': tab === 'laender' }" @click="tab = 'laender'">{{ $t['tab_countries'] }}</button>
                        <button type="button" class="fkpi-tab" :class="{ 'is-active': tab === 'revenue' }" @click="tab = 'revenue'">{{ $t['tab_revenue'] }}</button>
                        <button type="button" class="fkpi-tab" :class="{ 'is-active': tab === 'supply' }" @click="tab = 'supply'">{{ $t['tab_supply'] }}</button>
                        <button type="button" class="fkpi-tab" :class="{ 'is-active': tab === 'traffic' }" @click="tab = 'traffic'">{{ $t['tab_traffic'] }}</button>
                        <button type="button" class="fkpi-tab" :class="{ 'is-active': tab === 'conversion' }" @click="tab = 'conversion'">{{ $t['tab_conversion'] }}</button>
                    </div>

                    <div class="small text-muted mb-3 fkpi-mono">
                        <span class="badge rounded-pill" style="background: var(--fkpi-success-soft); color: var(--fkpi-success);">{{ $t['live_data'] }}</span>
                        <span class="ms-2" x-text="'{{ $t['updated_at_label'] }}: ' + raw.generated_at.slice(0, 19).replace('T', ' ')"></span>
                    </div>

                    {{-- Countries --}}
                    <div x-show="tab === 'laender'" x-cloak>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <button type="button" class="fkpi-country-btn" :class="{ 'is-active': countrySel === '__total__' }" @click="countrySel = '__total__'">&#127757; {{ $t['total_overview'] }}</button>
                            <template x-for="c in raw.countries" :key="c.id">
                                <button type="button" class="fkpi-country-btn" :class="{ 'is-active': countrySel === c.id }" @click="countrySel = c.id" x-text="c.label"></button>
                            </template>
                        </div>
                        <div class="fkpi-stat-grid mb-3">
                            <div class="fkpi-stat">
                                <div class="fkpi-stat-label">{{ $t['col_tours'] }}</div>
                                <div class="fkpi-stat-value fkpi-mono" style="color: var(--fkpi-primary);" x-text="fmtInt(activeCountryBlock.total_tours)"></div>
                                <div class="small text-muted">{{ $t['in_database'] }}</div>
                            </div>
                            <div class="fkpi-stat">
                                <div class="fkpi-stat-label">{{ $t['col_bookings'] }}</div>
                                <div class="fkpi-stat-value fkpi-mono" style="color: var(--fkpi-accent);" x-text="fmtInt(activeCountryBlock.bookings)"></div>
                            </div>
                            <div class="fkpi-stat">
                                <div class="fkpi-stat-label">GMV</div>
                                <div class="fkpi-stat-value fkpi-mono" style="color: var(--fkpi-success);" x-text="fmtEuro(activeCountryBlock.gmv)"></div>
                            </div>
                            <div class="fkpi-stat">
                                <div class="fkpi-stat-label">{{ $t['label_avg_per_booking'] }}</div>
                                <div class="fkpi-stat-value fkpi-mono text-dark" x-text="activeCountryBlock.bookings ? fmtEuro(activeCountryBlock.gmv / activeCountryBlock.bookings) : '—'"></div>
                            </div>
                        </div>
                        <div class="fkpi-panel">
                            <div class="fkpi-panel-title">{{ $t['insight'] }}</div>
                            <p class="mb-0 small" x-text="countryInsight"></p>
                        </div>
                        <div class="fkpi-panel mb-3" style="border-left:4px solid var(--fkpi-accent)!important;background:linear-gradient(90deg,var(--fkpi-accent-soft),transparent)">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                <div class="fkpi-panel-title mb-0">{{ $t['priority_panel_title'] }}</div>
                            </div>
                            <p class="small text-muted mb-3">{{ $t['priority_panel_subtitle'] }}</p>
                            <p class="small text-muted mb-2"><i class="fas fa-hand-pointer me-1 opacity-75"></i>{{ $t['priority_click_hint'] }}</p>
                            <template x-if="!raw.priority_regions || raw.priority_regions.length === 0">
                                <p class="mb-0 small text-muted fst-italic">{{ $t['priority_empty'] }}</p>
                            </template>
                            <div class="fkpi-table-wrap" x-show="raw.priority_regions && raw.priority_regions.length">
                                <table class="fkpi-table">
                                    <thead>
                                        <tr>
                                            <th>{{ $t['priority_col_rank'] }}</th>
                                            <th>{{ $t['priority_col_area'] }}</th>
                                            <th>{{ $t['priority_col_bookings'] }}</th>
                                            <th>{{ $t['priority_col_active_tours'] }}</th>
                                            <th>{{ $t['priority_col_pressure'] }}</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(pr, idx) in raw.priority_regions" :key="pr.display + '-' + idx">
                                            <tr @click="countrySel = pr.country_key" :class="{ 'table-warning': pr.tier === 'high' }" style="cursor:pointer" :title="'{{ $t['priority_click_hint'] }}'">
                                                <td class="fkpi-mono text-muted" x-text="idx + 1"></td>
                                                <td>
                                                    <span class="fw-semibold" x-text="pr.display"></span>
                                                </td>
                                                <td class="fkpi-mono" x-text="pr.demand"></td>
                                                <td class="fkpi-mono" x-text="pr.supply_active"></td>
                                                <td class="fkpi-mono fw-semibold" style="color:var(--fkpi-accent)" x-text="'×' + pr.pressure"></td>
                                                <td>
                                                    <span class="badge rounded-pill small" :class="pr.tier === 'high' ? 'bg-danger text-white' : (pr.tier === 'medium' ? 'bg-warning text-dark' : 'bg-light text-secondary border')" x-text="pr.tier_label"></span>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-lg-6">
                                <div class="fkpi-panel h-100">
                                    <div class="fkpi-panel-title">{{ $t['demand_regions'] }}</div>
                                    <template x-for="r in activeCountryBlock.regions" :key="r.region">
                                        <div class="mb-2">
                                            <div class="d-flex justify-content-between small mb-1">
                                                <span class="text-truncate me-2" x-text="r.region"></span>
                                                <span class="fkpi-mono text-muted" x-text="r.bookings + ' · ' + fmtEuro(r.revenue)"></span>
                                            </div>
                                            <div class="rounded-pill bg-light" style="height:6px;overflow:hidden">
                                                <div class="h-100 rounded-pill" :style="'width:' + barPct(r.bookings, regionDemandMax) + '%;background:' + activeCountryBlock.color"></div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="fkpi-panel h-100">
                                    <div class="fkpi-panel-title">{{ $t['supply_regions'] }}</div>
                                    <template x-for="s in activeCountryBlock.supply_regions" :key="s.region">
                                        <div class="mb-2">
                                            <div class="d-flex justify-content-between small mb-1">
                                                <span class="text-truncate me-2" x-text="s.region"></span>
                                                <span class="fkpi-mono"><span class="text-success" x-text="s.active + ' {{ $t['col_active'] }}'"></span><span class="text-danger" x-show="s.inactive" x-text="' · ' + s.inactive + ' {{ $t['col_inactive'] }}'"></span></span>
                                            </div>
                                            <div class="rounded-pill bg-light d-flex" style="height:6px;overflow:hidden">
                                                <div class="h-100" :style="'width:' + barPct(s.active, regionSupplyMax) + '%;background:var(--fkpi-success)'"></div>
                                                <div class="h-100" :style="'width:' + barPct(s.inactive, regionSupplyMax) + '%;background:rgba(220,38,38,.35)'"></div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <template x-if="countrySel !== '__total__' && activeCountryBlock.top_tours && activeCountryBlock.top_tours.length">
                            <div class="fkpi-panel mb-3">
                                <div class="fkpi-panel-title">{{ $t['top_tours'] }}</div>
                                <div class="row g-2">
                                    <template x-for="(tour, idx) in activeCountryBlock.top_tours" :key="tour.title">
                                        <div class="col-md-4">
                                            <div class="p-3 rounded-3 border h-100" style="border-color: var(--fkpi-border) !important;">
                                                <div class="fkpi-mono text-muted small mb-1" x-text="String(idx+1).padStart(2,'0')"></div>
                                                <div class="fw-semibold small" x-text="tour.title"></div>
                                                <div class="fkpi-mono small mt-2"><span :style="'color:'+activeCountryBlock.color" x-text="tour.bookings + ' {{ $t['col_bookings'] }}'"></span> · <span class="text-muted" x-text="fmtEuro(tour.avg_price) + ' {{ $t['avg_suffix'] }}'"></span></div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                        <div class="fkpi-panel">
                            <div class="fkpi-panel-title">{{ $t['country_compare'] }}</div>
                            <div class="fkpi-table-wrap">
                                <table class="fkpi-table">
                                    <thead>
                                        <tr>
                                            <th>{{ $t['col_country'] }}</th>
                                            <th>{{ $t['col_tours'] }}</th>
                                            <th>{{ $t['col_active'] }}</th>
                                            <th>{{ $t['col_bookings'] }}</th>
                                            <th>GMV</th>
                                            <th>{{ $t['col_platform'] }}</th>
                                            <th>{{ $t['col_guides'] }}</th>
                                            <th>{{ $t['col_avg'] }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="c in countriesSorted" :key="c.id">
                                            <tr @click="countrySel = c.id" :style="countrySel === c.id ? 'background:rgba(79,70,229,.08)' : ''">
                                                <td><span class="fw-semibold" :style="'color:'+c.color" x-text="c.label"></span></td>
                                                <td class="fkpi-mono" x-text="fmtInt(c.total_tours)"></td>
                                                <td class="fkpi-mono text-success" x-text="fmtInt(c.active_tours)"></td>
                                                <td class="fkpi-mono" x-text="fmtInt(countryYearStats(c).bookings)"></td>
                                                <td class="fkpi-mono fw-semibold" :style="'color:'+c.color" x-text="fmtEuro(countryYearStats(c).gmv)"></td>
                                                <td class="fkpi-mono" style="color:var(--fkpi-primary)" x-text="fmtEuro(countryYearStats(c).platform)"></td>
                                                <td class="fkpi-mono" style="color:var(--fkpi-success)" x-text="fmtEuro(countryYearStats(c).guide)"></td>
                                                <td class="fkpi-mono text-muted" x-text="countryYearStats(c).bookings ? fmtEuro(countryYearStats(c).gmv / countryYearStats(c).bookings) : '—'"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Revenue --}}
                    <div x-show="tab === 'revenue'" x-cloak>
                        <div class="fkpi-stat-grid mb-3">
                            <div class="fkpi-stat">
                                <div class="fkpi-stat-label">GMV</div>
                                <div class="fkpi-stat-value fkpi-mono" style="color: var(--fkpi-accent);" x-text="fmtEuro(totalsFiltered.gmv)"></div>
                                <div class="small text-muted" x-text="totalsFiltered.bookings ? (fmtEuro(totalsFiltered.gmv / totalsFiltered.bookings) + ' / {{ $t['col_booking'] }}') : ''"></div>
                            </div>
                            <div class="fkpi-stat">
                                <div class="fkpi-stat-label">{{ $t['col_platform'] }}</div>
                                <div class="fkpi-stat-value fkpi-mono" style="color: var(--fkpi-primary);" x-text="fmtEuro(totalsFiltered.platform)"></div>
                                <div class="small text-muted" x-text="platformPct + '% GMV'"></div>
                            </div>
                            <div class="fkpi-stat">
                                <div class="fkpi-stat-label">{{ $t['col_guides'] }}</div>
                                <div class="fkpi-stat-value fkpi-mono" style="color: var(--fkpi-success);" x-text="fmtEuro(totalsFiltered.guide)"></div>
                            </div>
                            <div class="fkpi-stat">
                                <div class="fkpi-stat-label">{{ $t['col_bookings'] }}</div>
                                <div class="fkpi-stat-value fkpi-mono text-dark" x-text="fmtInt(totalsFiltered.bookings)"></div>
                            </div>
                        </div>
                        <div class="fkpi-panel">
                            <div class="fkpi-panel-title">{{ $t['gmv_split'] }}</div>
                            <div class="d-flex rounded-3 overflow-hidden mb-2" style="height:28px">
                                <div class="d-flex align-items-center justify-content-center text-white fkpi-mono small fw-bold" :style="'flex:'+Math.max(totalsFiltered.platform,1)+';min-width:36px;background:var(--fkpi-primary)'" x-text="platformPct + '%'"></div>
                                <div class="d-flex align-items-center justify-content-center fkpi-mono small fw-bold" :style="'flex:'+Math.max(totalsFiltered.guide,1)+';min-width:36px;background:var(--fkpi-success-soft);color:var(--fkpi-success)'" x-text="(100-platformPct) + '%'"></div>
                            </div>
                            <div class="d-flex flex-wrap gap-3 small">
                                <span><span class="d-inline-block rounded-circle me-1" style="width:8px;height:8px;background:var(--fkpi-primary)"></span> {{ $t['col_platform'] }} <strong class="fkpi-mono" x-text="fmtEuro(totalsFiltered.platform)"></strong></span>
                                <span><span class="d-inline-block rounded-circle me-1" style="width:8px;height:8px;background:var(--fkpi-success)"></span> {{ $t['col_guides'] }} <strong class="fkpi-mono" x-text="fmtEuro(totalsFiltered.guide)"></strong></span>
                            </div>
                        </div>
                        <div class="fkpi-panel">
                            <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                                <div class="fkpi-panel-title mb-0">{{ $t['monthly_trend'] }}</div>
                                <div class="d-flex flex-wrap gap-1">
                                    <button type="button" class="fkpi-chip" :class="{ 'is-on': chartMetric === 'gmv' }" @click="chartMetric = 'gmv'">GMV</button>
                                    <button type="button" class="fkpi-chip" :class="{ 'is-on': chartMetric === 'platform' }" @click="chartMetric = 'platform'">{{ $t['col_platform'] }}</button>
                                    <button type="button" class="fkpi-chip" :class="{ 'is-on': chartMetric === 'guide' }" @click="chartMetric = 'guide'">{{ $t['col_guides'] }}</button>
                                    <button type="button" class="fkpi-chip" :class="{ 'is-on': chartMetric === 'bookings' }" @click="chartMetric = 'bookings'">{{ $t['col_bookings'] }}</button>
                                </div>
                            </div>
                            <div class="fkpi-bar-row">
                                <template x-for="m in monthsFiltered" :key="m.ym">
                                    <div class="fkpi-bar-col">
                                        <div class="fkpi-bar" :title="m.label + ': ' + (chartMetric === 'bookings' ? m.bookings : fmtEuro(m[chartMetric]))"
                                            :style="'height:' + barHeight(m[chartMetric], chartMax) + 'px;background:' + chartColor"></div>
                                        <span class="text-muted" style="font-size:0.6rem;transform:rotate(-38deg);transform-origin:center;white-space:nowrap;margin-top:2px" x-text="m.label"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div class="fkpi-panel">
                            <div class="fkpi-panel-title">{{ $t['revenue_by_country'] }}</div>
                            <template x-for="c in countriesSorted" :key="'rev'+c.id">
                                <div class="mb-3" x-show="countryYearStats(c).gmv > 0">
                                    <div class="d-flex justify-content-between small mb-1 flex-wrap gap-1">
                                        <span class="fw-semibold" :style="'color:'+c.color" x-text="c.label"></span>
                                        <span class="fkpi-mono text-muted small" x-text="'GMV ' + fmtEuro(countryYearStats(c).gmv) + ' · {{ $t['col_platform'] }} ' + fmtEuro(countryYearStats(c).platform)"></span>
                                    </div>
                                    <div class="rounded-pill overflow-hidden d-flex w-100" style="height:7px">
                                        <div :style="'flex:'+Math.max(countryYearStats(c).platform,1)+';background:var(--fkpi-primary)'"></div>
                                        <div :style="'flex:'+Math.max(countryYearStats(c).guide,1)+';background:var(--fkpi-success-soft)'"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Supply --}}
                    <div x-show="tab === 'supply'" x-cloak>
                        <p class="small text-muted mb-3">{{ $t['expand_hint'] }}</p>
                        <div class="row g-2 mb-3">
                            <template x-for="kpi in supplyKpiList" :key="kpi.id">
                                <div class="col-md-6 col-xl-3">
                                    <div class="fkpi-kpi-card h-100" :class="{ 'is-open': openKpi === kpi.id }" @click="openKpi = openKpi === kpi.id ? null : kpi.id">
                                        <div class="d-flex justify-content-between gap-2">
                                            <div>
                                                <div class="fw-semibold small" x-text="kpi.name"></div>
                                                <div class="text-muted" style="font-size:0.7rem" x-text="kpi.sub"></div>
                                            </div>
                                            <div class="text-end">
                                                <div class="fs-5 fw-bold fkpi-mono" style="color:var(--fkpi-success)" x-text="kpi.display"></div>
                                                <div class="small" :class="kpi.trendUp ? 'text-success' : 'text-danger'" x-text="(kpi.trendUp ? '▲ ' : '▼ ') + kpi.trendStr"></div>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-end mt-2 gap-2">
                                            <svg class="fkpi-spark" width="120" height="36" viewBox="0 0 120 36" preserveAspectRatio="none">
                                                <polyline fill="none" :points="sparkPoints(kpi.history, '#059669')" stroke="#059669" stroke-width="1.5" stroke-linecap="round" opacity="0.75"></polyline>
                                            </svg>
                                            <div class="flex-grow-1" style="min-width:72px">
                                                <div class="rounded-pill bg-light" style="height:4px;overflow:hidden">
                                                    <div class="h-100 rounded-pill" style="background:linear-gradient(90deg,var(--fkpi-success-soft),var(--fkpi-success));transition:width .3s" :style="'width:' + Math.min(100, (kpi.current/kpi.target)*100) + '%'"></div>
                                                </div>
                                                <div class="d-flex justify-content-between fkpi-mono mt-1" style="font-size:0.65rem;color:var(--fkpi-muted)">
                                                    <span x-text="Math.round(Math.min(100, (kpi.current/kpi.target)*100)) + '%'"></span>
                                                    <span x-text="'→ ' + kpi.targetLabel"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div x-show="openKpi === kpi.id" x-transition.opacity class="small text-muted border-top pt-2 mt-2" x-text="kpi.why"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <div class="fkpi-panel">
                            <div class="fkpi-panel-title">{{ $t['supply_demand_regions'] }}</div>
                            <p class="small text-muted mb-2">{{ $t['tours_never_booked'] }}</p>
                            <template x-for="row in raw.mismatch_regions" :key="row.region">
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span class="fw-medium" x-text="row.region"></span>
                                        <span class="fkpi-mono text-muted"><span class="text-success" x-text="row.supply + ' {{ $t['col_tours'] }}'"></span> · <span style="color:var(--fkpi-primary)" x-text="row.demand + ' {{ $t['col_bookings'] }}'"></span></span>
                                    </div>
                                    <div class="rounded-pill bg-light position-relative" style="height:6px">
                                        <div class="position-absolute top-0 start-0 h-100 rounded-pill" :style="'width:' + barPct(row.demand, mismatchMaxDemand) + '%;background:rgba(79,70,229,.2)'"></div>
                                        <div class="position-absolute top-0 start-0 h-100 rounded-pill" :style="'width:' + barPct(row.supply, mismatchMaxDemand) + '%;background:var(--fkpi-success)'"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Traffic --}}
                    <div x-show="tab === 'traffic'" x-cloak>
                        <div class="fkpi-panel border-primary border-opacity-25" style="border-width:2px!important;background:linear-gradient(135deg,rgba(79,70,229,.06),transparent)">
                            <div class="fw-semibold text-primary mb-2">{{ $t['traffic_coming_soon_title'] }}</div>
                            <p class="small text-muted mb-0">{{ $t['traffic_coming_soon_body'] }}</p>
                        </div>
                        <div class="row g-2">
                            <template x-for="card in trafficPlaceholders" :key="card.title">
                                <div class="col-md-6">
                                    <div class="fkpi-kpi-card opacity-75">
                                        <div class="fw-semibold" x-text="card.title"></div>
                                        <div class="display-6 text-muted my-2 fkpi-mono">—</div>
                                        <div class="small text-muted" x-text="card.hint"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Conversion --}}
                    <div x-show="tab === 'conversion'" x-cloak>
                        <div class="row g-2 mb-3">
                            <template x-for="(kpi, idx) in conversionKpis" :key="idx">
                                <div class="col-md-6">
                                    <div class="fkpi-kpi-card h-100" :class="{ 'is-open': openConv === idx }" @click="openConv = openConv === idx ? null : idx">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <span class="badge rounded-pill me-1" :style="kpi.lag ? 'background:var(--fkpi-accent-soft);color:var(--fkpi-accent)' : 'background:var(--fkpi-success-soft);color:var(--fkpi-success)'" x-text="kpi.lag ? '{{ $t['kpi_lag'] }}' : '{{ $t['kpi_lead'] }}'"></span>
                                                <div class="fw-semibold small mt-1" x-text="kpi.name"></div>
                                                <div class="text-muted" style="font-size:0.7rem" x-text="kpi.sub"></div>
                                            </div>
                                            <div class="fs-4 fw-bold fkpi-mono" style="color:var(--fkpi-accent)" x-text="kpi.val"></div>
                                        </div>
                                        <div x-show="openConv === idx" x-transition.opacity class="small text-muted border-top pt-2 mt-2" x-text="kpi.why"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <div class="fkpi-panel">
                            <div class="fkpi-panel-title">{{ $t['booking_status'] }}</div>
                            <div class="d-flex rounded-pill overflow-hidden mb-3" style="height:8px">
                                <template x-for="s in statusSegments" :key="s.key">
                                    <div :style="'flex:'+s.c+';background:'+s.color"></div>
                                </template>
                            </div>
                            <template x-for="s in statusSegments" :key="'l'+s.key">
                                <div class="d-flex align-items-center gap-2 mb-1 small">
                                    <span class="rounded-circle d-inline-block" :style="'width:8px;height:8px;background:'+s.color"></span>
                                    <span class="flex-grow-1" x-text="s.label"></span>
                                    <span class="fkpi-mono fw-semibold" x-text="s.c"></span>
                                    <span class="fkpi-mono text-muted" style="width:3rem;text-align:right" x-text="statusPct(s.c) + '%'"></span>
                                </div>
                            </template>
                            <div class="mt-3 pt-3 border-top small text-muted" x-text="conversionFooter"></div>
                        </div>
                    </div>

                    <div class="fkpi-panel mt-3" style="background:linear-gradient(90deg,var(--fkpi-primary-soft),transparent);border-left:4px solid var(--fkpi-primary)!important">
                        <div class="fkpi-stat-label mb-1">{{ $t['next_steps'] }}</div>
                        <p class="mb-0 small" x-text="nextStepText"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js_after')
<script>
window.financeAnalyticsDashboard = function (raw) {
    return {
        raw,
        tab: 'laender',
        year: 'all',
        chartMetric: 'gmv',
        countrySel: '__total__',
        openKpi: null,
        openConv: null,
        get yearOptionsList() {
            const opts = [{ key: 'all', label: @json($t['all_years']) }];
            (raw.years || []).forEach(y => opts.push({ key: String(y), label: String(y) }));
            return opts;
        },
        get monthsFiltered() {
            if (this.year === 'all') return raw.months || [];
            const y = parseInt(this.year, 10);
            return (raw.months || []).filter(m => m.year === y);
        },
        get totalsFiltered() {
            return this.monthsFiltered.reduce((a, m) => ({
                bookings: a.bookings + m.bookings,
                gmv: a.gmv + m.gmv,
                platform: a.platform + m.platform,
                guide: a.guide + m.guide,
            }), { bookings: 0, gmv: 0, platform: 0, guide: 0 });
        },
        get platformPct() {
            const t = this.totalsFiltered;
            return t.gmv > 0 ? Math.round((t.platform / t.gmv) * 100) : 0;
        },
        get chartMax() {
            const m = this.chartMetric;
            return Math.max(1, ...this.monthsFiltered.map(x => x[m] || 0));
        },
        get chartColor() {
            if (this.chartMetric === 'platform') return 'var(--fkpi-primary)';
            if (this.chartMetric === 'guide') return 'var(--fkpi-success)';
            if (this.chartMetric === 'bookings') return '#64748b';
            return 'var(--fkpi-accent)';
        },
        countryYearStats(c) {
            if (this.year === 'all') {
                return { bookings: c.bookings, gmv: c.gmv, platform: c.platform, guide: c.guide };
            }
            const y = c.by_year && c.by_year[String(this.year)];
            return y || { bookings: 0, gmv: 0, platform: 0, guide: 0 };
        },
        get countriesSorted() {
            return [...(raw.countries || [])].filter(c => this.countryYearStats(c).gmv > 0)
                .sort((a, b) => this.countryYearStats(b).gmv - this.countryYearStats(a).gmv);
        },
        get activeCountryBlock() {
            if (this.countrySel === '__total__') {
                const cs = raw.countries || [];
                return {
                    color: '#4f46e5',
                    label: '',
                    total_tours: cs.reduce((s, c) => s + c.total_tours, 0),
                    active_tours: cs.reduce((s, c) => s + c.active_tours, 0),
                    inactive_tours: cs.reduce((s, c) => s + c.inactive_tours, 0),
                    tours_with_bookings: cs.reduce((s, c) => s + c.tours_with_bookings, 0),
                    tours_without_bookings: cs.reduce((s, c) => s + c.tours_without_bookings, 0),
                    bookings: this.totalsFiltered.bookings,
                    gmv: this.totalsFiltered.gmv,
                    platform: this.totalsFiltered.platform,
                    guide: this.totalsFiltered.guide,
                    regions: [...cs]
                        .map(c => ({
                            region: c.label,
                            bookings: this.countryYearStats(c).bookings,
                            revenue: this.countryYearStats(c).gmv,
                            color: c.color,
                        }))
                        .filter(r => r.bookings > 0)
                        .sort((a, b) => b.bookings - a.bookings),
                    supply_regions: [...cs].sort((a, b) => b.total_tours - a.total_tours).map(c => ({
                        region: c.label,
                        tours: c.total_tours,
                        active: c.active_tours,
                        inactive: c.inactive_tours,
                    })),
                    top_tours: [],
                };
            }
            const c = (raw.countries || []).find(x => x.id === this.countrySel);
            if (!c) return this.activeCountryBlockFallback();
            const st = this.countryYearStats(c);
            return {
                ...c,
                bookings: st.bookings,
                gmv: st.gmv,
                platform: st.platform,
                guide: st.guide,
                regions: (c.regions || []).map(r => ({ ...r })),
                supply_regions: c.supply_regions || [],
                top_tours: c.top_tours || [],
            };
        },
        activeCountryBlockFallback() {
            return {
                color: '#4f46e5', label: '', total_tours: 0, active_tours: 0, inactive_tours: 0,
                tours_with_bookings: 0, tours_without_bookings: 0,
                bookings: 0, gmv: 0, platform: 0, guide: 0, regions: [], supply_regions: [], top_tours: [],
            };
        },
        get regionDemandMax() {
            const r = this.activeCountryBlock.regions || [];
            return Math.max(1, ...r.map(x => x.bookings));
        },
        get regionSupplyMax() {
            const r = this.activeCountryBlock.supply_regions || [];
            return Math.max(1, ...r.map(x => x.tours || 0));
        },
        get mismatchMaxDemand() {
            const m = raw.mismatch_regions || [];
            return Math.max(1, ...m.map(x => x.demand));
        },
        get countryInsight() {
            const b = this.activeCountryBlock;
            const ar = b.total_tours ? Math.round((b.active_tours / b.total_tours) * 100) : 0;
            const cov = b.total_tours ? Math.round((b.tours_with_bookings / b.total_tours) * 100) : 0;
            if (this.countrySel === '__total__') {
                const lead = (this.countriesSorted[0] && this.countriesSorted[0].label) || '—';
                return {!! json_encode(app()->getLocale() === 'de'
                    ? 'Stärkster Markt nach GMV (Zeitraum): '
                    : 'Strongest market by GMV (period): ') !!} + lead
                    + {!! json_encode(app()->getLocale() === 'de' ? '. Aktivierungsrate Touren: ' : '. Tour activation rate: ') !!} + ar + '%'
                    + {!! json_encode(app()->getLocale() === 'de' ? '. Buchungsabdeckung: ' : '. Booking coverage: ') !!} + cov + '%'
                    + {!! json_encode(app()->getLocale() === 'de' ? '. Touren ohne akzeptierte Buchung: ' : '. Tours without accepted booking: ') !!} + (b.tours_without_bookings || 0);
            }
            const avg = b.bookings ? (b.gmv / b.bookings).toFixed(0) : 0;
            return (b.label || '') + ': GMV ' + this.fmtEuro(b.gmv) + ', Ø ' + avg + ' € / Buchung. ' + (b.tours_without_bookings || 0)
                + {!! json_encode(app()->getLocale() === 'de' ? ' Touren ohne Buchung.' : ' tours with no booking yet.') !!};
        },
        get supplyKpiList() {
            const k = raw.supply_kpis || {};
            const fmtTrend = (n) => (n >= 0 ? '+' : '') + n + '% MoM';
            const card = (id, name, sub, snap, why) => {
                const cur = snap.current;
                const tgt = snap.target;
                const trend = snap.trend_pct;
                const display = id === 'gar' ? Math.round(cur) + '%' : (id === 'atg' ? Number(cur).toFixed(2) : String(Math.round(cur)));
                const targetLabel = id === 'gar' ? Math.round(tgt) + '%' : String(Math.round(tgt));
                return {
                    id, name, sub, display, current: cur, target: tgt, targetLabel,
                    trendStr: fmtTrend(trend), trendUp: trend >= 0,
                    history: snap.history || [], why,
                };
            };
            return [
                card('ag', {!! json_encode(app()->getLocale() === 'de' ? 'Aktive Guidings' : 'Active guidings') !!}, {!! json_encode(app()->getLocale() === 'de' ? 'Veröffentlichte Touren' : 'Published tours') !!}, k.active_guidings, {!! json_encode(app()->getLocale() === 'de' ? 'Anzahl buchbarer Touren (Status veröffentlicht).' : 'Count of bookable tours (published status).') !!}),
                card('atg', {!! json_encode(app()->getLocale() === 'de' ? 'Ø Touren / Guide' : 'Avg tours / guide') !!}, {!! json_encode(app()->getLocale() === 'de' ? 'Angebotstiefe' : 'Depth of offer') !!}, k.avg_tours_per_guide, {!! json_encode(app()->getLocale() === 'de' ? 'Aktive Touren geteilt durch Guides mit mindestens einer aktiven Tour.' : 'Active tours divided by guides with at least one active tour.') !!}),
                card('guides', {!! json_encode(app()->getLocale() === 'de' ? 'Aktive Guides' : 'Active guides') !!}, {!! json_encode(app()->getLocale() === 'de' ? 'Mit mind. 1 aktiver Tour' : 'With ≥1 active tour') !!}, k.active_guides, {!! json_encode(app()->getLocale() === 'de' ? 'Distinct User mit veröffentlichter Tour.' : 'Distinct users with a published tour.') !!}),
                card('gar', {!! json_encode(app()->getLocale() === 'de' ? 'Guide-Aktivierung' : 'Guide activation') !!}, {!! json_encode(app()->getLocale() === 'de' ? 'Aktive / alle mit Touren' : 'Active / all with tours') !!}, k.guide_activation_pct, {!! json_encode(app()->getLocale() === 'de' ? 'Anteil der Guides, die mindestens eine aktive Tour haben.' : 'Share of guides who have at least one active tour.') !!}),
            ];
        },
        get conversionKpis() {
            const c = raw.conversion || {};
            return [
                { name: @json($t['acceptance_rate']), sub: {!! json_encode(app()->getLocale() === 'de' ? 'Akzeptiert / (Akzeptiert + Abgelehnt + Storno)' : 'Accepted / (Accepted + Rejected + Cancelled)') !!}, val: (c.acceptance_rate_closed || 0) + '%', lag: true, why: {!! json_encode(app()->getLocale() === 'de' ? 'Anteil abgeschlossener Anfragen, die akzeptiert wurden.' : 'Share of closed requests that were accepted.') !!} },
                { name: @json($t['rejection_rate']), sub: {!! json_encode(app()->getLocale() === 'de' ? 'Guide lehnt ab' : 'Guide rejects') !!}, val: (c.reject_rate_closed || 0) + '%', lag: true, why: {!! json_encode(app()->getLocale() === 'de' ? 'Hohe Ablehnung kann Buchungsvolumen dämpfen.' : 'High rejection may depress booking volume.') !!} },
                { name: {!! json_encode(app()->getLocale() === 'de' ? 'Gast-Buchungen' : 'Guest bookings') !!}, sub: {!! json_encode(app()->getLocale() === 'de' ? 'Anteil bei akzeptierten Buchungen' : 'Share among accepted bookings') !!}, val: (c.guest_share_accepted_pct || 0) + '%', lag: false, why: {!! json_encode(app()->getLocale() === 'de' ? 'is_guest auf akzeptierten Buchungen.' : 'is_guest flag on accepted bookings.') !!} },
                { name: {!! json_encode(app()->getLocale() === 'de' ? 'Reviews' : 'Reviews') !!}, sub: {!! json_encode(app()->getLocale() === 'de' ? 'Anteil akzeptierter mit Review' : 'Share of accepted with a review') !!}, val: (c.review_rate_accepted_pct || 0) + '%', lag: true, why: {!! json_encode(app()->getLocale() === 'de' ? 'Review-Datensatz oder is_reviewed.' : 'Review record or is_reviewed flag.') !!} },
            ];
        },
        get statusSegments() {
            const b = raw.booking_status || {};
            const total = raw.booking_status_total || 1;
            const order = [
                { key: 'accepted', label: @json($t['status_accepted']), color: 'var(--fkpi-success)' },
                { key: 'rejected', label: @json($t['status_rejected']), color: 'var(--fkpi-danger)' },
                { key: 'cancelled', label: @json($t['status_cancelled']), color: 'var(--fkpi-accent)' },
                { key: 'pending', label: @json($t['status_pending']), color: '#64748b' },
            ];
            const rows = order.map(o => ({ ...o, c: parseInt(b[o.key] || 0, 10) })).filter(o => o.c > 0);
            let other = total - rows.reduce((s, x) => s + x.c, 0);
            if (other > 0) rows.push({ key: '_other', label: @json($t['status_other']), color: '#94a3b8', c: other });
            return rows;
        },
        statusPct(c) {
            const t = raw.booking_status_total || 1;
            return Math.round((c / t) * 1000) / 10;
        },
        get conversionFooter() {
            const c = raw.conversion || {};
            const g = c.guest_share_accepted_pct || 0;
            const r = c.review_rate_accepted_pct || 0;
            return {!! json_encode(app()->getLocale() === 'de' ? 'Gast-Buchungen: ' : 'Guest bookings: ') !!} + g + '% · Reviews: ' + r + '%';
        },
        get trafficPlaceholders() {
            return [
                { title: 'Organic clicks (EN)', hint: 'Google Search Console' },
                { title: 'Organic clicks (DE)', hint: 'Google Search Console' },
                { title: {!! json_encode(app()->getLocale() === 'de' ? 'Plattform-Besucher' : 'Platform visitors') !!}, hint: 'Google Analytics 4' },
                { title: {!! json_encode(app()->getLocale() === 'de' ? 'Keywords' : 'Keywords') !!}, hint: 'GSC / SEO-Tools' },
            ];
        },
        get nextStepText() {
            if (this.tab === 'laender' && raw.laender_next_step_summary) {
                return raw.laender_next_step_summary;
            }
            const map = {
                laender: {!! json_encode(app()->getLocale() === 'de' ? 'Regionen mit hoher Nachfrage und wenig Angebot priorisieren (Akquise, Marketing).' : 'Prioritise regions with high demand and thin supply (recruitment, marketing).') !!},
                revenue: {!! json_encode(app()->getLocale() === 'de' ? 'GMV-Trend mit Provision (cag_percent) beobachten; Ausreißer in Rechnungen prüfen.' : 'Watch GMV trend vs platform share (cag_percent); investigate outliers in invoices.') !!},
                supply: {!! json_encode(app()->getLocale() === 'de' ? 'Touren ohne Buchungen prüfen (Qualität, Fotos, Preis) und Guides aktivieren.' : 'Review tours with no bookings (quality, photos, pricing) and activate more guides.') !!},
                traffic: {!! json_encode(app()->getLocale() === 'de' ? 'GA4 / GSC anbinden oder monatlich exportieren, um Traffic-KPIs zu füllen.' : 'Connect GA4 / GSC or export monthly to populate traffic KPIs.') !!},
                conversion: {!! json_encode(app()->getLocale() === 'de' ? 'Ablehnungsgründe und Checkout-Abbrüche analysieren (Hotjar, Umfrage).' : 'Analyse rejection reasons and checkout drop-offs (Hotjar, surveys).') !!},
            };
            return map[this.tab] || '';
        },
        fmtEuro(n) {
            if (n == null || isNaN(n)) return '—';
            const v = Math.round(Number(n) * 100) / 100;
            return '€' + v.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 });
        },
        fmtInt(n) {
            return Number(n || 0).toLocaleString();
        },
        barPct(val, max) {
            return max > 0 ? Math.max(2, Math.round((val / max) * 100)) : 0;
        },
        barHeight(val, max) {
            const v = val || 0;
            return max > 0 ? Math.max(4, Math.round((v / max) * 72)) : 4;
        },
        sparkPoints(data, color) {
            if (!data || !data.length) return '';
            const w = 120, h = 36, pad = 4;
            const max = Math.max(...data), min = Math.min(...data), range = max - min || 1;
            return data.map((v, i) => {
                const x = pad + (i / (data.length - 1 || 1)) * (w - pad * 2);
                const y = h - pad - ((v - min) / range) * (h - pad * 2);
                return x + ',' + y;
            }).join(' ');
        },
    };
};
</script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
@endsection
