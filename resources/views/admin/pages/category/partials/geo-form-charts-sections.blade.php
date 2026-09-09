<details class="category-form__section">
    <summary class="category-form__section-title">
        <span>{{ __('admin.category_pages.form.filter') }}</span>
    </summary>
    <div class="category-form__section-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-0">
                    <label for="searchPlace" class="form-label">{{ __('admin.category_pages.form.location') }}</label>
                    <input id="searchPlace" class="form-control" type="text" placeholder="{{ __('admin.category_pages.form.location_placeholder') }}" name="filters[place]" value="{{ $place }}" autocomplete="on">
                    <input type="hidden" id="placeLat" value="{{ $placeLat }}" name="filters[placeLat]"/>
                    <input type="hidden" id="placeLng" value="{{ $placeLng }}" name="filters[placeLng]"/>
                    <input type="hidden" id="country" value="{{ $filterCountry ?? $country ?? '' }}" name="filters[country]"/>
                    <input type="hidden" id="city" value="{{ $city }}" name="filters[city]"/>
                    <input type="hidden" id="region" value="{{ $filterRegion ?? $region ?? '' }}" name="filters[region]"/>
                </div>
            </div>
        </div>
    </div>
</details>

<details class="category-form__section" @if(!empty($fish_chart) && count($fish_chart)) open @endif>
    <summary class="category-form__section-title">
        <span>{{ __('admin.category_pages.form.fish_availability') }}</span>
        <button class="btn btn-outline-secondary btn-sm" type="button" onclick="event.preventDefault(); add_fish_chart_item();">
            <i class="fa fa-plus"></i> {{ __('admin.category_pages.form.add_item') }}
        </button>
    </summary>
    <div class="category-form__section-body">
        <div class="form-group mb-3">
            <label for="fish_avail_title" class="form-label">{{ __('admin.category_pages.form.section_title') }}</label>
            <input type="text" class="form-control" name="fish_avail_title" id="fish_avail_title" value="{{ $fish_avail_title }}">
        </div>
        <div class="form-group mb-3">
            <label for="fish_avail_intro" class="form-label">{{ __('admin.category_pages.form.section_intro') }}</label>
            <textarea class="form-control" rows="3" name="fish_avail_intro" id="fish_avail_intro">{{ $fish_avail_intro }}</textarea>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-bordered" id="fish_chart_table">
                <thead>
                    <tr>
                        <th width="5%"></th>
                        <th width="23%">{{ __('admin.category_pages.form.fish') }}</th>
                        @for($i = 1; $i <= 12; $i++)
                            <th width="6%" class="text-center">{{ date('M', strtotime(date("Y-$i-d"))) }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</details>

<details class="category-form__section" @if(!empty($fish_size_limit) && count($fish_size_limit)) open @endif>
    <summary class="category-form__section-title">
        <span>{{ __('admin.category_pages.form.size_limit') }}</span>
        <button class="btn btn-outline-secondary btn-sm" type="button" onclick="event.preventDefault(); add_fish_size_limit_item();">
            <i class="fa fa-plus"></i> {{ __('admin.category_pages.form.add_item') }}
        </button>
    </summary>
    <div class="category-form__section-body">
        <div class="form-group mb-3">
            <label for="size_limit_title" class="form-label">{{ __('admin.category_pages.form.section_title') }}</label>
            <input type="text" class="form-control" name="size_limit_title" id="size_limit_title" value="{{ $size_limit_title }}">
        </div>
        <div class="form-group mb-3">
            <label for="size_limit_intro" class="form-label">{{ __('admin.category_pages.form.section_intro') }}</label>
            <textarea class="form-control" rows="3" name="size_limit_intro" id="size_limit_intro">{{ $size_limit_intro }}</textarea>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-bordered" id="fish_size_limit_table">
                <thead>
                    <tr>
                        <th width="5%"></th>
                        <th width="20%">{{ __('admin.category_pages.form.fish') }}</th>
                        <th width="75%">{{ __('admin.category_pages.form.size_limit') }}</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</details>

<details class="category-form__section" @if(!empty($fish_time_limit) && count($fish_time_limit)) open @endif>
    <summary class="category-form__section-title">
        <span>{{ __('admin.category_pages.form.time_limit') }}</span>
        <button class="btn btn-outline-secondary btn-sm" type="button" onclick="event.preventDefault(); add_fish_time_limit_item();">
            <i class="fa fa-plus"></i> {{ __('admin.category_pages.form.add_item') }}
        </button>
    </summary>
    <div class="category-form__section-body">
        <div class="form-group mb-3">
            <label for="time_limit_title" class="form-label">{{ __('admin.category_pages.form.section_title') }}</label>
            <input type="text" class="form-control" name="time_limit_title" id="time_limit_title" value="{{ $time_limit_title }}">
        </div>
        <div class="form-group mb-3">
            <label for="time_limit_intro" class="form-label">{{ __('admin.category_pages.form.section_intro') }}</label>
            <textarea class="form-control" rows="3" name="time_limit_intro" id="time_limit_intro">{{ $time_limit_intro }}</textarea>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-bordered" id="fish_time_limit_table">
                <thead>
                    <tr>
                        <th width="5%"></th>
                        <th width="20%">{{ __('admin.category_pages.form.fish') }}</th>
                        <th width="75%">{{ __('admin.category_pages.form.time_limit') }}</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</details>
