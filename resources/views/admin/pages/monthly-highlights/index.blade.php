@extends('admin.layouts.app')

@section('title', 'Monthly Highlights')

@section('content')
    <div class="side-app">
        <div class="main-container container-fluid">
            <div class="page-header">
                <h1 class="page-title">Monthly Highlights</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Admin</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Monthly Highlights</li>
                    </ol>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">Homepage season module</h3>
                            <a href="{{ route('admin.monthly-highlights.create') }}" class="btn btn-primary">Add highlight</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Month</th>
                                            <th>Title (EN)</th>
                                            <th>Title (DE)</th>
                                            <th>Items</th>
                                            <th>Active</th>
                                            <th style="width:140px">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($highlights as $highlight)
                                            <tr>
                                                <td>{{ now()->month($highlight->month)->translatedFormat('F') }}</td>
                                                <td>{{ $highlight->title_en }}</td>
                                                <td>{{ $highlight->title_de }}</td>
                                                <td>{{ count($highlight->items ?? []) }} / {{ \App\Models\MonthlyHighlight::MAX_ITEMS }}</td>
                                                <td>
                                                    @if($highlight->is_active)
                                                        <span class="badge bg-success">Active</span>
                                                    @else
                                                        <span class="badge bg-secondary">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.monthly-highlights.edit', $highlight) }}" class="btn btn-sm btn-primary">Edit</a>
                                                    <form action="{{ route('admin.monthly-highlights.destroy', $highlight) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this highlight?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">No monthly highlights yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
