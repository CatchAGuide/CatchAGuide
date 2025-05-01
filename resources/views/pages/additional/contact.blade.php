@extends('layouts.app-v2-1')

@section('title', __('message.contact'))

@section('content')
    {!! ReCaptcha::htmlScriptTagJsApi() !!}
    <!--Page Header Start-->
    <!-- <section class="page-header">
        <div class="page-header__top">
            <div class="page-header-bg" style="background-image: url({{asset('assets/images/Titelbild_Kontakt.jpg')}})">
            </div>
            <div class="page-header-bg-overly"></div>
            <div class="container">
                <div class="page-header__top-inner">
                    <h1 class="h2">@lang('message.contact')</h1>
                </div>
            </div>
        </div>
        <div class="page-header__bottom">
            <div class="container">
                <div class="page-header__bottom-inner">
                    <ul class="thm-breadcrumb list-unstyled">
                        <li><a href="{{ route('welcome') }}">@lang('message.home')</a></li>
                        <li><span>&#183;</span></li>
                        <li class="active">@lang('message.contact')</li>
                    </ul>
                </div>
            </div>
        </div>
    </section> -->
    <!--Page Header End-->

    <!--Contact Page Start-->
    <section class="contact-pages">
        <div class="container">
                    <h1 class="h2 mt-5 mb-0">@lang('message.contact')</h1>
            <div class="row">
                <div class="col-12">
                    <div class="contact-page__left">
                        <div class="section-title text-left">
                            <!-- <span class="section-title__tagline">@lang('contact.writeUs')!</span> -->
                            <h3 class="">@lang('contact.shareYourQuestion')</h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="contact-page__right">
                        <div class="comment-form">
                            <form action="{{route('sendcontactmail')}}" method="POST">
                                @method('post')
                                @csrf
                                <div class="row">
                                    <div class="col-xl-6">
                                        <div class="comment-form__input-box">
                                            <input type="text" placeholder="@lang('contact.yourName')" name="name" required>
                                        </div>
                                    </div>
                                    <div class="col-xl-6">
                                        <div class="comment-form__input-box">
                                            <input type="email" placeholder="@lang('contact.email')" name="email" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xl-12">
                                        <div class="comment-form__input-box">
                                            <input type="tel" placeholder="@lang('contact.phone')" name="phone">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xl-12">
                                        <div class="comment-form__input-box">
                                            <textarea name="description" placeholder="@lang('contact.feedback')" required></textarea>
                                        </div>
                                        <div class="submit-container">
                                            {!! htmlFormSnippet() !!}
                                            <button type="submit" class="thm-btn comment-form__btn">@lang('contact.btnSend')</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <div class="result"></div><!-- /.result -->
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mt-3 mt-lg-0">
                    <div class="contact-page__social">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="tel:+49{{env('CONTACT_NUM')}}"><i class="fab fa-whatsapp"></i></a>
                        <a href="https://www.instagram.com/catchaguide_official/"><i class="fab fa-instagram"></i></a>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <!--Information Single-->
                            <div class="information__single">
                                <div class="information__icon">
                                    <span class="icon-place"></span>
                                </div>
                                <div class="information__text">
                                    <p>Düsseldorf <br> NRW</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <!--Information Single-->
                            <div class="information__single">
                                <div class="information__icon">
                                    <span class="icon-phone-call"></span>
                                </div>
                                <div class="information__text">
                                    <h4>
                                        <a href="tel:+49{{env('CONTACT_NUM')}}" class="information__number-1">+49 (0) {{env('CONTACT_NUM')}}</a>

                                    </h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <!--Information Single-->
                            <div class="information__single">
                                <div class="information__icon">
                                    <span class="icon-at"></span>
                                </div>
                                <div class="information__text">
                                    <h4>
                                        <a href="mailto:info.catchaguide@gmail.com" class="information__mail-2">info.catchaguide@gmail.com</a>
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--Contact Page End-->
    <section class="socials-container">
        <div class="container">
            
        </div>
    </section>
    <!--Information Start-->
    <section class="information">
        <div class="container">
           
        </div>
    </section>
    <!--Information End-->
@endsection
