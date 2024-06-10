<script>
    var gaProperty = 'G-FEQEC651H4',

        disableStr = 'ga-disable-' + gaProperty;

    if (document.cookie.indexOf(disableStr + '=true') > -1) {

        window[disableStr] = true;

    }

    function gaOptout()

    {

        document.cookie = disableStr + '=true; expires=Thu, 31 Dec 2099 23:59:59 UTC; path=/';

        window[disableStr] = true;

    }

    function gaOptin()

    {

        window[disableStr] = false;

        document.cookie = disableStr + '=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/';

        console.log('test');

    }

</script>

<script src="https://cdn.jsdelivr.net/npm/cookieconsent@3/build/cookieconsent.min.js" data-cfasync="false"></script>

<script>
    window.cookieconsent.initialise({
        type: 'opt-in',
        container: document.getElementById("content"),
        palette:{
            popup: {background: "#05223a"},
            button: {background: "#05223a", text: "white"}
        },
        law: {
            regionalLaw: false,
        },
        content: {
            header: 'Wir benutzen Cookies auf unserer Seite!',
            message: 'Diese Seite benutzt Cookies um deine Benutzererfahrung zu verbessern.',
            dismiss: 'Verstanden!',
            allow: 'Cookies akzeptieren!',
            deny: 'Ablehnen',
            link: 'Mehr erfahren',
            href: '{{ route('law.gdpr') }}',
            close: '&#x274c;',
            policy: 'Cookie Policy',
            target: '_blank',
        },
        revokable:true,


        onInitialise: function (status) {
            var type = this.options.type;
            var didConsent = this.hasConsented();
            if (type === 'opt-in' && didConsent) {
                @if(app('env') !== 'local')

                (function (i, s, o, g, r, a, m) {
                    i['GoogleAnalyticsObject'] = r;
                    i[r] = i[r] || function () {
                        (i[r].q = i[r].q || []).push(arguments)
                    }, i[r].l = 1 * new Date();
                    a = s.createElement(o),
                        m = s.getElementsByTagName(o)[0];
                    a.async = 1;
                    a.src = g;
                    m.parentNode.insertBefore(a, m)
                })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');
                ga('create', gaProperty, 'auto', {
                    anonymizeIp: true
                });
                ga('set', 'anonymizeIp', true);
                ga('send', 'pageview');
                gaOptin();
                @endif
            }

        },

        onStatusChange: function (status, chosenBefore) {
            var type = this.options.type;
            var didConsent = this.hasConsented();
            if (type === 'opt-in' && didConsent) {
                @if(app('env') !== 'local')
                (function (i, s, o, g, r, a, m) {
                    i['GoogleAnalyticsObject'] = r;
                    i[r] = i[r] || function () {
                        (i[r].q = i[r].q || []).push(arguments)
                    }, i[r].l = 1 * new Date();
                    a = s.createElement(o),
                        m = s.getElementsByTagName(o)[0];
                    a.async = 1;
                    a.src = g;
                    m.parentNode.insertBefore(a, m)
                })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');
                ga('create', gaProperty, 'auto', {
                    anonymizeIp: true
                });
                ga('set', 'anonymizeIp', true);
                ga('send', 'pageview');
                gaOptin();
                @endif

            }

            if (type === 'opt-in' && !didConsent) {
                gaOptout();
            }
        },

        onRevokeChoice: function () {
            var type = this.options.type;

            if (type === 'opt-in') {
                gaOptout();

            }
        }
    });
</script>
