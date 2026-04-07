@extends('admin.layouts.app')

@section('title', 'Guiding Einstellungen')

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

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h3>Boat extras
                                <i title="Add boat extra" style="color: red; cursor: pointer" class="side-menu__icon fe fe-plus-circle" data-bs-toggle="modal" data-bs-target="#addboatextra"></i>
                            </h3>
                        </div>
                        <div class="card-body table-responsive">
                            <table id="boatextratable" class="table">
                                <thead>
                                <tr>
                                    <th scope="col">Name</th>
                                    <th scope="col">Name English</th>
                                    <th scope="col">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($extras as $row)
                                    <tr>
                                        <td><span class="fi fi-de"></span><span class="px-2">{{ $row->getRawOriginal('name') }}</span></td>
                                        <td><span class="fi fi-gb"></span><span class="px-2">{{ $row->getRawOriginal('name_en') }}</span></td>
                                        <td>
                                            <i style="font-size: 20px; color: red; cursor: pointer" class="side-menu__icon fe fe-trash" data-bs-toggle="modal" data-bs-target="#deleteboatextra{{ $row->id }}"></i>
                                            <i style="font-size: 20px; color: blue; cursor: pointer" class="side-menu__icon fe fe-edit" data-bs-toggle="modal" data-bs-target="#editboatextra{{ $row->id }}"></i>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="editboatextra{{ $row->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit boat extra</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form method="POST" action="{{ route('admin.settings.boat-extras.update', $row->id) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Name (DE)</label>
                                                            <input type="text" name="name" class="form-control" value="{{ $row->getRawOriginal('name') }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Name (EN)</label>
                                                            <input type="text" name="name_en" class="form-control" value="{{ $row->getRawOriginal('name_en') }}">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary">Save</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="deleteboatextra{{ $row->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Delete boat extra</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to delete <strong>{{ $row->getRawOriginal('name') }}</strong>?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                    <form method="POST" action="{{ route('admin.settings.boat-extras.destroy', $row->id) }}" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Delete</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addboatextra" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add boat extra</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.settings.boat-extras.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Name (DE)</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Name (EN)</label>
                            <input type="text" name="name_en" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js_after')
<script>
    let boatextratable = new DataTable('#boatextratable');
</script>
@endsection

