@extends('admin.layouts.app')

@section('title', 'Alle Mitarbeiter')

@section('content')
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">@yield('title')</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">System</a></li>
                        <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                    </ol>
                </div>

            </div>
            <!-- PAGE-HEADER END -->

            {{-- Message modal (success or error after action) --}}
            <div class="modal fade" id="employeeMessageModal" tabindex="-1" aria-labelledby="employeeMessageModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header" id="employeeMessageModalHeader">
                            <h5 class="modal-title" id="employeeMessageModalLabel"></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="employeeMessageModalBody"></div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Confirmation modal (before reset password or delete) --}}
            <div class="modal fade" id="employeeConfirmModal" tabindex="-1" aria-labelledby="employeeConfirmModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="employeeConfirmModalLabel"></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="employeeConfirmModalBody"></div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('admin.employees.modal_cancel') }}</button>
                            <button type="button" class="btn btn-primary" id="employeeConfirmModalSubmit"></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row -->
            <div class="row row-sm">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <a href="{{ route('admin.employees.create') }}" class="btn btn-primary">Mitarbeiter erstellen</a>
                            <a href="{{ route('admin.employees.trashed') }}" class="btn btn-outline-secondary">{{ __('admin.employees.view_trashed') }}</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered text-nowrap border-bottom" id="responsive-datatable">
                                    <thead>
                                    <tr>
                                        <th class="wd-15p border-bottom-0">Name</th>
                                        <th class="wd-15p border-bottom-0">E-Mail Adresse</th>
                                        <th class="wd-15p border-bottom-0">{{ __('admin.employees.password_reset_by') }} / {{ __('admin.employees.password_reset_at') }}</th>
                                        <th class="wd-15p border-bottom-0 text-center">Aktionen</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($employees as $employee)
                                            <tr>
                                                <td>{{ $employee->name }}</td>
                                                <td>{{ $employee->email }}</td>
                                                <td>
                                                    @if($employee->password_reset_at)
                                                        {{ $employee->passwordResetByUser?->name ?? '—' }}
                                                        <br><small class="text-muted">{{ $employee->password_reset_at?->format('d.m.Y H:i') }}</small>
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-sm btn-secondary" title="Bearbeiten"><i class="fa fa-pen"></i></a>
                                                    <form action="{{ route('admin.employees.reset-password', $employee) }}" method="post" class="d-inline" id="form-reset-{{ $employee->id }}">
                                                        @csrf
                                                        <button type="button" class="btn btn-sm btn-info btn-employee-confirm" title="{{ __('admin.employees.password_reset') }}" data-form-id="form-reset-{{ $employee->id }}" data-confirm-title="{{ __('admin.employees.confirm_reset_title') }}" data-confirm-message="{{ __('admin.employees.confirm_reset_message') }}" data-confirm-btn="{{ __('admin.employees.password_reset') }}"><i class="fa fa-key"></i></button>
                                                    </form>
                                                    @if($employee->id !== auth('employees')->id())
                                                        <form action="{{ route('admin.employees.destroy', $employee) }}" method="post" class="d-inline" id="form-delete-{{ $employee->id }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="button" class="btn btn-sm btn-danger btn-employee-confirm" title="{{ __('admin.employees.delete') }}" data-form-id="form-delete-{{ $employee->id }}" data-confirm-title="{{ __('admin.employees.confirm_delete_title') }}" data-confirm-message="{{ __('admin.employees.confirm_delete_message') }}" data-confirm-btn="{{ __('admin.employees.delete') }}"><i class="fa fa-trash"></i></button>
                                                        </form>
                                                    @endif
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
            <!-- End Row -->
        </div>
        <!-- CONTAINER CLOSED -->

    </div>

    @if(session('employees_success') || session('employees_error'))
        <script>
            window.employeeMessageData = {
                success: @json(session('employees_success')),
                error: @json(session('employees_error')),
                tempPassword: @json(session('temporary_password'))
            };
        </script>
    @endif
@endsection

@push('js_after')
    <script>
        (function () {
            var confirmModal = document.getElementById('employeeConfirmModal');
            if (!confirmModal) return;
            var confirmTitle = document.getElementById('employeeConfirmModalLabel');
            var confirmBody = document.getElementById('employeeConfirmModalBody');
            var confirmSubmitBtn = document.getElementById('employeeConfirmModalSubmit');
            var formToSubmit = null;
            document.addEventListener('click', function (e) {
                var btn = e.target.closest('.btn-employee-confirm');
                if (!btn) return;
                e.preventDefault();
                var formId = btn.getAttribute('data-form-id');
                var title = btn.getAttribute('data-confirm-title');
                var message = btn.getAttribute('data-confirm-message');
                var btnText = btn.getAttribute('data-confirm-btn');
                if (!formId || !title || !message) return;
                formToSubmit = document.getElementById(formId);
                if (!formToSubmit) return;
                confirmTitle.textContent = title;
                confirmBody.textContent = message;
                confirmSubmitBtn.textContent = btnText;
                var modal = new bootstrap.Modal(confirmModal);
                modal.show();
            });
            confirmSubmitBtn.addEventListener('click', function () {
                if (formToSubmit) formToSubmit.submit();
            });
        })();
    </script>
    @if(session('employees_success') || session('employees_error'))
        <script>
            (function () {
                var data = window.employeeMessageData;
                if (!data || (!data.success && !data.error)) return;
                var modalEl = document.getElementById('employeeMessageModal');
                var header = document.getElementById('employeeMessageModalHeader');
                var title = document.getElementById('employeeMessageModalLabel');
                var body = document.getElementById('employeeMessageModalBody');
                if (data.success) {
                    header.className = 'modal-header bg-success text-white';
                    title.textContent = '{{ __("admin.employees.modal_success_title") }}';
                    if (data.tempPassword) {
                        body.innerHTML = '<p class="mb-2">' + (data.success || '') + '</p><p class="mb-0"><strong>{{ __("admin.employees.temporary_password") }}:</strong> <code class="user-select-all d-inline-block mt-1">' + data.tempPassword + '</code></p>';
                    } else {
                        body.innerHTML = '<p class="mb-0">' + (data.success || '') + '</p>';
                    }
                } else if (data.error) {
                    header.className = 'modal-header bg-danger text-white';
                    title.textContent = '{{ __("admin.employees.modal_error_title") }}';
                    body.innerHTML = '<p class="mb-0">' + data.error + '</p>';
                }
                var modal = new bootstrap.Modal(modalEl);
                modal.show();
            })();
        </script>
    @endif
@endpush
