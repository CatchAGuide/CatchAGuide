@if(!$agent->isMobile())
    <div class="accordion" id="accordionExample">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                    <i class="fa fa-user"></i>&nbsp;Mein Konto
                </button>
            </h2>
            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne"
                 data-bs-parent="#accordionExample">
                <div class="accordion-body py-0">
                    <ul class="list-unstyled">
                        <li><a href="{{ route('profile.settings') }}"><i class="fa fa-chevron-right"></i>
                                Einstellungen</a></li>
                        <li><a href="{{ route('profile.bookings') }}"><i class="fa fa-chevron-right"></i>
                                Buchungen</a></li>
                        <li><a href="{{ route('profile.favoriteguides') }}"><i
                                    class="fa fa-chevron-right"></i> Lieblingsguides</a></li>
                        <li><a href="{{ route('chat') }}"><i class="fa fa-chevron-right"></i>
                                Nachrichten ({{Auth::user()->countunreadmessages()}})</a></li>
                        @if(!Auth::user()->is_guide)
                            <li><a href="{{route('profile.becomeguide')}}"><i class="fa fa-chevron-right"></i>
                                    Als Guide verifizieren</a></li>
                        @endif
                        <li><a href="javascript:void(0)" onclick="$('#logoutForm').submit();">Ausloggen</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingTwo">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                    <i class="fa fa-cogs"></i>&nbsp;Einstellungen
                </button>
            </h2>
            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo"
                 data-bs-parent="#accordionExample">
                <div class="accordion-body py-0">
                    <ul class="list-unstyled">
                        <li><a href="{{ route('profile.settings') }}"><i class="fa fa-chevron-right"></i>
                                Konto Einstellungen</a></li>
                        @if(Auth::user()->is_guide)
                            <li><a href="{{ route('profile.myguidings') }}"><i class="fa fa-chevron-right"></i>
                                    Meine Guidings</a></li>
                            <li><a href="{{ route('profile.guidebookings') }}"><i class="fa fa-chevron-right"></i>
                                    Bei mir gebucht</a></li>
                            <li><a href="{{ route('profile.newguiding') }}"><i class="fa fa-chevron-right"></i>
                                    Guiding erstellen</a></li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endif
