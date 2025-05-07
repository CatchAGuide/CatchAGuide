@extends('admin.layouts.app')

@section('title', 'Contact Requests')

@section('content')
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">Contact Requests</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">System</a></li>
                        <li class="breadcrumb-item"><a href="#">Admin</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Contact Requests</li>
                    </ol>
                </div>
            </div>
            <!-- PAGE-HEADER END -->
            
            <!-- Row -->
            <div class="row row-sm">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Contact Requests</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped text-nowrap border-bottom">
                                    <thead>
                                        <tr>
                                            <th width="5%" class="border-bottom-0">ID</th>
                                            <th width="20%" class="border-bottom-0">Name</th>
                                            <th width="20%" class="border-bottom-0">Email</th>
                                            <th width="15%" class="border-bottom-0">Phone</th>
                                            <th width="15%" class="border-bottom-0">Source Type</th>
                                            <th width="15%" class="border-bottom-0">Created At</th>
                                            <th width="10%" class="border-bottom-0">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($contactRequests as $request)
                                            <tr>
                                                <td>{{ $request->id }}</td>
                                                <td>{{ $request->name }}</td>
                                                <td>{{ $request->email }}</td>
                                                <td>{{ $request->phone }}</td>
                                                <td>{{ $request->source_type }}</td>
                                                <td>{{ $request->created_at->format('F j, Y g:i A') }}</td>
                                                <td>
                                                    <button class="btn btn-sm btn-info toggle-btn" type="button" data-bs-toggle="collapse" 
                                                            data-bs-target="#description-{{ $request->id }}" aria-expanded="false" 
                                                            aria-controls="description-{{ $request->id }}">
                                                        <i class="fa fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr class="collapse" id="description-{{ $request->id }}">
                                                <td colspan="7">
                                                    <div class="p-3 bg-light">
                                                        <h5>Message:</h5>
                                                        <p style="white-space: normal; word-break: break-word;">{{ $request->description }}</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">No contact requests found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                @if(method_exists($contactRequests, 'links'))
                                    {{ $contactRequests->links() }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Row -->
        </div>
        <!-- CONTAINER CLOSED -->

    </div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize Bootstrap collapse functionality
        $('.toggle-btn').on('click', function() {
            const target = $(this).data('bs-target');
            
            // Check if the target is currently shown or hidden
            const isCollapsed = $(target).hasClass('show');
            
            // Toggle the icon based on the current state
            const icon = $(this).find('i');
            if (isCollapsed) {
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            } else {
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            }
            
            // Let Bootstrap handle the collapse toggle
            // No need to call collapse('toggle') manually
        });
    });
</script>
@endsection