@extends('layouts.app')

@section('title', translate('Thank You'))

@section('content')
    <style>
        .thankyou-page {
            font-family: 'Raleway', sans-serif;
            padding: 50px 0;
            background: #f8f9fa;
            min-height: 100vh;
            margin-bottom: -200px; /* Remove extra space at bottom */
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
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            margin-top: 30px;
            padding: 25px;
            background: #f8f9fa;
            border-radius: 12px;
            border: 1px solid #e9ecef;
        }
        .booking-details .section {
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
    </style>

    <div class="thankyou-page">
        <div class="container">
            <div class="_header">
                <h1>{{ translate('Thank you for your reservation!') }}</h1>
            </div>
            <div class="_body">
                <div class="_box">
                    <div class="booking-status">
                        <p class="fw-bold fs-5 mb-0">
                            {{ translate('Your booking request has been successfully received by the guide. You will be notified of the guide\'s response by email within the next 72 hours.') }}
                        </p>
                    </div>

                    @if(isset($booking))
                    <div class="booking-details">
                        <div class="section">
                            <h3>{{ translate('Booking Information') }}</h3>
                            <div class="detail-row">
                                <span class="detail-label">{{ translate('Booking ID') }}</span>
                                <span class="detail-value">#{{ $booking->id }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">{{ translate('Booking Status') }}</span>
                                <span class="detail-value">
                                    <span class="badge bg-warning">{{ translate('Pending Confirmation') }}</span>
                                </span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">{{ translate('Tour Date') }}</span>
                                <span class="detail-value">{{ \Carbon\Carbon::parse($booking->book_date)->format('d.m.Y') }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">{{ translate('Number of Participants') }}</span>
                                <span class="detail-value">{{ $booking->count_of_users }}</span>
                            </div>
                        </div>

                        <div class="section">
                            <h3>{{ translate('Guide Information') }}</h3>
                            <div class="guide-info">
                                @if($booking->guiding->user->avatar)
                                    <img src="{{ $booking->guiding->user->avatar }}" alt="Guide Photo">
                                @endif
                                <div>
                                    <h4>{{ $booking->guiding->user->firstname }} {{ $booking->guiding->user->lastname }}</h4>
                                    <p>{{ translate('Professional Fishing Guide') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="section">
                            <h3>{{ translate('Tour Details') }}</h3>
                            <div class="detail-row">
                                <span class="detail-label">{{ translate('Tour Name') }}</span>
                                <span class="detail-value">{{ $booking->guiding->title }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">{{ translate('Duration') }}</span>
                                <span class="detail-value">{{ $booking->guiding->duration }} {{ translate('hours') }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">{{ translate('Location') }}</span>
                                <span class="detail-value">{{ $booking->guiding->location }}</span>
                            </div>
                        </div>

                        <div class="section">
                            <h3>{{ translate('Price Breakdown') }}</h3>
                            <div class="detail-row">
                                <span class="detail-label">{{ translate('Base Price') }}</span>
                                <span class="detail-value">€{{ number_format($booking->price - $booking->total_extra_price, 2, ',', '.') }}</span>
                            </div>
                            @if($booking->extras)
                            <div class="detail-row">
                                <span class="detail-label">{{ translate('Extras') }}</span>
                                <span class="detail-value">€{{ number_format($booking->total_extra_price, 2, ',', '.') }}</span>
                            </div>
                            @endif
                            <div class="detail-row">
                                <span class="detail-label">{{ translate('Total Price') }}</span>
                                <span class="detail-value fw-bold">€{{ number_format($booking->price, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="payment-info mt-4">
                        <h4 class="mb-3">{{ translate('Payment Information') }}</h4>
                        <p>{{ translate('Payment will be handled directly with your guide after the booking is confirmed.') }}</p>
                    </div>
                    @endif

                    <div class="text-center mt-4">
                        <p class="text-muted">
                            {{ translate('Contact us at any time if you have any questions about your booking or tour.') }}
                        </p>
                    </div>

                    <div class="btn-group">
                        <a href="{{ route('additional.contact') }}" class="btn btn-outline">
                            {{ translate('Contact Us') }}
                        </a>
                        <a href="{{ route('guidings.index') }}" class="btn btn-back">
                            {{ translate('Back to Guidings') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection