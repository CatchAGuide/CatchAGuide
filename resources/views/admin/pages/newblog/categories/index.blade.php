@extends('admin.layouts.app')

@section('title', 'Alle Kategorien')

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
                        <li class="breadcrumb-item"><a href="#">Blog</a></li>
                        <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                    </ol>
                </div>
<!-- TODO Category delete einbauen -->
            </div>
            <!-- PAGE-HEADER END -->
            <!-- Row -->
            <div class="row row-sm">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal">Kategorie erstellen</button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered text-nowrap border-bottom" id="responsive-datatable">
                                    <thead>
                                    <tr>
                                        <th class="wd-15p border-bottom-0">Name</th>
                                        <th class="wd-15p border-bottom-0">Slug</th>
                                        <th class="wd-5p border-bottom-0">Aktionen</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($categories as $category)
                                        <tr>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <div class="mb-1">
                                                       <span class="fi fi-de"></span><span class="px-2">{{$category->name}}</span>
                                                    </div>
                                                    <div>
                                                        <span class="fi fi-gb"></span><span class="px-2">{{$category->name_en ? $category->name_en : null }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $category->slug }}</td>
                                            <td>
                                                <a href="javascript:void(0)" class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#editCategoryModal-{{ $category->id }}"><i class="fa fa-pen"></i></a>
                                                <a href="{{route('admin.newblog.category.delete',$category)}}" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></a>
                                            </td>
                                        </tr>

                                        <div class="modal fade" id="editCategoryModal-{{ $category->id }}" tabindex="-1" aria-labelledby="editCategoryModalLabel-{{ $category->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editCategoryModalLabel-{{ $category->id }}">Kategorie #{{ $category->id }} editieren</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('admin.newblog.categories.update', $category) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <div class="mb-2">
                                                                    <label for="name">Name</label>
                                                                    <input type="text" id="name" class="form-control" name="name" value="{{ $category->name }}" required>
                                                                </div>

                                                                <div>
                                                                    <label for="name">Name (en) </label>
                                                                    <input type="text" id="name-en" class="form-control" name="name_en" value="{{ $category->name_en}}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">abbrechen</button>
                                                            <button type="submit" class="btn btn-primary">Speichern</button>
                                                        </div>
                                                    </form>
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
            <!-- End Row -->
        </div>
        <!-- CONTAINER CLOSED -->


        <div class="modal fade" id="createCategoryModal" tabindex="-1" aria-labelledby="createCategoryModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createCategoryModalLabel">Kategorie erstellen</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('admin.newblog.categories.store') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <div class="mb-2">
                                    <label for="name">Name</label>
                                    <input type="text" id="name" class="form-control" name="name" required>
                                </div>
                                <div>
                                    <label for="name">Name (en) </label>
                                    <input type="text" id="name-en" class="form-control" name="name_en">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">abbrechen</button>
                            <button type="submit" class="btn btn-primary">Erstellen</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
