<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use App\Models\Vacation;

class ClearVacationTranslationCacheCommand extends Command
{
    protected $signature = 'vacation:clear-translation-cache {vacation_id?}';
    protected $description = 'Clear vacation relation translation cache';

    public function handle()
    {
        $vacationId = $this->argument('vacation_id');
        
        if ($vacationId) {
            $this->clearVacationCache($vacationId);
        } else {
            $this->info('Clearing all vacation translation caches...');
            $vacations = Vacation::all();
            foreach ($vacations as $vacation) {
                $this->clearVacationCache($vacation->id);
            }
        }
        
        $this->info('✅ Vacation translation cache cleared!');
        return 0;
    }
    
    private function clearVacationCache($vacationId)
    {
        $vacation = Vacation::with(['accommodations', 'boats', 'packages', 'guidings', 'extras'])
                           ->find($vacationId);
        
        if (!$vacation) {
            $this->error("Vacation {$vacationId} not found");
            return;
        }
        
        $this->line("Clearing cache for vacation {$vacationId}: {$vacation->title}");
        
        $relations = [
            'accommodations' => 'accommodation',
            'boats' => 'boat', 
            'packages' => 'package',
            'guidings' => 'guiding',
            'extras' => 'extra'
        ];
        
        $languages = ['en', 'de', 'es', 'fr']; // Add more as needed
        
        foreach ($relations as $relationMethod => $relationType) {
            $items = $vacation->$relationMethod;
            foreach ($items as $item) {
                foreach ($languages as $language) {
                    $cacheKey = 'vacation_relation_translation_' . $item->id . '_' . $relationType . '_' . $language;
                    if (Cache::forget($cacheKey)) {
                        $this->line("  Cleared: {$cacheKey}");
                    }
                }
            }
        }
    }
}