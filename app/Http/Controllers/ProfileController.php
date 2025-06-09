<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;

use App\Mail\Guide\StorniGuidingMail;
use App\Mail\Ceo\BookingCancelMailToCEO;
use App\Models\BlockedEvent;
use App\Models\Booking;
use App\Models\Guiding;
use App\Models\Method;
use App\Models\Payment;
use App\Models\Target;
use App\Models\User;
use App\Models\Water;
use App\Models\GuidingBoatType;
use App\Models\GuidingBoatDescription;
use App\Models\GuidingAdditionalInformation;
use App\Models\GuidingRequirements;
use App\Models\GuidingRecommendations;
use Auth;
use Hash;
use Config;
use Illuminate\Http\Request;
use Mail;

use App\Events\BookingStatusChanged;
use App\Models\Inclussion;
use App\Models\ExtrasPrice;
use App\Models\BoatExtras;

class ProfileController extends Controller
{
    public function index()
    {
        return view('pages.profile.index');
    }

    public function settings()
    {
        return view('pages.profile.account',[
            'user'=> auth()->user()
        ]);
    }

    public function getbalance(Request $request)
    {
        // TODO rausnehmen
        $payment = new Payment();
        $payment->amount = $request->amount;
        $payment->is_completed = 0;
        $payment->type = 'withdraw';
        $payment->user_id = Auth::user()->id;
        $payment->save();


        $user = User::find(Auth::user()->id);
        $user->pending_balance += $request->amount;
        $user->save();

        return redirect()->back()->with('message', 'Wir haben Deine Anfrage erhalten und kümmern uns um eine schnellstmögliche Abwicklung');

    }

    public function accountUpdate(UpdateUserRequest $request)
    {   

        $user = auth()->user();

        if($request->hasFile('image')){
            $imageName = time().'.'.$request->image->extension();
            $request->image->move(public_path('images'), $imageName);
            // public/images/file.png
            $user->profil_image = $imageName;
        }

        $user->bar_allowed = $request->bar_allowed;
        $user->banktransfer_allowed = $request->banktransfer_allowed;
        $user->paypal_allowed = $request->paypal_allowed;
        $user->banktransferdetails = $request->banktransferdetails;
        $user->paypaldetails = $request->paypaldetails;
        $user->number_of_guides = $request->numguides;


        if($user->information()) {
            $user->information->update($request->information);
        } else {
            $user->information->create($request->information);
        }
        $user->language = json_encode($request->get('language_speak'));
        $user->tax_id = $request->get('information')['tax_id'];
        $user->phone = $request->phone;

        if($user->is_company){
            $user->company_name = $request->company_name;
        }else{
            $user->firstname = $request->get('firstname');
            $user->lastname = $request->get('lastname');
        }

        $user->update();


        if($request->new_password){
            if (!(Hash::check($request->get('current_password'), Auth::user()->password))) {
                // The passwords matches
                return redirect()->back()->with("error","Dein jetztiges Passwort stimmt nicht überein..");
            }

            if(strcmp($request->get('current_password'), $request->get('new_password')) == 0){
                // Current password and new password same
                return redirect()->back()->with("error","Dein neues Passwort darf nicht das selbe sein wie Dein aktuelles.");
            }

            $validatedData = $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|string|min:8|confirmed',
            ]);

            //Change Password
            $user = Auth::user();
            $user->password = bcrypt($request->get('new_password'));
            $user->save();
        }

        if ($request->get('update_merchant')) {
            $compliance = $this->getMerchantStatus(auth()->user());
            return redirect($compliance->overview_url, 303);
        }




        return redirect()->route('profile.settings')->with(['message' => 'Erfolgreich gespeichert!']);
    }

    public function becomeguide()
    {
        return view('pages.profile.becomeguide');
    }

    public function myguidings()
    {
        // Get all guidings for the authenticated user and check for duplicates
        $allGuidings = Guiding::where('user_id', auth()->user()->id)->get();
        $duplicatesRemoved = false;
        
        // Group guidings by their attributes to find duplicates
        $groupedGuidings = $allGuidings->groupBy(function($guiding) {
            return implode('|', [
                $guiding->title,
                $guiding->city,
                $guiding->lat,
                $guiding->lng,
                $guiding->country,
                $guiding->location,
                $guiding->type_of_fishing,
                $guiding->type_of_boat,
                $guiding->target_fish,
                $guiding->methods,
                $guiding->water_types,
                $guiding->style_of_fishing,
                $guiding->tour_type,
                $guiding->duration,
                $guiding->no_guest,
                $guiding->price_type
            ]);
        });

        // For each group of duplicates, keep only the newest one
        foreach($groupedGuidings as $group) {
            if($group->count() > 1) {
                $duplicatesRemoved = true;
                // Sort by created_at in descending order and keep the first one
                $sortedGroup = $group->sortByDesc('created_at');
                $keepGuiding = $sortedGroup->first();
                
                // Delete all other duplicates
                foreach($sortedGroup->slice(1) as $duplicate) {
                    $duplicate->delete();
                }
            }
        }

        // Get fresh data after removing duplicates
        $guidings = Guiding::where('user_id', auth()->user()->id)->paginate(20);

        // Add a flash message if duplicates were removed
        if($duplicatesRemoved) {
            session()->flash('message', 'Duplicate guidings have been automatically removed.');
        }

        return view('pages.profile.myguidings',[
            'guidings' => $guidings
        ]);
    }

    //step 1
    public function newguiding()
    {
        $pageTitle = __('profile.creategiud');
        $locale = Config::get('app.locale');
        $nameField = $locale == 'en' ? 'name_en' : 'name';

        $modelClasses = [
            'targets' => Target::class,
            'methods' => Method::class,
            'waters' => Water::class, 
            'inclusions' => Inclussion::class,
            'boat_extras' => BoatExtras::class,
            'extras_prices' => ExtrasPrice::class,
            'guiding_boat_types' => GuidingBoatType::class,
            'guiding_boat_descriptions' => GuidingBoatDescription::class,
            'guiding_additional_infos' => GuidingAdditionalInformation::class,
            'guiding_requirements' => GuidingRequirements::class,
            'guiding_recommendations' => GuidingRecommendations::class
        ];

        $collections = [];
        foreach ($modelClasses as $key => $modelClass) {
            $collections[$key] = $modelClass::all()
            ->map(function($item) use ($nameField) {
                return [
                    'value' => $item->$nameField,
                    'id' => $item->id
                ];
            });
        }

        return view('pages.profile.newguiding', array_merge($collections, ['pageTitle' => $pageTitle]));
    }

    public function bookings()
    {

     //   $this->getBookingStatus();
        $bookings = Booking::orderBy('created_at','desc')->where('user_id',auth()->user()->id)->get();


        return view('pages.profile.bookings', [
           'bookings' => $bookings
        ]);
    }

    public function review($guideid)
    {
        $guide = User::find($guideid);
        return view('pages.profile.rating', compact('guide'));
    }

    public function guidebookings()
    {

        return view('pages.profile.guidebookings', [
            'bookings' => auth()->user()->bookings
        ]);
    }

    public function accept(Booking $booking){

        if(!$booking || $booking->status != 'pending'){
            abort(404);
        }

        if(auth()->user()->id == $booking->guiding->user->id){    
            $booking->status = 'accepted';
            $booking->save();
    
            $blockedevent = BlockedEvent::find($booking->blocked_event_id);
            $blockedevent->type = 'booking';
            $blockedevent->save();

            event(new BookingStatusChanged($booking, 'accepted'));

            return redirect()->route('profile.guidebookings')->with(['message' => 'Booking Accepted Successfully']);
        }
    }
    public function reject(Booking $booking){

        if(!$booking || $booking->status != 'pending'){
            abort(404);
        }

        return view('pages.additional.rejected',[
            'booking' => $booking,
        ]);
        
    }

    public function favoriteguides()
    {
        return view('pages.profile.favoriteguides',[
            'wishlist_items' => auth()->user()->wishlist_items
        ]);
    }

    public function more()
    {
        return view('pages.profile.more');
    }

    public function payments()
    {
        return view('pages.profile.payments', [
            'intent' => auth()->user()->createSetupIntent()
        ]);
    }

    public function abbuchen()
    {
        return view('pages.profile.abbuchen');
    }

    public function calendar()
    {
        // Get user's guidings for the filter dropdown
        $userGuidings = Guiding::where('user_id', auth()->id())
            ->select('id', 'title', 'location')
            ->orderBy('title')
            ->get();
            
        return view('pages.profile.calendar', [
            'userGuidings' => $userGuidings
        ]);
    }

    public function showBooking($bookingid)
    {
        $booking = Booking::find($bookingid);
        if(!$booking){
            abort(404);
        }
        if($booking->user_id == auth()->user()->id || $booking->guiding->user_id == auth()->user()->id ){
            $guiding = Guiding::where('id',$booking->guiding_id)->first();
            if(!$guiding){
                abort(404);
            }
    
            return view('pages.profile.showbooking', compact('booking', 'guiding'));
        }else{
            abort(404);
        }

    }

    public function stornobooking($bookingid)
    {
        $booking = Booking::find($bookingid);
        if($booking->user_id != auth()->user()->id) {
            return back()->with('error', 'Du hast nicht die Berechtigung die Buchung zu stornieren. Bitte wende Dich an Catchaguide für mehr Informationen.');
        }
        $guiding = Guiding::find($booking->guiding_id);

        Mail::queue(new StorniGuidingMail($booking, $guiding, $guiding->user, auth()->user()));
        Mail::queue(new BookingCancelMailToCEO($booking, $guiding, $guiding->user, auth()->user()));

        $booking->status = 'cancelled';
        $booking->save();

        return back()->with('success', 'Deine Buchung wurde erfolgreich storniert');
    }

    
    public function activate(Guiding $guiding){
        if($guiding->user->id != auth()->user()->id){
            abort(404);
        }
           
        $guiding->status = '1';
        $guiding->save();
        return redirect()->back()->with(['message' => 'Guiding Activated']);;
    }

    public function deactivate(Guiding $guiding){

        if($guiding->user->id != auth()->user()->id){
            abort(404);
        }
           
        $guiding->status = '0';
        $guiding->save();

        return redirect()->back()->with(['message' => 'Guiding Deactivated']);;
    }

    /*
    public function getMerchantStatus (User $guide)
    {
        $merchant = (new OppApiService())->retrieveMerchant($guide->merchant_id);
        $status = $merchant->compliance;
        return $status;
    }
    */

    /*
    private function getBookingStatus()
    {
        $allBookingsOfUSer = auth()->user()->bookings;
        foreach ($allBookingsOfUSer as $key => $booking) {
            $transactions[] = trim($booking->transaction_id);
        }
        $transactions = array_filter($transactions, 'strlen');
        $transactionList = implode(',', $transactions);

        $transactionStatuses = (new OppApiService())->retrieveFilteredTransactions($transactionList);
        $statusArray = array($transactionStatuses);
        foreach ($statusArray[0]->data as $key => $status) {
            $transactionUid = $statusArray[0]->data[$key]->uid;
            $lastStatus = end($statusArray[0]->data[$key]->statuses)->status;
            $result = Booking::where('transaction_id', $transactionUid)->update(['status'=> $lastStatus]);
        }
    }
*/
    /*
    public function processMerchantStatus()
    {
        $compliance = $this->getMerchantStatus(auth()->user());
        Log::channel('opp')->info('processMerchantStatus ->compliance: ' . json_encode(array($compliance)));

        return redirect($compliance->overview_url, 303);


    }
    */


}
