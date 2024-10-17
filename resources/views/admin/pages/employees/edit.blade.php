@extends('admin.layouts.app')

@section('title', 'Mitarbeiter #' . $employee->id . ' editieren')

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
                        <li class="breadcrumb-item"><a href="#">Mitarbeiter</a></li>
                        <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                    </ol>
                </div>

            </div>
            <!-- PAGE-HEADER END -->
            <!-- Row -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">@yield('title')</h3>
                        </div>
                        <div class="card-body">
                            <form action="{{route('admin.employees.update', $employee->id)}}" method="post">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="name">Name</label>
                                            <input type="text" class="form-control" id="name" name="name" placeholder="Vorname" value="{{ $employee->name }}">
                                        </div>
                                    </div>

                                </div>
                                <div class="form-group">
                                    <label for="email">Email Adresse</label>
                                    <input type="email" class="form-control" id="emial" name="email" placeholder="Email Adresse" value="{{ $employee->email }}">
                                </div>
                                <div class="card-footer text-end">
                                    <button type="submit" class="btn btn-success my-1"> Speichern</button>
                                    <a href="{{ route('admin.employees.index') }}" class="btn btn-danger my-1">Abbrechen</a>
                                </div>
                            </form>


                    </div>
                </div>
            </div>
            <!-- End Row -->
        </div>
        <!-- CONTAINER CLOSED -->

    </div>
@endsection
