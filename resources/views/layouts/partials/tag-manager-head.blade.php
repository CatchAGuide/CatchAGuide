{{--
    Consent defaults + Google Tag Manager. GA4 and Microsoft Clarity are loaded by the GTM container (one GA4
    property and one Clarity project for both domains; GTM tags each hit with site_language from <html lang>) —
    don't add gtag.js or the Clarity snippet here.
    Consent comes from the cookie banner (accept_analytics / accept_advertising); the banner reloads the page
    after a choice, so the defaults below always reflect the current choice.
    The config() default covers a production config cache built before this key existed (that cache once
    rendered no GTM at all); an explicitly empty GTM_CONTAINER_ID still turns tracking off.
--}}
@php($gtmContainerId = config('services.google_tag_manager.container_id', 'GTM-K6VGF9NQ'))
@if ($gtmContainerId)
    <script>
        (function (w, d) {
            function accepted(name) {
                return d.cookie.split('; ').indexOf(name + '=true') !== -1;
            }
            var analytics = accepted('accept_analytics') ? 'granted' : 'denied';
            var ads = accepted('accept_advertising') ? 'granted' : 'denied';

            w.dataLayer = w.dataLayer || [];
            w.gtag = w.gtag || function () { w.dataLayer.push(arguments); };
            w.gtag('consent', 'default', {
                analytics_storage: analytics,
                ad_storage: ads,
                ad_user_data: ads,
                ad_personalization: ads
            });

            // Queued until GTM loads Clarity; without it EU visitors get cookieless one-page sessions.
            w.clarity = w.clarity || function () { (w.clarity.q = w.clarity.q || []).push(arguments); };
            w.clarity('consentv2', { analytics_Storage: analytics, ad_Storage: ads });
        })(window, document);
    </script>
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer',@json($gtmContainerId));</script>
@endif
