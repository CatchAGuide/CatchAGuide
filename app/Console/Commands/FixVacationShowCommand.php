<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixVacationShowCommand extends Command
{
    protected $signature = 'fix:vacation-show';
    protected $description = 'Fix vacation show view to properly display translated data like guidings';

    public function handle()
    {
        $this->info('Analyzing vacation show view translation issues...');
        
        $viewPath = resource_path('views/pages/vacations/show.blade.php');
        $content = File::get($viewPath);
        
        // Create backup
        $backupPath = $viewPath . '.backup-' . date('Y-m-d-H-i-s');
        File::put($backupPath, $content);
        $this->line("Backup created: {$backupPath}");
        
        // Patterns to fix based on guidings implementation
        $replacements = [
            // Fix title usage - use translate() function like guidings
            '/\{\{\s*\$translatedVacation->title\s*\}\}/' => '{{ translate($vacation->title) }}',
            
            // Fix description with fallback
            '/\{\{\s*\$translatedVacation->surroundings_description\s*\}\}/' => '{{ $translatedVacation->surroundings_description ?? $vacation->surroundings_description }}',
            
            // Fix HTML description with proper escaping
            '/\{!!\s*\$translatedVacation->surroundings_description\s*!!\}/' => '{!! $translatedVacation->surroundings_description ?? $vacation->surroundings_description !!}',
            
            // Fix array handling for best_travel_times
            '/is_array\(\$translatedVacation->best_travel_times\)\s*\?\s*implode\([^}]+\)\s*:\s*\$translatedVacation->best_travel_times/' => '(is_array($translatedVacation->best_travel_times ?? $vacation->best_travel_times) ? implode(\', \', $translatedVacation->best_travel_times ?? $vacation->best_travel_times) : ($translatedVacation->best_travel_times ?? $vacation->best_travel_times))',
            
            // Fix array handling for target_fish
            '/is_array\(\$translatedVacation->target_fish\)\s*\?\s*implode\([^}]+\)\s*:\s*\$translatedVacation->target_fish/' => '(is_array($translatedVacation->target_fish ?? $vacation->target_fish) ? implode(\', \', $translatedVacation->target_fish ?? $vacation->target_fish) : ($translatedVacation->target_fish ?? $vacation->target_fish))',
            
            // Fix array handling for included_services
            '/is_array\(\$translatedVacation->included_services\)\s*\?\s*implode\([^}]+\)\s*:\s*\$translatedVacation->included_services/' => '(is_array($translatedVacation->included_services ?? $vacation->included_services) ? implode(\', \', $translatedVacation->included_services ?? $vacation->included_services) : ($translatedVacation->included_services ?? $vacation->included_services))',
            
            // Fix travel_included with fallback
            '/\{\{\s*\$translatedVacation->travel_included\s*\}\}/' => '{{ $translatedVacation->travel_included ?? $vacation->travel_included }}',
            
            // Fix distance fields with fallback
            '/\{\{\s*\$translatedVacation->airport_distance\s*\}\}/' => '{{ $translatedVacation->airport_distance ?? $vacation->airport_distance }}',
            '/\{\{\s*\$translatedVacation->water_distance\s*\}\}/' => '{{ $translatedVacation->water_distance ?? $vacation->water_distance }}',
            '/\{\{\s*\$translatedVacation->shopping_distance\s*\}\}/' => '{{ $translatedVacation->shopping_distance ?? $vacation->shopping_distance }}',
        ];
        
        $changeCount = 0;
        foreach ($replacements as $pattern => $replacement) {
            $newContent = preg_replace($pattern, $replacement, $content, -1, $count);
            if ($count > 0) {
                $content = $newContent;
                $changeCount += $count;
                $this->line("Applied replacement: {$count} matches for pattern");
            }
        }
        
        // Additional fixes for travel_options array
        $travelOptionsPattern = '/is_array\(\$translatedVacation->travel_options\)\s*\?\s*\$translatedVacation->travel_options\s*:\s*\[\]/';
        $travelOptionsReplacement = '(is_array($translatedVacation->travel_options ?? $vacation->travel_options) ? ($translatedVacation->travel_options ?? $vacation->travel_options) : [])';
        $content = preg_replace($travelOptionsPattern, $travelOptionsReplacement, $content, -1, $count);
        if ($count > 0) {
            $changeCount += $count;
            $this->line("Fixed travel_options array handling: {$count} matches");
        }
        
        // Write the fixed content
        File::put($viewPath, $content);
        
        $this->info("Fixed vacation show view with {$changeCount} changes");
        $this->line("Original backed up to: {$backupPath}");
        
        return 0;
    }
}