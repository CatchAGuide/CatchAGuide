@extends('admin.layouts.app')

@section('title', 'Alle Guideanfragen')

@section('content')
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">@yield('title')</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Verwaltung</a></li>
                        <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                    </ol>
                </div>

            </div>
            <!-- PAGE-HEADER END -->
            <!-- Row -->
            <div class="row row-sm">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered text-nowrap border-bottom" id="responsive-datatable">
                                    <thead>
                                    <tr>
                                        <th class="wd-15p border-bottom-0">ID</th>
                                        <th class="wd-15p border-bottom-0">Name</th>
                                        <th class="wd-20p border-bottom-0">E-Mail Adresse</th>
                                        <th class="wd-20p border-bottom-0">Telefonnummer</th>
                                        <th class="wd-25p border-bottom-0 text-center">Aktionen</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($guides as $guide)
                                        <tr>
                                            <td>{{ $guide->id }}</td>
                                            <td>{{ $guide->full_name }}</td>
                                            <td>{{ $guide->email }}</td>
                                            <td>{{ $guide->phone }}</td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <a href="{{ route('admin.guides.change-status', $guide) }}" class="btn btn-sm btn-success"><i class="fa fa-check"></i></a>
                                                </div>
                                            </td>
                                        </tr>
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

    </div>
@endsection
