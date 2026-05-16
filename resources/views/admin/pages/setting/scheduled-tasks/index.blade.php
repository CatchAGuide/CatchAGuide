@extends('admin.layouts.app')

@section('title', 'Scheduled tasks')

@push('css_after')
<style>
    .scheduled-tasks-card .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .scheduled-tasks-card table.dataTable {
        width: 100% !important;
        margin: 0 !important;
        table-layout: fixed;
    }
    .scheduled-tasks-card .st-col-on { width: 4.5rem; }
    .scheduled-tasks-card .st-col-cmd {
        word-break: break-word;
        overflow-wrap: anywhere;
    }
    .scheduled-tasks-card .st-col-cmd code {
        white-space: normal;
        font-size: 0.75rem;
        line-height: 1.25;
        display: block;
    }
    .scheduled-tasks-card .st-col-actions { width: 5.5rem; text-align: right; white-space: nowrap; }
    #scheduledTasksTable_wrapper .dataTables_filter,
    #scheduledTasksTable_wrapper .dataTables_length {
        padding: 0.35rem 0.75rem;
    }
    #scheduledTasksTable_wrapper .dataTables_info,
    #scheduledTasksTable_wrapper .dataTables_paginate {
        padding: 0.35rem 0.75rem;
    }
</style>
@endpush

@section('content')
    @php
        $editingKey = session('editing_scheduled_task_key');
        $updateUrls = collect($tasks)->mapWithKeys(function ($t) {
            if (!empty($t['is_custom'])) {
                return [$t['key'] => route('admin.settings.scheduled-tasks.custom.update', $t['id'])];
            }
            return [$t['key'] => route('admin.settings.scheduled-tasks.update', $t['key'])];
        })->all();
    @endphp
    <div class="side-app">
        <div class="main-container container-fluid">
            <div class="page-header d-flex flex-wrap justify-content-between align-items-start gap-2">
                <div>
                    <h1 class="page-title">@yield('title')</h1>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="#">Verwaltung</a></li>
                        <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                    </ol>
                </div>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomScheduledTaskModal">
                    <i class="fe fe-plus me-1"></i> Add custom command
                </button>
            </div>

            @if(session('success'))
                <div class="alert alert-success py-2">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger py-2">
                    <ul class="mb-0 ps-3 small">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <p class="text-muted small mb-2 mb-md-3">
                Server cron must run <code>php artisan schedule:run</code> every minute.
                Verify with <code>php artisan schedule:list</code> or <code>php artisan scheduled-tasks:inspect</code>.
            </p>

            <div class="card scheduled-tasks-card">
                <div class="card-body p-0">
                    <div class="table-responsive px-1 px-md-2 pb-1">
                        <table id="scheduledTasksTable" class="table table-sm table-striped table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center st-col-on">On</th>
                                    <th style="width:22%">Task</th>
                                    <th class="st-col-cmd" style="width:30%">Command</th>
                                    <th style="width:26%">Schedule</th>
                                    <th class="text-end st-col-actions">Edit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tasks as $task)
                                    <tr>
                                        <td class="text-center">
                                            @if($task['enabled'])
                                                <span class="badge bg-success rounded-pill">On</span>
                                            @else
                                                <span class="badge bg-secondary rounded-pill">Off</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-start gap-1 flex-wrap">
                                                @if(!empty($task['is_custom']))
                                                    <span class="badge bg-info text-dark" style="font-size:0.65rem">Custom</span>
                                                @endif
                                                <div>
                                                    <div class="fw-semibold small mb-0">{{ $task['label'] }}</div>
                                                    <div class="text-muted" style="font-size:0.75rem;line-height:1.2">{{ \Illuminate\Support\Str::limit($task['description'] ?? '', 80) }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="st-col-cmd small">
                                            <code title="{{ $task['command'] }}">{{ $task['command'] }}</code>
                                        </td>
                                        <td class="small">{{ $task['schedule_summary'] }}</td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <button type="button"
                                                        class="btn btn-outline-primary edit-scheduled-task"
                                                        title="Edit"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editScheduledTaskModal"
                                                        data-task-key="{{ $task['key'] }}">
                                                    <i class="fe fe-edit"></i>
                                                </button>
                                                @if(!empty($task['is_custom']))
                                                    <form method="POST" action="{{ route('admin.settings.scheduled-tasks.custom.destroy', $task['id']) }}" class="d-inline"
                                                          onsubmit="return confirm('Remove this custom scheduled task?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" title="Remove"><i class="fe fe-trash-2"></i></button>
                                                    </form>
                                                @endif
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

    {{-- Edit modal (built-in + custom) --}}
    <div class="modal fade"
         id="editScheduledTaskModal"
         tabindex="-1"
         aria-labelledby="editScheduledTaskModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title" id="editScheduledTaskModalLabel">Edit scheduled task</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="scheduledTaskEditForm" method="POST" action="">
                    @csrf
                    @method('PUT')
                    <div class="modal-body py-2">
                        <div id="edit-builtin-summary">
                            <p class="small text-muted mb-2" id="scheduled-task-meta"></p>
                            <div class="small mb-3 p-2 bg-light rounded font-monospace text-break" id="scheduled-task-command-display"></div>
                        </div>
                        <div id="edit-custom-fields" class="d-none border-top pt-3 mt-2">
                            <div class="row g-2">
                                <div class="col-12">
                                    <label class="form-label small mb-0">Label <span class="text-danger">*</span></label>
                                    <input type="text" name="label" id="st_label" class="form-control form-control-sm" value="{{ old('label') }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small mb-0">Description</label>
                                    <textarea name="description" id="st_description" rows="2" class="form-control form-control-sm">{{ old('description') }}</textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small mb-0">Artisan command <span class="text-danger">*</span></label>
                                    <input type="text" name="command" id="st_command" class="form-control form-control-sm font-monospace" placeholder="example:command --flag" value="{{ old('command') }}">
                                    <div class="form-text small">Same as you would pass to <code>php artisan …</code> (no shell metacharacters).</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mt-1">
                                        <input type="checkbox" class="form-check-input" name="without_overlapping" id="st_without_overlap" value="1" @checked(old('without_overlapping'))>
                                        <label class="form-check-label small" for="st_without_overlap">Without overlapping</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mt-1">
                                        <input type="checkbox" class="form-check-input" name="run_in_background" id="st_run_bg" value="1" @checked(old('run_in_background'))>
                                        <label class="form-check-label small" for="st_run_bg">Run in background</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small mb-0">Log file (under <code>storage/</code>)</label>
                                    <input type="text" name="append_output_to" id="st_append_log" class="form-control form-control-sm" placeholder="logs/my-task.log" value="{{ old('append_output_to') }}">
                                </div>
                            </div>
                        </div>

                        <div class="row g-2 mt-1">
                            <div class="col-md-4">
                                <label class="form-label small mb-0">Status</label>
                                <select name="is_enabled" id="st_is_enabled" class="form-select form-select-sm">
                                    <option value="1" @selected(old('is_enabled') === true || old('is_enabled') === '1' || old('is_enabled') === 1)>Enabled</option>
                                    <option value="0" @selected(old('is_enabled') === false || old('is_enabled') === '0' || old('is_enabled') === 0)>Disabled</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label small mb-0">Repeat</label>
                                <select name="frequency" id="st_frequency" class="form-select form-select-sm schedule-frequency">
                                    @foreach($frequencies as $freqKey => $freqLabel)
                                        <option value="{{ $freqKey }}" @selected(old('frequency') === $freqKey)>{{ $freqLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4" data-schedule-field="time" style="display:none;">
                                <label class="form-label small mb-0">Time (HH:MM)</label>
                                <input type="text" name="schedule_time" id="st_schedule_time" class="form-control form-control-sm" placeholder="14:30" value="{{ old('schedule_time') }}">
                            </div>
                            <div class="col-md-4" data-schedule-field="dow" style="display:none;">
                                <label class="form-label small mb-0">Weekday</label>
                                <select name="day_of_week" id="st_day_of_week" class="form-select form-select-sm">
                                    @foreach($weekdays as $dow => $name)
                                        <option value="{{ $dow }}" @selected((string) old('day_of_week') === (string) $dow)>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12" data-schedule-field="cron" style="display:none;">
                                <label class="form-label small mb-0">Cron (5 fields)</label>
                                <input type="text" name="cron_expression" id="st_cron_expression" class="form-control form-control-sm font-monospace" placeholder="0 */2 * * *" value="{{ old('cron_expression') }}">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Add custom --}}
    <div class="modal fade" id="addCustomScheduledTaskModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title">Add custom command</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.settings.scheduled-tasks.custom.store') }}">
                    @csrf
                    <div class="modal-body py-2">
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label small mb-0">Label <span class="text-danger">*</span></label>
                                <input type="text" name="label" class="form-control form-control-sm" value="{{ old('label') }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small mb-0">Description</label>
                                <textarea name="description" class="form-control form-control-sm" rows="2">{{ old('description') }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label small mb-0">Artisan command <span class="text-danger">*</span></label>
                                <input type="text" name="command" class="form-control form-control-sm font-monospace" value="{{ old('command') }}" placeholder="cache:warm-files" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small mb-0">Status</label>
                                <select name="is_enabled" class="form-select form-select-sm">
                                    <option value="1" @selected(old('is_enabled', '1') === '1' || old('is_enabled') === true)>Enabled</option>
                                    <option value="0" @selected(old('is_enabled') === '0' || old('is_enabled') === false)>Disabled</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label small mb-0">Repeat</label>
                                <select name="frequency" class="form-select form-select-sm add-frequency">
                                    @foreach($frequencies as $freqKey => $freqLabel)
                                        <option value="{{ $freqKey }}" @selected(old('frequency') === $freqKey)>{{ $freqLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 add-schedule-time" style="display:none;">
                                <label class="form-label small mb-0">Time (HH:MM)</label>
                                <input type="text" name="schedule_time" class="form-control form-control-sm" value="{{ old('schedule_time') }}" placeholder="03:00">
                            </div>
                            <div class="col-md-4 add-schedule-dow" style="display:none;">
                                <label class="form-label small mb-0">Weekday</label>
                                <select name="day_of_week" class="form-select form-select-sm">
                                    @foreach($weekdays as $dow => $name)
                                        <option value="{{ $dow }}" @selected((string) old('day_of_week') === (string) $dow)>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 add-schedule-cron" style="display:none;">
                                <label class="form-label small mb-0">Cron</label>
                                <input type="text" name="cron_expression" class="form-control form-control-sm font-monospace" value="{{ old('cron_expression') }}">
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="without_overlapping" value="1" id="add_without_overlap" @checked(old('without_overlapping'))>
                                    <label class="form-check-label small" for="add_without_overlap">Without overlapping</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="run_in_background" value="1" id="add_run_bg" @checked(old('run_in_background'))>
                                    <label class="form-check-label small" for="add_run_bg">Run in background</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label small mb-0">Log file (under storage/)</label>
                                <input type="text" name="append_output_to" class="form-control form-control-sm" value="{{ old('append_output_to') }}" placeholder="logs/custom.log">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary">Add task</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js_after')
<script>
(function () {
    window.__scheduledTasksByKey = @json(collect($tasks)->keyBy('key'));
    var updateUrls = @json($updateUrls);
    var editingKeyFromServer = @json($editingKey);
    var hasValidationErrors = @json($errors->any());
    var openAddModal = @json(session('open_add_custom_modal'));

    function toggleModalScheduleFields() {
        var $modal = $('#editScheduledTaskModal');
        var freq = $('#st_frequency').val();
        $modal.find('[data-schedule-field]').hide();
        if (freq === 'daily_at' || freq === 'weekly') {
            $modal.find('[data-schedule-field="time"]').show();
        }
        if (freq === 'weekly') {
            $modal.find('[data-schedule-field="dow"]').show();
        }
        if (freq === 'cron') {
            $modal.find('[data-schedule-field="cron"]').show();
        }
    }

    function toggleAddModalSchedule() {
        var f = $('#addCustomScheduledTaskModal .add-frequency').val();
        var $m = $('#addCustomScheduledTaskModal');
        $m.find('.add-schedule-time,.add-schedule-dow,.add-schedule-cron').hide();
        if (f === 'daily_at' || f === 'weekly') $m.find('.add-schedule-time').show();
        if (f === 'weekly') $m.find('.add-schedule-dow').show();
        if (f === 'cron') $m.find('.add-schedule-cron').show();
    }

    function setEditFormMode(isCustom) {
        var $custom = $('#edit-custom-fields');
        var $builtin = $('#edit-builtin-summary');
        if (isCustom) {
            $custom.removeClass('d-none');
            $builtin.addClass('d-none');
            $custom.find('input, textarea, select').prop('disabled', false);
        } else {
            $custom.addClass('d-none');
            $builtin.removeClass('d-none');
            $custom.find('input, textarea, select').prop('disabled', true);
        }
    }

    function applyTaskToModal(task) {
        if (!task || !updateUrls[task.key]) return;
        $('#scheduledTaskEditForm').attr('action', updateUrls[task.key]);
        var custom = !!task.is_custom;
        setEditFormMode(custom);
        if (custom) {
            $('#st_label').val(task.label || '');
            $('#st_description').val(task.description || '');
            $('#st_command').val(task.command || '');
            $('#st_without_overlap').prop('checked', !!task.without_overlapping);
            $('#st_run_bg').prop('checked', !!task.run_in_background);
            $('#st_append_log').val(task.append_output_to || '');
        } else {
            $('#scheduled-task-meta').text(task.label + ' — ' + (task.description || ''));
            $('#scheduled-task-command-display').text(task.command);
        }
        $('#st_is_enabled').val(task.enabled ? '1' : '0');
        $('#st_frequency').val(task.frequency).trigger('change');
        $('#st_schedule_time').val(task.schedule_time || '');
        $('#st_day_of_week').val(task.day_of_week !== undefined && task.day_of_week !== null ? String(task.day_of_week) : '0');
        $('#st_cron_expression').val(task.cron_expression || '');
        toggleModalScheduleFields();
    }

    $(function () {
        $('#scheduledTasksTable').DataTable({
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
            order: [[1, 'asc']],
            columnDefs: [
                { orderable: false, targets: [0, 4] }
            ],
            autoWidth: false,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/de-DE.json'
            }
        });

        $(document).on('click', '.edit-scheduled-task', function () {
            var key = $(this).data('task-key');
            if (hasValidationErrors && editingKeyFromServer && key === editingKeyFromServer) {
                return;
            }
            var task = window.__scheduledTasksByKey[key];
            if (task) applyTaskToModal(task);
        });

        $(document).on('change', '#st_frequency', toggleModalScheduleFields);
        $('#addCustomScheduledTaskModal').on('change', '.add-frequency', toggleAddModalSchedule);

        $('#editScheduledTaskModal').on('hidden.bs.modal', function () {
            $('#scheduledTaskEditForm').attr('action', '');
        });

        if (hasValidationErrors && editingKeyFromServer && window.__scheduledTasksByKey[editingKeyFromServer]) {
            var t = window.__scheduledTasksByKey[editingKeyFromServer];
            $('#scheduledTaskEditForm').attr('action', updateUrls[editingKeyFromServer]);
            setEditFormMode(!!t.is_custom);
            if (t.is_custom) {
                $('#st_label').val(@json(old('label', '')));
                $('#st_description').val(@json(old('description', '')));
                $('#st_command').val(@json(old('command', '')));
            } else {
                $('#scheduled-task-meta').text(t.label + ' — ' + (t.description || ''));
                $('#scheduled-task-command-display').text(t.command);
            }
            toggleModalScheduleFields();
            new bootstrap.Modal(document.getElementById('editScheduledTaskModal')).show();
        }

        if (openAddModal) {
            toggleAddModalSchedule();
            new bootstrap.Modal(document.getElementById('addCustomScheduledTaskModal')).show();
        }
    });
})();
</script>
@endsection
