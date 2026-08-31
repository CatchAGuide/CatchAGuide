@extends('layouts.app-v2')

@section('title', __('partner.meta_title'))
@section('description', __('partner.meta_description'))

@section('share_tags')
    <meta property="og:title" content="{{ __('partner.meta_title') }}" />
    <meta property="og:description" content="{{ __('partner.meta_description') }}" />
    <meta property="og:image" content="{{ asset('assets/images/homepage/hero-tour.webp') }}"/>
@endsection

@php
    $partnerFaqs = [
        ['q' => __('partner.faq_1_q'), 'a' => __('partner.faq_1_a')],
        ['q' => __('partner.faq_2_q'), 'a' => __('partner.faq_2_a')],
        ['q' => __('partner.faq_3_q'), 'a' => __('partner.faq_3_a')],
        ['q' => __('partner.faq_4_q'), 'a' => __('partner.faq_4_a')],
        ['q' => __('partner.faq_5_q'), 'a' => __('partner.faq_5_a')],
        ['q' => __('partner.faq_6_q'), 'a' => __('partner.faq_6_a')],
    ];
    $partnerQuotes = [
        ['name' => __('partner.quote_1_name'), 'role' => __('partner.quote_1_role'), 'text' => __('partner.quote_1_text'), 'stat' => __('partner.quote_1_stat')],
        ['name' => __('partner.quote_2_name'), 'role' => __('partner.quote_2_role'), 'text' => __('partner.quote_2_text'), 'stat' => __('partner.quote_2_stat')],
        ['name' => __('partner.quote_3_name'), 'role' => __('partner.quote_3_role'), 'text' => __('partner.quote_3_text'), 'stat' => __('partner.quote_3_stat')],
    ];
    $contactPhone = (string) config('cag.contact_num');
    $contactTel = '+49'.preg_replace('/\D/', '', $contactPhone);
    $contactMail = 'info.catchaguide@gmail.com';
@endphp

@section('content')
<div class="cag-partner-hub" data-partner-hub>
    <div class="cag-partner-hub__hero-shell cag-site-nav-shell">
        @include('layouts.partials.site-nav', [
            'overlay' => true,
            'idPrefix' => 'partner',
        ])

        <section class="cag-partner-hub__hero" data-partner-hero>
            <img
                class="cag-partner-hub__hero-img"
                src="{{ asset('assets/images/homepage/hero-tour.webp') }}"
                alt=""
            >
            <div class="cag-partner-hub__hero-shade"></div>
            <div class="cag-partner-hub__inner cag-partner-hub__hero-copy">
                <h1 class="cag-partner-hub__hero-title">{{ __('partner.hero_title') }}</h1>
                <p class="cag-partner-hub__hero-text">{{ __('partner.hero_text') }}</p>
                <div class="cag-partner-hub__hero-actions">
                    @include('pages.additional.partials.partner-signup-cta', ['label' => __('partner.cta_become')])
                    <a href="#ablauf" class="cag-partner-hub__btn cag-partner-hub__btn--ghost">{{ __('partner.cta_how') }}</a>
                </div>
                <p class="cag-partner-hub__hero-note">{{ __('partner.hero_note') }}</p>
            </div>
        </section>
    </div>

    <section class="cag-partner-hub__stats" aria-label="{{ __('partner.stat_anglers_label') }}">
        <div class="cag-partner-hub__inner cag-partner-hub__stats-grid">
            <div class="cag-partner-hub__stat">
                <p class="cag-partner-hub__stat-value">{{ __('partner.stat_anglers') }}</p>
                <p class="cag-partner-hub__stat-label">{{ __('partner.stat_anglers_label') }}</p>
            </div>
            <div class="cag-partner-hub__stat">
                <p class="cag-partner-hub__stat-value">{{ __('partner.stat_offers') }}</p>
                <p class="cag-partner-hub__stat-label">{{ __('partner.stat_offers_label') }}</p>
            </div>
            <div class="cag-partner-hub__stat">
                <p class="cag-partner-hub__stat-value">{{ __('partner.stat_partners') }}</p>
                <p class="cag-partner-hub__stat-label">{{ __('partner.stat_partners_label') }}</p>
            </div>
            <div class="cag-partner-hub__stat">
                <p class="cag-partner-hub__stat-value">{{ __('partner.stat_countries') }}</p>
                <p class="cag-partner-hub__stat-label">{{ __('partner.stat_countries_label') }}</p>
            </div>
        </div>
    </section>

    <section class="cag-partner-hub__section cag-partner-hub__section--white">
        <div class="cag-partner-hub__inner">
            <h2 class="cag-partner-hub__title">{{ __('partner.audience_title') }}</h2>
            <p class="cag-partner-hub__lead">{{ __('partner.audience_text') }}</p>
            <div class="cag-partner-hub__offer-grid">
                <article class="cag-partner-hub__offer-card">
                    <img src="{{ asset('assets/images/homepage/hero-tour.webp') }}" alt="{{ __('partner.offer_guidings_title') }}">
                    <div class="cag-partner-hub__offer-body">
                        <h3>{{ __('partner.offer_guidings_title') }}</h3>
                        <p>{{ __('partner.offer_guidings_text') }}</p>
                        <a href="#signup">{{ __('partner.offer_cta') }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                    </div>
                </article>
                <article class="cag-partner-hub__offer-card">
                    <img src="{{ asset('assets/images/homepage/hero-camp.webp') }}" alt="{{ __('partner.offer_camps_title') }}">
                    <div class="cag-partner-hub__offer-body">
                        <h3>{{ __('partner.offer_camps_title') }}</h3>
                        <p>{{ __('partner.offer_camps_text') }}</p>
                        <a href="#signup">{{ __('partner.offer_cta') }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                    </div>
                </article>
                <article class="cag-partner-hub__offer-card">
                    <img src="{{ asset('assets/images/homepage/hero-trip.webp') }}" alt="{{ __('partner.offer_trips_title') }}">
                    <div class="cag-partner-hub__offer-body">
                        <h3>{{ __('partner.offer_trips_title') }}</h3>
                        <p>{{ __('partner.offer_trips_text') }}</p>
                        <a href="#signup">{{ __('partner.offer_cta') }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section class="cag-partner-hub__section cag-partner-hub__section--slate">
        <div class="cag-partner-hub__inner">
            <h2 class="cag-partner-hub__title">{{ __('partner.why_title') }}</h2>
            <div class="cag-partner-hub__why-grid">
                <div>
                    <div class="cag-partner-hub__icon-row">
                        <i class="fas fa-shield-alt" aria-hidden="true"></i>
                        <h3>{{ __('partner.why_risk_title') }}</h3>
                    </div>
                    <p>{{ __('partner.why_risk_text') }}</p>
                </div>
                <div>
                    <div class="cag-partner-hub__icon-row">
                        <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                        <h3>{{ __('partner.why_guests_title') }}</h3>
                    </div>
                    <p>{{ __('partner.why_guests_text') }}</p>
                </div>
                <div>
                    <div class="cag-partner-hub__icon-row">
                        <i class="fas fa-sliders-h" aria-hidden="true"></i>
                        <h3>{{ __('partner.why_control_title') }}</h3>
                    </div>
                    <p>{{ __('partner.why_control_text') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section id="ablauf" class="cag-partner-hub__section cag-partner-hub__section--white cag-partner-hub__anchor">
        <div class="cag-partner-hub__inner">
            <h2 class="cag-partner-hub__title">{{ __('partner.flow_title') }}</h2>
            <ol class="cag-partner-hub__timeline">
                <li>
                    <span class="cag-partner-hub__step cag-partner-hub__step--filled">1</span>
                    <div>
                        <h3>{{ __('partner.flow_1_title') }}</h3>
                        <p>{{ __('partner.flow_1_text') }}</p>
                    </div>
                </li>
                <li>
                    <span class="cag-partner-hub__step">2</span>
                    <div>
                        <h3>{{ __('partner.flow_2_title') }}</h3>
                        <p>{{ __('partner.flow_2_text') }}</p>
                    </div>
                </li>
                <li>
                    <span class="cag-partner-hub__step">3</span>
                    <div>
                        <h3>{{ __('partner.flow_3_title') }}</h3>
                        <p>{{ __('partner.flow_3_text') }}</p>
                    </div>
                </li>
                <li>
                    <span class="cag-partner-hub__step">4</span>
                    <div>
                        <h3>{{ __('partner.flow_4_title') }}</h3>
                        <p>{{ __('partner.flow_4_text') }}</p>
                    </div>
                </li>
            </ol>
        </div>
    </section>

    <section class="cag-partner-hub__section cag-partner-hub__section--muted">
        <div class="cag-partner-hub__inner">
            <h2 class="cag-partner-hub__title">{{ __('partner.we_title') }}</h2>
            <div class="cag-partner-hub__we-grid">
                <div>
                    <div class="cag-partner-hub__icon-row cag-partner-hub__icon-row--dark">
                        <i class="fas fa-bullhorn" aria-hidden="true"></i>
                        <h3>{{ __('partner.we_marketing_title') }}</h3>
                    </div>
                    <p>{{ __('partner.we_marketing_text') }}</p>
                </div>
                <div>
                    <div class="cag-partner-hub__icon-row cag-partner-hub__icon-row--dark">
                        <i class="fas fa-headset" aria-hidden="true"></i>
                        <h3>{{ __('partner.we_advice_title') }}</h3>
                    </div>
                    <p>{{ __('partner.we_advice_text') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="cag-partner-hub__section cag-partner-hub__section--slate">
        <div class="cag-partner-hub__inner">
            <h2 class="cag-partner-hub__title">{{ __('partner.price_title') }}</h2>
            <p class="cag-partner-hub__lead cag-partner-hub__lead--on-dark">{{ __('partner.price_intro') }}</p>
            <div class="cag-partner-hub__price-grid">
                <div class="cag-partner-hub__price-card">
                    <h3>{{ __('partner.price_tours_title') }}</h3>
                    <p class="cag-partner-hub__price-eyebrow">{{ __('partner.price_tours_eyebrow') }}</p>
                    <ul>
                        <li>{{ __('partner.price_tours_1') }}</li>
                        <li>{{ __('partner.price_tours_2') }}</li>
                        <li>{{ __('partner.price_tours_3') }}</li>
                    </ul>
                    <p class="cag-partner-hub__price-note">{{ __('partner.price_tours_note') }}</p>
                </div>
                <div class="cag-partner-hub__price-card">
                    <h3>{{ __('partner.price_packages_title') }}</h3>
                    <p class="cag-partner-hub__price-flat">{{ __('partner.price_packages_rate') }}</p>
                    <p class="cag-partner-hub__price-note">{{ __('partner.price_packages_note') }}</p>
                </div>
            </div>
            <ul class="cag-partner-hub__perks">
                <li><i class="fas fa-check" aria-hidden="true"></i>{{ __('partner.price_perk_signup') }}</li>
                <li><i class="fas fa-check" aria-hidden="true"></i>{{ __('partner.price_perk_monthly') }}</li>
                <li><i class="fas fa-check" aria-hidden="true"></i>{{ __('partner.price_perk_term') }}</li>
                <li><i class="fas fa-check" aria-hidden="true"></i>{{ __('partner.price_perk_exclusive') }}</li>
            </ul>
            @include('pages.additional.partials.partner-signup-cta', ['label' => __('partner.cta_become')])
        </div>
    </section>

    <section class="cag-partner-hub__section cag-partner-hub__section--white cag-partner-hub__quotes-section">
        <div class="cag-partner-hub__inner">
            <h2 class="cag-partner-hub__title">{{ __('partner.quotes_title') }}</h2>
            <div class="cag-partner-hub__quotes" role="list">
                @foreach($partnerQuotes as $quote)
                    <blockquote class="cag-partner-hub__quote" role="listitem">
                        <div class="cag-partner-hub__quote-head">
                            <span class="cag-partner-hub__quote-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($quote['name'], 0, 1)) }}</span>
                            <div>
                                <cite>{{ $quote['name'] }}</cite>
                                <p>{{ $quote['role'] }}</p>
                            </div>
                        </div>
                        <p class="cag-partner-hub__quote-text">“{{ $quote['text'] }}”</p>
                        <p class="cag-partner-hub__quote-stat">{{ $quote['stat'] }}</p>
                    </blockquote>
                @endforeach
            </div>
        </div>
    </section>

    <section class="cag-partner-hub__section cag-partner-hub__section--muted">
        <div class="cag-partner-hub__inner">
            <h2 class="cag-partner-hub__title">{{ __('partner.bring_title') }}</h2>
            <p class="cag-partner-hub__lead">{{ __('partner.bring_intro') }}</p>
            <ul class="cag-partner-hub__bring">
                <li><i class="fas fa-check-circle" aria-hidden="true"></i><p><strong>{{ __('partner.bring_1_lead') }}</strong>{{ __('partner.bring_1_text') }}</p></li>
                <li><i class="fas fa-check-circle" aria-hidden="true"></i><p><strong>{{ __('partner.bring_2_lead') }}</strong>{{ __('partner.bring_2_text') }}</p></li>
                <li><i class="fas fa-check-circle" aria-hidden="true"></i><p><strong>{{ __('partner.bring_3_lead') }}</strong>{{ __('partner.bring_3_text') }}</p></li>
                <li><i class="fas fa-check-circle" aria-hidden="true"></i><p><strong>{{ __('partner.bring_4_lead') }}</strong>{{ __('partner.bring_4_text') }}</p></li>
                <li><i class="fas fa-check-circle" aria-hidden="true"></i><p><strong>{{ __('partner.bring_5_lead') }}</strong>{{ __('partner.bring_5_text') }}</p></li>
            </ul>
        </div>
    </section>

    <section id="signup" class="cag-partner-hub__section cag-partner-hub__section--white cag-partner-hub__anchor">
        <div class="cag-partner-hub__inner">
            <h2 class="cag-partner-hub__title">{{ __('partner.steps_title') }}</h2>
            <ol class="cag-partner-hub__signup-steps">
                <li>
                    <span class="cag-partner-hub__step cag-partner-hub__step--filled">1</span>
                    <div>
                        <h3>{{ __('partner.step_1_title') }}</h3>
                        <p>{{ __('partner.step_1_text') }}</p>
                    </div>
                </li>
                <li>
                    <span class="cag-partner-hub__step">2</span>
                    <div>
                        <h3>{{ __('partner.step_2_title') }}</h3>
                        <p>{{ __('partner.step_2_text') }}</p>
                    </div>
                </li>
                <li>
                    <span class="cag-partner-hub__step">3</span>
                    <div>
                        <h3>{{ __('partner.step_3_title') }}</h3>
                        <p>{{ __('partner.step_3_text') }}</p>
                    </div>
                </li>
                <li>
                    <span class="cag-partner-hub__step">4</span>
                    <div>
                        <h3>{{ __('partner.step_4_title') }}</h3>
                        <p>{{ __('partner.step_4_text') }}</p>
                    </div>
                </li>
            </ol>
            <p class="cag-partner-hub__hint"><i class="fas fa-info-circle" aria-hidden="true"></i>{{ __('partner.steps_hint') }}</p>
            @include('pages.additional.partials.partner-signup-cta', [
                'label' => __('partner.cta_signup'),
                'class' => 'cag-partner-hub__btn cag-partner-hub__btn--primary cag-partner-hub__btn--block',
            ])
        </div>
    </section>

    <section class="cag-partner-hub__section cag-partner-hub__section--muted">
        <div class="cag-partner-hub__inner">
            <h2 class="cag-partner-hub__title">{{ __('partner.faq_title') }}</h2>
            <div class="cag-partner-hub__faq" data-partner-faq>
                @foreach($partnerFaqs as $faq)
                    <div class="cag-partner-hub__faq-item{{ $loop->first ? ' is-open' : '' }}">
                        <button
                            type="button"
                            class="cag-partner-hub__faq-toggle"
                            aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                        >
                            <span>{{ $faq['q'] }}</span>
                            <span class="cag-partner-hub__faq-icon" aria-hidden="true"></span>
                        </button>
                        <div class="cag-partner-hub__faq-panel">
                            <div class="cag-partner-hub__faq-panel-inner">
                                <p>{{ $faq['a'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="cag-partner-hub__section cag-partner-hub__section--slate cag-partner-hub__talk">
        <div class="cag-partner-hub__inner">
            <span class="cag-partner-hub__talk-avatar" aria-hidden="true">J</span>
            <h2 class="cag-partner-hub__title">{{ __('partner.talk_title') }}</h2>
            <p class="cag-partner-hub__lead cag-partner-hub__lead--on-dark">{{ __('partner.talk_text') }}</p>
            <p class="cag-partner-hub__talk-name">{{ __('partner.talk_name') }}</p>
            <div class="cag-partner-hub__talk-actions">
                <a href="tel:{{ $contactTel }}" class="cag-partner-hub__btn cag-partner-hub__btn--ghost">
                    <i class="fas fa-phone-alt" aria-hidden="true"></i>+49 (0) {{ $contactPhone }}
                </a>
                <a href="mailto:{{ $contactMail }}" class="cag-partner-hub__btn cag-partner-hub__btn--ghost">
                    <i class="fas fa-envelope" aria-hidden="true"></i>{{ $contactMail }}
                </a>
            </div>
        </div>
    </section>

    <section class="cag-partner-hub__ready">
        <img src="{{ asset('assets/images/homepage/hero-vacation.webp') }}" alt="">
        <div class="cag-partner-hub__ready-shade"></div>
        <div class="cag-partner-hub__inner cag-partner-hub__ready-copy">
            <h2 class="cag-partner-hub__title">{{ __('partner.ready_title') }}</h2>
            <p class="cag-partner-hub__lead">{{ __('partner.ready_text') }}</p>
            @include('pages.additional.partials.partner-signup-cta', [
                'label' => __('partner.cta_become'),
                'class' => 'cag-partner-hub__btn cag-partner-hub__btn--primary cag-partner-hub__btn--block',
            ])
            <a href="{{ route('additional.contact') }}" class="cag-partner-hub__ready-link">{{ __('partner.ready_contact') }}</a>
        </div>
    </section>
</div>
@endsection

@section('js_after')
<script>
function handleLanguageSwitch(selectElement, formId) {
    var form = document.getElementById(formId);
    if (form) form.submit();
}
(function () {
    var shell = document.querySelector('[data-partner-hub] .cag-site-nav-shell');
    var hero = document.querySelector('[data-partner-hero]');
    var nav = shell ? shell.querySelector('.cag-site-nav') : null;
    if (nav && hero) {
        var syncNavSolid = function () {
            nav.classList.toggle('is-solid', hero.getBoundingClientRect().bottom <= nav.offsetHeight + 12);
        };
        syncNavSolid();
        window.addEventListener('scroll', syncNavSolid, { passive: true });
        window.addEventListener('resize', syncNavSolid);
    }

    var faqRoot = document.querySelector('[data-partner-faq]');
    if (!faqRoot) {
        return;
    }
    faqRoot.addEventListener('click', function (event) {
        var button = event.target.closest('.cag-partner-hub__faq-toggle');
        if (!button || !faqRoot.contains(button)) {
            return;
        }
        var item = button.closest('.cag-partner-hub__faq-item');
        if (!item) {
            return;
        }
        var willOpen = !item.classList.contains('is-open');
        faqRoot.querySelectorAll('.cag-partner-hub__faq-item.is-open').forEach(function (openItem) {
            openItem.classList.remove('is-open');
            var openButton = openItem.querySelector('.cag-partner-hub__faq-toggle');
            if (openButton) {
                openButton.setAttribute('aria-expanded', 'false');
            }
        });
        if (willOpen) {
            item.classList.add('is-open');
            button.setAttribute('aria-expanded', 'true');
        }
    });
})();
</script>
@endsection
