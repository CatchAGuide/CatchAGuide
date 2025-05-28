@extends('admin.layouts.app')

@section('title', 'Email Logs')

@section('content')
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">Email Logs</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">System</a></li>
                        <li class="breadcrumb-item"><a href="#">Admin</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Email Logs</li>
                    </ol>
                </div>
            </div>
            <!-- PAGE-HEADER END -->
            
            <!-- Row -->
            <div class="row row-sm">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Email Logs</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped text-nowrap border-bottom" id="responsive-datatable">
                                    <thead>
                                        <tr>
                                            <th width="5%" class="border-bottom-0">ID</th>
                                            <th width="15%" class="border-bottom-0">Email</th>
                                            <th width="20%" class="border-bottom-0">Subject</th>
                                            <th width="10%" class="border-bottom-0">Type</th>
                                            <th width="10%" class="border-bottom-0 text-center">Language</th>
                                            <th width="10%" class="border-bottom-0">Target</th>
                                            <th width="10%" class="border-bottom-0">Created At</th>
                                            {{-- <th width="10%" class="border-bottom-0">Actions</th> --}}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($emailLogs as $log)
                                            <tr>
                                                <td>{{ $log->id }}</td>
                                                <td>{{ $log->email }}</td>
                                                <td>{{ $log->subject }}</td>
                                                <td>{{ $log->type }}</td>
                                                <td class="text-center">
                                                    @if($log->language == 'de')
                                                        <label><i class="fi fi-de"></i></label> 
                                                    @elseif($log->language == 'en')
                                                        <label><i class="fi fi-gb"></i></label>
                                                    @else
                                                        <label>{{ $log->language }}</label>
                                                    @endif
                                                </td>
                                                <td>{{ $log->target }}</td>
                                                <td>{{ $log->created_at->format('F j, Y g:i A') }}</td>
                                                {{-- <td>
                                                    <a href="#" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a>
                                                </td> --}}
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center">No email logs found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                @if(method_exists($emailLogs, 'links'))
                                    {{ $emailLogs->links() }}
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
