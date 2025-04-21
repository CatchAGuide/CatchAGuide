@extends('admin.layouts.app')

@section('title', $form)

@section('custom_style')
<style type="text/css">
    input[type=number] {
    -moz-appearance: textfield;
    }

    .overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.7);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }
    
    .spinner {
        border: 5px solid #f3f3f3;
        border-top: 5px solid #3498db;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
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
                        <li class="breadcrumb-item"><a href="#">Category</a></li>
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
                        <form action="{{ $route }}" method="post" id="dynamic-form" enctype="multipart/form-data">
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
                                            <select class="form-control" name="languageSwitch" id="languageSwitch">
                                                @foreach(config('app.locales') as $key => $locale)
                                                    <option value="{{$locale}}" @if($locale == $language) selected @endif>@if($locale == 'de') Deutsch @elseif($locale == 'en') English @endif</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="name">Name</label>
                                            <input type="text" class="form-control" id="name" name="name" placeholder="Name" value="{{ $name }}" {{ $allowed_fields ? '' : 'readonly' }} required>
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
                                    <label for="content">Content</label>
                                    <textarea id="content" cols="30" rows="10" class="form-control" name="content">{{ $content }}</textarea>
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
</script>

<script>

    $(function(){

    @if(isset($faq))
        @foreach($faq as $row)
            add_faq_item({{ $row->id }}, '{{ $row->question }}', '{{ $row->answer }}');
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

<script>
    $(document).ready(function() {
        // Handle language change
        $('#languageSwitch').on('change', function() {
            var selectedLanguage = $(this).val();
            
            // Extract the ID from the form action URL instead of the current URL
            var formAction = $('#dynamic-form').attr('action');
            var id = formAction.split('/').pop();
            
            console.log('Selected language:', selectedLanguage);
            console.log('Form action:', formAction);
            console.log('Extracted ID:', id);
            
            // Show loading indicator
            $('body').append('<div class="overlay"><div class="spinner"></div></div>');
            
            // Make AJAX request to get content in selected language
            $.ajax({
                url: "{{ route('admin.category.target-fish.language-data', ['id' => ':id']) }}".replace(':id', id),
                type: 'GET',
                data: {
                    language: selectedLanguage
                },
                success: function(response) {
                    console.log('Response received:', response);
                    
                    // Update form fields with the response data
                    $('#title').val(response.title || '');
                    $('#sub_title').val(response.sub_title || '');
                    
                    // Update CKEditor content
                    if (CKEDITOR.instances.introduction) {
                        CKEDITOR.instances.introduction.setData(response.introduction || '');
                    } else {
                        $('#introduction').val(response.introduction || '');
                    }
                    
                    if (CKEDITOR.instances.content) {
                        CKEDITOR.instances.content.setData(response.content || '');
                    } else {
                        $('#content').val(response.content || '');
                    }
                    
                    $('#faq_title').val(response.faq_title || '');
                    
                    // Update language flag
                    var langFlag = selectedLanguage === 'en' ? 'gb' : 'de';
                    $('.fi').removeClass('fi-gb fi-de').addClass('fi-' + langFlag);
                    
                    // Clear existing FAQ items
                    $('#faq_table tbody').empty();
                    
                    // Add new FAQ items if they exist
                    console.log(response.faq);
                    if (response.faq && response.faq.length > 0) {
                        response.faq.forEach(function(faq) {
                            add_faq_item(faq.id, faq.question, faq.answer);
                        });
                    }
                    
                    // Remove loading indicator
                    $('.overlay').remove();
                },
                error: function(xhr, status, error) {
                    console.error('Error loading language data:', xhr.responseText);
                    console.error('Status:', status);
                    console.error('Error:', error);
                    // Remove loading indicator
                    $('.overlay').remove();
                    alert('Error loading language data. Please try again.');
                }
            });
        });
    });
</script>
@endpush
