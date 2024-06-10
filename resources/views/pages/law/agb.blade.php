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
                    <h1 class="h2">@lang('message.term-conditions')</h1>
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
    @if(app()->getLocale() == 'en')
    <section class="about-page">
        <div class="container">
            <div class="row">
                <h3>ALLGEMEINE GESCHÄFTS- UND NUTZUNGSBEDINGUNGEN</h3>
                <p>für die Nutzung sowie den Bezug oder Absatz von Waren oder Dienstleistungen auf der „Catch A Guide“-Plattform</p>

                <h4 class="pt-5">1&#41; Betreiber und Anwendungsbereich</h4>
                <p>Die nachfolgenden Allgemeinen Geschäftsbedingungen (nachstehend „AGB“) gelten für die Nutzung der Website https://www.catchaguide.com oder der Catch A Guide-App (Website und App werden nachstehend gesammelt als „Plattform“ bezeichnet) sowie für Vertragsschlüsse über Waren und/oder Dienstleistungen auf der Plattform. Die Plattform wird von der Catch A Guide UG (haftungsbeschränkt), Von-Gahlen-Straße 31, 40625 Düsseldorf, betrieben (nachstehend als „wir“ oder „uns“ bezeichnet).</p>

                <h4 class="pt-5">2&#41; Verträge zwischen Nutzer und Anbietern</h4>
                <ol>
                    <li>Auf der Plattform können Kunden (nachstehend „Nutzer“) Waren und/oder Dienstleistungen dritter Anbieter bestellen, die von diesen Anbietern (nachstehend “Anbieter“) auf der Plattform beworben werden. Den Vertrag über eine jeweilige Ware oder Dienstleistung schließt der Nutzer nicht mit uns, sondern mit dem betreffenden Anbieter. Für diesen Vertrag gelten die AGB des jeweiligen Anbieters, sofern sie wirksam in den Vertrag einbezogen werden.  </li>
                    <li>Die Vertragsdaten einschließlich auf der Plattform eingebundener Allgemeiner Geschäftsbedingungen des Anbieters, sofern vorhanden, werden auf der Plattform gespeichert. Sie können die Vertragsdaten einschließlich der bezeichneten Allgemeinen Geschäftsbedingungen des Anbieters ausdrucken oder speichern, indem Sie die übliche Funktionalität Ihres Endgeräts bzw. Browsers nutzen (dort meist „Drucken“ bzw. “Datei” > “Speichern unter”).</li>
                    <li>Die Zahlung für den Bezug der Waren oder Dienstleistungen von Anbietern leistet der Nutzer über den auf der Plattform vorgesehenen Zahlungsdienst direkt an den Anbieter. Die betreffende Rechnung erhält der Nutzer ebenfalls direkt vom Anbieter.  Wir erbringen in Form der Bereitstellung der Plattform eine technische Dienstleistung und treten lediglich als Vermittler auf.</li>
                    <li>Ein registrierter Nutzer hat die Möglichkeit, sich in seinem Profil als Anbieter zu verifizieren. Die Verifizierung wird von uns überprüft und freigegeben, ein Anspruch auf Freigabe besteht jedoch nicht. Nach erfolgter Freigabe kann der Anbieter Angebote erstellen und auf der Plattform platzieren. Die Präsentation der Waren bzw. Dienstleistungen auf der Plattform beinhalten noch kein Angebot auf Abschluss eines Kaufvertrags. Die Buchung erfolgt wie nachfolgend dargestellt: Die Nutzer können ein Angebot in Form einer Buchungsanfrage über das Portal stellen. Der Anbieter bekommt die Anfrage und kann diese binnen 72 Stunden annehmen, bei Ablauf dieser Frist wird die Buchung automatisch abgelehnt. Bei Annahme ist die Buchung für uns als „vermittelt“ anzusehen und wird in Rechnung gestellt. Nach dem Guiding kann der Nutzer den Anbieter bewerten. Die weiteren Einzelheiten insbesondere zum konkreten Ablauf richten sich nach den jeweiligen Bedingungen des Anbieters.</li>
                </ol>
                
                <h4 class="pt-5">3&#41; Verträge zwischen uns und den Nutzern über die Nutzung der Plattform</h4>
                <ol>
                    <li>Die Bereitstellung der Plattform stellt noch kein verbindliches Angebot zum Abschluss eines entsprechenden Nutzungsvertrages zwischen dem Nutzer und uns dar. Ein verbindliches Angebot erfolgt erst dadurch, dass der Nutzer sein Registrierungsgesuch über die Plattform an uns übermittelt. Dieses Angebot können wir durch Registrierungsbestätigung per E-Mail annehmen, sind dazu jedoch nicht verpflichtet.</li>
                    <li>Um über die Plattform Verträge mit Anbietern schließen zu können, muss sich der Nutzer zunächst registrieren. Bei der Registrierung des Nutzerkontos sind richtige und vollständige Angaben zu machen. Daten Dritter dürfen ohne deren Einwilligung nicht verwendet werden. Alternativ kann der Nutzer Buchungen als „Gast“ vornehmen, ohne sich zuvor registrieren zu müssen.</li>
                    <li>Wir speichern die Vertragsbestimmungen, also die Registrierungsdaten und die vorliegenden AGB. Sie können die Vertragsbestimmungen Ihrerseits ausdrucken oder speichern, indem Sie jeweils die übliche Funktionalität Ihres Browsers nutzen (dort meist „Drucken“ bzw. “Datei” > “Speichern unter”). Die Registrierungsdaten sind in der Übersicht enthalten, die im letzten Schritt der Registrierung angezeigt wird. </li>
                    <li>Die Darstellung der Angebote auf der Plattform inkl. der stetig neu eingestellten Angebote richtet sich nach dem Zufallsprinzip und wird durch uns nicht nach Maßgabe bestimmter Parameter beeinflusst.</li>
                </ol>

                <h4 class="pt-5">4&#41; Nutzungsbedingungen der Plattform für die Nutzer</h4>
                <ol>
                    <li>Es dürfen nur rechtmäßige Nutzerbeiträge (Mitteilungen, Bewertungen o.ä.) auf oder über die Plattform kommuniziert werden. Insbesondere dürfen die Nutzerbeiträge und/oder deren Einstellung auf der Plattform keine Rechte Dritter (z.B. Namens-, Kennzeichen-, Urheber-, Datenschutz-, Persönlichkeitsrechte usw.) verletzen. Der Nutzer sichert uns zu, dass er über die erforderlichen Rechte für die Einstellung seiner Nutzerbeiträge auf der Plattform frei verfügen kann und Rechte Dritter nicht entgegenstehen.  </li>
                    <li>Die Nutzerbeiträge, ob in Bild oder Text, dürfen keine Gewaltdarstellungen beinhalten und nicht sexuell anstößig sein. Sie dürfen keine diskriminierenden, beleidigenden, rassistischen, verleumderischen oder sonst rechts- oder sittenwidrigen Aussagen oder Darstellungen beinhalten</li>
                    <li>Bewertungen, die zu Anbietern abgegeben werden, dürfen keine unzutreffenden Tatsachenbehauptungen oder Schmähkritik enthalten und nicht gegen Persönlichkeitsrechte verstoßen.</li>
                    <li>Der Nutzer stellt uns von sämtlichen Ansprüchen frei, die Dritte gegen uns wegen der Verletzung ihrer Rechte oder wegen Rechtsverstößen aufgrund der vom Nutzer eingestellten Angebote und/oder Inhalte geltend machen, sofern der Nutzer diese zu vertreten hat. Der Nutzer übernimmt diesbezüglich auch die Kosten unserer Rechtsverteidigung einschließlich sämtlicher Gerichts- und Anwaltskosten.</li>
                    <li>Wir behalten uns vor fremde Inhalte zu sperren, wenn diese nach den geltenden Gesetzen strafbar sind oder erkennbar zur Vorbereitung strafbarer Handlungen dienen.</li>
                    <li>Werden für eine Anzeigenschaltung keine entgeltlichen Dienste der Plattform (wie bspw. kostenpflichtige Sonderplatzierungen) genutzt, so sind wir berechtigt, die Anzeige jederzeit zu sperren und/ oder zu löschen.</li>
                </ol>

                <h4 class="pt-5">5&#41; Nutzung der Plattform als Anbieter / Vergütung</h4>
                <ol>
                    <li>Anbieter können die Präsentation ihrer Waren und/oder Dienstleistungen in einem jeweils selbst administrierten Bereich gestalten.</li>
                    <li>Wir behalten uns vor, im Zuge der Registrierung Unterlagen zur Verifikation von Identität und Gewerbebetrieb des Anbieters als Voraussetzung für die Registrierung anzufordern, bspw. eine Kopie des Personalausweises eines Einzelunternehmers oder Geschäftsführers, eine Gewerbeanmeldung, eine Umsatzsteuernummer, ggf. eine Handwerkskarte und/oder ggf. einen Gesellschaftervertrag (bei juristischen Personen). </li>
                    <li>Bei Bezahlung durch den Nutzer bei einem Verkauf auf der Plattform berechnen wir dem Anbieter eine Service-Gebühr, deren Höhe sich nach folgenden Maßgaben berechnet:
                    <br>
                    - 10 % bei einer Gesamtsummer bis 350€
                    <br>
                    - 7.5% bei einer Gesamtsummer von 350€ bis 1500€
                    <br>
                    - 3% bei einer Gesamtsummer über 1500€
                    <br>
                    der bezahlten Bruttosumme. 
                    <br>
                    Die Service-Gebühr wird bei der Auszahlung der Einnahmen des Anbieters an den Anbieter automatisch eingezogen. Die Zahlungsverwaltung wird bis zum Tag der gebuchten Dienstleistung von dem Unternehmen Online Payment Plattform organisiert. 
                    </li>
                    <li>Der Anbieter erhält auf einer monatlichen Basis eine Rechnung mit seinen Erträgen und den angefallenen Service-Gebühren.</li>
                </ol>

                <h4 class="pt-5">6&#41; Pflichten der Anbieter / Folge bei Verstößen</h4>
                <ol>
                    <li>Der Anbieter hat dafür Sorge zu tragen, dass seine Produktpräsentation mit den gesetzlichen Bestimmungen im Einklang stehen. Dazu zählen für Unternehmer unter anderem: die Informationspflichten für den elektronischen Geschäftsverkehr (Vertragsschluss, Speicherung des Vertragstextes etc.), die Informationspflichten beim Fernabsatz an Verbraucher (Widerrufsbelehrung bzw. ggf. Information über den Ausschluss des Widerrufsrechts, Versandkostenangabe, Lieferzeitangabe,  Bestehen eines gesetzlichen Mängelhaftungsrechts etc.), der Hinweis auf die Plattform der EU-Kommission zur Online-Streitbeilegung unter Angabe eines klickbaren Links dorthin (https://www.ec.europa.eu/consumers/odr), die Vorgaben der Preisangabenverordnung, das Verbot der unlauteren, irreführenden oder sonst wettbewerbswidrigen Werbung nach UWG oder produktspezifische Kennzeichnungspflichten (soweit sie auch für Werbung/Verkaufsangebote im Internet gelten).</li>
                    <li>Darüber hinaus hat der Anbieter dafür Sorge zu tragen, dass seine Produktpräsentation keine gewerblichen Schutzrechte Dritter oder Rechte Dritter an geistigem Eigentum verletzen wie bspw. Patentrechte, Urheberrechte, Namensrechte oder Kennzeichenrechte (Marken, Designs) und dass sie nicht gegen Datenschutzrecht oder Persönlichkeitsrechte Dritter oder sonstige Rechte Dritter verstößt.</li>
                    <li>Der Anbieter stellt uns von sämtlichen Ansprüchen frei, die Dritte gegen uns wegen der Verletzung ihrer Rechte oder wegen Rechtsverstößen aufgrund der vom Nutzer eingestellten Angebote und/oder Inhalte geltend machen, sofern der Nutzer diese zu vertreten hat. Der Nutzer übernimmt diesbezüglich auch die Kosten unserer Rechtsverteidigung einschließlich sämtlicher Gerichts- und Anwaltskosten.</li>
                    <li>Es ist uns gestattet, Produktpräsentationen sofort zu sperren, wenn objektive und belastbare Anhaltspunkte dafür vorliegen, dass diese rechtswidrig sind oder Rechte Dritter verletzen. Als Anhaltspunkt für eine Rechtswidrigkeit oder Rechtsverletzung ist es für diese Zwecke unter anderem anzusehen, wenn Dritte Maßnahmen, gleich welcher Art, gegen uns oder gegen den Anbieter ergreifen und diese Maßnahmen auf den begründeten Vorwurf einer Rechtswidrigkeit und/oder Rechtsverletzung stützen. Die Unterbrechung der Produktpräsentation ist aufzuheben, sobald der Verdacht der Rechtswidrigkeit bzw. der Rechtsverletzung ausgeräumt ist.</li>
                    <li>Wir unterrichten Sie unverzüglich über eine Sperrung von Produktpräsentationen und fordern Sie unter Bestimmung einer angemessenen Frist zur Ausräumung des Vorwurfs auf. Nach fruchtlosem Fristablauf steht uns ein sofortiges Kündigungsrecht zu.</li>
                </ol>

                <h4 class="pt-5">7&#41; Inkrafttreten und Kündigung</h4>
                <ol>
                    <li>Diese AGB treten in Kraft, sobald sich die Nutzer und Anbieter registriert und den Nutzungsbedingungen zugestimmt haben. </li>
                    <li>Jede Partei ist berechtigt, den Vertrag zur Nutzung der Plattform jederzeit zu kündigen. </li>
                    <li>Jede Kündigung muss schriftlich erfolgen. Kündigungen per E-Mail wahren die Schriftform.</li>
                </ol>

                <h4 class="pt-5">8&#41; Haftung</h4>
                <ol>
                    <li>Wir stellen mit unserer Plattform die Möglichkeit zum Vertragsschluss und zur Abwicklung abgeschlossener Verträge zwischen dem Nutzer und dem Anbieter zur Verfügung. Wir treten lediglich als Vermittler auf und haftet nicht für Ansprüche aus den über die Plattform zwischen dem Nutzer und dem Anbieter zustande gekommenen Verträgen.</li>
                    <li>Für die Erfüllung der zustande gekommenen Verträge über Waren und/oder Dienstleistungen haften ausschließlich der Anbieter und der Nutzer untereinander. Ebenso haftet ausschließlich der Anbieter im Rahmen der geltenden vertraglichen und gesetzlichen Bestimmungen, für jegliche Schäden, auch an Leben, Leib und Gesundheit, welche der Nutzer aufgrund des Vertrages erleidet. Für die Einhaltung der gesetzlichen Bestimmungen, z.B. des Fernabsatzrechts, ist ausschließlich der Anbieter verantwortlich.</li>
                    <li>Für nicht von uns verschuldete Störungen innerhalb des Leitungsnetzes übernehmen wir keine Haftung. </li>
                    <li>Für die vorübergehende Nichterreichbarkeit der Plattform im Rahmen von notwendigen Wartungsarbeiten haften wir nicht. Wartungsarbeiten sind von uns mit einer Vorfrist von 72 Stunden anzukündigen. Erfordert eine Sicherheitsbedrohung ein kurzfristigeres Vorgehen, sind wir in diesem Fall berechtigt, die Wartungsarbeiten unverzüglich durchzuführen, müssen jedoch den Nutzern die Erforderlichkeit darlegen und begründen. </li>
                    <li>Wir ergreifen angemessene technische und organisatorische Maßnahmen zum Schutz der Nutzerdaten vor Verlust, Diebstahl oder Manipulation. Für den Verlust von Daten haften wir nach Maßgabe der vorstehenden Absätze nur dann, wenn ein solcher Verlust durch angemessene Datensicherungsmaßnahmen seitens des Nutzers nicht vermeidbar gewesen wäre. </li>
                    <li>Unsere sonstige Haftung auf Schadensersatz ist begrenzt auf die gesetzlichen Bestimmungen.</li>
                </ol>

                <h4 class="pt-5">9&#41; Datenschutz</h4>
                <p>Die Einhaltung der datenschutzrechtlichen Regelungen (DSGVO und BDSG) nehmen wir sehr ernst. Zum Umgang mit den Nutzer- und Anbieterdaten verweisen wir auf unsere Datenschutzerklärung unter https://catchaguide.com/data-protection.</p>

                <h4 class="pt-5">10&#41; Schlussbestimmungen</h4>
                <ol>
                    <li>Auf das Vertragsverhältnis zwischen uns und den Nutzern und Anbietern findet das Recht der Bundesrepublik Deutschland Anwendung. Die Anwendung von UN-Kaufrecht wird ausgeschlossen. </li>
                    <li>Gerichtsstand für alle Streitigkeiten aus oder im Zusammenhang mit den Vereinbarungen zwischen uns und den Nutzern ist, soweit gesetzlich zulässig, unser Geschäftssitz. Wir sind jedoch nach unserer Wahl berechtigt, am Sitz des Kunden zu klagen.</li>
                    <li>Änderungen oder Ergänzungen dieser AGB sind nur wirksam, wenn sie schriftlich abgeschlossen oder schriftlich wechselseitig bestätigt worden sind. Dies gilt auch für die Aufhebung des Schriftformerfordernisses.</li>
                    <li>Sollten einzelne Bestimmungen dieser AGB unwirksam sein oder werden und/oder den gesetzlichen Regelungen widersprechen, so wird hierdurch die Wirksamkeit der AGB im Übrigen nicht berührt. Die unwirksame Bestimmung wird von den Vertragsparteien einvernehmlich durch eine solche Bestimmung ersetzt, welche dem wirtschaftlichen Zweck der unwirksamen Bestimmung in rechtswirksamer Weise am nächsten kommt. Die vorstehende Regelung gilt entsprechend bei Regelungslücken.</li>
                </ol>
            </div>
        </div>
    </section>
    @else
    <section class="about-page">
        <div class="container">
            <div class="row">
                <h3>ALLGEMEINE GESCHÄFTS- UND NUTZUNGSBEDINGUNGEN</h3>
                <p>für die Nutzung sowie den Bezug oder Absatz von Waren oder Dienstleistungen auf der „Catch A Guide“-Plattform</p>

                <h4 class="pt-5">1&#41; Betreiber und Anwendungsbereich</h4>
                <p>Die nachfolgenden Allgemeinen Geschäftsbedingungen (nachstehend „AGB“) gelten für die Nutzung der Website https://www.catchaguide.de oder der Catch A Guide-App (Website und App werden nachstehend gesammelt als „Plattform“ bezeichnet) sowie für Vertragsschlüsse über Waren und/oder Dienstleistungen auf der Plattform. Die Plattform wird von der Catch A Guide UG (haftungsbeschränkt), Von-Gahlen-Straße 31, 40625 Düsseldorf, betrieben (nachstehend als „wir“ oder „uns“ bezeichnet).</p>

                <h4 class="pt-5">2&#41; Verträge zwischen Nutzer und Anbietern</h4>
                <ol>
                    <li>Auf der Plattform können Kunden (nachstehend „Nutzer“) Waren und/oder Dienstleistungen dritter Anbieter bestellen, die von diesen Anbietern (nachstehend “Anbieter“) auf der Plattform beworben werden. Den Vertrag über eine jeweilige Ware oder Dienstleistung schließt der Nutzer nicht mit uns, sondern mit dem betreffenden Anbieter. Für diesen Vertrag gelten die AGB des jeweiligen Anbieters, sofern sie wirksam in den Vertrag einbezogen werden.</li>
                    <li>Die Vertragsdaten einschließlich auf der Plattform eingebundener Allgemeiner Geschäftsbedingungen des Anbieters, sofern vorhanden, werden auf der Plattform gespeichert. Sie können die Vertragsdaten einschließlich der bezeichneten Allgemeinen Geschäftsbedingungen des Anbieters ausdrucken oder speichern, indem Sie die übliche Funktionalität Ihres Endgeräts bzw. Browsers nutzen (dort meist „Drucken“ bzw. “Datei” > “Speichern unter”).</li>
                    <li>Die Zahlung für den Bezug der Waren oder Dienstleistungen von Anbietern leistet der Nutzer über den auf der Plattform vorgesehenen Zahlungsdienst direkt an den Anbieter. Die betreffende Rechnung erhält der Nutzer ebenfalls direkt vom Anbieter.  Wir erbringen in Form der Bereitstellung der Plattform eine technische Dienstleistung und treten lediglich als Vermittler auf.</li>
                    <li>Ein registrierter Nutzer hat die Möglichkeit, sich in seinem Profil als Anbieter zu verifizieren. Die Verifizierung wird von uns überprüft und freigegeben, ein Anspruch auf Freigabe besteht jedoch nicht. Nach erfolgter Freigabe kann der Anbieter Angebote erstellen und auf der Plattform platzieren. Die Präsentation der Waren bzw. Dienstleistungen auf der Plattform beinhalten noch kein Angebot auf Abschluss eines Kaufvertrags. Die Buchung erfolgt wie nachfolgend dargestellt: Die Nutzer können ein Angebot in Form einer Buchungsanfrage über das Portal stellen. Der Anbieter bekommt die Anfrage und kann diese binnen 72 Stunden annehmen, bei Ablauf dieser Frist wird die Buchung automatisch abgelehnt. Bei Annahme ist die Buchung für uns als „vermittelt“ anzusehen und wird in Rechnung gestellt. Nach dem Guiding kann der Nutzer den Anbieter bewerten. Die weiteren Einzelheiten insbesondere zum konkreten Ablauf richten sich nach den jeweiligen Bedingungen des Anbieters.</li>
                </ol>
                
                <h4 class="pt-5">3&#41; Verträge zwischen uns und den Nutzern über die Nutzung der Plattform</h4>
                <ol>
                    <li>Die Bereitstellung der Plattform stellt noch kein verbindliches Angebot zum Abschluss eines entsprechenden Nutzungsvertrages zwischen dem Nutzer und uns dar. Ein verbindliches Angebot erfolgt erst dadurch, dass der Nutzer sein Registrierungsgesuch über die Plattform an uns übermittelt. Dieses Angebot können wir durch Registrierungsbestätigung per E-Mail annehmen, sind dazu jedoch nicht verpflichtet.</li>
                    <li>Um über die Plattform Verträge mit Anbietern schließen zu können, muss sich der Nutzer zunächst registrieren. Bei der Registrierung des Nutzerkontos sind richtige und vollständige Angaben zu machen. Daten Dritter dürfen ohne deren Einwilligung nicht verwendet werden. Alternativ kann der Nutzer Buchungen als „Gast“ vornehmen, ohne sich zuvor registrieren zu müssen.</li>
                    <li>Wir speichern die Vertragsbestimmungen, also die Registrierungsdaten und die vorliegenden AGB. Sie können die Vertragsbestimmungen Ihrerseits ausdrucken oder speichern, indem Sie jeweils die übliche Funktionalität Ihres Browsers nutzen (dort meist „Drucken“ bzw. “Datei” > “Speichern unter”). Die Registrierungsdaten sind in der Übersicht enthalten, die im letzten Schritt der Registrierung angezeigt wird. </li>
                    <li>Die Darstellung der Angebote auf der Plattform inkl. der stetig neu eingestellten Angebote richtet sich nach dem Zufallsprinzip und wird durch uns nicht nach Maßgabe bestimmter Parameter beeinflusst.</li>
                </ol>

                <h4 class="pt-5">4&#41; Nutzungsbedingungen der Plattform für die Nutzer</h4>
                <ol>
                    <li>Es dürfen nur rechtmäßige Nutzerbeiträge (Mitteilungen, Bewertungen o.ä.) auf oder über die Plattform kommuniziert werden. Insbesondere dürfen die Nutzerbeiträge und/oder deren Einstellung auf der Plattform keine Rechte Dritter (z.B. Namens-, Kennzeichen-, Urheber-, Datenschutz-, Persönlichkeitsrechte usw.) verletzen. Der Nutzer sichert uns zu, dass er über die erforderlichen Rechte für die Einstellung seiner Nutzerbeiträge auf der Plattform frei verfügen kann und Rechte Dritter nicht entgegenstehen.  </li>
                    <li>Die Nutzerbeiträge, ob in Bild oder Text, dürfen keine Gewaltdarstellungen beinhalten und nicht sexuell anstößig sein. Sie dürfen keine diskriminierenden, beleidigenden, rassistischen, verleumderischen oder sonst rechts- oder sittenwidrigen Aussagen oder Darstellungen beinhalten</li>
                    <li>Bewertungen, die zu Anbietern abgegeben werden, dürfen keine unzutreffenden Tatsachenbehauptungen oder Schmähkritik enthalten und nicht gegen Persönlichkeitsrechte verstoßen.</li>
                    <li>Der Nutzer stellt uns von sämtlichen Ansprüchen frei, die Dritte gegen uns wegen der Verletzung ihrer Rechte oder wegen Rechtsverstößen aufgrund der vom Nutzer eingestellten Angebote und/oder Inhalte geltend machen, sofern der Nutzer diese zu vertreten hat. Der Nutzer übernimmt diesbezüglich auch die Kosten unserer Rechtsverteidigung einschließlich sämtlicher Gerichts- und Anwaltskosten.</li>
                    <li>Wir behalten uns vor fremde Inhalte zu sperren, wenn diese nach den geltenden Gesetzen strafbar sind oder erkennbar zur Vorbereitung strafbarer Handlungen dienen.</li>
                    <li>Werden für eine Anzeigenschaltung keine entgeltlichen Dienste der Plattform (wie bspw. kostenpflichtige Sonderplatzierungen) genutzt, so sind wir berechtigt, die Anzeige jederzeit zu sperren und/ oder zu löschen.</li>
                </ol>

                <h4 class="pt-5">5&#41; Nutzung der Plattform als Anbieter / Vergütung</h4>
                <ol>
                    <li>Anbieter können die Präsentation ihrer Waren und/oder Dienstleistungen in einem jeweils selbst administrierten Bereich gestalten.</li>
                    <li>Wir behalten uns vor, im Zuge der Registrierung Unterlagen zur Verifikation von Identität und Gewerbebetrieb des Anbieters als Voraussetzung für die Registrierung anzufordern, bspw. eine Kopie des Personalausweises eines Einzelunternehmers oder Geschäftsführers, eine Gewerbeanmeldung, eine Umsatzsteuernummer, ggf. eine Handwerkskarte und/oder ggf. einen Gesellschaftervertrag (bei juristischen Personen). </li>
                    <li>Bei Bezahlung durch den Nutzer bei einem Verkauf auf der Plattform berechnen wir dem Anbieter eine Service-Gebühr, deren Höhe sich nach folgenden Maßgaben berechnet:
                    <br>
                    - 10 % bei einer Gesamtsummer bis 350€
                    <br>
                    - 7.5% bei einer Gesamtsummer von 350€ bis 1500€
                    <br>
                    - 3% bei einer Gesamtsummer über 1500€
                    <br>
                    der bezahlten Bruttosumme. 
                    <br>
                    Die Service-Gebühr wird bei der Auszahlung der Einnahmen des Anbieters an den Anbieter automatisch eingezogen. Die Zahlungsverwaltung wird bis zum Tag der gebuchten Dienstleistung von dem Unternehmen Online Payment Plattform organisiert. 
                    </li>
                    <li>Der Anbieter erhält auf einer monatlichen Basis eine Rechnung mit seinen Erträgen und den angefallenen Service-Gebühren.</li>
                </ol>

                <h4 class="pt-5">6&#41; Pflichten der Anbieter / Folge bei Verstößen</h4>
                <ol>
                    <li>Der Anbieter hat dafür Sorge zu tragen, dass seine Produktpräsentation mit den gesetzlichen Bestimmungen im Einklang stehen. Dazu zählen für Unternehmer unter anderem: die Informationspflichten für den elektronischen Geschäftsverkehr (Vertragsschluss, Speicherung des Vertragstextes etc.), die Informationspflichten beim Fernabsatz an Verbraucher (Widerrufsbelehrung bzw. ggf. Information über den Ausschluss des Widerrufsrechts, Versandkostenangabe, Lieferzeitangabe,  Bestehen eines gesetzlichen Mängelhaftungsrechts etc.), der Hinweis auf die Plattform der EU-Kommission zur Online-Streitbeilegung unter Angabe eines klickbaren Links dorthin (https://www.ec.europa.eu/consumers/odr), die Vorgaben der Preisangabenverordnung, das Verbot der unlauteren, irreführenden oder sonst wettbewerbswidrigen Werbung nach UWG oder produktspezifische Kennzeichnungspflichten (soweit sie auch für Werbung/Verkaufsangebote im Internet gelten).</li>
                    <li>Darüber hinaus hat der Anbieter dafür Sorge zu tragen, dass seine Produktpräsentation keine gewerblichen Schutzrechte Dritter oder Rechte Dritter an geistigem Eigentum verletzen wie bspw. Patentrechte, Urheberrechte, Namensrechte oder Kennzeichenrechte (Marken, Designs) und dass sie nicht gegen Datenschutzrecht oder Persönlichkeitsrechte Dritter oder sonstige Rechte Dritter verstößt.</li>
                    <li>Der Anbieter stellt uns von sämtlichen Ansprüchen frei, die Dritte gegen uns wegen der Verletzung ihrer Rechte oder wegen Rechtsverstößen aufgrund der vom Nutzer eingestellten Angebote und/oder Inhalte geltend machen, sofern der Nutzer diese zu vertreten hat. Der Nutzer übernimmt diesbezüglich auch die Kosten unserer Rechtsverteidigung einschließlich sämtlicher Gerichts- und Anwaltskosten.</li>
                    <li>Es ist uns gestattet, Produktpräsentationen sofort zu sperren, wenn objektive und belastbare Anhaltspunkte dafür vorliegen, dass diese rechtswidrig sind oder Rechte Dritter verletzen. Als Anhaltspunkt für eine Rechtswidrigkeit oder Rechtsverletzung ist es für diese Zwecke unter anderem anzusehen, wenn Dritte Maßnahmen, gleich welcher Art, gegen uns oder gegen den Anbieter ergreifen und diese Maßnahmen auf den begründeten Vorwurf einer Rechtswidrigkeit und/oder Rechtsverletzung stützen. Die Unterbrechung der Produktpräsentation ist aufzuheben, sobald der Verdacht der Rechtswidrigkeit bzw. der Rechtsverletzung ausgeräumt ist.</li>
                    <li>Wir unterrichten Sie unverzüglich über eine Sperrung von Produktpräsentationen und fordern Sie unter Bestimmung einer angemessenen Frist zur Ausräumung des Vorwurfs auf. Nach fruchtlosem Fristablauf steht uns ein sofortiges Kündigungsrecht zu.</li>
                </ol>

                <h4 class="pt-5">7&#41; Inkrafttreten und Kündigung</h4>
                <ol>
                    <li>Diese AGB treten in Kraft, sobald sich die Nutzer und Anbieter registriert und den Nutzungsbedingungen zugestimmt haben. </li>
                    <li>Jede Partei ist berechtigt, den Vertrag zur Nutzung der Plattform jederzeit zu kündigen. </li>
                    <li>Jede Kündigung muss schriftlich erfolgen. Kündigungen per E-Mail wahren die Schriftform.</li>
                </ol>

                <h4 class="pt-5">8&#41; Haftung</h4>
                <ol>
                    <li>Wir stellen mit unserer Plattform die Möglichkeit zum Vertragsschluss und zur Abwicklung abgeschlossener Verträge zwischen dem Nutzer und dem Anbieter zur Verfügung. Wir treten lediglich als Vermittler auf und haftet nicht für Ansprüche aus den über die Plattform zwischen dem Nutzer und dem Anbieter zustande gekommenen Verträgen.</li>
                    <li>Für die Erfüllung der zustande gekommenen Verträge über Waren und/oder Dienstleistungen haften ausschließlich der Anbieter und der Nutzer untereinander. Ebenso haftet ausschließlich der Anbieter im Rahmen der geltenden vertraglichen und gesetzlichen Bestimmungen, für jegliche Schäden, auch an Leben, Leib und Gesundheit, welche der Nutzer aufgrund des Vertrages erleidet. Für die Einhaltung der gesetzlichen Bestimmungen, z.B. des Fernabsatzrechts, ist ausschließlich der Anbieter verantwortlich.</li>
                    <li>Für nicht von uns verschuldete Störungen innerhalb des Leitungsnetzes übernehmen wir keine Haftung. </li>
                    <li>Für die vorübergehende Nichterreichbarkeit der Plattform im Rahmen von notwendigen Wartungsarbeiten haften wir nicht. Wartungsarbeiten sind von uns mit einer Vorfrist von 72 Stunden anzukündigen. Erfordert eine Sicherheitsbedrohung ein kurzfristigeres Vorgehen, sind wir in diesem Fall berechtigt, die Wartungsarbeiten unverzüglich durchzuführen, müssen jedoch den Nutzern die Erforderlichkeit darlegen und begründen. </li>
                    <li>Wir ergreifen angemessene technische und organisatorische Maßnahmen zum Schutz der Nutzerdaten vor Verlust, Diebstahl oder Manipulation. Für den Verlust von Daten haften wir nach Maßgabe der vorstehenden Absätze nur dann, wenn ein solcher Verlust durch angemessene Datensicherungsmaßnahmen seitens des Nutzers nicht vermeidbar gewesen wäre. </li>
                    <li>Unsere sonstige Haftung auf Schadensersatz ist begrenzt auf die gesetzlichen Bestimmungen.</li>
                </ol>

                <h4 class="pt-5">9&#41; Datenschutz</h4>
                <p>Die Einhaltung der datenschutzrechtlichen Regelungen (DSGVO und BDSG) nehmen wir sehr ernst. Zum Umgang mit den Nutzer- und Anbieterdaten verweisen wir auf unsere Datenschutzerklärung unter https://catchaguide.de/data-protection.</p>

                <h4 class="pt-5">10&#41; Schlussbestimmungen</h4>
                <ol>
                    <li>Auf das Vertragsverhältnis zwischen uns und den Nutzern und Anbietern findet das Recht der Bundesrepublik Deutschland Anwendung. Die Anwendung von UN-Kaufrecht wird ausgeschlossen. </li>
                    <li>Gerichtsstand für alle Streitigkeiten aus oder im Zusammenhang mit den Vereinbarungen zwischen uns und den Nutzern ist, soweit gesetzlich zulässig, unser Geschäftssitz. Wir sind jedoch nach unserer Wahl berechtigt, am Sitz des Kunden zu klagen.</li>
                    <li>Änderungen oder Ergänzungen dieser AGB sind nur wirksam, wenn sie schriftlich abgeschlossen oder schriftlich wechselseitig bestätigt worden sind. Dies gilt auch für die Aufhebung des Schriftformerfordernisses.</li>
                    <li>Sollten einzelne Bestimmungen dieser AGB unwirksam sein oder werden und/oder den gesetzlichen Regelungen widersprechen, so wird hierdurch die Wirksamkeit der AGB im Übrigen nicht berührt. Die unwirksame Bestimmung wird von den Vertragsparteien einvernehmlich durch eine solche Bestimmung ersetzt, welche dem wirtschaftlichen Zweck der unwirksamen Bestimmung in rechtswirksamer Weise am nächsten kommt. Die vorstehende Regelung gilt entsprechend bei Regelungslücken.</li>
                </ol>
            </div>
        </div>
    </section>

    @endif
    <!--About Page End-->
@endsection
