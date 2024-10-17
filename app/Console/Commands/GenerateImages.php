<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Guiding;
use App\Models\Water;
use App\Models\Target;
use App\Models\Method;
use App\Models\Levels;

class GenerateImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:images';

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
        $guidings = Guiding::where('id',99)->get();
        // $guidings = Guiding::where('id',8)->get();

        foreach($guidings as $guiding){
            if(count($guiding->galleries)){
                $i = 0;     
                foreach($guiding->galleries as $index => $photo){
                         
                        $url = env('APP_URL').'/images/'.$photo->image_name;
                        app('asset')->uploadImageFromUrl($guiding,'image_'.$i,$url);
                        $i++;
                };
            }else{
                $path = $guiding->thumbnail_path;

                $url = env('APP_URL').'/images/'.$path;
                echo($url);
                app('asset')->uploadImageFromUrl($guiding,'image_0',$url);
            }
        }
        return 0;
    }
}
