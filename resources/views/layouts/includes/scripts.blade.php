{{-- <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script> --}}

{{-- <script>
    tinymce.init({
        selector: 'textarea#editor',
        menubar: false
    });
</script> --}}

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
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>

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
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBiGuDOg_5yhHeoRz-7bIkc9T1egi1fA7Q&libraries=places,geocoder"></script>
<script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>


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
    function initialize() {
        var input = document.getElementById('searchPlace');
        var autocomplete = new google.maps.places.Autocomplete(input);
        google.maps.event.addListener(autocomplete, 'place_changed', function () {
            var place = autocomplete.getPlace();
            document.getElementById('LocationLat').value = place.geometry.location.lat();
            document.getElementById('LocationLng').value = place.geometry.location.lng();
        });
    }

    window.addEventListener('load', initialize);
</script>