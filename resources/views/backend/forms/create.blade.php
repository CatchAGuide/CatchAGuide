@extends('backend.layout.app')

@section('title', 'Fahrzeug erstellen')

@section('content')
    <script src="https://cdn.ckeditor.com/4.16.1/standard/ckeditor.js"></script>
    <div class="quote-area">
        <div class="container-fluid">
            <div class="row no-gutters">
                <div class="col-xl-8 quote-form-wrapper">
                    <div class="row justify-content-xl-end justify-content-center">
                            <div class="quote-form">
                                <div class="text-center">
                                    <h1 class="c3">Bitte geben Sie hier Ihre Fahrzeugdaten ein.</h1>
                                </div><!-- /.thm-header -->
                                @if ($errors->any())
                                    @foreach ($errors->all() as $error)
                                        <div>{{$error}}</div>
                                    @endforeach
                                @endif
                                 <br>
                                <form class="clearfix text-center" action="{{route('admin.store')}}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row">
                                        <div class="col-lg-4 col-sm-12">
                                            <div class="form-group">
                                                <label>Name:</label>
                                                <input type="text" name="name" id="name" placeholder="Name" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-sm-12">
                                            <div class="form-group">
                                                <label>Hersteller:</label>
                                                <input type="text" name="manufacturer" id="manufacturer" placeholder="Hersteller" class="form-control" required>
                                            </div><!-- /.form-group -->
                                        </div>
                                        <div class="col-lg-4 col-sm-12">
                                            <div class="form-group">
                                                <label>Model:</label>
                                                <input type="text" name="model" id="model" placeholder="Model" class="form-control" required>
                                            </div><!-- /.form-group -->
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="form-group">
                                        <label>Max. Personen:</label>
                                        <input type="number" min="0" name="max_person" id="max_person" placeholder="Maximale Personenzahl" class="form-control" required>
                                    </div><!-- /.form-group -->
                                    <div class="form-group">
                                        <label>Sitzplätze:</label>
                                        <input type="number" min="0" name="seats" id="seats" placeholder="Sitzplätze" class="form-control" required>
                                    </div><!-- /.form-group -->
                                    <div class="row">
                                        <div class="col-12">
                                            <h5 class="mb-2">Schlafplätze:</h5>
                                        </div>
                                        <div class="col-lg-2 col-sm-12">
                                            <div class="form-group">
                                                <label>Gesamt:</label>
                                                <input type="number" min="0" name="sleeping_places" id="sleeping_places" placeholder="Gesamt" class="form-control" required>
                                            </div><!-- /.form-group -->
                                        </div>
                                        <div class="col-lg-2 col-sm-12">
                                            <div class="form-group">
                                                <label>Alkoven:</label>
                                                <input type="number" min="0" name="bed_alcove" id="bed_alcove" placeholder="Alkoven" class="form-control" required>
                                            </div><!-- /.form-group -->
                                        </div>
                                        <div class="col-lg-2 col-sm-12">
                                            <div class="form-group">
                                                <label>Hinten:</label>
                                                <input type="number" min="0" name="rear_sleeping_places" id="rear_sleeping_places" placeholder="Hinten" class="form-control" required>
                                            </div><!-- /.form-group -->
                                        </div>
                                        <div class="col-lg-2 col-sm-12">
                                            <div class="form-group">
                                                <label>Dinette:</label>
                                                <input type="number" min="0" name="dinette_sleeping_places" id="dinette_sleeping_places" placeholder="Dinette" class="form-control" required>
                                            </div><!-- /.form-group -->
                                        </div>
                                        <div class="col-lg-2 col-sm-12">
                                            <div class="form-group">
                                                <label>Stockbett:</label>
                                                <input type="number" min="0" name="bunk_bed" id="bunk_bed" placeholder="Stockbett" class="form-control" required>
                                            </div><!-- /.form-group -->
                                        </div>
                                        <div class="col-lg-2 col-sm-12">
                                            <div class="form-group">
                                                <label>Hubbett:</label>
                                                <input type="number" min="0" name="lift_bed" id="lift_bed" placeholder="Hubbett" class="form-control" required>
                                            </div><!-- /.form-group -->
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label>Frischwasser Tank:</label>
                                                <input type="text" min="0" name="fresh_water_tank" id="fresh_water_tank" placeholder="Frischwasser Tank" class="form-control" required>
                                            </div><!-- /.form-group -->
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label>Abwasser Tank:</label>
                                                <input type="text" min="0" name="waste_water_tank" id="waste_water_tank" placeholder="Abwasser Tank" class="form-control" required>
                                            </div><!-- /.form-group -->
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Heizung:</label>
                                        <input type="text" min="0" name="heating" id="heating" placeholder="Heizung" class="form-control" required>
                                    </div><!-- /.form-group -->
                                    <div class="form-group">
                                        <label>Garage:</label>
                                        <input type="text" min="0" name="rear_garage" id="rear_garage" placeholder="Garage" class="form-control" required>
                                    </div><!-- /.form-group -->
                                    <hr>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label>Verkaufspreis:</label>
                                                <input type="number" min="0" name="price" id="price" placeholder="Verkaufspreis" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label>Kilometerstand:</label>
                                                <input type="number" min="0" name="mileage" id="mileage" placeholder="Kilometerstand" class="form-control" required>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-12">
                                            <h5 class="mb-2">Motorisierung:</h5>
                                        </div>
                                        <div class="col-lg-3 col-sm-12">
                                            <div class="form-group">
                                                <label>Kraftstoff:</label>
                                                <input type="text" name="fuel_type" id="fuel_type" placeholder="Kraftstoff" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-sm-12">
                                            <div class="form-group">
                                                <label>Leistung in PS/KW:</label>
                                                <input type="number" min="0" name="power" id="power" placeholder="Leistung" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-sm-12">
                                            <div class="form-group">
                                                <label>Getriebeart:</label>
                                                <input type="text" name="gearbox" id="gearbox" placeholder="Getriebeart" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-sm-12">
                                            <div class="form-group">
                                                <label>Schadstoffklasse:</label>
                                                <input type="number" min="0" name="emission_class" id="emission_class" placeholder="Schadstoffklasse" class="form-control" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <h5 class="mb-2">Abmessungen:</h5>
                                        </div>
                                        <div class="col-lg-4 col-sm-12">
                                            <div class="form-group">
                                                <label>Fahrzeuglänge:</label>
                                                <input type="number" min="0" name="length" id="length" placeholder="Fahrzeuglänge" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-sm-12">
                                            <div class="form-group">
                                                <label>Fahrzeugbreite:</label>
                                                <input type="number" min="0" name="width" id="width" placeholder="Fahrzeugbreite" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-sm-12">
                                            <div class="form-group">
                                                <label>Fahrzeughöhe:</label>
                                                <input type="number" min="0" name="heigth" id="heigth" placeholder="Fahrzeughöhe" class="form-control" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <h5 class="mb-2">Sonstiges</h5>
                                        </div>
                                        <div class="col-lg-4 col-sm-12">
                                            <div class="form-group">
                                                <label>Umweltplakette:</label>
                                                <input type="number" min="0" name="eco_badge" id="eco_badge" placeholder="Umweltplakette" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-sm-12">
                                            <div class="form-group">
                                                <label>Vorbesitzer:</label>
                                                <input type="number" min="0" name="vehicle_owners" id="vehicle_owners" placeholder="Vorbesitzer" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-sm-12">
                                            <div class="form-group">
                                                <label>Zulässiges Gesamtgewicht:</label>
                                                <input type="number" min="0" name="total_weight" id="total_weight" placeholder="Zulässiges Gesamtgewicht" class="form-control" required>
                                            </div>
                                        </div>

                                        <div class="col-6">
                                            <div class="form-group">
                                                <label>Erstzulassung:</label>
                                                <input type="date" name="first_registration" id="first_registration" placeholder="Erstzulassung" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label>Hauptuntersuchung:</label>
                                                <input type="date" name="main_exam" id="main_exam" placeholder="Hauptuntersuchung" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label>Vermietungslink:</label>
                                                <input type="text" name="lend" id="lend" placeholder="Vermietungslink" class="form-control" required>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row mb-2">
                                        <div class="col-12">
                                            <h5 class="mb-2">
                                                Ausstattung:
                                            </h5>
                                        </div>
                                        @foreach(\App\Models\Equipment::getAttributesList() as $key => $value)
                                            <div class="col-lg-3 col-sm-12">
                                                <label><input class="p-3" type="checkbox" name="check[{{ $value }}]" value="{{ $value }}"> {{ __('equipment.' . $value) }}</label>

                                            </div>
                                        @endforeach
                                    </div>
                                    <hr>
                                    <div class="form-group">
                                        <textarea name="description" id="description" class="form-control" placeholder="Beschreibung" required></textarea>
                                    </div>
                                    <div class="form-group">
                                        <input type="file" class="form-control-file" name="files[]" multiple>
                                    </div>
                                    <button type="submit" style="background-color: #05223a !important;" >Fahrzeug erstellen</button>
                                </form>
                            </div><!-- /.quote-form -->
                    </div><!-- /.row -->
                </div><!-- /.col-lg-8 -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <script>
        CKEDITOR.replace( 'description' );
    </script>
@endsection
