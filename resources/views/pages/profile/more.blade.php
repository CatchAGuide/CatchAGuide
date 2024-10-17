@extends('layouts.app')

@section('title', 'Allgemeine Guidelines')
@section('extracss')

@stop

@section('content')


    <section class="page-header">
        <div class="page-header__top">
            <div class="page-header-bg"
                 style="background-image: url({{asset('assets/images/Coverbild_News_Blog_1.2.jpg')}})">
            </div>
            <div class="page-header-bg-overly"></div>
            <div class="container">
                <div class="page-header__top-inner">
                    <h1 class="h2">@yield('title')</h1>
                </div>
            </div>
        </div>
        <div class="page-header__bottom">
            <div class="container">
                <div class="page-header__bottom-inner">
                    <ul class="thm-breadcrumb list-unstyled">
                        <li><a href="{{route('welcome')}}">Home</a></li>
                        <li><span>&#183;</span></li>
                        <li class="active">@yield('title')</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>


    <div class="container mt-4">
        <div class="row">
            <div class="col-xl-10 col-lg-10 col-sm-10 {{$agent->ismobile() ? 'text-center' : ''}}">
                <div class="video-one__left">
                    <span class="section-title__tagline">Was ist ein privater Angel-Guide?</span>
                    <h4 class="section-title__title">Voraussetzung ein privater Guide bei Catch a Guide zu werden:</h4>
                    <hr>
                    <ol class="mb-5 border " style="border: 1px">
                        <li class=" mt-3">Du besitzt einen gültigen Bundesfischereischein & die entsprechende Gewässerkarte.
                        </li>

                        <li>Du achtest auf einen waidgerechten Umgang mit Fischen.</li>

                        <li> Du kennst dich hervorragend in dem von dir angebotenem Gewässer sowie dem Zielfisch
                            deines Guidings aus.
                        </li>

                        <li>Du achtest auf Deine Umwelt und hinterlässt den Angelplatz sauber.</li>
                        <li>Du pflegst einen offenen Umgang mit anderen Anglern.</li>
                        <li>Du bist freundlich & hilfsbereit.</li>
                        <li>Du bist zuverlässig & respektvoll.</li>
                        <li>Du fühlst dich sicher im Umgang mit diversen Angeltechniken.</li>
                        <li>Du bist fähig und hast Spaß daran, Dein angesammeltes Wissen verständlich
                            weiterzugeben.
                        </li>
                        <li class="mb-3">Du fängst regelmäßig Deine Fische.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="container mt-4">
        <div class="row">
            <div class="col-xl-12 col-lg-12 col-sm-12 {{$agent->ismobile() ? 'text-center' : ''}}">
                <div class="video-one__left">
                    <h4 class="section-title__title">Anregungen für ein gelungenes Guiding und ein schönes Erlebnis für Euren Gast: </h4>
                    <ul class="mb-5">
                        <li><b>Absprache mit Deinem Gast:</b> Nutze die Chatfunktion, um mit Deinem Gast in
                            Kontakt treten zu können. Hier könnt Ihr zum Beispiel einen Treffpunkt oder eine
                            genaue Uhrzeit ausmachen, sowie weitere Details des Angeltags besprechen. Dies beugt
                            Missverständnissen von Beginn an vor und erlaubt eine individuelle Vorbereitung des
                            Guidings.
                        </li>
                        <li><b>Verpflegung:</b> Du hast Lust, Deinem Gast ein kleines Extra zu bieten? Warum
                            nicht eine kleine Lunchbox für Dich und Deinen Gast vorbereiten? Plane
                            beispielsweise ein Brötchen, einen kleinen Snack, Obst oder ein Getränk für alle
                            Teilnehmer ein und schaffe ein einmaliges Erlebnis.
                        </li>
                        <li><b>Fotos:</b> Du als Angler weißt am besten, wie wichtig Fotos zur Erinnerung eines
                            tollen Angeltages sind. Führe im besten Falle immer eine Kamera oder Smartphone mit,
                            um geschossene Bilder mit Deinen Gästen zu teilen oder auf Deinem Profil zu
                            veröffentlichen und es ansprechend gestalten zu können.
                        </li>
                        <li><b>Durchführung Deines Guidings:</b> Versuch Deinen Gast stets zufrieden zu stellen.
                            Sei zuvorkommend und geh proaktiv auf Ihn zu, wenn Schwierigkeiten bei einer Montage
                            oder ähnlichem auftreten. So kannst Du sicherstellen, dass der Gast Dich positiv in
                            Erinnerung behält, eine gute Bewertung und Wertschätzung hinterlässt und wohlmöglich
                            erneut ein Guiding bei Dir bucht.
                        </li>

                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="container mt-4">
        <div class="row">
            <div class="col-xl-12 col-lg-12 col-sm-12 {{$agent->ismobile() ? 'text-center' : ''}}">
                <div class="video-one__left">
                    <h4 class="section-title__title">Wie gelange ich als Guide an Teilnehmer: </h4>
                    <ul class="mb-5">
                        <li>Unsere Plattform bietet die aller erste gesammelte Übersicht aller Privat-Guidings,
                            auf welcher jeder Angler ein Angebot entsprechend seines Geschmacks finden kann. Sei
                            auch Du Teil davon und biete eine einzigartige Tour an, die von deinen individuellen
                            Kenntnissen als auch Charaktereigenschaften geprägt ist.
                        </li>
                        <li>Gestalte Dein Profil so attraktiv wie möglich. Hast Du schöne Fangbilder, die Du zum
                            Besten geben möchtet? Wählt sowohl ein Profilbild, auf dem Ihr gut zu erkennen seid,
                            als auch andere großartige Bilder in Deiner persönlichen Galerie.
                        </li>
                        <li>Wähle einen ansprechenden Titel und ein Bild um das von Dir angebotene Guiding
                            bestens dar zu stellen. Jetzt bist du bereit und kannst Deine Touren mit Freunden
                            oder anderen Anglern aus Deiner Community teilen. Verlinke Deine Guidings/ Dein
                            Profil auf Instagram, Facebook oder ähnlichen Sozialen Foren, um Dein Netzwerk auf
                            Dich aufmerksam zu machen.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="container mt-4">
        <div class="row">
            <div class="col-xl-12 col-lg-12 col-sm-12 {{$agent->ismobile() ? 'text-center' : ''}}">
                <div class="video-one__left">
                    <h4 class="section-title__title">Welche Ausrüstung benötige Ich?</h4>
                    <ul class="mb-5">
                        <li>Nutze Deine Grundausrüstung während der geplanten Tour für Dich selbst.
                        </li>
                        <li>Du hast zusätzliche Gerätschaften, welche Du für das Guiding verleihen kannst? Kein
                            Problem! Lass es Deine Gäste wissen und erwähne es in Deinem Angebot.
                        </li>
                        <li>Führe eine Ersatzausrüstung für Dich mit, um einen Abbruch des Guidings durch
                            Unfälle wie z.B. einen Rutenbruch vorzubeugen.
                        </li>
                        <li>Du möchtest einen ganz besonderen Köder/ Montage fischen? Passe den Preis Deines
                            Guidings an und bringe Deinen Gästen spezielles Tackle direkt mit ans Wasser.
                        </li>
                        <li>Wenn Du Deine Gäste mit auf ein Boot nehmen möchtest, kannst Du dies natürlich sehr
                            gerne machen. Achte hierbei in jedem Fall auf die Sicherheit aller Beteiligten
                            (Schwimmwesten, ausreichen Platz, etc.).
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="container mt-4">
        <div class="row">
            <div class="col-xl-12 col-lg-12 col-sm-12 {{$agent->ismobile() ? 'text-center' : ''}}">
                <div class="video-one__left">
                    <h4 class="section-title__title">Rechtliche Grundlagen:</h4>
                    <ul class="mb-5">
                        <li>Besitz eines gültigen Bundesfischereischeins ist vorgeschrieben.
                        </li>
                        <li>Besitz einer gültigen Gewässerkarte (falls notwendig) ist ebenfalls von Nöten.
                        </li>
                        <li>Lasse Deine Gäste wissen, welche Gewässerkarte für Dein Guiding benötigt wird,
                            sodass sich alle Teilnehmer im Voraus vorbereiten können.
                        </li>
                        <li>Mögliche Sonderlizenzen zum Guiden benötigt? Bitte sprich Dich mit dem verantwortlichen Angelverein ab,
                            wenn Dein Guiding an privaten Gewässern stattfindet.
                        </li>
                        <li>Achte auf Schonzeiten, Mindestmaße und Naturschutz.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <section class="book-now">
        <div class="book-now-shape" style="background-image: url({{ asset('assets/images/shapes/book-now-shape.png') }})"></div>
        <div class="container">
            <div class="row">
                <div class="col-xl-12">
                    <div class="book-now__inner">
                        <div class="book-now__left">
                            <p>Jetzt Guide werden</p>
                            <h2>Du möchtest auch dabei sein?</h2>
                        </div>
                        <div class="book-now__right">
                            <a href="{{ route('profile.index') }}" class="thm-btn book-now__btn">Guide werden</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
