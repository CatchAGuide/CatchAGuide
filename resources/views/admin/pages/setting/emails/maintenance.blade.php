@extends('admin.layouts.app')

@section('title', 'Email Maintenance')

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
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body table-responsive">
                            <table id="emailMaintenanceTable" class="table">
                                <thead>
                                <tr>
                                    <th scope="col">Name</th>
                                    <th scope="col">Category</th>
                                    <th scope="col">Description</th>
                                    <th scope="col">English Email</th>
                                    <th scope="col">German Email</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($emailTemplates as $template)
                                    <tr>
                                        <td>
                                            <strong>{{ $template['name'] }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $template['category'] == 'guide' ? 'primary' : 'success' }}">
                                                {{ ucfirst($template['category']) }}
                                            </span>
                                        </td>
                                        <td>{{ $template['description'] }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-primary preview-email-btn" 
                                                    data-template="{{ $template['template_key'] }}" 
                                                    data-locale="en">
                                                <i class="fa fa-eye"></i> Preview EN
                                            </button>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-secondary preview-email-btn" 
                                                    data-template="{{ $template['template_key'] }}" 
                                                    data-locale="de">
                                                <i class="fa fa-eye"></i> Preview DE
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Row -->
        </div>
        <!-- CONTAINER CLOSED -->

    </div>

    <!-- Email Preview Modal -->
    <div class="modal fade" id="emailPreviewModal" tabindex="-1" role="dialog" aria-labelledby="emailPreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="emailPreviewModalLabel">
                        <i class="fa fa-envelope"></i> Email Preview: <span id="templateName"></span> (<span id="templateLocale"></span>)
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="emailPreviewContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2">Loading email preview...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
        let emailMaintenanceTable = $('#emailMaintenanceTable').DataTable({
            "pageLength": 25,
            "order": [[ 1, "asc" ], [ 0, "asc" ]], // Sort by category first, then name
            "columnDefs": [
                { "orderable": false, "targets": [3, 4] } // Disable sorting on action columns
            ],
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/de-DE.json"
            }
        });

        let currentTemplate = '';
        let currentLocale = '';

        // Handle preview button clicks
        $(document).on('click', '.preview-email-btn', function() {
            currentTemplate = $(this).data('template');
            currentLocale = $(this).data('locale');
            
            // Show modal
            $('#emailPreviewModal').modal('show');
            
            // Reset modal content
            $('#emailPreviewContent').html(`
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Loading email preview...</p>
                </div>
            `);
            
            // Load email content via AJAX
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
                            <h4>Error Loading Preview</h4>
                            <p>There was an error loading the email preview. Please try again.</p>
                            <small>Error: ${error}</small>
                        </div>
                    `);
                }
            });
        });

        // Handle "Open in New Tab" button
        $('#openInNewTab').click(function() {
            if (currentTemplate && currentLocale) {
                const url = `{{ route('admin.settings.email.preview', ['template' => 'TEMPLATE_PLACEHOLDER', 'locale' => 'LOCALE_PLACEHOLDER']) }}`
                    .replace('TEMPLATE_PLACEHOLDER', currentTemplate)
                    .replace('LOCALE_PLACEHOLDER', currentLocale);
                window.open(url, '_blank');
            }
        });

        // Clear current template and locale when modal is hidden
        $('#emailPreviewModal').on('hidden.bs.modal', function () {
            currentTemplate = '';
            currentLocale = '';
        });
    });
</script>
@endsection