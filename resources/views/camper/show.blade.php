@extends('layout.app')

@section('title', 'Unser Fahrzeug')

@section('content')
    <style>
        #desc > p {
            line-break: anywhere;
        }
    </style>
    <div class="single-service f1">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-sm-12 text-center">
                    <article>
                        <figure>
                            @if($campers->images()->count() > 0)
                                <img src="{{ $campers->getFirstImage() }}" alt="Service Thumb">
                            @else
                                <img src="{{ asset('assets/img/service-1-1.jpg') }}" alt="Service Thumb">
                            @endif
                        </figure>
                        <div class="service-body">
                            <h2>{{$campers->name}}</h2>
                            @if($campers->images()->count() > 0)
                                <div class="row flex-md-row flex-column align-items-center">
                                    @foreach($campers->images as $image)
                                        <div class="col-sm-3">
                                            <img src="{{ $image->getImage() }}" alt="Service Image">
                                        </div><!-- /.col-sm-6 -->
                                    @endforeach
                                </div><!-- /.d-flex -->
                            @endif
                            <div class="container">
                                <h3>Unser Fahrzeug {{$campers->name}}:</h3>
                                <p class="my-4"><a href="{!! $campers->lend !!}" class="btn btn-block"
                                                   style="background-color: #05223a !important; color: white !important;">jetzt
                                        mieten </a></p>

                            </div>
                            <div class="row">
                                <div class="col-12" id="desc">
                                    {!! $campers->description !!}
                                </div>
                            </div>
                        </div><!-- /.service-body -->
                        <br>
                        <br>
                        <div class="service-body col-lg-12 col-sm-12 text-center">
                            <h2>Unser {{$campers->name}} im Detail</h2>
                            <div class="row">
                                <div class="service-body text-left">
                                    <div class="table-responsive">
                                        <table class="table table-striped" id="datatable">
                                            <tbody>
                                            <tr>
                                                <td><strong>Max. Personenzahl</strong></td>
                                                <td>{{$campers->max_person}}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Sitzplätze</strong></td>
                                                <td>{{$campers->seats}}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center" colspan="2"><h5><strong>Schlafplätze</strong>
                                                    </h5></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Gesamt</strong></td>
                                                <td>{{$campers->sleeping_places}}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Alkove</strong></td>
                                                <td>{{$campers->bed_alcove}}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Hinten</strong></td>
                                                <td>{{$campers->rear_sleeping_places}} </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Dinette</strong></td>
                                                <td>{{$campers->dinette_sleeping_places}}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Hubbett</strong></td>
                                                <td>{{$campers->lift_bed}}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Stockbett</strong></td>
                                                <td>{{$campers->bunk_bed}}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center" colspan="2"><h5><strong>Ausstattung</strong>
                                                    </h5></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Frischwasser Tank in L:</strong></td>
                                                <td>{{$campers->fresh_water_tank}}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Abwasser Tank in L:</strong></td>
                                                <td>{{$campers->waste_water_tank}}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Heizung:</strong></td>
                                                <td>{{$campers->heating}}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Garage in cm:</strong></td>
                                                <td>{{$campers->rear_garage}}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center" colspan="2"><h5><strong>Motorisierung</strong>
                                                    </h5></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Kraftstoffart</strong></td>
                                                <td>{{$campers->fuel_type}}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Leistung in PS/KW</strong></td>
                                                <td>{{$campers->power}}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Getriebe</strong></td>
                                                <td>{{$campers->gearbox}}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Emissionsklasse</strong></td>
                                                <td>{{$campers->emission_class}}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Umweltplakette:</strong></td>
                                                <td>{{$campers->eco_badge}}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center" colspan="2"><h5><strong>Abmessungen</strong>
                                                    </h5></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Fahrzeuglänge in cm</strong></td>
                                                <td>{{$campers->length}}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Fahrzeugbreite in cm</strong></td>
                                                <td>{{$campers->width}}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Fahrzeughöhe in cm</strong></td>
                                                <td>{{$campers->heigth}}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center" colspan="2"><h5><strong>Sonstiges</strong></h5>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Kilometerstand:</strong></td>
                                                <td>{{$campers->mileage}}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Vorbesitzer:</strong></td>
                                                <td>{{$campers->vehicle_owners}}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Max. zul. Gewicht in kg:</strong></td>
                                                <td>{{$campers->total_weight}}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Erstzulassung:</strong></td>
                                                <td>{{$campers->first_registration}}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Hauptuntersuchung:</strong></td>
                                                <td>{{$campers->main_exam}}</td>
                                            </tr>
                                            @if($campers->equipment)
                                                <tr>
                                                    <td class="text-center" colspan="2"><h5>
                                                            <strong>Sonderausstattung</strong></h5></td>
                                                </tr>
                                                <tr>
                                                @foreach($campers->equipment->getAttributes() as $key => $value)
                                                    @if($key !== 'id' && $key !== 'camper_id' && $key !== 'created_at' && $key !== 'updated_at')
                                                        <tr>
                                                            <td><b>{{ __('equipment.' . $key) }}</td>
                                                            <td>{{ ($value ? 'Ja' : 'Nein') }}</td>
                                                        </tr>
                                                        @endif
                                                        @endforeach
                                                        </tr>
                                                    @endif
                                            </tbody>
                                        </table>
                                    </div>
                                    <h5 class="mb-2 mt-2 text-center"><strong>Kaufpreis</strong></h5>
                                    <div class="list-group text-center mb-xl-5">
                                        <ul>
                                            <li><strong>{{$campers->price}} Euro inkl. MwSt.</strong></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <br>
                            <div class="col-xl-12 quote-form-wrapper">
                                <div class="row justify-content-xl-end justify-content-center">
                                    <div class="col-xl-11 pl-30 pr-0">
                                        <div class="quote-form">
                                            <div class="thm-header text-center">
                                                <p class="c1 pb-10">Haben wir Ihr Interesse geweckt?</p>
                                                <h1 class="c3">Fragen Sie uns gerne nach einem Angebot!</h1>
                                            </div><!-- /.thm-header -->
                                            @include('includes.forms.contactform')
                                        </div><!-- /.quote-form -->
                                    </div><!-- /.col-11 -->
                                </div><!-- /.row -->
                            </div><!-- /.col-lg-8 -->

                        </div><!-- /.col-lg-8 -->
                    </article>
                </div><!-- /.row -->
            </div><!-- /.container -->

        </div><!-- /.service -->

@endsection
