@extends('admin.layouts.app')

@section('title', 'Guide#' . $guides->id)

@section('content')

    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">@yield('title')</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Show</a></li>
                        <li class="breadcrumb-item"><a href="#">Guide</a></li>
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
                                            <label for="firstname">Vorname</label>
                                            <input type="text" class="form-control" id="firstname" name="firstname" placeholder="Vorname" disabled value="{{ $guides->firstname }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="lastname">Nachname</label>
                                            <input type="text" class="form-control" id="lastname" name="lastname" placeholder="Nachname" disabled value="{{ $guides->lastname }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email Adresse</label>
                                    <input type="email" class="form-control" id="email"  name="email" placeholder="Email Adresse" disabled value="{{ $guides-> email}}">
                                </div>
                            <div class="row row-sm">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered text-nowrap border-bottom" id="responsive-datatable">
                                                    <thead>
                                                    <tr>
                                                        <th class="wd-15p border-bottom-0">ID</th>
                                                        <th class="wd-15p border-bottom-0">Name des Guidings</th>
                                                        <th class="wd-15p border-bottom-0">Region / Bundesland</th>
                                                        <th class="wd-20p border-bottom-0">Empfohlen für</th>
                                                        <th class="wd-15p border-bottom-0">Maximail Gast anzahl</th>
                                                        <th class="wd-10p border-bottom-0">Dauer</th>
                                                        <th class="wd-10p border-bottom-0">Guide ID</th>
                                                        <th class="wd-10p border-bottom-0">Guide Name</th>
                                                        <th class="wd-25p border-bottom-0">Erforderliche Lizenz</th>
                                                        <th class="wd-25p border-bottom-0">Zielfisch</th>
                                                        <th class="wd-25p border-bottom-0">Gewässer</th>
                                                        <th class="wd-25p border-bottom-0">Beschreibung</th>
                                                        <th class="wd-25p border-bottom-0"> Benötigte Ausrüstung</th>
                                                        <th class="wd-25p border-bottom-0">Bereitgestellte Ausrüstung</th>
                                                        <th class="wd-25p border-bottom-0">Zusätzliche Information</th>
                                                        <th class="wd-25p border-bottom-0">Preis pro Person</th>
                                                        <th class="wd-25p border-bottom-0">Preis 2 Personen</th>
                                                        <th class="wd-25p border-bottom-0">Preis 3 Personen</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($guides->guidings as $guiding)
                                                        <tr>
                                                            <td>{{$guiding-> id}}</td>
                                                            <td>{{$guiding -> title}}</td>
                                                            <td>{{$guiding -> location}}</td>
                                                            <td>{{$guiding -> recommended_for}}</td>
                                                            <td>{{$guiding -> max_guests}}</td>
                                                            <td>{{$guiding -> duration}}</td>
                                                            <td>{{$guiding -> user->id}}</td>
                                                            <td>{{$guiding -> user->full_name }}</td>
                                                            <td>{{$guiding -> required_special_license}}</td>
                                                            <td>{{$guiding -> fishing_type}}</td>
                                                            <td>{{$guiding -> fishing_from}}</td>
                                                            <td>{{$guiding -> description}}</td>
                                                            <td>{{$guiding -> required_equipment}}</td>
                                                            <td>{{$guiding -> provided_equipment}}</td>
                                                            <td>{{$guiding -> additional_information}}</td>
                                                            <td>{{$guiding -> price}}</td>
                                                            <td>{{$guiding -> price_two_persons}}</td>
                                                            <td>{{$guiding -> price_three_persons}}</td>

                                                        </tr>
                                                    </tbody>
                                                    @endforeach
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
