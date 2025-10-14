<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Destination;
use App\Models\Country;
use App\Models\CountryTranslation;
use App\Models\Region;
use App\Models\RegionTranslation;
use App\Models\City;
use App\Models\CityTranslation;
use App\Models\DestinationFaq;
use App\Models\DestinationFishChart;
use App\Models\DestinationFishSizeLimit;
use App\Models\DestinationFishTimeLimit;
use Illuminate\Support\Facades\DB;

class MigrateDestinationsData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'destinations:migrate
                            {--dry-run : Run without making changes}
                            {--type= : Only migrate specific type (country, region, city)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate data from old destinations table to new Country/Region/City structure';

    /**
     * Mapping of old destination IDs to new model IDs
     */
    protected $countryIdMap = [];
    protected $regionIdMap = [];
    protected $cityIdMap = [];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🚀 Starting Destination Data Migration...');
        $this->newLine();

        $isDryRun = $this->option('dry-run');
        $type = $this->option('type');

        if ($isDryRun) {
            $this->warn('⚠️  DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        // Check if new tables exist
        if (!$this->checkTablesExist()) {
            $this->error('❌ New tables do not exist. Please run migrations first: php artisan migrate');
            return 1;
        }

        // Note: We don't use DB::transaction() because DDL statements (ALTER TABLE) 
        // cause implicit commits in MySQL, which would break the transaction
        try {
            // Migrate in order due to dependencies
            if (!$type || $type === 'country') {
                $this->migrateCountries($isDryRun);
            }

            if (!$type || $type === 'region') {
                $this->migrateRegions($isDryRun);
            }

            if (!$type || $type === 'city') {
                $this->migrateCities($isDryRun);
            }

            // Migrate related data
            if (!$type) {
                // Drop foreign key constraints before updating
                if (!$isDryRun) {
                    $this->dropForeignKeyConstraints();
                }
                
                $this->migrateFaqs($isDryRun);
                $this->migrateFishCharts($isDryRun);
                $this->migrateFishLimits($isDryRun);
            }

            $this->newLine();
            if ($isDryRun) {
                $this->info('✅ Dry run completed. No changes were made.');
            } else {
                $this->info('✅ Migration completed successfully!');
            }

        } catch (\Exception $e) {
            $this->error('❌ Migration failed: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        } finally {
            $this->newLine();
            $this->displaySummary();
        }

        return 0;
    }

    /**
     * Check if new tables exist
     */
    protected function checkTablesExist(): bool
    {
        $tables = ['c_countries', 'c_country_translations', 'c_regions', 'c_region_translations', 'c_cities', 'c_city_translations'];
        
        foreach ($tables as $table) {
            if (!DB::getSchemaBuilder()->hasTable($table)) {
                $this->error("Table '{$table}' does not exist");
                return false;
            }
        }
        
        return true;
    }

    /**
     * Migrate countries
     */
    protected function migrateCountries($isDryRun)
    {
        $this->info('📍 Migrating Countries...');

        // Get all unique country slugs
        $countrySlugs = Destination::whereType('country')
            ->select('slug')
            ->groupBy('slug')
            ->pluck('slug');

        $bar = $this->output->createProgressBar(count($countrySlugs));

        foreach ($countrySlugs as $slug) {
            // Get all language variants for this country slug
            // Use groupBy to handle duplicates (take first occurrence of each language)
            $variants = Destination::whereType('country')
                ->where('slug', $slug)
                ->get()
                ->groupBy('language')
                ->map(function ($group) {
                    if ($group->count() > 1) {
                        $this->warn("   ⚠️  Found duplicate language '{$group->first()->language}' for slug '{$group->first()->slug}' - using ID: {$group->first()->id}");
                    }
                    return $group->first(); // Take the first occurrence
                })
                ->values();
            
            // Use the first variant as the base data source
            $baseCountry = $variants->first();

            if (!$isDryRun) {
                // Create base country record
                $country = Country::create([
                    'name' => $baseCountry->name,
                    'slug' => $baseCountry->slug,
                    'countrycode' => $baseCountry->countrycode,
                    'filters' => $baseCountry->filters,
                    'thumbnail_path' => $baseCountry->thumbnail_path,
                ]);

                // Create translation for each language variant
                foreach ($variants as $variant) {
                    // Map old destination ID to new country ID
                    $this->countryIdMap[$variant->id] = $country->id;

                    CountryTranslation::create([
                        'country_id' => $country->id,
                        'language' => $variant->language ?? 'de',
                        'title' => $variant->title,
                        'sub_title' => $variant->sub_title,
                        'introduction' => $variant->introduction,
                        'content' => $variant->content,
                        'fish_avail_title' => $variant->fish_avail_title,
                        'fish_avail_intro' => $variant->fish_avail_intro,
                        'size_limit_title' => $variant->size_limit_title,
                        'size_limit_intro' => $variant->size_limit_intro,
                        'time_limit_title' => $variant->time_limit_title,
                        'time_limit_intro' => $variant->time_limit_intro,
                        'faq_title' => $variant->faq_title,
                    ]);
                }
            } else {
                // In dry run, just collect the IDs
                foreach ($variants as $variant) {
                    $this->countryIdMap[$variant->id] = 'DRY_RUN_' . $variant->id;
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("   ✓ Migrated " . count($countrySlugs) . " countries");
    }

    /**
     * Migrate regions
     */
    protected function migrateRegions($isDryRun)
    {
        $this->info('📍 Migrating Regions...');

        // Get all unique region slugs with country_id
        $regionGroups = Destination::whereType('region')
            ->select('slug', 'country_id')
            ->groupBy('slug', 'country_id')
            ->get();

        $bar = $this->output->createProgressBar(count($regionGroups));

        foreach ($regionGroups as $group) {
            // Get all language variants for this region
            // Use groupBy to handle duplicates (take first occurrence of each language)
            $variants = Destination::whereType('region')
                ->where('slug', $group->slug)
                ->where('country_id', $group->country_id)
                ->get()
                ->groupBy('language')
                ->map(function ($langGroup) {
                    if ($langGroup->count() > 1) {
                        $this->warn("   ⚠️  Found duplicate language '{$langGroup->first()->language}' for region '{$langGroup->first()->slug}' - using ID: {$langGroup->first()->id}");
                    }
                    return $langGroup->first();
                })
                ->values();
            
            // Use the first variant as the base data source
            $baseRegion = $variants->first();

            if (!$isDryRun) {
                // Get the new country ID from our mapping
                $newCountryId = $this->countryIdMap[$baseRegion->country_id] ?? null;

                if (!$newCountryId) {
                    $this->warn("   ⚠️  Skipping region '{$baseRegion->name}' - country not found");
                    continue;
                }

                // Create base region record
                $region = Region::create([
                    'country_id' => $newCountryId,
                    'name' => $baseRegion->name,
                    'slug' => $baseRegion->slug,
                    'filters' => $baseRegion->filters,
                    'thumbnail_path' => $baseRegion->thumbnail_path,
                ]);

                // Create translation for each language variant
                foreach ($variants as $variant) {
                    // Map old destination ID to new region ID
                    $this->regionIdMap[$variant->id] = $region->id;

                    RegionTranslation::create([
                        'region_id' => $region->id,
                        'language' => $variant->language ?? 'de',
                        'title' => $variant->title,
                        'sub_title' => $variant->sub_title,
                        'introduction' => $variant->introduction,
                        'content' => $variant->content,
                        'fish_avail_title' => $variant->fish_avail_title,
                        'fish_avail_intro' => $variant->fish_avail_intro,
                        'size_limit_title' => $variant->size_limit_title,
                        'size_limit_intro' => $variant->size_limit_intro,
                        'time_limit_title' => $variant->time_limit_title,
                        'time_limit_intro' => $variant->time_limit_intro,
                        'faq_title' => $variant->faq_title,
                    ]);
                }
            } else {
                foreach ($variants as $variant) {
                    $this->regionIdMap[$variant->id] = 'DRY_RUN_' . $variant->id;
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("   ✓ Migrated " . count($regionGroups) . " regions");
    }

    /**
     * Migrate cities
     */
    protected function migrateCities($isDryRun)
    {
        $this->info('📍 Migrating Cities...');

        // Get all unique city slugs with country_id
        $cityGroups = Destination::whereType('city')
            ->select('slug', 'country_id', 'region_id')
            ->groupBy('slug', 'country_id', 'region_id')
            ->get();

        $bar = $this->output->createProgressBar(count($cityGroups));

        foreach ($cityGroups as $group) {
            // Get all language variants for this city
            // Use groupBy to handle duplicates (take first occurrence of each language)
            $variants = Destination::whereType('city')
                ->where('slug', $group->slug)
                ->where('country_id', $group->country_id)
                ->get()
                ->groupBy('language')
                ->map(function ($langGroup) {
                    if ($langGroup->count() > 1) {
                        $this->warn("   ⚠️  Found duplicate language '{$langGroup->first()->language}' for city '{$langGroup->first()->slug}' - using ID: {$langGroup->first()->id}");
                    }
                    return $langGroup->first();
                })
                ->values();
            
            // Use the first variant as the base data source
            $baseCity = $variants->first();

            if (!$isDryRun) {
                // Get the new country and region IDs from our mapping
                $newCountryId = $this->countryIdMap[$baseCity->country_id] ?? null;
                $newRegionId = $baseCity->region_id ? ($this->regionIdMap[$baseCity->region_id] ?? null) : null;

                if (!$newCountryId) {
                    $this->warn("   ⚠️  Skipping city '{$baseCity->name}' - country not found");
                    continue;
                }

                // Create base city record
                $city = City::create([
                    'country_id' => $newCountryId,
                    'region_id' => $newRegionId,
                    'name' => $baseCity->name,
                    'slug' => $baseCity->slug,
                    'filters' => $baseCity->filters,
                    'thumbnail_path' => $baseCity->thumbnail_path,
                ]);

                // Create translation for each language variant
                foreach ($variants as $variant) {
                    // Map old destination ID to new city ID
                    $this->cityIdMap[$variant->id] = $city->id;

                    CityTranslation::create([
                        'city_id' => $city->id,
                        'language' => $variant->language ?? 'de',
                        'title' => $variant->title,
                        'sub_title' => $variant->sub_title,
                        'introduction' => $variant->introduction,
                        'content' => $variant->content,
                        'fish_avail_title' => $variant->fish_avail_title,
                        'fish_avail_intro' => $variant->fish_avail_intro,
                        'size_limit_title' => $variant->size_limit_title,
                        'size_limit_intro' => $variant->size_limit_intro,
                        'time_limit_title' => $variant->time_limit_title,
                        'time_limit_intro' => $variant->time_limit_intro,
                        'faq_title' => $variant->faq_title,
                    ]);
                }
            } else {
                foreach ($variants as $variant) {
                    $this->cityIdMap[$variant->id] = 'DRY_RUN_' . $variant->id;
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("   ✓ Migrated " . count($cityGroups) . " cities");
    }

    /**
     * Drop foreign key constraints on related tables and add destination_type column
     */
    protected function dropForeignKeyConstraints()
    {
        $this->info('🔓 Preparing related tables...');
        
        // Check and drop foreign keys if they exist
        $tables = [
            'destination_faqs' => 'destination_faqs_destination_id_foreign',
            'destination_fish_charts' => 'destination_fish_charts_destination_id_foreign',
            'destination_fish_size_limits' => 'destination_fish_size_limits_destination_id_foreign',
            'destination_fish_time_limits' => 'destination_fish_time_limits_destination_id_foreign'
        ];
        
        foreach ($tables as $table => $constraint) {
            try {
                DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$constraint}`");
            } catch (\Exception $e) {
                // Constraint might not exist, continue
            }
            
            // Add destination_type column if it doesn't exist
            if (!DB::getSchemaBuilder()->hasColumn($table, 'destination_type')) {
                DB::statement("ALTER TABLE `{$table}` ADD COLUMN `destination_type` VARCHAR(50) NULL AFTER `destination_id`");
            }
        }
        
        $this->info('   ✓ Related tables prepared');
    }

    /**
     * Migrate FAQs (update destination_id and add destination_type)
     */
    protected function migrateFaqs($isDryRun)
    {
        $this->info('📝 Updating FAQs...');

        $faqs = DestinationFaq::all();
        $updated = 0;

        foreach ($faqs as $faq) {
            $oldDestId = $faq->destination_id;
            
            // Determine which map to use based on the old destination
            if (isset($this->countryIdMap[$oldDestId])) {
                if (!$isDryRun) {
                    $faq->update([
                        'destination_id' => $this->countryIdMap[$oldDestId],
                        'destination_type' => 'country'
                    ]);
                }
                $updated++;
            } elseif (isset($this->regionIdMap[$oldDestId])) {
                if (!$isDryRun) {
                    $faq->update([
                        'destination_id' => $this->regionIdMap[$oldDestId],
                        'destination_type' => 'region'
                    ]);
                }
                $updated++;
            } elseif (isset($this->cityIdMap[$oldDestId])) {
                if (!$isDryRun) {
                    $faq->update([
                        'destination_id' => $this->cityIdMap[$oldDestId],
                        'destination_type' => 'city'
                    ]);
                }
                $updated++;
            }
        }

        $this->info("   ✓ Updated {$updated} FAQs");
    }

    /**
     * Migrate fish charts
     */
    protected function migrateFishCharts($isDryRun)
    {
        $this->info('🐟 Updating Fish Charts...');

        $charts = DestinationFishChart::all();
        $updated = 0;

        foreach ($charts as $chart) {
            $oldDestId = $chart->destination_id;
            $newId = $this->countryIdMap[$oldDestId] 
                ?? $this->regionIdMap[$oldDestId] 
                ?? $this->cityIdMap[$oldDestId] 
                ?? null;

            if ($newId && !$isDryRun) {
                $chart->update(['destination_id' => $newId]);
                $updated++;
            } elseif ($newId) {
                $updated++;
            }
        }

        $this->info("   ✓ Updated {$updated} fish charts");
    }

    /**
     * Migrate fish limits
     */
    protected function migrateFishLimits($isDryRun)
    {
        $this->info('📏 Updating Fish Limits...');

        // Size limits
        $sizeLimits = DestinationFishSizeLimit::all();
        $updated = 0;

        foreach ($sizeLimits as $limit) {
            $oldDestId = $limit->destination_id;
            $newId = $this->countryIdMap[$oldDestId] 
                ?? $this->regionIdMap[$oldDestId] 
                ?? $this->cityIdMap[$oldDestId] 
                ?? null;

            if ($newId && !$isDryRun) {
                $limit->update(['destination_id' => $newId]);
                $updated++;
            } elseif ($newId) {
                $updated++;
            }
        }

        // Time limits
        $timeLimits = DestinationFishTimeLimit::all();
        foreach ($timeLimits as $limit) {
            $oldDestId = $limit->destination_id;
            $newId = $this->countryIdMap[$oldDestId] 
                ?? $this->regionIdMap[$oldDestId] 
                ?? $this->cityIdMap[$oldDestId] 
                ?? null;

            if ($newId && !$isDryRun) {
                $limit->update(['destination_id' => $newId]);
                $updated++;
            } elseif ($newId) {
                $updated++;
            }
        }

        $this->info("   ✓ Updated {$updated} fish limits");
    }

    /**
     * Display migration summary
     */
    protected function displaySummary()
    {
        $this->info('📊 Migration Summary:');
        $this->info('   Countries migrated: ' . count(array_unique($this->countryIdMap)));
        $this->info('   Regions migrated: ' . count(array_unique($this->regionIdMap)));
        $this->info('   Cities migrated: ' . count(array_unique($this->cityIdMap)));
    }
}
