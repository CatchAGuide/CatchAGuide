<?php

namespace App\Http\Livewire;


use App\Mail\Guest\GuestBookingRequestMail;
use App\Mail\Guide\GuideBookingRequestMail;
use App\Mail\Guest\AutomaticRegistrationMail;
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
               !empty(trim($this->userData['phone'] ?? ''));
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

    protected $messages = [
        'selectedDate.required' => 'Bitte wählen Sie ein Datum aus dem Kalender.',
        'userData.firstname.required' => 'Bitte geben Sie Ihren Vornamen ein.',
        'userData.lastname.required' => 'Bitte geben Sie Ihren Nachnamen ein.',
        'userData.address.required' => 'Bitte geben Sie Ihre Straße und Hausnummer ein.',
        'userData.postal.required' => 'Bitte geben Sie Ihre Postleitzahl ein.',
        'userData.city.required' => 'Bitte geben Sie Ihre Stadt ein.',
        'userData.phone.required' => 'Bitte geben Sie Ihre Telefonnummer ein.',
        'userData.email.required' => 'Bitte geben Sie Ihre E-Mail-Adresse ein.',
        'userData.email.email' => 'Bitte geben Sie eine gültige E-Mail-Adresse ein.',
        'userData.email.unique' => 'Diese E-Mail-Adresse ist bereits vergeben.',
        'extraQuantities.*.max' => 'Die Anzahl der Extras darf nicht größer als die Anzahl der Personen sein.',
    ];

    public function render()
    {
        $formattedDate = Carbon::parse($this->selectedDate)->format('F d, Y');

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
            'countryCode' => '+49',
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
                $this->addError('userData.email', 'Diese E-Mail-Adresse ist bereits vergeben.');
                return;
            }
        }

        $this->page++;
    }

    public function prev()
    {
        $this->page--;
    }

    public function updated($propertyName)
    {
        $rules = [
            'selectedDate' => ['required', 'string'],
            'userData.firstname' => 'required|string',
            'userData.lastname' => 'required|string',
            'userData.address' => 'required|string',
            'userData.postal' => 'required|string',
            'userData.city' => 'required|string',
            'userData.phone' => 'required|string',
            'userData.email' => 'required|email',
        ];

        // Only validate email uniqueness if creating account
        if ($propertyName === 'userData.email' && $this->checkoutType === 'guest' && $this->userData['createAccount']) {
            $rules['userData.email'] = 'required|email|unique:users,email';
        }

        $this->validateOnly($propertyName, $rules);
    }

    public function validateData()
    {
        if ($this->page == 1) {
            if (!$this->selectedDate) {
                $this->addError('selectedDate', 'Bitte wählen Sie ein Datum aus dem Kalender.');
                return false;
            }
            $rules = [
                'selectedDate' => ['required', 'string'],
                'userData.firstname' => 'required|string',
                'userData.lastname' => 'required|string',
                'userData.address' => 'required|string',
                'userData.postal' => 'required|string',
                'userData.city' => 'required|string',
                'userData.phone' => 'required|string',
                'userData.email' => 'required|email',
            ];

            // Only validate email uniqueness if creating account
            if ($this->checkoutType === 'guest' && $this->userData['createAccount']) {
                $rules['userData.email'] = 'required|email|unique:users,email';
            }

            $this->validate($rules);
        }
        
        if ($this->page === 2) {
            if ($this->checkoutType === 'guest' && !$this->userData['createAccount']) {
                $this->validate([
                    'userData.guestCheckTerms' => 'required|accepted',
                ], [
                    'userData.guestCheckTerms.required' => 'You must accept the Terms and Conditions to proceed.',
                    'userData.guestCheckTerms.accepted' => 'You must accept the Terms and Conditions to proceed.',
                ]);
            }
        }
    }

    public function checkout()
    {
        $this->loading = true;

        $this->validateData();
        $this->validate([
            'extraQuantities.*' => ['required', 'numeric', 'max:' . $this->persons],
        ]);

        $currentUser = auth()->user();

        if ($currentUser) {
            $user = $currentUser;
            $isGuest = false;
            $userId = $user->id;
            
            // Update phone for registered user
            if (!$user->is_guide) {
                $user->phone = $this->userData['phone'];
                $user->save();
            }

            if ($user->information) {
                $user->information->phone = $this->userData['phone'];
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
                $locale = app()->getLocale();

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
                        'email' => $this->userData['email'],
                        'language' => $locale,
                    ]);
                } else {
                    // Update existing guest user information
                    $user->update([
                        'salutation' => $this->userData['salutation'],
                        'title' => $this->userData['title'],
                        'firstname' => $this->userData['firstname'],
                        'lastname' => $this->userData['lastname'],
                        'address' => $this->userData['address'],
                        'postal' => $this->userData['postal'],
                        'city' => $this->userData['city'],
                        'country' => $this->userData['country'],
                        'phone' => $this->userData['phone'],
                        'language' => $locale,
                    ]);
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
            // If the selected date is more than 3 days from now, add 72 hours to the expiration time
            $expiresAt = Carbon::now()->addHours(72);
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
            'phone' => $this->userData['phone'],
            'email' => $this->userData['email'],
            'token' => $this->generateBookingToken($blockedEvent->id),
        ]);

        Log::info('Booking: ' . $booking->id);
        Log::info('Blocked event: ' . $blockedEvent->id);
        $updateSchedule = CalendarSchedule::find($blockedEvent->id);
        Log::info('Update schedule: ' . $updateSchedule);
        $updateSchedule->booking_id = $booking->id;
        $updateSchedule->save();

        if (!app()->environment('local')) {
            SendCheckoutEmail::dispatch($booking, $user, $this->guiding, $this->guiding->user);
        }
        
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
            $this->availableEvents = (new EventService())->getAvailableEvents($this->guiding->duration, $this->selectedDate, $this->guiding->user);
        }
    }

    public function setSelectedTime($selectedTime)
    {
        $this->selectedTime = $selectedTime;
    }
    
}
