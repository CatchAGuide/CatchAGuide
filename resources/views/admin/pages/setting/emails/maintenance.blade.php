@extends('admin.layouts.app')

@section('title', 'Email Templates')

@section('custom_style')
<style>
    .email-catalogue-header {
        background: linear-gradient(135deg, #313041 0%, #4a4a5e 100%);
        border-radius: 12px;
        color: #fff;
        padding: 1.75rem 2rem;
        margin-bottom: 1.5rem;
    }
    .email-catalogue-header h2 {
        color: #fff;
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 0.35rem;
    }
    .email-catalogue-header p {
        color: rgba(255,255,255,0.75);
        margin-bottom: 0;
        font-size: 0.9rem;
    }
    .email-stat-pill {
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.18);
        border-radius: 10px;
        padding: 0.65rem 1rem;
        text-align: center;
        min-width: 90px;
    }
    .email-stat-pill .stat-value {
        font-size: 1.35rem;
        font-weight: 700;
        line-height: 1.2;
    }
    .email-stat-pill .stat-label {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        opacity: 0.8;
    }
    .email-filter-bar {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
    }
    .email-filter-pills .btn {
        border-radius: 20px;
        font-size: 0.82rem;
        padding: 0.35rem 0.9rem;
        margin: 0.15rem;
    }
    .email-filter-pills .btn.active {
        background-color: #e8604c;
        border-color: #e8604c;
        color: #fff;
    }
    .email-template-card {
        border: 1px solid #e9ecef;
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
        height: 100%;
        display: flex;
        flex-direction: column;
        transition: box-shadow 0.2s ease, transform 0.2s ease;
    }
    .email-template-card:hover {
        box-shadow: 0 8px 24px rgba(49, 48, 65, 0.12);
        transform: translateY(-2px);
    }
    .email-thumbnail-wrap {
        position: relative;
        height: 210px;
        overflow: hidden;
        background: #f0f0f2;
        border-bottom: 1px solid #e9ecef;
        cursor: pointer;
    }
    .email-thumbnail-scaler {
        width: 600px;
        height: 800px;
        transform: scale(0.42);
        transform-origin: top left;
        pointer-events: none;
    }
    .email-thumbnail-scaler iframe {
        width: 600px;
        height: 800px;
        border: none;
        background: #fff;
    }
    .email-thumbnail-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to bottom, transparent 60%, rgba(49,48,65,0.55));
        display: flex;
        align-items: flex-end;
        justify-content: center;
        padding-bottom: 0.75rem;
        opacity: 0;
        transition: opacity 0.2s ease;
    }
    .email-thumbnail-wrap:hover .email-thumbnail-overlay {
        opacity: 1;
    }
    .email-thumbnail-overlay span {
        background: rgba(255,255,255,0.95);
        color: #313041;
        font-size: 0.78rem;
        font-weight: 600;
        padding: 0.35rem 0.85rem;
        border-radius: 20px;
    }
    .email-card-badges {
        position: absolute;
        top: 0.6rem;
        left: 0.6rem;
        right: 0.6rem;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        z-index: 2;
        pointer-events: none;
    }
    .email-card-body {
        padding: 1.1rem 1.15rem 0.5rem;
        flex: 1;
    }
    .email-card-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #313041;
        margin-bottom: 0.35rem;
        line-height: 1.3;
    }
    .email-card-desc {
        font-size: 0.8rem;
        color: #6c757d;
        line-height: 1.45;
        margin-bottom: 0.85rem;
    }
    .email-meta-block {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 0.65rem 0.75rem;
        margin-bottom: 0.6rem;
    }
    .email-meta-block:last-of-type {
        margin-bottom: 0;
    }
    .email-meta-label {
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 700;
        color: #868e96;
        margin-bottom: 0.3rem;
    }
    .email-meta-label i {
        margin-right: 0.25rem;
        color: #e8604c;
    }
    .email-meta-value {
        font-size: 0.78rem;
        color: #313041;
        line-height: 1.4;
    }
    .email-trigger-list {
        margin: 0;
        padding-left: 1.1rem;
        font-size: 0.76rem;
        color: #495057;
        line-height: 1.45;
    }
    .email-trigger-list li {
        margin-bottom: 0.15rem;
    }
    .email-card-footer {
        padding: 0.75rem 1.15rem 1rem;
        border-top: 1px solid #f0f0f2;
        display: flex;
        gap: 0.5rem;
        margin-top: auto;
    }
    .email-card-footer .btn {
        flex: 1;
        font-size: 0.78rem;
        border-radius: 8px;
    }
    .email-log-key {
        font-family: monospace;
        font-size: 0.7rem;
        background: #e9ecef;
        padding: 0.1rem 0.35rem;
        border-radius: 4px;
        color: #495057;
    }
    .email-search-input {
        border-radius: 20px;
        font-size: 0.85rem;
        padding-left: 2.25rem;
    }
    .email-search-wrap {
        position: relative;
    }
    .email-search-wrap i {
        position: absolute;
        left: 0.85rem;
        top: 50%;
        transform: translateY(-50%);
        color: #adb5bd;
        font-size: 0.85rem;
    }
    #emailPreviewContent {
        max-height: 75vh;
        overflow-y: auto;
    }
    #emailPreviewContent iframe,
    #emailPreviewContent table {
        max-width: 100%;
    }
    .email-no-results {
        text-align: center;
        padding: 3rem 1rem;
        color: #6c757d;
    }
</style>
@endsection

@section('content')
    <div class="side-app">
        <div class="main-container container-fluid">

            <div class="page-header">
                <h1 class="page-title">@yield('title')</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Verwaltung</a></li>
                        <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                    </ol>
                </div>
            </div>

            {{-- Summary header --}}
            <div class="email-catalogue-header">
                <div class="row align-items-center">
                    <div class="col-lg-7 mb-3 mb-lg-0">
                        <h2><i class="fa fa-envelope-open-text me-2"></i>Platform Email Catalogue</h2>
                        <p>Overview of all automatic platform emails — what they look like, when they send, and what triggers them.</p>
                    </div>
                    <div class="col-lg-5">
                        <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                            <div class="email-stat-pill">
                                <div class="stat-value">{{ $stats['total'] }}</div>
                                <div class="stat-label">Templates</div>
                            </div>
                            <div class="email-stat-pill">
                                <div class="stat-value">{{ $stats['active'] }}</div>
                                <div class="stat-label">Active</div>
                            </div>
                            <div class="email-stat-pill">
                                <div class="stat-value">{{ $stats['immediate'] }}</div>
                                <div class="stat-label">Immediate</div>
                            </div>
                            <div class="email-stat-pill">
                                <div class="stat-value">{{ $stats['scheduled'] }}</div>
                                <div class="stat-label">Scheduled</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <div class="email-filter-bar">
                <div class="row align-items-center g-2">
                    <div class="col-lg-7">
                        <div class="email-filter-pills d-flex flex-wrap align-items-center">
                            <span class="text-muted me-2 small fw-semibold">Category:</span>
                            <button type="button" class="btn btn-sm btn-outline-secondary filter-category active" data-category="all">All</button>
                            @foreach($categories as $catKey => $cat)
                                <button type="button" class="btn btn-sm btn-outline-secondary filter-category" data-category="{{ $catKey }}">
                                    <i class="fa {{ $cat['icon'] }} me-1"></i>{{ $cat['label'] }}
                                </button>
                            @endforeach
                            <span class="text-muted ms-3 me-2 small fw-semibold d-none d-md-inline">Timing:</span>
                            @foreach($triggerTypes as $typeKey => $type)
                                <button type="button" class="btn btn-sm btn-outline-secondary filter-trigger d-none d-md-inline-block" data-trigger="{{ $typeKey }}">
                                    <i class="fa {{ $type['icon'] }} me-1"></i>{{ $type['label'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="email-search-wrap">
                            <i class="fa fa-search"></i>
                            <input type="text" id="emailSearchInput" class="form-control email-search-input" placeholder="Search templates, triggers, log keys…">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card grid --}}
            <div class="row" id="emailTemplateGrid">
                @foreach($emailTemplates as $template)
                    @php
                        $catMeta = $categories[$template['category']] ?? ['label' => ucfirst($template['category']), 'color' => 'secondary', 'icon' => 'fa-envelope'];
                        $triggerMeta = $triggerTypes[$template['trigger_type']] ?? ['label' => ucfirst($template['trigger_type']), 'color' => 'secondary', 'icon' => 'fa-circle'];
                        $statusMeta = $statuses[$template['status']] ?? ['label' => ucfirst($template['status']), 'color' => 'secondary'];
                        $searchText = strtolower(implode(' ', [
                            $template['name'],
                            $template['description'],
                            $template['recipient'],
                            $template['schedule'] ?? '',
                            $template['log_type'] ?? '',
                            $template['scheduler_command'] ?? '',
                            implode(' ', $template['trigger_conditions'] ?? []),
                        ]));
                    @endphp
                    <div class="col-xl-4 col-lg-6 mb-4 email-template-col"
                         data-category="{{ $template['category'] }}"
                         data-trigger="{{ $template['trigger_type'] }}"
                         data-status="{{ $template['status'] }}"
                         data-search="{{ $searchText }}">
                        <div class="email-template-card">
                            {{-- Thumbnail --}}
                            <div class="email-thumbnail-wrap preview-email-btn"
                                 data-template="{{ $template['template_key'] }}"
                                 data-locale="en"
                                 title="Click to preview">
                                <div class="email-card-badges">
                                    <span class="badge bg-{{ $catMeta['color'] }}">
                                        <i class="fa {{ $catMeta['icon'] }} me-1"></i>{{ $catMeta['label'] }}
                                    </span>
                                    <span class="badge bg-{{ $statusMeta['color'] }}">{{ $statusMeta['label'] }}</span>
                                </div>
                                <div class="email-thumbnail-scaler">
                                    <iframe src="{{ $template['preview_url_en'] }}"
                                            loading="lazy"
                                            title="Preview: {{ $template['name'] }}"
                                            tabindex="-1"></iframe>
                                </div>
                                <div class="email-thumbnail-overlay">
                                    <span><i class="fa fa-expand me-1"></i> Preview full email</span>
                                </div>
                            </div>

                            <div class="email-card-body">
                                <div class="email-card-title">{{ $template['name'] }}</div>
                                <div class="email-card-desc">{{ $template['description'] }}</div>

                                <div class="email-meta-block">
                                    <div class="email-meta-label">
                                        <i class="fa fa-bolt"></i> Trigger
                                        <span class="badge bg-{{ $triggerMeta['color'] }} ms-1" style="font-size:0.62rem;">{{ $triggerMeta['label'] }}</span>
                                    </div>
                                    <ul class="email-trigger-list">
                                        @foreach($template['trigger_conditions'] as $condition)
                                            <li>{{ $condition }}</li>
                                        @endforeach
                                    </ul>
                                </div>

                                <div class="email-meta-block">
                                    <div class="email-meta-label"><i class="fa fa-clock"></i> When it runs</div>
                                    <div class="email-meta-value">
                                        <strong>{{ $template['schedule'] }}</strong>
                                        @if(!empty($template['scheduler_command']))
                                            <br><span class="text-muted">Command:</span>
                                            <code class="email-log-key">{{ $template['scheduler_command'] }}</code>
                                        @endif
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap gap-2 mt-2 align-items-center">
                                    <span class="badge bg-light text-dark border" style="font-size:0.7rem;">
                                        <i class="fa fa-user me-1 text-muted"></i>{{ $template['recipient'] }}
                                    </span>
                                    @if(!empty($template['log_type']))
                                        <span class="email-log-key" title="Email log type">{{ $template['log_type'] }}</span>
                                    @endif
                                    @if(!empty($template['pdf_ref']))
                                        <span class="badge bg-light text-muted border" style="font-size:0.65rem;">PDF {{ $template['pdf_ref'] }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="email-card-footer">
                                <button class="btn btn-outline-primary btn-sm preview-email-btn"
                                        data-template="{{ $template['template_key'] }}"
                                        data-locale="en">
                                    <span class="fi fi-gb me-1"></span> EN
                                </button>
                                <button class="btn btn-outline-secondary btn-sm preview-email-btn"
                                        data-template="{{ $template['template_key'] }}"
                                        data-locale="de">
                                    <span class="fi fi-de me-1"></span> DE
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div id="emailNoResults" class="email-no-results d-none">
                <i class="fa fa-search fa-2x mb-3 d-block text-muted"></i>
                <p class="mb-0">No email templates match your filters.</p>
            </div>

        </div>
    </div>

    {{-- Preview modal --}}
    <div class="modal fade" id="emailPreviewModal" tabindex="-1" role="dialog" aria-labelledby="emailPreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="emailPreviewModalLabel">
                        <i class="fa fa-envelope"></i> Email Preview: <span id="templateName"></span> (<span id="templateLocale"></span>)
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="emailPreviewContent">
                    <div class="text-center py-4">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading email preview…</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="openInNewTab">
                        <i class="fa fa-external-link-alt"></i> Open in New Tab
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js_after')
<script>
    $(function() {
        let currentTemplate = '';
        let currentLocale = '';
        let activeCategory = 'all';
        let activeTrigger = null;

        function applyFilters() {
            const query = $('#emailSearchInput').val().toLowerCase().trim();
            let visible = 0;

            $('.email-template-col').each(function() {
                const $col = $(this);
                const matchCategory = activeCategory === 'all' || $col.data('category') === activeCategory;
                const matchTrigger = !activeTrigger || $col.data('trigger') === activeTrigger;
                const matchSearch = !query || ($col.data('search') || '').includes(query);
                const show = matchCategory && matchTrigger && matchSearch;

                $col.toggle(show);
                if (show) visible++;
            });

            $('#emailNoResults').toggleClass('d-none', visible > 0);
        }

        $('.filter-category').on('click', function() {
            $('.filter-category').removeClass('active');
            $(this).addClass('active');
            activeCategory = $(this).data('category');
            applyFilters();
        });

        $('.filter-trigger').on('click', function() {
            const trigger = $(this).data('trigger');
            if ($(this).hasClass('active')) {
                $(this).removeClass('active');
                activeTrigger = null;
            } else {
                $('.filter-trigger').removeClass('active');
                $(this).addClass('active');
                activeTrigger = trigger;
            }
            applyFilters();
        });

        $('#emailSearchInput').on('input', applyFilters);

        function openPreview(template, locale) {
            currentTemplate = template;
            currentLocale = locale;

            $('#emailPreviewModal').modal('show');
            $('#emailPreviewContent').html(`
                <div class="text-center py-4">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading email preview…</p>
                </div>
            `);

            $.ajax({
                url: `{{ route('admin.settings.email.preview.ajax', ['template' => 'TEMPLATE_PLACEHOLDER', 'locale' => 'LOCALE_PLACEHOLDER']) }}`
                    .replace('TEMPLATE_PLACEHOLDER', currentTemplate)
                    .replace('LOCALE_PLACEHOLDER', currentLocale),
                type: 'GET',
                success: function(response) {
                    $('#templateName').text(response.template_name);
                    $('#templateLocale').text(response.locale.toUpperCase());
                    $('#emailPreviewContent').html(response.html);
                },
                error: function(xhr, status, error) {
                    $('#emailPreviewContent').html(`
                        <div class="alert alert-danger">
                            <h5>Error Loading Preview</h5>
                            <p>There was an error loading the email preview. Please try again.</p>
                            <small class="text-muted">${error}</small>
                        </div>
                    `);
                }
            });
        }

        $(document).on('click', '.preview-email-btn', function(e) {
            e.preventDefault();
            openPreview($(this).data('template'), $(this).data('locale'));
        });

        $('#openInNewTab').on('click', function() {
            if (currentTemplate && currentLocale) {
                const url = `{{ route('admin.settings.email.preview', ['template' => 'TEMPLATE_PLACEHOLDER', 'locale' => 'LOCALE_PLACEHOLDER']) }}`
                    .replace('TEMPLATE_PLACEHOLDER', currentTemplate)
                    .replace('LOCALE_PLACEHOLDER', currentLocale);
                window.open(url, '_blank');
            }
        });

        $('#emailPreviewModal').on('hidden.bs.modal', function() {
            currentTemplate = '';
            currentLocale = '';
        });
    });
</script>
@endsection
