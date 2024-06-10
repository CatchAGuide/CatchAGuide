@extends('layout.app')

@section('title', 'Fahrzeuge')

@section('content')
    <div class="service">
        <div class="container">
            <div class="row">
                @foreach($campers as $camper)
                    <div class="col-lg-4 col-md-6">
                        <div class="service-card">
                            <figure>
                                <a href="{{route('camper.show', $camper->id)}}">
                                    @if($camper->images()->count() > 0)
                                        <img src="{{ $camper->getFirstImage() }}" alt="Camper Image">
                                    @else
                                        <img src="{{ asset('/assets/img/service-1-1.jpg') }}" alt="Camper Image">
                                    @endif
                                </a>
                            </figure>
                            <div class="service-card-body">
                                <h2><a href="{{route('camper.show', $camper->id)}}">{{$camper->name}}</a></h2>
                                <p>Hersteller: {{$camper->manufacturer}}<br>Model: {{$camper->model}}<br> geignet für bis zu {{$camper->max_person}} Personen</p>
                                <a href="{{route('camper.show', $camper->id)}}">Mehr erfahren</a>
                            </div><!-- /.service-card-body -->
                        </div><!-- /.service-card -->
                    </div><!-- /.col-lg-4 -->
                @endforeach
            </div><!-- /.row -->
        </div><!-- /.container -->

        <div class="feature-area-2">
            <div class="container">
                <div class="row">
                    <div class="col-lg-3">
                        <ol class="feature-list">
                            <li>Beratung</li>
                            <li>Erfahrung</li>
                            <li>Top Fahrzeuge</li>
                            <li>Zubehör</li>
                            <li>Zufriedenheitsgarantie</li>
                            <li>Auf Wunsch:<br>Empfehlungen für Reiseziele</li>
                        </ol><!-- /.feature-list -->
                    </div><!-- /.col-lg-5 -->
                    <div class="col-lg-9 d-flex justify-content-lg-end justify-content-center flex-lg-row flex-column">
                        <div class="thm-header text-white">
                        </div><!-- /.thm-header -->
                        <figure>
                            <img src="{{ asset('/assets/img/chooseus.png') }}" alt="Feature Banner">
                        </figure>
                    </div><!-- /.col-lg-7 -->
                </div><!-- /.row -->
            </div><!-- /.container -->
        </div><!-- /.feature-area-2 -->

        <div class="repair-service">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="thm-header text-center">
                            <p class="c1 pb-10">Unsere Fahrzeuge</p>
                            <h1 class="c3">Wohnmobile bieten Vorteile</h1>
                        </div><!-- /.thm-header -->
                    </div><!-- /.col-12 -->
                    <div class="col-xl-3 col-md-6 mb-md-4 mb-xl-0">
                        <div class="repair-card">
                                <span class="icon-holder">
                                    <i class="carevan-icon-speedometer"></i>
                                </span>
                            <h2><a href="#">Flexibilität</a></h2>
                            <p>Sie möchten vielleicht nicht an einem Reiseziel bleiben?<br>Kein Problem.</p>
                        </div><!-- /.repair-card -->
                    </div><!-- /.col-xl-3 -->
                    <div class="col-xl-3 col-md-6 mb-md-4 mb-xl-0">
                        <div class="repair-card">
                                <span class="icon-holder">
                                    <i class="carevan-icon-exhaust"></i>
                                </span>
                            <h2><a href="#">Mobilität</a></h2>
                            <p>Sie möchten auch am Reiseziel mobil bleiben?<br>Kein Problem.</p>
                        </div><!-- /.repair-card -->
                    </div><!-- /.col-xl-3 -->
                    <div class="col-xl-3 col-md-6">
                        <div class="repair-card">
                                <span class="icon-holder">
                                    <i class="carevan-icon-car-parts-1"></i>
                                </span>
                            <h2><a href="#">Freiheit</a></h2>
                            <p>Sie möchten spontan losfahren und Rasten, wo sie möchten?<br>Auch das ist kein Problem.</p>
                        </div><!-- /.repair-card -->
                    </div><!-- /.col-xl-3 -->
                    <div class="col-xl-3 col-md-6">
                        <div class="repair-card">
                                <span class="icon-holder">
                                    <i class="carevan-icon-air-filter"></i>
                                </span>
                            <h2><a href="#">Erholung</a></h2>
                            <p>Erholung, Entspannung, Komfort?<br>Absolut kein Problem.</p>
                        </div><!-- /.repair-card -->
                    </div><!-- /.col-xl-3 -->
                </div><!-- /.row -->
            </div><!-- /.container -->
        </div><!-- /.repair-service -->
    </div><!-- /.service -->

@endsection
