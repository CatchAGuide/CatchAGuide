<div class="category-editor__fields">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="title" class="form-label">{{ __('admin.category_pages.form.title') }}</label>
                <input type="text" class="form-control" id="title" name="title" value="{{ $title ?? '' }}" @if(!empty($requireSeoFields)) required @endif>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="sub_title" class="form-label">{{ __('admin.category_pages.form.sub_title') }}</label>
                <input type="text" class="form-control" id="sub_title" name="sub_title" value="{{ $sub_title ?? '' }}" @if(!empty($requireSeoFields)) required @endif>
            </div>
        </div>
    </div>
    <div class="form-group mb-3">
        <label for="introduction" class="form-label">{{ __('admin.category_pages.form.introduction') }}</label>
        <textarea id="introduction" rows="3" class="form-control" name="introduction">{{ $introduction ?? '' }}</textarea>
    </div>
    <div class="form-group mb-0">
        <label for="content" class="form-label">{{ __('admin.category_pages.form.content') }}</label>
        <textarea id="content" rows="8" class="form-control" name="content">{{ $content ?? '' }}</textarea>
    </div>
</div>
