<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Country;
use App\Models\Region;
use App\Models\City;
use App\Models\Destination;
use App\Models\DestinationFaq;
use App\Models\DestinationFishChart;
use App\Models\DestinationFishSizeLimit;
use App\Models\DestinationFishTimeLimit;

class FixDestinationRelationships extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'destinations:fix-relationships
                            {--dry-run : Analyze without making changes}
                            {--analyze-only : Only show analysis, no fixes}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix relationships between destination-related tables and new City/Region/Country structure';

    /**
     * Mapping arrays
     */
    protected $idMapping = [];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $analyzeOnly = $this->option('analyze-only');

        $this->info('🔍 Analyzing Destination Relationships...');
        $this->newLine();

        // Step 1: Check if old destinations table exists
        $hasOldTable = Schema::hasTable('destinations');
        
        if ($hasOldTable) {
            $this->info('✓ Old "destinations" table found');
            $this->buildMappingFromOldTable();
        } else {
            $this->warn('⚠️  Old "destinations" table not found. Attempting to infer relationships...');
            $this->buildMappingFromCurrentData();
        }

        $this->newLine();

        // Step 2: Analyze current state
        $this->analyzeCurrentState();

        if ($analyzeOnly) {
            return 0;
        }

        $this->newLine();

        // Step 3: Fix relationships
        if ($isDryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be made');
        } else {
            $this->warn('⚠️  This will modify your database!');
            if (!$this->option('force') && !$this->confirm('Do you want to continue?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->newLine();
        $this->fixRelationships($isDryRun);

        $this->newLine();
        $this->info('✅ Done!');

        return 0;
    }

    /**
     * Build ID mapping from old destinations table
     */
    protected function buildMappingFromOldTable()
    {
        $this->info('Building ID mapping from old destinations table...');

        $oldDestinations = DB::table('destinations')->get();

        foreach ($oldDestinations as $dest) {
            $type = $dest->type ?? null;
            $oldId = $dest->id;
            $name = $dest->name ?? '';
            $slug = $dest->slug ?? '';

            if (!$type) {
                continue;
            }

            // Try to find the corresponding record in new tables
            if ($type === 'country') {
                // Try by slug first, then by name, then by ID
                $newRecord = Country::where('slug', $slug)->first();
                if (!$newRecord && $name) {
                    $newRecord = Country::where('name', $name)->first();
                }
                if (!$newRecord) {
                    $newRecord = Country::find($oldId);
                }
                
                if ($newRecord) {
                    $this->idMapping[$oldId] = [
                        'new_id' => $newRecord->id,
                        'type' => 'country',
                        'name' => $name
                    ];
                }
            } elseif ($type === 'region') {
                // Try by slug first, then by name, then by ID
                $newRecord = Region::where('slug', $slug)->first();
                if (!$newRecord && $name) {
                    $newRecord = Region::where('name', $name)->first();
                }
                if (!$newRecord) {
                    $newRecord = Region::find($oldId);
                }
                
                if ($newRecord) {
                    $this->idMapping[$oldId] = [
                        'new_id' => $newRecord->id,
                        'type' => 'region',
                        'name' => $name
                    ];
                }
            } elseif ($type === 'city') {
                // Try by slug first, then by name, then by ID
                $newRecord = City::where('slug', $slug)->first();
                if (!$newRecord && $name) {
                    $newRecord = City::where('name', $name)->first();
                }
                if (!$newRecord) {
                    $newRecord = City::find($oldId);
                }
                
                if ($newRecord) {
                    $this->idMapping[$oldId] = [
                        'new_id' => $newRecord->id,
                        'type' => 'city',
                        'name' => $name
                    ];
                }
            }
        }

        $this->info('   ✓ Found ' . count($this->idMapping) . ' mappings');
        
        // Also infer any remaining unmapped IDs from current data
        $this->buildMappingFromCurrentData();
    }

    /**
     * Build mapping by inferring from current data
     */
    protected function buildMappingFromCurrentData()
    {
        $this->info('Inferring relationships from current data...');

        // Get all unique destination IDs from related tables
        $faqIds = DB::table('destination_faqs')->pluck('destination_id')->unique();
        $chartIds = DB::table('destination_fish_charts')->pluck('destination_id')->unique();
        $sizeIds = DB::table('destination_fish_size_limits')->pluck('destination_id')->unique();
        $timeIds = DB::table('destination_fish_time_limits')->pluck('destination_id')->unique();

        $allIds = $faqIds->merge($chartIds)->merge($sizeIds)->merge($timeIds)->unique();

        foreach ($allIds as $id) {
            // Skip if already mapped
            if (isset($this->idMapping[$id])) {
                continue;
            }

            // Check if ID exists in new tables
            $country = Country::find($id);
            $region = Region::find($id);
            $city = City::find($id);

            if ($country) {
                $this->idMapping[$id] = [
                    'new_id' => $country->id,
                    'type' => 'country',
                    'name' => $country->name
                ];
            } elseif ($region) {
                $this->idMapping[$id] = [
                    'new_id' => $region->id,
                    'type' => 'region',
                    'name' => $region->name
                ];
            } elseif ($city) {
                $this->idMapping[$id] = [
                    'new_id' => $city->id,
                    'type' => 'city',
                    'name' => $city->name
                ];
            }
        }

        $this->info('   ✓ Inferred ' . count($this->idMapping) . ' mappings');
    }

    /**
     * Analyze current state of data
     */
    protected function analyzeCurrentState()
    {
        $this->info('📊 Current State Analysis:');
        $this->newLine();

        $tables = [
            'destination_faqs' => 'FAQs',
            'destination_fish_charts' => 'Fish Charts',
            'destination_fish_size_limits' => 'Fish Size Limits',
            'destination_fish_time_limits' => 'Fish Time Limits'
        ];

        foreach ($tables as $table => $label) {
            $this->line("  {$label} ({$table}):");
            
            $total = DB::table($table)->count();
            $withType = DB::table($table)->whereNotNull('destination_type')->count();
            $withoutType = $total - $withType;
            
            $this->line("    • Total records: {$total}");
            $this->line("    • With destination_type: {$withType}");
            $this->line("    • Missing destination_type: {$withoutType}");

            // Check how many have valid mappings
            $destinationIds = DB::table($table)->pluck('destination_id')->unique();
            $validMappings = 0;
            $invalidMappings = 0;

            foreach ($destinationIds as $destId) {
                if (isset($this->idMapping[$destId])) {
                    $validMappings++;
                } else {
                    $invalidMappings++;
                }
            }

            $this->line("    • Unique destination_ids: " . $destinationIds->count());
            $this->line("    • With valid mapping: {$validMappings}");
            
            if ($invalidMappings > 0) {
                $this->warn("    ⚠️  Without valid mapping: {$invalidMappings}");
                
                // Show which IDs don't have mappings
                $unmappedIds = [];
                foreach ($destinationIds as $destId) {
                    if (!isset($this->idMapping[$destId])) {
                        $unmappedIds[] = $destId;
                    }
                }
                if (count($unmappedIds) <= 10) {
                    $this->line("       Unmapped IDs: " . implode(', ', $unmappedIds));
                    
                    // Show details from old table if it exists
                    if (Schema::hasTable('destinations')) {
                        foreach ($unmappedIds as $unmappedId) {
                            $oldDest = DB::table('destinations')->where('id', $unmappedId)->first();
                            if ($oldDest) {
                                $this->line("         ID {$unmappedId}: {$oldDest->name} (type: {$oldDest->type}, slug: {$oldDest->slug})");
                            } else {
                                $this->line("         ID {$unmappedId}: Not found in destinations table");
                            }
                        }
                    }
                }
            }

            $this->newLine();
        }
    }

    /**
     * Fix all relationships
     */
    protected function fixRelationships($isDryRun)
    {
        $this->info('🔧 Fixing Relationships...');
        $this->newLine();

        $this->fixTableRelationships('destination_faqs', 'FAQs', $isDryRun);
        $this->fixTableRelationships('destination_fish_charts', 'Fish Charts', $isDryRun);
        $this->fixTableRelationships('destination_fish_size_limits', 'Fish Size Limits', $isDryRun);
        $this->fixTableRelationships('destination_fish_time_limits', 'Fish Time Limits', $isDryRun);
    }

    /**
     * Fix relationships for a specific table
     */
    protected function fixTableRelationships($tableName, $label, $isDryRun)
    {
        $this->info("Fixing {$label}...");

        $records = DB::table($tableName)->get();
        $updated = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($records as $record) {
            $oldDestId = $record->destination_id;
            $currentType = $record->destination_type ?? null;

            if (!isset($this->idMapping[$oldDestId])) {
                $skipped++;
                continue;
            }

            $mapping = $this->idMapping[$oldDestId];
            $needsUpdate = false;

            // Check if destination_type needs updating
            if ($currentType !== $mapping['type']) {
                $needsUpdate = true;
            }

            // Check if destination_id needs updating (if old table existed and IDs changed)
            if ($oldDestId != $mapping['new_id']) {
                $needsUpdate = true;
            }

            if ($needsUpdate) {
                if (!$isDryRun) {
                    try {
                        DB::table($tableName)
                            ->where('id', $record->id)
                            ->update([
                                'destination_id' => $mapping['new_id'],
                                'destination_type' => $mapping['type']
                            ]);
                        $updated++;
                    } catch (\Exception $e) {
                        $this->error("   Error updating record {$record->id}: " . $e->getMessage());
                        $errors++;
                    }
                } else {
                    $updated++;
                }
            }
        }

        $mode = $isDryRun ? ' (dry run)' : '';
        $this->info("   ✓ Updated: {$updated}{$mode}");
        
        if ($skipped > 0) {
            $this->warn("   ⚠️  Skipped (no mapping): {$skipped}");
        }
        
        if ($errors > 0) {
            $this->error("   ✗ Errors: {$errors}");
        }

        $this->newLine();
    }
}
