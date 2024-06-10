<footer class="site-footer">
    <div class="site-footer__top">
        <div class="container">
            <div class="site-footer__top-inner" style="{{$agent->ismobile() ? 'padding: 20px' : ''}}">
                <div class="row">
                @if($agent->ismobile())

                        <ul class="footer-widget__about-contact list-unstyled">
                            <li>
                                <div class="icon">
                                    <i class="fas fa-phone-square-alt"></i>
                                </div>
                                <div class="text">
                                    <a href="tel: +4915752996580"> +4915752996580</a>
                                </div>
                            </li>
                            <li>
                                <div class="icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="text">
                                    <a href="mailto:info.catchaguide@gmail.com">info.catchaguide@gmail.com</a>
                                </div>
                            </li>
                        </ul>

                        <div class="col-xl-4 col-lg-6 col-md-6">

                            @include('layouts.partials.newsletterform')

                        </div>
                    @endif
                    <div class="col-xl-4 col-lg-6 col-md-6">
                        <div class="footer-widget__column footer-widget__about {{$agent->ismobile() ? 'text-center' : ''}}">
                            <div class="footer-widget__about-logo {{$agent->ismobile() ? 'text-center' : ''}}">
                                <a href="{{ route('welcome') }}"><img
                                        src="{{ asset('assets/images/logo/400PngdpiLogo-2.png') }}"
                                        alt="Logo"></a>
                            </div>
                            <p class="footer-widget__about-text">@lang('message.listHere')</p>
                            @if(!$agent->ismobile())
                                <ul class="footer-widget__about-contact list-unstyled">
                                    <li>
                                        <div class="icon">
                                            <i class="fas fa-phone-square-alt"></i>
                                        </div>
                                        <div class="text">
                                            <a href="tel: +4915752996580"> +4915752996580</a>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="icon">
                                            <i class="fas fa-envelope"></i>
                                        </div>
                                        <div class="text">
                                            <a href="mailto:info.catchaguide@gmail.com">info.catchaguide@gmail.com</a>
                                        </div>
                                    </li>
                                </ul>
                            @endif
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-6 col-md-6">
                        <div class="footer-widget__column footer-widget__company clearfix {{$agent->ismobile() ? 'text-center' : ''}}">
                            <h3 class="footer-widget__title">@lang('message.legal')</h3>
                            <ul class="footer-widget__company-list list-unstyled">
                                <li><a href="{{route('law.imprint')}}">@lang('message.imprint')</a></li>
                                <li><a href="{{route('law.data-protection')}}">@lang('message.data-protection')</a></li>
                                <li><a href="{{route('law.agb')}}">@lang('message.conditions')</a></li>
                                <li><a href="{{route('law.faq')}}">FAQ</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-6 col-md-6">
                        <div class="footer-widget__column footer-widget__explore {{$agent->ismobile() ? 'text-center' : ''}}">
                            <h3 class="footer-widget__title">@lang('message.miscellaneous')</h3>
                            <ul class="list-unstyled footer-widget__explore-list">
                                <li><a href="{{route('additional.about_us')}}">@lang('message.about-us')</a></li>
                                <li><a href="{{route('guidings.index')}}">@lang('message.Guiding')</a></li>
                                <li><a href="{{route('profile.settings')}}">@lang('message.myaccount')</a></li>
                                <li><a href="{{route('additional.contact')}}">@lang('message.contact')</a></li>
                            </ul>
                        </div>
                    </div>
                    @if(!$agent->ismobile())
                        <div class="col-xl-4 col-lg-6 col-md-6">
                            @include('layouts.partials.newsletterform')
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="site-footer__bottom">
        <div class="container">
            <div class="row">
                <div class="col-xl-12">
                    <div class="site-footer__bottom-inner">
                        <div class="site-footer__bottom-left">
                            <div class="footer-widget__social">
                                <a href="https://www.facebook.com/CatchAGuide" target="_blank"><i class="fab fa-facebook"></i></a>
                                <a href="https://wa.me/+4915752996580" target="_blank"><i class="fab fa-whatsapp"></i></a>
                                <a href="https://www.instagram.com/catchaguide_official/" target="_blank"><i
                                        class="fab fa-instagram"></i></a>
                            </div>
                        </div>
                        <div class="site-footer__bottom-right">
                            <p>@ All Copyright {{ now()->year }}, <a href="#">{{ config('app.name') }}</a></p>
                        </div>
                        <div class="site-footer__bottom-left-arrow">
                            <a href="#" data-target="html" class="scroll-to-target scroll-to-top"><span
                                    class="icon-right-arrow"></span></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
