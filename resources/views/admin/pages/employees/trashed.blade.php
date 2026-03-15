@extends('admin.layouts.app')

@section('title', __('admin.employees.trashed'))

@section('content')
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">@yield('title')</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.employees.index') }}">System</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.employees.index') }}">Mitarbeiter</a></li>
                        <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                    </ol>
                </div>
            </div>
            <!-- PAGE-HEADER END -->

            <!-- Row -->
            <div class="row row-sm">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary">{{ __('admin.employees.back_to_list') }}</a>
                        </div>
                        <div class="card-body">
                            @if($employees->isEmpty())
                                <p class="text-muted mb-0">{{ __('admin.employees.trashed_empty') }}</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-bordered text-nowrap border-bottom">
                                        <thead>
                                        <tr>
                                            <th class="wd-15p border-bottom-0">Name</th>
                                            <th class="wd-15p border-bottom-0">E-Mail Adresse</th>
                                            <th class="wd-15p border-bottom-0">{{ __('admin.employees.deleted_at') }}</th>
                                            <th class="wd-15p border-bottom-0">{{ __('admin.employees.deleted_by') }}</th>
                                            <th class="wd-15p border-bottom-0 text-center">Aktionen</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($employees as $employee)
                                            <tr>
                                                <td>{{ $employee->name }}</td>
                                                <td>{{ $employee->email }}</td>
                                                <td>{{ $employee->deleted_at?->format('d.m.Y H:i') }}</td>
                                                <td>{{ $employee->deletedByUser?->name ?? '—' }}</td>
                                                <td class="text-center">
                                                    <form action="{{ route('admin.employees.restore', $employee->id) }}" method="post" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success">{{ __('admin.employees.restore') }}</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Row -->
        </div>
        <!-- CONTAINER CLOSED -->

    </div>
@endsection
