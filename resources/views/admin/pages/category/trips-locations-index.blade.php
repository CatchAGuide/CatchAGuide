@extends('admin.layouts.app')

@section('title', 'Trip locations')

@section('content')
    <style>
        .frm-btn-delete {
            display: contents;
        }
    </style>
    <div class="side-app">
        <div class="main-container container-fluid">
            <div class="page-header">
                <h1 class="page-title">Trip location categories</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">System</a></li>
                        <li class="breadcrumb-item"><a href="#">Categories</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Trip locations</li>
                    </ol>
                </div>
            </div>
            <div class="row row-sm">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <a href="{{ route('admin.category.trip-location.create') }}" class="btn btn-primary">Add trip location</a>
                            <p class="text-muted small mb-0">Trips appear here when their <strong>Country</strong> field matches the category <strong>slug</strong> (URL segment under /trips/c/…).</p>
                        </div>
                        <div class="card-body">
                            <table class="table blog-table table-bordered table-striped text-nowrap border-bottom">
                                <thead>
                                <tr>
                                    <th width="10%" class="border-bottom-0 text-center">Language</th>
                                    <th width="22%" class="border-bottom-0">Name</th>
                                    <th width="18%" class="border-bottom-0">Slug (trip country)</th>
                                    <th width="35%" class="border-bottom-0">Title</th>
                                    <th width="15%" class="border-bottom-0">Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($rows as $row)
                                        <tr>
                                            <td class="text-center">
                                                @if($row->language == 'de')
                                                <label><i class="fi fi-de"></i></label>
                                                @elseif($row->language == 'en')
                                                <label><i class="fi fi-gb"></i></label>
                                                @else
                                                <label><i class="fi fi-de"></i></label>
                                                @endif
                                            </td>
                                            <td>{{ $row->name }}</td>
                                            <td><code>{{ $row->slug }}</code></td>
                                            <td>{{ $row->title }}</td>
                                            <td>
                                                <a href="{{ route('admin.category.trip-location.edit', $row->id) }}" class="btn btn-sm btn-secondary"><i class="fa fa-edit"></i></a>
                                                <form class="frm-btn-delete" action="{{ route('admin.category.trip-location.destroy', $row->id) }}" method="post">
                                                    @method('DELETE')
                                                    @csrf()
                                                    <button class="btn btn-sm btn-danger" type="submit" onclick="return confirm('Delete this trip location category?')"><i class="fa fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            {{ $rows->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
