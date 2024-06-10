<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;

use App\Models\Guiding;
use App\Models\Water;
use App\Models\Method;
use App\Models\Target;
use App\Models\Inclussion;
use App\Models\GuideInclussion;
use App\Models\GuidingExtras;
use App\Models\Levels;
use App\Models\FishingType;
use App\Models\FishingFrom;
use App\Models\EquipmentStatus;



use Livewire\WithFileUploads;

class EditGuiding extends Component
{

    use WithFileUploads;

    public $guiding;
    public $debounce = 0;

    //1
    public $title;
    public $lat;
    public $lng;
    public $location;

    //2
    public $levels = [];
    public $selectedLevels = [];
    public $inclussions = [];
    public $recommended_for_anfaenger;
    public $recommended_for_fortgeschrittene;
    public $recommended_for_profis;
    public $duration;
    public $special_license_needed;
    public $required_special_license;
    public $water_name;
    public $meeting_point;
    public $additional_information;
    public $provided_equipment;

    public $SpecialLicenseNeeded = '';
    public $inputVisible = false;
    public $inputValue = '';

    //3
    public $fishing_type;
    public $fishing_from;
    public $equipment_status;
    public $boat_information;
    public $water = [];
    public $targets = [];
    public $methods = [];
    public $methods_sonstiges;
    public $water_sonstiges;
    public $target_fish_sonstiges;
    public $required_equipment = '';
    
    public $needed_equipment  = '';
    public $aboutBoat = false;
    public $is_needed = false;

    //4
    public $description;

    //5
    public $price = 0;
    public $max_guests = 1;
    public $price_two_persons = 0;
    public $price_three_persons = 0;
    public $price_four_persons = 0;
    public $price_five_persons = 0;

    public $extras = [];
    public $extraName;
    public $extraPrice;

    public $activeExtra = [];
    
    
    public $p1 = true;
    public $p2 = false;
    public $p3 = false;
    public $p4 =  false;
    public $p5 = false;

    //end
    public $allwaters;
    public $alltargets;
    public $allmethods;
    public $allinclussions;
    public $allfishingtypes;
    public $allfishingfrom;
    public $allequipmentStatus;

    public $typeFishing = '';
    public $fishingFrom = '';

    public $message;
    public $value;
    public $trixId;

    protected $listeners = ['locationSelected','trixChange'];
    protected $listenersGuest = ['maxGuestsChanged'];

    public $totalStep = 5;
    public $currentStep = 1;

    public $selectedPlace;

    public $featuredImage;
    
    public $photos = [];
    public $allphotos = [];
    public $removePhoto;
    public $maxFileCount = 5;

    public $selectedPhotos = [];
    public $imagesToDelete = [];

    public $userPhotos;

    public $loading = false;


    public function mount(Guiding $guiding)
    {
        $this->guiding = $guiding;

    

        $this->title = $guiding->title;
        $this->lat = $guiding->lat;
        $this->lng = $guiding->lng;
        $this->location = $guiding->location;
        $this->selectedPlace = $guiding->location;

        foreach($guiding->inclussions as $guideInc){
            $this->inclussions[$guideInc->id] = $guideInc->id;
        }


        $this->recommended_for_anfaenger = $guiding->recommended_for_anfaenger;
        $this->recommended_for_fortgeschrittene = $guiding->recommended_for_fortgeschrittene;
        $this->recommended_for_profis = $guiding->recommended_for_profis;
        $this->required_special_license = $guiding->required_special_license;
        $this->duration = $guiding->duration;

        if($guiding->required_special_license){
            $this->SpecialLicenseNeeded = 'Ja';
            $this->special_license_needed = 'Ja';
            $this->inputVisible= true; 
        }

        $this->water_name = $guiding->water_name;
        $this->meeting_point = $guiding->meeting_point;
        $this->additional_information = $guiding->additional_information;
        $this->fishing_type = $guiding->fishing_type_id;
        $this->fishing_from = $guiding->fishing_from_id;
        $this->required_equipment = $guiding->equipment_status_id;
        $this->needed_equipment = $guiding->needed_equipment;

        $this->userPhotos = $guiding->galleries ? json_decode($guiding->galleries, true) : [];
   
     
   
        if($guiding->fishing_from_id == 1){
            $this->aboutBoat = true;
            $this->boat_information = $guiding->boat_information;
        }

        if($guiding->equipment_status_id == 2){
            $this->is_needed = true;
            $this->needed_equipment = $guiding->needed_equipment;
        }


        
        foreach($guiding->guidingWaters as $water){
            $this->water[$water->id] = $water->id;
        }


        $this->water_sonstiges = $guiding->water_sonstiges;

        foreach($guiding->guidingTargets as $target){
            $this->targets[$target->id] = $target->id;
        }

        $this->target_fish_sonstiges = $guiding->target_fish_sonstiges;

        foreach($guiding->guidingMethods as $method){
            $this->methods[$method->id] = $method->id;
        }

        $this->methods_sonstiges = $guiding->methods_sonstiges;

        $this->description = $guiding->description;

        $this->max_guests = $guiding->max_guests;

        $this->price = $guiding->price;

        $this->price_two_persons = $guiding->price_two_persons;
        if($guiding->price_two_persons){
           $this->p2 = true;
        }

        $this->price_three_persons = $guiding->price_three_persons;
        if($guiding->price_two_persons){
            $this->p3 = true;
         }
        $this->price_four_persons = $guiding->price_four_persons;
        if($guiding->price_two_persons){
            $this->p4 = true;
         }
        $this->price_five_persons = $guiding->price_five_persons;
        if($guiding->price_two_persons){
            $this->p5 = true;
         }

       
        foreach ($this->guiding->extras as $extra) {
            $this->extras[$extra->id] = [
                'name' => $extra->name,
                'price' => $extra->price,
            ];
        }


        foreach($guiding->levels as $guideLevel){
            $this->selectedLevels[$guideLevel->id] = $guideLevel->id;
        }


          

    }

    public function render()
    {
        $this->allinclussions = Inclussion::all();
        $this->allfishingfrom = FishingFrom::all();
        $this->allfishingtypes = FishingType::all();
        $this->allequipmentStatus = EquipmentStatus::all();
        $this->levels = Levels::all();
        $this->allwaters = Water::all();
        $this->alltargets = Target::all();
        $this->allmethods = Method::all();
        

        return view('livewire.edit-guiding',[
            'guiding' => $this->guiding,
        ]);
    }



    public function increaseStep(){
        if($this->currentStep == 2){
            if (!in_array(true,$this->selectedLevels)) {
                $this->addError('selectedLevels', 'At least one checkbox must be selected.');
                return;
            }
        }

        if($this->currentStep == 3){
            if($this->aboutBoat == true){
                $this->validate([
                    'boat_information' => ['required', 'string'],
                ]);
            }
        }



        $this->validateData();
        $this->currentStep++;

        if($this->currentStep > $this->totalStep){
            $this->currentStep =  $this->totalStep;
        }

        $this->emit('scrollToTop');

    }

    public function decreaseStep(){
        
        $this->currentStep--;
        $this->emit('stepDecreased', ['selectedPlace' => $this->selectedPlace]);
        $this->emit('scrollToTop');

    }

    public function updated($propertyName){
        
        if($this->currentStep == 1){
            $this->validateOnly($propertyName,[
                'title' => ['required', 'string'],
                'location' => ['required', 'string'],
                'lat' => ['nullable', 'numeric'],
                'lng' => ['nullable', 'numeric'],
            ]);
        }elseif($this->currentStep == 2){
            $this->validateOnly($propertyName,[
                'duration' => ['required', 'numeric'],
                'required_special_license' => ['nullable', 'string'],
                'water_name' => ['required', 'string'],
                'meeting_point' => ['required', 'string'],
                'additional_information' => ['nullable', 'string'],
            ]);
        }
        elseif($this->currentStep == 3){
            $this->validateOnly($propertyName,[
                'required_equipment' => ['required','numeric'],
                'fishing_from' => ['required','numeric'],
                'fishing_type' => ['required','numeric'],
                'water' => ['required', 'array'],
                'targets' => ['required', 'array'],
                'methods' => ['required', 'array'],
            ]);
            
        }
        elseif($this->currentStep == 4){
            $this->validateOnly($propertyName,[
                'description' => ['required', 'string'],
            ]);
        }
        elseif($this->currentStep == 5){
            $this->validateOnly($propertyName,[
                'max_guests' => ['required', 'integer'],
                'price' => ['required', 'numeric'],
            ]);

            if($this->max_guests == 2){
                $this->validateOnly($propertyName,[
                    'price_two_persons' => ['required', 'numeric'],
                ]);
                 $this->price_two_persons;
                 $this->price_three_persons = null;
                 $this->price_four_persons = null;
                $this->price_five_persons = null;
            }
            if($this->max_guests == 3){
                $this->validateOnly($propertyName,[
                    'price_three_persons' => ['required', 'numeric'],
                ]);
                $this->price_two_persons;
                $this->price_three_persons;
                $this->price_four_persons = null;
               $this->price_five_persons = null;
            }
            if($this->max_guests == 4){
                $this->validateOnly($propertyName,[
                    'price_four_persons' => ['required', 'numeric'],
                ]);
                $this->price_two_persons;
                $this->price_three_persons;
                $this->price_four_persons;
                $this->price_five_persons = null;
            }
            if($this->max_guests == 5){
                $this->validateOnly($propertyName,[
                    'price_five_persons' => ['required', 'numeric'],
                ]);
            }
            if(count($this->extras) > 0){
           
                $this->validateOnly($propertyName,[
                    'extras.*.name' => ['required', 'string'],
                    'extras.*.price' => ['required', 'numeric'],
                ]);

            }
        }
    }

    public function validateData(){
        if($this->currentStep == 1){
            $this->validate([
                'title' => ['required', 'string'],
                'location' => ['required', 'string'],
                'lat' => ['nullable', 'numeric'],
                'lng' => ['nullable', 'numeric'],
            ]);
        }elseif($this->currentStep == 2){
            $this->validate([
                'selectedLevels' => 'required|array',
                'duration' => ['required', 'numeric'],
                'required_special_license' => ['nullable', 'string'],
                'water_name' => ['required', 'string'],
                'meeting_point' => ['required', 'string'],
                'additional_information' => ['nullable', 'string'],
     
            ]);
        }
        elseif($this->currentStep == 3){
            $this->validate([
                'required_equipment' => ['required','numeric'],
                'fishing_from' => ['required','numeric'],
                'fishing_type' => ['required','numeric'],
                'water' => ['required', 'array'],
                'targets' => ['required', 'array'],
                'methods' => ['required', 'array'],
            ]);
            
        }
        elseif($this->currentStep == 4){
            $this->validate([
                'description' => ['required', 'string'],
            ]);
        }
        elseif($this->currentStep == 5){
            $this->validate([
                'max_guests' => ['required', 'integer'],
                'price' => ['required', 'numeric'],

            ]);

            if($this->max_guests == 2){
                $this->validate([
                    'price' => ['required', 'numeric'],
                    'price_two_persons' => ['required', 'numeric'],
                ]);
                $this->price_two_persons;
                $this->price_three_persons = null;
                $this->price_four_persons = null;
               $this->price_five_persons = null;
            }
            if($this->max_guests == 3){
                $this->validate([
                    'price' => ['required', 'numeric'],
                    'price_two_persons' => ['required', 'numeric'],
                    'price_three_persons' => ['required', 'numeric'],
                ]);
                $this->price_two_persons;
                $this->price_three_persons;
                $this->price_four_persons = null;
               $this->price_five_persons = null;
            }
            if($this->max_guests == 4){
                $this->validate([
                    'price' => ['required', 'numeric'],
                    'price_two_persons' => ['required', 'numeric'],
                    'price_three_persons' => ['required', 'numeric'],
                    'price_four_persons' => ['required', 'numeric'],
                ]);
                $this->price_two_persons;
                $this->price_three_persons;
                $this->price_four_persons;
                $this->price_five_persons = null;
            }
            if($this->max_guests == 5){
                $this->validate([
                    'price' => ['required', 'numeric'],
                    'price_two_persons' => ['required', 'numeric'],
                    'price_three_persons' => ['required', 'numeric'],
                    'price_four_persons' => ['required', 'numeric'],
                    'price_five_persons' => ['required', 'numeric'],
                ]);
            }
            if(count($this->extras) > 0){
           
                    $this->validate([
                        'extras.*.name' => ['required', 'string'],
                        'extras.*.price' => ['required', 'numeric'],
                    ]);
 
            }
        }


    }

    
    public function updatedPhotos(){

        $countExistingPhotos = count($this->userPhotos) ? count($this->userPhotos) : 0 ;
        $countSelectedPhotos = count($this->photos) +  count($this->selectedPhotos);

        $countAllPhotos = $countSelectedPhotos + $countExistingPhotos;

        foreach($this->photos as $key => $image){
            
            $validator = Validator::make(['image' => $image], [
                'image' => 'image|dimensions:min_width=800,min_height=600|mimes:jpeg,jpg,png,webp',
            ]);
 
            if ($validator->passes() && $countAllPhotos < 6) {
          
                $this->allphotos[] = $image;
                $this->selectedPhotos[] = $image;
            }elseif ($validator->fails() && $validator->errors()->has('image')) {

                session()->flash('invalid_image', 'Oops! It seems there was an issue with your image upload. Please try again with an image that is at least 800x600 pixels in size.');
            }else{
                session()->flash('invalid_image', 'Something went wrong. Please try again.');
            }

        }
      
    }
  
    public function updatedSpecialLicenseNeeded($value)
    {
        if ($value === 'Nein') {
            $this->inputVisible = false;
        } else {
            $this->inputVisible= true;
        }
    }

    public function updatedGearNeeded($value){
     
        $this->required_equipment = $value;
    }

    public function updatedMaxGuests($value)
    {
        // Reset the input values
        $numInputs = intval($value);

        $this->p1 = true;
        $this->p2 = $numInputs >= 2;
        $this->p3 = $numInputs >= 3;
        $this->p4 = $numInputs >= 4;
        $this->p5 = $numInputs >= 5;


    }   

    public function updatedFishingFrom($value){
        $this->fishing_from = $value;
        if($value == 1){
            $this->aboutBoat = true;
        }else{
            $this->aboutBoat = false;
        }
        
    }
        
    public function updatedRequiredEquipment($value){
  
        if($value == 2){
            $this->is_needed = true;
        }else{
            $this->is_needed = false;
        }
        
    }

    public function removePhoto($index)
    {           
        $this->photos = array_values($this->photos);

        if (isset($this->selectedPhotos[$index])) {
            $filePath = $this->selectedPhotos[$index]->getRealPath();
            unset($this->selectedPhotos[$index]);
            $this->selectedPhotos = array_values($this->selectedPhotos);    
            File::delete($filePath);
        }
    }

    public function deleteUserPhoto($image){


        $imagepath = 'assets/guides/'.$this->userPhotos[$image];

        if (file_exists($imagepath)) {
            // unlink($imagepath);
            $this->imagesToDelete[] = $this->userPhotos[$image];
            unset($this->userPhotos[$image]);
        } 

       
        //  $imagePath = public_path('guidings/'.$$this->userPhotos[$image]);
      
        // app('asset')->deleteThumbnails($this->guiding, $imageName);
        // app('asset')->deleteImage($this->guiding, $imageName);
    }

    public function addExtra()
    {
        $this->extras[] = [
            'extraName' => $this->extraName,
            'extraPrice' => $this->extraPrice,
        ];
    }   


    public function removeExtra($key)
    {
        unset($this->extras[$key]);
    }

        public function locationSelected($data)
    {
        $this->location = $data['location'];
        $this->lat = $data['lat'];
        $this->lng = $data['lng'];
        $this->selectedPlace = $this->location;
    }

    public function register()
    {           
    
        $this->validateData();
        $this->loading = true;

        $latitude = $this->lat;
        $longitude = $this->lng;
        $location = $this->location;

        $this->guiding->title =  $this->title;
        $this->guiding->location =  $this->location;
        $this->guiding->max_guests =  $this->max_guests;
        $this->guiding->duration =  $this->duration;
        $this->guiding->price =  $this->price;
        $this->guiding->lat =  $this->lat;
        $this->guiding->lng =  $this->lng;
        $this->guiding->user_id = auth()->id();
        $this->guiding->water_sonstiges =  $this->water_sonstiges;
        $this->guiding->description =  $this->description;
        $this->guiding->fishing_type_id =  $this->fishing_type;
        $this->guiding->fishing_from_id =  $this->fishing_from;
        $this->guiding->equipment_status_id =  $this->required_equipment;

        $this->guiding->additional_information =  $this->additional_information;
        $this->guiding->required_special_license =  $this->required_special_license;

        $this->guiding->price_two_persons =  $this->price_two_persons;
        $this->guiding->price_three_persons =  $this->price_three_persons;
        $this->guiding->price_four_persons =  $this->price_four_persons;
        $this->guiding->price_five_persons =  $this->price_five_persons;
        $this->guiding->water_name =  $this->water_name;
        $this->guiding->needed_equipment =  $this->needed_equipment;
        $this->guiding->meeting_point =  $this->meeting_point;
        $this->guiding->target_fish_sonstiges =  $this->target_fish_sonstiges;
        $this->guiding->methods_sonstiges =  $this->methods_sonstiges;
        $this->guiding->boat_information =  $this->boat_information;


        $galleries = [];  

        if(count($this->imagesToDelete)){

            foreach($this->imagesToDelete as $index => $deleteImage){
                $imagepath = 'assets/guides/'.$deleteImage;
                    if (file_exists($imagepath)) {
                        unlink($imagepath);
                    }
            }

            foreach($this->userPhotos as $usp){
                $galleries[] = $usp; 
            }

            $this->guiding->galleries =  json_encode($galleries);
        
        }


        if(count($this->selectedPhotos)){  
           
            foreach($this->selectedPhotos as $photo){
                
                $galleries[] = $this->ImageUpload($photo);

            }

            $this->guiding->galleries =  json_encode($galleries);

        }

            
        //featured image
        if($this->featuredImage){
            $featured_image = $this->imageUpload($this->featuredImage);
            $this->guiding->thumbnail_path = $featured_image;
        }



        $this->guiding->save();


        $this->photos = [];


        $this->guiding->inclussions()->sync($this->inclussions);
        $this->guiding->levels()->sync($this->selectedLevels);
        $this->guiding->guidingTargets()->sync($this->targets);
        $this->guiding->guidingMethods()->sync($this->methods);
        $this->guiding->guidingWaters()->sync($this->water);


        $newExtras = collect($this->extras)->map(function ($extra) {
            return [
                'guiding_id' => $this->guiding->id,
                'name' => $extra['name'],
                'price' => $extra['price'],
            ];
        });
    
        $this->guiding->extras()->delete();
        $this->guiding->extras()->createMany($newExtras);

        sleep(3);
        // Hide the loading overlay
        $this->loading = false;

        return redirect()->route('profile.myguidings')->with(['message' => 'Das Guiding wurde erfolgreich erstellt!']);


    }

    public function ImageUpload($photo){

        $directory = public_path('assets/guides');
                
        // Ensure the directory exists
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true); // Recursive directory creation
        }

        // Generate a unique filename for each photo
        $imageName = time() . '_' . Str::random(10) . '.' . $photo->getClientOriginalExtension();

        // Resize and save the image using Intervention Image
        $image = Image::make($photo->getRealPath())->resize(800, 600);
        $image->save(public_path('assets/guides/' . $imageName));

        // Add the filename to the $galleries array
        $galleries = $imageName;

        return $galleries;
    }
}
