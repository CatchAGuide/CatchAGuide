@extends('admin.layouts.app')

@section('title', __('admin.security.title'))

@section('content')
@php
    $summary = $overview['summary'];
    $badge = ['high' => 'bg-danger', 'medium' => 'bg-warning text-dark', 'low' => 'bg-secondary', 'info' => 'bg-info'];
    $kpis = [
        ['key' => 'events', 'value' => $summary['events'], 'icon' => 'fe-activity', 'color' => 'primary'],
        ['key' => 'ips', 'value' => $summary['ips'], 'icon' => 'fe-globe', 'color' => 'secondary'],
        ['key' => 'attacks', 'value' => $summary['attacks'], 'icon' => 'fe-alert-triangle', 'color' => 'danger'],
        ['key' => 'crawlers', 'value' => $summary['crawlers'], 'icon' => 'fe-search', 'color' => 'info'],
        ['key' => 'blocked', 'value' => $summary['blocked'], 'icon' => 'fe-slash', 'color' => 'warning'],
    ];
@endphp
<div class="side-app">
    <div class="main-container container-fluid">

        <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h1 class="page-title mb-1">{{ __('admin.security.title') }}</h1>
                <p class="text-muted small mb-0">{{ __('admin.security.subtitle') }}</p>
            </div>
            <div>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('admin.security.breadcrumb_dashboard') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('admin.security.nav') }}</li>
                </ol>
            </div>
        </div>

        <div class="alert alert-light border small">
            <i class="fe fe-info me-1"></i>{{ __('admin.security.scope_note', ['days' => config('ddos.threat_intelligence.retention_days', 7)]) }}
        </div>

        <form method="GET" action="{{ route('admin.security.threats') }}" class="row g-2 align-items-end mb-4">
            <div class="col-12 col-sm-4 col-lg-3">
                <label for="hours" class="form-label small text-muted mb-1">{{ __('admin.security.filters.window') }}</label>
                <select id="hours" name="hours" class="form-select">
                    @foreach ($windows as $window)
                        <option value="{{ $window }}" @selected($hours === $window)>{{ __('admin.security.filters.windows.'.$window) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-sm-4 col-lg-3">
                <label for="ip" class="form-label small text-muted mb-1">{{ __('admin.security.filters.ip') }}</label>
                <input id="ip" type="text" name="ip" value="{{ $ip }}" maxlength="45" class="form-control" placeholder="{{ __('admin.security.filters.ip_placeholder') }}">
            </div>
            <div class="col-12 col-sm-4 col-lg-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ __('admin.security.filters.apply') }}</button>
                <a href="{{ route('admin.security.threats') }}" class="btn btn-outline-secondary">{{ __('admin.security.filters.reset') }}</a>
            </div>
        </form>

        <div class="row g-3 mb-4">
            @foreach ($kpis as $kpi)
                <div class="col-6 col-xl">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-shrink-0 bg-{{ $kpi['color'] }} bg-opacity-15 rounded-3 p-3 me-3">
                                <i class="fe {{ $kpi['icon'] }} fa-2x text-{{ $kpi['color'] }}"></i>
                            </div>
                            <div class="min-w-0">
                                <h6 class="text-uppercase text-muted small mb-1">{{ __('admin.security.kpi.'.$kpi['key']) }}</h6>
                                <h3 class="mb-0 fw-bold">{{ $kpi['value'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($summary['truncated'])
            <div class="alert alert-warning small">
                {{ __('admin.security.truncated', ['max' => \App\Services\Security\ThreatOverviewService::MAX_EVENTS]) }}
            </div>
        @endif

        @forelse ($overview['entries'] as $entry)
            @php
                $profile = $entry['profile'];
                $verdict = $entry['verdict'];
            @endphp
            <details class="card border-0 shadow-sm mb-3">
                <summary class="card-body d-flex flex-wrap align-items-center gap-3" style="cursor: pointer;">
                    <span class="badge {{ $badge[$verdict->severity] }}">{{ __('admin.security.severity.'.$verdict->severity) }}</span>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold">{{ __('admin.security.verdicts.'.$verdict->key.'.title') }}</div>
                        <div class="small text-muted text-break">
                            <code>{{ $profile['ip'] }}</code>
                            @if ($profile['reverse_dns'])
                                &middot; {{ $profile['reverse_dns'] }}
                            @endif
                        </div>
                    </div>
                    @if ($entry['blocks'])
                        <span class="badge bg-dark">{{ __('admin.security.currently_blocked') }}</span>
                    @endif
                    <div class="small text-muted text-end">
                        <div>{{ __('admin.security.events_count', ['count' => $profile['events']]) }}</div>
                        <div>{{ $profile['last_seen']->format('d M Y H:i') }}</div>
                    </div>
                </summary>

                <div class="card-body border-top">
                    <div class="row g-4">
                        <div class="col-12 col-lg-6">
                            <h6 class="text-uppercase text-muted small">{{ __('admin.security.detail.what_happened') }}</h6>
                            <p>{{ __('admin.security.verdicts.'.$verdict->key.'.summary') }}</p>

                            <h6 class="text-uppercase text-muted small">{{ __('admin.security.detail.why') }}</h6>
                            <ul>
                                @foreach ($verdict->evidence as $line)
                                    <li>{{ __('admin.security.evidence.'.$line['key'], $line['params']) }}</li>
                                @endforeach
                            </ul>

                            <h6 class="text-uppercase text-muted small">{{ __('admin.security.detail.what_to_do') }}</h6>
                            <p class="mb-0">{{ __('admin.security.verdicts.'.$verdict->key.'.action') }}</p>
                        </div>

                        <div class="col-12 col-lg-6">
                            <dl class="row small mb-3">
                                <dt class="col-sm-4">{{ __('admin.security.detail.first_seen') }}</dt>
                                <dd class="col-sm-8">{{ $profile['first_seen']->format('d M Y H:i') }}</dd>

                                <dt class="col-sm-4">{{ __('admin.security.detail.event_types') }}</dt>
                                <dd class="col-sm-8">
                                    @foreach ($profile['types'] as $type => $count)
                                        {{ __('admin.security.types.'.$type) }} &times;{{ $count }}@if (! $loop->last), @endif
                                    @endforeach
                                </dd>

                                <dt class="col-sm-4">{{ __('admin.security.detail.user_agent') }}</dt>
                                <dd class="col-sm-8 text-break">{{ implode(' | ', $profile['user_agents']) ?: '-' }}</dd>

                                <dt class="col-sm-4">{{ __('admin.security.detail.endpoints') }}</dt>
                                <dd class="col-sm-8 text-break">
                                    @foreach ($profile['endpoints'] as $path => $count)
                                        <div><code>{{ \Illuminate\Support\Str::limit($path, 80) }}</code> &times;{{ $count }}</div>
                                    @endforeach
                                </dd>

                                <dt class="col-sm-4">{{ __('admin.security.detail.threat_score') }}</dt>
                                <dd class="col-sm-8">{{ $profile['max_score'] }}</dd>

                                @foreach ($entry['blocks'] as $block)
                                    <dt class="col-sm-4">{{ __('admin.security.detail.blocked_until') }}</dt>
                                    <dd class="col-sm-8">{{ $block['context'] }}: {{ $block['until']->format('d M Y H:i') }}</dd>
                                @endforeach
                            </dl>

                            <h6 class="text-uppercase text-muted small">{{ __('admin.security.detail.timeline') }}</h6>
                            <ul class="list-unstyled small mb-0">
                                @foreach ($profile['timeline'] as $event)
                                    <li class="text-break">
                                        <span class="text-muted">{{ $event['at']->format('d M H:i:s') }}</span>
                                        &middot; {{ __('admin.security.types.'.$event['type']) }}
                                        &middot; <code>{{ $event['url'] }}</code>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </details>
        @empty
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center text-muted py-5">{{ __('admin.security.empty') }}</div>
            </div>
        @endforelse

    </div>
</div>
@endsection
