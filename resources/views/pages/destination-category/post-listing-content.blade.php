{{-- Shared destination SEO blocks (fish chart, limits, FAQ). Expects $row_data, $fish_chart, $fish_size_limit, $fish_time_limit, $faq --}}
<div class="mb-3">{!! $row_data->content !!}</div>

@if($row_data->fish_avail_title != '' && $row_data->fish_avail_intro != '')
    <h2 class="mb-2 mt-5">{{ $row_data->fish_avail_title }}</h2>
    <p>{!! $row_data->fish_avail_intro !!}</p>
    @if($fish_chart->count() > 0)
    <div class="table-responsive">
        <table class="table table-bordered " id="fish_chart_table">
            <thead>
                <tr>
                    <th width="28%">@lang('vacations.fish')</th>
                    <th width="6%" class="text-center">Jan</th>
                    <th width="6%" class="text-center">Feb</th>
                    <th width="6%" class="text-center">Mar</th>
                    <th width="6%" class="text-center">Apr</th>
                    <th width="6%" class="text-center">May</th>
                    <th width="6%" class="text-center">Jun</th>
                    <th width="6%" class="text-center">Jul</th>
                    <th width="6%" class="text-center">Aug</th>
                    <th width="6%" class="text-center">Sep</th>
                    <th width="6%" class="text-center">Oct</th>
                    <th width="6%" class="text-center">Nov</th>
                    <th width="6%" class="text-center">Dec</th>
                </tr>
            </thead>
            <tbody>
                @foreach($fish_chart as $row)
                <tr>
                    <td>{{ $row->fish }}</td>
                    <td class="text-center" style="background-color: {{ $row->bg_color($row->jan) }}"></td>
                    <td class="text-center" style="background-color: {{ $row->bg_color($row->feb) }}"></td>
                    <td class="text-center" style="background-color: {{ $row->bg_color($row->mar) }}"></td>
                    <td class="text-center" style="background-color: {{ $row->bg_color($row->apr) }}"></td>
                    <td class="text-center" style="background-color: {{ $row->bg_color($row->may) }}"></td>
                    <td class="text-center" style="background-color: {{ $row->bg_color($row->jun) }}"></td>
                    <td class="text-center" style="background-color: {{ $row->bg_color($row->jul) }}"></td>
                    <td class="text-center" style="background-color: {{ $row->bg_color($row->aug) }}"></td>
                    <td class="text-center" style="background-color: {{ $row->bg_color($row->sep) }}"></td>
                    <td class="text-center" style="background-color: {{ $row->bg_color($row->oct) }}"></td>
                    <td class="text-center" style="background-color: {{ $row->bg_color($row->nov) }}"></td>
                    <td class="text-center" style="background-color: {{ $row->bg_color($row->dec) }}"></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
@endif

<div class="row">
    @if($row_data->size_limit_title != '' && $row_data->size_limit_intro != '')
    <div class="col-sm-12 col-md-12 col-lg-12 mt-5">
        <h2>{{ $row_data->size_limit_title }}</h2>
        <p>{!! $row_data->size_limit_intro !!}</p>
        @if(!empty($fish_size_limit))
        <table class="table table-bordered table-striped" id="fish_size_limit_table">
            <thead>
                <tr>
                    <th width="20%">@lang('vacations.fish')</th>
                    <th width="80%">{{ translate('Size Limit') }}</th>
                </tr>
            </thead>
            <tbody>
            @foreach($fish_size_limit as $row)
                <tr>
                    <td>{{ $row->fish }}</td>
                    <td>{{ $row->data }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @endif
    @if($row_data->time_limit_title != '' && $row_data->time_limit_intro != '')
    <div class="col-sm-12 col-md-12 col-lg-12 mt-5">
        <h2>{{ $row_data->time_limit_title }}</h2>
        <p>{!! $row_data->time_limit_intro !!}</p>
        @if(!empty($fish_time_limit))
        <table class="table table-bordered table-striped" id="fish_time_limit_table">
            <thead>
                <tr>
                    <th width="20%">@lang('vacations.fish')</th>
                    <th width="80%">{{ translate('Time Limit') }}</th>
                </tr>
            </thead>
            <tbody>
            @foreach($fish_time_limit as $row)
                <tr>
                    <td>{{ $row->fish }}</td>
                    <td>{{ $row->data }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @endif
</div>

@if($row_data->faq_title != '' && $faq->count() > 0)
<h2 class="mb-3 mt-5">{{ $row_data->faq_title }}</h2>
    <div class="accordion mb-5" id="faq">
        @foreach($faq as $row)
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq{{ $row->id }}" aria-expanded="true" aria-controls="faq{{ $row->id }}">{{ $row->question }}</button>
                </h2>
                <div class="accordion-collapse collapse" id="faq{{ $row->id }}" data-bs-parent="#faq">
                    <div class="accordion-body ">{{ $row->answer }}</div>
                </div>
            </div>
        @endforeach
    </div>
@endif
