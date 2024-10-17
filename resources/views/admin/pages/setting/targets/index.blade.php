@extends('admin.layouts.app')

@section('title', 'Guiding Einstellungen')

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
                            <h3>Zielfische
                                <i title="Zielfisch hinzufügen" style="color: red; cursor: pointer" class="side-menu__icon fe fe-plus-circle" data-bs-toggle="modal" data-bs-target="#addtarget"></i>
                                @include('admin.pages.setting.modals.addtargetmodal')
                            </h3>
                        </div>
                        <div class="card-body table-responsive">
                            <table id="targettable" class="table">
                                <thead>
                                <tr>
                                    <th scope="col">Name</th>
                                    <th scope="col">Name English</th>
                                    <th scope="col">Aktion</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($targets as $target)
                                    @include('admin.pages.setting.modals.edittargetmodal')
                                    @include('admin.pages.setting.modals.deletetargetmodal')
                                    <tr>
                                        <td>
                                        <div>
                                               <span class="fi fi-de"></span><span class="px-2">{{$target->name}}</span>

                                        </div>
                                        </td>
                                        <td>
                                            <div>
                                                <span class="fi fi-gb"></span><span class="px-2">{{$target->name_en ? $target->name_en : null }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <i style="font-size: 20px; color: red; cursor: pointer" class="side-menu__icon fe fe-trash" data-bs-toggle="modal" data-bs-target="#deletetarget{{$target->id}}"></i>
                                            <i style="font-size: 20px; color: blue; cursor: pointer" class="side-menu__icon fe fe-edit" data-bs-toggle="modal" data-bs-target="#edittarget{{$target->id}}"></i>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Row -->
        </div>
        <!-- CONTAINER CLOSED -->

    </div>
@endsection


@section('js_after')
<script>
    let targettable = new DataTable('#targettable');
</script>
@endsection