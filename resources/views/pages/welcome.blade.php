@extends('layout.app')

@section('title', 'Startseite')

@section('content')

<div id="minimal-bootstrap-carousel" class="carousel slide carousel-fade slider-content-style-one slider-home-one">
    <ol class="carousel-indicators">
        <li data-target="#minimal-bootstrap-carousel" data-slide-to="0" class="active"></li>
        <li data-target="#minimal-bootstrap-carousel" data-slide-to="1"></li>
        <li data-target="#minimal-bootstrap-carousel" data-slide-to="2"></li>
    </ol>

    <!-- Wrapper for slides -->
    <div class="carousel-inner">
        <div class="carousel-item active slide-1" style="background-image: url({{asset('/assets/img/camper_hero.JPG')}});background-position: center left;">
            <div class="carousel-caption">
                <div class="container">
                    <div class="box valign-middle">
                        <div class="content text-center">
                            <p data-animation="animated fadeInUp " class="tag-line animDe-1"><span class="text-uppercase f1 fw-8">Garantiert ein schöner Urlaub</span></p>
                            <h2 data-animation="animated fadeInUp " class="animDe-2">Mit uns werden</h2>
                            <h2 data-animation="animated fadeInUp " class="animDe-3">Ihre Reisen komfortabel.</h2>
                            <a style="background-color: #05223a !important;" href="{{route('camper.index')}}" data-animation="animated fadeInUp " class="bg1 text-white banner-btn animDe-4">Unsere Fahrzeuge</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="carousel-item slide-2" style="background-image: url({{asset('/assets/img/campingplatzhero.JPG')}});background-position: top center;">
            <div class="carousel-caption">
                <div class="container">
                    <div class="box valign-middle">
                        <div class="content text-center">
                            <div data-animation="animated fadeInUp " class="animDe-1 d-flex justify-content-center align-items-center mx-auto icon-box">
                                <i class="carevan-icon-caravan"></i>
                            </div>
                            <h2 data-animation="animated fadeInUp " class="animDe-2">Entspannt ans Ziel kommen,</h2>
                            <h2 data-animation="animated fadeInUp" class=" animDe-3">sicher und bequem reisen.</h2>
                            <a style="background-color: #05223a !important;" href="{{route('camper.index')}}" data-animation="animated fadeInUp " class="animDe-4 bg1 text-white banner-btn">Unsere Fahrzeuge</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="carousel-item slide-3" style="background-image: url({{asset('assets/img/landscape_hero.JPG')}});background-position: top center;">
            <div class="carousel-caption">
                <div class="container">
                    <div class="box valign-middle">
                        <div class="content text-center">
                            <h2 data-animation="animated fadeInUp " class="animDe-1">Top Service</h2>
                            <h2 data-animation="animated fadeInUp " class="animDe-2">für einen unkomplizierten Urlaub.</h2>
                            <p data-animation="animated fadeInUp " class="animDe-3">Wir möchten das Sie eine schöne Zeit haben</p>
                            <p data-animation="animated fadeInUp " class="animDe-3">darum sind wir Ihr Ansprechpartner.</p>
                            <div class="banner-btn-box">
                                <a style="background-color: #05223a !important;" href="{{route('pages.contact')}}" data-animation="animated fadeInUp " class="animDe-4 bg1 text-white banner-btn">Kontakt aufnehmen</a>
                            </div><!-- /.banner-btn-box -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Controls -->
    <a class="carousel-control-prev carousel-control-one-prev" href="#minimal-bootstrap-carousel" role="button" data-slide="prev">
        <i class="fa fa-angle-left"></i>
        <span class="sr-only">Zurück</span>
    </a>
    <a class="carousel-control-next carousel-control-one-next" href="#minimal-bootstrap-carousel" role="button" data-slide="next">
        <i class="fa fa-angle-right"></i>
        <span class="sr-only">Weiter</span>
    </a>
</div>

<div class="intro">
    <div class="container">
        <div class="row">
            <div class="col-12 mb-xl-5 text-xl-left text-center" style="color:#05223a!important">
                <div class="thm-header" style="color:#05223a!important">
                    <p class="c1 pb-10" style="color:#05223a!important">Wir über uns:</p>
                    <h1 class="c3">„Mobiles- und stationäres Camping ist unsere Passion“</h1>
                </div><!-- /.thm-header -->
            </div><!-- /.col-12 -->
            <div class="col-xl-6">
                <figure class="text-xl-left text-center my-xl-0 my-3">
                    <img src="{{ asset('assets/img/booking.png') }}" alt="Intro">
                </figure>
            </div><!-- /.col-xl-6 -->
            <div class="col-xl-6">
                <section class="pt-xl-0 pt-3">
                    <p>Als ein mittelständiges, in der Region Mittelrhein gelegenes Unternehmen bieten wir unseren Kunden neben  Saison- und Dauerstellplätzen auch  Wohnmobile und Vans zur Vermietung, auf unserem Campingplatz zum Mühlbachtal in 56355 Nastätten an.  Die Nähe zur Metropol-Region Rhein/Main, dem Taunus und den Einzugsbereich von Limburg und Koblenz ermöglicht uns einen großen interessierten Kundenkreis anzusprechen.</p>

                    <p>Auch ist die zentrale- und verkehrsgünstige Lage als Startpunkt für Urlaubsziele nach Frankreich, den skandinavischen Ländern und den südlichen Regionen ideal.</p>
                </section>
                <div class="facts text-white text-xl-left text-center" style="background-color:#05223a !important; ">
                    <div class="d-flex justify-content-between flex-sm-row flex-column">
                        <div class="single-fact">
                            <i class="carevan-icon-caravan"></i>
                            <h1 class="counter">99</h1>
                            <p class="f1 fw-6">Verkaufte Wohnmobile</p>
                        </div><!-- /.single-fact -->
                        <div class="single-fact">
                            <i class="carevan-icon-disc-brake"></i>
                            <h1 class="counter">99</h1>
                            <p class="f1 fw-6">Vermietete Wohnmobile</p>
                        </div><!-- /.single-fact -->
                        <div class="single-fact">
                            <i class="carevan-icon-heart"></i>
                            <h1 class="counter">999</h1>
                            <p class="f1 fw-6">Zufriedene Kunden</p>
                        </div><!-- /.single-fact -->
                    </div><!-- /.d-flex -->
                </div><!-- /.facts -->
            </div><!-- /.col-xl-6 -->
        </div><!-- /.row -->
    </div><!-- /.container -->
</div><!-- /.intro -->


<div class="sec-service">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="thm-header text-center"  style="color:#05223a!important">
                    <p class="c1 pb-10" style="color:#05223a!important; ">Ihr Vorteil durch uns</p>
                    <h1 class="c3">Urlaub wo Sie möchten</h1>
                </div><!-- /.thm-header -->
            </div><!-- /.col-12 -->
            <div class="service-carousel owl-carousel">
                <div class="col-lg-4">
                    <div class="service-card">
                        <figure>
                            <a href="{{route('camper.index')}}">
                                <img src="{{asset('assets/img/luggage.png') }}" alt="Service Thumb">
                            </a>
                        </figure>
                        <div class="service-card-body">
                            <h2><a href="{{route('camper.index')}}">Wohin es Sie auch zieht</a></h2>
                            <p>Mit unseren Fahrzeugen kommen Sie immer an Ihr Wunsch-Ziel.</p>
                            <a href="{{route('camper.index')}}" style="color:#05223a!important">Unsere Fahrzeuge</a>
                        </div><!-- /.service-card-body -->
                    </div><!-- /.service-card -->
                </div><!-- /.col-lg-4 -->
                <div class="col-lg-4">
                    <div class="service-card">
                        <figure>
                            <a href="{{route('camper.index')}}">
                                <img src="{{ asset('assets/img/beach.png') }}" alt="Service Thumb">
                            </a>
                        </figure>
                        <div class="service-card-body">
                            <h2><a href="{{route('camper.index')}}">Ob am Strand</a></h2>
                            <p>Ein Familienurlaub zum genießen.</p>
                            <a href="{{route('camper.index')}}" style="color:#05223a!important">Unsere Fahrzeuge</a>
                        </div><!-- /.service-card-body -->
                    </div><!-- /.service-card -->
                </div><!-- /.col-lg-4 -->
                <div class="col-lg-4">
                    <div class="service-card">
                        <figure>
                            <a href="{{route('camper.index')}}">
                                <img src="{{asset('assets/img/hammopcks.png') }}" alt="Service Thumb">
                            </a>
                        </figure>
                        <div class="service-card-body">
                            <h2><a href="{{route('camper.index')}}">Im Grünen</a></h2>
                            <p>Der Natur ganz nah kommen, kein Problem.</p>
                            <a href="{{route('camper.index')}}" style="color:#05223a!important">Unsere Fahrzeuge</a>
                        </div><!-- /.service-card-body -->
                    </div><!-- /.service-card -->
                </div><!-- /.col-lg-4 -->
            </div><!-- /.service-carousel owl-carousel -->
        </div><!-- /.row -->
    </div><!-- /.container -->
</div><!-- /.sec-service -->


<div class="process text-center f1">
    <div class="container">
        <div class="row justify-content-center flex-md-row flex-column no-gutters">
            <div class="col-12">
                <div class="thm-header text-center" style="color:#05223a!important">
                    <p class="c1 pb-10" style="color:#05223a!important">Der Ablauf</p>
                    <h1 class="c3">In nur 3 Schritten zum Wohnmobil.</h1>
                </div><!-- /.thm-header -->
            </div><!-- /.col-12 -->
            <div class="col-lg-4">
                <div class="process-single">
                    <h1 class="f3 c1" style="color:#05223a!important">01</h1>
                    <h2 class="f1 fw-4 c3">Wohnmobil aussuchen</h2>
                    <p class="f1 fw-6">Sie entdecken bei uns Ihr Traum-Wohnmobil und können Ihren nächsten Urlaub kaum erwarten.</p>
                </div><!-- /.process-single -->
            </div><!-- /.col-lg-4 -->
            <div class="col-lg-4">
                <div class="process-single">
                    <h1 class="f3 c1" style="color:#05223a!important">02</h1>
                    <h2 class="f1 fw-4 c3">Sie kontaktieren uns</h2>
                    <p class="f1 fw-6">Sie schreiben uns mit Ihren Fragen und Wünschen, oder rufen uns an, dann besprechen wir alles nötige.</p>
                </div><!-- /.process-single -->
            </div><!-- /.col-lg-4 -->
            <div class="col-lg-4">
                <div class="process-single">
                    <h1 class="f3 c1" style="color:#05223a!important">03</h1>
                    <h2 class="f1 fw-4 c3">Kaufvertrag / Mietvertrag</h2>
                    <p class="f1 fw-6">Glückwunsch! Wenn alle Fragen geklärt sind, sind Sie stolzer Besitzer / Mieter eines unserer Wohnmobile.</p>
                </div><!-- /.process-single -->
            </div><!-- /.col-lg-4 -->
            <div class="col-xl-12 col-lg-8 px-0">
                <p class="block-text">Wir begleiten Sie vom Anfang bis zum Ende.<br>Haben Sie das perfekte Wohnmobil noch nicht gefunden, nehmen Sie Kontakt auf und wir kümmern uns gemeinsam darum.</p>
            </div><!-- /.col-xl-6.col-lg-8 -->
        </div><!-- /.row -->
    </div><!-- /.container -->
</div><!-- /.process -->
</div><!-- /.page-wrapper -->

@endsection
