@extends('admin.layouts.app')

@section('title', 'Guiding #' . $guiding->id )

@section('content')
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">@yield('title')</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Verwaltung</a></li>
                        <li class="breadcrumb-item"><a href="#">Guidings</a></li>
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
                        <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="title">Name</label>
                                            <input type="text" class="form-control" id="title" name="title" placeholder="Titel" value="" disabled>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="location">Lage</label>
                                            <input type="text" class="form-control" id="location" name="location" placeholder="Lage" disabled value="{{ $guiding->location }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="recommended_for">Empfohlen für</label>
                                    <input type="text" class="form-control" id="recommended_for"  name="recommended_for" placeholder="Empfohlen für" disabled value="{{ $guiding-> recommended_for}}">
                                </div>
                                <div class="form-group">
                                    <label for="max_guests">Maximail Gast anzahl</label>
                                    <input type="number" class="form-control" step="1" id="max_guests"  name="max_guests" placeholder="Maximail Gast anzahl" disabled value="{{ $guiding-> max_guests}}">
                                </div>
                                <div class="form-group">
                                    <label for="duration">Dauer</label>
                                    <input type="number" class="form-control" step="0.01" id="duration"  name="duration" placeholder="Dauer" disabled value="{{ $guiding-> duration}}">
                                </div>
                                <div class="form-group">
                                    <label for="fishing_type">Angeltyp</label>
                                    <input type="text" class="form-control" id="fishing_type"  name="fishing_type" placeholder="Angeltyp" disabled value="{{ $guiding-> fishing_type}}">
                                </div>
                                <div class="form-group">
                                    <label for="fishing_from">Gewässer</label>
                                    <input type="text" class="form-control" id="fishing_from"  name="fishing_from" placeholder="Gewässer" disabled value="{{ $guiding-> fishing_from}}">
                                </div>
                                <div class="form-group">
                                    <label for="description">Beschreibung</label>
                                    <input type="text" class="form-control" id="description"  name="description" placeholder="Beschreibung" disabled value="{{ $guiding-> description}}">
                                </div>
                                <div class="form-group">
                                    <label for="required_equipment">Benötigte Ausrüstung</label>
                                    <input type="text" class="form-control" id="required_equipment"  name="required_equipment" placeholder="Benötigte Ausrüstung" disabled value="{{ $guiding-> required_equipment}}">
                                </div>
                                <div class="form-group">
                                    <label for="provided_equipment">Bereitgestellte Ausrüstung</label>
                                    <input type="text" class="form-control" id="provided_equipment"  name="provided_equipment" placeholder="Bereitgestellte Ausrüstung" disabled value="{{ $guiding-> provided_equipment}}">
                                </div>
                                <div class="form-group">
                                    <label for="additional_information">Zusätzliche Information</label>
                                    <input type="text" class="form-control" id="additional_information"  name="additional_information" placeholder="Zusätzliche Information" disabled value="{{ $guiding-> additional_information}}">
                                </div>
                                <div class="form-group">
                                    <label for="price">Preis pro Person</label>
                                    <input type="number" step="0.01" class="form-control" id="price"  name="price" placeholder="Preis pro Person" disabled value="{{ $guiding-> price}}">
                                </div>
                                <div class="form-group">
                                    <label for="price_two_persons">Preis 2 Person</label>
                                    <input type="number" step="0.01" class="form-control" id="price_two_persons"  name="price_two_persons" placeholder="Preis 2 Person" disabled value="{{ $guiding-> price_two_persons}}">
                                </div>
                                <div class="form-group">
                                    <label for="price_three_persons">Preis 3 Person</label>
                                    <input type="number" step="0.01" class="form-control" id="price_three_persons"  name="price_three_persons" placeholder="Preis 3 Person" disabled value="{{ $guiding-> price_three_persons}}">
                                </div>
                            <div class="table-responsive">
                                <table class="table table-bordered text-nowrap border-bottom" id="responsive-datatable">
                                    <thead>
                                    <tr>
                                        <th class="wd-20p border-bottom-0">Kunde</th>
                                        <th class="wd-15p border-bottom-0">Guide</th>
                                        <th class="wd-15p border-bottom-0">Guiding</th>
                                        <th class="wd-15p border-bottom-0">Preis</th>
                                        <th class="wd-15p border-bottom-0">Anteil Guide</th>
                                        <th class="wd-15p border-bottom-0">Anteil CaG</th>
                                        <th class="wd-15p border-bottom-0">Status</th>
                                        <th class="wd-15p border-bottom-0">Buchungsdatum</th>
                                        <th class="wd-15p border-bottom-0">Guidingsdatum</th>
                                        <th class="wd-15p border-bottom-0">ID Guide</th>
                                        <th class="wd-15p border-bottom-0">ID Guiding</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($guiding->bookings as $booking)
                                        <tr>
                                            <td>{{ $booking->user->full_name }}</td>
                                            <td>{{ $booking->guiding->user->full_name }}</td>
                                            <td>{{ $booking->user->full_name}}</td>
                                            <td>{{ $booking->guiding->price }} €</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td>{{$booking->created_at->format('d.m.Y')}}</td>
                                            <td>{{$booking->blocked_event->created_at->format('d.m.Y')}}</td>
                                            <td>{{$booking->user->id}}</td>
                                            <td>{{$booking->guiding->id}}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>


                        </div>

                    </div>
                </div>
            </div>
            <!-- End Row -->
        </div>
        <!-- CONTAINER CLOSED -->

    </div>
@endsection
