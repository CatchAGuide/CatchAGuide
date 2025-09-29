@extends('pages.profile.layouts.profile')
@section('title', "$guiding->title".' '.__('profile.edit') )
@section('custom_style')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>

    .remove-btn{
        position: absolute;
        top:0;
        right:0;
    }
    textarea.select2-search__field{
        padding-bottom:28px !important;
        color:gray;
    }
    .overlay-container {
  position: fixed;
  top: 50%;
  left: 50%;
  width: 100%;
  height: 100%;
  transform: translate(-50%, -50%);
  z-index: 999;
}

.overlay {
  position: relative;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.7);
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}

.spinner {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  border: 4px solid #fff;
  border-top-color: transparent;
  animation: spin 1s infinite linear;
}

.spinner-icon {
  width: 100%;
  height: 100%;
  background-image: url('path/to/spinner-icon.png');
  background-repeat: no-repeat;
  background-position: center center;
}

.message {
  margin-top: 20px;
  color: #fff;
  font-weight: bold;
  text-align: center;
}

.gallery-container{
  background: rgb(237, 237, 237);
  padding:20px;
}


@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
@endsection

@section('profile-content')
<div class="container shadow-lg p-4 ">
@livewire('edit-guiding',['guiding' => $guiding])
</div>
@endsection
<?php /*
@section('profile-content')
    <form action="{{ route('guidings.update', $guiding->id) }}" method="POST" enctype="multipart/form-data">

        {{-- <input type="hidden" name="lat" value="{{$guiding->lat}}">
        <input type="hidden" name="lng" value="{{$guiding->lng}}"> --}}
        @csrf
        @method('put')
        @if ($errors->any())
            @foreach ($errors->all() as $error)
                <div>{{$error}}</div>
            @endforeach
        @endif
        <h3>1. @lang('profile.one')</h3>
        <div class="row">
            <div class="form-group col-md-12">
                <div class="alert alert-primary" role="alert">
                    @lang('profile.onemsg')
                </div>
                <input type="file" name="images[]" accept="image/png, image/jpeg, image/jpg" multiple="multiple">
                <div class="row row-eq-height my-4">
                    @foreach(app('guiding')->getImagesUrl($guiding) as $limgKey => $limg)
                        <div class="col-md-2 col-6 guidings-gallery">
                            <div class="position-relative my-1">
                                <img src="{{$limg}}?version={{ time() }}" class="img-fluid" alt="">
                                <button type="button" class="btn btn-sm btn-danger rounded-0 remove-btn" data-url="{{route('deleteImage',[$guiding,$limgKey])}}">x</button>       
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-12">
                <label for="title">@lang('profile.guidetitle')<span style="color:red;">*</span>
                    <span style="color:red; font-size: 12px;">
                       @lang('profile.guidetitlemsg')
                    </span>
                </label>
                <input type="text" class="form-control" id="title" value="{{$guiding->title}}" name="title" required>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-12">
                <label for="searchPlace">@lang('profile.location')<span style="color:red;">*</span></label>
                <input type="text" class="form-control" id="searchPlace" value="{{$guiding->location}}" name="location"
                       required>
                <input type="hidden" id="placeLat" name="lat" value="{{ old('lat',$guiding->lat) }}"/>
                <input type="hidden" id="placeLng" name="lng" value="{{ old('lng',$guiding->lng) }}"/>
            </div>
        </div>
        <div class="row">
            <hr style="margin: 20px 0px;">
            <div class="form-group">
                <h4 style="margin-bottom: 15px;">@lang('profile.designfor')<span style="color:red;">*</span></h4>
                <div class="form-check form-check-inline">
                    <input type="hidden" value="0" name="recommended_for_anfaenger">
                    <input class="form-check-input" {{$guiding->recommended_for_anfaenger == 1 ? 'checked' : ""}} type="checkbox" value=1 id="recommended_for_anfaenger" name="recommended_for_anfaenger">
                    <label class="form-check-label" for="flexCheckDefault">
                        @lang('profile.begginer')
                    </label>
                  </div>
                  <input type="hidden" value="0" name="recommended_for_fortgeschrittene">
                  <div class="form-check form-check-inline">
                    <input type="hidden" value="0" name="recommended_for_fortgeschrittene">
                    <input class="form-check-input" {{$guiding->recommended_for_fortgeschrittene == 1 ? 'checked' : ""}} type="checkbox" value=1 id="recommended_for_fortgeschrittene" name="recommended_for_fortgeschrittene">
                    <label class="form-check-label" for="flexCheckDefault">
                        @lang('profile.advanced')
                    </label>
                  </div>
                  <div class="form-check form-check-inline">

                    <input type="hidden" value="0" name="recommended_for_profis">
                    <input class="form-check-input" {{$guiding->recommended_for_profis == 1 ? 'checked' : ""}} type="checkbox" value=1 id="recommended_for_profis" name="recommended_for_profis">
                    <label class="form-check-label" for="flexCheckDefault">
                        @lang('profile.professionals')
                    </label>
                  </div>
            </div>
            {{-- <div class="form-group col-md-12">
                <h4 style="margin-bottom: 15px;">@lang('profile.designfor')<span style="color:red;">*</span></h4>
                <input type="hidden" value="0" name="recommended_for_anfaenger">
                <input class="form-check-input" {{$guiding->recommended_for_anfaenger == 1 ? 'checked' : ""}} type="checkbox" value=1 id="recommended_for_anfaenger" name="recommended_for_anfaenger">
                <label class="form-check-label" for="flexCheckDefault">
                    @lang('profile.begginer')
                </label>
                <br>
                <input type="hidden" value="0" name="recommended_for_fortgeschrittene">
                <input class="form-check-input" {{$guiding->recommended_for_fortgeschrittene == 1 ? 'checked' : ""}} type="checkbox" value=1 id="recommended_for_fortgeschrittene" name="recommended_for_fortgeschrittene">
                <label class="form-check-label" for="flexCheckDefault">
                    @lang('profile.advanced')
                </label>
                <br>
                <input type="hidden" value="0" name="recommended_for_profis">
                <input class="form-check-input" {{$guiding->recommended_for_profis == 1 ? 'checked' : ""}} type="checkbox" value=1 id="recommended_for_profis" name="recommended_for_profis">
                <label class="form-check-label" for="flexCheckDefault">
                    @lang('profile.professionals')
                </label>
            </div> --}}
            <hr class="mt-4">
            <div class="form-group col-md-12">
                <label for="duration">@lang('profile.duration')<span style="color:red;">*</span><br>
                    <span style="color:red; font-size: 12px;">
                        @lang('profile.durationmsg')

                    </span>
                </label>
                <input type="number" class="form-control" id="duration" value="{{$guiding->duration}}"
                       placeholder="@lang('profile.duration')" name="duration" required>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-12">
                <label for="required_special_license">@lang('profile.specificguestcard')<span
                            style="color:red;">*</span><br>
                    <span style="color:red; font-size: 12px;">
                        *@lang('profile.specificguestcardmsg')
                    </span>
                </label>
                <select class="form-control" id="special_license_needed" name="special_license_needed" required>
                    <option value="Nein">@lang('profile.no')</option>
                    <option {{$guiding->required_special_license ? 'selected' : ''}}  value="Ja">@lang('profile.yes')</option>
                </select>
            </div>
            <div class="form-group col-md-12" id="special_lizence" {{$guiding->required_special_license ? '' : 'hidden'}}>
                <div class="d-flex">
                    <label for="required_special_license">@lang('profile.designforYes')*</label>
                    <input type="text" class="form-control" id="required_special_license" value="{{$guiding->required_special_license}}" name="required_special_license">
                </div>

            </div>
        </div>
        <hr class="mt-4">
        <h3>2. @lang('profile.two')</h3>
        <div class="form-group col-md-12">
            <label for="fishing_type">@lang('profile.angelType')?<span style="color:red;">*</span></label>
            <select class="form-control" id="fishing_type" name="fishing_type" required>
                <option {{$guiding->fishing_type == 'Aktiv' ? 'selected' : ""}}>@lang('profile.active')</option>
                <option {{$guiding->fishing_type == 'Passiv' ? 'selected' : ""}}>@lang('profile.passive')</option>
                <option {{$guiding->fishing_type == 'Aktiv & Passiv' ? 'selected' : ""}}>@lang('profile.actAndPass')</option>
            </select>
        </div>
        <div class="row">
            <div class="form-group col-md-12">
                <label for="fishing_from">@lang('profile.WhereFrom')?<span style="color:red;">*</span></label>
                <select class="form-control" id="fishing_from" name="fishing_from" required>
                    <option>@lang('profile.WhereFromChoice1')</option>
                    <option>@lang('profile.WhereFromChoice2')</option>
                </select>
            </div>
            <hr class="mt-4">
            <div class="form-group col-md-12">
                <h4 style="margin-bottom: 15px;">@lang('profile.waterType')<span style="color:red;">*</span>
                    <span style="color:red; font-size: 12px;">
                        *@lang('profile.waterTypeMsg')
                    </span>
                </h4>
                @foreach($waters as $water)
                    <input class="form-check-input" style="margin-left: 12px" {{in_array($water->name, unserialize($guiding->water)) ? 'checked' : ''}} type="checkbox"
                           value="{{$water->name}}" id="{{$water->name}}" name="water[]">
                    <label class="form-check-label" for="{{$water->name}}">
                        {{$water->name}}
                    </label>
                @endforeach
            </div>
            <div class="form-group col-md-12">
                <label class="form-check-label" for="water_sonstiges">
                    @lang('profile.otherWaterTypes')
                </label>
                <input class="form-control" type="text" id="water_sonstiges" value="{{$guiding->water_sonstiges}}"
                       placeholder="Sonstige Gewässertypen" name="water_sonstiges">
            </div>
            <hr style="margin-top: 15px;">
            <div class="form-group col-md-12">
                <h4 style="margin-bottom: 15px;">@lang('profile.targetFish')<span style="color:red;">*</span>
                    <span style="color:red; font-size: 12px;">
                        *@lang('profile.targetFishMsg')
                    </span>
                </h4>


                @foreach($targets as $target)
                    <input class="form-check-input"
                           {{in_array($target->name, unserialize($guiding->targets)) ? 'checked' : ''}} type="checkbox"
                           value="{{$target->name}}" id="{{$target->name}}" name="targets[]">
                    <label class="form-check-label" for="{{$target->name}}">
                        {{$target->name}}
                    </label>
                @endforeach
            </div>
            <div class="form-group col-md-12">
                <label class="form-check-label" for="target_fish_sonstiges">
                   @lang('profile.otherTargetFish')
                </label>
                <input class="form-control" type="text" id="target_fish_sonstiges"
                       value="{{$guiding->target_fish_sonstiges}}" placeholder="@lang('profile.otherTargetFish')"
                       name="target_fish_sonstiges">
            </div>

            <hr style="margin-top: 15px;">
            <div class="form-group col-md-12">
                <h4 style="margin-bottom: 15px;">@lang('profile.techniqueMethod')<span style="color:red;">*</span>
                    <span style="color:red; font-size: 12px;">
                        *@lang('profile.techniqueMethodMsg')
                    </span>
                </h4>
                @foreach($methods as $method)
                    <input class="form-check-input"
                           {{in_array($method->name, unserialize($guiding->methods)) ? 'checked' : ''}} type="checkbox"
                           value="{{$method->name}}" id="{{$method->name}}"
                           name="methods[]">
                    <label class="form-check-label" for="{{$method->name}}">
                        {{$method->name}}
                    </label>
                    <br>
                @endforeach
            </div>
            <div class="form-group col-md-12">
                <label class="form-check-label" for="methods_sonstiges">
                    @lang('profile.otherTechniqueMethod')
                </label>
                <input class="form-control" type="text" value="{{$guiding->methods_sonstiges}}"
                       placeholder="@lang('profile.otherTechniqueMethod')" id="methods_sonstiges" name="methods_sonstiges">
            </div>
            <div class="row">
                <div class="form-group col-md-12">
                    <label for="water_name">@lang('profile.bodyOfWater')<span style="color:red;">*</span>
                        <span style="color:red; font-size: 12px;">*@lang('profile.bodyOfWaterMsg')</span></label>
                    <input type="text" class="form-control" id="water_name" value="{{$guiding->water_name}}"
                           name="water_name" placeholder="Gewässername" required>
                </div>
            </div>
            <hr class="mt-4">
            <h3>3. @lang('profile.three')<span style="color:red;">*</span></h3>
            <div class="row">
                <div class="form-group col-md-12">
                    <span style="color:red; font-size: 12px;">*@lang('profile.threeMsg')</span>
                    <textarea id="editor" name="description">{!!  $guiding->description !!} </textarea>
                </div>
            </div>
            <hr class="mt-4">
            <h3>4. @lang('profile.four')</h3>
            <div class="row">
                <div class="form-group col-md-12">
                    <label for="required_equipment">@lang('profile.gearandeq')<span style="color:red;">*</span></label>
                    <select class="form-control" id="required_equipment" name="required_equipment">
                        <option>@lang('profile.pleaseSelect')</option>
                        <option {{$guiding->required_equipment == "is_needed" ? 'selected' : ''}} value="is_needed">@lang('profile.broughtBySelf')
                        </option>
                        <option {{$guiding->required_equipment == "is_there" ? 'selected' : ''}} value="is_there">@lang('profile.avail')
                        </option>
                    </select>
                </div>
                <div class="form-group col-md-12" id="needed_equipment" hidden>
                    <label>Benötigtes Equipment<span style="color:red;">*</span></label>
                    <input type="text" class="form-control" value="{{$guiding->needed_equipment}}"
                           placeholder="Benötigtes Equipment" name="needed_equipment">
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-12">
                    <label for="provided_equipment">@lang('profile.meetingPoint')<span style="color:red;">*</span></label>
                    <input type="text" class="form-control" id="meeting_point" value="{{$guiding->meeting_point}}"
                           placeholder="@lang('profile.meetingPoint')" name="meeting_point">
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-12">
                    <label for="additional_information">@lang('profile.four')</label>
                    <input type="text" class="form-control" id="additional_information" placeholder="@lang('profile.four')"
                           value="{{$guiding->additional_information}}" name="additional_information">
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-12">
                    <label for="catering">@lang('profile.meals')</label>
                    <input type="text" class="form-control" id="catering" placeholder="@lang('profile.meals')"
                           value="{{$guiding->catering}}" name="catering">
                </div>
            </div>
            {{--
                <div class="row">
                    <div class="form-group col-md-12">
                        <label for="payment_point">Buchung oder Anfrage*<br/><span style="color:red; font-size: 12px;">*Wähle aus, ob dein Guiding direkt gebucht werden kann (soweit verfügbar) oder ob der Gast erst eine Anfrage stellen soll.</span></label>
                        <select class="form-control" id="payment_point" name="payment_point" >
                            <option>bitte auswählen..</option>
                            <option {{$guiding->payment_point == "with_booking" ? 'selected' : ''}} value="with_booking">Guiding kann direkt gebucht werden</option>
                            <option {{$guiding->payment_point == "after_inquiry" ? 'selected' : ''}} value="after_inquiry">Nur Anfrage möglich</option>
                        </select>
                    </div>
                </div>
            --}}
            <hr class="mt-4">
            <h3>@lang('profile.five')</h3>
            <p>@lang('profile.fees')<span style="color:red;">*</span><br>
                <span style="color:red; font-size: 11px;">*@lang('profile.feeMsg')</span><br>
                @lang('profile.feeMsg1')<br>
                @lang('profile.feeMsg2')<br>
                @lang('profile.feeMsg3')<br>
            </p>
            <hr class="mt-2">
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="max_guests">@lang('profile.maxGuest')<span style="color:red;">*</span></label>
                    <select class="form-control" id="max_guests" name="max_guests" required>
                        <option {{$guiding->max_guests == 1 ? 'selected' : ""}} value="1">1</option>
                        <option {{$guiding->max_guests == 2 ? 'selected' : ""}} value="2">2</option>
                        <option {{$guiding->max_guests == 3 ? 'selected' : ""}} value="3">3</option>
                        <option {{$guiding->max_guests == 4 ? 'selected' : ""}} value="4">4</option>
                        <option {{$guiding->max_guests == 5 ? 'selected' : ""}} value="5">5</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-4" id="one-person">
                    <label for="price">@lang('profile.totalPrice')</label>
                    <input type="number" class="form-control" id="price" value="{{$guiding->price}}"
                           placeholder="@lang('profile.totalPrice')" name="price">
                </div>
                <div class="form-group col-md-4" id="two-person" {{$guiding->price_two_persons > 0 ? '' : 'hidden'}}>
                    <label for="price_two_persons">{{ translate('Gesamtpreis für zwei Personen') }}</label>
                    <input type="number" class="form-control" id="price_two_persons"
                           placeholder="{{ translate('Gesamtpreis für zwei Personen') }}"
                           value="{{$guiding->price_two_persons > 0 ? $guiding->price_two_persons : ''}}"
                           name="price_two_persons">
                </div>

                <div class="form-group col-md-4"
                     id="three-person" {{$guiding->price_three_persons > 0 ? '' : 'hidden'}}>
                    <label for="price_three_persons">{{ translate('Gesamtpreis für drei Personen') }}</label>
                    <input type="number" class="form-control" id="price_three_persons"
                           placeholder="{{ translate('Gesamtpreis für drei Personen') }}"
                           value="{{$guiding->price_three_persons > 0 ? $guiding->price_three_persons : ''}}"
                           name="price_three_persons">
                </div>
                <div class="form-group col-md-4" id="four-person" {{$guiding->price_four_persons > 0 ? '' : 'hidden'}}>
                    <label for="price_four_persons">{{ translate('Gesamtpreis für vier Personen') }}</label>
                    <input type="number" class="form-control" id="price_four_persons"
                           placeholder="{{ translate('Gesamtpreis für vier Personen') }}"
                           value="{{$guiding->price_four_persons > 0 ? $guiding->price_four_persons : ''}}"
                           name="price_four_persons">
                </div>
                <div class="form-group col-md-4" id="five-person" {{$guiding->price_five_persons > 0 ? '' : 'hidden'}}>
                    <label for="price_five_persons">{{ translate('Gesamtpreis für fünf Personen') }}</label>
                    <input type="number" class="form-control" id="price_five_persons"
                           placeholder="{{ translate('Gesamtpreis für fünf Personen') }}"
                           value="{{$guiding->price_five_persons > 0 ? $guiding->price_five_persons : ''}}"
                           name="price_five_persons">
                </div>
            </div>
            <hr class="mt-4">
            <button type="submit" class="btn btn-primary">@lang('profile.save')</button>
            <hr class="mt-4">
            <!-- Button trigger modal -->
            <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#exampleModal">
                @if($guiding->status == 1)
                    <button type="button" class="btn btn-danger">
                        @lang('profile.deactivateGuide')
                    </button>
                @else
                    <button type="button" class="btn btn-success">
                        @lang('profile.activateGuide')
                    </button>
                @endif
            </a>
        </div>
    </form>

    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    @if($guiding->status == 1)
                        <h5 class="modal-title" id="exampleModalLabel">@lang('profile.deactivateGuide')</h5>
                    @else
                        <h5 class="modal-title" id="exampleModalLabel">@lang('profile.activateGuide')</h5>
                    @endif

                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @if($guiding->status == 1)
                        {{ translate('Willst Du das Guiding wirklich deaktivieren? Es erscheint dann nicht mehr im Suchverlauf...') }}
                    @else
                        {{ translate('Willst Du das Guiding wirklich aktivieren? Es steht dann für Buchungen zur Verfügung.') }}
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Zurück') }}</button>
                    <a href="{{route('deleteguiding', $guiding->id)}}">
                        @if($guiding->status == 1)
                            <button type="button" class="btn btn-danger">@lang('profile.deactivateGuide')</button>
                        @else
                            <button type="button" class="btn btn-success">@lang('profile.activateGuide')</button>
                        @endif
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js_after')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
    $('.js-example-basic-single').select2({
        placeholder: "Select target fish",
        allowClear: true
    });
});
</script>
<script>
    $(document).ready(function () {
        $('.remove-btn').on('click', function () {
            var url = $(this).data('url');
            var imageContainer = $(this).parent();

            $.ajax({
                url: url,
                type: 'GET',
                success: function (response) {
                    // On successful removal, remove the image container from the DOM
                    imageContainer.remove();
                },
                error: function (xhr, status, error) {
                    console.error(error);
                }
            });
        });
    });
</script>
    <script type="text/javascript">
        var i = 0;
        $("#add-btn").click(function(){
            if(i < 4){
                ++i;
            $("#dynamicAddRemove").append('<tr><td><input type="file" accept="image/png, image/jpeg, image/jpg" name="gallery['+i+'][image_name]" class="form-control" /></td><td><button type="button" class="btn btn-danger remove-tr">@lang("profile.del")</button></td></tr>');
            }
        });
        $(document).on('click', '.remove-tr', function(){
            $(this).parents('tr').remove();
        });
    </script>

    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places,geocoding"></script>
    <script>
        function initialize() {
            var input = document.getElementById('searchPlace');
            var autocomplete = new google.maps.places.Autocomplete(input);
            google.maps.event.addListener(autocomplete, 'place_changed', function () {
                var place = autocomplete.getPlace();
                document.getElementById('placeLat').value = place.geometry.location.lat();
                document.getElementById('placeLng').value = place.geometry.location.lng();
            });
        }
        window.addEventListener('load', initialize);
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'de',
                events: {
                    url: '/events',
                    method: 'GET'
                },

                headerToolbar: {
                    right: 'today,prev,next',
                    center: 'title',
                    left: ''
                }
            });
            calendar.render();
        });

    </script>

    <script>
        $('#max_guests').on('change', (elem) => {
            let max_guests = $('#max_guests');

            if (max_guests.val() == 1) {

                $('#one-person').prop('hidden', false);
                $('#one-person').find('input').first().prop('required', true);

                $('#two-person').prop('hidden', true);
                $('#two-person').find('input').first().prop('required', false);

                $('#three-person').prop('hidden', true);
                $('#three-person').find('input').first().prop('required', false);

                $('#four-person').prop('hidden', true);
                $('#four-person').find('input').first().prop('required', false);

                $('#five-person').prop('hidden', true);
                $('#five-person').find('input').first().prop('required', false);
            } else if (max_guests.val() == 2) {

                $('#one-person').prop('hidden', false);
                $('#one-person').find('input').first().prop('required', true);

                $('#two-person').prop('hidden', false);
                $('#two-person').find('input').first().prop('required', true);

                $('#three-person').prop('hidden', true);
                $('#three-person').find('input').first().prop('required', false);

                $('#four-person').prop('hidden', true);
                $('#four-person').find('input').first().prop('required', false);

                $('#five-person').prop('hidden', true);
                $('#five-person').find('input').first().prop('required', false);
            } else if (max_guests.val() == 3) {

                $('#one-person').prop('hidden', false);
                $('#one-person').find('input').first().prop('required', true);

                $('#two-person').prop('hidden', false);
                $('#two-person').find('input').first().prop('required', true);

                $('#three-person').prop('hidden', false);
                $('#three-person').find('input').first().prop('required', true);

                $('#four-person').prop('hidden', true);
                $('#four-person').find('input').first().prop('required', false);

                $('#five-person').prop('hidden', true);
                $('#five-person').find('input').first().prop('required', false);
            } else if (max_guests.val() == 4) {

                $('#one-person').prop('hidden', false);
                $('#one-person').find('input').first().prop('required', true);

                $('#two-person').prop('hidden', false);
                $('#two-person').find('input').first().prop('required', true);

                $('#three-person').prop('hidden', false);
                $('#three-person').find('input').first().prop('required', true);

                $('#four-person').prop('hidden', false);
                $('#four-person').find('input').first().prop('required', true);

                $('#five-person').prop('hidden', true);
                $('#five-person').find('input').first().prop('required', false);
            } else if (max_guests.val() == 5) {

                $('#one-person').prop('hidden', false);
                $('#one-person').find('input').first().prop('required', true);

                $('#two-person').prop('hidden', false);
                $('#two-person').find('input').first().prop('required', true);

                $('#three-person').prop('hidden', false);
                $('#three-person').find('input').first().prop('required', true);

                $('#four-person').prop('hidden', false);
                $('#four-person').find('input').first().prop('required', true);

                $('#five-person').prop('hidden', false);
                $('#five-person').find('input').first().prop('required', true);
            }

        })

        $('#required_equipment').on('change', (elem) => {
            if ($('#required_equipment').val() === 'is_needed') {
                $('#needed_equipment').prop('hidden', false)
            } else {
                $('#needed_equipment').prop('hidden', true)
            }
        });

        $('#special_license_needed').on('change', (elem) => {
            if ($('#special_license_needed').val() === 'Nein') {
                $('#special_lizence').prop('hidden', true)
            } else {
                $('#special_lizence').prop('hidden', false)
            }
        });
    </script>
@endsection
*/ ?>