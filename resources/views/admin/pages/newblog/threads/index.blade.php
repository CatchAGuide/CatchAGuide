@extends('admin.layouts.app')

@section('title', 'Alle Beiträge')

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

            </div>
            <!-- PAGE-HEADER END -->
            <!-- Row -->
            <div class="row row-sm">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <a href="{{ route('admin.newblog.threads.create') }}" class="btn btn-primary">Beitrag erstellen</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table blog-table table-bordered text-nowrap border-bottom" id="responsive-datatable">
                                    <thead>
                                    <tr>
                                        <th class="w-0 border-bottom-0">Language</th>
                                        <th class="wd-15p border-bottom-0">Titel</th>
                                        <th class="wd-15p border-bottom-0">Slug</th>
                                        <th class="wd-15p border-bottom-0">Aktionen</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($threads as $thread)
                                            <tr>
                                                <td class="text-center">@if($thread->language == 'de')<label><i class="fi fi-de"></i></label>  @elseif($thread->language == 'en') <label><i class="fi fi-gb"></i></label> @else <label><i class="fi fi-de"></i></label> @endif </td>
                                                <td>{{ $thread->title }}</td>
                                                <td>{{ $thread->slug }}</td>
                                                <td>
                                                    <a href="{{ route('admin.newblog.threads.edit', $thread) }}" class="btn btn-sm btn-secondary"><i class="fa fa-pen"></i></a>
                                                    <a href="{{ route('admin.newblog.delete', $thread) }}" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></a>
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
