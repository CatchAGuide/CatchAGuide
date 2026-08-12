<div class="category-editor__faq-section mt-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="category-editor__section-title mb-0">{{ __('admin.category_pages.form.faq') }}</h3>
        <button class="btn btn-outline-secondary btn-sm" type="button" onclick="window.addScopedFaqItem?.();">
            <i class="fa fa-plus"></i> {{ __('admin.category_pages.form.add_item') }}
        </button>
    </div>
    <div class="form-group mb-3">
        <label for="faq_title" class="form-label">{{ __('admin.category_pages.form.faq_title') }}</label>
        <input type="text" class="form-control" placeholder="{{ __('admin.category_pages.form.faq_title') }}" name="faq_title" id="faq_title" value="{{ $faq_title ?? '' }}">
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-bordered" id="faq_table">
            <thead>
                <tr>
                    <th width="4%"></th>
                    <th width="48%">{{ __('admin.category_pages.form.question') }}</th>
                    <th width="48%">{{ __('admin.category_pages.form.answer') }}</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
