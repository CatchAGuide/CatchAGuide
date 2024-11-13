@extends('admin.layouts.app')

@section('title', 'Guiding #' . $guiding->id . ' editieren')

@section('content')
    <div class="container">
        <div class="row">
            <form action="{{ route('guidings.update', $guiding->id) }}" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="lat" value="{{$guiding->lat}}">
                <input type="hidden" name="lng" value="{{$guiding->lng}}">
                <input type="hidden" name="user_id" value="{{$guiding->user_id}}">
                @csrf
                @method('put')
                <hr>
                @if ($errors->any())
                    @foreach ($errors->all() as $error)
                        <div>{{$error}}</div>
                    @endforeach
                @endif
                <h3>1. Das Guiding</h3>
                <hr>
                <div class="row">
                    <div class="form-group col-md-12">
                        <img class=""
                             src="{{asset('images/' . $guiding->thumbnail_path)}}"
                             alt="" width="300">
                        <label for="thumbnail">Thumbnail*</label>
                        <input type="file" class="form-control" id="thumbnail" name="thumbnail" >
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-12">
                        <label for="title">Titel*</label>
                        <input type="text" class="form-control" id="title" value="{{$guiding->title}}" name="title" required>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="searchPlace">Ort*</label>
                        <input type="text" class="form-control" id="searchPlace" value="{{$guiding->location}}" name="location" required>
                        <input type="hidden" id="placeLat" name="lat" />
                        <input type="hidden" id="placeLng" name="lng" />
                    </div>
                    <div class="form-group col-md-6">
                        <label for="max_guests">Maximale Gästeanzahl*</label>
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
                    <h4 style="margin-bottom: 15px;">Ausgelegt für:*</h4>
                    <div class="form-group col-md-4">
                        <input type="hidden" value="0" name="recommended_for_anfaenger">
                        <input class="form-check-input" {{$guiding->recommended_for_anfaenger == 1 ? 'checked' : ""}} type="checkbox" value=1 id="recommended_for_anfaenger"
                               name="recommended_for_anfaenger">
                        <label class="form-check-label" for="flexCheckDefault">
                            Anfänger
                        </label>
                    </div>
                    <div class="form-group col-md-4">
                        <input type="hidden" value="0" name="recommended_for_fortgeschrittene">
                        <input class="form-check-input" {{$guiding->recommended_for_fortgeschrittene == 1 ? 'checked' : ""}} type="checkbox" value=1 id="recommended_for_fortgeschrittene"
                               name="recommended_for_fortgeschrittene">
                        <label class="form-check-label" for="flexCheckDefault">
                            Fortgeschrittene
                        </label>
                    </div>
                    <div class="form-group col-md-4">
                        <input type="hidden" value="0" name="recommended_for_profis">
                        <input class="form-check-input" {{$guiding->recommended_for_profis == 1 ? 'checked' : ""}} type="checkbox" value=1 id="recommended_for_profis"
                               name="recommended_for_profis">
                        <label class="form-check-label" for="flexCheckDefault">
                            Profis
                        </label>
                    </div>
                    <hr>
                    <div class="form-group col-md-6">
                        <label for="duration">Dauer in Stunden*</label>
                        <input type="number" class="form-control" id="duration" value="{{$guiding->duration}}" name="duration" required>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-12">
                        <label for="required_special_license">Wird eine spezifische Gastkarte/Gewässerschein benötigt?*</label>
                        <select class="form-control" id="special_license_needed" name="special_license_needed" required>
                            <option value="Nein">Nein</option>
                            <option {{$guiding->required_special_license ? 'selected' : ''}}  value="Ja">Ja</option>
                        </select>
                    </div>
                    <div class="form-group col-md-12" id="special_lizence" {{$guiding->required_special_license ? '' : 'hidden'}}>
                        <label for="required_special_license">Bitte gib die Gastkarte oder den Gewässerschein an der benötigt wird*</label>
                        <input type="text" class="form-control" id="required_special_license"
                               value="{{$guiding->required_special_license}}" name="required_special_license">
                    </div>
                </div>

                <hr>
                <h3>2. Technische Informationen</h3>
                <hr>
                <div class="form-group col-md-6">
                    <label for="fishing_type">Angel-Art?*</label>
                    <select class="form-control" id="fishing_type" name="fishing_type" required>
                        <option>Aktiv</option>
                        <option>Passiv</option>
                    </select>
                </div>
                <hr>
                <div class="form-group col-md-6 mb-3">
                    <label for="fishing_from">Von wo aus wird geangelt?*</label>
                    <select class="form-control" id="fishing_from" name="fishing_from" required>
                        <option>Vom Boot</option>
                        <option>Vom Ufer</option>
                    </select>
                </div>
                <hr>
                <div class="row">
                    <h4 style="margin-bottom: 15px;">Gewässer Typen*</h4>
                    @foreach($waters as $water)
                        <div class="form-group col-md-3">
                            <input class="form-check-input" {{in_array($water->name, unserialize($guiding->water)) ? 'checked' : ''}} type="checkbox" value="{{$water->name}}" id="{{$water->name}}" name="water[]">
                            <label class="form-check-label" for="{{$water->name}}">
                                {{$water->name}}
                            </label>
                        </div>
                    @endforeach

                    <div class="form-group col-md-12">
                        <label class="form-check-label" for="water_sonstiges">
                            Sonstiges
                        </label>
                        <input class="form-control" type="text" id="water_sonstiges" value="{{$guiding->water_sonstiges}}" name="water_sonstiges">
                    </div>

                    <hr style="margin-top: 15px;">
                    <h4 style="margin-bottom: 15px;">Zielfisch*</h4>
                    @foreach($targets as $target)
                        <div class="col-md-3 form-group">
                            <input class="form-check-input" {{in_array($target->name, unserialize($guiding->targets)) ? 'checked' : ''}} type="checkbox" value="{{$target->name}}" id="{{$target->name}}" name="targets[]">
                            <label class="form-check-label" for="{{$target->name}}">
                                {{$target->name}}
                            </label>
                        </div>
                    @endforeach
                    <div class="form-group col-md-12">
                        <label class="form-check-label" for="target_fish_sonstiges">
                            Sonstiger Fisch
                        </label>
                        <input class="form-control" type="text" id="target_fish_sonstiges" value="{{$guiding->target_fisth_sonstiges}}" name="target_fish_sonstiges">
                    </div>

                    <hr style="margin-top: 15px;">
                    <h4 style="margin-bottom: 15px;">Technik / Methode*</h4>
                    @foreach($methods as $method)
                        <div class="col-md-3 form-group">
                            <input class="form-check-input" {{in_array($method->name, unserialize($guiding->methods)) ? 'checked' : ''}} type="checkbox" value="{{$method->name}}" id="{{$method->name}}"
                                   name="methods[]">
                            <label class="form-check-label" for="{{$method->name}}">
                                {{$method->name}}
                            </label>
                        </div>
                    @endforeach
                    <div class="form-group col-md-12">
                        <label class="form-check-label" for="methods_sonstiges">
                            Sonstige Methode
                        </label>
                        <input class="form-control" type="text" value="{{$guiding->methods_sonstiges}}" id="methods_sonstiges" name="methods_sonstiges">
                    </div>


                    <hr>
                    <h3>3. Beschreibung</h3>
                    <hr>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label for="description">Beschreibung*</label>
                            <textarea id="editor" name="description">{{ $guiding->description }}</textarea>
                        </div>

                    <hr>
                    <h3>4. Sonstiges</h3>
                    <hr>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label for="required_equipment">Equipment*</label>
                            <select class="form-control" id="required_equipment" name="required_equipment" >
                                <option>bitte auswählen..</option>
                                <option {{$guiding->required_equipment == "is_needed" ? 'selected' : ''}} value="is_needed">soll selbst mitgebracht werden</option>
                                <option {{$guiding->required_equipment == "is_there" ? 'selected' : ''}} value="is_there">ist vorhanden</option>
                            </select>
                        </div>
                        <div class="form-group col-md-12" id="needed_equipment" hidden>
                            <label>Benötigtes Equipment*</label>
                            <input type="text" class="form-control" value="{{$guiding->needed_equipment}}" name="needed_equipment">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label for="provided_equipment">Treffpunkt*</label>
                            <input type="text" class="form-control" id="meeting_point" value="{{$guiding->meeting_point}}" name="meeting_point">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label for="additional_information">Sonstiges</label>
                            <input type="text" class="form-control" id="additional_information" value="{{$guiding->additional_information}}" name="additional_information">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label for="catering">Verpflegung</label>
                            <input type="text" class="form-control" id="catering" value="{{$guiding->catering}}" name="catering">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label for="payment_point">Anfrage oder Buchung*</label>
                            <select class="form-control" id="payment_point" name="payment_point" >
                                <option>bitte auswählen..</option>
                                <option {{$guiding->payment_point == "with_booking" ? 'selected' : ''}} value="with_booking">Guiding kann direkt gebucht werden</option>
                                <option {{$guiding->payment_point == "after_inquiry" ? 'selected' : ''}} value="after_inquiry">Nur Anfrage möglich</option>
                            </select>
                        </div>
                    </div>
                    <hr>
                    <h3>5. Preis</h3>
                    <p>Catchaguide addiert eine Gebühr von 12€ auf Deinen Preis was den Endpreis des Kunden darstellt.</p>
                    <hr>
                    <div class="row">
                        <div class="form-group col-md-4" id="one-person">
                            <label for="price">Preis 1 Person</label>
                            <input type="number" class="form-control" id="price" value="{{$guiding->price}}" name="price">
                        </div>
                        <div class="form-group col-md-4" id="two-person" {{$guiding->price_two_persons > 0 ? '' : 'hidden'}}>
                            <label for="price_two_persons">Preis 2 Person</label>
                            <input type="number" class="form-control" id="price_two_persons" value="{{$guiding->price_two_persons > 0 ? $guiding->price_two_persons : ''}}" name="price_two_persons">
                        </div>

                        <div class="form-group col-md-4" id="three-person" {{$guiding->price_three_persons > 0 ? '' : 'hidden'}}>
                            <label for="price_three_persons">Preis 3 Person</label>
                            <input type="number" class="form-control" id="price_three_persons" value="{{$guiding->price_three_persons > 0 ? $guiding->price_three_persons : ''}}" name="price_three_persons">
                        </div>
                        <div class="form-group col-md-4" id="four-person" {{$guiding->price_four_persons > 0? '' : 'hidden'}}>
                            <label for="price_four_persons">Preis 4 Person</label>
                            <input type="number" class="form-control" id="price_four_persons" value="{{$guiding->price_four_persons > 0 ? $guiding->price_four_persons : ''}}" name="price_four_persons">
                        </div>
                        <div class="form-group col-md-4" id="five-person" {{$guiding->price_five_persons > 0 ? '' : 'hidden'}}>
                            <label for="price_five_persons">Preis 5 Person</label>
                            <input type="number" class="form-control" id="price_five_persons" value="{{$guiding->price_five_persons > 0 ? $guiding->price_five_persons : ''}}" name="price_five_persons">
                        </div>
                    </div>
                    <br/>
                    <hr style="margin-top: 40px;"><hr>
                    <button type="submit" class="btn btn-primary">Speichern</button>
                </div>
            </form>
        </div>
    </div>


    <hr><hr>
    <!-- Button trigger modal -->
    <a href="javascript:void(0)"  data-bs-toggle="modal" data-bs-target="#exampleModal">
        @if($guiding->status == 1)
            <button  type="button" class="btn btn-danger">
                Guiding deaktivieren
            </button>
        @else
            <button  type="button" class="btn btn-success">
                Guiding aktivieren
            </button>
        @endif


    </a>

    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    @if($guiding->status == 1)
                        <h5 class="modal-title" id="exampleModalLabel">Guiding deaktivieren</h5>
                    @else
                        <h5 class="modal-title" id="exampleModalLabel">Guiding aktivieren</h5>
                    @endif

                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @if($guiding->status == 1)
                        Willst Du das Guiding wirklich deaktivieren? Es erscheint dann nicht mehr im Suchverlauf...
                    @else
                        Willst Du das Guiding wirklich aktivieren? Es steht dann für Buchungen zur Verfügung.
                    @endif


                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Zurück</button>
                    <a href="{{route('deleteguiding', $guiding->id)}}">
                        @if($guiding->status == 1)
                            <button type="button" class="btn btn-danger">Guiding deaktivieren</button>
                        @else
                            <button type="button" class="btn btn-success">Guiding aktivieren</button>
                        @endif

                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js_after')

    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY', 'AIzaSyBiGuDOg_5yhHeoRz-7bIkc9T1egi1fA7Q') }}&libraries=places,geocoding"></script>
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
        $('#max_guests').on('change', (elem) => {
            let max_guests = $('#max_guests');

            if(max_guests.val() >= 1) {
                $('#one-person').prop('hidden', false);
                $('#two-person').prop('hidden', true);
                $('#three-person').prop('hidden', true);
                $('#four-person').prop('hidden', true);
                $('#five-person').prop('hidden', true);
            }

            if(max_guests.val() >= 2) {
                $('#two-person').prop('hidden', false);
                $('#three-person').prop('hidden', true);
                $('#four-person').prop('hidden', true);
                $('#five-person').prop('hidden', true);
            }

            if(max_guests.val() >= 3) {
                $('#three-person').prop('hidden', false);
                $('#four-person').prop('hidden', true);
                $('#five-person').prop('hidden', true);
            }

            if(max_guests.val() >= 4) {
                $('#four-person').prop('hidden', false);
                $('#five-person').prop('hidden', true);
            }

            if(max_guests.val() >= 5) {
                $('#five-person').prop('hidden', false);
            }

        })

        $('#required_equipment').on('change', (elem) => {
            if($('#required_equipment').val() === 'is_needed') {
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
