{{-- Customer offer / booking page behind the public token (spec §8.2). No login, noindex. --}}
<x-mail.cag-shell :title="$doc['title']" :reason="__('sales.customer.footer_reason', ['site' => $site], $doc['locale'])" :home-url="$homeUrl" :imprint-url="$imprintUrl" :privacy-url="$privacyUrl" color-scheme="light" :max-width="1080">
    <x-slot:head>
        <meta name="robots" content="noindex, nofollow">
        <meta name="referrer" content="no-referrer">
    </x-slot:head>
    <x-slot:styles>
        <style>
            .cag-accept summary::-webkit-details-marker { display: none; }
            .cag-accept[open] > summary { display: none !important; }
            [data-accept-terms].is-missing { background: #FBE9E9; color: #8E2424; }

            /* Phones and the builder preview: one column, in reading order. */
            @media only screen and (max-width: 480px) {
                .cp-wrap { padding: 22px 20px 26px !important; }
                .cp-accept-btn { display: block !important; }
            }

            /* Desktop: content left, total + accept + contact in a column that stays in view. */
            @media only screen and (min-width: 900px) {
                /* The shell card clips with overflow:hidden (rounded corners), which turns off
                   position:sticky; clip keeps the corners without being a scroll container. */
                .card { overflow: clip !important; }
                .cp-wrap { padding: 36px 44px 40px !important; }
                .cp-grid {
                    display: grid;
                    grid-template-columns: minmax(0, 1fr) 340px;
                    column-gap: 40px;
                    grid-template-areas:
                        "intro summary"
                        "products summary"
                        "details summary"
                        "sign summary";
                    grid-template-rows: auto auto auto 1fr;
                }
                .cp-intro { grid-area: intro; }
                .cp-products { grid-area: products; }
                .cp-details { grid-area: details; }
                .cp-sign { grid-area: sign; }
                .cp-summary { grid-area: summary; }
                .cp-summary__inner { position: sticky; top: 24px; }
                .cp-summary__inner > table:first-child { margin-top: 0 !important; }
            }
        </style>
    </x-slot:styles>

    @include('sales.partials.document-body', ['doc' => $doc, 'preview' => false])

    <x-slot:scripts>
        <script>
            // Highlight the terms checkbox instead of submitting without it (no alert dialogs).
            document.querySelectorAll('[data-accept-form]').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    var box = form.querySelector('input[name="terms"]');
                    var label = form.querySelector('[data-accept-terms]');
                    if (box && !box.checked) {
                        event.preventDefault();
                        label.classList.add('is-missing');
                        box.focus();
                    }
                });
            });
        </script>
    </x-slot:scripts>
</x-mail.cag-shell>
