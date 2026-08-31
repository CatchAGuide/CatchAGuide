@extends('admin.layouts.app')

@section('title', $form)

@section('custom_style')
<link rel="stylesheet" href="{{ asset('css/admin-category-pages.css') }}">
@endsection

@section('content')
<div class="side-app category-editor">
    <div class="main-container container-fluid">
        <div class="page-header">
            <div>
                <h1 class="page-title">{{ $form }}</h1>
                <p class="text-muted mb-0">
                    <a href="{{ route('admin.category.hub') }}">{{ __('admin.category_pages.editor.back_to_hub') }}</a>
                    ·
                    <a href="{{ route('admin.category.methods.index') }}">{{ __('admin.category_pages.editor.back_to_list') }}</a>
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
            <input type="hidden" name="content_scope" id="content_scope" value="{{ $activeScope }}">
            <input type="hidden" name="languageSwitch" id="languageSwitch" value="{{ $language }}">

            <div class="category-editor__layout">
                <aside class="category-editor__rail">
                    <h2 class="h6 mb-3">{{ __('admin.category_pages.editor.identity') }}</h2>
                    <img src="{{ $thumbnail }}" alt="" class="category-editor__thumb">
                    <div class="form-group mb-3">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ $name }}" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="thumbnailImage">Thumbnail</label>
                        <input id="thumbnailImage" type="file" name="thumbnailImage" class="form-control">
                    </div>
                    <p class="category-editor__meta">
                        <strong>{{ __('admin.category_pages.editor.public_url') }}:</strong><br>
                        <a href="{{ $publicUrl }}" target="_blank" rel="noopener">{{ $publicUrl }}</a>
                    </p>
                </aside>

                <section class="category-editor__panel">
                    <h2 class="h6 mb-2">{{ __('admin.category_pages.editor.scope') }}</h2>
                    <div class="category-editor__scope-tabs" id="scope-tabs">
                        @foreach($scopes as $scope)
                            @php
                                $filled = ($completeness[$scope]['de'] ?? false) || ($completeness[$scope]['en'] ?? false);
                            @endphp
                            <button type="button"
                                class="category-editor__tab scope-tab {{ $activeScope === $scope ? 'is-active' : '' }} {{ $filled ? 'is-filled' : 'is-empty' }}"
                                data-scope="{{ $scope }}">
                                {{ \App\Domain\CategoryPage\CategoryPageScope::label($scope) }}
                            </button>
                        @endforeach
                    </div>

                    <h2 class="h6 mb-2">{{ __('admin.category_pages.editor.locale') }}</h2>
                    <div class="category-editor__locale-tabs" id="locale-tabs">
                        @foreach(config('app.locales') as $locale)
                            <button type="button"
                                class="category-editor__tab locale-tab {{ $language === $locale ? 'is-active' : '' }}"
                                data-locale="{{ $locale }}">
                                @if($locale === 'de') Deutsch @else English @endif
                            </button>
                        @endforeach
                    </div>

                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" class="form-control" id="title" name="title" value="{{ $title }}" required>
                    </div>
                    <div class="form-group">
                        <label for="sub_title">Sub Title</label>
                        <input type="text" class="form-control" id="sub_title" name="sub_title" value="{{ $sub_title }}" required>
                    </div>
                    <div class="form-group">
                        <label for="introduction">Introduction</label>
                        <textarea id="introduction" rows="4" class="form-control" name="introduction">{{ $introduction }}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="content">Content</label>
                        <textarea id="content" rows="10" class="form-control" name="content">{{ $content }}</textarea>
                    </div>

                    <div class="form-group">
                        <h4 class="h6">
                            <button class="btn btn-secondary btn-sm mb-1" onclick="add_faq_item()" type="button"><i class="fa fa-plus"></i></button>
                            FAQ
                        </h4>
                        <input type="text" class="form-control mb-2" placeholder="FAQ section title" name="faq_title" id="faq_title" value="{{ $faq_title }}">
                        <table class="table table-bordered table-striped" id="faq_table">
                            <thead>
                                <tr>
                                    <th width="4%"></th>
                                    <th width="48%">Question</th>
                                    <th width="48%">Answer</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <div class="category-editor__actions">
                        <button type="submit" class="btn btn-success">{{ __('admin.category_pages.editor.save') }}</button>
                        <button type="submit" class="btn btn-outline-primary" name="translate_to_en" value="1">{{ __('admin.category_pages.editor.translate_en') }}</button>
                    </div>
                </section>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js_after')
<script>
    CKEDITOR.replace('introduction');
    CKEDITOR.replace('content');

    const languageDataUrl = @json(route('admin.category.methods.language-data', $methodId));
    let faqIndex = 0;

    function add_faq_item(question = '', answer = '') {
        const tbody = document.querySelector('#faq_table tbody');
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove()"><i class="fa fa-trash"></i></button></td>
            <td><input type="text" class="form-control" name="faq[${faqIndex}][question]" value="${question.replace(/"/g, '&quot;')}"></td>
            <td><textarea class="form-control" name="faq[${faqIndex}][answer]" rows="2">${answer}</textarea></td>
        `;
        tbody.appendChild(row);
        faqIndex++;
    }

    function syncEditors() {
        if (CKEDITOR.instances.introduction) {
            CKEDITOR.instances.introduction.updateElement();
        }
        if (CKEDITOR.instances.content) {
            CKEDITOR.instances.content.updateElement();
        }
    }

    function loadLanguageData() {
        syncEditors();
        const scope = document.getElementById('content_scope').value;
        const locale = document.getElementById('languageSwitch').value;

        fetch(`${languageDataUrl}?scope=${encodeURIComponent(scope)}&language=${encodeURIComponent(locale)}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('title').value = data.title || '';
                document.getElementById('sub_title').value = data.sub_title || '';
                if (CKEDITOR.instances.introduction) {
                    CKEDITOR.instances.introduction.setData(data.introduction || '');
                }
                if (CKEDITOR.instances.content) {
                    CKEDITOR.instances.content.setData(data.content || '');
                }
                document.getElementById('faq_title').value = data.faq_title || '';

                const tbody = document.querySelector('#faq_table tbody');
                tbody.innerHTML = '';
                faqIndex = 0;
                (data.faq || []).forEach(item => add_faq_item(item.question || '', item.answer || ''));
            });
    }

    document.querySelectorAll('.scope-tab').forEach(button => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.scope-tab').forEach(el => el.classList.remove('is-active'));
            button.classList.add('is-active');
            document.getElementById('content_scope').value = button.dataset.scope;
            loadLanguageData();
        });
    });

    document.querySelectorAll('.locale-tab').forEach(button => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.locale-tab').forEach(el => el.classList.remove('is-active'));
            button.classList.add('is-active');
            document.getElementById('languageSwitch').value = button.dataset.locale;
            loadLanguageData();
        });
    });

    document.getElementById('category-scoped-form').addEventListener('submit', syncEditors);

    @foreach($faq as $item)
        add_faq_item(@json($item->question ?? ''), @json($item->answer ?? ''));
    @endforeach
</script>
@endpush
