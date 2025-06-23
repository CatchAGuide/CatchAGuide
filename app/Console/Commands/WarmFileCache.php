<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Guiding;
use Illuminate\Support\Facades\Cache;

class WarmFileCache extends Command
{
    protected $signature = 'cache:warm-files';
    protected $description = 'Pre-warm the file existence cache for guiding images';

    public function handle()
    {
        $this->info('🔥 Warming file existence cache...');
        
        $guidings = Guiding::where('status', 1)
            ->whereNotNull('thumbnail_path')
            ->orWhereNotNull('gallery_images')
            ->get(['id', 'thumbnail_path', 'gallery_images']);
        
        $checkedFiles = [];
        $existingFiles = 0;
        $missingFiles = 0;
        
        $bar = $this->output->createProgressBar($guidings->count());
        $bar->start();
        
        foreach ($guidings as $guiding) {
            // Check thumbnail
            if ($guiding->thumbnail_path && !in_array($guiding->thumbnail_path, $checkedFiles)) {
                $cacheKey = 'file_exists_' . md5($guiding->thumbnail_path);
                $exists = file_exists(public_path($guiding->thumbnail_path));
                
                Cache::put($cacheKey, $exists, 3600); // Cache for 1 hour
                
                if ($exists) {
                    $existingFiles++;
                } else {
                    $missingFiles++;
                    // Cache missing files for longer to avoid repeated checks
                    Cache::put('file_missing_' . md5($guiding->thumbnail_path), true, 7200);
                }
                
                $checkedFiles[] = $guiding->thumbnail_path;
            }
            
            // Check gallery images
            $galleries = json_decode($guiding->gallery_images, true);
            if (is_array($galleries)) {
                foreach ($galleries as $imagePath) {
                    if (!empty($imagePath) && !in_array($imagePath, $checkedFiles)) {
                        $cacheKey = 'file_exists_' . md5($imagePath);
                        $exists = file_exists(public_path($imagePath));
                        
                        Cache::put($cacheKey, $exists, 3600);
                        
                        if ($exists) {
                            $existingFiles++;
                        } else {
                            $missingFiles++;
                            Cache::put('file_missing_' . md5($imagePath), true, 7200);
                        }
                        
                        $checkedFiles[] = $imagePath;
                    }
                }
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        
        $this->info("✅ File cache warmed!");
        $this->line("   📁 Total files checked: " . count($checkedFiles));
        $this->line("   ✅ Existing files: {$existingFiles}");
        $this->line("   ❌ Missing files: {$missingFiles}");
        
        if ($missingFiles > 0) {
            $this->warn("⚠️  Found {$missingFiles} missing files. Consider cleaning up database references.");
        }
    }
} 