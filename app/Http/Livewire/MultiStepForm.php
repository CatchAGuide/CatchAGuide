<?php

namespace App\Http\Livewire;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;

use Livewire\Component;
use Livewire\WithFileUploads;
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
use App\Models\FishingEquipment;



use Illuminate\Validation\Rule;


use Illuminate\Support\Facades\Session;


class MultiStepForm extends Component
{
    use WithFileUploads;

    public $debounce = 0;

    //1
    public $title;
    public $lat;
    public $lng;
    public $location;
    public $country;

    //2
    public $levels;
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
    public $guidingequipment = [];
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
    public $price;
    public $max_guests = 1;
    public $price_two_persons;
    public $price_three_persons;
    public $price_four_persons;
    public $price_five_persons;

    public $extras = [];
    public $extraName;
    public $extraPrice;


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

    protected $listeners = ['locationSelected','trixChange','fishingFromChange'];
    protected $listenersGuest = ['maxGuestsChanged'];

    public $totalStep = 5;
    public $currentStep = 1;

    public $selectedPlace;

    public $featuredImage;

    public $photos = [];
    public $imageNames = [];
    public $allphotos = [];
    public $removePhoto;
    public $maxFileCount = 5;

    public $selectedPhotos = [];
    public $imagesToDelete = [];



    public $loading = false;

    public function mount(){
        $this->recommended_for_anfaenger = 0;
        $this->recommended_for_fortgeschrittene = 0;
        $this->recommended_for_profis = 0;
        $this->aboutBoat = false;
        $this->currentStep = 1;
    }

    public function render()
    {
        $this->allinclussions = Inclussion::all();
        $this->allfishingfrom = FishingFrom::all();
        $this->allfishingtypes = FishingType::all();
        $this->allfishingequipment = FishingEquipment::all();
        $this->allequipmentStatus = EquipmentStatus::all();
        $this->levels = Levels::all();
        $this->allwaters = Water::orderBy('name','asc')->get();
        $this->alltargets = Target::orderBy('name','asc')->get();
        $this->allmethods = Method::orderBy('name','asc')->get();
        return view('livewire.multi-step-form');
    }



    public function increaseStep(){

        $this->validateData();


        if($this->currentStep == 1){
                $this->validate([
                    'selectedPlace' => 'required',
                ]);

                if(count($this->selectedPhotos) <= 0){
                    $this->addError('photos', 'Please upload at least one image.');
                    return;
                }

        }

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

    public function locationSelected($data)
    {

        $this->location = $data['location'];
        $this->lat = $data['lat'];
        $this->lng = $data['lng'];
        $this->selectedPlace = $this->location;
        $this->country = $data['country'];
    }

    public function initializeAutocomplete()
    {
        $this->dispatchBrowserEvent('initialize-autocomplete');
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
                'additional_information' => ['nullable', 'string'],
            ]);
        }
        elseif($this->currentStep == 3){
            $this->validateOnly($propertyName,[
                // 'required_equipment' => ['required','numeric'],
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
            }
            if($this->max_guests == 3){
                $this->validateOnly($propertyName,[
                    'price_two_persons' => ['required', 'numeric'],
                    'price_three_persons' => ['required', 'numeric'],
                ]);
            }
            if($this->max_guests == 4){
                $this->validateOnly($propertyName,[
                    'price_two_persons' => ['required', 'numeric'],
                    'price_three_persons' => ['required', 'numeric'],
                    'price_four_persons' => ['required', 'numeric'],
                ]);

            }
            if($this->max_guests == 5){
                $this->validateOnly($propertyName,[
                    'price_two_persons' => ['required', 'numeric'],
                    'price_three_persons' => ['required', 'numeric'],
                    'price_four_persons' => ['required', 'numeric'],
                    'price_five_persons' => ['required', 'numeric'],
                ]);
            }
            if(count($this->extras) > 0){

                $this->validateOnly($propertyName,[
                    'extras.*.extraName' => ['required', 'string'],
                    'extras.*.extraPrice' => ['required', 'numeric'],
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
                'meeting_point' => ['nullable', 'string'],
                'additional_information' => ['nullable', 'string'],

            ]);
        }
        elseif($this->currentStep == 3){
            $this->validate([
                // 'required_equipment' => ['required','numeric'],
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
                    'price_two_persons' => ['required', 'numeric'],
                ]);
                 $this->price_two_persons;
                 $this->price_three_persons = null;
                 $this->price_four_persons = null;
                $this->price_five_persons = null;
            }
            if($this->max_guests == 3){
                $this->validate([
                    'price_three_persons' => ['required', 'numeric'],
                ]);
                $this->price_two_persons;
                $this->price_three_persons;
                $this->price_four_persons = null;
               $this->price_five_persons = null;
            }
            if($this->max_guests == 4){
                $this->validate([
                    'price_four_persons' => ['required', 'numeric'],
                ]);
                $this->price_two_persons;
                $this->price_three_persons;
                $this->price_four_persons;
                $this->price_five_persons = null;
            }
            if($this->max_guests == 5){
                $this->validate([
                    'price_five_persons' => ['required', 'numeric'],
                ]);
            }
            if(count($this->extras) > 0){

                $this->validate([
                    'extras.*.extraName' => ['required', 'string'],
                    'extras.*.extraPrice' => ['required', 'numeric'],
                ]);

            }
        }
    }

    public function updatedPhotos(){

        $countSelectedPhotos = count($this->photos) +  count($this->selectedPhotos);



        foreach($this->photos as $index => $image){

            $validator = Validator::make(['image' => $image], [
                'image' => 'image|dimensions:min_width=800,min_height=600|mimes:jpeg,jpg,png,webp',
            ]);


            if ($validator->passes() && $countSelectedPhotos < 6) {

                $this->selectedPhotos[] = $image;

            }elseif ($validator->fails() && $validator->errors()->has('image')) {

                session()->flash('invalid_image', 'The image should be at least 800x600 pixels.');
            }else{
                session()->flash('invalid_image', '5 only');
            }

        }

        $this->imageNames = collect($this->selectedPhotos)->map(function ($image) {
            return $image->getClientOriginalName();
        })->toArray();

    }


    public function removePhoto($index)
    {
        // File::delete($this->photos[$index]->getRealPath());
        // unset($this->photos[$index]);
        $this->photos = array_values($this->photos);

        if (isset($this->selectedPhotos[$index])) {
            $filePath = $this->selectedPhotos[$index]->getRealPath();
            unset($this->selectedPhotos[$index]);
            $this->selectedPhotos = array_values($this->selectedPhotos);
            File::delete($filePath);
        }
    }



    public function register()
    {

        $this->validateData();
        $this->loading = true;
        $latitude = $this->lat;
        $longitude = $this->lng;
        $location = $this->location;

        $galleries = [];

        if(count($this->selectedPhotos)){

            foreach($this->selectedPhotos as $photo){

                $galleries[] = $this->ImageUpload($photo);

            }

        }

        //featured image
        $featured_image = null;

        if($this->featuredImage){
            $featured_image = $this->imageUpload($this->featuredImage);
        }


        $guiding = Guiding::create([
            'title' =>  $this->title,
            'slug' =>  slugify($this->title.'-in-'.$this->location),
            'location' =>  $this->location,
            'country' => $this->country,
            'max_guests' =>  $this->max_guests,
            'duration' =>  $this->duration,
            'price' =>  $this->price,
            'lat' =>  $this->lat,
            'lng' =>  $this->lng,
            'user_id' =>  auth()->id(),
            'water_sonstiges' =>  $this->water_sonstiges,
            'description' => $this->description,
            'fishing_from_id' =>  $this->fishing_from,
            'fishing_type_id' =>  $this->fishing_type,
            'boat_information' => $this->boat_information,
            'additional_information' =>  $this->additional_information,
            'required_special_license' =>  $this->required_special_license,
            'price_two_persons' =>  $this->price_two_persons,
            'price_three_persons' =>  $this->price_three_persons,
            'price_four_persons' =>  $this->price_four_persons,
            'price_five_persons' =>  $this->price_five_persons,
            'water_name' =>  $this->water_name,
            'thumbnail_path' => $featured_image,
            'galleries' => json_encode($galleries),
            'needed_equipment' =>  $this->needed_equipment,
            // 'equipment_status_id' =>  $this->required_equipment,
            'meeting_point' =>  $this->meeting_point,
            'target_fish_sonstiges' =>  $this->target_fish_sonstiges,
            'methods_sonstiges' => $this->methods_sonstiges,
        ]);





        $this->photos = [];

        $guiding->inclussions()->attach($this->inclussions);
        $guiding->levels()->attach($this->selectedLevels);
        $guiding->guidingTargets()->attach($this->targets);
        $guiding->guidingMethods()->attach($this->methods);
        $guiding->guidingWaters()->attach($this->water);
        $guiding->fishing_equipment()->attach($this->guidingequipment);

        foreach($this->extras as $extra){
            if($extra){
                GuidingExtras::create([
                    'guiding_id' => $guiding->id,
                    'name' => $extra['extraName'],
                    'price' => $extra['extraPrice']
                ]);

            }
        }

        sleep(3);
        // Hide the loading overlay
        $this->loading = false;

        return redirect()->route('profile.myguidings')->with(['message' => 'Das Guiding wurde erfolgreich erstellt!']);

    }

    public function updatedSpecialLicenseNeeded($value)
    {
        if ($value === 'Nein') {
            $this->inputVisible = false;
        } else {
            $this->inputVisible= true;
        }
    }

    // public function updatedGearNeeded($value){

    //     $this->required_equipment = $value;
    // }

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

    public function ImageUpload($photo){

        $directory = public_path('assets/guides');

        // Ensure the directory exists
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true); // Recursive directory creation
        }

        // Generate a unique filename for each photo
        $imageName = time() . '_' . Str::random(10) . '.' . $photo->getClientOriginalExtension();

        // Resize and save the image using Intervention Image
        $image = Image::make($photo->getRealPath())->orientate()->resize(800, 600);
        $image->save(public_path('assets/guides/' . $imageName));

        // Add the filename to the $galleries array
        $galleries = $imageName;

        return $galleries;
    }

}
