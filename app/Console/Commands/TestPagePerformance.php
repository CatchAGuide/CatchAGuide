<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Guiding;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use App\Http\Controllers\GuidingsController;

class TestPagePerformance extends Command
{
    protected $signature = 'test:page-performance';
    protected $description = 'Test performance of different parts of the guidings page';

    public function handle()
    {
        $this->info('🔍 Testing Page Performance...');
        $this->newLine();

        // Test 1: Controller performance
        $this->info('1. Testing Controller Performance...');
        $start = microtime(true);
        
        $request = new Request();
        $controller = new GuidingsController();
        
        try {
            $response = $controller->index($request);
            $controllerTime = round((microtime(true) - $start) * 1000, 2);
            $this->line("   ✅ Controller execution: {$controllerTime}ms");
        } catch (\Exception $e) {
            $this->error("   ❌ Controller failed: " . $e->getMessage());
            return;
        }

        // Test 2: Helper function performance
        $this->info('2. Testing Helper Functions...');
        
        $guidings = Guiding::with(['boatType'])->where('status', 1)->limit(20)->get();
        
        // Test get_galleries_image_link
        $start = microtime(true);
        foreach ($guidings as $guiding) {
            get_galleries_image_link($guiding);
        }
        $helperTime = round((microtime(true) - $start) * 1000, 2);
        $this->line("   📸 get_galleries_image_link (20 guidings): {$helperTime}ms");

        // Test translate function
        $start = microtime(true);
        for ($i = 0; $i < 50; $i++) {
            translate('Test string ' . $i);
        }
        $translateTime = round((microtime(true) - $start) * 1000, 2);
        $this->line("   🌐 translate function (50 calls): {$translateTime}ms");

        // Test 3: View rendering performance
        $this->info('3. Testing View Rendering...');
        
        $viewData = [
            'guidings' => $guidings->take(5), // Test with fewer items
            'allGuidings' => $guidings->take(5),
            'otherguidings' => collect(),
            'targetFishOptions' => collect(),
            'methodOptions' => collect(),
            'waterTypeOptions' => collect(),
            'alltargets' => collect(),
            'guiding_waters' => collect(),
            'guiding_methods' => collect(),
            'title' => 'Test',
            'filter_title' => 'Test',
            'searchMessage' => '',
            'destination' => null,
            'targetFishCounts' => [],
            'methodCounts' => [],
            'waterTypeCounts' => [],
            'durationCounts' => [],
            'personCounts' => [],
            'isMobile' => false,
            'total' => 5,
            'filterCounts' => [],
            'maxPrice' => 1000,
            'overallMaxPrice' => 1000,
            'agent' => (object)['ismobile' => function() { return false; }]
        ];

        $start = microtime(true);
        try {
            $html = View::make('pages.guidings.index', $viewData)->render();
            $viewTime = round((microtime(true) - $start) * 1000, 2);
            $this->line("   🎨 View rendering (5 guidings): {$viewTime}ms");
            $this->line("   📄 HTML size: " . number_format(strlen($html)) . " characters");
        } catch (\Exception $e) {
            $this->error("   ❌ View rendering failed: " . $e->getMessage());
        }

        $this->newLine();
        $this->info('✅ Performance test complete!');
        
        // Recommendations
        $this->newLine();
        $this->info('💡 Recommendations:');
        if ($controllerTime > 1000) {
            $this->line('   - Controller is slow (>1s). Check database queries.');
        }
        if ($helperTime > 100) {
            $this->line('   - Helper functions are slow. Consider caching image paths.');
        }
        if ($viewTime > 500) {
            $this->line('   - View rendering is slow. Consider reducing complexity.');
        }
    }
} 