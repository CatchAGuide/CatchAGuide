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
                    ·
                    <a href="{{ $backToListRoute }}">{{ __('admin.category_pages.editor.back_to_list') }}</a>
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
                    <img src="{{ $thumbnail }}" alt="" class="category-editor__thumb">
                    <div class="form-group mb-3">
                        <label for="name" class="form-label">{{ __('admin.category_pages.form.name') }}</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ $name }}" @if(!empty($nameReadonly)) readonly @else required @endif>
                    </div>
                    <div class="form-group mb-3">
                        <label for="thumbnailImage" class="form-label">{{ __('admin.category_pages.form.thumbnail') }}</label>
                        <input id="thumbnailImage" type="file" name="thumbnailImage" class="form-control form-control-sm">
                    </div>
                    <p class="category-editor__meta">
                        <strong>{{ __('admin.category_pages.editor.public_url') }}</strong><br>
                        <a href="{{ $publicUrl }}" target="_blank" rel="noopener">{{ $publicUrl }}</a>
                    </p>
                </aside>

                <div class="category-editor__main">
                    @include('admin.pages.category.partials.editor-context-toolbar', compact('scopes', 'activeScope', 'language', 'completeness'))

                    <section class="category-editor__panel">
                        @include('admin.pages.category.partials.editor-seo-fields', compact('title', 'sub_title', 'introduction', 'content') + ['requireSeoFields' => true])
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

    const languageDataUrl = @json($languageDataUrl);
    let faqIndex = 0;

    window.addScopedFaqItem = function (question = '', answer = '') {
        const tbody = document.querySelector('#faq_table tbody');
        if (!tbody) return;
        const row = document.createElement('tr');
        const q = String(question).replace(/"/g, '&quot;');
        row.innerHTML = `
            <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()"><i class="fa fa-trash"></i></button></td>
            <td><input type="text" class="form-control form-control-sm" name="faq[${faqIndex}][question]" value="${q}"></td>
            <td><textarea class="form-control form-control-sm" name="faq[${faqIndex}][answer]" rows="2">${answer}</textarea></td>
        `;
        tbody.appendChild(row);
        faqIndex++;
    };

    function syncEditors() {
        if (CKEDITOR.instances.introduction) CKEDITOR.instances.introduction.updateElement();
        if (CKEDITOR.instances.content) CKEDITOR.instances.content.updateElement();
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
                if (CKEDITOR.instances.introduction) CKEDITOR.instances.introduction.setData(data.introduction || '');
                if (CKEDITOR.instances.content) CKEDITOR.instances.content.setData(data.content || '');
                document.getElementById('faq_title').value = data.faq_title || '';
                const tbody = document.querySelector('#faq_table tbody');
                tbody.innerHTML = '';
                faqIndex = 0;
                (data.faq || []).forEach(item => window.addScopedFaqItem(item.question || '', item.answer || ''));
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
        window.addScopedFaqItem(@json($item->question ?? ''), @json($item->answer ?? ''));
    @endforeach
</script>
@endpush
