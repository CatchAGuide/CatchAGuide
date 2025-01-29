@extends('admin.layouts.app')

@section('title', 'Alle Faq\'s')

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
                            <a href="{{ route('admin.faq.create',$page) }}" class="btn btn-primary">FAQ Erstellen</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered text-nowrap border-bottom" id="responsive-datatable">
                                    <thead>
                                    <tr>
                                        <th class="wd-15p border-bottom-0">ID</th>
                                        <th class="wd-15p border-bottom-0">Language</th>
                                        <th class="wd-15p border-bottom-0">Frage</th>
                                        <th class="wd-15p border-bottom-0">Antwort</th>
                                        <th class="wd-25p border-bottom-0">Aktionen</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($frequentlyAskedQuestions as $faq)
                                    <tr>
                                        <td>{{$faq -> id}}</td>
                                        <td>
                                            @if($faq->language)
                                                @if($faq->language == 'en')
                                                <span class="fi fi-gb"></span>
                                                @else
                                                <span class="fi fi-de"></span>
                                                @endif
                                            @else
                                            <span class="fi fi-de"></span>
                                            @endif 

                                        </td>
                                        <td>{{$faq -> question}}</td>
                                        <td>{{$faq -> limitanswer()}}</td>

                                        <td class="text-center">
                                            <div class="btn-group">
                                                <a href="{{ route('admin.faq.edit', [$faq,$page]) }}" class="btn btn-sm btn-secondary"><i class="fa fa-pen"></i></a>
                                                <a href="{{ route('admin.faq.destroy', $faq) }}" class="btn btn-sm btn-primary"><i class="fa fa-trash"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                    </tbody>
                                    @endforeach
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
