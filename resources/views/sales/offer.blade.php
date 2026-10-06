{{-- Customer offer / booking page behind the public token (spec §8.2). No login, noindex. --}}
<x-mail.cag-shell :title="$doc['title']" :reason="__('sales.customer.footer_reason', ['site' => $site], $doc['locale'])" :home-url="$homeUrl" :imprint-url="$imprintUrl" :privacy-url="$privacyUrl" color-scheme="light">
    <x-slot:head>
        <meta name="robots" content="noindex, nofollow">
        <meta name="referrer" content="no-referrer">
    </x-slot:head>
    <x-slot:styles>
        <style>
            .cag-accept summary::-webkit-details-marker { display: none; }
            .cag-accept[open] > summary { display: none !important; }
            [data-accept-terms].is-missing { background: #FBE9E9; color: #8E2424; }
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
