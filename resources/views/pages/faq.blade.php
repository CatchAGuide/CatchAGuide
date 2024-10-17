@extends('layout.app')

@section('title', 'FAQ')

@section('content')

<div class="about-page f1">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <header>
                    <figure>
                        <img src="{{ asset('assets/img/faq1.png') }}" alt="About us">
                        <figcaption>
                            <p class="f1 fw-3">Es sind noch Fragen offen, dann finden Sie hier vielleicht schon die passende Antwort.<br>
                                Sollte dies nicht der Fall sein, dann zögern Sie nicht und nehmen mit uns Kontakt auf.</p>
                        </figcaption>
                    </figure>
                </header>
            </div><!-- /.col-12 -->
        </div><!-- /.row -->
    </div><!-- /.container -->
    <div class="faqs">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="thm-header text-center" style="color: #05223a">
                        <p class="c1 pb-10" style="color: #05223a">FAQ</p>
                        <h1 class="c3">Fragen & Antworten</h1>
                    </div><!-- /.thm-header -->
                </div><!-- /.col-12 -->
                <div class="col-xl-6 text-xl-left text-center mb-xl-0 mb-3">
                    <img src="{{ asset('assets/img/faq2.png') }}" alt="Frequently Asked Questions">
                </div><!-- /.col-lg-6 -->
                <div class="col-xl-6">
                    <div class="accordion faq-blocks f1" id="FAQs">
                        <div class="card border-0">
                            <div class="card-header border-0" id="faqh-1">
                                <h2>
                                    <button type="button" data-toggle="collapse" data-target="#faq-1"
                                            aria-expanded="false" aria-controls="faq-1">
                                       Wie erfolgt die Abwicklung der Kaution?
                                    </button>
                                </h2>
                            </div>

                            <div id="faq-1" class="collapse" aria-labelledby="faqh-1" data-parent="#FAQs">
                                <div class="card-body">
                                    <p>Bei Übernahme des Wohnmobils wird die Kaution in Bar hinterlegt.
                                        Die Rückzahlung erfolgt nach Abschuss des  Mietverhältnisses
                                        (Schadensfreiheit  am Fahrzeug und erfolgter Zahlung aller Nebenkosten) in bar.</p>
                                </div>
                            </div>
                        </div><!-- /.card -->
                        <div class="card border-0">
                            <div class="card-header border-0" id="faqh-2">
                                <h2>
                                    <button type="button" data-toggle="collapse" data-target="#faq-2"
                                            aria-expanded="true" aria-controls="faq-2">
                                        Wieviele Kilometer kann ich am Tag fahren?
                                    </button>
                                </h2>
                            </div>

                            <div id="faq-2" class="collapse show" aria-labelledby="faqh-2" data-parent="#FAQs">
                                <div class="card-body">
                                    <p>Eine tägliche Kilometerbegrenzung besteht nicht. Die Kilometerleistung ist auf den gebuch-ten Zeitraum bezogen
                                        und ist Teil des Mietvertrages. Mehrkilometer werden nach Abschluss gem. den Kilometersätzen berechnet.</p>
                                </div>
                            </div>
                        </div><!-- /.card -->
                        <div class="card border-0">
                            <div class="card-header border-0" id="faqh-3">
                                <h2>
                                    <button type="button" data-toggle="collapse" data-target="#faq-3"
                                            aria-expanded="false" aria-controls="faq-3">
                                       Wie bin ich mit meinem Wohnmobil versichert?
                                    </button>
                                </h2>
                            </div>

                            <div id="faq-3" class="collapse" aria-labelledby="faqh-3" data-parent="#FAQs">
                                <div class="card-body">
                                    <p>Das Fahrzeug ist Haftpflicht und Vollkasko versichert mit dem jeweils
                                        angegebenen Selbst-Behalt (Vollkasko). Weitere Details entnehmen Sie der Buchungsplattform des ADAC.  </p>
                                </div>
                            </div>
                        </div><!-- /.card -->
                    </div>
                </div><!-- /.col-lg-6 -->
            </div><!-- /.row -->
        </div><!-- /.container -->
    </div><!-- /.faqs -->
</div><!-- /.about-page -->
@endsection
