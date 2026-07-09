@if($fish_chart->count() > 0)
<div class="vacation-fish-chart">
<div class="table-responsive">
    <table class="table table-bordered vacation-fish-chart__table" id="fish_chart_table">
        <thead>
            <tr>
                <th width="28%">@lang('vacations.fish')</th>
                @foreach(['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'] as $month)
                    <th width="6%" class="text-center">{{ $month }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($fish_chart as $row)
                <tr>
                    <td>{{ $row->fish }}</td>
                    @foreach(['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'] as $monthKey)
                        <td class="text-center" style="background-color: {{ $row->bg_color($row->{$monthKey}) }}"></td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
</div>
@endif
