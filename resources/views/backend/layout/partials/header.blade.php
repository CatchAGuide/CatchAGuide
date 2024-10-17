<!-- space for preloader -->
<header class="site-header header-one" style="background-color: #05223a">
    <div class="top-header">
        <div class="container clearfix">
            <div class="logo-box float-left"  >
                <a href="{{ route('pages.welcome') }}">
                    <img src="{{asset('assets/img/400PngdpiLogoCropped.png') }}" width="200px" height="55px"  alt="">
                </a>
            </div><!-- /.logo-box -->
            <div class="float-right right-contact-block">
                <div class="single-right-contact">
                    <div class="icon-block">
                        <i class="carevan-icon-placeholder"></i><!-- /.Carivon-icon-placeholder -->
                    </div><!-- /.icon-block -->
                    <div class="text-block">
                        <p><span>855 Broklyn Street</span> New York, Usa</p>
                    </div><!-- /.text-block -->
                </div><!-- /.single-right-contact -->
                <div class="single-right-contact">
                    <div class="icon-block">
                        <i class="carevan-icon-phone-call"></i><!-- /.Carivon-icon-placeholder -->
                    </div><!-- /.icon-block -->
                    <div class="text-block">
                        <p><span>666 888 0000</span> Telefon</p>
                    </div><!-- /.text-block -->
                </div><!-- /.single-right-contact -->
                <div class="single-right-contact">
                    <div class="icon-block">
                        <i class="carevan-icon-clock"></i><!-- /.Carivon-icon-placeholder -->
                    </div><!-- /.icon-block -->
                    <div class="text-block">
                        <p><span>Mon-Sat: 8am - 7pm</span> Öffnungszeiten</p>
                    </div><!-- /.text-block -->
                </div><!-- /.single-right-contact -->
                <div class="single-right-contact">
                    <a href="{{route('pages.contact')}}" class="header-btn">Kontaktieren</a><!-- /.header-btn -->
                </div><!-- /.single-right-contact -->
            </div><!-- /.float-right -->
        </div><!-- /.container -->
    </div><!-- /.top-header -->
    <nav class="navbar navbar-expand-lg navbar-light header-navigation stricky header-style-one">
        <div class="container clearfix">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="logo-box clearfix">
                <button class="menu-toggler" data-target="#main-nav-bar">
                    <span class="fa fa-bars"></span>
                </button>
            </div><!-- /.logo-box -->

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="main-navigation" id="main-nav-bar">
                <ul class="navigation-box">
                    <li><a href="{{route('admin.backendindex')}}">Fahrzeuge</a></li>
                    <li><a href="{{route('admin.create')}}">Fahrzeug erstellen</a></li>
                    <li><a href="{{route('admin.contactform-index')}}">Kontaktanfragen</a></li>
                    <li></li>
                    <li><a href="{{ url('/logout') }}">Logout </a></li>
                </ul>

            <!-- /.right-side-box -->
        </div>
        <!-- /.container -->
    </nav>
</header><!-- /.site-header -->
