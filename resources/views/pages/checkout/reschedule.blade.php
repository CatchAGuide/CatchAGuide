@extends('layouts.app')

@section('title', 'Booking Reschedule')

@section('custom_style')
<style>
    .page-header {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .info-box {
        background-color: #f8f9fa;
        border-left: 4px solid #17a2b8;
        padding: 15px;
        margin-bottom: 20px;
    }
    
    .warning-box {
        background-color: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 15px;
        margin-bottom: 20px;
    }
    
    .info-section {
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        padding: 20px;
        margin-bottom: 30px;
    }
    
    .info-section h3 {
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 10px;
        margin-bottom: 20px;
        font-weight: bold;
        color: #333;
    }
    
    .required-field::after {
        content: '*';
        color: red;
        margin-left: 3px;
    }
    
    .action-buttons {
        margin-top: 30px;
    }
    
    .btn-return {
        background-color: #6c757d;
        color: white;
    }
    
    .btn-proceed {
        background-color: #e86549;
        color: white;
    }
    
    .btn-cancel {
        background-color: #6c757d;
        color: white;
    }
    
    .price-info {
        border-left: 4px solid #17a2b8;
        padding: 15px;
        background-color: #f8f9fa;
    }
    
    .form-label {
        font-weight: 500;
    }
    
    .extra-container {
        margin-bottom: 15px;
        padding: 15px;
        border-radius: 5px;
        background-color: #f9f9f9;
        border-left: 3px solid #e86549;
        transition: all 0.2s ease;
    }
    
    .extra-container:hover {
        background-color: #f0f0f0;
    }
    
    .quantity-container {
        margin-top: 10px;
        padding-left: 10px;
        display: none;
    }
    
    .quantity-input {
        width: 100% !important;
        max-width: 120px;
        text-align: center;
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 5px;
        height: 38px;
    }
    
    .bordered-heading {
        position: relative;
        padding-bottom: 5px;
    }
    
    .bordered-heading:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 50px;
        height: 2px;
        background-color: #e86549;
    }
    
    .price-summary {
        background-color: #f8f9fa;
        border-radius: 5px;
        padding: 15px;
        margin-top: 20px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    
    .price-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        padding: 5px 0;
    }
    
    .price-total {
        font-weight: bold;
        border-top: 1px solid #dee2e6;
        padding-top: 10px;
        margin-top: 10px;
    }
    
    .btn-proceed {
        background-color: #e86549;
        color: white;
        font-weight: 600;
        padding: 12px 20px;
        transition: all 0.3s ease;
        width: 100%;
    }
    
    .btn-proceed:hover {
        background-color: #d55a40;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        color: white;
    }
    
    @media (max-width: 767.98px) {
        .info-section {
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .page-header h1 {
            font-size: 1.8rem;
        }
        
        .extra-container {
            padding: 12px;
        }
        
        .quantity-container {
            padding-left: 5px;
        }
        
        .price-summary {
            padding: 12px;
        }
        
        .btn-proceed {
            padding: 10px 15px;
        }
    }
    
    #loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }
    
    .spinner-container {
        text-align: center;
        background-color: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    }
    
    .spinner-border {
        width: 3rem;
        height: 3rem;
    }
</style>
@endsection

@section('content')
    <div class="container my-5">
        <div class="page-header">
            <h1>{{ translate('Booking Reschedule') }}</h1>
            <p class="text-muted">{{ translate('Update your booking details below') }}</p>
        </div>
        
        <form id="rescheduleForm" method="POST" action="{{ route('booking.reschedule.store') }}">
            @csrf
            <input type="hidden" name="booking_id" value="{{ $booking->id }}">
            <input type="hidden" name="selectedDate" value="{{ $selectedDate }}">
            
            <!-- Main Content -->
            <div class="row">
                <!-- Left Column -->
                <div class="col-lg-7">
                    <!-- Fishing License Warning -->
                    <div class="info-box mb-4">
                        <p class="mb-0">{{ translate('Please ensure yourself that you have the valid fishing license for the country, area or body of water where you intend to fish. Catch A Guide assumes no liability for possible personal injury and/or property damage during a guiding.') }}</p>
                    </div>
                    
                    <!-- Guiding Information -->
                    <div class="info-section">
                        <h3>{{ translate('Guiding Information') }}</h3>
                        <div class="row mb-3">
                            <div class="col-12">
                                <h5>{{ $guiding->title }}</h5>
                                <div>
                                  @if($guiding->user->profil_image)
                                    <img class="rounded-circle" src="{{asset('images/'. $guiding->user->profil_image)}}" alt="" width="24" height="24">
                                  @else
                                    <img class="rounded-circle" src="{{asset('images/placeholder_guide.jpg')}}" alt="" width="24" height="24">
                                  @endif
                                </div>
                                <p class="text-muted">by {{ $guiding->user->firstname }}</p>
                            </div>
                        </div>
                        
                        <div class="row mb-2">
                            @if($guiding->is_boat)
                                <div class="flex-column border-bottom">
                                    <div class="my-2">
                                        <span class="text-dark fw-bold">{{ translate('Fishing from ')}}:</span>
                                    </div>
                                    <div class="px-2 text-dark">
                                        {{$guiding->is_boat ? $guiding->boat_type : ''}}
                                    </div>
                                </div>
                            @else
                                <div class="flex-column border-bottom">
                                    <div class="my-2">
                                        <span class="text-dark fw-bold">{{ translate('Shore') }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="flex-column border-bottom">
                            <div class="my-2">
                                <span class="text-dark fw-bold">{{ translate('Location')}}:</span>
                            </div>
                            <div class="px-2 text-dark">
                                {{$guiding->location ? $guiding->location : ''}}
                            </div>
                        </div>

                        <div class="flex-column border-bottom">
                            <div class="my-2">
                                <span class="text-dark fw-bold">@lang('profile.duration'):</span>
                            </div>
                            <div class="px-2 text-dark">
                                <p>{{$guiding->duration}} @if($guiding->duration_type == 'multi_day') days @else @lang('message.hours') @endif</p>
                            </div>
                        </div>

                        <div class="flex-column border-bottom">
                            <div class="my-2">
                                <span class="text-dark fw-bold">@lang('profile.targetFish'):</span>
                            </div>
                            <div class="px-2 text-dark">
                                <p>
                                {{implode(', ', collect($guiding->getTargetFishNames())->pluck('name')->toArray())}}
                                </p>
                            </div>
                        </div>
                        
                        @php
                            $guidingInclusions = $guiding->getInclusionNames();
                            $guidingInclusions = !empty($guidingInclusions) ? array_column($guidingInclusions, 'name') : [];
                        @endphp

                        @if (!empty($guidingInclusions))
                        <div class="flex-column border-bottom">
                            <div class="my-2">
                                <span class="text-dark fw-bold">@lang('profile.inclussion'):</span>
                            </div>
                            <div class="px-2 text-dark">
                                {{ implode(', ', array_filter($guidingInclusions)) }}
                            </div>
                        </div>
                        @endif

                        @if($guiding->requirements)
                        <div class="flex-column">
                            <div class="my-2">
                                <span class="text-dark fw-bold">@lang('checkout.requirements_for_participation'):</span>
                            </div>
                            <div class="px-2 text-dark">
                                @if($guiding->requirements)
                                    @foreach($guiding->getRequirementsAttribute() as $requirement)
                                        {!! $requirement['name'] !!}: {!! $requirement['value'] !!}
                                        <br>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    <!-- Personal Information -->
                    <div class="info-section">
                        <h3>{{ translate('Personal Information') }}</h3>
                        
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="fw-bold d-block">{{ translate('First Name') }}</label>
                                <div>{{ $user->firstname }}</div>
                                <input type="hidden" name="first_name" value="{{ $user->firstname }}">
                            </div>
                            <div class="col-6">
                                <label class="fw-bold d-block">{{ translate('Last Name') }}</label>
                                <div>{{ $user->lastname }}</div>
                                <input type="hidden" name="last_name" value="{{ $user->lastname }}">
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <label class="fw-bold d-block">{{ translate('E-mail Address') }}</label>
                            <div>{{ $booking->email }}</div>
                            <input type="hidden" name="email" value="{{ $booking->email }}">
                        </div>
                        
                        <div class="mt-3">
                            <label class="fw-bold d-block">{{ translate('Address') }}</label>
                            <div>{{ $user->information && $user->information->address ? $user->information->address : '' }}</div>
                            <input type="hidden" name="address" value="{{ $user->information && $user->information->address ? $user->information->address : '' }}">
                        </div>
                        
                        <div class="row g-3 mt-1">
                            <div class="col-6">
                                <label class="fw-bold d-block">{{ translate('City') }}</label>
                                <div>{{ $user->information && $user->information->city ? $user->information->city : '' }}</div>
                                <input type="hidden" name="city" value="{{ $user->information && $user->information->city ? $user->information->city : '' }}">
                            </div>
                            <div class="col-6">
                                <label class="fw-bold d-block">{{ translate('Country / Region') }}</label>
                                <div>{{ $user->information && $user->information->country ? $user->information->country : '' }}</div>
                                <input type="hidden" name="country" value="{{ $user->information && $user->information->country ? $user->information->country : '' }}">
                            </div>
                        </div>
                        
                        <div class="row g-3 mt-1">
                            <div class="col-6">
                                <label class="fw-bold d-block">{{ translate('Postal code') }}</label>
                                <div>{{ $user->information && $user->information->postal ? $user->information->postal : '' }}</div>
                                <input type="hidden" name="postal_code" value="{{ $user->information && $user->information->postal ? $user->information->postal : '' }}">
                            </div>
                            <div class="col-6">
                                <label class="fw-bold d-block">{{ translate('Phone Number') }}</label>
                                <div>{{ $booking->phone }}</div>
                                <input type="hidden" name="phone" value="{{ $booking->phone }}">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column - Booking Overview -->
                <div class="col-lg-5">
                    <div class="info-section">
                        <h3>Booking Overview</h3>
                        <div class="row mb-3">
                            <div class="col-6 font-weight-bold">Total Guest:</div>
                            <div class="col-6 d-flex align-items-center">
                                <input type="number" class="form-control me-2" id="guest_count" name="count_of_users" 
                                       min="1" max="{{ $guiding->max_guests }}" value="{{ $booking->count_of_users }}" style="width: 70px;">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="update_guests" style="display: none;">Update</button>
                            </div>
                        </div>
                        
                        <!-- Store max guest value and original guest count for JavaScript -->
                        <input type="hidden" id="max_guest" value="{{ $guiding->max_guests }}">
                        <input type="hidden" id="original_guest_count" value="{{ $booking->count_of_users }}">
                        
                        <div class="row mb-2">
                            <div class="col-6 font-weight-bold">Booking Date:</div>
                            <div class="col-6">{{ \Carbon\Carbon::parse($selectedDate)->format('F d, Y') }}</div>
                        </div>
                        
                        @php
                            $guidingprice = 0;
                            $prices = json_decode($guiding->prices, true) ?? [];
                            $priceType = $guiding->price_type;
                            $basePrice = $guiding->price ?? 0;
                            $extras = json_decode($guiding->pricing_extra, true) ?? [];
                            
                            // This will be calculated via JavaScript
                            $initialGuidingPrice = 0;
                            if($priceType == 'per_person'){
                                foreach ($prices as $price) {
                                    if ($price['person'] == $booking->count_of_users) {
                                        $initialGuidingPrice = $price['amount'];
                                        break;
                                    }
                                }
                            } else {
                                $initialGuidingPrice = $basePrice;
                            }

                            if ($initialGuidingPrice == 0 && !empty($prices)) {
                                $lastPrice = end($prices);
                                $initialGuidingPrice = $lastPrice['amount'] * $booking->count_of_users;
                            }
                        @endphp
                        
                        <!-- Store pricing data for JavaScript -->
                        <input type="hidden" id="price_type" value="{{ $priceType }}">
                        <input type="hidden" id="base_price" value="{{ $basePrice }}">
                        <input type="hidden" id="price_data" value="{{ json_encode($prices) }}">
                        
                        @if(count($extras))
                            <div class="mt-4 mb-4">
                                <h5 class="bordered-heading mb-3">Available Extras</h5>
                                <div class="extras-container">
                                    @foreach($extras as $index => $extra)
                                    @php
                                        // Check if this extra was previously selected in the booking
                                        $bookingExtras = [];
                                        if ($booking->extras !== null) {
                                            $bookingExtras = is_array($booking->extras) ? $booking->extras : unserialize($booking->extras) ?? [];
                                        }
                                        $isSelected = false;
                                        $quantity = 1;
                                        
                                        foreach($bookingExtras as $bookingExtra) {
                                            if(isset($bookingExtra['extra_name']) && $bookingExtra['extra_name'] == $extra['name']) {
                                                $isSelected = true;
                                                $quantity = $bookingExtra['extra_quantity'] ?? 1;
                                                break;
                                            }
                                        }
                                    @endphp
                                    <div class="extra-container">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input extra-checkbox" 
                                                       id="extra_{{$index}}" 
                                                       name="extras[{{$index}}][selected]" 
                                                       data-name="{{$extra['name']}}" 
                                                       data-price="{{$extra['price']}}"
                                                       {{ $isSelected ? 'checked' : '' }}>
                                                <label class="form-check-label" for="extra_{{$index}}">
                                                    {{$extra['name']}} - <span class="fw-bold">€{{number_format($extra['price'], 2)}}</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="quantity-container" id="quantity_container_{{$index}}" style="{{ $isSelected ? 'display: block;' : 'display: none;' }}">
                                            <div class="d-flex align-items-center">
                                                <div class="row w-100 align-items-center">
                                                    <div class="col-sm-4 col-5">
                                                        <label for="quantity_{{$index}}" class="mb-0">Quantity:</label>
                                                    </div>
                                                    <div class="col-sm-8 col-7">
                                                        <input type="number" class="form-control quantity-input" 
                                                               id="quantity_{{$index}}" 
                                                               name="extras[{{$index}}][quantity]" 
                                                               min="1" value="{{ $quantity }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        
                        <div class="price-summary">
                            <div class="price-item">
                                <div>Guiding Price:</div>
                                <div>€<span id="guiding-price">{{ number_format($initialGuidingPrice, 2) }}</span></div>
                            </div>
                            
                            <div id="extras-price-items">
                                <!-- Dynamic extras will be added here -->
                            </div>
                            
                            <div class="price-item price-total">
                                <div>Total:</div>
                                <div class="fs-5 fw-bold text-primary">€<span id="total-price">{{ number_format($initialGuidingPrice, 2) }}</span></div>
                                <input type="hidden" name="total_price" id="total-price-input" value="{{ $initialGuidingPrice }}">
                            </div>
                        </div>
                        
                        <div class="price-info mt-4">
                            <p>You don't have to pay yet. The price per tour includes VAT and all services, which are included. You pay the total amount directly to the guide. The guide's cancellation policy applies. After your booking request has been confirmed by the guide, you will receive the payment options in the booking confirmation email.</p>
                            <p class="font-weight-bold mb-0">You will receive a confirmation or cancellation within the next 3 days.</p>
                        </div>
                        
                        <!-- Terms and Conditions checkbox -->
                        <div class="mt-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" name="terms_accepted" required>
                                <label class="form-check-label" for="terms">
                                    <span class="text-danger">*</span> I hereby confirm that I have read and understood the 
                                    <a href="{{ route('law.agb') }}" target="_blank" class="text-primary">Terms and Conditions</a> and 
                                    <a href="{{ route('law.data-protection') }}" target="_blank" class="text-primary">Privacy Policy</a>. I agree to be bound by these terms for this booking.
                                </label>
                            </div>
                        </div>
                        
                        <div class="warning-box mt-4" id="terms-warning">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-circle text-warning me-2"></i>
                                <p class="mb-0">Please accept the Terms and Conditions to proceed with your booking.</p>
                            </div>
                        </div>
                        
                        <div class="action-buttons">
                            <div class="row">
                                <div class="col-12 mb-2">
                                    <button type="button" class="btn btn-proceed btn-block" id="submit-button" disabled>
                                        <i class="fas fa-check me-2"></i> CONFIRM RESCHEDULE
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Loading Overlay -->
    <div id="loading-overlay" style="display: none;">
        <div class="spinner-container">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Processing your reschedule request...</p>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="successModalLabel">Reschedule Confirmed</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                    </div>
                    <p>Your booking has been successfully rescheduled. You will receive a confirmation email shortly.</p>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('profile.bookings') }}" class="btn btn-primary">Go to My Bookings</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Modal -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="errorModalLabel">Error</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                    </div>
                    <p id="error-message">There was an error processing your reschedule request. Please try again.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="retry-button">Try Again</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js_after')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('rescheduleForm');
        const termsCheckbox = document.getElementById('terms');
        const termsWarning = document.getElementById('terms-warning');
        const submitButton = document.getElementById('submit-button');
        const guestCountInput = document.getElementById('guest_count');
        const updateGuestsBtn = document.getElementById('update_guests');
        const loadingOverlay = document.getElementById('loading-overlay');
        const successModal = new bootstrap.Modal(document.getElementById('successModal'));
        const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
        const retryButton = document.getElementById('retry-button');
        
        // Get pricing data
        const priceType = document.getElementById('price_type').value;
        const basePrice = parseFloat(document.getElementById('base_price').value);
        const priceData = JSON.parse(document.getElementById('price_data').value || '[]');
        
        let guidingPrice = parseFloat(document.getElementById('guiding-price').textContent.replace(/,/g, ''));
        let totalPrice = guidingPrice;
        
        const maxGuest = parseInt(document.getElementById('max_guest').value) || 1;
        const originalGuestCount = parseInt(document.getElementById('original_guest_count').value) || 1;
        
        // Initially hide the terms warning
        termsWarning.style.display = 'none';
        
        // Handle terms checkbox change
        termsCheckbox.addEventListener('change', function() {
            // Enable/disable submit button based on checkbox state
            submitButton.disabled = !this.checked;
            
            // Show/hide terms warning
            termsWarning.style.display = this.checked ? 'none' : 'block';
        });
        
        // Handle guest count update
        updateGuestsBtn.addEventListener('click', function() {
            updateGuidingPrice();
            // Hide the update button after updating
            this.style.display = 'none';
        });
        
        guestCountInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (updateGuestsBtn.style.display !== 'none') {
                    updateGuidingPrice();
                    updateGuestsBtn.style.display = 'none';
                }
            }
        });
        
        guestCountInput.addEventListener('input', function() {
            // Remove non-numeric characters
            this.value = this.value.replace(/[^0-9]/g, '');
            
            // Ensure minimum value is 1
            if (this.value === '' || parseInt(this.value) < 1) {
                this.value = '1';
            }
            
            // Ensure maximum value doesn't exceed max_guest
            if (parseInt(this.value) > maxGuest) {
                this.value = maxGuest;
            }
            
            // Show update button only if value has changed from original
            if (parseInt(this.value) !== originalGuestCount) {
                updateGuestsBtn.style.display = 'block';
            } else {
                updateGuestsBtn.style.display = 'none';
            }
        });
        
        function updateGuidingPrice() {
            const guestCount = parseInt(guestCountInput.value) || 1;
            
            // Ensure guest count is within limits
            if (guestCount < 1) {
                guestCountInput.value = '1';
                return;
            }
            
            if (guestCount > maxGuest) {
                guestCountInput.value = maxGuest;
                return;
            }
            
            // Calculate guiding price based on guest count
            let newGuidingPrice = 0;
            
            if (priceType === 'per_person') {
                // Find the price for this number of guests
                let foundPrice = false;
                for (const price of priceData) {
                    if (parseInt(price.person) === guestCount) {
                        newGuidingPrice = parseFloat(price.amount);
                        foundPrice = true;
                        break;
                    }
                }
                
                // If no exact match, use the last price and multiply
                if (!foundPrice && priceData.length > 0) {
                    const lastPrice = priceData[priceData.length - 1];
                    newGuidingPrice = parseFloat(lastPrice.amount) * guestCount;
                }
            } else {
                // Fixed price
                newGuidingPrice = basePrice;
            }
            
            // Update the guiding price display
            guidingPrice = newGuidingPrice;
            document.getElementById('guiding-price').textContent = guidingPrice.toFixed(2);
            
            // Update total price
            updateTotalPrice();
        }
        
        // Handle extras checkboxes
        const extraCheckboxes = document.querySelectorAll('.extra-checkbox');
        extraCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const index = this.id.replace('extra_', '');
                const quantityContainer = document.getElementById(`quantity_container_${index}`);
                const quantityInput = document.getElementById(`quantity_${index}`);
                
                if (this.checked) {
                    quantityContainer.style.display = 'block';
                    updateTotalPrice();
                } else {
                    quantityContainer.style.display = 'none';
                    updateTotalPrice();
                }
            });
        });
        
        // Handle quantity inputs
        const quantityInputs = document.querySelectorAll('.quantity-input');
        quantityInputs.forEach(input => {
            input.addEventListener('input', function() {
                // Remove non-numeric characters
                this.value = this.value.replace(/[^0-9]/g, '');
                
                // Ensure minimum value is 1
                if (this.value === '' || parseInt(this.value) < 1) {
                    this.value = '1';
                }
                
                updateTotalPrice();
            });
        });
        
        // Function to update the total price
        function updateTotalPrice() {
            let extrasTotal = 0;
            let extrasPriceHTML = '';
            
            extraCheckboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    const index = checkbox.id.replace('extra_', '');
                    const quantityInput = document.getElementById(`quantity_${index}`);
                    const quantity = parseInt(quantityInput.value) || 1;
                    const price = parseFloat(checkbox.dataset.price);
                    const extraTotal = price * quantity;
                    extrasTotal += extraTotal;
                    
                    extrasPriceHTML += `
                        <div class="price-item">
                            <div>${checkbox.dataset.name} (${quantity}x):</div>
                            <div>€${extraTotal.toFixed(2)}</div>
                        </div>
                    `;
                }
            });
            
            // Update extras price items
            document.getElementById('extras-price-items').innerHTML = extrasPriceHTML;
            
            // Calculate and update total price
            totalPrice = guidingPrice + extrasTotal;
            document.getElementById('total-price').textContent = totalPrice.toFixed(2);
            document.getElementById('total-price-input').value = totalPrice.toFixed(2);
        }
        
        // Handle form submission via AJAX
        submitButton.addEventListener('click', function() {
            if (!termsCheckbox.checked) {
                termsWarning.style.display = 'block';
                return;
            }
            
            // Show loading overlay
            loadingOverlay.style.display = 'flex';
            
            // Get form data
            const formData = new FormData(form);
            
            // Send AJAX request
            fetch('{{ route("booking.reschedule.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                // Hide loading overlay
                loadingOverlay.style.display = 'none';
                
                if (data.success) {
                    // Show success modal
                    successModal.show();
                } else {
                    // Show error modal with message
                    document.getElementById('error-message').textContent = data.message || 'There was an error processing your reschedule request.';
                    errorModal.show();
                }
            })
            .catch(error => {
                // Hide loading overlay
                loadingOverlay.style.display = 'none';
                
                // Show error modal
                document.getElementById('error-message').textContent = 'There was an error connecting to the server. Please try again.';
                errorModal.show();
                console.error('Error:', error);
            });
        });
        
        // Handle retry button
        retryButton.addEventListener('click', function() {
            errorModal.hide();
            submitButton.click();
        });
        
        // Initialize total price with pre-selected extras
        updateTotalPrice();
    });
</script>
@endsection
