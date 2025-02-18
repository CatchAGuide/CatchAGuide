@extends('admin.layouts.app')

@section('title', 'Alle Buchungen')

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
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered text-nowrap border-bottom" id="booking-datatable">
                                    <thead>
                                    <tr>
                                        <th class="wd-20p border-bottom-0">ID</th>
                                        <th class="wd-20p border-bottom-0">Customer</th>
                                        <th class="wd-15p border-bottom-0">Phone #</th>
                                        <th class="wd-15p border-bottom-0">Price</th>
                                        <th class="wd-15p border-bottom-0">Guide Share</th>
                                        <th class="wd-15p border-bottom-0">CaG Share</th>
                                        <th class="wd-15p border-bottom-0">Booking Date</th>
                                        <th class="wd-15p border-bottom-0">Transaction</th>
                                        <th class="wd-15p border-bottom-0">Status</th>
                                        <th class="wd-15p border-bottom-0">Guide</th>
                                        <th class="wd-15p border-bottom-0">Guiding</th>
                                        <th class="wd-15p border-bottom-0">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($bookings as $booking)
                                            <tr class="{{ $booking->is_guest ? 'bg-warning' : '' }}">
                                                <td>{{ $booking->id }}</td>
                                                <td>
                                                    @if ($booking->user)
                                                        {{ $booking->user->firstname ?? 'Guest' }} {{ $booking->user->lastname ?? '' }}
                                                    @endif
                                                </td>
                                                <td>{{ $booking->phone }}</td>
                                                <td>{{ two($booking->price) }} €</td>
                                                <td>{{ two($booking->price - $booking->cag_percent) }} €</td>
                                                <td>{{ two($booking->cag_percent) }} €</td>
                                                <td>
                                                    @if($booking->blocked_event)
                                                        {{ \Carbon\Carbon::parse($booking->blocked_event->due)->format('d.m.Y') }}
                                                    @else
                                                        -storniert-
                                                    @endif
                                                </td>
                                                <td>{{ $booking->transaction_id }}</td>
                                                <td>
                                                    <span class="@if($booking->status == 'rejected' || $booking->status == 'cancelled') text-danger @elseif($booking->status == 'accepted') text-success @else {{ !$booking->is_guest ? "text-warning" : "" }} glow @endif">
                                                        {{ strtoupper($booking->status) }}
                                                        @if($booking->is_guest)
                                                            <br>
                                                            <small class="text-muted">Guest Checkout</small>
                                                        @endif
                                                    </span>
                                                    @if($booking->last_employee_id)

                                                        <br>

                                                        <span class="text-info">by {{ $booking->employee->name }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{route('admin.guides.edit', $booking->guiding->user->id)}}">
                                                        {{ $booking->guiding->user->full_name }}
                                                    </a>
                                                </td>
                                                <td>
                                                    <a href="{{route('admin.guidings.edit', $booking->guiding->id)}}">
                                                        {{$booking->guiding->title}}
                                                    </a>
                                                </td>
                                                <td>
                                                    @if($booking->status == 'pending')
                                                        <a href="{{ route('booking.accept', $booking->token . '|' . auth()->user()->id) }}" class="btn btn-sm btn-success"><i class="fa fa-check"></i></a>
                                                    @endif

                                                    <a href="{{ route('admin.bookings.edit', $booking) }}" class="btn btn-sm btn-secondary"><i class="fa fa-pen"></i></a>
                                                    <a href="javascript:deleteResource('{{ route('admin.bookings.destroy', $booking, false) }}')" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></a>
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
