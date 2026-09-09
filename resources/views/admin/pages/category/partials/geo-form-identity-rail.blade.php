<aside class="category-editor__rail">
    <h2 class="category-editor__rail-title">{{ __('admin.category_pages.editor.identity') }}</h2>

    @if($method != '')
        <img src="{{ $thumbnail }}" alt="" class="category-editor__thumb">
    @endif

    @if(!isset($countries))
        <div class="form-group mb-3">
            <label for="countrycode" class="form-label">{{ __('admin.category_pages.form.country_code') }}</label>
            <input type="text" class="form-control" name="countrycode" id="countrycode" value="{{ $countrycode }}">
        </div>
    @endif

    @if(isset($countries))
        <div class="form-group mb-3">
            <label for="country_id" class="form-label">{{ __('admin.category_pages.form.country') }}</label>
            <select class="form-select" name="country_id" id="country_id" required>
                <option value="">-- {{ __('admin.category_pages.form.select') }} --</option>
                @foreach($countries as $row)
                    <option value="{{ $row->id }}" {{ ($row->id == $country_id) ? 'selected' : '' }}>{{ $row->name }}</option>
                @endforeach
            </select>
        </div>
    @endif

    @if(isset($regions))
        <div class="form-group mb-3">
            <label for="region_id" class="form-label">{{ __('admin.category_pages.form.region') }}</label>
            <select class="form-select" name="region_id" id="region_id">
                <option value="">-- {{ __('admin.category_pages.form.select') }} --</option>
            </select>
        </div>
    @endif

    <div class="form-group mb-3">
        <label for="name" class="form-label">{{ __('admin.category_pages.form.name') }}</label>
        <input type="text" class="form-control" id="name" name="name" value="{{ $name }}" required>
    </div>

    <div class="form-group mb-0">
        <label for="thumbnailImage" class="form-label">{{ __('admin.category_pages.form.thumbnail') }}</label>
        <input id="thumbnailImage" type="file" name="thumbnailImage" class="form-control form-control-sm">
    </div>
</aside>
