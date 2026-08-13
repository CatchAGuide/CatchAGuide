<footer class="site-footer cag-footer">
    <div class="site-footer__top">
        <div class="container">
            <div class="cag-footer__grid">
                <div class="cag-footer__brand footer-widget__column footer-widget__about">
                    <div class="footer-widget__about-logo">
                        <a href="{{ route('welcome') }}">
                            <img
                                src="{{ asset('assets/images/logo/CatchAGuide2_Logo_PNG.png') }}"
                                alt="{{ config('app.name') }}"
                                width="220"
                                height="52"
                            >
                        </a>
                    </div>
                    <p class="footer-widget__about-text">@lang('message.listHere')</p>
                    <ul class="footer-widget__about-contact list-unstyled">
                        <li>
                            <div class="icon"><i class="fas fa-phone-square-alt" aria-hidden="true"></i></div>
                            <div class="text">
                                <a href="tel:+49{{ config('cag.contact_num') }}">+49 (0) {{ config('cag.contact_num') }}</a>
                            </div>
                        </li>
                        <li>
                            <div class="icon"><i class="fas fa-envelope" aria-hidden="true"></i></div>
                            <div class="text">
                                <a href="mailto:info.catchaguide@gmail.com">info.catchaguide@gmail.com</a>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="cag-footer__links footer-widget__column footer-widget__company">
                    <h3 class="footer-widget__title">@lang('message.legal')</h3>
                    <ul class="footer-widget__company-list list-unstyled">
                        <li><a href="{{ route('law.imprint') }}">@lang('message.imprint')</a></li>
                        <li><a href="{{ route('law.data-protection') }}">@lang('message.data-protection')</a></li>
                        <li><a href="{{ route('law.agb') }}">@lang('message.conditions')</a></li>
                        <li><a href="{{ route('law.notice-and-takedown') }}">@lang('message.notice-takedown')</a></li>
                        <li><a href="{{ route('law.faq') }}">@lang('message.faq')</a></li>
                    </ul>
                </div>

                <div class="cag-footer__links footer-widget__column footer-widget__explore">
                    <h3 class="footer-widget__title">@lang('message.miscellaneous')</h3>
                    <ul class="list-unstyled footer-widget__explore-list">
                        <li><a href="{{ route('guidings.landing') }}">@lang('message.Guiding')</a></li>
                        <li><a href="{{ route('vacations.index') }}">@lang('homepage.footer_vacations')</a></li>
                        <li><a href="{{ route('destination') }}">@lang('homepage.footer_destinations')</a></li>
                        <li><a href="{{ route('profile.settings') }}">@lang('message.myaccount')</a></li>
                        <li><a id="contact-footer" href="{{ route('additional.contact') }}">@lang('message.contact')</a></li>
                        <li><a href="{{ route('additional.for_agents') }}">@lang('message.for_agents')</a></li>
                    </ul>
                </div>

                <div class="cag-footer__newsletter">
                    @include('layouts.partials.newsletterform')
                </div>
            </div>
        </div>
    </div>

    <div class="site-footer__bottom">
        <div class="container">
            <div class="cag-footer__bottom">
                <a href="#" data-target="html" class="scroll-to-target scroll-to-top cag-footer__scroll-top" aria-label="Back to top">
                    <span class="icon-right-arrow" aria-hidden="true"></span>
                </a>
                <div class="footer-widget__social">
                    <a href="https://www.facebook.com/CatchAGuide" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                        <i class="fab fa-facebook" aria-hidden="true"></i>
                    </a>
                    <a href="https://wa.me/+49{{ config('cag.contact_num') }}" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp">
                        <i class="fab fa-whatsapp" aria-hidden="true"></i>
                    </a>
                    <a href="https://www.instagram.com/catchaguide_official/" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                        <i class="fab fa-instagram" aria-hidden="true"></i>
                    </a>
                </div>
                <p class="cag-footer__copy">
                    @ All Copyright {{ now()->year }},
                    <a href="{{ route('welcome') }}">{{ config('app.name') }}</a>
                </p>
            </div>
        </div>
    </div>
</footer>
