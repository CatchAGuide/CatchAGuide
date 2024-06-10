@extends('admin.layouts.app')

@section('title', 'Alle Zahlungen')

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
                        <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                    </ol>
                </div>

            </div>
            <!-- PAGE-HEADER END -->
            <!-- Row -->
            <div class="row row-sm">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h3>Zahlungen</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered text-nowrap border-bottom" id="responsive-datatable">
                                    <thead>
                                    <tr>
                                        <th class="wd-10p border-bottom-0">Transaction-ID</th>
                                        <th class="wd-15p border-bottom-0">Benutzer</th>
                                        <th class="wd-25p border-bottom-0">Typ</th>
                                        <th class="wd-25p border-bottom-0">Euro</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($transactions as $transaction)
                                            <tr>
                                                <td>{{ $transaction->uuid }}</td>
                                                <td>{{ $transaction->payable?->full_name }}</td>
                                                <td>
                                                    @if($transaction->type === 'deposit')
                                                        <span class="badge bg-secondary">Einzahlung</span>
                                                    @else
                                                        <span class="badge bg-danger">Auszahlung</span>
                                                    @endif
                                                </td>
                                                <td>{{ two($transaction->amount) }} €</td>
                                            </tr>
                                        @endforeach

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h3>Auszahlungsanfragen</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered text-nowrap border-bottom" id="responsive-datatable">
                                    <thead>
                                    <tr>
                                        <th class="wd-10p border-bottom-0">Transaction-ID</th>
                                        <th class="wd-15p border-bottom-0">Benutzer</th>
                                        <th class="wd-25p border-bottom-0">Typ</th>
                                        <th class="wd-25p border-bottom-0">Euro</th>
                                        <th class="wd-25p border-bottom-0">Status</th>
                                        <th class="wd-25p border-bottom-0">Aktion</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($payments as $transaction)
                                        <tr>
                                            <td>{{ $transaction->id }}</td>
                                            <td>{{ $transaction->user->full_name }}</td>
                                            <td>
                                                @if($transaction->type === 'deposit')
                                                    <span class="badge bg-secondary">Einzahlung</span>
                                                @else
                                                    <span class="badge bg-danger">Auszahlung</span>
                                                @endif
                                            </td>
                                            <td>{{ two($transaction->amount) }} €</td>
                                            <td>
                                                @if($transaction->is_completed === 1)
                                                    freigegeben
                                                @else
                                                    angefragt
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{route('admin.payments.showoutpayments', $transaction->id)}}"><i class="side-menu__icon fe fe-search"></i></a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Row -->
        </div>
        <!-- CONTAINER CLOSED -->

    </div>
@endsection
