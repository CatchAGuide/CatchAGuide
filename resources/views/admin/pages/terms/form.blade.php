@extends('admin.layouts.app')

@section('title', $pageTitle)

@section('content')
    <div class="side-app">
        <div class="main-container container-fluid">
            <div class="page-header">
                <h1 class="page-title">@yield('title')</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.terms.index') }}">Terms & Conditions</a></li>
                        <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">@yield('title')</h3>
                        </div>
                        <div class="card-body">
                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if(session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif

                            <form action="{{ $route }}" method="POST">
                                @csrf
                                @if($method === 'PUT')
                                    @method('PUT')
                                @endif

                                <div class="form-group">
                                    <label for="language">Language</label>
                                    @php
                                        $flag = $language === 'en' ? 'gb' : 'de';
                                    @endphp
                                    <span class="fi fi-{{ $flag }}" id="language-flag"></span>
                                    <select class="form-control" name="language" id="language" required>
                                        <option value="de" @selected(old('language', $language) === 'de')>Deutsch</option>
                                        <option value="en" @selected(old('language', $language) === 'en')>English</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="title">Title</label>
                                    <input type="text"
                                           class="form-control"
                                           id="title"
                                           name="title"
                                           value="{{ old('title', $translation->title ?? '') }}"
                                           placeholder="Section title"
                                           required>
                                </div>

                                <div class="form-group">
                                    <label for="content">Content</label>
                                    <textarea id="content"
                                              name="content"
                                              cols="30"
                                              rows="12"
                                              class="form-control"
                                              required>{{ old('content', $translation->content ?? '') }}</textarea>
                                </div>

                                @if($section)
                                    <div class="form-group">
                                        <label for="sort_order">Sort order</label>
                                        <input type="number"
                                               class="form-control"
                                               id="sort_order"
                                               name="sort_order"
                                               min="0"
                                               value="{{ old('sort_order', $section->sort_order) }}">
                                    </div>
                                @endif

                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="hidden" name="is_active" value="0">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               name="is_active"
                                               id="is_active"
                                               value="1"
                                               @checked(old('is_active', $section->is_active ?? true))>
                                        <label class="form-check-label" for="is_active">Active</label>
                                    </div>
                                </div>

                                <div class="card-footer text-end">
                                    <button type="submit" class="btn btn-success my-1">Speichern</button>
                                    <a href="{{ route('admin.terms.index') }}" class="btn btn-danger my-1">Abbrechen</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js_after')
    <script>
        CKEDITOR.replace('content');

        @if($section)
        $(function () {
            $('#language').change(function () {
                var language = $(this).val();
                var flagClass = language === 'en' ? 'fi-gb' : 'fi-de';
                $('#language-flag').removeClass('fi-gb fi-de').addClass(flagClass);

                var $submitBtn = $('button[type="submit"]');
                $submitBtn.prop('disabled', true).text('Loading...');

                $.ajax({
                    url: '{{ route('admin.terms.translation', $section) }}',
                    method: 'GET',
                    data: { language: language },
                    success: function (data) {
                        $('#title').val(data.title || '');
                        if (CKEDITOR.instances['content']) {
                            CKEDITOR.instances['content'].setData(data.content || '');
                        }
                        if (!data.exists) {
                            alert('Translation for this language does not exist yet. Fill out the form and save to create it.');
                        }
                    },
                    error: function () {
                        alert('Error loading translation data. Please try again.');
                    },
                    complete: function () {
                        $submitBtn.prop('disabled', false).text('Speichern');
                    }
                });
            });
        });
        @endif
    </script>
@endpush
