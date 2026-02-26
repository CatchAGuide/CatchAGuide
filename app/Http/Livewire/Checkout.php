<?php

namespace App\Http\Livewire;

use Mail;

use Illuminate\Support\Str;


use App\Models\Booking;
use App\Models\Guiding;
use App\Models\User;
use App\Services\EventService;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

use Carbon\Carbon;
use App\Services\HelperService;
use App\Models\GuidingExtras;
use Illuminate\Support\Facades\Log;

use App\Jobs\SendCheckoutEmail;
use App\Models\UserInformation;
use App\Models\UserGuest;
use App\Models\CalendarSchedule;
use Illuminate\Support\Facades\Cache;
use App\Services\DDoSProtectionService;

class Checkout extends Component
{
    public Guiding $guiding;
    public int $persons;
    public $initialSelectedDate;

    public int $page = 1;

    public $waters;
    public $targets;
    public $methods;

    public $extras;

    public $selectedExtras = [];

    public $selectedDate;
    public $selectedTime;
    public $availableEvents = [];

    public $payment_method;

    public $totalPrice;
    public $totalExtraPrice;

    public $guidingprice;

    public $loading = false;
    public $showInput = false;
    public $extraQuantities = [];

    public $extraData;

    public $userData = [
        'salutation' => '',
        'title' => '',
        'firstname' => '',
        'lastname' => '',
        'address' => '',
        'postal' => '',
        'city' => '',
        'country' => '',
        'countryCode' => '+49',
        'phone' => '',
        'email' => '',
        'createAccount' => false,
        'guestCheckTerms' => false,
    ];

    public $checkoutType = 'guest';

    public function getIsFormValidProperty()
    {
        return $this->selectedDate && 
               !empty(trim($this->userData['firstname'] ?? '')) && 
               !empty(trim($this->userData['lastname'] ?? '')) && 
               !empty(trim($this->userData['email'] ?? '')) && 
               !empty(trim($this->userData['address'] ?? '')) && 
               !empty(trim($this->userData['city'] ?? '')) && 
               !empty(trim($this->userData['country'] ?? '')) && 
               !empty(trim($this->userData['phone'] ?? '')) &&
               !empty(trim($this->userData['countryCode'] ?? ''));
    }

    protected $rules = [
        // 'userData.firstname' => 'required|string',
        // 'userData.lastname' => 'required|string',
        // 'userData.address' => 'required|string',
        // 'userData.postal' => 'required|string',
        // 'userData.city' => 'required|string',
        // 'userData.phone' => 'required|string',
        // 'userData.email' => 'required|email',
        // 'extras' => 'nullable|array',
    ];

    protected function getMessages()
    {
        return [
            'selectedDate.required' => __('validation.custom.selectedDate.required'),
            'userData.firstname.required' => __('validation.custom.userData.firstname.required'),
            'userData.lastname.required' => __('validation.custom.userData.lastname.required'),
            'userData.address.required' => __('validation.custom.userData.address.required'),
            'userData.postal.required' => __('validation.custom.userData.postal.required'),
            'userData.city.required' => __('validation.custom.userData.city.required'),
            'userData.country.required' => __('validation.custom.userData.country.required'),
            'userData.phone.required' => __('validation.custom.userData.phone.required'),
            'userData.email.required' => __('validation.custom.userData.email.required'),
            'userData.email.email' => __('validation.custom.userData.email.email'),
            'userData.email.unique' => __('validation.custom.userData.email.unique'),
            'extraQuantities.*.max' => __('validation.custom.extraQuantities.*.max'),
        ];
    }

    public function render()
    {
        $formattedDate = $this->selectedDate ? Carbon::parse($this->selectedDate)->format('F d, Y') : null;

        return view('livewire.checkout', [
            'guiding' => $this->guiding,
            'persons' => $this->persons,
            'waters' => $this->waters,
            'formattedDate' => $formattedDate
        ]);
    }

    public function mount()
    {
        $this->extras = json_decode($this->guiding->pricing_extra, true) ?? [];
        $this->targets = $this->guiding->getTargetFishNames();

        // Initialize arrays with default values
        $this->selectedExtras = array_fill(0, count($this->extras), false);
        $this->extraQuantities = array_fill(0, count($this->extras), 1);
        
        $this->waters = $this->guiding->getWaterNames();
        $this->methods = $this->guiding->getFishingMethodNames();
        
        $this->guidingprice = 0;
        if($this->guiding->price_type == 'per_person'){
            $prices = json_decode($this->guiding->prices, true);
            foreach ($prices as $price) {
                if ($price['person'] == $this->persons) {
                    $this->guidingprice = $price['amount'];
                    break;
                }
            }
        } else {
            $this->guidingprice = $this->guiding->price;
        }

        if ($this->guidingprice == 0 && !empty($prices)) {
            $lastPrice = end($prices);
            $this->guidingprice = $lastPrice['amount'] * $this->persons;
        }
        $user = auth()->user();

        $guidingExtras = collect(json_decode($this->guiding->pricing_extra, true) ?? [])->filter(function($extra, $index) {
            return isset($this->selectedExtras[$index]) && $this->selectedExtras[$index];
        })->values();

        $totalExtraPrice = 0;
        
        foreach ($guidingExtras as $extra) {
            $quantity = $this->extraQuantities[$extra->id] ?? 0; // Get the quantity for the current extra
            $totalExtraPrice += $extra->price * $quantity; // Calculate the total price for this extra
        }
        
        $this->totalExtraPrice = $totalExtraPrice;
        $this->totalPrice =  $this->totalExtraPrice + $this->guidingprice;

        // Set checkoutType based on authentication status
        $this->checkoutType = $user ? 'login' : 'guest';
        
        $address = trim(($user->information->address ?? '') . ' ' . ($user->information->address_number ?? ''));
        
        $this->userData = [
            'salutation' => 'male',
            'title' => '',
            'firstname' => ($user->firstname ?? ''),
            'lastname' => ($user->lastname ?? ''),
            'address' => $address ?: '',
            'postal' => ($user->information->postal ?? ''),
            'city' => ($user->information->city ?? ''),
            'country' => ($user->information->country ?? 'Deutschland'),
            'countryCode' => ($user->phone_country_code ?? '+49'),
            'phone' => ($user->phone ?? ''),
            'email' => ($user->email ?? ''),
            'createAccount' => false,
            'guestCheckTerms' => false,
        ];

        // Set the initial selected date if provided
        $this->selectedDate = $this->initialSelectedDate ?? null;
    }

    public function updatedSelectedExtras($value, $index)
    {
        // Convert string 'true'/'false' to boolean if needed
        if (is_string($value)) {
            $this->selectedExtras[$index] = $value === 'true';
        } else {
            $this->selectedExtras[$index] = (bool)$value;
        }
        
        $this->calculateTotalPrice();
    }

    public function removeExtras()
    {
        // Reset the selected extras
        $this->selectedExtras = [];
        $this->calculateTotalPrice();
    }

    public function calculateTotalPrice()
    {
        $totalExtraPrice = 0;
        $extraData = [];

        foreach ($this->extras as $index => $extra) {            
            if (!empty($this->selectedExtras[$index])) {
                $quantity = $this->extraQuantities[$index] ?? 1;
                $price = floatval($extra['price']);
                $subtotal = $price * intval($quantity);
                
                $totalExtraPrice += $subtotal;
                
                $extraData[] = [
                    'extra_id' => $index,
                    'extra_name' => $extra['name'],
                    'extra_price' => $price,
                    'extra_quantity' => $quantity,
                    'extra_total_price' => $subtotal,
                ];
            }
        }

        $this->extraData = !empty($extraData) ? serialize($extraData) : null;
        $this->totalExtraPrice = $totalExtraPrice;
        $this->totalPrice = $this->totalExtraPrice + $this->guidingprice;
    }

    public function next()
    {   
        // Validate data
        $this->validateData();

        // Check if the email already exists only if creating account
        if ($this->checkoutType === 'guest' && $this->userData['createAccount']) {
            $existingUser = User::where('email', $this->userData['email'])->first();
            if ($existingUser) {
                $this->addError('userData.email', __('validation.custom.userData.email.unique'));
                return;
            }
        }

        $this->page++;
    }

    public function prev()
    {
        $this->page--;
    }

    /**
     * Handle property updates with basic validation
     */
    public function updated($propertyName)
    {
        // DDoS Protection: Rate limit validation requests
        if (!$this->checkValidationRateLimit()) {
            return;
        }

        // Input validation for security
        if (!$this->validateInputSecurity($propertyName)) {
            Log::channel('ddos_attacks')->warning('Suspicious input detected in checkout', [
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'property' => $propertyName,
                'value' => $this->getPropertyValue($propertyName),
                'user_id' => auth()->id()
            ]);
            $this->addError($propertyName, 'Invalid input detected.');
            return;
        }

        // Only validate specific fields that need real-time validation
        if (str_starts_with($propertyName, 'userData.')) {
            $fieldName = str_replace('userData.', '', $propertyName);
            
            // Only validate critical fields in real-time (email with longer debounce to check existing users)
            if (in_array($fieldName, ['email', 'firstname', 'lastname', 'phone', 'countryCode'])) {
                $rules = [
                    "userData.{$fieldName}" => $fieldName === 'email' ? 'required|email|max:255' : 
                                             ($fieldName === 'phone' ? 'required|string|min:3|max:20' : 'required|string|max:255'),
                ];

                // Only validate email uniqueness if creating account (with rate limiting)
                if ($fieldName === 'email' && $this->checkoutType === 'guest' && $this->userData['createAccount']) {
                    if ($this->canCheckEmailUniqueness()) {
                        $rules["userData.{$fieldName}"] = 'required|email|max:255|unique:users,email';
                    }
                }

                $customMessages = [];
                if ($fieldName === 'email') {
                    $customMessages["userData.{$fieldName}.required"] = __('validation.custom.userData.email.required');
                    $customMessages["userData.{$fieldName}.email"] = __('validation.custom.userData.email.email');
                    $customMessages["userData.{$fieldName}.unique"] = __('validation.custom.userData.email.unique');
                } elseif ($fieldName === 'firstname') {
                    $customMessages["userData.{$fieldName}.required"] = __('validation.custom.userData.firstname.required');
                } elseif ($fieldName === 'lastname') {
                    $customMessages["userData.{$fieldName}.required"] = __('validation.custom.userData.lastname.required');
                } elseif ($fieldName === 'phone') {
                    $customMessages["userData.{$fieldName}.required"] = __('validation.custom.userData.phone.required');
                }
                
                $this->validateOnly($propertyName, $rules, $customMessages);
            }
        }
    }

    public function validateData()
    {
        if ($this->page == 1) {
            if (!$this->selectedDate) {
                $this->addError('selectedDate', __('validation.custom.selectedDate.required'));
                return false;
            }
            $rules = [
                'selectedDate' => ['required', 'string'],
                'userData.firstname' => 'required|string',
                'userData.lastname' => 'required|string',
                'userData.address' => 'required|string',
                'userData.postal' => 'required|string',
                'userData.city' => 'required|string',
                'userData.country' => 'required|string',
                'userData.phone' => 'required|string|min:3',
                'userData.countryCode' => 'required|string',
                'userData.email' => 'required|email',
            ];

            // Only validate email uniqueness if creating account
            if ($this->checkoutType === 'guest' && $this->userData['createAccount']) {
                $rules['userData.email'] = 'required|email|unique:users,email';
            }

            $this->validate($rules, [
                'userData.firstname.required' => __('validation.custom.userData.firstname.required'),
                'userData.lastname.required' => __('validation.custom.userData.lastname.required'),
                'userData.address.required' => __('validation.custom.userData.address.required'),
                'userData.postal.required' => __('validation.custom.userData.postal.required'),
                'userData.city.required' => __('validation.custom.userData.city.required'),
                'userData.country.required' => __('validation.custom.userData.country.required'),
                'userData.phone.required' => __('validation.custom.userData.phone.required'),
                'userData.email.required' => __('validation.custom.userData.email.required'),
                'userData.email.email' => __('validation.custom.userData.email.email'),
                'userData.email.unique' => __('validation.custom.userData.email.unique'),
            ]);
        }
        
        if ($this->page === 2) {
            if ($this->checkoutType === 'guest' && !$this->userData['createAccount']) {
                $this->validate([
                    'userData.guestCheckTerms' => 'required|accepted',
                ], [
                    'userData.guestCheckTerms.required' => __('validation.custom.userData.guestCheckTerms.required'),
                    'userData.guestCheckTerms.accepted' => __('validation.custom.userData.guestCheckTerms.accepted'),
                ]);
            }
        }
    }

    public function checkout()
    {
        $this->loading = true;

        // DDoS protection is handled by middleware and component-level validation

        $this->validateData();
        $this->validate([
            'extraQuantities.*' => ['required', 'numeric', 'max:' . $this->persons],
        ]);

        $currentUser = auth()->user();
        $locale = app()->getLocale();

        if ($currentUser) {
            $user = $currentUser;
            $isGuest = false;
            $userId = $user->id;
            
            // Update phone for registered user
            if (!$user->is_guide) {
                $user->phone = $this->userData['phone'];
                $user->phone_country_code = $this->userData['countryCode'];
                $user->save();
            }

            if ($user->information) {
                $user->information->phone = $this->userData['phone'];
                $user->information->phone_country_code = $this->userData['countryCode'];
                $user->information->save();
            }
        } else {
            if ($this->userData['createAccount']) {
                // Create regular user if checkbox is checked
                $randomPassword = Str::random(10);
                $user = User::create([
                    'firstname' => $this->userData['firstname'],
                    'lastname' => $this->userData['lastname'],
                    'email' => $this->userData['email'],
                    'is_temp_password' => 1,
                    'password' => \Hash::make($randomPassword),
                ]);
                
                // Create UserInformation record
                $userInformation = UserInformation::create([
                    'phone' => $this->userData['phone'],
                    'phone_country_code' => $this->userData['countryCode'],
                    'address' => $this->userData['address'],
                    'postal' => $this->userData['postal'],
                    'city' => $this->userData['city'],
                    'country' => $this->userData['country'],
                ]);

                $user->user_information_id = $userInformation->id;
                $user->save();

                if (!app()->environment('local')) {
                    // Mail::to($user->email)->queue(new AutomaticRegistrationMail($user, $randomPassword));
                }
                 
                $isGuest = false;
                $userId = $user->id;
            } else {
                // Check if guest user already exists with this email
                $user = UserGuest::where('email', $this->userData['email'])->first();

                if (!$user) {                    
                    // Create new guest user if not found
                    $user = UserGuest::create([
                        'salutation' => $this->userData['salutation'],
                        'title' => $this->userData['title'],
                        'firstname' => $this->userData['firstname'],
                        'lastname' => $this->userData['lastname'],
                        'address' => $this->userData['address'],
                        'postal' => $this->userData['postal'],
                        'city' => $this->userData['city'],
                        'country' => $this->userData['country'],
                        'phone' => $this->userData['phone'],
                        'phone_country_code' => $this->userData['countryCode'],
                        'email' => $this->userData['email'],
                        'language' => $locale,
                    ]);
                } else {                    
                    // Update existing guest user information
                    $user->salutation = $this->userData['salutation'];
                    $user->title = $this->userData['title'];
                    $user->firstname = $this->userData['firstname'];
                    $user->lastname = $this->userData['lastname'];
                    $user->address = $this->userData['address'];
                    $user->postal = $this->userData['postal'];
                    $user->city = $this->userData['city'];
                    $user->country = $this->userData['country'];
                    $user->phone = $this->userData['phone'];
                    $user->phone_country_code = $this->userData['countryCode'];
                    $user->language = $locale;
                    $user->save();
                }
                
                $isGuest = true;
                $userId = $user->id; // Set user_id to null for guest bookings
            }
        } 
 
        $blockedEvent = (new EventService())->createBlockedEvent($this->selectedTime, $this->selectedDate, $this->guiding, 'tour_request'); 
 
        $fee = (new HelperService())->calculateRates($this->guidingprice);
        $partnerFee = (new HelperService())->convertAmountToString($fee);

        $expiresAt = Carbon::now()->addHours(24); // Default expiration time (24 hours)

        // Calculate the difference between the selected date and the current date
        $dateDifference = Carbon::parse($this->selectedDate)->diffInDays(Carbon::now());

        if ($dateDifference > 3) {
            // If the selected date is more than 3 days from now, extend to 48 hours total response time
            $expiresAt = Carbon::now()->addHours(48);
        }

        $booking = Booking::create([
            'user_id' => $userId, // Use null for guests, actual user ID for registered users
            'is_guest' => $isGuest,
            'guiding_id' => $this->guiding->id,
            'blocked_event_id' => $blockedEvent->id,
            'is_paid' => false,
            'extras' => $this->extraData,
            'total_extra_price' => $this->totalExtraPrice,
            'count_of_users' => $this->persons,
            'price' => $this->totalPrice,
            'cag_percent' => $fee,
            'status' => 'pending',
            'book_date' => $this->selectedDate,
            'expires_at' => $expiresAt,
            'phone' => $this->userData['countryCode'] . ' ' . $this->userData['phone'],
            'phone_country_code' => $this->userData['countryCode'],
            'language' => $locale,
            'email' => $this->userData['email'],
            'token' => $this->generateBookingToken($blockedEvent->id),
        ]);

        $updateSchedule = CalendarSchedule::find($blockedEvent->id);
        $updateSchedule->booking_id = $booking->id;
        $updateSchedule->save();

        if (!app()->environment('local')) {
            SendCheckoutEmail::dispatch($booking, $user, $this->guiding, $this->guiding->user);
        }
        
        // Checkout completed successfully
        
        sleep(5);

        $this->loading = false;
        return redirect(route('thank-you', [$booking]));
    }
    
    public function generateBookingToken($eventID) {
        $timestamp = time();
        $combinedString = $eventID . '-' . $timestamp;
    
        // Generate a hash of the combined string
        return hash('sha256', $combinedString);
    }

    public function updatedSelectedDate()
    {
        if ($this->selectedDate) {
            // Automatically set a default time when date is selected
            $this->selectedTime = '00:00';
            // Temporarily comment out to avoid potential errors
            // $this->availableEvents = (new EventService())->getAvailableEvents($this->guiding->duration, $this->selectedDate, $this->guiding->user);
        }
    }

    public function setSelectedTime($selectedTime)
    {
        $this->selectedTime = $selectedTime;
    }
    
    public function refresh()
    {
        // This method will be called after successful registration
        // It will refresh the component to reflect the new authenticated state
        $this->mount();
    }

    /**
     * DDoS Protection: Check validation rate limit using the service
     */
    private function checkValidationRateLimit(): bool
    {
        $protectionService = app(DDoSProtectionService::class);
        $identifier = $this->getRateLimitIdentifier();
        
        $config = [
            'context' => 'checkout_validation',
            'limits' => ['minute' => 20],
            'validate_input' => false
        ];
        
        $result = $protectionService->shouldBlockRequest(request(), $config);
        return !$result['blocked'];
    }

    /**
     * DDoS Protection: Validate input security using the service
     */
    private function validateInputSecurity(string $propertyName): bool
    {
        $value = $this->getPropertyValueForValidation($propertyName);
        
        if (!is_string($value)) {
            return true; // Skip non-string values
        }

        // Create a mock request with the value to validate
        $mockRequest = request()->duplicate([], [$propertyName => $value]);
        
        $protectionService = app(DDoSProtectionService::class);
        $config = [
            'context' => 'checkout_input',
            'limits' => ['minute' => 1000], // High limit for validation
            'validate_input' => true
        ];
        
        $result = $protectionService->shouldBlockRequest($mockRequest, $config);
        return !$result['blocked'];
    }

    /**
     * Get property value safely for DDoS validation
     */
    private function getPropertyValueForValidation(string $propertyName)
    {
        $keys = explode('.', $propertyName);
        $value = $this;
        
        foreach ($keys as $key) {
            if (is_array($value) && isset($value[$key])) {
                $value = $value[$key];
            } elseif (is_object($value) && isset($value->$key)) {
                $value = $value->$key;
            } else {
                return null;
            }
        }
        
        return $value;
    }

    /**
     * DDoS Protection: Rate limit email uniqueness checks using the service
     */
    private function canCheckEmailUniqueness(): bool
    {
        $protectionService = app(DDoSProtectionService::class);
        $identifier = $this->getRateLimitIdentifier();
        
        $config = [
            'context' => 'checkout_email_check',
            'limits' => ['minute' => 5],
            'validate_input' => false
        ];
        
        $result = $protectionService->shouldBlockRequest(request(), $config);
        return !$result['blocked'];
    }

    /**
     * Get rate limit identifier
     */
    private function getRateLimitIdentifier(): string
    {
        if (auth()->check()) {
            return 'user_' . auth()->id();
        }
        
        return 'ip_' . request()->ip();
    }    
}
