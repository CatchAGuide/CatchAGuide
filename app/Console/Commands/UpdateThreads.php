<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use App\Models\Thread;
class UpdateThreads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:threads';

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
        $threads = Thread::all();
  
        foreach($threads as $thread){


            $this->info($thread->id);
          if($thread->getThumbnailPath()){
            $webpath = $thread->thumbnail_path;
            $image = Image::make(public_path($thread->getThumbnailPath()));
            
            $webpImageName = pathinfo($webpath, PATHINFO_FILENAME) . '.webp';
            $webpImage = $image->encode('webp', 90);
            $webpImage->save(public_path('blog/' . $webpImageName));
            $webp_path = 'blog/'.$webpImageName;

            $thread->thumbnail_path = $webp_path;
            $thread->save();
          }
            $content = $thread->body;
          
            // Assuming $content contains your HTML content
                // Define a regular expression pattern to match image tags
                $pattern = '/<img\s+[^>]*src="([^"]+)"[^>]*>/i';

                // Use preg_replace_callback to replace image tags with WebP image tags
                $content = preg_replace_callback($pattern, function($match) {
                    $src = $match[1];

                    // Check if the image source starts with "data:image"
                    if (strpos($src, 'data:image') === 0) {
                        // Convert base64-encoded image to WebP
                            // Decode the base64-encoded image
                            $webpData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $src));

                            // Create an Intervention Image instance from the decoded data
                            $webpImage = Image::make($webpData);

                            // Generate a unique filename for the WebP image
                            $webpImageName = 'blog/contents/' . time() . uniqid() . '.webp';

                            // Get the public path to the folder
                            $folderPath = public_path('blog/contents');

                            // Create the folder if it doesn't exist
                            if (!File::exists($folderPath)) {
                            File::makeDirectory($folderPath, 0755, true, true);
                            }

                            // Save the image as WebP using Intervention Image
                            $webpImage->encode('webp', 90)->save(public_path($webpImageName));

                        // Return the updated image tag with the WebP source
                        return '<img src="' . asset($webpImageName) . '" alt="WebP image">';
                    } else {
                        // If the image source is not base64-encoded, return the original image tag
                        return $match[0];
                    }
                }, $content);

                $thread->body = $content;
                $thread->save();
                $this->info($thread->title);
    



            // $content = $thread->body;

            // if(!empty($content)){
            //     $dom = new \DomDocument();
            //     if($dom->loadHTML($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD)){
            //         $imageFile = $dom->getElementsByTagName('img');
          
            //         foreach ($imageFile as $item => $image) {
            //             $data = $image->getAttribute('src');
            //             list($type, $data) = explode(';', $data);
            //             list(, $data) = explode(',', $data);
            //             $imgeData = base64_decode($data);
                    
            //             // Generate a unique filename for the WebP image
            //             $webpImageName = 'blog/contents/' . time() . $item . '.webp';
                    
            //             // Get the public path to the folder
            //             $folderPath = public_path('blog/contents');
                    
            //             // Create the folder if it doesn't exist
            //             if (!File::exists($folderPath)) {
            //                 File::makeDirectory($folderPath, 0755, true, true);
            //             }
                    
            //             // Save the image as WebP
            //             $webpImage = Image::make($imgeData)->encode('webp', 75);
            //             $webpImagePath = public_path($webpImageName);
            //             $webpImage->save($webpImagePath);
                    
            //             // Remove the 'src' attribute from the original image
            //             $image->removeAttribute('src');
                    
            //             // Set the 'src' attribute to the WebP image URL
            //             $image->setAttribute('src', asset($webpImageName));
            //         }
              
            //         $content = $dom->saveHTML();
        
            //         $thread->body = $content;
            //         $thread->save();
        
            //         $this->info('Updating '.$thread->title);
            //     };
        

            // }

        }

    }
}
