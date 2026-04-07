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
                            <h3>Facilities
                                <i title="Add facility" style="color: red; cursor: pointer" class="side-menu__icon fe fe-plus-circle" data-bs-toggle="modal" data-bs-target="#addfacility"></i>
                            </h3>
                        </div>
                        <div class="card-body table-responsive">
                            <table id="facilitiestable" class="table">
                                <thead>
                                <tr>
                                    <th scope="col">Value (EN)</th>
                                    <th scope="col">Value (DE)</th>
                                    <th scope="col">Active</th>
                                    <th scope="col">Sort</th>
                                    <th scope="col">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($facilities as $row)
                                    <tr>
                                        <td>{{ $row->getRawOriginal('value') }}</td>
                                        <td>{{ $row->getRawOriginal('value_de') }}</td>
                                        <td>{{ $row->is_active ? 'Yes' : 'No' }}</td>
                                        <td>{{ $row->sort_order }}</td>
                                        <td>
                                            <i style="font-size: 20px; color: red; cursor: pointer" class="side-menu__icon fe fe-trash" data-bs-toggle="modal" data-bs-target="#deletefacility{{ $row->id }}"></i>
                                            <i style="font-size: 20px; color: blue; cursor: pointer" class="side-menu__icon fe fe-edit" data-bs-toggle="modal" data-bs-target="#editfacility{{ $row->id }}"></i>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="editfacility{{ $row->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit facility</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form method="POST" action="{{ route('admin.settings.facilities.update', $row->id) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Value (EN)</label>
                                                            <input type="text" name="value" class="form-control" value="{{ $row->getRawOriginal('value') }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Value (DE)</label>
                                                            <input type="text" name="value_de" class="form-control" value="{{ $row->getRawOriginal('value_de') }}">
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Active</label>
                                                                <select name="is_active" class="form-select">
                                                                    <option value="1" {{ $row->is_active ? 'selected' : '' }}>Yes</option>
                                                                    <option value="0" {{ !$row->is_active ? 'selected' : '' }}>No</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Sort order</label>
                                                                <input type="number" name="sort_order" class="form-control" value="{{ $row->sort_order }}">
                                                            </div>
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

                                    <div class="modal fade" id="deletefacility{{ $row->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Delete facility</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to delete <strong>{{ $row->getRawOriginal('value') }}</strong>?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                    <form method="POST" action="{{ route('admin.settings.facilities.destroy', $row->id) }}" style="display:inline;">
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

    <div class="modal fade" id="addfacility" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add facility</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.settings.facilities.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Value (EN)</label>
                            <input type="text" name="value" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Value (DE)</label>
                            <input type="text" name="value_de" class="form-control">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Active</label>
                                <select name="is_active" class="form-select">
                                    <option value="1" selected>Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sort order</label>
                                <input type="number" name="sort_order" class="form-control" value="0">
                            </div>
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
    let facilitiestable = new DataTable('#facilitiestable');
</script>
@endsection

