<!DOCTYPE html>
<html lang="{{app()->getLocale()}}">

<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-K6VGF9NQ');</script>
    <!-- End Google Tag Manager -->
    
    <meta name="keywords" content="online catch guide" >
    <meta name="robots" content="INDEX,FOLLOW" >
    @if(count($attributes))
        @foreach($attributes as $attribute)
            @if($attribute->meta_type == 'title')
            <!-- 1 -->
                <title>{{$attribute->content}} - {{ config('app.name') }}</title>
            @else
            <!-- 2 -->
                <title>@yield('title', 'Bitte Title setzen') - {{ config('app.name') }}</title>
            @endif
            
            @if($attribute->meta_type == 'description')
                <meta name="description" content="{{$attribute->content}}">
            @endif

            @if($attribute->meta_type == 'keywords')
                <meta name="keywords" content="{{$attribute->content}}">
            @endif
        @endforeach
    @else
        @if(Request::segment(1) == 'guidings')
            @if(empty($__env->yieldContent('title')))
            <title>Guidings - {{ config('app.name') }}</title>
            <meta name="description" content="{{ config('app.name') }} Guidings">
            @else
            <title>@yield('title', 'Bitte Title setzen') - {{ config('app.name') }}</title>
            <meta name="description" content="{{ config('app.name') }} - @yield('title')">
            @endif
        @else
            @php
            $page_attr = App\Models\PageAttribute::whereDomain(request()->getHost())->whereUri(request()->path())->get();

            $page_title = $page_attr->where('meta_type', 'title')->first();
            $page_meta_desc = $page_attr->where('meta_type', 'description')->first();
            $page_keywords = $page_attr->where('meta_type', 'keywords')->first();
            @endphp

            @if(is_null($page_title))
            <title>@yield('title', 'Bitte Title setzen') - {{ config('app.name') }}</title>
            @else
            <title>@yield('title', 'Bitte Title setzen') - {{ $page_title->content }}</title>
            @endif

            @if(!is_null($page_meta_desc))
            <meta name="description" content="{{ $page_meta_desc->content }}">
            @endif

            @if(!is_null($page_keywords))
            <meta name="keywords" content="{{ $page_keywords->content }}">
            @endif
        @endif
    @endif

    <!-- favicons Icons -->
    @if(app()->getLocale() == 'en')
        <link rel="apple-touch-icon" sizes="180x180" href="https://catchaguide.com/assets/images/favicon.png"/>
        <link rel="icon" type="image/png" sizes="32x32" href="https://catchaguide.com/assets/images/favicon.png"/>
        <link rel="icon" type="image/png" sizes="16x16" href="https://catchaguide.com/assets/images/favicon.png"/>
        <link rel="icon" type="image/png" sizes="48x48" href="https://catchaguide.com/assets/images/favicon.png"/>
    @else
        <link rel="apple-touch-icon" sizes="180x180" href="https://catchaguide.de/assets/images/favicon.png"/>
        <link rel="icon" type="image/png" sizes="32x32" href="https://catchaguide.de/assets/images/favicon.png"/>
        <link rel="icon" type="image/png" sizes="16x16" href="https://catchaguide.de/assets/images/favicon.png"/>
        <link rel="icon" type="image/png" sizes="48x48" href="https://catchaguide.de/assets/images/favicon.png"/>

    @endif
  
    <!-- HTML TAGS -->

    <link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}" />
    @yield('share_tags')
    @yield('custom_style')
    <style>
        #cookie-consent-banner {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(20, 20, 20, 0.5); /* Semi-transparent black overlay */
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 999; /* Adjust the z-index as needed to ensure it overlays other content */
        }   

        .consent-preferences{
            display: none;
        }
        .header {
            margin-bottom: 5rem;
        }
        #global-search {
            border: 1px solid #ccc;
            bottom: -54px;
        }
        #global-search .global-search-row {
            padding-right: 0 !important;
        }
        #global-search input,
        #global-search select,
        #global-search button {
            border:2px solid #E8604C!important;
        }
        #global-search .fa {
            color: #E8604C !important;
        }

        #global-search .myselect2{
            border:2px solid #E8604C !important;
            padding:2px 0px;
            border-width: 2px !important;
            height: 40px;
        }
        #global-search .myselect2 li.select2-selection__choice{
                background-color: #313041 !important;
                color: #fff !important;
                border: 0 !important;
                font-size:14px;
                vertical-align: middle !important;
                margin-top:0 !important;
             
        }
        #global-search .myselect2 button.select2-selection__choice__remove{
            border: 0 !important;
            color: #fff !important;
        }
        #global-search .select2-selection{
            height: 36px;
        }
        /*#global-search .myselect2 .select2-container {
            padding-top: 5px !important;
        }*/
        #global-search .myselect2 .selection {
            line-height: 17px;
        }
        .new-filter-btn{
            background-color:#E8604C!important;
            color:#fff!important;
        }
        .new-filter-btn:hover{
            background-color:#313041!important;
        }

        /*#mobileherofilter .new-filter-btn{
            background-color:#E8604C;
            color:#fff;
        }
        #mobileherofilter .new-filter-btn:hover{
            background-color:#313041;
        }*/
        .navbar-custom {
            /* padding-bottom: 40px!important;  */
            /* border-bottom: 70px solid var(--thm-black)!important; */
        }
        .header-login-link,
        .header-signup-link {
            color: #000!important;
        }
        .page-header__bottom .container {
            max-width: 1600px;
        }
    </style>

    @include('layouts.includes.styles')

    @if(app()->getLocale() == 'en')
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-XCZ8HKR8Y5"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-XCZ8HKR8Y5');
    </script>
    @endif

    @if(app()->getLocale() == 'de')
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-SYZ9VBYH3S"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-SYZ9VBYH3S');
    </script>
    @endif

</head>

<body>

  
<!-- /.preloader -->
<div class="page-wrapper">
  
    @include('layouts.partials.newheader-short')

    @yield('content')

    @include('layouts.partials.footer')

</div><!-- /.page-wrapper -->


<div class="mobile-nav__wrapper">
    <div class="mobile-nav__overlay mobile-nav__toggler"></div>
    <!-- /.mobile-nav__overlay -->
    <div class="mobile-nav__content">
        <span class="mobile-nav__close mobile-nav__toggler"><i class="fa fa-times"></i></span>

        <div class="logo-box">
            <a href="{{ route('welcome') }}" aria-label="logo image"><img src="{{ asset('assets/images/logo/CatchAGuide2_Logo_PNG.png') }}" width="155" alt=""/></a>
        </div>
        <!-- /.logo-box -->
        <div class="mobile-nav__container"></div>
        <!-- /.mobile-nav__container -->

        <ul class="mobile-nav__contact list-unstyled">
            <li>
                <i class="fa fa-envelope"></i>
                <a href="mailto:info.catchaguide@gmail.com">info.catchaguide@gmail.com</a>
            </li>
            <li>
                <i class="fa fa-phone-alt"></i>
                <a href="tel:+49{{env('CONTACT_NUM')}}">+49 (0) {{env('CONTACT_NUM')}}</a>
            </li>
        </ul><!-- /.mobile-nav__contact -->
        <div class="mobile-nav__top">
            <div class="mobile-nav__social">
                <a href="https://www.facebook.com/CatchAGuide" class="fab fa-facebook-square"></a>
                <a href="https://wa.me/+49{{env('CONTACT_NUM')}}" class="fab fa-whatsapp"></a>
                <a href="https://www.instagram.com/catchaguide_official/" class="fab fa-instagram"></a>
                <div class="language-wrapper">
                    <form action="{{ route('language.switch') }}" method="POST">
                        @csrf
                        <select name="language" class="selectpicker" data-width="fit" onchange="this.form.submit()">
                            @foreach (config('app.locales') as $key => $locale)
                            <option  value="{{ $locale }}" data-content='<span class="fi fi-{{$key}}"></span>' {{ app()->getLocale() == $locale ? 'selected' : '' }}></option>
                            @endforeach
                        </select>        
                    </form>
                </div>
            </div><!-- /.mobile-nav__social -->
        </div><!-- /.mobile-nav__top -->

    </div>
    <!-- /.mobile-nav__content -->
</div>
<!-- /.mobile-nav__wrapper -->

<div class="search-popup">
    <div class="search-popup__overlay search-toggler"></div>
    <!-- /.search-popup__overlay -->
    <div class="search-popup__content">
        <form action="#">
            <label for="search" class="sr-only">search here</label><!-- /.sr-only -->
            <input type="text" id="search" placeholder="Search Here..."/>
            <button type="submit" aria-label="search submit" class="thm-btn">
                <i class="icon-magnifying-glass"></i>
            </button>
        </form>
    </div>
    <!-- /.search-popup__content -->
</div>
<!-- /.search-popup -->

<div id="cookie-consent-banner">
    <div class="bg-white p-4 shadow-lg card border-0 rounded-0" style="max-width:30rem">
        <div class="mb-2">
            <h4 class="fw-bolder">@lang('cookie.header')</h4>
        </div>
        <div class="consent-main" style="font-size:16px;">
            <div class="my-2 text-dark text-justify">
                @if(app()->getLocale() == 'en')
                <p>We use cookies to enhance your browsing experience, serve personalized ads or content, and analyze our traffic.<br>
                     By clicking <strong>"Yes"</strong>, you consent to our use of cookies.
                        <a class="color-primary" href="{{route('law.data-protection')}}"><u>Read Our Cookie Policy</u></a>
                </p>
                @elseif(app()->getLocale() == 'de')
                <p>Wir verwenden Cookies, um Ihr Browsing-Erlebnis zu verbessern, personalisierte Werbung oder Inhalte bereitzustellen und unseren Datenverkehr zu analysieren.<br>
                    Wenn Sie auf <strong>"Ja"</strong> klicken, stimmen Sie unserer Verwendung von Cookies zu.
                       <a class="color-primary" href="{{route('law.data-protection')}}"><u>Lesen Sie unsere Cookie-Richtlinie</u></a>
               </p>
                @endif
            </div>
    
            <div class="d-flex">
                <div class="me-1">
                    <button id="cookie-accept" class="btn theme-primary rounded-0">@lang('cookie.yes-btn')</button>
                </div>
                <div class="mx-1">
                    <button id="cookie-decline" class="btn theme-primary rounded-0">@lang('cookie.no-btn')</button>
                </div>
                <div class="mx-1">
                    <button id="cookie-adjust-preferences" class="btn theme-primary rounded-0">@lang('cookie.few-btn')</button>
                </div>
            </div>
        </div>
        <div class="consent-preferences text-dark my-2">
            <form id="cookie-preferences-form" style="font-size:16px;">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="functional-cookie" checked disabled>
                    <label for="functional-cookie"><strong>@lang('cookie.functional')</strong></label>
                    <p><small>@lang('cookie.functional-msg')</small></p>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="analytics-cookie">
                    <label class="form-check-label" for="analytics-cookie"><strong>@lang('cookie.analytical')</strong></label>
                    <p><small>@lang('cookie.analytical-msg')</small></p>
                    
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="advertising-cookie">
                    <label class="form-check-label" for="advertising-cookie"><strong>@lang('cookie.advertising')</strong></label>
                    <p><small>@lang('cookie.advertising-msg')</small></p>
                </div>
            </form>
            <div class="text-center">
                <button id="preference-close" type="button" class="btn btn-dark rounded-0">@lang('cookie.close-btn')</button>
                <button id="save-cookie-preferences" type="button" class="btn theme-primary rounded-0">@lang('cookie.save-preference-btn')</button>
  
            </div>

        </div>

    </div>

</div>



<!-- Modal -->
<div class="modal fade" id="guideapplicationmodal" tabindex="-1" aria-labelledby="guideapplicationmodalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div class="row">
                    <button type="button" class="btn-close" style="padding-right: 40px; padding-bottom: 20px"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                    <h4 class="modal-title" id="exampleModalLabel">@lang('message.modal-title')</h4>
                    <p>@lang('message.modal-message')
                    </p>
                </div>

            </div>
            <form action="{{route('guide')}}" method="post">
                @csrf

                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 px-5">
                            <div class="form-group ">
                                <label for="firstname"><b>@lang('modal-firstname')</b></label>
                                <input type="text" class="form-control" id="firstname" name="firstname"
                                       placeholder="Vorname">
                            </div>
                        </div>
                        <div class="col-lg-12 col-md-12 px-5">
                            <div class="form-group">
                                <label for="lastname"><b>@lang('modal-lastname')</b></label>
                                <input type="text" class="form-control" id="lastname" name="lastname"
                                       placeholder="Nachname">
                            </div>
                        </div>
                        <div class="col-lg-12 col-md-12 px-5">
                            <div class="form-group">
                                <label for="email"><b>Email</b></label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Email">
                            </div>
                        </div>
                        <div class="col-lg-12 col-md-12 px-5">
                            <div class="form-group">
                                <label for="country"><b>Land</b></label>
                                <input type="text" class="form-control" id="country" name="country" placeholder="Land">
                            </div>
                        </div>
                        <div class="col-lg-12 col-md-12 mb-4 px-5">
                            <div class="form-group">
                                <label for="state"><b>Bundesland</b></label>
                                <select name="state" id="state" class="form-control">
                                    <option value="Bitte auswählen">Bitte auswählen</option>
                                    <option value="Schleswig-Holstein">Schleswig-Holstein</option>
                                    <option value="Hamburg">Hamburg</option>
                                    <option value="Niedersachsen">Niedersachsen</option>
                                    <option value="Bremen">Bremen</option>
                                    <option value="Nordrhein-Westfalen">Nordrhein-Westfalen</option>
                                    <option value="Hessen">Hessen</option>
                                    <option value="Rheinland-Pfalz">Rheinland-Pfalz</option>
                                    <option value="Baden-Württemberg">Baden-Württemberg</option>
                                    <option value="Bayern">Bayern</option>
                                    <option value="Saarland ">Saarland</option>
                                    <option value="Berlin ">Berlin</option>
                                    <option value="Brandenburg ">Brandenburg</option>
                                    <option value="Mecklenburg-Vorpommern">Mecklenburg-Vorpommern</option>
                                    <option value="Sachsen">Sachsen</option>
                                    <option value="Sachsen-Anhalt">Sachsen-Anhalt</option>
                                    <option value="Thüringen">Thüringen</option>
                                </select>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="defaultCheck1" required>
                            <label class="form-check-label" for="defaultCheck1" >
                                <a href="{{route('law.data-protection')}}">@lang('message.privacy-policy') </a> @lang('message.and') <a
                                    href="{{route('law.agb')}}">@lang('message.terms')</a>
                            </label>
                        </div>
                        <button type="submit" class="thm-btn">Senden</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


@include('layouts.includes.scripts')
<script>
    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(function (position) {
            const latitude = position.coords.latitude;
            const longitude = position.coords.longitude;
    
            // Send the location data to your Laravel controller
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            console.log(latitude);
            console.log(longitude);
            $.ajax({
                type: "POST",
                url: "{{ route('user.location') }}", // Use the named route
                data: { latitude: latitude, longitude: longitude },
                success: function (response) {
                    console.log("Location data sent successfully.");
                    // Handle the nearest listings data here
                    $('#nearest-listing').removeClass('d-none');
                    displayNearestListings(response);
                },
                error: function (error) {
                    console.error("Error sending location data: " + error.statusText);
                },
            });
        });
    } else {
        console.error("Geolocation is not available in this browser.");
    }
    function displayNearestListings(nearestListings) {
    const listingsContainer = document.getElementById("nearest-listings-container");

    // Check if the container element exists
    if (listingsContainer) {
        const listingCarousel = document.createElement("div");
        @if($agent->ismobile())
        listingCarousel.className = "new-custom-owl owl-carousel owl-theme";
        @else
        listingCarousel.className = "custom-owl owl-carousel owl-theme";
        @endif
        listingsContainer.appendChild(listingCarousel);

        nearestListings.forEach(function (listing) {
            const listingElement = document.createElement("div");
            listingElement.className = "item";

            const listingCard = document.createElement("div");
            listingCard.className = "card";
            listingCard.style.minHeight = "340px";

            const imgElement = document.createElement("img");
            imgElement.src = listing.image_url;
            imgElement.className = "card-img-top";

            const cardBody = document.createElement("div");
            cardBody.className = "card-body";

            const h5 = document.createElement("h5");
            h5.className = "crop-text-2 card-title h6";
            h5.textContent = listing.title;

            const location = document.createElement("small");
            location.className = "crop-text-1 small-text";
            location.textContent = listing.location;

            const pricewrapper = document.createElement("small");
            pricewrapper.className = "fw-bold";
            pricewrapper.textContent = "@lang('message.from') ";

            const price = document.createElement("span");
            price.className = "color-primary";
            price.textContent = listing.price +'€';


            listingCarousel.appendChild(listingElement);
            listingElement.appendChild(listingCard);
            listingCard.appendChild(imgElement);
            listingCard.appendChild(cardBody);
            cardBody.appendChild(h5);
            cardBody.appendChild(location);
            cardBody.appendChild(pricewrapper);
            pricewrapper.appendChild(price);
        });

        // Initialize Owl Carousel after all the items are added
        @if($agent->ismobile())
        $(listingCarousel).owlCarousel({
            center:true,
            items: 1,
            margin: 10,
            loop:false,
            nav:false,
            dots: false,
            stagePadding: 50,
            responsive: {
                0: {
                    items: 1
                },
                600: {
                    items: 3
                },
                1000: {
                    items: 4
                }
            }
        });
        @else

        $(listingCarousel).owlCarousel({
            loop: true,
            margin: 30,
            nav: false,
            smartSpeed: 500,
            autoHeight: false,
            autoplay: true,
            dots: false,
            autoplayTimeout: 10000,
            
            responsive: {
                0: {
                    items: 1
                },
                600: {
                    items: 3
                },
                1000: {
                    items: 4
                }
            }
        });
        @endif
    }
}
    
</script>
<script>
    function setCookie(name, value, days) {
    var expires = '';
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = '; expires=' + date.toUTCString();
    }
    document.cookie = name + '=' + value + expires + '; path=/';
}

// Function to delete a cookie
function deleteCookie(name) {
    document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
}

// Function to get cookies
function getCookie(name) {
    var nameEQ = name + "=";
    var cookies = document.cookie.split(';');
    for (var i = 0; i < cookies.length; i++) {
        var cookie = cookies[i];
        while (cookie.charAt(0) === ' ') {
            cookie = cookie.substring(1, cookie.length);
        }
        if (cookie.indexOf(nameEQ) === 0) {
            return cookie.substring(nameEQ.length, cookie.length);
        }
    }
    return null;
}

document.addEventListener('DOMContentLoaded', function () {
    // Check if the user has accepted cookies
    var hasAcceptedCookies = getCookie('default_cookie');
    var hasAcceptedAnalytics = getCookie('accept_analytics');
    var hasAcceptedAdvertising = getCookie('accept_advertising');

    // Check if the user has accepted cookies and hide the banner
    if (hasAcceptedCookies) {
        $('#cookie-consent-banner').css('display','none');
    }

    // Show the Cookie Consent Banner if not accepted
    if (!hasAcceptedCookies) {
        $('#cookie-consent-banner').fadeIn(2000).css('display','flex');
    }

    if(hasAcceptedAnalytics){
        <?php /*
        // window.dataLayer = window.dataLayer || [];
        // function gtag(){dataLayer.push(arguments);}
        // gtag('js', new Date());
        // @if(app()->getLocale() == 'en')
        //     gtag('config', 'G-XCZ8HKR8Y5');
        //     (function(c,l,a,r,i,t,y){
        //         c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
        //         t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
        //         y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
        //     })(window, document, "clarity", "script", "i9xet5addk");
        // @endif
        //     @if(app()->getLocale() == 'de')
        //     gtag('config', 'G-SYZ9VBYH3S');
        //     (function(c,l,a,r,i,t,y){
        //         c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
        //         t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
        //         y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
        //     })(window, document, "clarity", "script", "iof6zfrxm3");
        // @endif
        */ ?>
    }



    // Handle clicking the Accept button
    $('#cookie-accept').click(function () {
        setCookie('default_cookie', 'true', 365); // Set a cookie for accepting all cookies
        setCookie('accept_analytics', 'true', 365);
        setCookie('accept_advertising', 'true', 365);
        $('#cookie-consent-banner').hide(); // Hide the banner
        window.location.reload();
    });

    // Handle clicking the Decline button
    $('#cookie-decline').click(function () {
        setCookie('default_cookie', 'true'); // Set a cookie for declining all cookies
        $('#cookie-consent-banner').hide(); // Hide the banner
    });

    // Handle clicking the Adjust Preferences button
    $('#cookie-adjust-preferences').click(function () {
        $('#functional-cookie').prop('checked', true); // Default check the functional cookie
        // $('#cookie-preferences-modal').modal('show');
        $('.consent-preferences').css('display','block');
        $('.consent-main').css('display','none')
    });

    $('#preference-close').click(function () {
        $('.consent-main').css('display','block')
        $('.consent-preferences').css('display','none');
    });



    // Initialize the Cookie Preferences Modal
    $('#cookie-preferences-modal').on('shown.bs.modal', function () {
        // Check and set the state of the checkboxes based on user preferences
        if (hasAcceptedAnalytics) {
            $('#analytics-cookie').prop('checked', true);
        }
        if (hasAcceptedAdvertising) {
            $('#advertising-cookie').prop('checked', true);
        }
    });

    // Handle clicking the Save Preferences button in the modal
    $('#save-cookie-preferences').click(function () {
        setCookie('default_cookie', 'true', 365); 
        if ($('#analytics-cookie').prop('checked')) {
            setCookie('accept_analytics', 'true', 365);
        } else {
            deleteCookie('accept_analytics');
        }

        if ($('#advertising-cookie').prop('checked')) {
            setCookie('accept_advertising', 'true', 365);
        } else {
            deleteCookie('accept_advertising');
        }

        // Close the modal
        $('#cookie-consent-banner').css('display','none')
        window.location.reload();
    });

});

</script>
<script type="text/javascript">
    (function(c,l,a,r,i,t,y){
        c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
        t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
        y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
    })(window, document, "clarity", "script", "i9xet5addk");
</script>


</body>

</html>
