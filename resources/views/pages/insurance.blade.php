@extends('layout.app')

@section('title', 'Versicherungen')

@section('content')

<div class="about-page f1">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <header>
                    <figure>
                        <img src="{{ asset('assets/img/insurancebanner.png') }}" alt="About us">
                        <figcaption>
                            <p class="f1 fw-3">Hier finden Sie alle wichtigen Informationen zu unseren Versicherungen.</p>
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
                        <p class="c1 pb-10" style="color: #05223a">Versicherungen</p>
                        <h1 class="c3">Fragen & Antworten</h1>
                    </div><!-- /.thm-header -->
                </div><!-- /.col-12 -->
                <div class="col-xl-6 text-xl-left text-center mb-xl-0 mb-3">
                    <img src="{{ asset('assets/img/insurance.png') }}" alt="Frequently Asked Questions">
                </div><!-- /.col-lg-6 -->
                <div class="col-xl-6">
                    <div class="accordion faq-blocks f1" id="FAQs">
                        <div class="card border-0">
                            <div class="card-header border-0" id="faqh-1">
                                <h2>
                                    <button type="button" data-toggle="collapse" data-target="#faq-1"
                                            aria-expanded="false" aria-controls="faq-1">
                                        Haftpflicht
                                    </button>
                                </h2>
                            </div>

                            <div id="faq-1" class="collapse" aria-labelledby="faqh-1" data-parent="#FAQs">
                                <div class="card-body">
                                    <p>Antwort</p>
                                </div>
                            </div>
                        </div><!-- /.card -->
                        <div class="card border-0">
                            <div class="card-header border-0" id="faqh-2">
                                <h2>
                                    <button type="button" data-toggle="collapse" data-target="#faq-2"
                                            aria-expanded="true" aria-controls="faq-2">
                                        Teilkasko
                                    </button>
                                </h2>
                            </div>

                            <div id="faq-2" class="collapse show" aria-labelledby="faqh-2" data-parent="#FAQs">
                                <div class="card-body">
                                    <p>Antwort</p>
                                </div>
                            </div>
                        </div><!-- /.card -->
                        <div class="card border-0">
                            <div class="card-header border-0" id="faqh-3">
                                <h2>
                                    <button type="button" data-toggle="collapse" data-target="#faq-3"
                                            aria-expanded="false" aria-controls="faq-3">
                                        Vollkasko
                                    </button>
                                </h2>
                            </div>

                            <div id="faq-3" class="collapse" aria-labelledby="faqh-3" data-parent="#FAQs">
                                <div class="card-body">
                                    <p>Antwort</p>
                                </div>
                            </div>
                        </div><!-- /.card -->
                        <div class="card border-0">
                            <div class="card-header border-0" id="faqh-4">
                                <h2>
                                    <button type="button" data-toggle="collapse" data-target="#faq-4"
                                            aria-expanded="false" aria-controls="faq-4">
                                        Schutzbrief
                                    </button>
                                </h2>
                            </div>

                            <div id="faq-4" class="collapse" aria-labelledby="faqh-4" data-parent="#FAQs">
                                <div class="card-body">
                                    <p>Antwort</p>
                                </div>
                            </div>
                        </div>
                        <div class="card border-0">
                            <div class="card-header border-0" id="faqh-5">
                                <h2>
                                    <button type="button" data-toggle="collapse" data-target="#faq-5"
                                            aria-expanded="false" aria-controls="faq-5">
                                        Fahrerschutz-Versicherung
                                    </button>
                                </h2>
                            </div>

                            <div id="faq-5" class="collapse" aria-labelledby="faqh-5" data-parent="#FAQs">
                                <div class="card-body">
                                    <p>Antwort</p>
                                </div>
                            </div>
                        </div>
                        <div class="card border-0">
                            <div class="card-header border-0" id="faqh-6">
                                <h2>
                                    <button type="button" data-toggle="collapse" data-target="#faq-6"
                                            aria-expanded="false" aria-controls="faq-6">
                                        Neuwagengarantie
                                    </button>
                                </h2>
                            </div>

                            <div id="faq-6" class="collapse" aria-labelledby="faqh-6" data-parent="#FAQs">
                                <div class="card-body">
                                    <p>Antwort</p>
                                </div>
                            </div>
                        </div>
                        <div class="card border-0">
                            <div class="card-header border-0" id="faqh-7">
                                <h2>
                                    <button type="button" data-toggle="collapse" data-target="#faq-7"
                                            aria-expanded="false" aria-controls="faq-7">
                                        Urlaub-Schutz-Paket
                                    </button>
                                </h2>
                            </div>

                            <div id="faq-7" class="collapse" aria-labelledby="faqh-7" data-parent="#FAQs">
                                <div class="card-body">
                                    <p>Antwort</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- /.col-lg-6 -->
            </div><!-- /.row -->
        </div><!-- /.container -->
    </div><!-- /.faqs -->
</div><!-- /.about-page -->
@endsection
