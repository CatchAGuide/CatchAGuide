@extends('admin.layouts.app')

@section('title', 'Beitrag erstellen')

@section('custom_style')

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
                        <form action="{{route('admin.newblog.threads.store')}}" method="post" enctype="multipart/form-data">
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
                                            <label for="threadImage">Thumbnail</label><br/>
                                            <input id="threadImage" type="file" name="threadImage">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="title">Titel</label>
                                            <input type="text" class="form-control" id="title" name="title" placeholder="Titel des Beitrags" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="excerpt">Excerpt</label>
                                    <textarea id="excerpt" cols="20" rows="2" class="form-control" name="excerpt"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="introduction">Introduction</label>
                                    <textarea id="introduction" cols="20" rows="10" class="form-control" name="introduction"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="body">Inhalt</label>
                                    <textarea id="body" cols="30" rows="10" class="form-control" name="body"></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="autor">Autor</label>
                                            <input type="text" class="form-control" id="author" name="author" placeholder="Autor des Beitrags" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="my-2">
                                    <span><strong>Filter</strong></span>
                                </div>

                                <div class="row">
                                    <div class="col-4">
                                        <div class="form-group">
                                            <label for="location">Location</label>
                                            <input id="searchPlace" class="form-control" type="text" placeholder="Search Location" name="filters[place]" autocomplete="on">
                                            <input type="hidden" id="placeLat" name="filters[placeLat]"/>
                                            <input type="hidden" id="placeLng" name="filters[placeLng]"/>
                                            <input type="hidden" id="country" name="filters[country]"/>
                                        </div>

                                    </div>
                                    <div class="col-4">
                                        <div class="form-group">
                                            <label for="target_fish">Target Fish</label>
                                            <select class="form-control" name="filters[target_fish][]" id="target-fish" multiple="multiple">

                                                @foreach($alltargets as $target)
                                                    <option value="{{$target->id}}">{{$target->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="form-group">
                                            <label for="">Methods</label>
                                            <select class="form-control" name="filters[methods][]" id="methods" multiple="multiple">

                                                @foreach($allmethods as $method)
                                                    <option value="{{$method->id}}">{{$method->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
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

    </script>

@endpush
