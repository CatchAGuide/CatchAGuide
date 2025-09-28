@extends('layouts.app-v2-1')

@section('title', 'Booking Request')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.0.3/css/font-awesome.css" />
    {{-- Checkout-specific styles are now handled by SCSS compilation --}}
    <!-- template styles -->
    <link rel="stylesheet" href="{{ asset('assets/css/tevily.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/tevily-responsive.css') }}" />
    <!--Page Header Start-->
    {{-- <section class="page-header">
        <div class="page-header__top">
            <div class="page-header-bg-magazin" style="background-image: url({{asset('assets/images/shutterstock_620805824.jpg')}}); "></div>
            <div class="page-header-bg-overly-magazin"></div>
            <div class="container">
                <div class="page-header__top-inner">
                    <h2>@yield('title')</h2>
                </div>
            </div>
        </div>
        
        <div class="alert-warning mb-6 mobile-order-1-2">
            {!! __('checkout.fishing_permit_warning') !!}
        </div>

        <div class="checkout-grid">
            <!-- Main Content -->
            <div class="main-content">
                <!-- Guiding Information -->
                <section class="info-card highlight mobile-order-7">
                    <div class="p-3 border-b">
                        <h5 class="text-lg font-semibold">{{ __('checkout.your_guiding') }}</h5>
                    </div>
                    <div class="p-3" x-show="guiding">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div class="info-row">
                                <div class="info-label">{{ __('checkout.title') }}</div>
                                <div class="info-value" x-text="guiding?.title || '{{ __('checkout.loading') }}'"></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">{{ __('checkout.location') }}</div>
                                <div class="info-value" x-text="guiding?.location || '{{ __('checkout.not_specified') }}'"></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">{{ __('checkout.duration') }}</div>
                                <div class="info-value" x-text="guiding?.duration + ' ' + (guiding?.duration_type === 'multi_day' ? '{{ __('checkout.days') }}' : '{{ __('checkout.hours') }}')"></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">{{ __('checkout.target_fish') }}</div>
                                <div class="info-value" x-text="guiding?.target_fish?.join(', ') || '{{ __('checkout.loading') }}'"></div>
                            </div>
                            <div class="info-row" x-show="guiding?.requirements?.length">
                                <div class="info-label">{{ __('checkout.requirements') }}</div>
                                <div class="info-value">
                                    <template x-for="req in guiding?.requirements || []" :key="req.name">
                                        <div x-text="req.name + ': ' + req.value"></div>
                                    </template>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">{{ __('checkout.date') }}</div>
                                <div class="info-value" x-text="selectedDate ? formatDate(selectedDate) : '{{ __('checkout.please_select_date') }}'"></div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Optional Extras -->
                <section class="info-card mobile-order-3" x-show="guiding?.extras?.length">
                    <div class="p-3">
                        <h5 class="text-lg font-semibold text-slate-800 mb-1">{{ __('checkout.optional_extras') }}</h5>
                        <p class="text-sm text-slate-600 mb-2">{{ __('checkout.prices_per_person') }}</p>
                        
                        <div class="border-t border-slate-200 pt-2">
                            <div class="space-y-5">
                                <template x-for="(extra, index) in guiding?.extras || []" :key="index">
                                    <div class="flex items-center justify-between py-1">
                                        <div class="flex items-center">
                                            <input 
                                                type="checkbox" 
                                                :id="'extra-' + index"
                                                x-model="selectedExtras[index]"
                                                @change="calculatePrice()"
                                                class="w-4 h-4 text-red-600 border-slate-300 rounded focus:ring-red-500 extra-checkbox"
                                            >
                                            <label :for="'extra-' + index" class="text-sm font-medium text-slate-700" x-text="extra.name"></label>
                                        </div>
                                        <div class="text-sm font-medium text-slate-800" x-text="'+€' + extra.price + ' {{ __('checkout.per_person') }}'"></div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- User Details Form -->
                <section class="info-card form-section mobile-order-5">
                    <div class="p-3 border-b">
                        <div class="form-header">
                            <h5 class="text-lg font-semibold">{{ __('checkout.your_details') }}</h5>
                            <div class="mode-selector" x-show="!isLoggedIn">
                                <button 
                                    @click="openLoginModal()" 
                                    class="mode-button"
                                >
                                    {{ __('checkout.log_in') }}
                                </button>
                                <button 
                                    @click="openRegisterModal()" 
                                    class="mode-button"
                                >
                                    {{ __('checkout.register') }}
                                </button>
                                <button 
                                    @click="setMode('guest')" 
                                    :class="mode === 'guest' ? 'mode-button active' : 'mode-button'"
                                >
                                    {{ __('checkout.book_as_guest') }}
                                </button>
                            </div>
                        </div>
                        <p class="form-description" x-text="modeText"></p>
                        <p class="form-privacy">{{ __('checkout.contact_info_privacy') }}</p>
                    </div>
                    <div class="p-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('checkout.first_name_required') }}</label>
                            <input
                                type="text"
                                x-model="form.firstName"
                                :disabled="mode === 'login'"
                                class="form-input"
                                placeholder="{{ __('checkout.enter_first_name') }}"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('checkout.last_name_required') }}</label>
                            <input
                                type="text"
                                x-model="form.lastName"
                                :disabled="mode === 'login'"
                                class="form-input"
                                placeholder="{{ __('checkout.enter_last_name') }}"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('checkout.email_address_required') }}</label>
                            <input
                                type="email"
                                x-model="form.email"
                                :disabled="mode === 'login'"
                                class="form-input"
                                placeholder="{{ __('checkout.enter_email') }}"
                            />
                        </div>
                        <div>
                            @include('includes.forms.phone-input', [
                                'name' => 'userData.phone',
                                'id' => 'phone',
                                'countryCodeName' => 'userData.countryCode',
                                'countryCodeId' => 'countryCode',
                                'selectedCountryCode' => '+49',
                                'phoneValue' => '',
                                'showLabel' => true,
                                'showHelpText' => false,
                                'required' => true,
                                'errorClass' => '',
                                'alpineModel' => 'form.phone',
                                'alpineModelCountryCode' => 'form.countryCode',
                                'modernCheckout' => true,
                                'labelText' => 'checkout.phone_number_required'
                            ])
                        </div>
                    </div>
                    
                </section>
            </div>

            <!-- Sidebar -->
            <aside class="sidebar">
                <div class="space-y-4">
                    <!-- Date Selection -->
                    <section class="info-card p-3 calendar-container mobile-order-2">
                        <h5 class="text-lg font-semibold mb-1">{{ __('checkout.choose_preferred_date') }}</h5>
                        <div class="text-sm text-slate-600 mb-3" x-text="selectedDate ? formatDate(selectedDate) : '{{ __('checkout.please_select_date') }}'"></div>
                        <div id="calendar-container"></div>
                    </section>

                    <!-- Booking Summary -->
                    <section class="info-card booking-summary highlight mobile-order-6">
                        <div class="p-3 border-b summary-header">
                            <h5 class="text-lg font-semibold">{{ __('checkout.booking_summary') }}</h5>
                            <div class="guest-counter">
                                <button 
                                    @click="updatePersons(persons - 1)"
                                    class="counter-button"
                                    :disabled="persons <= 1"
                                >−</button>
                                <span x-text="persons"></span>
                                <button 
                                    @click="updatePersons(persons + 1)"
                                    class="counter-button"
                                    :disabled="persons >= (guiding?.max_guest || 10)"
                                >+</button>
                            </div>
                        </div>
                        <div class="p-3 summary-content">
                            <div class="summary-row">
                                <div class="summary-label">{{ __('checkout.guests') }}</div>
                                <div class="summary-value" x-text="persons"></div>
                            </div>
                            <div class="summary-row">
                                <div class="summary-label">{{ __('checkout.date') }}</div>
                                <div class="summary-value" x-text="selectedDate ? formatDate(selectedDate) : '{{ __('checkout.not_selected') }}'"></div>
                            </div>
                            <div class="summary-row">
                                <div class="summary-label">{{ __('checkout.guiding_price_label') }}</div>
                                <div class="summary-value">€<span x-text="pricing.guidingPrice || 0"></span></div>
                            </div>
                            
                            <template x-for="extra in pricing.breakdown?.extras || []" :key="extra.name">
                                <div class="summary-row">
                                    <div class="summary-label" x-text="extra.name + ' (' + extra.quantity + '×)'"></div>
                                    <div class="summary-value">+€<span x-text="extra.total"></span></div>
                                </div>
                            </template>
                            
                            <div class="summary-divider"></div>
                            <div class="summary-row">
                                <div class="summary-label font-semibold">{{ __('checkout.total_label') }}</div>
                                <div class="summary-value font-semibold">€<span x-text="pricing.totalPrice || 0"></span></div>
                            </div>
                        </div>
                    </section>

                    <!-- Payment Info -->
                    <section class="info-card p-3 payment-info mobile-order-8">
                        <h6 class="payment-title">{{ __('checkout.payment_after_confirmation_via') }}</h6>
                        
                        <!-- Payment Method Buttons -->
                        <div class="payment-methods" x-show="guiding?.guide?.payment_methods?.length">
                            <div class="payment-method-buttons">
                                <template x-for="method in guiding?.guide?.payment_methods || []" :key="method">
                                    <div class="payment-method-btn" :class="getPaymentMethodClass(method)">
                                        <div class="payment-icon" :class="getPaymentMethodIcon(method)">
                                            <img :src="getPaymentMethodIconSrc(method)" :alt="method" />
                                        </div>
                                        <span x-text="method"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </section>

                    <!-- Policies above Submit -->
                    <div class="p-1 mobile-order-9">
                        <label class="terms-checkbox text-slate-700">
                            <input
                                type="checkbox"
                                x-model="form.policyAccepted"
                                class="h-4 w-4 rounded border-slate-300"
                            />
                            <span>
                                {{ __('checkout.terms_and_conditions_accept') }} 
                                <a href="{{ route('law.agb') }}" class="underline" target="_blank">{{ __('checkout.terms_and_conditions_link') }}</a> 
                                {{ __('checkout.and') }} 
                                <a href="{{ route('law.data-protection') }}" class="underline" target="_blank">{{ __('checkout.privacy_policy_link') }}</a>.
                            </span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button class="submit-button mobile-order-9"
                        @click="submitBooking()"
                        :disabled="!canSubmit || loading"
                        aria-label="Send booking request"
                        title="Send booking request — free & non-binding"
                    >
                        <i class="fas fa-user me-2"></i>
                        <span x-show="!loading">{{ __('checkout.inquire_free_secure_spot') }}</span>
                        <span x-show="loading">{{ __('checkout.processing') }}</span>
                    </button>

                    <!-- Security Certificates -->
                    <div class="security-certificates mobile-order-10">
                        <div class="cert-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>{{ __('checkout.ssl_secure') }}</span>
                        </div>
                        <div class="cert-item">
                            <i class="fas fa-check"></i>
                            <span>{{ __('checkout.free') }}</span>
                        </div>
                        <div class="cert-item">
                            <i class="fas fa-check"></i>
                            <span>{{ __('checkout.non_binding') }}</span>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </section> --}}
    <!--Page Header End-->
    <livewire:checkout :guiding="$guiding" :persons="$persons" :initial-selected-date="$selectedDate" />
@endsection
