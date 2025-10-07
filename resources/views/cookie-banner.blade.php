<div id="cookie-banner" class="cookie-banner">
    <div class="cookie-banner-content">
        <p>Wir verwenden Cookies, um die Benutzerfreundlichkeit unserer Website zu verbessern. Durch die Nutzung unserer Website erklären Sie sich mit der Verwendung von Cookies einverstanden.</p>
        <button id="accept-cookies" class="cookie-button">Akzeptieren</button>
        <button id="decline-cookies" class="cookie-button">Ablehnen</button>
    </div>
</div>
<script>
    function setCookie(name, value, days, essential = false) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        const expires = "; expires=" + date.toUTCString();
        document.cookie = name + "=" + (value || "") + expires + "; path=/; SameSite=Lax" + (essential ? "; Secure" : "");
    }

    function getCookie(name) {
        const nameEQ = name + "=";
        const ca = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    function loadGoogleMapsScript(src, callback) {
        const script = document.createElement('script');
        script.type = 'text/javascript';
        script.src = src;
        script.async = true;

        if (callback) {
            script.onload = callback;
        }

        document.body.appendChild(script);
    }

    function loadGuidingsGoogleMapsScript() {
        loadGoogleMapsScript('https://maps.googleapis.com/maps/api/js?key='.env('GOOGLE_MAPS_API_KEY').'&libraries=places,geocoding&callback=initMap&v=weekly', null);
    }

    document.addEventListener("DOMContentLoaded", function () {
        const cookieConsent = getCookie("cookie_consent");

        if (cookieConsent === "") {
            document.getElementById("cookie-banner").style.display = "block";
        } else {
            if (cookieConsent === "accepted") {
                loadGuidingsGoogleMapsScript();
            }

            document.getElementById("cookie-banner").style.display = "none";
        }

        document.getElementById("accept-cookies").addEventListener("click", function () {
            setCookie("cookie_consent", "accepted", 30);
            document.getElementById("cookie-banner").style.display = "none";
            loadGuidingsGoogleMapsScript();
        });

        document.getElementById("decline-cookies").addEventListener("click", function () {
            setCookie("cookie_consent", "declined", 30);
            document.getElementById("cookie-banner").style.display = "none";
        });
    });


</script>