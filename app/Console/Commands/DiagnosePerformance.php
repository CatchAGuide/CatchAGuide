<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Guiding;
use App\Services\GuidingFilterService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class DiagnosePerformance extends Command
{
    protected $signature = 'performance:diagnose';
    protected $description = 'Diagnose performance issues with guidings listing';

    public function handle()
    {
        $this->info('🔍 Diagnosing Performance Issues...');
        $this->newLine();

        // 1. Check database indexes
        $this->checkDatabaseIndexes();
        
        // 2. Check filter cache
        $this->checkFilterCache();
        
        // 3. Check basic query performance
        $this->checkQueryPerformance();
        
        // 4. Check environment
        $this->checkEnvironment();
        
        $this->newLine();
        $this->info('✅ Diagnosis complete!');
    }

    private function checkDatabaseIndexes()
    {
        $this->info('📊 Checking Database Indexes...');
        
        $expectedIndexes = [
            'idx_guidings_status_city',
            'idx_guidings_status_country',
            'idx_guidings_status_region',
            'idx_guidings_lat_lng',
            'idx_guidings_status_price'
        ];
        
        $existingIndexes = DB::select("SHOW INDEX FROM guidings");
        $indexNames = collect($existingIndexes)->pluck('Key_name')->unique()->toArray();
        
        foreach ($expectedIndexes as $expectedIndex) {
            if (in_array($expectedIndex, $indexNames)) {
                $this->line("  ✅ {$expectedIndex} - EXISTS");
            } else {
                $this->error("  ❌ {$expectedIndex} - MISSING");
            }
        }
        
        $this->line("  📈 Total indexes on guidings table: " . count($indexNames));
        $this->newLine();
    }

    private function checkFilterCache()
    {
        $this->info('💾 Checking Filter Cache...');
        
        // Check if filter file exists
        if (Storage::disk('local')->exists('cache/guiding-filters.json')) {
            $fileSize = Storage::disk('local')->size('cache/guiding-filters.json');
            $this->line("  ✅ Filter file exists ({$fileSize} bytes)");
            
            // Check file age
            $lastModified = Storage::disk('local')->lastModified('cache/guiding-filters.json');
            $age = now()->timestamp - $lastModified;
            $this->line("  📅 File age: " . gmdate('H:i:s', $age) . " (hours:minutes:seconds)");
        } else {
            $this->error("  ❌ Filter file missing!");
        }
        
        // Check cache
        if (Cache::has('guiding_filter_data')) {
            $this->line("  ✅ Filter data cached in memory");
        } else {
            $this->line("  ⚠️  Filter data not in memory cache");
        }
        
        $this->newLine();
    }

    private function checkQueryPerformance()
    {
        $this->info('⚡ Testing Query Performance...');
        
        // Test basic count query
        $start = microtime(true);
        $count = Guiding::where('status', 1)->count();
        $basicTime = round((microtime(true) - $start) * 1000, 2);
        $this->line("  📊 Basic count query: {$basicTime}ms ({$count} guidings)");
        
        // Test with eager loading
        $start = microtime(true);
        $guidings = Guiding::with(['boatType'])->where('status', 1)->limit(20)->get();
        $eagerTime = round((microtime(true) - $start) * 1000, 2);
        $this->line("  🔗 With eager loading (20 records): {$eagerTime}ms");
        
        // Test location filter
        $start = microtime(true);
        $locationResult = Guiding::locationFilter('Berlin', 'Deutschland', null, 50, null, null);
        $locationTime = round((microtime(true) - $start) * 1000, 2);
        $this->line("  🗺️  Location filter: {$locationTime}ms (" . count($locationResult['ids']) . " results)");
        
        // Test filter service
        $start = microtime(true);
        $filterService = new GuidingFilterService();
        $filterCounts = $filterService->getFilterCounts();
        $filterTime = round((microtime(true) - $start) * 1000, 2);
        $this->line("  🔧 Filter service: {$filterTime}ms");
        
        $this->newLine();
    }

    private function checkEnvironment()
    {
        $this->info('🌍 Environment Information...');
        
        $this->line("  🐘 PHP Version: " . PHP_VERSION);
        $this->line("  🗄️  Database: " . DB::connection()->getPdo()->getAttribute(\PDO::ATTR_SERVER_VERSION));
        $this->line("  💾 Memory Limit: " . ini_get('memory_limit'));
        $this->line("  ⏱️  Max Execution Time: " . ini_get('max_execution_time') . "s");
        $this->line("  🚀 OPcache Enabled: " . (function_exists('opcache_get_status') && opcache_get_status() ? 'Yes' : 'No'));
        
        // Check if we're in production
        $this->line("  🏗️  Environment: " . app()->environment());
        $this->line("  🐛 Debug Mode: " . (config('app.debug') ? 'ON' : 'OFF'));
        
        $this->newLine();
    }
} 