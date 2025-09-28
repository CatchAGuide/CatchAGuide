@extends('admin.layouts.app')

@section('title', $form)

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
                <h1 class="page-title">{{ $form }}</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">System</a></li>
                        <li class="breadcrumb-item"><a href="#">Blog</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $form }}</li>
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
                        <form action="{{ $route }}" method="post" enctype="multipart/form-data">
                            @method('post')
                            @csrf
                            <div class="card-body">
                                @if ($errors->any())
                                    <div class="alert alert-warning" role="alert">
                                    @foreach ($errors->all() as $error)
                                        {{ $error }}<br>
                                    @endforeach
                                    </div>
                                @endif

                                @if($method != '')
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <img src="{{ $thumbnail }}" style="width: 300px;">
                                    </div>
                                </div>
                                @endif
                                
                                
                                @if(!isset($countries))
                                <div class="row">
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="title">Country Code</label>
                                            <input type="text" class="form-control" name="countrycode" id="countrycode" value="{{ $countrycode }}">
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <div class="row">
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="title">Language</label>
                                            @php
                                                $lang = 'de';
                                                if($language == 'en'){
                                                    $lang = 'gb';
                                                }else{
                                                    $lang = 'de';
                                                }
                                            @endphp
                                            <span class="fi fi-{{ $lang }}"></span>
                                            <select class="form-control" name="language" id="language">
                                                @foreach(config('app.locales') as $key => $locale)
                                                    <option value="{{$locale}}" @if($locale == $language) selected @endif>@if($locale == 'de') Deutsch @elseif($locale == 'en') English @endif</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                @if(isset($countries))
                                <div class="row">
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="country_id">Country</label>
                                            <select class="form-select" name="country_id" id="country_id" required>
                                                <option>-- Select --</option>
                                            @foreach($countries as $row)
                                                <option value="{{ $row->id }}" {{ ($row->id == $country_id)? 'selected="selected"' : '' }}>{{ $row->name }}</option>
                                            @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                
                                @if(isset($regions))
                                <div class="row">
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="region_id">Region</label>
                                            <select class="form-select" name="region_id" id="region_id">
                                                <option>-- Select --</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                <div class="row">
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="name">Name</label>
                                            <input type="text" class="form-control" id="name" name="name" placeholder="Name" value="{{ $name }}" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-2">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="thumbnailImage">Thumbnail</label><br/>
                                            <input id="thumbnailImage" type="file" name="thumbnailImage">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="title">Title</label>
                                            <input type="text" class="form-control" id="title" name="title" placeholder="Title" value="{{ $title }}" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="sub_title">Sub Title</label>
                                            <input type="text" class="form-control" id="sub_title" name="sub_title" placeholder="Sub Title" value="{{ $sub_title }}" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="introduction">Introduction</label>
                                    <textarea id="introduction" cols="20" rows="4" class="form-control" name="introduction">{{ $introduction }}</textarea>
                                </div>
                                <div class="form-group">
                                    <label for="body">Content</label>
                                    <textarea id="body" cols="30" rows="10" class="form-control" name="body">{{ $body }}</textarea>
                                </div>
                                <div class="my-2">
                                    <span><strong>Filter</strong></span>
                                </div>

                                <div class="row">
                                    <div class="col-4">
                                        <div class="form-group">
                                            <label for="location">Location</label>
                                            <input id="searchPlace" class="form-control" type="text" placeholder="Search Location" name="filters[place]" value="{{ $place }}" autocomplete="on">
                                            <input type="hidden" id="placeLat"  value="{{ $placeLat }}" name="filters[placeLat]"/>
                                            <input type="hidden" id="placeLng" value="{{ $placeLng }}" name="filters[placeLng]"/>
                                            <input type="hidden" id="country" value="{{ $country }}"  name="filters[country]"/>
                                            <input type="hidden" id="city" value="{{ $city }}"  name="filters[city]"/>
                                            <input type="hidden" id="region" value="{{ $region }}"  name="filters[region]"/>
                                        </div>

                                    </div>
                                </div>

                                <div class="form-group pb-3">
                                    <h4><button class="btn btn-secondary btn-sm mb-1" onclick="add_fish_chart_item()" type="button"><i class="fa fa-plus"></i></button> Availability of Fish</h4>
                                    <input type="text" class="form-control mb-2" placeholder="Title" name="fish_avail_title" id="fish_avail_title" value="{{ $fish_avail_title }}">
                                    <textarea class="form-control mb-2" cols="20" rows="4" placeholder="Content" name="fish_avail_intro" id="fish_avail_intro">{{ $fish_avail_intro }}</textarea>
                                    <table class="table table-bordered table-striped" id="fish_chart_table">
                                        <thead>
                                        <tr>
                                            <th width="5%"></th>
                                            <th width="23%">Fish</th>
                                            @for($i = 1; $i <= 12; $i++)
                                            <th width="6%" class="text-center">{{ date('M', strtotime(date("Y-$i-d"))) }}</th>
                                            @endfor
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>

                                <div class="form-group pb-3">
                                    <h4><button class="btn btn-secondary btn-sm mb-1" onclick="add_fish_size_limit_item()" type="button"><i class="fa fa-plus"></i></button> Size Limit</h4>
                                    <input type="text" class="form-control mb-2" placeholder="Title" name="size_limit_title" id="size_limit_title" value="{{ $size_limit_title }}">
                                    <textarea class="form-control mb-2" cols="20" rows="4" placeholder="Content" name="size_limit_intro" id="size_limit_intro">{{ $size_limit_intro }}</textarea>
                                    <table class="table table-bordered table-striped" id="fish_size_limit_table">
                                        <thead>
                                        <tr>
                                            <th width="5%"></th>
                                            <th width="20%">Fish</th>
                                            <th width="75%">Size Limit</th>
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>

                                <div class="form-group pb-3">
                                    <h4><button class="btn btn-secondary btn-sm mb-1" onclick="add_fish_time_limit_item()" type="button"><i class="fa fa-plus"></i></button> Time Limit</h4>
                                    <input type="text" class="form-control mb-2" placeholder="Title" name="time_limit_title" id="time_limit_title" value="{{ $time_limit_title }}">
                                    <textarea class="form-control mb-2" cols="20" rows="4" placeholder="Content" name="time_limit_intro" id="time_limit_intro">{{ $time_limit_intro }}</textarea>
                                    <table class="table table-bordered table-striped" id="fish_time_limit_table">
                                        <thead>
                                        <tr>
                                            <th width="5%"></th>
                                            <th width="20%">Fish</th>
                                            <th width="75%">Time Limit</th>
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>

                                <div class="form-group">
                                    <h4><button class="btn btn-secondary btn-sm mb-1" onclick="add_faq_item()" type="button"><i class="fa fa-plus"></i></button> FAQ</h4>
                                    <input type="text" class="form-control mb-2" placeholder="Title" name="faq_title" id="faq_title" value="{{ $faq_title }}">
                                    <table class="table table-bordered table-striped" id="faq_table">
                                        <thead>
                                        <tr>
                                            <th width="4%"></th>
                                            <th width="48%">Question</th>
                                            <th width="48%">Answer</th>
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="card-footer text-end">
                                @if($method != '')
                                    @method('PUT')
                                @endif
                                <button type="submit" class="btn btn-success my-1">Submit</button>
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
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&loading=async&libraries=places,geocoding&callback=initialize"></script>
<script>(g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})
    ({key: "{{ env('GOOGLE_MAPS_API_KEY') }}", v: "weekly"});
</script>
<script>
    CKEDITOR.replace( 'body' );
    CKEDITOR.replace( 'introduction' );

    function initialize() {
        var input = document.getElementById('searchPlace');
        var autocomplete = new google.maps.places.Autocomplete(input);
        google.maps.event.addListener(autocomplete, 'place_changed', function () {
            var place = autocomplete.getPlace();
            document.getElementById('placeLat').value = place.geometry.location.lat();
            document.getElementById('placeLng').value = place.geometry.location.lng();
            var country = null;
            var city = null;
            var region = null;
            
            for (var i = 0; i < place.address_components.length; i++) {
                for (var j = 0; j < place.address_components[i].types.length; j++) {
                    if (place.address_components[i].types[j] === 'country') {
                        country = place.address_components[i].long_name;
                    }
                    if (place.address_components[i].types[j] === 'locality') {
                        city = place.address_components[i].long_name;
                    }
                    if (place.address_components[i].types[j] === 'administrative_area_level_1') {
                        region = place.address_components[i].long_name;
                    }
                }
            }
            
            document.getElementById('country').value = country || '';
            document.getElementById('city').value = city || '';
            document.getElementById('region').value = region || '';
        });
    }

    //window.addEventListener('load', initialize);

    $('#target-fish').select2({
        placeholder: "Select",
        allowClear: true
    });
    $('#methods').select2({
        placeholder: "Select",
        allowClear: true
    });

    @if(isset($regions))
    $(function(){
        countryRegions($('#country_id').val());
        $('#region_id').val('{!! $region_id !!}');
        $('#country_id').change(function(){
            let selected = parseInt($(this).val());
            countryRegions(selected);
        });
    });

    function countryRegions(selected) {
        var regions = $.parseJSON('{!! $regions !!}');
        var region_selections = '<option value="">-- Select --</option>';
        $.each(regions, function(key, value){
            if (selected == value.country_id) {
                region_selections += '<option value="' + value.id + '">' + value.name + '</option>';
            }
        });
        $('#region_id').html(region_selections);
    }
    @endif
</script>

<script>

    $(function(){

    @if(isset($fish_chart))
        @foreach($fish_chart as $row)
            add_fish_chart_item({{ $row['id'] }}, '{{ $row['fish'] }}', {{ $row['jan'] }}, {{ $row['feb'] }}, {{ $row['mar'] }}, {{ $row['apr'] }}, {{ $row['may'] }}, {{ $row['jun'] }}, {{ $row['jul'] }}, {{ $row['aug'] }}, {{ $row['sep'] }}, {{ $row['oct'] }}, {{ $row['nov'] }}, {{ $row['dec'] }});
        @endforeach
    @endif
    @if(isset($fish_size_limit))
        @foreach($fish_size_limit as $row)
            add_fish_size_limit_item({{ $row['id'] }}, '{{ $row['fish'] }}', '{{ $row['data'] }}');
        @endforeach
    @endif
    @if(isset($fish_time_limit))
        @foreach($fish_time_limit as $row)
            add_fish_time_limit_item({{ $row['id'] }}, '{{ $row['fish'] }}', '{{ $row['data'] }}');
        @endforeach
    @endif

    @if(isset($faq))
        @foreach($faq as $row)
            add_faq_item({{ $row['id'] }}, '{{ $row['question'] }}', '{{ $row['answer'] }}');
        @endforeach
    @endif
    });

    $(function(){
        $('.ttt').keyup(function(e){
            //console.log(this.value);
          if (/\D/g.test(this.value)){
            //console.log(this.value);
            this.value = this.value.replace(/\D/g, '');
          }
        });
    });

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

    function add_fish_chart_item_input(fish_chart_item_counter, month, value) {
        return '<input class="form-control form-control-sm text-center" type="text" oninput="onlyNumber(\'fish_chart_' + fish_chart_item_counter + '_' + month + '\', value)" id="fish_chart_' + fish_chart_item_counter + '_' + month + '" min="1" max="3" maxlength="1" name="fish_chart[' + fish_chart_item_counter + '][' + month + ']" value="' + value + '">';
    }

    let fish_chart_item_counter = 0;
    function add_fish_chart_item(id = 0, fish = '', jan = 1, feb = 1, mar = 1, apr = 1, may = 1, jun = 1, jul = 1, aug = 1, sep = 1, oct = 1, nov = 1, dec = 1) {
        let onclick = '';
        let row = '<tr id="fish_chart_item_item_' + fish_chart_item_counter + '"><td>' +
                        '<a class="btn btn-link btn-sm" href="javascript:void(0)" onclick="remove_fish_chart_item(' + fish_chart_item_counter + ')"><i class="fa fa-times fa-lg"></i></a>' +
                        '</td><td>' +
                        '<input class="form-control form-control-sm mb-1" placeholder="Fish" name="fish_chart[' + fish_chart_item_counter + '][fish]" type="text" value="' + fish + '">' +
                        '<input name="fish_chart[' + fish_chart_item_counter + '][id]" type="hidden" value="' + id + '">' +
                        '</td>' +
                        '<td>' + add_fish_chart_item_input(fish_chart_item_counter, 'jan', jan) + '</td>' +
                        '<td>' + add_fish_chart_item_input(fish_chart_item_counter, 'feb', feb) + '</td>' +
                        '<td>' + add_fish_chart_item_input(fish_chart_item_counter, 'mar', mar) + '</td>' +
                        '<td>' + add_fish_chart_item_input(fish_chart_item_counter, 'apr', apr) + '</td>' +
                        '<td>' + add_fish_chart_item_input(fish_chart_item_counter, 'may', may) + '</td>' +
                        '<td>' + add_fish_chart_item_input(fish_chart_item_counter, 'jun', jun) + '</td>' +
                        '<td>' + add_fish_chart_item_input(fish_chart_item_counter, 'jul', jul) + '</td>' +
                        '<td>' + add_fish_chart_item_input(fish_chart_item_counter, 'aug', aug) + '</td>' +
                        '<td>' + add_fish_chart_item_input(fish_chart_item_counter, 'sep', sep) + '</td>' +
                        '<td>' + add_fish_chart_item_input(fish_chart_item_counter, 'oct', oct) + '</td>' +
                        '<td>' + add_fish_chart_item_input(fish_chart_item_counter, 'nov', nov) + '</td>' +
                        '<td>' + add_fish_chart_item_input(fish_chart_item_counter, 'dec', dec) + '</td></tr>';
        $('#fish_chart_table tbody').append(row);
        fish_chart_item_counter++;
    }

    function remove_fish_chart_item(counter) {
        $('#fish_chart_item_item_' + counter).remove();
    }

    let fish_size_limit_item_counter = 0;
    function add_fish_size_limit_item(id = 0, fish = '', data = '') {
        let onclick = '';
        let row = '<tr id="fish_size_limit_item_' + fish_size_limit_item_counter + '">' +
                        '<td>' +
                        '<a class="btn btn-link btn-sm" href="javascript:void(0)" onclick="remove_fish_size_limit_item(' + fish_size_limit_item_counter + ')"><i class="fa fa-times fa-lg"></i></a>' + 
                        '</td><td>' +
                        '<input name="fish_size_limit[' + fish_size_limit_item_counter + '][id]" type="hidden" value="' + id + '">' +
                        '<input class="form-control form-control-sm mb-1" placeholder="Fish" name="fish_size_limit[' + fish_size_limit_item_counter + '][fish]" type="text" value="' + fish + '">' +
                        '</td><td>' +
                        '<input class="form-control form-control-sm mb-1" placeholder="Input" name="fish_size_limit[' + fish_size_limit_item_counter + '][data]" type="text" value="' + data + '">' +
                        '</td></tr>';
        $('#fish_size_limit_table tbody').append(row);
        fish_size_limit_item_counter++;
    }

    function remove_fish_size_limit_item(counter) {
        $('#fish_size_limit_item_' + counter).remove();
    }

    let fish_time_limit_item_counter = 0;
    function add_fish_time_limit_item(id = 0, fish = '', data = '') {
        let onclick = '';
        let row = '<tr id="fish_time_limit_item_' + fish_time_limit_item_counter + '">' +
                        '<td>' +
                        '<a class="btn btn-link btn-sm" href="javascript:void(0)" onclick="remove_fish_size_limit_item(' + fish_time_limit_item_counter + ')"><i class="fa fa-times fa-lg"></i></a>' + 
                        '</td><td>' +
                        '<input name="fish_time_limit[' + fish_time_limit_item_counter + '][id]" type="hidden" value="' + id + '">' +
                        '<input class="form-control form-control-sm mb-1" placeholder="Fish" name="fish_time_limit[' + fish_time_limit_item_counter + '][fish]" type="text" value="' + fish + '">' +
                        '</td><td>' +
                        '<input class="form-control form-control-sm mb-1" placeholder="Input" name="fish_time_limit[' + fish_time_limit_item_counter + '][data]" type="text" value="' + data + '">' +
                        '</td></tr>';
        $('#fish_time_limit_table tbody').append(row);
        fish_time_limit_item_counter++;
    }

    function remove_fish_time_limit_item(counter) {
        $('#fish_time_limit_item_' + counter).remove();
    }

    let faq_item_counter = 0;
    function add_faq_item(id = 0, question = '', answer = '') {
        let faq_row = '<tr id="faq_item_' + faq_item_counter + '">' +
                        '<td>' +
                        '<a class="btn btn-link btn-sm" href="javascript:void(0)" onclick="remove_faq_item(' + faq_item_counter + ')"><i class="fa fa-times fa-lg"></i></a>' +
                        '</td><td>' +
                        '<input name="faq[' + faq_item_counter + '][id]" type="hidden" value="' + id + '">' +
                        '<input class="form-control form-control-sm" name="faq[' + faq_item_counter + '][question]" value="' + question + '" type="text" placeholder="Question">' +
                        '</td><td>' +
                        '<input class="form-control form-control-sm" name="faq[' + faq_item_counter + '][answer]" value="' + answer + '" type="text" placeholder="Answer">' +
                        '</td></tr>';
        $('#faq_table tbody').append(faq_row);
        faq_item_counter++;
    }

    function remove_faq_item(counter) {
        $('#faq_item_' + counter).remove();
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/core-js-bundle@3.30.2/minified.js"></script>

@endpush
