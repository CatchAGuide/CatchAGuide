<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/vendors/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/vendors/jarallax/jarallax.min.js') }}"></script>
<script src="{{ asset('assets/vendors/jquery-ajaxchimp/jquery.ajaxchimp.min.js') }}"></script>
<script src="{{ asset('assets/vendors/jquery-appear/jquery.appear.min.js') }}"></script>
<script src="{{ asset('assets/vendors/jquery-circle-progress/jquery.circle-progress.min.js') }}"></script>
<script src="{{ asset('assets/vendors/jquery-magnific-popup/jquery.magnific-popup.min.js') }}"></script>
<script src="{{ asset('assets/vendors/jquery-validate/jquery.validate.min.js') }}"></script>
<script src="{{ asset('assets/vendors/nouislider/nouislider.min.js') }}"></script>
{{-- <script src="{{ asset('assets/vendors/odometer/odometer.min.js') }}"></script> --}}
<script src="{{ asset('assets/vendors/swiper/swiper.min.js') }}"></script>
<script src="{{ asset('assets/vendors/tiny-slider/tiny-slider.min.js') }}"></script>
<script src="{{ asset('assets/vendors/wnumb/wNumb.min.js') }}"></script>
<script src="{{ asset('assets/vendors/wow/wow.js') }}"></script>
<script src="{{ asset('assets/vendors/isotope/isotope.js') }}"></script>
<script src="{{ asset('assets/vendors/countdown/countdown.min.js') }}"></script>
<script src="{{ asset('assets/vendors/owl-carousel/owl.carousel.min.js') }}"></script>
<script src="{{ asset('assets/vendors/twentytwenty/twentytwenty.js') }}"></script>
<script src="{{ asset('assets/vendors/twentytwenty/jquery.event.move.js') }}"></script>
<script src="{{ asset('assets/vendors/bxslider/jquery.bxslider.min.js') }}"></script>
<script src="{{ asset('assets/vendors/bootstrap-select/js/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('assets/vendors/vegas/vegas.min.js') }}"></script>
<script src="{{ asset('assets/vendors/jquery-ui/jquery-ui.js') }}"></script>
<script src="{{ asset('assets/vendors/timepicker/timePicker.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js" integrity="sha512-qTXRIMyZIFb8iQcfjXWCO8+M5Tbc38Qi5WzdPOYZHIlZpzBHG3L3by84BBBOiRGiEb7KKtAOAs5qYdUiZiQNNQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/locales-all.min.js"></script>

<script src="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-star-rating@4.1.2/js/star-rating.min.js" type="text/javascript"></script>

<script src="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-star-rating@4.1.2/themes/krajee-svg/theme.js"></script>

<script src="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-star-rating@4.1.2/js/locales/LANG.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js" integrity="sha512-VEd+nq25CkR676O+pLBnDW09R7VQX9Mdiij052gVCp5yVH3jGtH70Ho/UUv4mJDsEdTvqRCFZg0NKGiojGnUCw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
{{-- <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> --}}
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/masonry-layout@4.2.2/dist/masonry.pkgd.min.js" integrity="sha384-GNFwBvfVxBkLMJpYMOABq3c+d3KnQxudP/mGPkzpZSTYykLBNsZEnG2D9G/X/+7D" crossorigin="anonymous" async></script>
<script src="/js/app.js"></script>

<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places,geocoding,marker&loading=async"></script>
@include('layouts.includes.maps-utils')
@stack('js_push')

<script>
    @if(Session::has('message'))
        toastr.options =
        
        {
            "closeButton" : true,
            "progressBar" : true
        }
    toastr.success("{{ session('message') }}");
    @endif

        @if(Session::has('error'))
        toastr.options =
        {
            "closeButton" : true,
            "progressBar" : true
        }
    toastr.error("{{ session('error') }}");
    @endif

        @if(Session::has('info'))
        toastr.options =
        {
            "closeButton" : true,
            "progressBar" : true
        }
    toastr.info("{{ session('info') }}");
    @endif

        @if(Session::has('warning'))
        toastr.options =
        {
            "closeButton" : true,
            "progressBar" : true
        }
    toastr.warning("{{ session('warning') }}");
    @endif
</script>

<!-- template js -->
<script src="{{ asset('assets/js/tevily.js') }}"></script>
<script type="module" src="{{ asset('js/app.js') }}"></script>
<script>
    function changeGuestCount(type, value) {
        var input = document.getElementById(type);
        var currentValue = parseInt(input.value);
        var newValue = currentValue + value;

        if (newValue >= 0) {
            input.value = newValue;
            updateGuestText();
        }
    }

    function updateGuestText() {
        var adults = document.getElementById('adults').value;
        var children = document.getElementById('children').value;
        var text = adults + " adult" + (adults > 1 ? "s" : "") + " • " + children + " child" + (children > 1 ? "ren" : "");
        document.getElementById('guestDropdown').innerText = text;
    }
</script>
@livewireScripts
@yield('js_after')
@stack('js_push')

<script>
    // asdffff
     var selectTarget = $('#home_target_fish');
      $("#home_target_fish").select2({
        multiple: true,
        placeholder: '@lang('message.target-fish')',
        width: 'resolve', // need to override the changed default
    });

    @foreach($alltargets as $target)
        var targetname = '{{$target->name}}';

        @if(app()->getLocale() == 'en')
        targetname = '{{$target->name_en}}'
        @endif

        var targetOption = new Option(targetname, '{{ $target->id }}');

        selectTarget.append(targetOption);

        @if(request()->get('target_fish'))
            @if(in_array($target->id, request()->get('target_fish')))
            $(targetOption).prop('selected', true);
            @endif
        @endif


    @endforeach
  
    // Trigger change event to update Select2 display
    selectTarget.trigger('change');
</script>
<script>
    // Helper: run a callback once the Google Maps JS API is ready
    function runWhenGoogleMapsReady(callback, maxAttempts = 50, interval = 200) {
        let attempts = 0;
        const timer = setInterval(function () {
            if (window.google && google.maps) {
                clearInterval(timer);
                callback();
            } else if (++attempts >= maxAttempts) {
                clearInterval(timer);
                console.warn('Google Maps JS API not available after waiting – skipping callback.');
            }
        }, interval);
    }

    // Config for mobile search modal (searchPlace) – initialized only when modal is shown so dropdown works on mobile
    const searchModalPlaceConfig = {
        input: 'searchPlace',
        lat: 'LocationLat',
        lng: 'LocationLng',
        city: 'LocationCity',
        country: 'LocationCountry',
        region: 'LocationRegion'
    };

    // Inputs that are visible on load (not inside a hidden modal) – init on page load
    const searchInputsOnLoad = [
        { input: 'searchPlaceMobile', lat: 'LocationLatMobile', lng: 'LocationLngMobile', city: 'LocationCityMobile', country: 'LocationCountryMobile', region: 'LocationRegionMobile' },
        { input: 'searchPlaceDesktop', lat: 'LocationLatDesktop', lng: 'LocationLngDesktop', city: 'LocationCityDesktop', country: 'LocationCountryDesktop', region: 'LocationRegionDesktop' },
        { input: 'searchPlaceHeaderDesktop', lat: 'LocationLatHeaderDesktop', lng: 'LocationLngHeaderDesktop', city: 'LocationCityHeaderDesktop', country: 'LocationCountryHeaderDesktop', region: 'LocationRegionHeaderDesktop' },
        { input: 'searchPlaceShortDesktop', lat: 'LocationLatShortDesktop', lng: 'LocationLngShortDesktop', city: 'LocationCityShortDesktop', country: 'LocationCountryShortDesktop', region: 'LocationRegionShortDesktop' }
    ];

    function initAutocompleteForConfig(MapsManager, config, callback) {
        if (!callback) {
            callback = function(place) {
                const locationData = MapsManager.extractLocationData(place);
                const latInput = document.getElementById(config.lat);
                const lngInput = document.getElementById(config.lng);
                const cityInput = document.getElementById(config.city);
                const countryInput = document.getElementById(config.country);
                const regionInput = document.getElementById(config.region);
                if (latInput) latInput.value = locationData.lat;
                if (lngInput) lngInput.value = locationData.lng;
                if (cityInput) cityInput.value = locationData.city || '';
                if (countryInput) countryInput.value = locationData.country || '';
                if (regionInput) regionInput.value = locationData.region || '';
            };
        }
        MapsManager.initAutocomplete(config.input, callback);
    }

    // Initialize search inputs that are visible on page load (excludes searchPlace in modal)
    function initializeGooglePlaces() {
        const MapsManager = window.GoogleMapsManager;
        if (!MapsManager) {
            console.warn('GoogleMapsManager not available – skipping autocomplete init.');
            return;
        }
        searchInputsOnLoad.forEach(function(config) {
            initAutocompleteForConfig(MapsManager, config);
        });
    }

    // Initialize only the mobile search modal Location input (so suggestions show on mobile when modal is visible)
    var searchPlaceModalAutocompleteInited = false;
    function initializeSearchModalPlaces() {
        if (searchPlaceModalAutocompleteInited) return;
        const MapsManager = window.GoogleMapsManager;
        if (!MapsManager || !document.getElementById(searchModalPlaceConfig.input)) return;
        searchPlaceModalAutocompleteInited = true;
        initAutocompleteForConfig(MapsManager, searchModalPlaceConfig);
    }

    // Initialize on page load (but only once Google Maps is actually ready)
    window.addEventListener('load', function () {
        const MapsManager = window.GoogleMapsManager;
        if (MapsManager) {
            MapsManager.waitForGoogleMaps(initializeGooglePlaces);
        } else {
            runWhenGoogleMapsReady(initializeGooglePlaces);
        }
    });

    // When mobile search modal is shown, init Places Autocomplete for searchPlace so suggestions appear (fix for mobile)
    document.addEventListener('DOMContentLoaded', function() {
        const searchModal = document.getElementById('searchModal');
        if (searchModal) {
            searchModal.addEventListener('shown.bs.modal', function () {
                const MapsManager = window.GoogleMapsManager;
                if (MapsManager) {
                    MapsManager.waitForGoogleMaps(initializeSearchModalPlaces);
                } else {
                    runWhenGoogleMapsReady(initializeSearchModalPlaces);
                }
            });
        }
        // Other modals: re-run full init in case they contain other search inputs
        ['mobileMenuModal'].forEach(function(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.addEventListener('shown.bs.modal', function () {
                    const MapsManager = window.GoogleMapsManager;
                    if (MapsManager) {
                        MapsManager.waitForGoogleMaps(initializeGooglePlaces);
                    } else {
                        runWhenGoogleMapsReady(initializeGooglePlaces);
                    }
                });
            }
        });
    });
</script>