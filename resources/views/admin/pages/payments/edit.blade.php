@extends('admin.layouts.app')

@section('title', 'Zahlung #' . $payment->id . ' anschauen')

@section('content')
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">@yield('title')</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Verwaltung</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.payments.index')}}">Zahlungen</a></li>
                        <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                    </ol>
                </div>

            </div>
            <!-- PAGE-HEADER END -->
            <!-- Row -->
            <div class="row">
                @if($payment->is_completed == 0)
                    <div class="alert alert-light" role="alert">
                        Diese Zahlung wurde zur Auszahlung angefragt!
                    </div>
                @else
                    <div class="alert alert-success" role="alert">
                        Diese Zahlung wurde bereits freigegegeben!
                    </div>
                @endif

                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Kunde</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">

                                <div class="col-lg-4 col-md-12">
                                    <div class="form-group">
                                        <label for="firstname">Vorname</label>
                                        <input type="text" class="form-control" id="firstname" name="firstname" placeholder="Vorname" value="{{ $payment->user->firstname }}">
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-12">
                                    <div class="form-group">
                                        <label for="lastname">Nachname</label>
                                        <input type="text" class="form-control" id="lastname" name="lastname" placeholder="Nachname" value="{{ $payment->user->lastname }}">
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-12">
                                    <div class="form-group">
                                        <label for="email">Email Adresse</label>
                                        <input type="email" class="form-control" id="email"  name="email" placeholder="Email Adresse" value="{{ $payment->user-> email}}">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <!-- End Row -->
            @if($payment->is_completed == 0 )
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">beantragte Auszahlung {{$payment->id}} aktueller Kontostand: {{two($payment->user->balance)}} €</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6 col-md-6">
                                        <a href="{{route('admin.payments.aproveoutpayments', $payment->id)}}">
                                            <div class="alert alert-success" role="alert">
                                                bestätige angefragter Betrag {{two($payment->user->pending_balance)}} € <i class="side-menu__icon fe fe-check"></i>
                                            </div>
                                        </a>

                                    </div>
                                    <div class="col-lg-6 col-md-6">
                                        <a href="{{route('admin.payments.deletepayments', $payment->id)}}">
                                            <div class="alert alert-danger" role="alert">
                                                lehne angefragten Betrag ab {{two($payment->user->pending_balance)}} € <i class="side-menu__icon fe fe-crosshair"></i>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div
            @else
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header alert-success">
                                <h3 class="card-title">beantragte Auszahlung {{two($payment->amount)}} € wurde erfolgreich ausgezahlt!</h3>
                            </div>
                        </div>
                    </div>
                </div
            @endif
        </div>
        <!-- CONTAINER CLOSED -->

    </div>
@endsection
