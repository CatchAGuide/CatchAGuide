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
    @include('admin.pages.category.partials.editor-scoped-tabs-script')
@endpush
