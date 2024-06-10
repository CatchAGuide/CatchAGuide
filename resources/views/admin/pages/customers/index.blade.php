@extends('admin.layouts.app')

@section('title', 'Alle Users')

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
                                        <th class="wd-25p border-bottom-0 text-center">Guide?</th>
                                        <th class="wd-25p border-bottom-0 text-center">Aktionen</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($customers as $customer)
                                            <tr>
                                                <td>{{ $customer->id }}</td>
                                                <td>{{ $customer->full_name }}</td>
                                                <td>{{ $customer->email }}</td>
                                                <td class="text-center">
                                                    @if($customer->is_guide)
                                                        <span class="badge badge-success-light">Ja</span>
                                                    @else
                                                        <span class="badge badge-danger-light">Nein</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        @if($customer->is_guide)
                                                            <a href="{{ route('admin.guides.change-status', $customer) }}" title="Deaktiviere Guidezugang" class="btn btn-sm btn-danger"><i class="fa fa-times"></i></a>
                                                        @else
                                                            <a href="{{ route('admin.guides.change-status', $customer) }}" title="Guide Zugang aktivieren" class="btn btn-sm btn-success"><i class="fa fa-check"></i></a>
                                                        @endif
                                                        <a title="Kunden ansehen" href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-sm btn-secondary"><i class="fa fa-pencil"></i></a>
                                                        <a title="Kunden löschen" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deletemodal{{$customer->id}}">
                                                            <i style="color:white;" class="fa fa-trash"></i>
                                                        </a>

                                                        <!-- Modal -->
                                                        <div class="modal fade" id="deletemodal{{$customer->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                            <div class="modal-dialog modal-dialog-centered">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="exampleModalLabel">Kunden löschen</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                    Willst Du den Kunden {{$customer->firstname}} {{$customer->lastname}} wirklich löschen?
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zurück</button>
                                                                        <a href="{{route('admin.customersdelete', $customer->id)}}">
                                                                            <button type="button" class="btn btn-danger">Löschen</button>
                                                                        </a>

                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
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
