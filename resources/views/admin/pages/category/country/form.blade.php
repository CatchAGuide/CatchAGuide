@extends('admin.layouts.app')

@section('title', 'Land')

@section('custom_style')
<style type="text/css">
input[type=number] {
  -moz-appearance: textfield;
}
</style>
@endsection

@section('content')
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">@yield('title')</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">System</a></li>
                        <li class="breadcrumb-item"><a href="#">Blog</a></li>
                        <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                    </ol>
                </div>

            </div>
            <!-- PAGE-HEADER END -->
            <!-- Row -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">@yield('title')</h3>
                        </div>
                        <form action="{{route('admin.category.country.store')}}" method="post" enctype="multipart/form-data">
                            @method('post')
                            @csrf
                            <div class="card-body">
                                @if ($errors->any())
                                    @foreach ($errors->all() as $error)
                                        <div>{{$error}}</div>
                                    @endforeach
                                @endif

                                <div class="row">
                                    <div class="col">
                                        <div class="form-group">
                                            <label for="title">Language</label>
                                            <span class="fi fi-de"></span>
                                            <select class="form-control" name="lang" id="language">
                                                @foreach(config('app.locales') as $key => $locale)
                                                    <option value="{{$locale}}">@if($locale == 'de') Deutsch @elseif($locale == 'en') English @endif</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="thumbnailImage">{{ __('guidings.Thumbnail') }}</label><br/>
                                            <input id="thumbnailImage" type="file" name="thumbnailImage">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="title">{{ __('guidings.Title') }}</label>
                                            <input type="text" class="form-control" id="title" name="title" placeholder="Titel des Beitrags" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="title">Sub {{ __('guidings.Title') }}</label>
                                            <input type="text" class="form-control" id="title" name="title" placeholder="Titel des Beitrags" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="introduction">{{ __('guidings.Introduction') }}</label>
                                    <textarea id="introduction" cols="20" rows="4" class="form-control" name="introduction"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="body">{{ __('guidings.Content') }}</label>
                                    <textarea id="body" cols="30" rows="10" class="form-control" name="body"></textarea>
                                </div>
                                <div class="my-2">
                                    <span><strong>Filter</strong></span>
                                </div>

                                <div class="row">
                                    <div class="col-4">
                                        <div class="form-group">
                                            <label for="location">{{ __('guidings.Location') }}</label>
                                            <input id="searchPlace" class="form-control" type="text" placeholder="Search Location" name="filters[place]" autocomplete="on">
                                            <input type="hidden" id="placeLat" name="filters[placeLat]"/>
                                            <input type="hidden" id="placeLng" name="filters[placeLng]"/>
                                            <input type="hidden" id="country" name="filters[country]"/>
                                        </div>

                                    </div>
                                </div>

                                <div class="form-group pb-3">
                                    <button class="btn btn-secondary mb-1" onclick="add_fish_chart_item()" type="button">{{ __('guidings.Add') . ' ' . __('guidings.FishChart') }}</button>

                                    <table class="table table-bordered table-striped" id="fish_chart_table">
                                        <thead>
                                        <tr>
                                            <th width="28%">{{ __('guidings.Fish') }}</th>
                                            @for($i = 1; $i <= 12; $i++)
                                            <th width="6%" class="text-center">{{ date('M', strtotime(date("Y-$i-d"))) }}</th>
                                            @endfor
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>

                                <div class="form-group">
                                    <button class="btn btn-secondary mb-1" onclick="add_faq_item()" type="button">{{ __('guidings.Add') }} FAQ</button>

                                    <table class="table table-bordered table-striped" id="faq_table">
                                        <thead>
                                        <tr>
                                            <th width="4%"></th>
                                            <th width="48%">German</th>
                                            <th width="48%">English</th>
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-success my-1">Speichern</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- End Row -->
        </div>
        <!-- CONTAINER CLOSED -->

    </div>
@endsection


@push('js_after')
    <script>
        CKEDITOR.replace( 'body' );
    </script>

<script>

    $('#target-fish').select2({
        placeholder: "Select",
        allowClear: true
    });
    $('#methods').select2({
        placeholder: "Select",
        allowClear: true
    });

</script>

    <script>
        $(document).ready(function(){
            $('#language').change(function(){
                var x = $(this).val();

                if(x == 'en'){
                    x = 'gb';
                }
                $('.fi').removeClass (function (index, className) {
                    return (className.match (/(^|\s)fi-\S+/g) || []).join(' ');
                });
                $('.fi').addClass('fi-' + x);
            })
        });
    </script>
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBiGuDOg_5yhHeoRz-7bIkc9T1egi1fA7Q&loading=async&libraries=places,geocoder"></script>
    <script>
        function initialize() {
            var input = document.getElementById('searchPlace');
            var autocomplete = new google.maps.places.Autocomplete(input);
            google.maps.event.addListener(autocomplete, 'place_changed', function () {
                var place = autocomplete.getPlace();
                document.getElementById('placeLat').value = place.geometry.location.lat();
                document.getElementById('placeLng').value = place.geometry.location.lng();
                var country = null;
                for (var i = 0; i < place.address_components.length; i++) {
                    for (var j = 0; j < place.address_components[i].types.length; j++) {
                        if (place.address_components[i].types[j] === 'country') {
                            country = place.address_components[i].long_name;
                            break;
                        }
                    }
                    if (country) {
                        break;
                    }
                }
                document.getElementById('country').value = country;
            });
        }

        window.addEventListener('load', initialize);

        $(function(){
            $('.ttt').keyup(function(e){
                //console.log(this.value);
              if (/\D/g.test(this.value)){
                //console.log(this.value);
                this.value = this.value.replace(/\D/g, '');
              }
            });
        });

        function add_fish_chart_item_input(fish_chart_item_counter, month) {
            return '<input class="form-control form-control-sm text-center" type="text" oninput="onlyNumber(\'fish_chart_' + fish_chart_item_counter + '_' + month + '\', value)" id="fish_chart_' + fish_chart_item_counter + '_' + month + '" min="0" max="3" maxlength="1" name="fish_chart[' + fish_chart_item_counter + '][' + month + '][\'ratio\']">';
        }

        let fish_chart_item_counter = 0;
        function add_fish_chart_item() {
            let onclick = '';
            /*let row = '<tr id="fish_chart_item_item_' + fish_chart_item_counter + '">' +
                            '<td>' +
                            '<input class="form-control form-control-sm mb-1" placeholder="Fish" name="fish_chart[' + fish_chart_item_counter + ']" type="text" value="">' +
                            '<input class="form-control form-control-sm mb-1" placeholder="Withdrawal Window" name="withdrawal_window[' + fish_chart_item_counter + ']" type="text" value="">' +
                            '<input class="form-control form-control-sm mb-1" placeholder="Closed Season" name="closed_season[' + fish_chart_item_counter + ']" type="text" value="">' +
                            '<a class="btn btn-link btn-sm" href="javascript:void(0)" onclick="remove_fish_chart_item(' + fish_chart_item_counter + ')"><i class="fa fa-times fa-lg"></i> Remove</a>' + 
                            '</td>' +
                            '<td><input class="form-control form-control-sm text-center" type="text" oninput="onlyNumber(\'fish_chart_' + fish_chart_item_counter + '_1\', value)" id="fish_chart_' + fish_chart_item_counter + '_1" min="0" max="3" maxlength="1" name="fish_chart[' + fish_chart_item_counter + '][\'1\'][\'ratio\']"></td>' +
                            '<td><input class="form-control form-control-sm text-center" type="text" oninput="onlyNumber(\'fish_chart_' + fish_chart_item_counter + '_2\', value)" id="fish_chart_' + fish_chart_item_counter + '_2" min="0" max="3" maxlength="1" name="fish_chart[' + fish_chart_item_counter + '][\'2\'][\'ratio\']"></td>' +
                            '<td><input class="form-control form-control-sm text-center" type="text" oninput="onlyNumber(\'fish_chart_' + fish_chart_item_counter + '_3\', value)" id="fish_chart_' + fish_chart_item_counter + '_3" min="0" max="3" maxlength="1" name="fish_chart[' + fish_chart_item_counter + '][\'3\'][\'ratio\']"></td>' +
                            '<td><input class="form-control form-control-sm text-center" type="text" oninput="onlyNumber(\'fish_chart_' + fish_chart_item_counter + '_4\', value)" id="fish_chart_' + fish_chart_item_counter + '_4" min="0" max="3" maxlength="1" name="fish_chart[' + fish_chart_item_counter + '][\'4\'][\'ratio\']"></td>' +
                            '<td><input class="form-control form-control-sm text-center" type="text" oninput="onlyNumber(\'fish_chart_' + fish_chart_item_counter + '_5\', value)" id="fish_chart_' + fish_chart_item_counter + '_5" min="0" max="3" maxlength="1" name="fish_chart[' + fish_chart_item_counter + '][\'5\'][\'ratio\']"></td>' +
                            '<td><input class="form-control form-control-sm text-center" type="text" oninput="onlyNumber(\'fish_chart_' + fish_chart_item_counter + '_6\', value)" id="fish_chart_' + fish_chart_item_counter + '_6" min="0" max="3" maxlength="1" name="fish_chart[' + fish_chart_item_counter + '][\'6\'][\'ratio\']"></td>' +
                            '<td><input class="form-control form-control-sm text-center" type="text" oninput="onlyNumber(\'fish_chart_' + fish_chart_item_counter + '_7\', value)" id="fish_chart_' + fish_chart_item_counter + '_7" min="0" max="3" maxlength="1" name="fish_chart[' + fish_chart_item_counter + '][\'7\'][\'ratio\']"></td>' +
                            '<td><input class="form-control form-control-sm text-center" type="text" oninput="onlyNumber(\'fish_chart_' + fish_chart_item_counter + '_8\', value)" id="fish_chart_' + fish_chart_item_counter + '_8" min="0" max="3" maxlength="1" name="fish_chart[' + fish_chart_item_counter + '][\'8\'][\'ratio\']"></td>' +
                            '<td><input class="form-control form-control-sm text-center" type="text" oninput="onlyNumber(\'fish_chart_' + fish_chart_item_counter + '_9\', value)" id="fish_chart_' + fish_chart_item_counter + '_9" min="0" max="3" maxlength="1" name="fish_chart[' + fish_chart_item_counter + '][\'9\'][\'ratio\']"></td>' +
                            '<td><input class="form-control form-control-sm text-center" type="text" oninput="onlyNumber(\'fish_chart_' + fish_chart_item_counter + '_10\', value)" id="fish_chart_' + fish_chart_item_counter + '_10" min="0" max="3" maxlength="1" name="fish_chart[' + fish_chart_item_counter + '][\'10\'][\'ratio\']"></td>' +
                            '<td><input class="form-control form-control-sm text-center" type="text" oninput="onlyNumber(\'fish_chart_' + fish_chart_item_counter + '_11\', value)" id="fish_chart_' + fish_chart_item_counter + '_11" min="0" max="3" maxlength="1" name="fish_chart[' + fish_chart_item_counter + '][\'11\'][\'ratio\']"></td>' +
                            '<td><input class="form-control form-control-sm text-center" type="text" oninput="onlyNumber(\'fish_chart_' + fish_chart_item_counter + '_12\', value)" id="fish_chart_' + fish_chart_item_counter + '_12" min="0" max="3" maxlength="1" name="fish_chart[' + fish_chart_item_counter + '][\'12\'][\'ratio\']"></td></tr>';*/
            let row = '<tr id="fish_chart_item_item_' + fish_chart_item_counter + '">' +
                            '<td>' +
                            '<input class="form-control form-control-sm mb-1" placeholder="Fish" name="fish_chart[' + fish_chart_item_counter + ']" type="text" value="">' +
                            '<input class="form-control form-control-sm mb-1" placeholder="Withdrawal Window" name="withdrawal_window[' + fish_chart_item_counter + ']" type="text" value="">' +
                            '<input class="form-control form-control-sm mb-1" placeholder="Closed Season" name="closed_season[' + fish_chart_item_counter + ']" type="text" value="">' +
                            '<a class="btn btn-link btn-sm" href="javascript:void(0)" onclick="remove_fish_chart_item(' + fish_chart_item_counter + ')"><i class="fa fa-times fa-lg"></i> Remove</a>' + 
                            '</td>' +
                            '<td>' + add_fish_chart_item_input(fish_chart_item_counter, 1) + '</td>' +
                            '<td>' + add_fish_chart_item_input(fish_chart_item_counter, 2) + '</td>' +
                            '<td>' + add_fish_chart_item_input(fish_chart_item_counter, 3) + '</td>' +
                            '<td>' + add_fish_chart_item_input(fish_chart_item_counter, 4) + '</td>' +
                            '<td>' + add_fish_chart_item_input(fish_chart_item_counter, 5) + '</td>' +
                            '<td>' + add_fish_chart_item_input(fish_chart_item_counter, 6) + '</td>' +
                            '<td>' + add_fish_chart_item_input(fish_chart_item_counter, 7) + '</td>' +
                            '<td>' + add_fish_chart_item_input(fish_chart_item_counter, 8) + '</td>' +
                            '<td>' + add_fish_chart_item_input(fish_chart_item_counter, 9) + '</td>' +
                            '<td>' + add_fish_chart_item_input(fish_chart_item_counter, 10) + '</td>' +
                            '<td>' + add_fish_chart_item_input(fish_chart_item_counter, 11) + '</td>' +
                            '<td>' + add_fish_chart_item_input(fish_chart_item_counter, 12) + '</td></tr>';
            $('#fish_chart_table tbody').append(row);
            fish_chart_item_counter++;
        }

        function onlyNumber(fish_chart, inputVal) {
            var patt=/^[0-9]+$/;
            var txt = inputVal.slice(0, -1);
            if(patt.test(inputVal)) {
                if (inputVal >= 0 && inputVal <= 3) {
                    document.getElementById(fish_chart).value = inputVal;
                } else {
                    document.getElementById(fish_chart).value = txt;
                }
            } else {
                document.getElementById(fish_chart).value = txt;
            }
        }

        function remove_fish_chart_item(counter) {
            $('#fish_chart_item_item_' + counter).remove();
        }

        let faq_item_counter = 0;
        function add_faq_item() {
            /*let faq_row = '<tr id="faq_item_' + counter + '">' +
                            '<td><a class="btn btn-link btn-sm" href="javascript:void(0)" onclick="remove_faq_item(' + counter + ')"><i class="fa fa-times fa-lg"></i></a></td>' +
                            '<td><input class="form-control form-control-sm" type="text" name="faq[' + counter + '][\'de\'][\'question\']" value=""></td>' +
                            '<td><input class="form-control form-control-sm" type="text" name="faq[' + counter + '][\'de\'][\'answer\']" value=""></td>' +
                            '<td><input class="form-control form-control-sm" type="text" name="faq[' + counter + '][\'en\'][\'question\']" value=""></td>' +
                            '<td><input class="form-control form-control-sm" type="text" name="faq[' + counter + '][\'en\'][\'answer\']" value=""></td></tr>';*/
            let faq_row = '<tr id="faq_item_' + faq_item_counter + '">' +
                            '<td><a class="btn btn-link btn-sm" href="javascript:void(0)" onclick="remove_faq_item(' + faq_item_counter + ')"><i class="fa fa-times fa-lg"></i></a></td>' +
                            '<td><label>Question (DE)</label><input class="form-control form-control-sm" type="text" name="faq[' + faq_item_counter + '][\'de\'][\'question\']" value="">' +
                            '<label>Answer (DE)</label><input class="form-control form-control-sm" type="text" name="faq[' + faq_item_counter + '][\'de\'][\'answer\']" value=""></td>' +
                            '<td><label>Question (EN)</label><input class="form-control form-control-sm" type="text" name="faq[' + faq_item_counter + '][\'en\'][\'question\']" value="">' +
                            '<label>Answer (EN)</label><input class="form-control form-control-sm" type="text" name="faq[' + faq_item_counter + '][\'en\'][\'answer\']" value=""></td></tr>';
            $('#faq_table tbody').append(faq_row);
            counter++;
        }

        function remove_faq_item(counter) {
            $('#faq_item_' + counter).remove();
        }
    </script>

@endpush
