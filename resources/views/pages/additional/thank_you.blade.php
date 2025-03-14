@extends('layouts.app')

@section('title', __('thank-you.thank_you'))

@section('content')
    <style>
        .thankyou-page {
            font-family: 'Raleway', sans-serif;
            padding: 50px 0;
            background: #f8f9fa;
            min-height: 100vh;
        }
        .thankyou-page ._header {
            background: linear-gradient(135deg, var(--thm-primary) 0%, #2c3e50 100%);
            padding: 40px 30px;
            text-align: center;
            border-radius: 12px 12px 0 0;
            position: relative;
            overflow: hidden;
        }
        .thankyou-page ._header:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('/images/pattern.png');
            opacity: 0.1;
        }
        .thankyou-page ._header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
            position: relative;
        }
        .thankyou-page ._body {
            margin: -20px 0 30px;
        }
        .thankyou-page ._box {
            margin: auto;
            max-width: 1200px;
            padding: 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        .booking-status {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: #e8f5e9;
            border-radius: 8px;
            border-left: 4px solid #4caf50;
        }
        .booking-details {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            margin-top: 30px;
            padding: 25px;
            background: #f8f9fa;
            border-radius: 12px;
            border: 1px solid #e9ecef;
        }
        .booking-details .section {
            width: calc(100% / 2 - 15px);
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .booking-details h3 {
            color: var(--thm-primary);
            font-size: 1.4rem;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--thm-primary);
            position: relative;
        }
        .booking-details h3:after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 50px;
            height: 2px;
            background: var(--thm-secondary);
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
            flex-wrap: wrap;
        }
        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .detail-label {
            font-weight: 600;
            color: #2c3e50;
            font-size: 1.1rem;
        }
        .detail-value {
            color: #34495e;
            font-size: 1.1rem;
        }
        .thankyou-page ._footer {
            text-align: center;
            margin-top: 40px;
        }
        .btn-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin: 20px 0;
        }
        .btn {
            background: var(--thm-primary);
            color: white;
            border: 0;
            font-size: 1rem;
            font-weight: 600;
            padding: 12px 30px;
            border-radius: 8px;
            text-transform: uppercase;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            background: var(--thm-secondary);
            color: white;
        }
        .btn-outline {
            border: 2px solid var(--thm-primary);
            color: var(--thm-primary);
            background: transparent;
            transition: all 0.3s ease;
        }
        .btn-outline:hover {
            background: var(--thm-primary);
            color: white;
        }
        .btn-back {
            background: #ffd7d4;
            color: #e74c3c;
            border: none;
            transition: all 0.3s ease;
        }
        .btn-back:hover {
            background: #e74c3c;
            color: white;
        }
        .redirect-timer {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 0.9rem;
            position: relative;
        }
        .guide-info {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .guide-info img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
        }
        .tour-info {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .payment-info {
            background: #fff3cd;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            border-left: 4px solid #ffc107;
        }
        @media (max-width: 992px) {
            .booking-details .section {
                width: 100%;
            }
        }
        @media (max-width: 768px) {
            .booking-details .section {
                width: 100%;
            }
        }
    </style>

    <div class="thankyou-page">
        <div class="container">
            <div class="_header">
                <h1>@lang('thank-you.thank_you')</h1>
            </div>
            <div class="_body">
                <div class="_box">
                    <div class="booking-status">
                        <p class="fw-bold fs-5 mb-0">
                            @lang('thank-you.booking_success_message')
                        </p>
                    </div>

                    @if(isset($booking))
                    <div class="booking-details">
                        <div class="section">
                            <h3>@lang('thank-you.booking_information')</h3>
                            <div class="detail-row">
                                <span class="detail-label">@lang('thank-you.booking_id')</span>
                                <span class="detail-value">#{{ $booking->id }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">@lang('thank-you.booking_status')</span>
                                <span class="detail-value">
                                    <span class="badge bg-warning">@lang('thank-you.pending_confirmation')</span>
                                </span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">@lang('thank-you.tour_date')</span>
                                <span class="detail-value">{{ \Carbon\Carbon::parse($booking->book_date)->format('d.m.Y') }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">@lang('thank-you.number_of_participants')</span>
                                <span class="detail-value">{{ $booking->count_of_users }}</span>
                            </div>
                        </div>

                        <div class="section">
                            <h3>@lang('thank-you.guide_information')</h3>
                            <div class="guide-info">
                                @if($booking->guiding->user->avatar)
                                    <img src="{{ $booking->guiding->user->avatar }}" alt="Guide Photo">
                                @endif
                                <div>
                                    <h4>{{ $booking->guiding->user->firstname }} {{ $booking->guiding->user->lastname }}</h4>
                                    <p>@lang('thank-you.professional_fishing_guide')</p>
                                </div>
                            </div>
                        </div>

                        <div class="section">
                            <h3>@lang('thank-you.tour_details')</h3>
                            <div class="detail-row">
                                <span class="detail-label">@lang('thank-you.tour_name')</span>
                                <span class="detail-value">{{ $booking->guiding->title }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">@lang('thank-you.duration')</span>
                                <span class="detail-value">{{ $booking->guiding->duration }} {{ $booking->guiding->duration_type == 'multi_day' ? __('guidings.days') : __('guidings.hours') }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">@lang('thank-you.location')</span>
                                <span class="detail-value">{{ $booking->guiding->location }}</span>
                            </div>
                        </div>

                        <div class="section">
                            <h3>@lang('thank-you.price_breakdown')</h3>
                            <div class="detail-row">
                                <span class="detail-label">@lang('thank-you.base_price')</span>
                                <span class="detail-value">€{{ number_format($booking->price - $booking->total_extra_price, 2, ',', '.') }}</span>
                            </div>
                            @if($booking->extras)
                            <div class="detail-row">
                                <span class="detail-label">@lang('thank-you.extras')</span>
                                <span class="detail-value">€{{ number_format($booking->total_extra_price, 2, ',', '.') }}</span>
                            </div>
                            @endif
                            <div class="detail-row">
                                <span class="detail-label">@lang('thank-you.total_price')</span>
                                <span class="detail-value fw-bold">€{{ number_format($booking->price, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="payment-info mt-4">
                        <h4 class="mb-3">@lang('thank-you.payment_information')</h4>
                        <p>@lang('thank-you.payment_message')</p>
                    </div>
                    @endif

                    <div class="text-center mt-4">
                        <p class="text-muted">
                            @lang('thank-you.contact_message')
                        </p>
                    </div>

                    <div class="btn-group">
                        <a href="{{ route('additional.contact') }}" class="btn btn-outline">
                            @lang('thank-you.contact_us')
                        </a>
                        <a href="{{ route('guidings.index') }}" class="btn btn-back">
                            @lang('thank-you.back_to_guidings')
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
