@extends('layouts.app')

@section('title', ucwords(translate('Allgemeine Geschäftsbedingungen')))

@section('content')
    <!--Page Header Start-->
    <section class="page-header">
        <div class="page-header__top">
            <div class="page-header-bg"
                 style="background-image: url({{asset('assets/images/Coverbild_News_Blog_1.2.jpg')}})">
            </div>
            <div class="page-header-bg-overly"></div>
            <div class="container">
                <div class="page-header__top-inner">
                    <h2>@lang('message.term-conditions')</h2>
                </div>
            </div>
        </div>
        <div class="page-header__bottom">
            <div class="container">
                <div class="page-header__bottom-inner">
                    <ul class="thm-breadcrumb list-unstyled">
                        <li><a href="{{ route('welcome') }}">@lang('message.home')</a></li>
                        <li><span>&#183;</span></li>
                        <li class="active">@lang('message.term-conditions')</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    <!--Page Header End-->

    <!--About Page Start-->
    <section class="about-page">
        <div class="container">
            <div class="row">
                @if(app()->getLocale() == 'de')
                <h3>ALLGEMEINE GESCHÄFTSBEDINGUNGEN</h3>
                <ul>
                    <li>für den Bezug oder Absatz von Waren oder Dienstleistungen auf der „Catch A Guide“-Plattform</li>
                </ul>
                <br> <br>
                <h4>INHALT</h4>
                <ol>
                    <li><b>BEZUG </b>VON WAREN UND/ODER DIENSTLEISTUNGEN AUF DER CATCH A GUIDE-PLATTFORM</li>
                    <li><b>ABSATZ </b>VON WAREN UND/ODER DIENSTLEISTUNGEN AUF DER CATCH A GUIDE--PLATTFORM</li>
                </ol>
                <br><br><br>
                <h4>1. BEZUG <b>VON WAREN UND/ODER DIENSTLEISTUNGEN AUF DER CATCH A GUIDE-PLATTFORM</b></h4>
                <ul>
                    <li><b>Betreiber und Anwendungsbereich</b></li>
                </ul>
                <ol>
                    <li>Dieser Abschnitt A. der Allgemeinen Geschäftsbedingungen (nachstehend „<b>AGB</b>“) gilt für die
                        Nutzung der Website <a href="www.catchaguide.com">www.catchaguide.com</a> oder der Catch A
                        Guide-App (Website und App werden
                        nachstehend gesammelt als „<b>Plattform</b>“ bezeichnet) für den Bezug, also die
                        Online-Bestellung, von
                        Waren und/oder Dienstleistungen auf der Plattform. Die Plattform wird von der Catch A Guide UG
                        (haftungsbeschränkt), Von-Gahlen-Straße 31, 40625 Düsseldorf betrieben (nachstehend als
                        „<b>wir</b>“
                        oder „<b>uns</b>“ bezeichnet).
                    </li>
                </ol>
                <br>
                <ul>
                    <li><b>Vertragsbeziehungen auf der Plattform</b></li>
                </ul>
                <ol>
                    <li>Auf der Plattform können Nutzer Waren und/oder Dienstleistungen dritter Anbieter bestellen, die
                        von diesen Anbietern (nachstehend “<b>Anbieter</b>“) auf der Plattform beworben werden. Den
                        Vertrag
                        über eine jeweilige Ware oder Dienstleistung schließt der Nutzer ggf. nicht mit uns, sondern mit
                        dem betreffenden Anbieter ab. Für diesen Vertrag gelten ggf. die AGB des jeweiligen Anbieters,
                        sofern sie wirksam in den Vertrag einbezogen werden.
                    </li>
                </ol>
                <br>
                <ul>
                    <li><b>Spenden</b></li>
                </ul>
                <ol>
                    <li>Über die Plattform ist es auch möglich, Spenden zu leisten. Angebote zur Entgegennahme von
                        Spenden auf der Plattform gelten für Zwecke dieser AGB als „Dienstleistungen“. Der Anbieter
                        eines Angebots zur Entgegenname von Spenden auf der Plattform gilt für Zwecke dieser AGB als
                        „Anbieter“.
                    </li>
                </ol>
                <br>
                <ul>
                    <li><b>Abschluss des Vertrages mit uns über die Nutzung der Plattform</b></li>
                </ul>
                <ol>
                    <li>Die Bereitstellung der Plattform stellt noch kein verbindliches Angebot zum Abschluss eines
                        entsprechenden Nutzungsvertrages zwischen dem Nutzer und uns dar. Ein verbindliches Angebot
                        erfolgt vielmehr erst dadurch, dass der Nutzer sein Registrierungsgesuch über die Plattform an
                        uns übermittelt. Dieses Angebot nehmen wir ggf. dadurch an, dass wir die Registrierung des
                        Nutzers durch eine Registrierungsbestätigung per E-Mail bestätigen.
                    </li>
                    <li>Wir speichern die Vertragsbestimmungen, also die Registrierungsdaten und die vorliegenden AGB.
                        Sie können die Vertragsbestimmungen Ihrerseits ausdrucken oder speichern, indem Sie jeweils die
                        übliche Funktionalität Ihres Browsers nutzen (dort meist „Drucken“ bzw. “Datei” > “Speichern
                        unter”). Die Registrierungsdaten sind in der Übersicht enthalten, die im letzten Schritt der
                        Registrierung angezeigt wird.
                    </li>
                    <li>Vertragssprache ist Deutsch.</li>
                </ol>
                <br>
                <ul>
                    <li><b>Abschluss des Vertrages zwischen Nutzer und Anbieter</b></li>
                </ul>
                <ol>
                    <li>Die Präsentation der Waren bzw. Dienstleistungen auf der Plattform beinhaltet ein Angebot des
                        jeweiligen Anbieters auf Abschluss eines entsprechenden Vertrages. Indem der Nutzer eine
                        Bestellung über eine jeweilige Ware oder Dienstleistung absendet, nimmt der Nutzer dieses
                        Angebot an.
                    </li>
                    <li>Die Vertragsdaten einschließlich auf der Plattform eingebundener Allgemeiner
                        Geschäftsbedingungen des Anbieters, sofern vorhanden, werden auf der Plattform gespeichert. Sie
                        können die Vertragsdaten einschließlich der bezeichneten Allgemeinen Geschäftsbedingungen des
                        Anbieters ausdrucken oder speichern, indem Sie die übliche Funktionalität Ihres Endgeräts bzw.
                        Browsers nutzen (dort meist „Drucken“ bzw. “Datei” > “Speichern unter”).
                    </li>
                    <li>Vertragssprache ist Deutsch.</li>
                </ol>
                <br>
                <ul>
                    <li><b>Nutzerkonto</b></li>
                </ul>
                <ol>
                    <li>Bei der Registrierung des Nutzerkontos sind richtige und vollständige Angaben zu machen. Daten
                        Dritter dürfen ohne deren Einwilligung nicht verwendet werden.
                    </li>
                    <li>Sie sind verpflichtet, Ihre Zugangsdaten wie bspw. Ihr Passwort vertraulich zu behandeln,
                        Dritten nicht zugänglich zu machen, und uns im Falle des Verlusts oder einer unbefugten Nutzung
                        Ihrer Zugangsdaten unverzüglich zu unterrichten.
                    </li>
                </ol>
                <br>
                <ul>
                    <li><b>Zahlung und Rechnungsstellung</b></li>
                </ul>
                <ol>
                    <li>Die Zahlung für den Bezug der Waren oder Dienstleistungen von Anbietern leistet der Nutzer über
                        den auf der Plattform vorgesehenen Zahlungsdienst direkt an den Anbieter.
                    </li>
                    <li>Bei Bezug der auf der Plattform angebotenen Waren oder Dienstleistungen zahlt uns der Nutzer
                        eine Bearbeitungsgebühr in Höhe von 10€. Diese Gebühr ist automatisch im Gesamtpreis der Waren
                        oder Dienstleistungen wie auf der Plattform angegeben enthalten.
                    </li>
                    <li>Die betreffende Rechnung erhält der Nutzer ggf. ebenfalls direkt vom Anbieter.</li>
                </ol>
                <br>
                <ul>
                    <li><b>Gewährleistung der Anbieter; besonderes Rücktrittsrecht</b></li>
                </ul>
                <ol>
                    <li> Bei Bestellungen auf der Plattform über eine termingebundene Dienstleistung eines Anbieters,
                        räumen wir Ihnen namens und im Auftrag des Anbieters das Recht ein, bis 24 Stunden vor Beginn
                        des vereinbarten Termins mit einer Gebühr (Kosten für die Transaktion) vom Vertrag
                        zurückzutreten. Dieses besondere Rücktrittsrecht hilft Ihnen in solchen Fällen, in denen ein
                        gesetzliches Verbraucher-Widerrufsrecht ausnahmsweise nicht besteht. Treten Sie binnen weniger
                        als 24 Stunden vom Vertrag zurück, so fallen Gebühren an. Die Gebühren teilen sich wie folgt
                        auf:
                    </li>
                    <br>
                    <h5 class="mb-1"><b>Mehr als 24 Stunden vor dem Termin</b></h5>
                    <table class="table table-bordered text-nowrap border-bottom" id="responsive-datatable">

                        <tr>
                            <th>Catch A Guide</th>
                            <th>Anbieter</th>
                            <th>Zahlungsanbieter</th>
                            <th>Bei Zahlungsart Kreditkarte</th>
                            <th>Bei anderen Zahlungsarten</th>
                        </tr>
                        <tbody>
                        <tr>
                            <td>0%</td>
                            <td>0%</td>
                            <td>&nbsp;</td>
                            <td>1,00€</td>
                            <td>0,20€</td>
                        </tr>
                        </tbody>
                    </table>
                    <br><br>
                    <h5 class="mb-1"><b>Weniger als 24 Stunden vor dem Termin</b></h5>
                    <table class="table table-bordered text-nowrap border-bottom" id="responsive-datatable">

                        <tr>
                            <th>Catch A Guide</th>
                            <th>Anbieter</th>
                            <th>Zahlungsanbieter</th>
                            <th>Bei Zahlungsart Kreditkarte</th>
                            <th>Bei anderen Zahlungsarten</th>
                        </tr>
                        <tbody>
                        <tr>
                            <td>100% der gezahlten Gebühr an uns (10€).</td>
                            <td>20%</td>
                            <td>&nbsp;</td>
                            <td>1,00€</td>
                            <td>0,20€</td>
                        </tr>
                        </tbody>
                    </table>
                    <br><br>
                    <li>Wir informieren darüber, dass im Übrigen die Gewährleistungsverpflichtungen der Anbieter sich
                        jeweils nach den gesetzlichen Bestimmungen richten, soweit in wirksam einbezogenen AGB des
                        jeweiligen Anbieters nicht zulässiger Weise etwas Abweichendes geregelt ist.
                    </li>
                </ol>
                <br>
                <ul>
                    <li><b>Anforderungen an Nutzerbeiträge</b></li>
                </ul>
                <ol>
                    <li>Es dürfen nur rechtmäßige Nutzerbeiträge (Mitteilungen, Bewertungen o.ä.) auf oder über die
                        Plattform kommuniziert werden. Insbesondere dürfen die Nutzerbeiträge und/oder deren Einstellung
                        auf der Plattform keine Rechte Dritter (z.B. Namens-, Kennzeichen-, Urheber-, Datenschutz-,
                        Persönlichkeitsrechte usw.) verletzen. Der Nutzer sichert uns zu, dass er über die
                        erforderlichen Rechte für die Einstellung seiner Nutzerbeiträge auf der Plattform frei verfügen
                        kann und Rechte Dritter nicht entgegenstehen.
                    </li>
                    <li>Die Nutzerbeiträge, ob in Bild oder Text, dürfen keine Gewaltdarstellungen beinhalten und nicht
                        sexuell anstößig sein. Sie dürfen keine diskriminierenden, beleidigenden, rassistischen,
                        verleumderischen oder sonst rechts- oder sittenwidrigen Aussagen oder Darstellungen beinhalten
                    </li>
                    <li>Bewertungen, die zu Anbietern abgegeben werden, dürfen keine unzutreffenden
                        Tatsachenbehauptungen oder Schmähkritik enthalten und nicht gegen Persönlichkeitsrechte
                        verstoßen.
                    </li>
                </ol>
                <br>
                <ul>
                    <li><b>Sperrung von Nutzerbeiträgen</b></li>
                </ul>
                <ol>
                    <li>Werden für eine Anzeigenschaltung keine entgeltlichen Dienste der Plattform (wie bspw.
                        kostenpflichtige Sonderplatzierungen) genutzt, so sind wir berechtigt, die Anzeige jederzeit zu
                        sperren und/ oder zu löschen.
                    </li>
                </ol>
                <br>
                <ul>
                    <li><b>Kündigung</b></li>
                </ul>
                <ol>
                    <li>Jede Partei ist berechtigt, den Vertrag zur Nutzung der Plattform jederzeit zu kündigen.
                    </li>
                </ol>
                <br>
                <ul>
                    <li><b>Haftungsausschlüsse und -beschränkungen</b></li>
                    <li>Für eine Haftung von uns auf Schadensersatz gilt</li>
                </ul>
                <ol>
                    <li>Bei Vorsatz und grober Fahrlässigkeit, auch unserer Erfüllungsgehilfen, haften wir nach den
                        gesetzlichen Bestimmungen. Das gleiche gilt bei fahrlässig verursachten Schäden aus der
                        Verletzung des Lebens, des Körpers oder der Gesundheit.
                    </li>
                    <li>Bei fahrlässig verursachten Sach- und Vermögensschäden haften wir nur bei der Verletzung einer
                        wesentlichen Vertragspflicht, jedoch der Höhe nach beschränkt auf die bei Vertragsschluss
                        vorhersehbaren und vertragstypischen Schäden; wesentliche Vertragspflichten sind solche, deren
                        Erfüllung die ordnungsgemäße Durchführung des Vertrags überhaupt erst ermöglicht und auf deren
                        Einhaltung der Vertragspartner regelmäßig vertrauen darf.
                    </li>
                    <li>Im Übrigen ist eine Haftung von uns, unabhängig von deren Rechtsgrund, ausgeschlossen.</li>
                    <li>Die Haftungsausschlüsse und -beschränkungen der vorstehenden Absätze (1) bis (3) gelten
                        sinngemäß auch zugunsten unserer Erfüllungsgehilfen.
                    </li>
                    <li>Eine Haftung wegen Übernahme einer Garantie oder nach dem Produkthaftungsgesetz bleibt von den
                        Haftungsausschlüssen und -beschränkungen der vorstehenden Absätze (1) bis (4) unberühr
                    </li>
                </ol>
                <br>
                <ul>
                    <li><b>Anwendbares Recht, Gerichtsstand</b></li>
                </ul>
                <ol>
                    <li>Es gilt deutsches Recht. Gegenüber einem Verbraucher gilt diese Rechtswahl nur insoweit, als
                        dadurch keine zwingend anwendbaren gesetzlichen Bestimmungen des Staates, in dem er seinen
                        Wohnsitz oder gewöhnlichen Aufenthalt hat, eingeschränkt werden.
                    </li>
                    <li>Gerichtsstand im Verkehr mit Kaufleuten, juristischen Personen des öffentlichen Rechts oder
                        öffentlich-rechtlichen Sondervermögen ist der Sitz unseres Unternehmens. Wir sind jedoch nach
                        unserer Wahl berechtigt, am Sitz des Kunden zu klagen.
                    </li>
                </ol>
                <br><br><br>
                <h4 class="mt-5">2. ABSATZ VON WAREN UND/ODER DIENSTLEISTUNGEN AUF DER CATCH A GUIDE-PLATTFORM</h4>
                <ul>
                    <li><b>Betreiber und Anwendungsbereich</b></li>
                </ul>
                <ol>
                    <li>>Dieser Abschnitt A. der Allgemeinen Geschäftsbedingungen (nachstehend „<b>AGB</b>“) gilt für
                        die
                        Nutzung der Website <a href="www.catchaguide.com">www.catchaguide.com</a> oder der Catch A
                        Guide-App (Website und App werden
                        nachstehend gesammelt als „<b>Plattform</b>“ bezeichnet) für den Bezug, also die
                        Online-Bestellung, von
                        Waren und/oder Dienstleistungen auf der Plattform. Die Plattform wird von der Catch A Guide UG
                        (haftungsbeschränkt), Von-Gahlen-Straße 31, 40625 Düsseldorf betrieben (nachstehend als
                        „<b>wir</b>“
                        oder „<b>uns</b>“ bezeichnet).
                    </li>
                </ol>
                <br>
                <ul>
                    <li><b>Vertragsbeziehungen auf der Plattform</b></li>
                </ul>
                <ol>
                    <li>Auf der Plattform können Nutzer Waren und/oder Dienstleistungen von Anbietern bestellen, die von
                        diesen Anbietern (nachstehend “<b>Anbieter</b>” oder „<b>Sie</b>“ bzw. „<b>Ihnen</b>“
                        bezeichnet) auf der Plattform
                        beworben werden. Den Vertrag über eine jeweilige Ware oder Dienstleistung schließt der Nutzer
                        ggf. nicht mit uns, sondern mit dem betreffenden Anbieter ab. Für diesen Vertrag gelten ggf. die
                        AGB des jeweiligen Anbieters, sofern sie wirksam in den Vertrag einbezogen werden. Die Anbieter
                        können ihre AGB auf der Plattform bereitstellen.
                    </li>
                </ol>
                <br>
                <ul>
                    <li><b>Abschluss des Vertrages mit uns über die Nutzung der Plattform</b></li>
                </ul>
                <ol>
                    <li>Die Bereitstellung der Plattform stellt noch kein verbindliches Angebot zum Abschluss eines
                        entsprechenden Nutzungsvertrages zwischen dem Nutzer und uns dar. Ein verbindliches Angebot
                        erfolgt vielmehr erst dadurch, dass der Nutzer sein Registrierungsgesuch über die Plattform an
                        uns übermittelt. Dieses Angebot nehmen wir ggf. dadurch an, dass wir die Registrierung des
                        Nutzers durch eine Registrierungsbestätigung per E-Mail bestätigen.
                    </li>
                    <li>Wir behalten uns vor, im Zuge der Registrierung Unterlagen zur Verifikation von Identität und
                        Gewerbebetrieb des Anbieters als Voraussetzung für die Registrierung anzufordern, bspw. eine
                        Kopie des Personalausweises eines Einzelunternehmers oder Geschäftsführers, eine
                        Gewerbeanmeldung, eine Umsatzsteuernummer, ggf. eine Handwerkskarte und/oder ggf. einen
                        Gesellschaftervertrag (bei juristischen Personen).
                    </li>
                    <li>ir speichern die Vertragsbestimmungen, also die Registrierungsdaten und die vorliegenden AGB.
                        Sie können die Vertragsbestimmungen Ihrerseits ausdrucken oder speichern, indem Sie jeweils die
                        übliche Funktionalität Ihres Browsers nutzen (dort meist „Drucken“ bzw. “Datei” > “Speichern
                        unter”). Die Registrierungsdaten sind in der Übersicht enthalten, die im letzten Schritt der
                        Registrierung angezeigt wird.
                    </li>
                    <li>Vertragssprache ist Deutsch.</li>
                </ol>
                <br>
                <ul>
                    <li><b>Nutzung der Plattform als Anbieter</b></li>
                </ul>
                <ol>
                    <li>Anbieter können die Präsentation ihrer Waren und/oder Dienstleistungen in einem jeweils selbst
                        administrierten Bereich gestalten
                    </li>
                </ol>
                <br>
                <ul>
                    <li><b>Einräumung eines besonderen Rücktrittsrechts an die Nutzer</b></li>
                </ul>
                <ol>
                    <li>Beim Abschluss von Verträgen zwischen Bestellern und dem Anbieter auf der Plattform über eine
                        termingebundene Dienstleistung oder eine Warenbestellung, für die eine Abholung vor Ort zu einem
                        bestimmten Termin (Zeitpunkt oder Zeitraum) vereinbart ist, ermächtigt der Anbieter uns, dem
                        Besteller namens und im Auftrag des Anbieters das Recht einzuräumen, bis 24 Stunden vor Beginn
                        des vereinbarten Termins kostenfrei vom Vertrag mit dem Anbieter zurückzutreten. Dieses
                        besondere Rücktrittsrecht ist für den Nutzer in denjenigen Fällen relevant, in denen ein
                        gesetzliches Verbraucher-Widerrufsrecht ausnahmsweise nicht besteht.
                    </li>
                </ol>
                <br>
                <ul>
                    <li><b>Vergütung</b></li>
                </ul>
                <ol>
                    <li>Bei Bezahlung durch den Nutzer bei einem Verkauf auf der Plattform berechnen wir dem Anbieter
                        außerdem eine Service-Gebühr. Die Höhe der Service-Gebühr beträgt 10% der bezahlten Summe. Die
                        Service-Gebühr wird bei der Auszahlung der Einnahmen des Anbieters an den Anbieter automatisch
                        eingezogen. Zuzüglich fallen Gebühren für verschiedene Zahlungsmethoden an, welche ebenfalls
                        automatisch eingezogen werden. Die Erträge der Anbieter können in einem Abstand von zwei Wochen
                        oder einem Monat ausgezahlt werden. Zuzüglich zu den Service-Gebühren von Catch A Guide fällt
                        eine Service-Gebühr des Zahlungsanbieters Securpay an. Diese ist abhängig von der jeweiligen
                        Zahlungsmethode. Die Auflistung der jeweiligen Service-Gebühren der einzelnen Zahlungsmethoden
                        finden sie hier:
                    </li>
                </ol>
                <table class="table table-bordered text-nowrap border-bottom mt-3" id="responsive-datatable">

                    <tr>
                        <th>Transaktionsgebühren <br>
                            Je Transaktion, unabhängig <br>der gewählten Zahlungsart
                        </th>
                        <th>Disagio für den <br>Zahlungstransfer mit der <br>Zahlart Kreditkarte</th>
                        <th>Disagio für den <br>Zahlungstransfer mit der <br>Zahlart Lastschrift</th>
                        <th>Disagio für den <br>Zahlungstransfer mit der <br>Zahlart Vorkasse</th>
                        <th>Disagio für den <br>Zahlungstransfer mit der <br>Zahlart Sofortüberweisung</th>
                    </tr>
                    <tbody>
                    <tr>
                        <td>0,20 EUR</td>
                        <td>1,50%</td>
                        <td>1,20%</td>
                        <td>0,70%</td>
                        <td>1,00%</td>
                    </tr>
                    </tbody>
                </table>
                <p>Der Anbieter erhält auf einer monatlichen Basis eine Rechnung mit seinen Erträgen und den
                    angefallenen Service-Gebühren.</p>
                <br>
                <ul>
                    <li><b>Präsentation der Waren und/ oder Dienstleistungen auf der Plattform</b></li>
                </ul>
                <ol>
                    <li>Einhaltung geltender Gesetze: Sie haben dafür Sorge zu tragen, dass Ihre Produktpräsentation mit
                        einschlägigen gesetzlichen Bestimmungen im Einklang steht. Dazu zählen für Unternehmer unter
                        anderem: die Informationspflichten für den elektronischen Geschäftsverkehr (Vertragsschluss,
                        Speicherung des Vertragstextes etc.), die Informationspflichten beim Fernabsatz an Verbraucher
                        (Widerrufsbelehrung bzw. ggf. Information über den Ausschluss des Widerrufsrechts,
                        Versandkostenangabe, Lieferzeitangabe, Bestehen eines gesetzlichen Mängelhaftungsrechts etc.),
                        der Hinweis auf die Plattform der EU-Kommission zur Online-Streitbeilegung unter Angabe eines
                        klickbaren Links dorthin (https://www.ec.europa.eu/consumers/odr), die Vorgaben der
                        Preisangabenverordnung, das Verbot der unlauteren, irreführenden oder sonst wettbewerbswidrigen
                        Werbung nach UWG oder produktspezifische Kennzeichnungspflichten (soweit sie auch für
                        Werbung/Verkaufsangebote im Internet gelten).
                    </li>
                    <li>Kein Verstoß der Produktpräsentationen gegen Rechte Dritter: Sie haben dafür Sorge zu tragen,
                        dass Ihre Produktpräsentation keine gewerblichen Schutzrechte Dritter oder Rechte Dritter an
                        geistigem Eigentum verletzen wie bspw. Patentrechte, Urheberrechte, Namensrechte oder
                        Kennzeichenrechte (Marken, Designs) und dass sie nicht gegen Datenschutzrecht oder
                        Persönlichkeitsrechte Dritter oder sonstige Rechte Dritter verstößt.
                    </li>
                </ol>
                <br>
                <ul>
                    <li><b>Sperrung von Produktpräsentationen</b></li>
                </ul>
                <ol>
                    <li>Es ist uns gestattet, Produktpräsentationen sofort zu sperren, wenn Anhaltspunkte dafür
                        vorliegen, dass diese rechtswidrig sind oder Rechte Dritter verletzen. Als Anhaltspunkt für eine
                        Rechtswidrigkeit oder Rechtsverletzung ist es für diese Zwecke unter anderem anzusehen, wenn
                        Dritte Maßnahmen, gleich welcher Art, gegen uns oder gegen Sie ergreifen und diese Maßnahmen auf
                        den Vorwurf einer Rechtswidrigkeit und/oder Rechtsverletzung stützen. Die Unterbrechung der
                        Produktpräsentation ist aufzuheben, sobald der Verdacht der Rechtswidrigkeit bzw. der
                        Rechtsverletzung ausgeräumt ist.
                    </li>
                    <li>Wir unterrichten Sie unverzüglich über eine Sperrung von Produktpräsentationen und fordern Sie
                        unter Bestimmung einer angemessenen Frist zur Ausräumung des Vorwurfs auf. Nach fruchtlosem
                        Fristablauf steht uns ein sofortiges Kündigungsrecht zu.
                    </li>
                </ol>
                <br>
                <ul>
                    <li><b>Kündigung</b></li>
                </ul>
                <ol>
                    <li>Jede Partei ist berechtigt, den Vertrag zur Nutzung der Plattform jederzeit zu kündigen.</li>
                </ol>
                <br>
                <ul>
                    <li><b>Ranking von Produktpräsentationen</b></li>
                </ul>
                <ol>
                    <li>Ranking der Anbieter in den normalen Such-Abläufen des Nutzers (nicht im Prospekt Bereich): Wenn
                        ein Nutzer nach einer Ware oder einer Dienstleistung sucht, werden ihm die Anbieter angezeigt,
                        welche örtlich gesehen am „nahesten“ liegen. Des Weiteren hat der Endnutzer die Möglichkeit,
                        vorgegebene Filter einzusetzen (Distanz in km, Bewertung des Unternehmens in Sternen von 1-5,
                        Preisklasse des Unternehmens von € – €€€, etc.) Sollten mehrere Unternehmen auf eingestellte
                        Filter zutreffen, so wird das Ranking immer nach der Distanz sortiert, von nah zu fern von oben
                        nach unten.
                    </li>
                    <li>Ranking im Prospekt Bereich, wo Anbieter explizit Rabatte auf ihre Dienstleistungen oder
                        Produkte schalten und diese bewerben können: Umso mehr Produkte und Dienstleistungen
                        (zusammengerechnet) ein Anbieter an einem Tag im Prospekt Bereich bewirbt, umso höher wird er an
                        diesem Tag im Ranking erscheinen. Auch hier kann der Nutzer die o.g. Filter einsetzen und das
                        Ranking aus seiner Sicht demnach beeinflussen. Sollten zwei oder mehr Unternehmen in die Filter
                        passen, ist das Unternehmen höher geranked, welches mehr Dienstleistungen und Produkte
                        (zusammengerechnet) bewirbt. Sollten zwei oder mehr Unternehmen gleich viele Dienstleistungen
                        und Produkte beworben haben, werden die Unternehmen nach der Distanz zum jeweiligen Kunden
                        geranked, von nah zu fern von oben nach unten.
                    </li>
                </ol>
                <br>
                <ul>
                    <li><b>Haftungsausschlüsse und -beschränkungen</b></li>
                </ul>
                <p>Für eine Haftung von uns auf Schadensersatz gilt:</p>
                <ol>
                    <li>Bei Vorsatz und grober Fahrlässigkeit, auch unserer Erfüllungsgehilfen, haften wir nach den
                        gesetzlichen Bestimmungen. Das gleiche gilt bei fahrlässig verursachten Schäden aus der
                        Verletzung des Lebens, des Körpers oder der Gesundheit.
                    </li>
                    <li>Bei fahrlässig verursachten Sach- und Vermögensschäden haften wir nur bei der Verletzung einer
                        wesentlichen Vertragspflicht, jedoch der Höhe nach beschränkt auf die bei Vertragsschluss
                        vorhersehbaren und vertragstypischen Schäden; wesentliche Vertragspflichten sind solche, deren
                        Erfüllung die ordnungsgemäße Durchführung des Vertrags überhaupt erst ermöglicht und auf deren
                        Einhaltung der Vertragspartner regelmäßig vertrauen darf.
                    </li>
                    <li>Im Übrigen ist eine Haftung von uns, unabhängig von deren Rechtsgrund, ausgeschlossen.
                    </li>
                    <li>Die Haftungsausschlüsse und -beschränkungen der vorstehenden Absätze (1) bis (3) gelten
                        sinngemäß auch zugunsten unserer Erfüllungsgehilfen.
                    </li>
                    <li>Eine Haftung wegen Übernahme einer Garantie oder nach dem Produkthaftungsgesetz bleibt von den
                        Haftungsausschlüssen und -beschränkungen der vorstehenden Absätze (1) bis (4) unberührt.
                    </li>
                </ol>
                <br>
                <ul>
                    <li><b>Anwendbares Recht, Gerichtsstand
                        </b></li>
                </ul>
                <ol>
                    <li>Es gilt deutsches Recht. Gegenüber einem Verbraucher gilt diese Rechtswahl nur insoweit, als
                        dadurch keine zwingend anwendbaren gesetzlichen Bestimmungen des Staates, in dem er seinen
                        Wohnsitz oder gewöhnlichen Aufenthalt hat, eingeschränkt werden.
                    </li>
                    <li>Gerichtsstand im Verkehr mit Kaufleuten, juristischen Personen des öffentlichen Rechts oder
                        öffentlich-rechtlichen Sondervermögen ist der Sitz unseres Unternehmens. Wir sind jedoch nach
                        unserer Wahl berechtigt, am Sitz des Kunden zu klagen.
                    </li>
                </ol>
                @elseif(app()->getLocale() == 'en')
                
                    <h3>GENERAL TERMS AND CONDITIONS OF BUSINESS</h3>
                    <ul>
                        <li>for purchasing or selling goods or services on the "Catch A Guide" platform</li>
                    </ul>
                    <br> <br>
                    <h4>CONTENTS</h4>
                    <ol>
                        <li><b>PURCHASING</b> GOODS AND/OR SERVICES ON THE CATCH A GUIDE PLATFORM</li>
                        <li><b>SELL</b> GOODS AND/OR SERVICES ON THE CATCH A GUIDE PLATFORM</li>
                    </ol>
                    <br><br><br>
                    <h4>1. OBTAINING <b>GOODS AND/OR SERVICES ON THE CATCH A GUIDE PLATFORM</b></h4>
                    <ul>
                        <li><b>operator and scope</b></li>
                    </ul>
                    <ol>
                        <li> This Section A. of the General Terms and Conditions (hereinafter " <b>Terms and Conditions</b> ") applies to the use of the website <a href="www.catchaguide.com">www.catchaguide.com</a> or the Catch A Guide app (Website and App are hereinafter referred to collectively as the " <b>Platform</b> ") for the subscription, i.e. the Ordering goods and/or services online on the platform. The platform is operated by Catch A Guide UG (limited liability), Von-Gahlen-Straße 31, 40625 Düsseldorf (hereinafter referred to as “ <b>we</b> ” or “ <b>us</b> ”).
                        </li>
                    </ol>
                    <br>
                    <ul>
                        <li><b>Contractual relationships on the platform</b></li>
                    </ul>
                    <ol>
                        <li>On the Platform, Users can order goods and/or services from third-party providers that are advertised on the Platform by these providers (hereinafter “ <b>Providers ”). </b>The user may not conclude the contract for a particular product or service with us, but with the relevant provider. The terms and conditions of the respective provider may apply to this contract, provided they are effectively included in the contract.
                        </li>
                    </ol>
                    <br>
                    <ul>
                        <li><b>Donate</b></li>
                    </ul>
                    <ol>
                        <li>It is also possible to make donations via the platform. Offers to accept donations on the platform are considered “services” for the purposes of these GTC. The provider of an offer to accept donations on the platform is considered a "provider" for the purposes of these GTC.
                        </li>
                    </ol>
                    <br>
                    <ul>
                        <li><b>Conclusion of the contract with us for the use of the platform</b></li>
                    </ul>
                    <ol>
                        <li>The provision of the platform does not constitute a binding offer to conclude a corresponding contract of use between the user and us. Rather, a binding offer is only made when the user submits his registration request to us via the platform. We may accept this offer by confirming the user's registration with a registration confirmation email.
                        </li>
                        <li>We store the contractual provisions, i.e. the registration data and these General Terms and Conditions. You can print out or save the contractual provisions by using the usual functionality of your browser (usually "Print" or "File" &gt; "Save as"). The registration data is contained in the overview that is displayed in the last step of the registration.
                        </li>
                        <li>Contract language is German.</li>
                    </ol>
                    <br>
                    <ul>
                        <li><b>Conclusion of the contract between user and provider</b></li>
                    </ul>
                    <ol>
                        <li>The presentation of the goods or services on the platform includes an offer from the respective provider to conclude a corresponding contract. By sending an order for a particular product or service, the user accepts this offer.
                        </li>
                        <li>The contract data, including the general terms and conditions of the provider integrated on the platform, if available, are stored on the platform. You can print out or save the contract data, including the general terms and conditions of the provider, by using the usual functionality of your end device or browser (usually "Print" or "File" &gt; "Save as").
                        </li>
                        <li>Contract language is German.</li>
                    </ol>
                    <br>
                    <ul>
                        <li><b>user account</b></li>
                    </ul>
                    <ol>
                        <li>When registering the user account, correct and complete information must be provided. Third-party data may not be used without their consent.
                        </li>
                        <li>You are obliged to treat your access data, such as your password, confidentially, not to make them accessible to third parties, and to inform us immediately in the event of loss or unauthorized use of your access data.
                        </li>
                    </ol>
                    <br>
                    <ul>
                        <li><b>Payment and Billing</b></li>
                    </ul>
                    <ol>
                        <li>The user pays for the purchase of goods or services from providers directly to the provider via the payment service provided on the platform.
                        </li>
                        <li>When purchasing the goods or services offered on the platform, the user pays us a processing fee of €10. This fee is automatically included in the total price of the goods or services as stated on the platform.
                        </li>
                        <li>The user may also receive the relevant invoice directly from the provider.</li>
                    </ol>
                    <br>
                    <ul>
                        <li><b>Provider warranties; special right of withdrawal</b></li>
                    </ul>
                    <ol>
                        <li>In the case of orders on the platform via a time-bound service from a provider, we grant you the right, in the name of and on behalf of the provider, to withdraw from the contract up to 24 hours before the start of the agreed appointment with a fee (costs for the transaction). This special right of withdrawal helps you in such cases in which a statutory consumer right of withdrawal does not exist in exceptional cases. If you withdraw from the contract within less than 24 hours, fees will be charged. The fees break down as follows:
                        </li>
                        <br>
                        <h5 class="mb-1"><b>More than 24 hours before the appointment</b></h5>
                        <table class="table table-bordered text-nowrap border-bottom" id="responsive-datatable">
    
                            <tbody><tr>
                                <th>Catch A Guide</th>
                                <th>Offerer</th>
                                <th>payment provider</th>
                                <th>When paying by credit card</th>
                                <th>For other payment methods</th>
                            </tr>
                            </tbody><tbody>
                            <tr>
                                <td>0%</td>
                                <td>0%</td>
                                <td>&nbsp;</td>
                                <td>€1.00</td>
                                <td>€0.20</td>
                            </tr>
                            </tbody>
                        </table>
                        <br><br>
                        <h5 class="mb-1"><b>Less than 24 hours before the appointment</b></h5>
                        <table class="table table-bordered text-nowrap border-bottom" id="responsive-datatable">
    
                            <tbody><tr>
                                <th>Catch A Guide</th>
                                <th>Offerer</th>
                                <th>payment provider</th>
                                <th>When paying by credit card</th>
                                <th>For other payment methods</th>
                            </tr>
                            </tbody><tbody>
                            <tr>
                                <td>100% of the fee paid to us (10€).</td>
                                <td>20%</td>
                                <td>&nbsp;</td>
                                <td>€1.00</td>
                                <td>€0.20</td>
                            </tr>
                            </tbody>
                        </table>
                        <br><br>
                        <li>We would like to inform you that the warranty obligations of the providers are based on the statutory provisions, unless something different is regulated in the effectively included general terms and conditions of the respective provider.
                        </li>
                    </ol>
                    <br>
                    <ul>
                        <li><b>User Contribution Requirements</b></li>
                    </ul>
                    <ol>
                        <li>Only lawful user contributions (notifications, ratings, etc.) may be communicated on or via the platform. In particular, the user contributions and/or their posting on the platform must not infringe any third-party rights (e.g. rights to names, trademarks, copyrights, data protection, personal rights, etc.). The user assures us that he can freely dispose of the necessary rights for the posting of his user contributions on the platform and that there are no conflicting rights of third parties.
                        </li>
                        <li>User contributions, whether in the form of images or text, must not contain any depictions of violence and must not be sexually offensive. They must not contain any discriminatory, insulting, racist, slanderous or otherwise illegal or immoral statements or representations
                        </li>
                        <li>Ratings given for providers must not contain any inaccurate statements of fact or abusive criticism and must not violate personal rights.
                        </li>
                    </ol>
                    <br>
                    <ul>
                        <li><b>Blocking of User Submissions</b></li>
                    </ul>
                    <ol>
                        <li>If no paid platform services (e.g. paid special placements) are used to place an ad, we are entitled to block and/or delete the ad at any time.
                        </li>
                    </ol>
                    <br>
                    <ul>
                        <li><b>Termination</b></li>
                    </ul>
                    <ol>
                        <li>Each party is entitled to terminate the contract for the use of the platform at any time.
                        </li>
                    </ol>
                    <br>
                    <ul>
                        <li><b>Disclaimers and Limitations of Liability</b></li>
                        <li>Our liability for damages applies</li>
                    </ul>
                    <ol>
                        <li>In the event of intent and gross negligence, including on the part of our vicarious agents, we are liable in accordance with the statutory provisions. The same applies to damage to life, limb or health caused by negligence.
                        </li>
                        <li>In the case of negligently caused damage to property and financial losses, we are only liable for the breach of a material contractual obligation, but limited to the damage that was foreseeable and typical for the contract at the time the contract was concluded; Essential contractual obligations are those whose fulfillment enables the proper execution of the contract in the first place and on whose compliance the contractual partner may regularly rely.
                        </li>
                        <li>Apart from that, liability on our part, regardless of its legal basis, is excluded.</li>
                        <li>The liability exclusions and limitations of the above paragraphs (1) to (3) also apply accordingly in favor of our vicarious agents.
                        </li>
                        <li>Liability due to the assumption of a guarantee or under the Product Liability Act remains unaffected by the exclusions and limitations of liability in paragraphs (1) to (4) above
                        </li>
                    </ol>
                    <br>
                    <ul>
                        <li><b>Governing Law, Jurisdiction</b></li>
                    </ul>
                    <ol>
                        <li>German law applies. This choice of law applies to a consumer only insofar as it does not restrict any mandatory statutory provisions of the state in which he has his place of residence or habitual abode.
                        </li>
                        <li>The place of jurisdiction in transactions with merchants, legal entities under public law or special funds under public law is the registered office of our company. However, we are entitled to choose to sue at the customer's registered office.
                        </li>
                    </ol>
                    <br><br><br>
                    <h4 class="mt-5">2. SALE OF GOODS AND/OR SERVICES ON THE CATCH A GUIDE PLATFORM</h4>
                    <ul>
                        <li><b>operator and scope</b></li>
                    </ul>
                    <ol>
                        <li>This section A. of the General Terms and Conditions (hereinafter " <b>Terms and Conditions</b> ") applies to the use of the website <a href="www.catchaguide.com">www.catchaguide.com</a> or the Catch A Guide app (Website and App are hereinafter collectively referred to as " <b>Platform</b> ") for the subscription, i.e. the online ordering of goods and/or services on the platform. The platform is operated by Catch A Guide UG (limited liability), Von-Gahlen-Straße 31, 40625 Düsseldorf (hereinafter referred to as “ <b>we</b> ” or “ <b>us</b>" designated).
                        </li>
                    </ol>
                    <br>
                    <ul>
                        <li><b>Contractual relationships on the platform</b></li>
                    </ul>
                    <ol>
                        <li>On the Platform, Users can order goods and/or services from providers that are advertised on the Platform by these providers (hereinafter referred to as “ <b>Providers</b> ” or “ <b>you</b> ” or “ <b>you ”). </b>The user may not conclude the contract for a particular product or service with us, but with the relevant provider. The terms and conditions of the respective provider may apply to this contract, provided they are effectively included in the contract. The providers can provide their terms and conditions on the platform.
                        </li>
                    </ol>
                    <br>
                    <ul>
                        <li><b>Conclusion of the contract with us for the use of the platform</b></li>
                    </ul>
                    <ol>
                        <li>The provision of the platform does not constitute a binding offer to conclude a corresponding contract of use between the user and us. Rather, a binding offer is only made when the user submits his registration request to us via the platform. We may accept this offer by confirming the user's registration with a registration confirmation email.
                        </li>
                        <li>In the course of registration, we reserve the right to request documents to verify the provider’s identity and business operations as a prerequisite for registration, e.g .a partnership agreement (in the case of legal entities).
                        </li>
                        <li>We store the contract terms, i.e. the registration data and these General Terms and Conditions. You can print out or save the contractual provisions by using the usual functionality of your browser (usually "Print" or "File" &gt; "Save as"). The registration data is contained in the overview that is displayed in the last step of the registration.
                        </li>
                        <li>Contract language is German.</li>
                    </ol>
                    <br>
                    <ul>
                        <li><b>Use of the platform as a provider</b></li>
                    </ul>
                    <ol>
                        <li>Providers can design the presentation of their goods and/or services in a self-administered area
                        </li>
                    </ol>
                    <br>
                    <ul>
                        <li><b>Granting of a special right of withdrawal to users</b></li>
                    </ul>
                    <ol>
                        <li>When concluding contracts between the customer and the provider on the platform for a time-bound service or an order for goods for which collection on site at a specific date (time or period) has been agreed, the provider authorizes us to deliver to the customer in the name and on behalf of the to grant the provider the right to withdraw from the contract with the provider free of charge up to 24 hours before the start of the agreed appointment. This special right of withdrawal is relevant for the user in those cases in which a statutory consumer right of withdrawal does not exist in exceptional cases.
                        </li>
                    </ol>
                    <br>
                    <ul>
                        <li><b>compensation</b></li>
                    </ul>
                    <ol>
                        <li>If the user pays for a sale on the platform, we also charge the provider a service fee. The amount of the service fee is 10% of the amount paid. The Service Fee is automatically collected when the Provider pays its earnings to the Provider. In addition, there are fees for various payment methods, which are also collected automatically. Income from providers can be paid out at intervals of two weeks or a month. In addition to the Catch A Guide service fee, there is a service fee from the payment provider Securpay. This depends on the respective payment method.
                        </li>
                    </ol>
                    <table class="table table-bordered text-nowrap border-bottom mt-3" id="responsive-datatable">
    
                        <tbody><tr>
                            <th>Transaction fees <br>
                                Per transaction, regardless <br>of the selected payment method
                            </th>
                            <th>Discount for the <br>payment transfer with the <br>payment method credit card</th>
                            <th>Discount for the <br>payment transfer with the <br>direct debit payment method</th>
                            <th>Discount for the <br>payment transfer with the <br>payment method in advance</th>
                            <th>Discount for the <br>payment transfer with the <br>payment method Sofortüberweisung</th>
                        </tr>
                        </tbody><tbody>
                        <tr>
                            <td>EUR 0.20</td>
                            <td>1.50%</td>
                            <td>1.20%</td>
                            <td>0.70%</td>
                            <td>1.00%</td>
                        </tr>
                        </tbody>
                    </table>
                    <p>The provider receives an invoice with its income and the accrued service fees on a monthly basis.</p>
                    <br>
                    <ul>
                        <li><b>Presentation of the goods and/or services on the platform</b></li>
                    </ul>
                    <ol>
                        <li>Compliance with Applicable Laws: It is your responsibility to ensure that your product presentation complies with relevant legal requirements. For entrepreneurs, this includes, among other things: the information requirements for electronic commerce (conclusion of contract, storage of the contract text, etc.), the information requirements for distance selling to consumers (cancellation instructions or, if applicable, information about the exclusion of the right of cancellation, shipping costs, delivery times, existence of a statutory right to liability for defects etc.), the reference to the platform of the EU Commission for online dispute resolution with a clickable link there (https://www.ec.
                        </li>
                        <li>No infringement of the product presentations against the rights of third parties: You must ensure that your product presentation does not infringe any industrial property rights or intellectual property rights of third parties, such as patent rights, copyrights, naming rights or trademark rights (brands, designs) and that they do not violate data protection law or personal rights of third parties or other rights of third parties.
                        </li>
                    </ol>
                    <br>
                    <ul>
                        <li><b>Blocking of product presentations</b></li>
                    </ul>
                    <ol>
                        <li>We are permitted to block product presentations immediately if there are indications that they are illegal or infringe the rights of third parties. For these purposes, it is to be regarded as an indication of illegality or a violation of the law if third parties take measures of any kind against us or against you and base these measures on an allegation of illegality and/or a violation of the law. The interruption of the product presentation must be lifted as soon as the suspicion of illegality or infringement has been eliminated.
                        </li>
                        <li>We will inform you immediately about the blocking of product presentations and request you to clear up the accusation within a reasonable period of time. After the deadline has expired without result, we have the right to terminate the contract immediately.
                        </li>
                    </ol>
                    <br>
                    <ul>
                        <li><b>Termination</b></li>
                    </ul>
                    <ol>
                        <li>Each party is entitled to terminate the contract for the use of the platform at any time.</li>
                    </ol>
                    <br>
                    <ul>
                        <li><b>Ranking of product presentations</b></li>
                    </ul>
                    <ol>
                        <li>Ranking of providers in the user's normal search processes (not in the prospectus area): When a user searches for a product or service, the providers that are "closest" in terms of location are displayed. Furthermore, the end user has the option of using predefined filters (distance in km, rating of the company in stars from 1-5, price range of the company from € - €€€, etc.). If several companies apply to the set filter, this will be Ranking always sorted by distance, from near to far from top to bottom.
                        </li>
                        <li>Ranking in the prospectus area, where providers can explicitly place discounts on their services or products and advertise them: The more products and services (added up) a provider advertises in the prospectus area on one day, the higher it will appear in the ranking that day. Here, too, the user can use the above filters and influence the ranking from his point of view. If two or more companies fit into the filters, the company that advertises more services and products (added together) will be ranked higher. If two or more companies have advertised the same number of services and products,
                        </li>
                    </ol>
                    <br>
                    <ul>
                        <li><b>Disclaimers and Limitations of Liability</b></li>
                    </ul>
                    <p>The following applies to our liability for damages:</p>
                    <ol>
                        <li>In the event of intent and gross negligence, including on the part of our vicarious agents, we are liable in accordance with the statutory provisions. The same applies to damage to life, limb or health caused by negligence.
                        </li>
                        <li>In the case of negligently caused damage to property and financial losses, we are only liable for the breach of a material contractual obligation, but limited to the damage that was foreseeable and typical for the contract at the time the contract was concluded; Essential contractual obligations are those whose fulfillment enables the proper execution of the contract in the first place and on whose compliance the contractual partner may regularly rely.
                        </li>
                        <li>Apart from that, liability on our part, regardless of its legal basis, is excluded.
                        </li>
                        <li>The liability exclusions and limitations of the above paragraphs (1) to (3) also apply accordingly in favor of our vicarious agents.
                        </li>
                        <li>Liability due to the assumption of a guarantee or under the Product Liability Act remains unaffected by the exclusions and limitations of liability in paragraphs (1) to (4) above.
                        </li>
                    </ol>
                    <br>
                    <ul>
                        <li><b>Governing Law, Jurisdiction
                            </b></li>
                    </ul>
                    <ol>
                        <li>German law applies. This choice of law applies to a consumer only insofar as it does not restrict any mandatory statutory provisions of the state in which he has his place of residence or habitual abode.
                        </li>
                        <li>The place of jurisdiction in transactions with merchants, legal entities under public law or special funds under public law is the registered office of our company. However, we are entitled to choose to sue at the customer's registered office.
                        </li>
                    </ol>
    

                @endif

            </div>
        </div>
    </section>
    <!--About Page End-->
@endsection
