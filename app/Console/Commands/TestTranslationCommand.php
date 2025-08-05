<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Translation\GeminiTranslationService;
use App\Services\Translation\VacationTranslationService;
use App\Helpers\TranslationHelper;
use App\Models\Vacation;

class TestTranslationCommand extends Command
{
    protected $signature = 'test:translation';
    protected $description = 'Test translation services to debug staging issues';

    public function handle()
    {
        $this->info('Starting Translation Service Debug...');
        
        // Test 1: Check environment variables
        $this->info('1. Checking environment variables...');
        $this->line('GOOGLE_GEMINI_API_KEY: ' . (config('services.gemini.key') ? 'SET' : 'NOT SET'));
        $this->line('GEMINI_BASE_URL: ' . (config('services.gemini.base_url') ?: 'NOT SET'));
        $this->line('GEMINI_MODEL: ' . (config('services.gemini.model') ?: 'NOT SET'));
        
        // Test 2: Check if translation_prompts.json exists
        $this->info('2. Checking translation_prompts.json...');
        $promptsPath = config_path('translation_prompts.json');
        if (file_exists($promptsPath)) {
            $this->line('✓ translation_prompts.json exists');
            $prompts = json_decode(file_get_contents($promptsPath), true);
            $this->line('✓ JSON is valid: ' . (is_array($prompts) ? 'YES' : 'NO'));
        } else {
            $this->error('✗ translation_prompts.json NOT FOUND at: ' . $promptsPath);
            return 1;
        }
        
        // Test 3: Try to instantiate GeminiTranslationService
        $this->info('3. Testing GeminiTranslationService instantiation...');
        try {
            $geminiService = new GeminiTranslationService();
            $this->line('✓ GeminiTranslationService created successfully');
        } catch (\Exception $e) {
            $this->error('✗ Failed to create GeminiTranslationService: ' . $e->getMessage());
            return 1;
        }
        
        // Test 4: Try to instantiate VacationTranslationService  
        $this->info('4. Testing VacationTranslationService instantiation...');
        try {
            $vacationService = new VacationTranslationService();
            $this->line('✓ VacationTranslationService created successfully');
        } catch (\Exception $e) {
            $this->error('✗ Failed to create VacationTranslationService: ' . $e->getMessage());
            return 1;
        }
        
        // Test 5: Try to initialize TranslationHelper
        $this->info('5. Testing TranslationHelper initialization...');
        try {
            TranslationHelper::init();
            $this->line('✓ TranslationHelper initialized successfully');
        } catch (\Exception $e) {
            $this->error('✗ Failed to initialize TranslationHelper: ' . $e->getMessage());
            return 1;
        }
        
        // Test 6: Test simple translation
        $this->info('6. Testing simple translation...');
        try {
            $result = $geminiService->translate('Hello world');
            $this->line('✓ Simple translation successful: ' . substr($result, 0, 50) . '...');
        } catch (\Exception $e) {
            $this->error('✗ Simple translation failed: ' . $e->getMessage());
            return 1;
        }
        
        // Test 7: Get a vacation and test translation
        $this->info('7. Testing vacation retrieval and translation setup...');
        try {
            $vacation = Vacation::where('status', true)->first();
            if ($vacation) {
                $this->line('✓ Found vacation: ' . $vacation->title . ' (ID: ' . $vacation->id . ')');
                $this->line('   Source language: ' . ($vacation->language ?: 'NOT SET'));
                
                // Test if translation would be attempted
                $hasChanges = $vacationService->hasSignificantChanges($vacation, 'en');
                $this->line('   Has significant changes: ' . ($hasChanges ? 'YES' : 'NO'));
                
                $existing = $vacationService->getTranslatedVacation($vacation, 'en');
                $this->line('   Has existing translation: ' . ($existing ? 'YES' : 'NO'));
                
            } else {
                $this->error('✗ No active vacations found');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('✗ Vacation test failed: ' . $e->getMessage());
            return 1;
        }
        
        // Test 8: Test logging
        $this->info('8. Testing logging...');
        try {
            \Log::info('Test log entry from translation debug command');
            $this->line('✓ Logging appears to work');
        } catch (\Exception $e) {
            $this->error('✗ Logging failed: ' . $e->getMessage());
        }
        
        $this->info('All tests completed successfully!');
        return 0;
    }
}