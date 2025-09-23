@extends('layouts.app-v2-1')

@section('title', __('checkout.booking_confirmed'))

@push('styles')
<link rel="stylesheet" href="{{ mix('css/app.css') }}">
<style>
.guide-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.guide-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}

.guide-name {
    font-size: 1.1rem;
    font-weight: 600;
    margin: 0 0 5px 0;
    color: #333;
}

.guide-title {
    font-size: 0.9rem;
    color: #666;
    margin: 0;
}
</style>
@endpush

@section('content')
<main class="container mx-auto pb-4">
    <h3 class="text-2xl font-bold mb-2">{{ __('checkout.request_sent') }} 🎣</h3>

    <!-- Success Message -->
    <div class="alert-success mb-3">
        <p class="alert-text">
            ✅ <strong>{{ __('checkout.confirmation_within_48h') }}</strong>
        </p>
    </div>

    @if(isset($booking))
    <div class="checkout-grid">
        <!-- Main Content -->
        <div class="main-content">
            <!-- Booking Information -->
            <section class="info-card">
                <div class="p-3 border-b">
                    <h5 class="text-lg font-semibold">{{ __('checkout.booking_information') }}</h5>
                </div>
                <div class="p-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div class="info-row">
                            <div class="info-label">{{ strtoupper(__('checkout.booking_id')) }}</div>
                            <div class="info-value">#{{ $booking->id }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">{{ strtoupper(__('checkout.booking_status')) }}</div>
                            <div class="info-value">
                                <span class="badge bg-warning text-dark">{{ __('checkout.pending_confirmation') }}</span>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">{{ strtoupper(__('checkout.tour_date')) }}</div>
                            <div class="info-value">{{ \Carbon\Carbon::parse($booking->book_date)->format('d.m.Y') }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">{{ strtoupper(__('checkout.number_of_participants')) }}</div>
                            <div class="info-value">{{ $booking->count_of_users }}</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Guide Information -->
            <section class="info-card">
                <div class="p-3 border-b">
                    <h5 class="text-lg font-semibold">{{ __('checkout.guide_information') }}</h5>
                </div>
                <div class="p-3">
                    <div class="guide-info">
                        @if($booking->guiding->user->profil_image)
                            <img src="{{ asset('images/' . $booking->guiding->user->profil_image) }}" alt="Guide Photo" class="guide-avatar">
                        @else
                            <img src="{{ asset('images/placeholder_guide.jpg') }}" alt="Guide Photo" class="guide-avatar">
                        @endif
                        <div>
                            <h4 class="guide-name">{{ $booking->guiding->user->firstname }} {{ $booking->guiding->user->lastname }}</h4>
                            <p class="guide-title">{{ __('checkout.professional_fishing_guide') }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Tour Details -->
            <section class="info-card">
                <div class="p-3 border-b">
                    <h5 class="text-lg font-semibold">{{ __('checkout.tour_details') }}</h5>
                </div>
                <div class="p-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div class="info-row">
                            <div class="info-label">{{ strtoupper(__('checkout.tour_name')) }}</div>
                            <div class="info-value">{{ $booking->guiding->title }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">{{ strtoupper(__('checkout.duration')) }}</div>
                            <div class="info-value">{{ $booking->guiding->duration }} {{ $booking->guiding->duration_type == 'multi_day' ? __('checkout.days') : __('checkout.hours') }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">{{ strtoupper(__('checkout.location')) }}</div>
                            <div class="info-value">{{ $booking->guiding->location }}</div>
                        </div>
                        @php
                            $targetFish = collect($booking->guiding->getTargetFishNames())->pluck('name')->toArray();
                        @endphp
                        @if(!empty($targetFish))
                        <div class="info-row">
                            <div class="info-label">{{ strtoupper(__('checkout.target_fish')) }}</div>
                            <div class="info-value">{{ implode(', ', $targetFish) }}</div>
                        </div>
                        @endif
                        @if($booking->guiding->description)
                        <div class="info-row">
                            <div class="info-label">{{ strtoupper(__('checkout.description')) }}</div>
                            <div class="info-value">{{ Str::limit($booking->guiding->description, 100) }}</div>
                        </div>
                        @endif
                        @if($booking->guiding->max_participants)
                        <div class="info-row">
                            <div class="info-label">{{ strtoupper(__('checkout.max_participants')) }}</div>
                            <div class="info-value">{{ $booking->guiding->max_participants }} {{ __('checkout.people') }}</div>
                        </div>
                        @endif
                        @if($booking->guiding->difficulty_level)
                        <div class="info-row">
                            <div class="info-label">{{ strtoupper(__('checkout.difficulty_level')) }}</div>
                            <div class="info-value">{{ ucfirst($booking->guiding->difficulty_level) }}</div>
                        </div>
                        @endif
                        @if($booking->guiding->equipment_provided)
                        <div class="info-row">
                            <div class="info-label">{{ strtoupper(__('checkout.equipment_provided')) }}</div>
                            <div class="info-value">{{ $booking->guiding->equipment_provided ? __('checkout.yes') : __('checkout.no') }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </section>

            @if($booking->extras)
            <!-- Selected Extras -->
            <section class="info-card">
                <div class="p-3 border-b">
                    <h5 class="text-lg font-semibold">{{ __('checkout.selected_extras') }}</h5>
                </div>
                <div class="p-3">
                    @php
                        $extras = unserialize($booking->extras);
                    @endphp
                    @if($extras && is_array($extras))
                        @foreach($extras as $extra)
                            <div class="extra-item">
                                <span class="extra-name">{{ $extra['extra_name'] }} ({{ $extra['extra_quantity'] }}x)</span>
                                <span class="extra-price">€{{ number_format($extra['extra_total_price'], 2, ',', '.') }}</span>
                            </div>
                        @endforeach
                    @endif
                </div>
            </section>
            @endif
        </div>

        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="space-y-4">
                <!-- Price Summary -->
                <section class="info-card booking-summary">
                    <div class="p-3 border-b summary-header">
                        <h5 class="text-lg font-semibold">{{ __('checkout.price_breakdown') }}</h5>
                    </div>
                    <div class="p-4 summary-content">
                        <div class="summary-row">
                            <div class="summary-label">{{ __('checkout.base_price') }}</div>
                            <div class="summary-value">€{{ number_format($booking->price - $booking->total_extra_price, 2, ',', '.') }}</div>
                        </div>
                        @if($booking->extras)
                        <div class="summary-row">
                            <div class="summary-label">{{ __('checkout.extras') }}</div>
                            <div class="summary-value">€{{ number_format($booking->total_extra_price, 2, ',', '.') }}</div>
                        </div>
                        @endif
                        <div class="summary-divider"></div>
                        <div class="summary-row">
                            <div class="summary-label font-semibold">{{ __('checkout.total_price') }}</div>
                            <div class="summary-value font-semibold">€{{ number_format($booking->price, 2, ',', '.') }}</div>
                        </div>
                    </div>
                </section>

                <!-- Payment Information -->
                <section class="info-card p-3 payment-info">
                    <h6 class="payment-title">{{ __('checkout.payment_information') }}</h6>
                    <p class="payment-text">{{ __('checkout.payment_message') }}</p>
                </section>

                <!-- Action Buttons -->
                <div class="btn-group">
                    <a href="{{ route('modern-checkout.index') }}" class="btn">
                        {{ __('checkout.book_another_guiding') }}
                    </a>
                    <a href="{{ route('guidings.index') }}" class="btn btn-outline">
                        {{ __('checkout.browse_guidings') }}
                    </a>
                    <a href="{{ route('additional.contact') }}" class="btn btn-back">
                        {{ __('checkout.contact_us') }}
                    </a>
                </div>
            </div>
        </aside>
    </div>
    @endif
</main>
@endsection

