@php
    $footerUser = auth()->user();
    $footerListOffer = ['url' => route('login'), 'modal' => false];
    if (config('guide_onboarding.new_onboarding_enabled')) {
        if ($footerUser) {
            $footerListOffer = ['url' => route('guide.onboarding'), 'modal' => false];
        } else {
            $footerListOffer = ['url' => '#', 'modal' => true];
        }
    }
    $footerGroups = [
        [
            'title' => __('homepage.footer_group_tours'),
            'items' => [
                ['label' => __('homepage.footer_by_country'), 'url' => route('destination')],
                ['label' => __('homepage.footer_by_fish'), 'url' => route('targets.index')],
                ['label' => __('homepage.footer_by_method'), 'url' => route('guidings.methods')],
            ],
        ],
        [
            'title' => __('homepage.footer_group_vacations'),
            'items' => [
                ['label' => __('homepage.offers_camps_title'), 'url' => route('vacations.camps.index')],
                ['label' => __('homepage.offers_trips_title'), 'url' => route('vacations.trips.index')],
            ],
        ],
        [
            'title' => __('homepage.footer_group_providers'),
            'items' => [
                [
                    'label' => __('homepage.footer_list_offer'),
                    'url' => $footerListOffer['url'],
                    'modal' => $footerListOffer['modal'],
                ],
                ['label' => __('homepage.footer_become_partner'), 'url' => route('additional.contact')],
                ['label' => __('message.faq'), 'url' => route('law.faq')],
            ],
        ],
        [
            'title' => __('homepage.footer_group_about'),
            'items' => [
                ['label' => __('homepage.filter-magazine'), 'url' => route($blogPrefix.'.index')],
                ['label' => __('message.contact'), 'url' => route('additional.contact')],
                ['label' => __('message.about-us'), 'url' => route('additional.about_us')],
                ['label' => __('message.myaccount'), 'url' => route('profile.settings')],
                ['label' => __('message.for_agents'), 'url' => route('additional.for_agents')],
            ],
        ],
        [
            'title' => __('message.legal'),
            'items' => [
                ['label' => __('message.imprint'), 'url' => route('law.imprint')],
                ['label' => __('message.data-protection'), 'url' => route('law.data-protection')],
                ['label' => __('message.conditions'), 'url' => route('law.agb')],
                ['label' => __('message.notice-takedown'), 'url' => route('law.notice-and-takedown')],
            ],
        ],
    ];
@endphp
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
                    <ul class="footer-widget__about-contact list-unstyled cag-footer__contact cag-footer__contact--brand">
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

                <nav class="cag-footer__accordion" data-cag-footer-accordion aria-label="{{ __('message.miscellaneous') }}">
                    @foreach($footerGroups as $group)
                        <div class="cag-footer__group">
                            <button
                                type="button"
                                class="cag-footer__group-title"
                                id="cag-footer-tab-{{ $loop->index }}"
                                aria-expanded="false"
                                aria-controls="cag-footer-panel-{{ $loop->index }}"
                            >
                                <span>{{ $group['title'] }}</span>
                                <span class="cag-footer__group-caret" aria-hidden="true"></span>
                            </button>
                            <div
                                class="cag-footer__group-panel"
                                id="cag-footer-panel-{{ $loop->index }}"
                                role="region"
                                aria-labelledby="cag-footer-tab-{{ $loop->index }}"
                            >
                                <div class="cag-footer__group-panel-inner">
                                    <ul class="cag-footer__group-list list-unstyled">
                                        @foreach($group['items'] as $item)
                                            <li>
                                                <a
                                                    href="{{ $item['url'] }}"
                                                    @if(!empty($item['modal'])) data-bs-toggle="modal" data-bs-target="#guideApplicationModal" @endif
                                                >{{ $item['label'] }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </nav>

                <div class="cag-footer__contact cag-footer__contact--mobile">
                    <div class="cag-footer__contact-label">{{ __('homepage.footer_contact_label') }}</div>
                    <a href="tel:+49{{ config('cag.contact_num') }}">+49 (0) {{ config('cag.contact_num') }}</a>
                    <a href="mailto:info.catchaguide@gmail.com">info.catchaguide@gmail.com</a>
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
                <form action="{{ route('language.switch') }}" method="POST" class="cag-footer__langs">
                    @csrf
                    <input type="hidden" name="redirect_url" value="{{ url()->current() }}">
                    @foreach(config('app.locales') as $locale)
                        <button
                            type="submit"
                            name="language"
                            value="{{ $locale }}"
                            class="cag-footer__lang{{ app()->getLocale() === $locale ? ' is-active' : '' }}"
                        >{{ strtoupper($locale) }}</button>
                    @endforeach
                </form>
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
                    © {{ now()->year }} Catch A Guide
                </p>
            </div>
        </div>
    </div>
</footer>
<script>
(function () {
    var root = document.querySelector('[data-cag-footer-accordion]');
    if (!root) {
        return;
    }

    root.addEventListener('click', function (event) {
        var button = event.target.closest('.cag-footer__group-title');
        if (!button || !root.contains(button)) {
            return;
        }

        var group = button.closest('.cag-footer__group');
        if (!group) {
            return;
        }

        var willOpen = !group.classList.contains('is-open');
        root.querySelectorAll('.cag-footer__group.is-open').forEach(function (openGroup) {
            openGroup.classList.remove('is-open');
            var openButton = openGroup.querySelector('.cag-footer__group-title');
            if (openButton) {
                openButton.setAttribute('aria-expanded', 'false');
            }
        });

        if (willOpen) {
            group.classList.add('is-open');
            button.setAttribute('aria-expanded', 'true');
        }
    });
})();
</script>
