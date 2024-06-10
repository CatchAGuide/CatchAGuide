<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;



use App\Models\Guiding;


class PopulateGuidingImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:populateimages';

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
        $guidings = Guiding::all();
        $directory = public_path('assets/guides');
                
        foreach ($guidings as $guiding) {
            $images = app('guiding')->getImagesUrl($guiding);
    
            if (count($images)) {
                $i = 0;
                $galleries = [];
    
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true); // Recursive directory creation
                }
    
                foreach ($images as $image) {
                    try {
                        $originalName = basename(parse_url($image, PHP_URL_PATH));
                        $imageName = time() . '_' . Str::random(10) . '_' . $originalName;
    
                        $imageData = file_get_contents($image);
                        if ($imageData !== false) {
                            $imagePath = public_path('assets/guides/' . $imageName);
                            file_put_contents($imagePath, $imageData);
    
                            if ($i === 0) {
                                $guiding->thumbnail_path = $imageName;
                            } else {
                                $galleries[] = $imageName;
                            }
                            $i++;
                        } else {
                            $this->error('Failed to retrieve image data from URL: ' . $image);
                        }
                    } catch (\Exception $e) {
                        $this->error('Error processing image from URL: ' . $image . '. ' . $e->getMessage());
                    }
                }
                
                $guiding->galleries = json_encode($galleries);
                $guiding->save();
            }
    
            $this->info('Processing next guiding...');
        }
    
        return 0;
    }

    public function imageUpload(){
        
    }
}
