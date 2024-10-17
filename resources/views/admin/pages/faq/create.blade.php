@extends('admin.layouts.app')

@section('title', 'FAQ erstellen')

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
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">@yield('title')</h3>
                        </div>
                        <div class="card-body">
                            <form action="{{route('admin.faq.store')}}" method="POST">
                                @csrf
                                <input type="hidden" name="page" value="{{$page}}">
                                <div class="form-group">
                                    <label for="question">Language</label>
                                    <select class="form-select" name="language" required>
                                        <option selected hidden>Select Language</option>
                                        <option value="de">German</option>
                                        <option value="en">English</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="question">Frage</label>
                                    <input type="text" class="form-control" id="question"  name="question" placeholder="Frage" required>
                                </div>
                                <div class="form-group">
                                    <label for="answer">Antwort</label>
                                    <textarea id="body" cols="30" rows="10" class="form-control" name="answer" required></textarea>

                                </div>
                                <div class="card-footer text-end">
                                    <button type="submit" class=" btn btn-success my-1">Speichern</button>
                                    <a href="/admin/faq/{{$page}}" class="btn btn-danger my-1">Abbrechen</a>
                                </div>
                            </form>


                        </div>

                    </div>
                </div>
            </div>
            <!-- End Row -->
        </div>
        <!-- CONTAINER CLOSED -->

    </div>
@endsection
@push('js_after')
    <script>
        CKEDITOR.replace( 'body' );
    </script>
@endpush
