@extends('admin.layouts.app')

@section('title', 'City')

@section('content')
    <style>
        .frm-btn-delete {
            display: contents;
        }
    </style>
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">City</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">System</a></li>
                        <li class="breadcrumb-item"><a href="#">Blog</a></li>
                        <li class="breadcrumb-item active" aria-current="page">City</li>
                    </ol>
                </div>

            </div>
            <!-- PAGE-HEADER END -->
            <!-- Row -->
            <div class="row row-sm">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <a href="{{ route('admin.category.city.create') }}" class="btn btn-primary">Add City</a>
                        </div>
                        <div class="card-body">
                            <table class="table blog-table table-bordered table-striped text-nowrap border-bottom">
                                <thead>
                                <tr>
                                    <th width="10%" class="border-bottom-0 text-center">Language</th>
                                    <th width="20%" class="border-bottom-0">Country</th>
                                    <th width="30%" class="border-bottom-0">Region</th>
                                    <th width="30%" class="border-bottom-0">City</th>
                                    <th width="10%" class="border-bottom-0">Aktionen</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($rows as $row)
                                        <tr>
                                            <td class="text-center">
                                                @if($row->language == 'de')
                                                <label><i class="fi fi-de"></i></label> 
                                                @elseif($row->language == 'en')
                                                <label><i class="fi fi-gb"></i></label>
                                                @else
                                                <label><i class="fi fi-de"></i></label>
                                                @endif
                                            </td>
                                            <td>{{ $row->country_name }}</td>
                                            <td>{{ $row->region_name }}</td>
                                            <td>{{ $row->name }}</td>
                                            <td>
                                                <a href="{{ route('admin.category.city.edit', $row->id) }}" class="btn btn-sm btn-secondary"><i class="fa fa-edit"></i></a>
                                                <form class="frm-btn-delete" action="{{ route('admin.category.city.destroy', $row->id) }}" method="post">
                                                    @method('DELETE')
                                                    @csrf()
                                                    <button class="btn btn-sm btn-danger" type="submit" onclick="return confirm('Are you sure to delete this City?')"><i class="fa fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            {{ $rows->links() }}
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Row -->
        </div>
        <!-- CONTAINER CLOSED -->

    </div>
@endsection
