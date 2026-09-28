{{--
    Shared body of the tour checkout and the reschedule page. One DOM, two independent layouts
    (resources/sass/page/_tour-checkout.scss):
    - desktop (>= 1024px): main column + sticky summary aside;
    - mobile (< 1024px): the aside wrappers collapse (display: contents) and the blocks are
      reordered into the single-column flow, with a sticky total bar at the bottom.
    Nothing is duplicated, so form fields, refs and the reCAPTCHA widget exist exactly once.

    Expects: $checkout (TourCheckoutViewModel). Optional: $title, $steps, $intro (partial name),
    $introData, $calendarNote, $submitLabel, $submitShortLabel, $withCaptcha.
--}}
@php($partials = 'pages.modern-checkout.partials')

<div class="tc" x-data="tourCheckout">
    <div class="tc__container">
        <div class="tc__grid">
            @include($partials.'.head', [
                'title' => $title ?? __('checkout.booking_request'),
                'steps' => $steps ?? ['send' => 'checkout.tour.step_send', 'clock' => 'checkout.tour.step_confirm', 'coin' => 'checkout.tour.step_pay'],
                'intro' => $intro ?? null,
                'introData' => $introData ?? [],
            ])

            @include($partials.'.calendar', ['checkout' => $checkout, 'note' => $calendarNote ?? null])

            @include($checkout->isReschedule() ? $partials.'.contact-locked' : $partials.'.contact', ['checkout' => $checkout])

            @include($partials.'.payment', ['checkout' => $checkout])

            <div class="tc__aside">
                @include($partials.'.product', ['product' => $checkout->product()])

                <div class="tc__summary">
                    <p class="tc__summary-meta" x-text="selectedLabel + ' · ' + participantsLabel"></p>

                    @include($partials.'.guests', ['checkout' => $checkout])

                    @include($partials.'.pricing', ['checkout' => $checkout])

                    @include($partials.'.cta', [
                        'submitLabel' => $submitLabel ?? __('checkout.tour.submit'),
                        'withCaptcha' => $withCaptcha ?? true,
                    ])
                </div>
            </div>
        </div>
    </div>

    @include($partials.'.mobile-bar', ['submitShortLabel' => $submitShortLabel ?? __('checkout.tour.submit_short')])
</div>

<script type="application/json" id="tour-checkout-config">@json($checkout->clientConfig())</script>
