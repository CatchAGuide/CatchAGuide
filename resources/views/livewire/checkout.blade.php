<div>
  <div wire:loading wire:target="checkout" class="overlay-container">
    <div class="overlay">
      <div class="spinner">
        <div class="spinner-icon" style="background-image: url({{asset('/assets/images/fish.png')}})"></div>
      </div>
      <div class="message">
        @lang('checkout.please_wait_while_processing')
      </div>
    </div>
  </div>

  <div id="all">
    <div id="content">
      <div class="container shadow-lg rounded p-0 my-4">

        <div class="card px-0 pt-1 pb-0 mt-3">
          <div class="text-center">
            <div class="my-2">
              <h2><strong>@lang('checkout.checkout') </strong></h2>
            </div>
          </div>
          <ul id="progressbar">
            <li class="active" id="account"><strong>@lang('checkout.booking_information')</strong></li>
            <li class="{{ $this->page === 2 ? 'active' : '' }}" id="personal"><strong>@lang('checkout.reservation')</strong></li>
          </ul>
        </div>

        <div class="row">
          <div class="col-md-12 clearfix" id="checkout">
            <div class="border-0">
              <form wire:submit.prevent="checkout" method="POST">
                <div class="tab-content" id="pills-tabContent">
                  <div class="tab-pane fade {{ $this->page === 1 ? 'show active' : '' }}" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
                    <div class="row">
                      <div class="col-lg-6 my-3 p-0">
                        <div class="card shadow px-2 pb-3">
                          <div class="my-4">
                            <h5 class="fw-bold">@lang('checkout.date_selection')</h5>
                          </div>
                            <div class="alert alert-warning mb-3" role="alert" id="dateSelectionAlert">
                               <i class="fas fa-calendar-alt me-2"></i>
                               @if($selectedDate)
                                {{ str_replace('{date}', \Carbon\Carbon::parse($selectedDate)->format('F j, Y'), __('checkout.you_have_selected_date')) }}
                               @else
                                @lang('checkout.please_select_a_date_to_proceed')
                               @endif
                           </div>
                           <!-- Debug: selectedDate = "{{ $selectedDate }}" -->
                          @error('selectedDate')
                          <div class="alert alert-danger mb-3" role="alert">
                              <i class="fas fa-exclamation-circle me-2"></i>
                              {{ $message }}
                          </div>
                          @enderror
                          
                          <!-- Calendar Legend -->
                          <div class="calendar-legend">
                              <div class="legend-item">
                                  <div class="legend-color legend-available"></div>
                                  <span>{{ __('checkout.available_for_request') }}</span>
                              </div>
                              <div class="legend-item">
                                  <div class="legend-color legend-blocked"></div>
                                  <span>{{ __('checkout.blocked') }}</span>
                              </div>
                          </div>
                          
                          <div class="col-md-12 d-flex justify-content-center">
                            <div id="lite-datepicker" wire:ignore></div>
                          </div>
                        </div>
                      </div>
                      
                      <div class="col-lg-6 my-3 p-0 p-sm-3">    
                        <div class="card shadow p-3">
                          @if($this->page === 1 && !auth()->check())
                            <div class="checkout-options-container mb-4">
                              <div class="d-flex align-items-start">
                                <i class="fas fa-user text-orange fs-4 me-3"></i>
                                <span>
                                  <a href="#" id="login-header" class="text-orange fw-bold text-decoration-none" data-bs-toggle="modal" data-bs-target="#loginModal" wire:click="$set('checkoutType', 'login')">@lang('checkout.sign_in')</a>
                                  @lang('checkout.to_book_with_your_saved_data_or')
                                  <a href="#" id="signup-header" class="text-orange fw-bold text-decoration-none" data-bs-toggle="modal" data-bs-target="#registerModal" wire:click="$set('checkoutType', 'register')">@lang('checkout.sign_up')</a>
                                  @lang('checkout.to_process_your_bookings_on_the_go')
                                </span>
                              </div>
                            </div>
                          @endif
  
                          @if(auth()->check())
                            <div class="mb-4">
                              <h5 class="fw-bold">@lang('checkout.fill_in_your_details')</h5>
                            </div>
                          @else
                            <div class="mb-4">
                              <h5 class="fw-bold">@lang('checkout.guest_information')</h5>
                            </div>
                          @endif
                          <div class="alert alert-warning mb-3" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            @lang('checkout.almost_done_required_fields')
                            <span class="text-danger">*</span> 
                          </div>
  
                          @if($errors->any())
                          <div class="alert alert-danger m-0 p-2 my-2" role="alert">
                            <div class="d-flex flex-column">
                              @foreach($errors->all() as $error)
                                <small class="text-danger">{{ $error }}</small>
                              @endforeach
                            </div>
                          </div>
                          @endif
  
                          <div class="row">
                            <div class="col-md-6">
                              <div class="form-group">
                                <label> @lang('checkout.first_name')<span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('userData.firstname') is-invalid @enderror" 
                                       placeholder="@lang('checkout.first_name')" 
                                       id="firstname" 
                                       wire:model.debounce.2s="userData.firstname" 
                                       required>
                                @error('userData.firstname')
                                  <div class="invalid-feedback">
                                    {{ $message }}
                                  </div>
                                @enderror
                              </div>
                            </div>
  
                            <div class="col-md-6">
                              <div class="form-group">
                                <label>@lang('checkout.last_name')<span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('userData.lastname') is-invalid @enderror" 
                                       placeholder="@lang('checkout.last_name')" 
                                       id="lastname" 
                                       wire:model.debounce.2s="userData.lastname" 
                                       required>
                                @error('userData.lastname')
                                  <div class="invalid-feedback">
                                    {{ $message }}
                                  </div>
                                @enderror
                              </div>
                            </div>
  
                            <div class="col-12">
                              <div class="form-group">
                                <label>@lang('checkout.email_address')<span class="text-danger">*</span></label>
                                <input type="email" 
                                       class="form-control @error('userData.email') is-invalid @enderror" 
                                       id="email" 
                                       wire:model.debounce.2s="userData.email" 
                                       required>
                                <small class="text-muted">@lang('checkout.confirmation_email_sent_to_address')</small>
                                @error('userData.email')
                                  <div class="invalid-feedback">
                                    {{ $message }}
                                  </div>
                                @enderror
                              </div>
                            </div>
                            
                            <div class="col-12">
                              <h5 class="fw-bold">@lang('checkout.your_address')</h5>
                            </div>
                            <br>
  
                            <div class="col-12">
                              <div class="form-group">
                                <label>@lang('checkout.address')<span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('userData.address') is-invalid @enderror" 
                                       id="address" 
                                       wire:model.debounce.2s="userData.address" 
                                       required>
                                @error('userData.address')
                                  <div class="invalid-feedback">
                                    {{ $message }}
                                  </div>
                                @enderror
                              </div>
                            </div>
  
                            <div class="col-6">
                              <div class="form-group">
                                <label>@lang('checkout.city')<span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('userData.city') is-invalid @enderror" 
                                       id="city" 
                                       wire:model.debounce.2s="userData.city" 
                                       required>
                                @error('userData.city')
                                  <div class="invalid-feedback">
                                    {{ $message }}
                                  </div>
                                @enderror
                              </div>
                            </div>
  
                            <div class="col-sm-6">
                              <div class="form-group">
                                <label for="country">@lang('checkout.country_region') <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="country" wire:model="userData.country" required>
                              </div>
                            </div>
  
                            <div class="col-6">
                              <div class="form-group">
                                <label>@lang('checkout.postal_code_optional') </label>
                                <input type="text" 
                                       class="form-control @error('userData.postal') is-invalid @enderror" 
                                       id="postal" 
                                       wire:model="userData.postal">
                                @error('userData.postal')
                                  <div class="invalid-feedback">
                                    {{ $message }}
                                  </div>
                                @enderror
                              </div>
                            </div>
  
                            <div class="col-12">
                              <div class="form-group">
                                @include('includes.forms.phone-input', [
                                    'name' => 'userData.phone',
                                    'id' => 'phone',
                                    'countryCodeName' => 'userData.countryCode',
                                    'countryCodeId' => 'countryCode',
                                    'selectedCountryCode' => $userData['countryCode'] ?? '+49',
                                    'phoneValue' => $userData['phone'] ?? '',
                                    'showLabel' => true,
                                    'showHelpText' => true,
                                    'wireModel' => 'userData.phone',
                                    'required' => true,
                                    'wireModelCountryCode' => 'userData.countryCode',
                                    'errorClass' => $errors->has('userData.phone') || $errors->has('userData.countryCode') ? 'is-invalid' : ''
                                ])
                                @error('userData.countryCode')
                                  <div class="invalid-feedback">
                                    {{ $message }}
                                  </div>
                                @enderror
                                @error('userData.phone')
                                  <div class="invalid-feedback">
                                    {{ $message }}
                                  </div>
                                @enderror
                              </div>
                            </div>
  
                            {{-- @if($checkoutType === 'guest')
                              <div class="col-12">
                                <div class="form-group">
                                  <div class="d-flex align-items-start">
                                    <input type="checkbox" 
                                           class="form-check-input mt-1 me-2 @error('userData.createAccount') is-invalid @enderror" 
                                           id="createAccount" 
                                           wire:model="userData.createAccount"
                                           style="min-width: 16px;">
                                    <label class="form-check-label" for="createAccount" style="font-size: 14px;">
                                    @lang('checkout.save_data_create_account')
                                      <a href="{{ route('law.agb') }}" target="_blank">@lang('checkout.terms_and_conditions')</a>
                                      {{ translate('and') }}
                                      <a href="{{ route('law.data-protection') }}" target="_blank">@lang('checkout.privacy_policy')</a>
                                    </label>
                                  </div>
                                  @error('userData.createAccount')
                                    <div class="invalid-feedback">
                                      {{ $message }}
                                    </div>
                                  @enderror
                                </div>
                              </div>
                            @endif --}}
                            
                            <div class="col-md-12">
                              <div class="row-buttons">
                                <div class="button-container">  
                                  <!-- zurück -->
                                <button class="thm-btn" type="button" onclick="window.history.back()"> <i class="fa fa-chevron-left"></i> @lang('message.return') </button>
                                </div>
                                <div class="button-container">
                                   @if($page === 1)
                                     <div class="pull-right">
                                       <button class="thm-btn disabled" type="button" wire:click="next" id="nextButton" disabled>
                                         @lang('message.further') <i class="fa fa-chevron-right"></i>
                                       </button>
                                     </div>
                                  @elseif ($page !== 2)
                                    <div class="pull-right">
                                      <button class="thm-btn" wire:click="next" id="checkoutProceedPage2">@lang('message.further') <i class="fa fa-chevron-right"></i></button>
                                    </div>
                                  @endif
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>      
                      </div>
                    </div>
                  </div>

                  <div class="tab-pane fade {{ $this->page === 2 ? 'show active' : '' }}" id="pills-orders" role="tabpanel" aria-labelledby="pills--tab">
                    <div class="alert alert-info note-box" role="alert">
                      @lang('forms.guidTitleMsg')
                    </div>
                    <div class="row d-flex justify-content-center checkout-container">
                      <div class="col-lg-8 col-md-8 col-sm-12 my-2 px-0 px-sm-4">
                        <!-- Guiding Information Card -->
                        <div class="card mb-4 shadow p-1">                          
                          <div class="card-body">
                            <div class="guide-info mt-3">
                              <div class="mb-2">
                                <h5><span class="bordered-heading">@lang('message.guiding-information')</span></h5>
                              </div>
                              <h6 class="my-1">{{$guiding->title}}</h6>
                              <div class="d-flex align-items-center mb-2">
                                <div>
                                  @if($guiding->user->profil_image)
                                    <img class="rounded-circle" src="{{asset('images/'. $guiding->user->profil_image)}}" alt="" width="24" height="24">
                                  @else
                                    <img class="rounded-circle" src="{{asset('images/placeholder_guide.jpg')}}" alt="" width="24" height="24">
                                  @endif
                                </div>
                                <div class="mx-2">
                                  <span>{{$guiding->user->firstname}}</span>
                                </div>
                              </div>
                              <div class="p-3 bg-light rounded">
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
                            </div>
                          </div>
                        </div>

                        <!-- Personal Information Card -->
                        <div class="card mb-4 shadow p-1">
                          <!-- <div class="card-header bg-light">
                            <h5 class="mb-0">{{ translate('Personal Information') }}</h5>
                          </div> -->
                          <div class="card-body">
                            <div class="mb-2"> 
                              <h5><span class="bordered-heading">@lang('checkout.personal_information')</span></h5>
                            </div>
                            <div class="row g-3">
                              <div class="col-md-6">
                                <span class="text-dark fw-bold">@lang('checkout.first_name')</span>
                                <p class="form-control-static">{{ $userData['firstname'] }}</p>
                              </div>
                              <div class="col-md-6">
                                <span class="text-dark fw-bold">@lang('checkout.last_name')</span>
                                <p class="form-control-static">{{ $userData['lastname'] }}</p>
                              </div>
                              <div class="col-12">
                                <span class="text-dark fw-bold">@lang('checkout.email_address')</span>
                                <p class="form-control-static">{{ $userData['email'] }}</p>
                              </div>
                              <div class="col-12">
                                <span class="text-dark fw-bold">@lang('checkout.address')</span>
                                <p class="form-control-static">{{ $userData['address'] }}</p>
                              </div>
                              <div class="col-md-4">
                                <span class="text-dark fw-bold">{{ translate('Postal code') }}</span>
                                <p class="form-control-static">{{ $userData['postal'] }}</p>
                              </div>
                              <div class="col-md-4">
                                <span class="text-dark fw-bold">@lang('checkout.city')</span>
                                <p class="form-control-static">{{ $userData['city'] }}</p>
                              </div>
                              <div class="col-md-4">
                                <span class="text-dark fw-bold">@lang('checkout.country_region')</span>
                                <p class="form-control-static">{{ $userData['country'] }}</p>
                              </div>
                              <div class="col-12">
                                <span class="text-dark fw-bold">{{ translate('Phone Number') }}</span>
                                <p class="form-control-static">{{ $userData['countryCode'] }} {{ $userData['phone'] }}</p>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="col-lg-4 col-md-4 col-sm-12 my-2 card shadow p-2 booking-overview">
                          <div class="p-3 rounded d-flex flex-column">
                            <div class="mb-2"><h5><span class="bordered-heading">@lang('message.booking-overview')</span></h5></div>
                            <div class="my-2">
                              <div class="px-2 py-1 d-flex">
                                <div class="col-8">
                                 <span class="text-dark fw-bold">
                                   @lang('message.total-guest'):</div>
                                 </span> 
                                <div class="ml-auto">{{$persons}}</div>
                              </div>
                              <div class="px-2 py-1 d-flex">
                                <div class="col-8">
                                <span class="text-dark fw-bold">
                                  @lang('message.booking-date'):
                                 </span> 
                                </div>
                                <div class="ml-auto">{{$formattedDate}}</div>
                              </div>
                              <div class="border-top px-4 mx-3"></div>
                              <div class="px-2 py-1 d-flex">
                                <div class="col-8">
                                <span class="text-dark fw-bold">
                                  @lang('message.guiding-price'):
                                 </span>   
                              </div>
                                <div class="ml-auto">€{{$guidingprice}}</div>
                              </div>@if(count($extras))
                              <div class="px-2 py-1 mb-2 bg-light">
                                <div>
                                  <span class="color-primary">Extras</span>
                                </div>
                                <div class="row p-2">
                                  <div>
                                    @foreach($extras as $index => $extra)
                                    <div class="col-md-12 extra-container">
                                        <div class="d-flex flex-column">
                                            <div class="form-check p-0">
                                                <label class="form-check-label text-dark">
                                                    <input 
                                                        type="checkbox" 
                                                        class="form-check-input me-1" 
                                                        wire:model="selectedExtras.{{$index}}"
                                                        wire:change="$refresh"
                                                    >
                                                    {{$extra['name']}} - €{{$extra['price']}}
                                                </label>
                                            </div>
                                            @if($selectedExtras[$index])
                                                <div class="quantity-container">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <label for="quantity_{{$index}}">Quantity:</label>
                                                        <input 
                                                            id="quantity_{{$index}}"
                                                            class="w-25 form-control form-control-sm mx-2 quantity-input" 
                                                            type="number"
                                                            min="1"
                                                            max="{{$persons}}"
                                                            wire:model="extraQuantities.{{$index}}"
                                                            wire:change="calculateTotalPrice"
                                                        >
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                  </div>
                                </div>
                              </div>
                              @endif

                              @if($totalExtraPrice > 0)
                              <div class="px-2 py-1 d-flex">
                                <div class="col-8">
                                <span class="text-dark fw-bold">
                                  @lang('message.total-extras'):
                                 </span>   
                              </div>
                                <div class="ml-auto">€{{$totalExtraPrice}}</div>
                              </div>
                              @endif
                                                             <div class="border-top px-4 mx-3"></div>
                               <div class="px-2 py-1 d-flex pt-3">
                                 <div class="col-8">
                                 <span class="text-dark fw-bold">
                                   Total
                                  </span>   
                               </div>
                                 <div class="ml-auto"><b class="green">€{{$totalPrice}}</b></div>
                               </div>
                               
                               <!-- Payment method icons below total price -->
                             </div>
                           </div>
                           <div class="alert alert-info note-box" role="alert">
                            <h6 class="mb-3">@lang('checkout.important_payment_info_title')</h6>
                            <p class="mb-3">@lang('checkout.payment_info_text', ['price' => $totalPrice])</p>
                            <div class="payment-methods-container mb-3">
                              @php
                                  $paymentMethods = [];
                                  if ($guiding->user->bar_allowed) {
                                      $paymentMethods[] = 'Cash';
                                  }
                                  if ($guiding->user->banktransfer_allowed) {
                                      $paymentMethods[] = 'Bank transfer';
                                  }
                                  if ($guiding->user->paypal_allowed) {
                                      $paymentMethods[] = 'PayPal';
                                  }
                              @endphp
                              
                              @if ($guiding->user->bar_allowed)
                                <div class="payment-method-item">
                                  <i class="fas fa-money-bill payment-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('booking.pay_onsite') }}"></i>
                                  <span class="payment-method-name">{{ __('booking.pay_onsite') }}</span>
                                </div>
                              @endif
                              
                              @if ($guiding->user->banktransfer_allowed)
                                <div class="payment-method-item">
                                  <i class="fas fa-credit-card payment-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('booking.accepts_bank_transfer') }}"></i>
                                  <span class="payment-method-name">{{ __('booking.bank_transfer') }}</span>
                                </div>
                              @endif
                              
                              @if ($guiding->user->paypal_allowed)
                                <div class="payment-method-item">
                                  <i class="fab fa-paypal payment-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('booking.accepts_paypal') }}"></i>
                                  <span class="payment-method-name">PayPal</span>
                                </div>
                              @endif
                            </div>
                            <p class="mb-0">@lang('checkout.payment_confirmation_text')</p>
                          </div>
                        
                          @if($checkoutType === 'guest' && !$userData['createAccount'])
                            <div class="col-12">
                              <div class="form-group terms-acceptance-container">
                                <div class="d-flex align-items-start">
                                  <input type="checkbox" 
                                         class="form-check-input mt-1 me-2 @error('userData.guestCheckTerms') is-invalid @enderror" 
                                         id="guestCheckTerms" 
                                         wire:model="userData.guestCheckTerms"
                                         style="min-width: 16px;">

                                  <label class="form-check-label" for="guestCheckTerms" style="font-size: 14px;">
                                    <span class="text-danger me-1">*</span>
                                    @lang('checkout.terms_acceptance_text')
                                    <a href="{{ route('law.agb') }}" target="_blank" class="text-primary fw-bold">@lang('checkout.terms_and_conditions')</a>
                                    @lang('checkout.and')
                                    <a href="{{ route('law.data-protection') }}" target="_blank" class="text-primary fw-bold">@lang('checkout.privacy_policy')</a>@lang('checkout.terms_acceptance_end')
                                  </label>
                                </div>
                                @error('userData.guestCheckTerms')
                                  <div class="invalid-feedback">
                                    {{ $message }}
                                  </div>
                                @enderror
                              </div>
                            </div>
                          @endif

                          <div class="booking-actions mt-4">
                            @if($checkoutType === 'guest' && !$userData['createAccount'] && !$userData['guestCheckTerms'])
                              <div class="alert alert-warning terms-reminder mb-3" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                @lang('checkout.please_accept')
                              </div>
                            @endif

                            <div class="d-grid gap-2">
                              <button class="btn thm-btn booking-btn {{ ($checkoutType === 'guest' && !$userData['createAccount'] && !$userData['guestCheckTerms']) ? 'disabled-btn' : '' }}" 
                                      type="submit" 
                                      id="checkoutFinal"
                                      @if($checkoutType === 'guest' && !$userData['createAccount'] && !$userData['guestCheckTerms']) 
                                        disabled 
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        title="{{ translate('Please accept the Terms and Conditions to enable booking') }}"
                                      @endif>
                                <i class="fas fa-check-circle me-2"></i>
                                @lang('message.reservation')
                              </button>

                              <button class="btn thm-btn btn-gray booking-btn" type="button" wire:click="prev">
                                <i class="fas fa-arrow-left me-2"></i>
                                @lang('message.return')
                              </button>

                              <a class="btn thm-btn btn-gray booking-btn" href="{{route('guidings.index')}}">
                                <i class="fas fa-times me-2"></i>
                                @lang('message.booking-cancel')
                              </a>
                            </div>
                          </div>
                      </div>
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('js_push')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
  const initCheckNumberOfColumns = () => {
    if (window.innerWidth > 1000) {
      return 2;
    } else if (window.innerWidth > 768) {
      return 2;
    } else {
      return 1;
    }
  }

  // Helper function to format date consistently
  const formatDateForDisplay = (date) => {
    const currentLocale = '{{app()->getLocale()}}';
    
    if (currentLocale === 'de') {
      const monthNames = ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];
      return monthNames[date.getMonth()] + ' ' + date.getDate() + ', ' + date.getFullYear();
    } else {
      const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
      return monthNames[date.getMonth()] + ' ' + date.getDate() + ', ' + date.getFullYear();
    }
  }

  
     document.addEventListener('livewire:load', function () {
     // Initialize tooltips when Livewire loads
     var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
     var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
       return new bootstrap.Tooltip(tooltipTriggerEl);
     });
     
     const blockedEvents = @json($guiding->getBlockedEvents());

    let lockDays = [];
    if (blockedEvents && typeof blockedEvents === 'object') {
      lockDays = Object.values(blockedEvents).flatMap(event => {
          const fromDate = new Date(event.from);
          const dueDate = new Date(event.due);

          // Create an array of all dates in the range
          const dates = [];
          for (let d = new Date(fromDate); d <= dueDate; d.setDate(d.getDate() + 1)) {
              dates.push(d.toISOString().split('T')[0]); // Format as YYYY-MM-DD
          }
          return dates;
      });
    }

    // Get initial selected date from Livewire
    const initialSelectedDate = @this.selectedDate || null;
    
    // No need to manually set up the message - Livewire handles it automatically
    
    const picker = new Litepicker({
      element: document.getElementById('lite-datepicker'),
      inlineMode: true,
      singleDate: true,
      numberOfColumns: initCheckNumberOfColumns(),
      numberOfMonths: initCheckNumberOfColumns(),
      minDate: new Date(new Date().getTime() + 24 * 60 * 60 * 1000),
      lockDays: lockDays, // Use the dynamically calculated blocked days
      lockDaysFormat: 'YYYY-MM-DD',
      disallowLockDaysInRange: true,
      startDate: initialSelectedDate ? new Date(initialSelectedDate) : null, // Set initial date
      autoApply: true, // Auto-apply date selection (no apply button needed)
      allowRepick: true, // Allow repicking dates
      lang: '{{app()->getLocale()}}',
      setup: (picker) => {
        picker.on('selected', (date1, date2) => {
          const selectedDate = date1.format('YYYY-MM-DD');
          
          // Livewire set date and force reactivity
          @this.set('selectedDate', selectedDate);
          @this.setSelectedTime('00:00');
          
          // Set Date to function for display
          $("#currentDate").html(date1.format('DD-MM-YYYY'));
          
          // Force Livewire to refresh and trigger validation
          @this.$refresh().then(() => {
            // Trigger validation after Livewire updates
            setTimeout(() => {
              if (typeof validateForm === 'function') {
                validateForm();
              }
            }, 100);
          });
        });

        picker.on('cleared', () => {
          // Clear the selected date using Livewire set method
          @this.set('selectedDate', null);
          $("#currentDate").html('');
          
          // Force Livewire to refresh and trigger validation
          @this.$refresh().then(() => {
            // Trigger validation after Livewire updates
            setTimeout(() => {
              if (typeof validateForm === 'function') {
                validateForm();
              }
            }, 100);
          });
        });

        // Change picker columns on resize
        window.addEventListener('resize', () => {
          picker.setOptions({
            numberOfColumns: initCheckNumberOfColumns(),
            numberOfMonths: initCheckNumberOfColumns()
          });
        });
      },
    });
  });
</script>
<script>
document.addEventListener('livewire:load', function () {
  // Handle numeric input validation
  document.addEventListener('input', function(e) {
    if (e.target.classList.contains('quantity-input')) {
      let value = e.target.value.replace(/[^0-9]/g, '');
      if (value === '') value = '1';
      if (parseInt(value) < 1) value = '1';
      e.target.value = value;
    }
  });


  // Handle checkbox changes
  Livewire.on('extraChanged', (extraId) => {
    const checkboxes = document.querySelectorAll('.form-check-input');
    checkboxes.forEach(checkbox => {
      if (checkbox.value === extraId.toString()) {
        const container = checkbox.closest('.extra-container').querySelector('.quantity-container');
        if (container) {
          container.style.display = checkbox.checked ? 'block' : 'none';
          
          // Initialize quantity input when showing
          if (checkbox.checked) {
            const quantityInput = container.querySelector('.quantity-input');
            if (quantityInput && !quantityInput.value) {
              quantityInput.value = '1';
            }
          }
        }
      }
    });
  });
});
document.addEventListener('livewire:next', function () {
  function scrollToFormCenter() {
          const form = document.getElementById('all');
          if (form) {
              const formTop = form.getBoundingClientRect().top + window.pageYOffset;
              window.scrollTo({ 
                  top: formTop - 50,
                  behavior: 'smooth'
              });
          }
      }
      scrollToFormCenter();
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Initialize tooltips
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
  });

  // Highlight terms checkbox when disabled button is clicked
  document.querySelector('.disabled-btn')?.addEventListener('click', function(e) {
    if (this.disabled) {
      const termsContainer = document.querySelector('.terms-acceptance-container');
      termsContainer.classList.add('highlight-terms');
      setTimeout(() => {
        termsContainer.classList.remove('highlight-terms');
      }, 2000);
    }
  });
  
  // Simple button validation with debouncing
  let validationTimeout;
  
  function validateForm() {
    // Clear any existing timeout
    if (validationTimeout) {
      clearTimeout(validationTimeout);
    }
    
    // Debounce validation to prevent excessive calls
    validationTimeout = setTimeout(() => {
      const nextButton = document.getElementById('nextButton');
      if (!nextButton) return;
      
        // Get selectedDate from Livewire - handle different data types
        let selectedDateValue;
        
        try {
          // Try to get the raw value first
          selectedDateValue = @this.selectedDate;
          
          // If it's an object, try to extract the date value
          if (selectedDateValue && typeof selectedDateValue === 'object') {
            // Check if it has a value property or is a Date object
            if (selectedDateValue.value) {
              selectedDateValue = selectedDateValue.value;
            } else if (selectedDateValue instanceof Date) {
              selectedDateValue = selectedDateValue.toISOString().split('T')[0];
            } else {
              // Try to convert to string and see if it contains a date
              const stringValue = String(selectedDateValue);
              if (stringValue && stringValue !== '[object Object]' && stringValue.match(/\d{4}-\d{2}-\d{2}/)) {
                selectedDateValue = stringValue;
              } else {
                selectedDateValue = null;
              }
            }
          }
        } catch (e) {
          console.log('Error accessing selectedDate:', e);
          selectedDateValue = null;
        }
        
        // First check - direct value validation
        let hasDate = selectedDateValue && 
                     typeof selectedDateValue === 'string' &&
                     selectedDateValue.trim() !== '' && 
                     selectedDateValue !== 'null' && 
                     selectedDateValue.length >= 10 && // YYYY-MM-DD format
                     selectedDateValue.match(/^\d{4}-\d{2}-\d{2}$/);
        
        // Fallback check - look for visual indicators that a date is selected
        if (!hasDate) {
          // Check if the calendar has a selected date (look for selected day in calendar)
          const selectedDay = document.querySelector('.litepicker .day-item.is-selected, .litepicker .day-item.is-start-date');
          const dateAlert = document.getElementById('dateSelectionAlert');
          
          // If we see a selected day in calendar or the date alert shows a selected date
          if (selectedDay || (dateAlert && dateAlert.textContent.includes('selected'))) {
            // Try to get the date from Livewire using a different approach
            try {
              const livewireData = window.Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'));
              if (livewireData && livewireData.get('selectedDate')) {
                selectedDateValue = livewireData.get('selectedDate');
                hasDate = selectedDateValue && typeof selectedDateValue === 'string' && selectedDateValue.length >= 10;
              }
            } catch (e) {
              console.log('Fallback date check failed:', e);
            }
          }
        }
      const firstname = document.getElementById('firstname')?.value?.trim() || '';
      const lastname = document.getElementById('lastname')?.value?.trim() || '';
      const email = document.getElementById('email')?.value?.trim() || '';
      const address = document.getElementById('address')?.value?.trim() || '';
      const city = document.getElementById('city')?.value?.trim() || '';
      const country = document.getElementById('country')?.value?.trim() || '';
      const phone = document.getElementById('phone')?.value?.trim() || '';
      const countryCode = document.getElementById('countryCode')?.value?.trim() || '';
       
      // Phone number must have at least 3 digits to be considered valid
      const isPhoneValid = phone && phone.length >= 3;
       
        // Check all required fields: selectedDate, firstname, lastname, email, address, city, country, phone
        const isValid = !!(hasDate && firstname && lastname && email && address && city && country && isPhoneValid && countryCode);
       
        // Debug validation (remove in production)
        const rawLivewireDate = @this.selectedDate;
        const selectedDayElement = document.querySelector('.litepicker .day-item.is-selected, .litepicker .day-item.is-start-date');
        const dateAlert = document.getElementById('dateSelectionAlert');
        
        console.log('Form validation status:', {
          rawLivewireDate: rawLivewireDate,
          rawLivewireType: typeof rawLivewireDate,
          processedDateValue: selectedDateValue,
          processedDateType: typeof selectedDateValue,
          selectedDateLength: selectedDateValue ? selectedDateValue.length : 'N/A',
          hasSelectedDayInCalendar: !!selectedDayElement,
          dateAlertContent: dateAlert ? dateAlert.textContent.substring(0, 50) : 'N/A',
          hasDate: hasDate,
          firstname: !!firstname,
          lastname: !!lastname, 
          email: !!email,
          address: !!address,
          city: !!city,
          country: !!country,
          phone: isPhoneValid,
          countryCode: !!countryCode,
          isValid: isValid
        });
      
       // Update button state: enabled only when all fields are valid
       nextButton.disabled = !isValid;
       nextButton.className = isValid ? 'thm-btn' : 'thm-btn disabled';
     }, 1000); // Increased to prevent DDoS protection triggers
  }
  
   // Run validation on page load (button starts disabled, only enables when all fields valid)
   if (typeof validateForm === 'function') {
     validateForm();
   }
   
   // Also run validation after a short delay to ensure Livewire is fully loaded
   setTimeout(() => {
     if (typeof validateForm === 'function') {
       validateForm();
     }
   }, 500);
  
  const inputs = ['firstname', 'lastname', 'email', 'address', 'city', 'country', 'phone', 'countryCode'];
  inputs.forEach(function(inputId) {
    const input = document.getElementById(inputId);
    if (input) {
      // Use debounced validation for input events to prevent DDoS triggers during autofill
      let inputTimeout;
      input.addEventListener('input', function() {
        clearTimeout(inputTimeout);
        inputTimeout = setTimeout(() => {
          if (typeof validateForm === 'function') {
            validateForm();
          }
        }, 800);
      });
      input.addEventListener('change', () => {
        if (typeof validateForm === 'function') {
          validateForm();
        }
      });
      input.addEventListener('blur', () => {
        if (typeof validateForm === 'function') {
          validateForm();
        }
      });
    }
  });
  
  // Watch for Livewire updates
  document.addEventListener('livewire:updated', () => {
    if (typeof validateForm === 'function') {
      validateForm();
    }
  });
  
  // Listen for specific Livewire selectedDate changes
  document.addEventListener('livewire:load', function () {
    @this.watch('selectedDate', value => {
      console.log('Livewire selectedDate changed to:', value);
      setTimeout(() => {
        if (typeof validateForm === 'function') {
          validateForm();
        }
      }, 100);
    });
  });
  
  // Re-initialize tooltips after Livewire updates
  document.addEventListener('livewire:updated', function() {
    // Destroy existing tooltips to prevent duplicates
    var existingTooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    existingTooltips.forEach(function(element) {
      var tooltip = bootstrap.Tooltip.getInstance(element);
      if (tooltip) {
        tooltip.dispose();
      }
    });
    
    // Re-initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
  });
});
</script>

@endpush

{{-- Checkout styles are now handled by SCSS compilation --}}
