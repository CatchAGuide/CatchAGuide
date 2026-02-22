@extends('admin.layouts.app')

@section('title', 'Alle Guidings')

@section('css_after')
<style>
    #guidingDetailsModal .guiding-detail-value { white-space: pre-wrap; word-break: break-word; }
    #guidingDetailsModal .modal-dialog-scrollable .modal-body { max-height: 85vh; }
    @media (min-width: 1200px) {
        #guidingDetailsModal .modal-xxl { max-width: 95vw; width: 95vw; }
    }
</style>
@endsection

@section('content')
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">@yield('title')</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Verwaltung</a></li>
                        <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                    </ol>
                </div>

            </div>
            <!-- PAGE-HEADER END -->
            <!-- Row -->
            <div class="row ">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <a href="{{ route('admin.guidings.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Guiding</a>
                        </div>
                        <div class="card-body table-responsive">
                            <table id="guiding-datatable" class="table">
                                <thead>
                                    <tr>
                                        <th class="wd-15p border-bottom-0">ID</th>
                                        <th class="wd-15p border-bottom-0">Name des Guidings</th>
                                        <th class="wd-10p border-bottom-0">Guide Name</th>
                                        <th class="wd-12p border-bottom-0" title="* = main language">Languages</th>
                                        <th class="wd-25p border-bottom-0">Aktionen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($guidings as $guiding)
                                    @php
                                        $mainLang = $guiding->language ?? 'de';
                                        $translationLangs = $guiding->languageTranslations->pluck('language')->toArray();
                                        $availableLangs = array_unique(array_merge([$mainLang], $translationLangs));
                                        sort($availableLangs);
                                    @endphp
                                    <tr>
                                        <td>{{$guiding -> id}}</td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <div class="fw-bold">{{$guiding -> title}}</div>
                                                <div class="text-info">{{$guiding -> location}}</div>
                                            </div>
       
                                        </td>
                                        <td>
                                            <a href="{{route('admin.guides.edit', $guiding->user->id)}}">
                                                {{$guiding -> user->full_name }}
                                            </a>
                                        </td>
                                        <td>
                                            @foreach($availableLangs as $langCode)
                                                <span class="badge me-1 {{ $langCode === $mainLang ? 'bg-primary' : 'bg-secondary' }}">{{ strtoupper($langCode) }}{{ $langCode === $mainLang ? ' *' : '' }}</span>
                                            @endforeach
                                            @if(empty($availableLangs))
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                @if($guiding->status == 1)
                                                    <a href="{{ route('admin.changeGuidingStatus', $guiding->id) }}" title="Guiding deaktivieren" class="btn btn-sm btn-danger"><i class="fa fa-times"></i></a>
                                                @else
                                                    <a href="{{ route('admin.changeGuidingStatus', $guiding->id) }}" title="Guiding aktivieren" class="btn btn-sm btn-success"><i class="fa fa-check"></i></a>
                                                @endif
                                                <a href="{{ route('admin.guidings.edit', $guiding) }}" class="btn btn-sm btn-secondary"><i class="fa fa-pen"></i></a>
                                                <button type="button" class="btn btn-sm btn-primary btn-guiding-details" title="Textdetails anzeigen" data-guiding-id="{{ $guiding->id }}" data-guiding-title="{{ e($guiding->title) }}" data-guiding-location="{{ e($guiding->location ?? '') }}" data-guiding-guide-name="{{ e($guiding->user->full_name ?? '') }}"><i class="fa fa-search"></i></button>
                                                <a href="{{ route('admin.guidings.show', $guiding) }}" class="btn btn-sm btn-outline-primary" title="Seite öffnen"><i class="fa fa-external-link-alt"></i></a>
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

            <div class="row bg-white p-2">
                {{-- <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered text-nowrap border-bottom" id="responsive-datatable">
                                    <thead>
                                    <tr>
                                        <th class="wd-15p border-bottom-0">ID</th>
                                        <th class="wd-15p border-bottom-0">Name des Guidings</th>
                                        <th class="wd-15p border-bottom-0">Ort</th>
                                        <th class="wd-10p border-bottom-0">Guide Name</th>
                                        <th class="wd-25p border-bottom-0">Preis pro Person</th>
                                        <th class="wd-25p border-bottom-0">Preis 2 Personen</th>
                                        <th class="wd-25p border-bottom-0">Preis 3 Personen</th>
                                        <th class="wd-25p border-bottom-0">Aktionen</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($guidings as $guiding)
                                    <tr>
                                        <td>{{$guiding -> id}}</td>
                                        <td>{{$guiding -> title}}</td>
                                        <td>{{$guiding -> location}}</td>
                                        <td>
                                            <a href="{{route('admin.guides.edit', $guiding->user->id)}}">
                                                {{$guiding -> user->full_name }}
                                            </a>
                                        </td>
                                        <td>{{$guiding -> price}} €</td>
                                        <td>
                                            @if($guiding -> price_two_persons)
                                                {{$guiding -> price_two_persons}} €
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($guiding -> price_three_persons)
                                                {{$guiding -> price_three_persons}} €
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                @if($guiding->status == 1)
                                                    <a href="{{ route('admin.changeGuidingStatus', $guiding->id) }}" title="Guiding deaktivieren" class="btn btn-sm btn-danger"><i class="fa fa-times"></i></a>
                                                @else
                                                    <a href="{{ route('admin.changeGuidingStatus', $guiding->id) }}" title="Guiding aktivieren" class="btn btn-sm btn-success"><i class="fa fa-check"></i></a>
                                                @endif
                                                <a href="{{ route('admin.guidings.edit', $guiding) }}" class="btn btn-sm btn-secondary"><i class="fa fa-pen"></i></a>
                                                <a href="{{ route('admin.guidings.show', $guiding) }}" class="btn btn-sm btn-primary"><i class="fa fa-search"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                    </tbody>
                                    @endforeach
                                </table>
                            </div>
                        </div>
                    </div>
                </div> --}}
            </div>
            <!-- End Row -->
        </div>
        <!-- CONTAINER CLOSED -->

    </div>

    <!-- Guiding text details modal -->
    <div class="modal fade" id="guidingDetailsModal" tabindex="-1" aria-labelledby="guidingDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xxl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="guidingDetailsModalLabel">
                        <i class="fa fa-search me-2"></i> Guiding – Textdetails
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <label class="mb-0 small text-muted">Sprache:</label>
                        <select id="guidingDetailsLangSwitch" class="form-select form-select-sm" style="width: auto;">
                            <option value="">—</option>
                        </select>
                        <a id="guidingDetailsShowLink" href="#" class="btn btn-sm btn-outline-primary" target="_blank" title="Seite öffnen"><i class="fa fa-external-link-alt"></i></a>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
                    </div>
                </div>
                <div class="modal-body">
                    <div id="guidingDetailsLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Laden…</span></div>
                        <p class="mt-2 text-muted mb-0">Lade Details…</p>
                    </div>
                    <div id="guidingDetailsContent" class="d-none">
                        <div id="guidingDetailsSections"></div>
                    </div>
                    <div id="guidingDetailsError" class="alert alert-danger d-none mb-0"></div>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('js_after')
@php
    $guidingDetailsLabels = [
        'labels' => [
            'title' => __('newguidings.title'),
            'description' => __('newguidings.overall_summary'),
            'additional_information' => __('newguidings.other_boat_info'),
            'desc_course_of_action' => __('newguidings.course_of_action'),
            'desc_meeting_point' => __('newguidings.meeting_point'),
            'desc_starting_time' => __('newguidings.starting_time'),
            'desc_tour_unique' => __('newguidings.tour_highlights'),
            'requirements' => __('newguidings.requirements_taking_part'),
            'recommendations' => __('newguidings.recommended_preparation'),
            'other_information' => __('newguidings.other_information'),
        ],
        'fieldMeta' => [
            'title' => ['type' => 'string', 'maxlength' => 255],
            'additional_information' => ['type' => 'string', 'maxlength' => 255],
            'description' => ['type' => 'longText', 'rows' => 8],
            'desc_course_of_action' => ['type' => 'text', 'rows' => 4],
            'desc_meeting_point' => ['type' => 'text', 'rows' => 4],
            'desc_starting_time' => ['type' => 'text', 'rows' => 3],
            'desc_tour_unique' => ['type' => 'text', 'rows' => 4],
            'other_information' => ['type' => 'longText', 'rows' => 6],
            'requirements' => ['type' => 'longText', 'rows' => 6],
            'recommendations' => ['type' => 'longText', 'rows' => 6],
        ],
        'noTextDetails' => __('newguidings.no_text_details_for_language'),
        'steps' => [
            ['step' => 1, 'title' => 'Step 1', 'fields' => ['title']],
            ['step' => 2, 'title' => 'Step 2', 'fields' => ['additional_information']],
            ['step' => 4, 'title' => 'Step 4', 'fields' => ['description', 'desc_course_of_action', 'desc_starting_time', 'desc_meeting_point', 'desc_tour_unique']],
            ['step' => 5, 'title' => 'Step 5', 'fields' => ['other_information', 'requirements', 'recommendations']],
        ],
    ];
@endphp
<script>
    window.guidingDetailsTranslations = @json($guidingDetailsLabels);
</script>
<script>
    $(function() {
        $('#guiding-datatable').DataTable({
            order: [[0, 'desc']]
        });

        var guidingDetailsModal = document.getElementById('guidingDetailsModal');
        if (guidingDetailsModal) {
            var bsModal = new bootstrap.Modal(guidingDetailsModal);
            var currentDetails = null;
            var currentGuidingId = null;
            var showBaseUrl = '{{ url("admin/guidings") }}';
            var labels = (window.guidingDetailsTranslations && window.guidingDetailsTranslations.labels) || {};
            var stepsConfig = (window.guidingDetailsTranslations && window.guidingDetailsTranslations.steps) || [];
            var fieldMeta = (window.guidingDetailsTranslations && window.guidingDetailsTranslations.fieldMeta) || {};

            $(document).on('click', '.btn-guiding-details', function() {
                var id = $(this).data('guiding-id');
                var title = $(this).data('guiding-title') || ('Guiding #' + id);
                var location = $(this).data('guiding-location') || '';
                var guideName = $(this).data('guiding-guide-name') || '';
                var modalTitle = 'Guidings ' + id + ' - ' + title + (location ? ' | ' + location : '') + (guideName ? ' | Guide: ' + guideName : '');
                $('#guidingDetailsModalLabel').text(modalTitle);
                $('#guidingDetailsShowLink').attr('href', showBaseUrl + '/' + id);
                $('#guidingDetailsContent').addClass('d-none');
                $('#guidingDetailsError').addClass('d-none');
                $('#guidingDetailsLoading').removeClass('d-none');
                $('#guidingDetailsLangSwitch').empty().append('<option value="">—</option>');
                bsModal.show();

                $.get('{{ url("admin/guidings") }}/' + id + '/details')
                    .done(function(res) {
                        if (res && res.error) {
                            $('#guidingDetailsLoading').addClass('d-none');
                            $('#guidingDetailsError').text(res.message || res.error || 'Details konnten nicht geladen werden.').removeClass('d-none');
                            return;
                        }
                        currentDetails = res;
                        currentGuidingId = id;
                        $('#guidingDetailsLoading').addClass('d-none');
                        if (!res.available_languages || res.available_languages.length === 0) {
                            res.available_languages = [res.main_language || 'de'];
                        }
                        res.available_languages.forEach(function(lang) {
                            var label = lang.toUpperCase();
                            if (lang === 'de') label = 'Deutsch';
                            if (lang === 'en') label = 'English';
                            $('#guidingDetailsLangSwitch').append('<option value="' + lang + '">' + label + '</option>');
                        });
                        var initialLang = res.main_language || res.available_languages[0];
                        $('#guidingDetailsLangSwitch').val(initialLang);
                        renderGuidingDetailsContent(initialLang);
                        $('#guidingDetailsContent').removeClass('d-none');
                    })
                    .fail(function(xhr) {
                        $('#guidingDetailsLoading').addClass('d-none');
                        var errMsg = 'Details konnten nicht geladen werden.';
                        try {
                            if (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error)) {
                                errMsg = xhr.responseJSON.message || xhr.responseJSON.error;
                            }
                        } catch (e) {}
                        $('#guidingDetailsError').text(errMsg).removeClass('d-none');
                    });
            });

            $('#guidingDetailsLangSwitch').on('change', function() {
                var lang = $(this).val();
                if (lang && currentDetails) renderGuidingDetailsContent(lang);
            });

            function getDataForLang(lang) {
                if (!currentDetails) return {};
                if (lang === currentDetails.main_language) return currentDetails.main || {};
                return currentDetails.translations && currentDetails.translations[lang] ? currentDetails.translations[lang] : (currentDetails.main || {});
            }

            function renderGuidingDetailsContent(lang) {
                var data = getDataForLang(lang);
                var steps = stepsConfig.length ? stepsConfig : [
                    { step: 1, title: 'Step 1', fields: ['title'] },
                    { step: 2, title: 'Step 2', fields: ['additional_information'] },
                    { step: 4, title: 'Step 4', fields: ['description', 'desc_course_of_action', 'desc_starting_time', 'desc_meeting_point', 'desc_tour_unique'] },
                    { step: 5, title: 'Step 5', fields: ['other_information', 'requirements', 'recommendations'] }
                ];
                var html = '';

                function renderInput(key, val) {
                    var meta = fieldMeta[key] || { type: 'text', rows: 3 };
                    var label = labels[key] || key;
                    var strVal = (val === undefined || val === null) ? '' : (typeof val === 'object' ? JSON.stringify(val, null, 2) : String(val));
                    var editBtn = '<button type="button" class="btn btn-sm btn-outline-secondary btn-detail-edit ms-1" title="Bearbeiten" data-field="' + escapeAttr(key) + '"><i class="fa fa-pen"></i></button><button type="button" class="btn btn-sm btn-success btn-detail-save d-none ms-1" title="Speichern" data-field="' + escapeAttr(key) + '"><i class="fa fa-check"></i></button>';
                    var labelRow = '<div class="d-flex align-items-center flex-wrap gap-1 mb-1"><label class="form-label small text-muted mb-0">' + escapeHtml(label) + '</label>' + editBtn + '</div>';
                    var wrapStart = '<div class="mb-3 guiding-detail-field-row" data-field="' + escapeAttr(key) + '">' + labelRow;
                    var wrapEnd = '</div>';
                    if (meta.type === 'string' && meta.maxlength) {
                        return wrapStart + '<input type="text" class="form-control form-control-sm guiding-detail-input" readonly maxlength="' + meta.maxlength + '" value="' + escapeAttr(strVal) + '">' + wrapEnd;
                    }
                    var rows = meta.rows || 4;
                    return wrapStart + '<textarea class="form-control form-control-sm guiding-detail-value guiding-detail-input" readonly rows="' + rows + '" style="resize:vertical">' + escapeHtml(strVal) + '</textarea>' + wrapEnd;
                }

                function renderScalar(key, val) {
                    if (val === undefined || val === null) return renderInput(key, '');
                    if (typeof val === 'object' && !Array.isArray(val)) val = JSON.stringify(val);
                    if (Array.isArray(val)) val = val.join(', ');
                    return renderInput(key, String(val));
                }

                function renderList(key, arr) {
                    if (!arr || typeof arr !== 'object') return '';
                    var sectionLabel = labels[key] || key;
                    var html = '<div class="mb-4"><label class="form-label fw-bold mb-2">' + escapeHtml(sectionLabel) + '</label>';
                    if (!Array.isArray(arr)) {
                        html += '<textarea class="form-control form-control-sm guiding-detail-value" readonly rows="4" style="resize:vertical">' + escapeHtml(JSON.stringify(arr, null, 2)) + '</textarea></div>';
                        return html;
                    }
                    arr.forEach(function(item, idx) {
                        var itemLabel = '';
                        var itemValue = '';
                        var itemId = (item && typeof item === 'object' && item.id !== undefined) ? String(item.id) : '';
                        if (item && typeof item === 'object') {
                            itemLabel = (item.name !== undefined && item.name !== null) ? String(item.name) : (item.value !== undefined ? String(item.value).substring(0, 50) + (String(item.value).length > 50 ? '…' : '') : '');
                            itemValue = (item.value !== undefined && item.value !== null) ? String(item.value) : '';
                        } else {
                            itemValue = String(item);
                        }
                        var rows = itemValue.length > 120 ? 4 : 2;
                        var editBtn = '<button type="button" class="btn btn-sm btn-outline-secondary btn-detail-edit ms-1" title="Bearbeiten" data-field="' + escapeAttr(key) + '" data-list-index="' + idx + '" data-list-id="' + escapeAttr(itemId) + '"><i class="fa fa-pen"></i></button><button type="button" class="btn btn-sm btn-success btn-detail-save d-none ms-1" title="Speichern" data-field="' + escapeAttr(key) + '" data-list-index="' + idx + '" data-list-id="' + escapeAttr(itemId) + '"><i class="fa fa-check"></i></button>';
                        html += '<div class="mb-3 guiding-detail-field-row" data-field="' + escapeAttr(key) + '" data-list-index="' + idx + '" data-list-id="' + escapeAttr(itemId) + '"><div class="d-flex align-items-center flex-wrap gap-1 mb-1"><label class="form-label small text-muted mb-0">' + escapeHtml(itemLabel) + '</label>' + editBtn + '</div><textarea class="form-control form-control-sm guiding-detail-value guiding-detail-input" readonly rows="' + rows + '" style="resize:vertical">' + escapeHtml(itemValue) + '</textarea></div>';
                    });
                    html += '</div>';
                    return html;
                }

                function escapeAttr(text) {
                    var div = document.createElement('div');
                    div.textContent = text;
                    return div.innerHTML.replace(/"/g, '&quot;');
                }

                steps.forEach(function(s) {
                    var sectionHtml = '';
                    s.fields.forEach(function(key) {
                        if (['requirements', 'recommendations', 'other_information'].indexOf(key) >= 0) {
                            sectionHtml += renderList(key, data[key]);
                        } else {
                            sectionHtml += renderScalar(key, data[key]);
                        }
                    });
                    html += '<div class="guiding-detail-step mb-4"><h6 class="text-primary border-bottom pb-2 mb-3">' + escapeHtml(s.title) + '</h6>' + (sectionHtml || '<p class="text-muted mb-0">—</p>') + '</div>';
                });

                var noDetails = (window.guidingDetailsTranslations && window.guidingDetailsTranslations.noTextDetails) || 'No text details for this language.';
                $('#guidingDetailsSections').html(html || '<p class="text-muted">' + escapeHtml(noDetails) + '</p>');
            }

            function escapeHtml(text) {
                var div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            $(document).on('click', '#guidingDetailsModal .btn-detail-edit', function() {
                var $row = $(this).closest('.guiding-detail-field-row');
                $row.find('.guiding-detail-input').prop('readonly', false);
                $(this).addClass('d-none');
                $row.find('.btn-detail-save').removeClass('d-none');
            });

            $(document).on('click', '#guidingDetailsModal .btn-detail-save', function() {
                var $btn = $(this);
                var $row = $btn.closest('.guiding-detail-field-row');
                var field = $row.data('field');
                var listIndex = $row.data('list-index');
                var listId = $row.data('list-id');
                var value = $row.find('.guiding-detail-input').val();
                var lang = $('#guidingDetailsLangSwitch').val();
                if (!currentGuidingId || !lang) return;
                var url = showBaseUrl + '/' + currentGuidingId + '/details-field';
                var payload = { field: field, value: value, language: lang, _token: '{{ csrf_token() }}' };
                if (listIndex !== undefined && listIndex !== null && String(listIndex) !== '') payload.list_index = listIndex;
                if (listId !== undefined && listId !== null && String(listId) !== '') payload.list_id = listId;
                $btn.prop('disabled', true);
                $.post(url, payload)
                    .done(function() {
                        if (!currentDetails) return;
                        if (lang === currentDetails.main_language) {
                            if (['requirements', 'recommendations', 'other_information'].indexOf(field) >= 0 && (listIndex !== undefined && listIndex !== null)) {
                                var arr = currentDetails.main[field];
                                if (Array.isArray(arr) && arr[listIndex]) arr[listIndex].value = value;
                            } else {
                                currentDetails.main[field] = value;
                            }
                        } else {
                            if (!currentDetails.translations[lang]) currentDetails.translations[lang] = {};
                            if (['requirements', 'recommendations', 'other_information'].indexOf(field) >= 0 && (listIndex !== undefined && listIndex !== null)) {
                                var arr = currentDetails.translations[lang][field];
                                if (!Array.isArray(arr)) arr = [];
                                if (arr[listIndex]) arr[listIndex].value = value; else arr[listIndex] = { value: value };
                                currentDetails.translations[lang][field] = arr;
                            } else {
                                currentDetails.translations[lang][field] = value;
                            }
                        }
                        renderGuidingDetailsContent(lang);
                    })
                    .fail(function(xhr) {
                        var msg = (xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : 'Speichern fehlgeschlagen.';
                        alert(msg);
                    })
                    .always(function() { $btn.prop('disabled', false); });
            });
        }
    });
</script>
@endsection
