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
                            <h3>Fishing Equipment
                                <i title="Add equipment" style="color: red; cursor: pointer" class="side-menu__icon fe fe-plus-circle" data-bs-toggle="modal" data-bs-target="#addequipment"></i>
                            </h3>
                        </div>
                        <div class="card-body table-responsive">
                            <table id="equipmenttable" class="table">
                                <thead>
                                <tr>
                                    <th scope="col">Name</th>
                                    <th scope="col">Name English</th>
                                    <th scope="col">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($equipment as $row)
                                    <tr>
                                        <td><span class="fi fi-de"></span><span class="px-2">{{ $row->name }}</span></td>
                                        <td><span class="fi fi-gb"></span><span class="px-2">{{ $row->name_en }}</span></td>
                                        <td>
                                            <i style="font-size: 20px; color: red; cursor: pointer" class="side-menu__icon fe fe-trash" data-bs-toggle="modal" data-bs-target="#deleteequipment{{ $row->id }}"></i>
                                            <i style="font-size: 20px; color: blue; cursor: pointer" class="side-menu__icon fe fe-edit" data-bs-toggle="modal" data-bs-target="#editequipment{{ $row->id }}"></i>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="editequipment{{ $row->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit equipment</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form method="POST" action="{{ route('admin.settings.updatefishingequipment', $row->id) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Name (DE)</label>
                                                            <input type="text" name="name" class="form-control" value="{{ $row->getRawOriginal('name') }}">
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

                                    <div class="modal fade" id="deleteequipment{{ $row->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Delete equipment</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to delete <strong>{{ $row->name }}</strong>?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                    <a class="btn btn-danger" href="{{ route('admin.settings.deleteequipment', $row->id) }}">Delete</a>
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

    <div class="modal fade" id="addequipment" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add equipment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.settings.storeequipment') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Name (DE)</label>
                            <input type="text" name="name" class="form-control">
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
    let equipmenttable = new DataTable('#equipmenttable');
</script>
@endsection

