@extends('layouts.app-v2-1')

@section('title',  ucwords(translate('Impressum')))

@section('meta_robots')
    <meta name="robots" content="noindex, nofollow">
@endsection

@section('content')
    <!--Page Header Start-->
    <div class="container">
        <section class="page-header">
            <div class="page-header__bottom breadcrumb-container guiding">
                <div class="page-header__bottom-inner">
                    <ul class="thm-breadcrumb list-unstyled">
                        <li><a href="{{ route('welcome') }}">@lang('message.home')</a></li>
                        <li><span><i class="fas fa-solid fa-chevron-right"></i></span></li>
                        <li class="active">@lang('message.imprint')</li>
                    </ul>
                </div>
            </div>
        </section>
    </div>
    <!--Page Header End-->

    <!--About Page Start-->
    <section class="about-pages">
        <div class="container">
            <div class="row">
                <h1 class="h2">@lang('message.imprint')</h1>
                @if(app()->getLocale() == 'de')
                <p>
                    Catch A Guide UG (haftungsbeschränkt)<br>
                    <br>
                    Von-Gahlen-Straße 31 <br>
                    40625, Düsseldorf <br>
                    Deutschland <br>
                    <br>
                    Geschäftsführer: Jonas Tusek <br>
                    <br>
                    Registergericht: Amtsgericht Düsseldorf <br>
                    Handelsregisternummer: HRB96629 <br>
                    <br>
                    Umsatzsteuer-Identifikationsnummer gemäß §27 a Umsatzsteuergesetz: DE352779006 <br>
                    <br>
                </p>
                <h5>Kontakt</h4>
                <p>Tel: +49{{env('CONTACT_NUM')}}<br>

                    Mail: info.catchaguide@gmail.com</p> <br>
                <h5>Streitschlichtung</h5>
                <p>Die Europäische Kommission stellt eine Plattform zur Online-Streitbeilegung (OS) bereit:
                    <a href="https://ec.europa.eu/consumers/odr.">https://ec.europa.eu/consumers/odr.</a>
                    Die E-Mail Adresse von Catch A Guide finden Sie oben im Impressum. Das Unternehmen ist nicht bereit
                    oder verpflichtet, an Streitbeilegungsverfahren vor einer Verbraucherschlichtungsstelle
                    teilzunehmen.</p>
                <br><br><br><br>
                <h5>Haftung für Inhaber</h5>
                <p>Als Diensteanbieter sind wir gemäß § 7 Abs.1 TMG für eigene Inhalte auf diesen Seiten nach den
                    allgemeinen Gesetzen verantwortlich. Nach §§ 8 bis 10 TMG sind wir als Diensteanbieter jedoch nicht
                    verpflichtet, übermittelte oder gespeicherte fremde Informationen zu überwachen oder nach Umständen
                    zu forschen, die auf eine rechtswidrige Tätigkeit hinweisen.
                    <br>
                    Die Inhalte dieser Seiten wurden mit größter Sorgfalt erstellt. Für die Richtigkeit, Vollständigkeit
                    und Aktualität der Inhalte kann jedoch keine Gewähr übernommen werden. Verpflichtungen zur
                    Entfernung oder Sperrung der Nutzung von Informationen nach den allgemeinen Gesetzen bleiben hiervon
                    unberührt. Eine diesbezügliche Haftung ist jedoch erst ab dem Zeitpunkt der Kenntnis einer konkreten
                    Rechtsverletzung möglich. Bei Bekanntwerden von entsprechenden Rechtsverletzungen werden diese
                    Inhalte umgehend entfernt. </p>
                <br><br> <br><br>
                <h5>Haftung für Links</h5>
                <p>Das Angebot dieser Seiten kann Links zu externen Websites Dritter enthalten, auf deren Inhalte wir
                    Einfluss haben. Deshalb kann für diese fremden Inhalte auch keine Gewähr übernehmen werden. Für die
                    Inhalte der verlinkten Seiten ist stets der jeweilige Anbieter oder Betreiber der Seiten
                    verantwortlich. Die verlinkten Seiten wurden zum Zeitpunkt der Verlinkung auf mögliche
                    Rechtsverstöße überprüft. Rechtswidrige Inhalte waren zum Zeitpunkt der Verlinkung nicht
                    erkennbar.
                    <br>
                    Eine permanente inhaltliche Kontrolle der verlinkten Seiten ist jedoch ohne konkrete Anhaltspunkte
                    einer Rechtsverletzung nicht zumutbar. Bei Bekanntwerden von Rechtsverletzungen werden derartige
                    Links umgehend entfernt.</p>
                <br><br><br><br>
                <h5>Urheberrecht</h5>
                <p>Die durch die vom Dienstanbieter erstellten Inhalte und Werke auf diesen Seiten unterliegen dem
                    deutschen Urheberrecht. Die Vervielfältigung, Bearbeitung, Verbreitung und jede Art der Verwertung
                    außerhalb der Grenzen des Urheberrechtes bedürfen der schriftlichen Zustimmung des jeweiligen Autors
                    bzw. Erstellers. Downloads und Kopien dieser Seite sind nur für den privaten, nicht kommerziellen
                    Gebrauch gestattet. <br>
                    Soweit die Inhalte auf dieser Seite nicht vom Dienstanbieter erstellt wurden, werden die
                    Urheberrechte Dritter beachtet. Insbesondere werden Inhalte Dritter als solche gekennzeichnet.
                    Sollten Sie trotzdem auf eine Urheberrechtsverletzung aufmerksam werden, bitten wir um einen
                    entsprechenden Hinweis. Bei Bekanntwerden von Rechtsverletzungen werden derartige Inhalte umgehend
                    entfernt.
                </p>
                @elseif(app()->getLocale() == 'en')
                <p>
                    Catch A Guide UG (limited liability)<br>
                    <br>
                    Von-Gahlen-Straße 31 <br>
                    40625, Düsseldorf <br>
                    Germany <br>
                    <br>
                    CEO: Jonas Tusek <br>
                    <br>
                    Commercial Register: Düsseldorf District Court <br>
                    Commercial Register Number: HRB96629 <br>
                    <br>
                    VAT Identification Number according to §27 a of the German Value Added Tax Act: DE352779006 <br>
                    <br>
                </p>
                <h5>Contact</h5>
                <p>
                    Tel: +49{{env('CONTACT_NUM')}} <br>
                    Email: info.catchaguide@gmail.com
                </p>
                <br>
                <h5>Dispute Resolution</h5>
                <p>
                    The European Commission provides a platform for online dispute resolution (ODR):
                    <a href="https://ec.europa.eu/consumers/odr">https://ec.europa.eu/consumers/odr</a>
                    You can find Catch A Guide's email address in the imprint above. The company is not willing or obligated to participate in dispute resolution proceedings before a consumer arbitration board.
                </p>
                <br><br><br><br>
                <h5>Liability for Content</h5>
                <p>
                    As a service provider, we are responsible for our own content on these pages in accordance with § 7 (1) of the German Telemedia Act (TMG). However, according to §§ 8 to 10 TMG, we are not obligated to monitor transmitted or stored third-party information or to investigate circumstances that indicate illegal activity.
                    <br>
                    The content of these pages has been created with the utmost care. However, we cannot guarantee the accuracy, completeness, or timeliness of the content. Obligations to remove or block the use of information under general laws remain unaffected. Liability in this regard is only possible from the time we become aware of a specific legal violation. Upon becoming aware of such legal violations, we will remove the content immediately.
                </p>
                <br><br><br><br>
                <h5>Liability for Links</h5>
                <p>
                    This website may contain links to third-party websites for which we have no influence over the content. Therefore, we cannot assume any liability for these external contents. The respective provider or operator of the linked pages is always responsible for their content. The linked pages were checked for possible legal violations at the time of linking. Illegal content was not recognizable at the time of linking.
                    <br>
                    Continuous monitoring of the linked pages is not reasonable without concrete evidence of a violation of the law. Upon becoming aware of legal violations, we will remove such links immediately.
                </p>
                <br><br><br><br>
                <h5>Copyright</h5>
                <p>
                    The content and works created by the service provider on these pages are subject to German copyright law. The reproduction, adaptation, distribution, and any kind of exploitation outside the limits of copyright require the written consent of the respective author or creator. Downloads and copies of this page are only permitted for private, non-commercial use. <br>
                    Insofar as the content on this page was not created by the service provider, the copyrights of third parties are respected. In particular, third-party content is marked as such. If you nevertheless become aware of a copyright infringement, please let us know. Upon becoming aware of legal violations, we will remove such content immediately.
                </p>
                @endif


            </div>
        </div>
    </section>
    <!--About Page End-->
@endsection
