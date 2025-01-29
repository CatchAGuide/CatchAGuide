@extends('admin.layouts.app')

@section('title', 'Alle Guides')

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
                                        <th class="wd-20p border-bottom-0">Adresse</th>
                                        <th class="wd-20p border-bottom-0">Steuer-ID</th>
                                        <th class="wd-25p border-bottom-0 text-center">Aktionen</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($guides as $guide)
                                        <tr>
                                            <td>{{ $guide->id }}</td>
                                            <td>{{ $guide->full_name }}</td>
                                            <td>{{ $guide->email }}</td>
                                            <td>{{ $guide->information->phone }}</td>
                                            <td>{{ $guide->information->address }} {{ $guide->information->address_number }}, {{ $guide->information->postal }} {{$guide->information->city}} {{$guide->information->country}}</td>
                                            <td>
                                                @if($guide->tax_id)
                                                    {{ $guide->tax_id }}
                                                @else
                                                    Keine Steuer-ID vorhanden
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    @if($guide->is_guide)
                                                        <a href="{{ route('admin.guides.change-status', $guide) }}" title="Guide Zugang löschen" class="btn btn-sm btn-danger"><i class="fa fa-times"></i></a>
                                                    @else
                                                        <a href="{{ route('admin.guides.change-status', $guide) }}" title="Guide Zugang aktivieren" class="btn btn-sm btn-success"><i class="fa fa-check"></i></a>
                                                    @endif
                                                    <a href="{{ route('admin.guides.edit', $guide) }}" title="Guide bearbeiten" class="btn btn-sm btn-danger"><i class="fa fa-pen"></i></a>
                                                    <a href="{{ route('admin.guides.show', $guide) }}" title="Guide ansehen" class="btn btn-sm btn-primary"><i class="fa fa-search"></i></a>
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
