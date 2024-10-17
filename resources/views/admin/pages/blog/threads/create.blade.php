@extends('admin.layouts.app')

@section('title', 'Beitrag erstellen')

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
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">@yield('title')</h3>
                        </div>
                        <form action="{{route('admin.blog.threads.store')}}" method="post" enctype="multipart/form-data">
                            @method('post')
                            @csrf
                            <div class="card-body">
                                @if ($errors->any())
                                    @foreach ($errors->all() as $error)
                                        <div>{{$error}}</div>
                                    @endforeach
                                @endif

                                <div class="row">
                                    <div class="col">
                                        <div class="form-group">
                                            <label for="title">Language</label>
                                            <span class="fi fi-de"></span>
                                            <select class="form-control" name="lang" id="language">
                                                @foreach(config('app.locales') as $key => $locale)
                                                    <option value="{{$locale}}">@if($locale == 'de') Deutsch @elseif($locale == 'en') English @endif</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="threadImage">Thumbnail</label><br/>
                                            <input id="threadImage" type="file" name="threadImage">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="title">Titel</label>
                                            <input type="text" class="form-control" id="title" name="title" placeholder="Titel des Beitrags" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="excerpt">Excerpt</label>
                                    <textarea id="excerpt" cols="20" rows="2" class="form-control" name="excerpt"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="body">Inhalt</label>
                                    <textarea id="body" cols="30" rows="10" class="form-control" name="body"></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="autor">Autor</label>
                                            <input type="text" class="form-control" id="author" name="author" placeholder="Autor des Beitrags" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="category">Kategorie</label>
                                    <select id="category" name="category_id" class="form-control" name="category_id">
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="row">
                                    <div class="col-lg-2 col-md-4">
                                        <div class="form-group">
                                            <label for="cache">Cache</label>
                                            <select id="cache" name="cache" class="form-control">
                                                <option value="0">No</option>
                                                <option value="1">Yes</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-success my-1">Speichern</button>
                            </div>
                        </form>
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

    <script>
        $(document).ready(function(){
            $('#language').change(function(){
                var x = $(this).val();

                if(x == 'en'){
                    x = 'gb';
                }
                $('.fi').removeClass (function (index, className) {
                    return (className.match (/(^|\s)fi-\S+/g) || []).join(' ');
                });
                $('.fi').addClass('fi-' + x);
            })
        });
    </script>
@endpush
