@extends('layout.app')

@section('title', 'Über uns')

@section('content')

    <div class="about-page f1">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <header>
                        <figure>
                            <img src="{{ asset('assets/img/campingplatzhero.JPG') }}" alt="About us">
                            <figcaption>
                                <p class="f1 fw-3">Das im Rhein-Lahn Kreis gelegene Nastätten, beheimatet verkehrsgünstig am Stadtrand unseren Campingplatz. Geographisch befindet er sich im auslaufenden Hochtaunus nicht weit entfernt von der Loreley in der Mittelrhein Region. Das Areal umfasst einhundertzwanzig Stellplätze beginnend mit 100 Quadratmeter je Parzellen und ist auf Saisons- bzw. Dauercamping Belegung ausgerichtet. Alle Parzellen sind mit fließend Wasser, Abwasser sowie Stromanschluss ausgestattet. Die moderne Infrastruktur umfasst neben WC Anlage, inklusive behindertengerechte Kabine, Duschkabinen, Waschmaschine auch Wäschetrockner und Geschirrwaschplätze.
                                    Eine Internetversorgung ist mit WiFi sichergestellt. Die Verkehrswege sind in der Dunkelheit beleuchtet bieten und neben Sicherheit auch Atmosphäre.</p>
                            </figcaption>
                        </figure>
                    </header>
                </div><!-- /.col-12 -->
            </div><!-- /.row -->
        </div><!-- /.container -->


        <div class="skills">
            <div class="container">
                <div class="row">
                    <div class="col-xl-5 order-xl-1 order-2">
                        <div class="skill-area f1 mt-xl-0 mt-3">
                            <p class="fw-4">Ein regelmäßiger Service ist für Sie und Ihren Wohnwagen sehr wichtig. Lassen Sie Ihren Wohnwagen fachgerecht und regelmäßig
                                vom Wohnwagen-Reparatur-Service-Center warten und nehmen Sie sich die Mühe für Ihre nächste Reise.</p>

                            <div class="skill-block">
                                <h3 >Service</h3>
                                <div class="progress">
                                    <span>100%</span>
                                    <div class="progress-bar" data-wow-delay=".2s" role="progressbar" style="width: 100%"
                                         aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" ></div>
                                </div>
                            </div><!-- /.skill-block -->
                            <div class="skill-block">
                                <h3>Erfahrung</h3>
                                <div class="progress">
                                    <span>100%</span>
                                    <div class="progress-bar" data-wow-delay=".2s" role="progressbar" style="width: 100%"
                                         aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div><!-- /.skill-block -->
                            <div class="skill-block">
                                <h3>Kundenzufriedenheit</h3>
                                <div class="progress">
                                    <span>100%</span>
                                    <div class="progress-bar" data-wow-delay=".2s" role="progressbar" style="width: 100%"
                                         aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div><!-- /.skill-block -->
                        </div><!-- /.skill-area -->
                    </div><!-- /.col-lg-5 -->
                    <div class="col-xl-7 order-xl-2 order-1 text-xl-left text-center">
                        <figure>
                            <img src="{{ asset('assets/img/holidays.png') }}" alt="Skills">
                        </figure>
                    </div><!-- /.col-lg-7 -->
                </div><!-- /.row -->
            </div><!-- /.container -->
        </div><!-- /.skills -->
        <div class="towit">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="thm-header text-center" style="color: #05223a !important;">
                            <p class="pb-10" style="color: #05223a !important;">Auf der Suche nach einer Örtlichkeit</p>
                            <h1 class="c3">Bei uns können Sie auch Ihren Urlaub verbringen.</h1>
                        </div><!-- /.thm-header -->
                    </div><!-- /.col-12 -->
                    <div class="pointers f1 col-12">
                        <figure>
                            <img src="{{ asset('assets/img/holidays2.png') }}" alt="Pointers">
                            <figcaption>

                                <div class="dropdown pointer-block">
                                    <button class="pointSingle dropdown-toggle" type="button" id="point1"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></button>
                                    <div class="dropdown-menu bg1 text-white" aria-labelledby="point1" style="background-color:#05223a">
                                        <i class="carevan-icon-car-parts"></i>
                                        <h2>Standort 1</h2>
                                        <p class="fw-6">Standortbeschreibung<br></p>
                                    </div>
                                </div><!-- /.pointer-block -->

                                <div class="dropdown pointer-block">
                                    <button class="pointSingle dropdown-toggle" type="button" id="point2"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></button>
                                    <div class="dropdown-menu bg1 text-white" aria-labelledby="point2" style="background-color:#05223a">
                                        <i class="carevan-icon-car-parts"></i>
                                        <h2>Standort 2</h2>
                                        <p class="fw-6">Standortbeschreibung<br></p>
                                    </div>
                                </div><!-- /.pointer-block -->

                                <div class="dropdown pointer-block">
                                    <button class="pointSingle dropdown-toggle" type="button" id="point3"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></button>
                                    <div class="dropdown-menu bg1 text-white" aria-labelledby="point3" style="background-color:#05223a">
                                        <i class="carevan-icon-car-parts"></i>
                                        <h2>Standort 3</h2>
                                        <p class="fw-6">Standortbeschreibung<br></p>
                                    </div>
                                </div><!-- /.pointer-block -->

                                <div class="dropdown pointer-block">
                                    <button class="pointSingle dropdown-toggle" type="button" id="point4"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></button>
                                    <div class="dropdown-menu bg1 text-white" aria-labelledby="point4" style="background-color:#05223a">
                                        <i class="carevan-icon-car-parts"></i>
                                        <h2>Standort 4</h2>
                                        <p class="fw-6">Standortbeschreibung<br></p>
                                    </div>
                                </div><!-- /.pointer-block -->

                                <div class="dropdown pointer-block">
                                    <button class="pointSingle dropdown-toggle" type="button" id="point5"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></button>
                                    <div class="dropdown-menu bg1 text-white" aria-labelledby="point5" style="background-color:#05223a">
                                        <i class="carevan-icon-car-parts"></i>
                                        <h2>Standort 5</h2>
                                        <p class="fw-6">Standortbeschreibung<br></p>
                                    </div>
                                </div><!-- /.pointer-block -->

                                <div class="dropdown pointer-block">
                                    <button class="pointSingle dropdown-toggle" type="button" id="point6"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></button>
                                    <div class="dropdown-menu bg1 text-white" aria-labelledby="point6" style="background-color:#05223a">
                                        <i class="carevan-icon-car-parts"></i>
                                        <h2>Standort 6</h2>
                                        <p class="fw-6">Standortbeschreibung<br></p>
                                    </div>
                                </div><!-- /.pointer-block -->

                            </figcaption>
                        </figure>
                    </div><!-- /.pointers -->
                </div><!-- /.row -->
            </div><!-- /.container -->
        </div><!-- /.towit -->
    </div><!-- /.about-page -->
@endsection
