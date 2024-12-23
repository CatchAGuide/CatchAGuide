<script src="{{ asset('assets/js/jquery.js') }}" ></script>
{{-- <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_API_KEY') }}"></script> --}}
<script src="{{ asset('assets/js/gmaps.js') }}"></script>
<script src="{{ asset('assets/js/map-helper.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/jquery.magnific-popup.min.js') }}"></script>
<script src="{{ asset('assets/js/owl.carousel.min.js') }}"></script>
<script src="{{ asset('assets/js/isotope.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('assets/js/waypoints.min.js') }}"></script>
<script src="{{ asset('assets/js/jquery.bxslider.min.js') }}"></script>
<script src="{{ asset('assets/js/jquery.counterup.min.js') }}"></script>
<script src="{{ asset('assets/js/wow.min.js') }}"></script>
<script src="{{ asset('assets/js/theme.js') }}"></script>
<script src="//cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="//cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>

<!-- Cookiebanner -->
<script type="text/javascript" src="{{asset('assets/js/custom.js')}}"></script>

<script type="text/javascript">
    (function($){
        "use strict";

        var tpj=jQuery;
        var revapi12;
        tpj(document).ready(function() {
            if(tpj("#rev_slider_12_1").revolution == undefined){
                revslider_showDoubleJqueryError("#rev_slider_12_1");
            }else{
                revapi12 = tpj("#rev_slider_12_1").show().revolution({
                    sliderType:"standard",
                    sliderLayout:"fullwidth",
                    dottedOverlay:"none",
                    delay:9000,
                    navigation: {
                        keyboardNavigation:"off",
                        keyboard_direction: "horizontal",
                        mouseScrollNavigation:"off",
                        mouseScrollReverse:"default",
                        onHoverStop:"off",
                        arrows: {
                            style:"hermes",
                            enable:true,
                            hide_onmobile:false,
                            hide_onleave:true,
                            hide_delay:200,
                            hide_delay_mobile:1200,
                            tmp:'<div class="tp-arr-allwrapper">	<div class="tp-arr-imgholder"></div>	<div class="tp-arr-titleholder">Mehr</div>	</div>',
                            left: {
                                h_align:"left",
                                v_align:"center",
                                h_offset:20,
                                v_offset:0
                            },
                            right: {
                                h_align:"right",
                                v_align:"center",
                                h_offset:20,
                                v_offset:0
                            }
                        }
                    },
                    visibilityLevels:[1240,1024,778,480],
                    gridwidth:1920,
                    gridheight:860,
                    lazyType:"none",
                    shadow:0,
                    spinner:"spinner3",
                    stopLoop:"off",
                    stopAfterLoops:-1,
                    stopAtSlide:-1,
                    shuffle:"off",
                    autoHeight:"off",
                    disableProgressBar:"on",
                    hideThumbsOnMobile:"off",
                    hideSliderAtLimit:0,
                    hideCaptionAtLimit:0,
                    hideAllCaptionAtLilmit:0,
                    debugMode:false,
                    fallbacks: {
                        simplifyAll:"off",
                        nextSlideOnWindowFocus:"off",
                        disableFocusListener:false,
                    }
                });
            }
        });
    })(jQuery);
</script>
@include('layout.content.cookie-consent')

@yield('js_after')

