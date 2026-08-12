@php
    $scopes = $scopes ?? [];
    $activeScope = $activeScope ?? ($scopes[0] ?? 'tours');
    $language = $language ?? 'de';
    $completeness = $completeness ?? [];
    $showScopeTabs = count($scopes) > 1;
    $requireSeoFields = $requireSeoFields ?? false;
@endphp

<section class="category-editor__panel category-editor__panel--seo mb-4">
    <div class="category-editor__panel-head">
        <div>
            <h2 class="category-editor__panel-title">{{ __('admin.category_pages.form.seo_content') }}</h2>
            <p class="category-editor__panel-desc">{{ __('admin.category_pages.global_note') }}</p>
        </div>
    </div>

    @include('admin.pages.category.partials.editor-context-toolbar', compact('scopes', 'activeScope', 'language', 'completeness'))

    @include('admin.pages.category.partials.editor-seo-fields', [
        'title' => $title ?? '',
        'sub_title' => $sub_title ?? '',
        'introduction' => $introduction ?? '',
        'content' => $content ?? '',
        'requireSeoFields' => $requireSeoFields,
    ])

    @include('admin.pages.category.partials.editor-faq-section', ['faq' => $faq ?? [], 'faq_title' => $faq_title ?? ''])

    @if(empty($hideScopedActions))
        @include('admin.pages.category.partials.editor-sticky-actions')
    @endif
</section>

@push('js_after')
<script>
(function () {
    if (window.__categoryScopedEditorInit) {
        return;
    }
    window.__categoryScopedEditorInit = true;

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

    window.syncCategoryEditors = function () {
        if (window.CKEDITOR?.instances?.introduction) {
            CKEDITOR.instances.introduction.updateElement();
        }
        if (window.CKEDITOR?.instances?.content) {
            CKEDITOR.instances.content.updateElement();
        }
    };

    const languageDataUrl = @json($languageDataUrl ?? null);

    window.loadScopedLanguageData = function () {
        if (!languageDataUrl) return;
        window.syncCategoryEditors?.();
        const scope = document.getElementById('content_scope')?.value;
        const locale = document.getElementById('languageSwitch')?.value;
        fetch(`${languageDataUrl}?scope=${encodeURIComponent(scope)}&language=${encodeURIComponent(locale)}`)
            .then(r => r.json())
            .then(data => {
                const title = document.getElementById('title');
                const subTitle = document.getElementById('sub_title');
                if (title) title.value = data.title || '';
                if (subTitle) subTitle.value = data.sub_title || '';
                if (CKEDITOR.instances.introduction) CKEDITOR.instances.introduction.setData(data.introduction || '');
                if (CKEDITOR.instances.content) CKEDITOR.instances.content.setData(data.content || '');
                const faqTitle = document.getElementById('faq_title');
                if (faqTitle) faqTitle.value = data.faq_title || '';
                const tbody = document.querySelector('#faq_table tbody');
                if (tbody) {
                    tbody.innerHTML = '';
                    faqIndex = 0;
                    (data.faq || []).forEach(item => window.addScopedFaqItem(item.question || '', item.answer || ''));
                }
            });
    };

    document.querySelectorAll('.scope-tab').forEach(button => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.scope-tab').forEach(el => el.classList.remove('is-active'));
            button.classList.add('is-active');
            const input = document.getElementById('content_scope');
            if (input) input.value = button.dataset.scope;
            window.loadScopedLanguageData?.();
        });
    });

    document.querySelectorAll('.locale-tab').forEach(button => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.locale-tab').forEach(el => el.classList.remove('is-active'));
            button.classList.add('is-active');
            const input = document.getElementById('languageSwitch');
            if (input) input.value = button.dataset.locale;
            window.loadScopedLanguageData?.();
        });
    });

    @if(!empty($faq))
        @foreach($faq as $item)
            window.addScopedFaqItem(@json($item->question ?? $item['question'] ?? ''), @json($item->answer ?? $item['answer'] ?? ''));
        @endforeach
    @endif
})();
</script>
@endpush
