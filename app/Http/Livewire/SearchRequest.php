<?php

namespace App\Http\Livewire;

use Illuminate\Http\Request;

use App\Models\SearchRequest as GuidingRequest;
use App\Mail\GuidingRequestMail;
use App\Mail\SearchRequestUserMail;

use App\Models\Target;
use App\Models\Method;

use Livewire\Component;
use Mail;

class SearchRequest extends Component
{
    public int $page = 1;
    public $fishingType;
    public $fishingPref;
    public $fishingFrom;

    public $daysOfFishing;


    public $country;
    public $region;
    public $city;
    public $number_of_guest;
    public $date_from;
    public $date_to;
    public $name;
    public $phone;
    public $email;
    public $locale;
    public $comments;

    public $target_fish;
    public $methods = [];

    public $submit = "";

    public $letmeknow = false;
    public $guided = false;
    public $calendar = true;
    public $rent_a_boat = false;
    public $boat_rental_days;
    public $daysofguiding;
    public $guidingdays;

    public $budgetToSpend;
    public int $counter = 1;

    protected $rules = [];

    public function render()
    {
        return view('livewire.search-request');
    }

    public function mount(){
        $this->page = 1;
    }

    public function save(){

        $this->rules = [
            'name' => ['required','string'],
            'email' => ['required','email'],
            'phone' => ['required', 'numeric'],
            'comments' => ['nullable','string'],
        ];

        $this->validate();


        $searchRequest = new GuidingRequest;

        $searchRequest->name = $this->name;
        $searchRequest->email = $this->email;
        $searchRequest->phone = $this->phone;
        $searchRequest->country = $this->country;
        $searchRequest->region = $this->region;
        $searchRequest->target_fish = $this->target_fish;
        $searchRequest->number_of_guest = $this->number_of_guest;
        $searchRequest->comments = $this->comments;
        if($this->letmeknow){

            $searchRequest->is_best_fishing_time_recommendation = 1;
        }else{
            $searchRequest->date_from = $this->date_from;
            $searchRequest->date_to = $this->date_to;
        }


        if($this->fishingType == 'holiday'){
            $searchRequest->fishing_type = "Fishing Holiday";
            if($this->guided){
                $searchRequest->is_guided = 1;
                if($this->daysofguiding == 'everyday'){
                    $searchRequest->days_of_guiding = 'Everyday';
                }else{
                    $searchRequest->days_of_guiding = $this->guidingdays;
                }
            }

            if($this->rent_a_boat){
                $searchRequest->is_boat_rental = 1;
                $searchRequest->days_of_boat_rental = $this->boat_rental_days;
            }
        }

        $searchRequest->total_budget_to_spend = $this->budgetToSpend;
        

        $searchRequest->save();

        if(app()->getLocale() == 'en'){
            \App::setLocale('en');
        }else{
            \App::setLocale('de');
        }
        $email = env('TO_CEO','info@catchaguide.com');
        if (!CheckEmailLog('guiding_request_mail', 'guiding_request_mail', $email)) {
            Mail::to($email)->send(new GuidingRequestMail($searchRequest));
        }
        if (!CheckEmailLog('search_request_user_mail', 'search_request_user_mail', $this->email)) {
            Mail::to($this->email)->send(new SearchRequestUserMail($searchRequest));
        }

        return redirect()->route('request.thank-you')->with(['message' => 'Das Guiding wurde erfolgreich erstellt!']);       

    }

    public function letMeKnow(){
        if($this->letmeknow){
          
            $this->letmeknow = false;
            $this->dispatchBrowserEvent('dispatchLetMeKnow');
            $this->calendar = true;
        
          
        }else{
            $this->dispatchBrowserEvent('dispatchLetMeKnow');
            $this->letmeknow = true;
            $this->calendar = false;
          
        }   

        if($this->calendar){
            $this->dispatchBrowserEvent('initDatePicker');
        }
    }

    public function next($type)
    {   
        if($this->page == 1){
            $this->fishingType = $type;
        }
        //holiday
        if($this->fishingType == 'holiday'){
            if($this->page == 2  ){
                $this->validate([
                    'country' => ['required', 'string'],
                    'region' => ['required', 'string'],
                ]);
                $this->counter++;
            }
    
            if($this->page == 3  ){
                $this->validate([
                    'target_fish' => ['required', 'string'],
                    'number_of_guest' => ['required', 'numeric','gt:0'],
                ]);
    
                if($this->calendar){
                    $this->dispatchBrowserEvent('initDatePicker');
                }

                $this->counter++;
            }
    
            if($this->page == 4  ){
                if(!$this->letmeknow){
                    $this->validate([
                        'date_from' => ['required', 'date_format:Y-m-d'],
                        'date_to' => ['required', 'date_format:Y-m-d'],
                    ]);
                }

                $this->counter++;
          
            }
    
            if($this->page == 5  ){
                if($type == 'yes'){
                    $this->guided = true;
                    $this->counter++;
                }else{
                    $this->guided = false;
                }
             
            }
    
            if($this->page == 6 ){
                    if($type == 'everyday'){
                        $this->daysofguiding = 'everyday';
                        $this->counter++;
                    }
    
                    if($type == 'customday'){
                        $this->daysofguiding = 'customday';
                    }
            }
    
            
            if($this->page == 6 ){
    
                if($type == 'yes'){
                    $this->rent_a_boat = true;
                }else{
                    $this->rent_a_boat = false;
                }
            }

            if($this->page == 7  && $this->guided == true && $this->daysofguiding == 'everyday'){
                $this->counter++;
            }

            if($this->page== 8  && $this->daysofguiding == 'customday'){
                $this->counter++;
            }
    
            if($this->page == 7  && $this->daysofguiding == 'customday'){
                $this->validate([
                    'guidingdays' => ['required', 'numeric','gt:0']
                ]); 
                $this->counter++;
                
            }

            if($this->page == 7  && $this->rent_a_boat == true){
                $this->validate([
                    'boat_rental_days' => ['required', 'numeric','gt:0']
                ]); 
                $this->counter++;
            }
          
        }
        //tour
        if($this->fishingType == 'tour'){
            if($this->page == 2  ){
                $this->validate([
                    'country' => ['required', 'string'],
                    'region' => ['required', 'string'],
                ]);
            }
    
            if($this->page == 3  ){
                $this->validate([
                    'target_fish' => ['required', 'string'],
                    'number_of_guest' => ['required', 'numeric','gt:0'],
                ]);
    
                if($this->calendar){
                    $this->dispatchBrowserEvent('initDatePicker');
                }
            }
    
            if($this->page == 4  ){
                if(!$this->letmeknow){
                    $this->validate([
                        'date_from' => ['required', 'date_format:Y-m-d'],
                        'date_to' => ['required', 'date_format:Y-m-d'],
                    ]);
                }
          
            }

            if($this->page == 5  ){
                if(!$this->letmeknow){
                    $this->validate([
                        'budgetToSpend' => ['required', 'numeric','gt:0'],
                    ]);
                }
          
            }

        }

        $this->page++;
    }

    public function prev()
    {
        if($this->calendar){
            $this->dispatchBrowserEvent('initDatePicker');
        }

        $this->page--;

        $this->counter--;
    }
}
