<?php

namespace App\Http\Livewire;


use App\Mail\Guest\GuestBookingRequestMail;
use App\Mail\Guide\GuideBookingRequestMail;
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

class Checkout extends Component
{
    public Guiding $guiding;
    public int $persons;

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
        'phone' => '',
        'email' => ''
    ];

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
        'userData.firstname.required' => 'Bitte gebe Deinen Vornamen an.',
        'userData.email.required' => 'Bitte gebe eine gültige Emailadresse an',
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


        foreach ($this->extras as $index => $extra) {
            $extraId = $index;
            $this->selectedExtras[$extraId] = false;
            $this->extraQuantities[$extraId] = $this->persons;
        }

        $this->waters = $this->guiding->getWaterNames();
        $this->methods = $this->guiding->getFishingMethodNames();
        
        $prices = json_decode($this->guiding->prices, true);
        $this->guidingprice = 0;
        foreach ($prices as $price) {
            if ($price['person'] == $this->persons) {
                $this->guidingprice = $price['amount'];
                break;
            }
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

        $this->userData = [
            'salutation' => 'male',
            'title' => '',
            'firstname' => ($user->firstname ?? ''),
            'lastname' => ($user->lastname ?? ''),
            'address' => ($user->information->address ?? '') . ' ' . ($user->information->address_number ?? ''),
            'postal' => ($user->information->postal ?? ''),
            'city' => ($user->information->city ?? ''),
            'country' => ($user->information->country ?? 'Deutschland'),
            'phone' => ($user->phone ?? ''),
            'email' => ($user->email ?? '')
        ];
    }

    public function updatedSelectedExtras()
    {
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
            if (isset($this->selectedExtras[$index]) && ($index == 0 || $this->selectedExtras[$index] == true)) {
                $quantity = $this->extraQuantities[$index] ?? 0;
                $totalExtraPrice += $extra['price'] * $quantity;
                $extraData[] = [
                    'extra_id' => $index,
                    'extra_name' => $extra['name'],
                    'extra_price' => $extra['price'],
                    'extra_quantity' => $quantity,
                    'extra_total_price' => $extra['price'] * $quantity,
                ];
            }
        }
    
        $this->extraData = !empty($extraData) ? serialize($extraData) : null;
        $this->totalExtraPrice = $totalExtraPrice;
        $this->totalPrice = $this->totalExtraPrice + $this->guidingprice;
    }

    public function next()
    {   

        $this->validateData();

        $this->page++;
    }

    public function prev()
    {
        $this->page--;
    }

    public function updated($propertyName){
        
        $this->validateOnly($propertyName,[
            'selectedDate' => ['required', 'string'],
            'userData.firstname' => 'required|string',
            'userData.lastname' => 'required|string',
            'userData.address' => 'required|string',
            'userData.postal' => 'required|string',
            'userData.city' => 'required|string',
            'userData.phone' => 'required|string',
            'userData.email' => 'required|email',
        ]);

    }

    public function validateData(){
        if ($this->page == 1) {

            $this->validate([
                'selectedDate' => ['required', 'string'],
                'userData.firstname' => 'required|string',
                'userData.lastname' => 'required|string',
                'userData.address' => 'required|string',
                'userData.postal' => 'required|string',
                'userData.city' => 'required|string',
                'userData.phone' => 'required|string',
                'userData.email' => 'required|email',
            ]);

        }
    }

    public function checkout()
    {

        $this->loading = true;

        $this->validateData();
        $this->validate([
            'extraQuantities.*' => ['required', 'numeric', 'max:' . $this->persons],
        ]);

        $user = auth()->user(); 

      //  $guide = User::find($this->guiding->user_id);

        $blockedEvent = (new EventService())->createBlockedEvent($this->selectedTime, $this->selectedDate, $this->guiding);

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
            'user_id' => $user->id,
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
            'expires_at' => Carbon::now()->addHours(72),
            'phone' => $this->userData['phone'],
            'token' => $this->generateBookingToken($blockedEvent->id),
        ]);

        if (!$user->is_guide) {
            $user->phone = $booking->phone;
            $user->save();
        }

        $user->information->phone = $booking->phone;
        $user->information->save();

        if (!app()->environment('local')) {
            SendCheckoutEmail::dispatch($booking,$user,$this->guiding,$this->guiding->user);
        }
        
        sleep(5);

        $this->loading = false;
        return redirect(route('thank-you',[$booking]));

    }
    
    public function generateBookingToken($eventID) {
        $timestamp = time();
        $combinedString = $eventID . '-' . $timestamp;
    
        // Generate a hash of the combined string
        return hash('sha256', $combinedString);
    }

    public function updatedSelectedDate()
    {
        $this->availableEvents = (new EventService())->getAvailableEvents($this->guiding->duration, $this->selectedDate, $this->guiding->user);
    }

    public function setSelectedTime($selectedTime)
    {
        $this->selectedTime = $selectedTime;
    }

}
