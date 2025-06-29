@extends('pages.profile.layouts.profile')
@section('title', 'Payment Information')

@section('profile-content')
    <style>
        /* Header Section Styling */
        .payments-header {
            background: linear-gradient(135deg, #313041, #252238);
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .payments-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
            opacity: 0.5;
            animation: float 20s infinite linear;
        }
        
        @keyframes float {
            0% { transform: translateX(-100px) translateY(-100px); }
            100% { transform: translateX(100px) translateY(100px); }
        }
        
        .payments-header h1 {
            color: white !important;
            font-weight: 700;
            margin-bottom: 0;
            z-index: 1;
            position: relative;
        }
        
        .payments-header p {
            color: white !important;
            opacity: 0.9;
            z-index: 1;
            position: relative;
        }

        .profile-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 25px;
            border-left: 4px solid #313041;
        }
        
        .section-title {
            color: #313041;
            font-weight: 600;
            margin-bottom: 20px;
            font-size: 1.1rem;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            font-weight: 500;
            color: #333;
            margin-bottom: 8px;
        }
        
        .required {
            color: #e8604c;
        }
        
        .form-control {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 12px;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #313041;
            box-shadow: 0 0 0 0.2rem rgba(49, 48, 65, 0.25);
        }
        
        .btn-primary {
            background-color: #e8604c;
            border-color: #e8604c;
            padding: 12px 30px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: #d54e37;
            border-color: #d54e37;
        }
        
        .guide-section {
            background: linear-gradient(135deg, #313041, #252238);
            color: white;
            border-left-color: #252238;
        }
        
        .guide-section .section-title {
            color: white;
        }
        
        .guide-section .form-label {
            color: #f8f9fa;
        }
        
        .payment-checkbox {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .payment-checkbox input {
            margin-right: 10px;
        }
        
        .helper-text {
            font-size: 0.85rem;
            color: #666;
            margin-top: 5px;
        }
        
        .guide-section .helper-text {
            color: #f8f9fa;
            opacity: 0.8;
        }
        
        .alert-info {
            background-color: #e3f2fd;
            border-color: #2196f3;
            color: #1976d2;
        }

        .balance-section {
            background: linear-gradient(135deg, #28a745, #20732e);
            color: white;
            border-left-color: #20732e;
        }

        .balance-section .section-title {
            color: white;
        }

        .balance-section .form-label {
            color: #f8f9fa;
        }

        .balance-section .helper-text {
            color: #f8f9fa;
            opacity: 0.8;
        }

        .stripe-section {
            background: linear-gradient(135deg, #6772e5, #5469d4);
            color: white;
            border-left-color: #5469d4;
        }

        .stripe-section .section-title {
            color: white;
        }

        .stripe-section .form-label {
            color: #f8f9fa;
        }

        .stripe-section .helper-text {
            color: #f8f9fa;
            opacity: 0.8;
        }

        .table-section {
            background: white;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 25px;
            border-left: 4px solid #17a2b8;
        }

        .table-section .section-title {
            color: #17a2b8;
        }

        /* Floating Save Button */
        .floating-save-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
            background-color: #e8604c;
            border: none;
            border-radius: 50px;
            padding: 15px 25px;
            color: white;
            font-weight: 600;
            box-shadow: 0 4px 20px rgba(232, 96, 76, 0.3);
            transition: all 0.3s ease;
            display: none;
        }
        
        .floating-save-btn:hover {
            background-color: #d54e37;
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(232, 96, 76, 0.4);
        }
        
        .floating-save-btn.show {
            display: block;
            animation: slideInUp 0.3s ease-out;
        }
        
        @keyframes slideInUp {
            from {
                transform: translateY(100px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif

    <!-- Header Section -->
    <div class="payments-header">
        <h1 class="mb-0 text-white">
            <i class="fas fa-credit-card"></i>
            Payment Information
        </h1>
        <p class="mb-0 mt-2 text-white">Manage your payment methods and financial settings</p>
    </div>

    <!-- Current Balance Section -->
    @if(method_exists(auth()->user(), 'hasDefaultPaymentMethod') && auth()->user()->hasDefaultPaymentMethod())
        <div class="profile-section balance-section">
            <h3 class="section-title">
                <i class="fas fa-wallet"></i> Current Balance
            </h3>
            <div class="row">
                <div class="col-md-6">
                    <h5 class="text-white">Your Current Balance: {{ isset(auth()->user()->balance) ? two(auth()->user()->balance) : '0.00' }} €</h5>
                </div>
                <div class="col-md-6">
                    @if(Route::has('payments.deposit'))
                        <form action="{{ route('payments.deposit') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-8">
                                    <input type="number" class="form-control" placeholder="Deposit Amount" 
                                           name="amount" min="25" max="1000">
                                </div>
                                <div class="col-4">
                                    <button type="submit" class="btn btn-light">
                                        <i class="fas fa-plus"></i> Deposit
                                    </button>
                                </div>
                            </div>
                        </form>
                    @else
                        <p class="text-white-50">Deposit functionality not available</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Stripe Payment Section -->
    @if($intent)
        <div class="profile-section stripe-section">
            <h3 class="section-title">
                <i class="fas fa-credit-card"></i> Credit Card Details
            </h3>
            @if(Route::has('payments.add-or-update-payment-method'))
                <form action="{{ route('payments.add-or-update-payment-method') }}" method="POST" id="addOrUpdatePaymentForm">
                    @csrf
                    <input type="hidden" name="payment_method" id="paymentMethodHidden">
                </form>

                <div class="form-group">
                    <label class="form-label" for="card-holder-name">Card Holder Name</label>
                    <input id="card-holder-name" class="form-control" type="text" 
                           placeholder="Card Holder Name" value="{{ auth()->user()->full_name ?? '' }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Card Information</label>
                    <div id="card-element" class="form-control"></div>
                </div>

                <button type="button" id="card-button" data-secret="{{ $intent->client_secret }}" class="btn btn-light">
                    <i class="fas fa-sync"></i> Update Payment Method
                </button>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Stripe payment integration is not configured.
                </div>
            @endif
        </div>
    @endif

    @if(auth()->user()->is_guide)
        <form action="{{route('profile.payments.update')}}" method="POST" id="paymentMethodsForm">
            @csrf
            @method('PUT')
            
            <!-- Hidden fields for payment methods -->
            <input type="hidden" name="paypal_allowed" value="0">
            <input type="hidden" name="banktransfer_allowed" value="0">
            <input type="hidden" name="bar_allowed" value="0">

            <!-- Guide Payment Methods Section -->
            <div class="profile-section guide-section">
                <h3 class="section-title">
                    <i class="fas fa-money-bill-wave"></i> @lang('profile.possiblepayment')
                </h3>
                <div class="alert alert-info">
                    <small>@lang('profile.possiblepaymentmsg')</small>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="payment-checkbox">
                            <input class="form-check-input" {{auth()->user()->bar_allowed ? 'checked' : ''}} 
                                   type="checkbox" value="1" id="bar_allowed" name="bar_allowed">
                            <label class="form-check-label" for="bar_allowed">@lang('profile.barOnSite')</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="payment-checkbox">
                            <input class="form-check-input" {{auth()->user()->banktransfer_allowed ? 'checked' : ''}} 
                                   onclick="displayBankDetails()" type="checkbox" value="1" id="banktransfer_allowed" name="banktransfer_allowed">
                            <label class="form-check-label" for="banktransfer_allowed">@lang('profile.transfer')</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="payment-checkbox">
                            <input class="form-check-input" {{auth()->user()->paypal_allowed == 1 ? 'checked' : ''}} 
                                   onclick="displayPaypalDetails()" type="checkbox" value="1" id="paypal_allowed" name="paypal_allowed">
                            <label class="form-check-label" for="paypal_allowed">@lang('profile.paypal')</label>
                        </div>
                    </div>
                </div>
                
                <div class="form-group" style="display: {{auth()->user()->banktransfer_allowed ? 'block' : 'none'}};" id="banktransferdetails">
                    <label class="form-label" for="banktransferdetails">@lang('profile.bankdetails')</label>
                    <textarea class="form-control" placeholder="IBAN" name="banktransferdetails" rows="2">{{auth()->user()->banktransferdetails}}</textarea>
                    <small class="helper-text">@lang('profile.bankdetailsmsg')</small>
                </div>
                
                <div class="form-group" style="display: {{auth()->user()->paypal_allowed ? 'block' : 'none'}};" id="paypaldetails">
                    <label class="form-label" for="paypaldetails">@lang('profile.paypaladd')</label>
                    <textarea class="form-control" placeholder="Paypal" name="paypaldetails" rows="2">{{auth()->user()->paypaldetails}}</textarea>
                    <small class="helper-text">@lang('profile.paypalmsg')</small>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-light">
                        <i class="fas fa-save"></i> Save Payment Methods
                    </button>
                </div>
            </div>
        </form>
    @endif

    <!-- Transaction History Section -->
    {{-- <div class="table-section">
        <h3 class="section-title">
            <i class="fas fa-history"></i> Transaction History
        </h3>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Transaction ID</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @if(auth()->user()->transactions ?? false)
                        @forelse(auth()->user()->transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->uuid ?? 'N/A' }}</td>
                                <td>
                                    @if(($transaction->type ?? '') === 'deposit')
                                        <span class="badge bg-success">Deposit</span>
                                    @else
                                        <span class="badge bg-warning">Withdrawal/Payment</span>
                                    @endif
                                </td>
                                <td>{{ isset($transaction->amount) ? two($transaction->amount) : '0.00' }} €</td>
                                <td>{{ $transaction->created_at ? $transaction->created_at->format('H:i d.m.Y') : 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No transactions found</td>
                            </tr>
                        @endforelse
                    @else
                        <tr>
                            <td colspan="4" class="text-center text-muted">Transaction history not available</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div> --}}

    <!-- Floating Save Button -->
    @if(auth()->user()->is_guide)
        <button type="button" class="floating-save-btn" id="floatingSaveBtn" onclick="const form = document.getElementById('paymentMethodsForm'); if(form) form.submit();">
            <i class="fas fa-save"></i> Save Changes
        </button>
    @endif
@endsection

@section('js_after')
    @if($intent && env('STRIPE_KEY'))
        <script src="https://js.stripe.com/v3/"></script>
    @endif

    <script>
        // Stripe functionality (only if available)
        @if($intent && env('STRIPE_KEY'))
            const stripe = Stripe('{{ env('STRIPE_KEY') }}');
            const elements = stripe.elements();
            const cardElement = elements.create('card');
            cardElement.mount('#card-element');

            const cardHolderName = document.getElementById('card-holder-name');
            const cardButton = document.getElementById('card-button');
            
            if (cardButton) {
                const clientSecret = cardButton.dataset.secret;

                cardButton.addEventListener('click', async (e) => {
                    const { setupIntent, error } = await stripe.confirmCardSetup(
                        clientSecret, {
                            payment_method: {
                                card: cardElement,
                                billing_details: { name: cardHolderName.value }
                            }
                        }
                    );

                    if (error) {
                        alert('Error: ' + error.message);
                    } else {
                        const paymentMethodHidden = document.getElementById('paymentMethodHidden');
                        if (paymentMethodHidden) {
                            paymentMethodHidden.value = setupIntent.payment_method;
                        }
                        const form = document.getElementById('addOrUpdatePaymentForm');
                        if (form) {
                            form.submit();
                        }
                    }
                });
            }
        @endif

        // Payment method display functions
        function displayBankDetails() {
            var banktransferCheckBox = document.getElementById('banktransfer_allowed');
            var banktransferDetails = document.getElementById('banktransferdetails');

            if(banktransferCheckBox.checked === true) {
                banktransferDetails.style.display = 'block';
            } else {
                banktransferDetails.style.display = 'none';
            }
        }

        function displayPaypalDetails() {
            var paypaltransferCheckBox = document.getElementById('paypal_allowed');
            var paypaltransferDetails = document.getElementById('paypaldetails');

            if(paypaltransferCheckBox.checked === true) {
                paypaltransferDetails.style.display = 'block';
            } else {
                paypaltransferDetails.style.display = 'none';
            }
        }

        // Floating Save Button functionality
        @if(auth()->user()->is_guide)
            let formChanged = false;
            const floatingSaveBtn = document.getElementById('floatingSaveBtn');
            const paymentMethodsForm = document.getElementById('paymentMethodsForm');
            
            if (floatingSaveBtn && paymentMethodsForm) {
                function showFloatingSaveButton() {
                    if (!formChanged) {
                        formChanged = true;
                        floatingSaveBtn.classList.add('show');
                    }
                }
                
                // Show floating button when any form field changes
                const formInputs = document.querySelectorAll('#paymentMethodsForm input, #paymentMethodsForm textarea, #paymentMethodsForm select');
                formInputs.forEach(input => {
                    input.addEventListener('input', showFloatingSaveButton);
                    input.addEventListener('change', showFloatingSaveButton);
                });
                
                // Show floating button on checkbox changes
                const checkboxes = document.querySelectorAll('#paymentMethodsForm input[type="checkbox"]');
                checkboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', showFloatingSaveButton);
                });
                
                // Hide floating button on form submit
                paymentMethodsForm.addEventListener('submit', function() {
                    floatingSaveBtn.classList.remove('show');
                });
                
                // Show floating button on scroll (alternative trigger)
                let ticking = false;
                function updateFloatingButton() {
                    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                    if (scrollTop > 300 && formChanged) {
                        floatingSaveBtn.classList.add('show');
                    }
                    ticking = false;
                }
                
                window.addEventListener('scroll', function() {
                    if (!ticking) {
                        requestAnimationFrame(updateFloatingButton);
                        ticking = true;
                    }
                });
            }
        @endif
    </script>
@endsection
