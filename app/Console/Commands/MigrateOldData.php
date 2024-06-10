<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Guiding;
use App\Models\Water;
use App\Models\Target;
use App\Models\Method;
use App\Models\Levels;

class MigrateOldData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:olddata';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {



        $guidings = Guiding::get();
        // $guidings = Guiding::where('id',8)->get();

        foreach($guidings as $guiding){
            $waters = $guiding->water;
            $targets = $guiding->targets;
            $methods = $guiding->methods;

            // if(count($guiding->galleries)){
            //     $i = 0;     
            //     foreach($guiding->galleries as $index => $photo){
                         
            //             $url = env('APP_URL').'/images/'.$photo->image_name;
            //             app('asset')->uploadImageFromUrl($guiding,'image_'.$i,$url);
            //             $i++;
                
                 
            //     };
            // }else{
            //     $path = $guiding->thumbnail_path;

            //     $url = env('APP_URL').'/images/'.$path;
            //     echo($url);
            //     app('asset')->uploadImageFromUrl($guiding,'image_0',$url);
            // }
            $this->getTypes($guiding);
            
            $levels = $this->getLevels($guiding);
            $newWaters = $this->getWaters($waters);
            $newTargets = $this->getTargets($targets);
            $newMethods = $this->getMethods($methods);

            if($this->getLevels($guiding)){
                $guiding->levels()->sync($this->getLevels($guiding));
            }

            if($this->getTargets($targets)){
                $guiding->guidingTargets()->sync($this->getTargets($targets));
            }

            if($this->getMethods($methods)){
                $guiding->guidingMethods()->sync($this->getMethods($methods));
            }
            
            if($this->getWaters($waters)){
                $guiding->guidingWaters()->sync($this->getWaters($waters));
            }
        
      
            $this->info('Migrating '.$guiding->title);
        }
        return 0;
    }

    public function getLevels($guiding){
        $level = [];
        if($guiding->recommended_for_anfaenger){
            $level[] = 1;
        }
        if($guiding->recommended_for_fortgeschrittene){
            $level[] = 2;
        }
        if($guiding->recommended_for_profis){
            $level[] = 3;
        }

        return $level;
    }

    public function getTypes($guiding){
        $fishingtype_id = 0;
        $fishingfrom_id = 0;
        $required_equipment = 0;

        if($guiding->fishing_type){
            if($guiding->fishing_type == 'Aktiv'){
                $fishingtype_id = 1;
            }
            if($guiding->fishing_type == 'Aktiv & Passiv'){
                $fishingtype_id = 2;
            }
            if($guiding->fishing_type == 'Passiv'){
                $fishingtype_id = 3;
            }
        }

        if($guiding->fishing_from){
            if($guiding->fishing_from == 'Vom Boot'){
                $fishingfrom_id = 1;
            }
            if($guiding->fishing_from == 'Vom Ufer'){
                $fishingfrom_id = 2;
            }
        }

        if($guiding->required_equipment){

            if($guiding->required_equipment == 'is_there'){
                $required_equipment = 1;
            }
            if($guiding->required_equipment == 'is_needed'){
                $required_equipment = 2;
            }
            
        }

        $guiding->equipment_status_id = $required_equipment;
        $guiding->fishing_type_id = $fishingtype_id;
        $guiding->fishing_from_id = $fishingfrom_id;

        $guiding->save();
        
    }

    public function getWaters($waters){
        if($waters){
            $waters = unserialize($waters);

            $allWaters = Water::whereIn('name',$waters)->pluck('id')->toArray();
            return $allWaters;
        }
       


    }

    public function getTargets($targets){
        if($targets){
            $targets = unserialize($targets);

            $allTargets = Target::whereIn('name',$targets)->pluck('id')->toArray();
            return $allTargets;
        }
   

    }

    public function getMethods($methods){
        if($methods){
            $methods = unserialize($methods);

            $allMethods = Method::whereIn('name',$methods)->pluck('id')->toArray();
            return $allMethods;
        }


    }

    
}
