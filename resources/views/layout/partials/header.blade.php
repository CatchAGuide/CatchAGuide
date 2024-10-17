    <!-- space for preloader -->
    <header class="site-header header-one"  style="background-color: #05223a">
        <div class="top-header">
            <div class="container clearfix" >
                <div class="logo-box float-left"  >
                    <a href="{{ route('pages.welcome') }}">
                        <img src="{{asset('assets/img/400PngdpiLogoCropped.png') }}" width="200px" height="55px"  alt="">
                    </a>
                </div><!-- /.logo-box -->
                <div class="float-right right-contact-block">
                    <div class="single-right-contact">
                        <div class="icon-block">
                            <i class="carevan-icon-placeholder" style="color:#05223a!important"></i><!-- /.Carivon-icon-placeholder -->
                        </div><!-- /.icon-block -->
                        <div class="text-block">
                            <p><span>Campingplatz 1</span> 56355 Nastätten</p>
                        </div><!-- /.text-block -->
                    </div><!-- /.single-right-contact -->
                    <div class="single-right-contact">
                        <div class="icon-block">
                            <i class="carevan-icon-phone-call" style="color:#05223a!important"></i><!-- /.Carivon-icon-placeholder -->
                        </div><!-- /.icon-block -->
                        <div class="text-block">
                            <p><span>0160 972 18 180</span> Telefon</p>
                        </div><!-- /.text-block -->
                    </div><!-- /.single-right-contact -->
                    <div class="single-right-contact">
                        <div class="icon-block">
                            <i class="carevan-icon-clock" style="color:#05223a!important"></i><!-- /.Carivon-icon-placeholder -->
                        </div><!-- /.icon-block -->
                        <div class="text-block">
                            <p><span>Mon-Sam: 8 - 19 Uhr</span> Öffnungszeiten</p>
                        </div><!-- /.text-block -->
                    </div><!-- /.single-right-contact -->
                    <div class="single-right-contact">
                        <a style="background-color:#05223a!important" href="{{route('pages.contact')}}" class="header-btn">Kontaktieren</a><!-- /.header-btn -->
                    </div><!-- /.single-right-contact -->
                </div><!-- /.float-right -->
            </div><!-- /.container -->
        </div><!-- /.top-header -->
        <nav class="navbar navbar-expand-lg navbar-light header-navigation stricky header-style-one" style="background-color: #05223a">
            <div class="container clearfix" >
                <!-- Brand and toggle get grouped for better mobile display -->
                <div class="logo-box clearfix">
                    <button class="menu-toggler" id="menu-toggler" data-target="#main-nav-bar">
                        <span class="fa fa-bars"></span>
                    </button>
                </div><!-- /.logo-box -->

                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="main-navigation" id="main-nav-bar" >
                    <ul class="navigation-box">
                        <li class="sub-menu"><a href="{{route('pages.about')}}">Über uns</a></li>
                        <li class="sub-menu"><a  href="{{route('camper.index')}}">Fahrzeuge</a></li>
                        <li class="sub-menu"><a  href="{{route('pages.contact')}}">Kontakt</a></li>
                        <li class="sub-menu"><a  href="{{route('pages.faq')}}">FAQ</a></li>
                        <li class="sub-menu"><a  href="{{route('pages.campingplatz')}}">Campingplatz</a></li>
                        <!--<li class="sub-menu"><a  href="{{route('pages.faq')}}">FAQ</a></li>-->
                    </ul>
                </div><!-- /.navbar-collapse -->
                <div class="right-side-box">
                    <div class="social">
                        <!--
                        <a href="#"><i class="fa fa-twitter"></i></a><a href="#"><i class="fa fa-facebook-f" ></i></a>
                        <a href="#"><i class="fa fa-youtube-play" ></i></a>
                        <a href="#"><i class="fa fa-google-plus"></i></a>
                        -->
                    </div>
                </div>
                <!-- /.right-side-box -->
            </div>
            <!-- /.container -->
        </nav>
    </header><!-- /.site-header -->
