@extends('admin.layouts.app')

@section('title', $formTitle ?? $form ?? '')

@section('custom_style')
<link rel="stylesheet" href="{{ asset('css/admin-category-pages.css') }}">
@endsection

@section('content')
<div class="side-app category-editor">
    <div class="main-container container-fluid">
        <div class="page-header category-editor__page-header">
            <div>
                <h1 class="page-title">{{ $formTitle ?? $form }}</h1>
                <p class="text-muted mb-0">
                    <a href="{{ route('admin.category.hub') }}">{{ __('admin.category_pages.editor.back_to_hub') }}</a>
                    @if(!empty($backToListRoute))
                        ·
                        <a href="{{ $backToListRoute }}">{{ __('admin.category_pages.editor.back_to_list') }}</a>
                    @endif
                </p>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-warning">
                @foreach ($errors->all() as $error)
                    {{ $error }}<br>
                @endforeach
            </div>
        @endif

        <form action="{{ $route }}" method="post" enctype="multipart/form-data" id="category-scoped-form">
            @csrf
            @method('PUT')

            <div class="category-editor__layout">
                <aside class="category-editor__rail">
                    <h2 class="category-editor__rail-title">{{ __('admin.category_pages.editor.identity') }}</h2>
                    @if($showThumbnail ?? true)
                        <img src="{{ $thumbnail }}" alt="" class="category-editor__thumb">
                        <div class="form-group mb-3">
                            <label for="thumbnailImage" class="form-label">{{ __('admin.category_pages.form.thumbnail') }}</label>
                            <input id="thumbnailImage" type="file" name="thumbnailImage" class="form-control form-control-sm">
                        </div>
                    @endif
                    <div class="form-group mb-3">
                        <label for="name" class="form-label">{{ __('admin.category_pages.form.name') }}</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ $name }}" @if(!empty($nameReadonly)) readonly @else required @endif>
                    </div>
                    <p class="category-editor__meta" id="category-public-url-wrap" @if(!empty($publicUrls)) data-public-urls='@json($publicUrls)' @endif>
                        <strong>{{ __('admin.category_pages.editor.public_url') }}</strong><br>
                        <a id="category-public-url" href="{{ $publicUrl }}" target="_blank" rel="noopener">{{ $publicUrl }}</a>
                    </p>
                </aside>

                <div class="category-editor__main">
                    @include('admin.pages.category.partials.editor-context-toolbar', compact('scopes', 'activeScope', 'language', 'completeness'))

                    <section class="category-editor__panel">
                        @include('admin.pages.category.partials.editor-seo-fields', compact('title', 'sub_title', 'introduction', 'content') + ['requireSeoFields' => $requireSeoFields ?? true])
                        @include('admin.pages.category.partials.editor-faq-section', compact('faq', 'faq_title'))
                        @include('admin.pages.category.partials.editor-sticky-actions')
                    </section>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js_after')
<script>
    CKEDITOR.replace('introduction');
    CKEDITOR.replace('content');
</script>
@include('admin.pages.category.partials.editor-scoped-tabs-script')
@endpush
