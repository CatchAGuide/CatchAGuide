@extends('admin.layouts.app')

@section('title', 'Alle Guidings')

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
            <div class="row ">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <button class="btn btn-primary"><i class="fa fa-plus"></i> Guiding</button>
                        </div>
                        <div class="card-body table-responsive">
                            <table id="guiding-datatable" class="table">
                                <thead>
                                    <tr>
                                        <th class="wd-15p border-bottom-0">ID</th>
                                        <th class="wd-15p border-bottom-0">Name des Guidings</th>
                                        <th class="wd-10p border-bottom-0">Guide Name</th>
                                        <th class="wd-25p border-bottom-0">Aktionen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($guidings as $guiding)
                                    <tr>
                                        <td>{{$guiding -> id}}</td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <div class="fw-bold">{{$guiding -> title}}</div>
                                                <div class="text-info">{{$guiding -> location}}</div>
                                            </div>
       
                                        </td>
                                        <td>
                                            <a href="{{route('admin.guides.edit', $guiding->user->id)}}">
                                                {{$guiding -> user->full_name }}
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                @if($guiding->status == 1)
                                                    <a href="{{ route('admin.changeGuidingStatus', $guiding->id) }}" title="Guiding deaktivieren" class="btn btn-sm btn-danger"><i class="fa fa-times"></i></a>
                                                @else
                                                    <a href="{{ route('admin.changeGuidingStatus', $guiding->id) }}" title="Guiding aktivieren" class="btn btn-sm btn-success"><i class="fa fa-check"></i></a>
                                                @endif
                                                <a href="{{ route('admin.guidings.edit', $guiding) }}" class="btn btn-sm btn-secondary"><i class="fa fa-pen"></i></a>
                                                <a href="{{ route('admin.guidings.show', $guiding) }}" class="btn btn-sm btn-primary"><i class="fa fa-search"></i></a>
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

            <div class="row bg-white p-2">
                {{-- <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered text-nowrap border-bottom" id="responsive-datatable">
                                    <thead>
                                    <tr>
                                        <th class="wd-15p border-bottom-0">ID</th>
                                        <th class="wd-15p border-bottom-0">Name des Guidings</th>
                                        <th class="wd-15p border-bottom-0">Ort</th>
                                        <th class="wd-10p border-bottom-0">Guide Name</th>
                                        <th class="wd-25p border-bottom-0">Preis pro Person</th>
                                        <th class="wd-25p border-bottom-0">Preis 2 Personen</th>
                                        <th class="wd-25p border-bottom-0">Preis 3 Personen</th>
                                        <th class="wd-25p border-bottom-0">Aktionen</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($guidings as $guiding)
                                    <tr>
                                        <td>{{$guiding -> id}}</td>
                                        <td>{{$guiding -> title}}</td>
                                        <td>{{$guiding -> location}}</td>
                                        <td>
                                            <a href="{{route('admin.guides.edit', $guiding->user->id)}}">
                                                {{$guiding -> user->full_name }}
                                            </a>
                                        </td>
                                        <td>{{$guiding -> price}} €</td>
                                        <td>
                                            @if($guiding -> price_two_persons)
                                                {{$guiding -> price_two_persons}} €
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($guiding -> price_three_persons)
                                                {{$guiding -> price_three_persons}} €
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                @if($guiding->status == 1)
                                                    <a href="{{ route('admin.changeGuidingStatus', $guiding->id) }}" title="Guiding deaktivieren" class="btn btn-sm btn-danger"><i class="fa fa-times"></i></a>
                                                @else
                                                    <a href="{{ route('admin.changeGuidingStatus', $guiding->id) }}" title="Guiding aktivieren" class="btn btn-sm btn-success"><i class="fa fa-check"></i></a>
                                                @endif
                                                <a href="{{ route('admin.guidings.edit', $guiding) }}" class="btn btn-sm btn-secondary"><i class="fa fa-pen"></i></a>
                                                <a href="{{ route('admin.guidings.show', $guiding) }}" class="btn btn-sm btn-primary"><i class="fa fa-search"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                    </tbody>
                                    @endforeach
                                </table>
                            </div>
                        </div>
                    </div>
                </div> --}}
            </div>
            <!-- End Row -->
        </div>
        <!-- CONTAINER CLOSED -->

    </div>
@endsection


@section('js_after')
<script>
    $(function(e) {
        $('#guiding-datatable').DataTable({
            order: [
                [0, 'desc']
            ]
        });
    });
</script>
@endsection
