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
                                    <th width="8%" class="border-bottom-0 text-center">Country</th>
                                    <th width="18%" class="border-bottom-0">Country Name</th>
                                    <th width="22%" class="border-bottom-0">Region</th>
                                    <th width="22%" class="border-bottom-0">City</th>
                                    <th width="12%" class="border-bottom-0 text-center">Languages</th>
                                    <th width="18%" class="border-bottom-0">Aktionen</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($rows as $row)
                                        <tr>
                                            <td class="text-center">
                                                @if($row->country && $row->country->countrycode)
                                                <label><i class="fi fi-{{ strtolower($row->country->countrycode) }}" style="font-size: 1.5em;"></i></label>
                                                @else
                                                <label>N/A</label>
                                                @endif
                                            </td>
                                            <td>{{ $row->country->name ?? 'N/A' }}</td>
                                            <td>{{ $row->region->name ?? 'N/A' }}</td>
                                            <td>{{ $row->name }}</td>
                                            <td class="text-center">
                                                @if($row->translations->count() > 0)
                                                    @foreach($row->translations as $translation)
                                                        @if($translation->language == 'de')
                                                        <i class="fi fi-de" style="font-size: 1.2em; margin: 0 2px;"></i>
                                                        @elseif($translation->language == 'en')
                                                        <i class="fi fi-gb" style="font-size: 1.2em; margin: 0 2px;"></i>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </td>
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
