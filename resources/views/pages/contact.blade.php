@extends('layout.app')

@section('title', __('message.contact'))

@section('content')

    <div class="quote-area">
        <div class="container-fluid">
            <div class="row no-gutters">
                <div class="col-xl-4 bg1 d-flex" style="background-color:#05223a">
                    <div class="content-box my-auto">
                        <div class="text-white text-center" >
                            <h2 class="f1" style="border: 1px solid">@lang('contact.writeUs') Sie sind auf der Suche nach dem richtigen Wohnmobil? <br /> Wir helfen Ihnen.</h2>
                            <div class="call-us text-center bg-white">
                                <p class="fw-7 c1 text-uppercase" style="color:#05223a ">Rufen Sie uns an.</p>
                                <h1 class="fw-3 c3">666 888 0000</h1>
                            </div><!-- /.call-us -->
                        </div>
                    </div><!-- /.content-box -->
                </div><!-- /.col-lg-4 -->
                <div class="col-xl-8 quote-form-wrapper">
                    <div class="row justify-content-xl-end justify-content-center">
                        <div class="col-xl-11 pl-30 pr-0">
                            <div class="quote-form">
                                <div class="thm-header text-center" style="color:#05223a">
                                    <p class="c1 pb-10" style="color:#05223a">Interesse geweckt?</p>
                                    <h1 class="c3">Fragen Sie uns gerne nach einem Angebot</h1>
                                </div><!-- /.thm-header -->
                                @include('includes.forms.contactform')
                            </div><!-- /.quote-form -->
                        </div><!-- /.col-11 -->
                    </div><!-- /.row -->
                </div><!-- /.col-lg-8 -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>



@endsection
