<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

class FixDestinationsMigration extends Command
{
    protected $signature = 'destinations:fix-migration 
                            {--rollback : Rollback new tables to clean state}
                            {--migrate : Run the corrected migration}
                            {--dry-run : Preview changes without making them}';

    protected $description = 'Fix the destinations migration issues - properly group translations and link related data';

    protected $countryIdMap = [];
    protected $regionIdMap = [];
    protected $cityIdMap = [];

    public function handle()
    {
        $this->info('═══════════════════════════════════════════════════════════════');
        $this->info('   DESTINATIONS MIGRATION FIX');
        $this->info('═══════════════════════════════════════════════════════════════');
        $this->newLine();

        $rollback = $this->option('rollback');
        $migrate = $this->option('migrate');
        $isDryRun = $this->option('dry-run');

        if (!$rollback && !$migrate) {
            $this->error('Please specify --rollback or --migrate (or both)');
            return 1;
        }

        if ($isDryRun) {
            $this->warn('⚠️  DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        if ($rollback) {
            if (!$isDryRun && !$this->confirm('⚠️  This will DELETE all data from new tables. Continue?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
            $this->rollbackNewTables($isDryRun);
        }

        if ($migrate) {
            $this->runCorrectedMigration($isDryRun);
        }

        return 0;
    }

    protected function rollbackNewTables($isDryRun)
    {
        $this->info('🔄 ROLLING BACK NEW TABLES...');
        $this->info('─────────────────────────────────────────────────────────────');

        if (!$isDryRun) {
            DB::beginTransaction();
            try {
                // Truncate in reverse order to respect foreign keys
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
                
                DB::table('c_city_translations')->truncate();
                $this->info('  ✓ Cleared c_city_translations');
                
                DB::table('c_cities')->truncate();
                $this->info('  ✓ Cleared c_cities');
                
                DB::table('c_region_translations')->truncate();
                $this->info('  ✓ Cleared c_region_translations');
                
                DB::table('c_regions')->truncate();
                $this->info('  ✓ Cleared c_regions');
                
                DB::table('c_country_translations')->truncate();
                $this->info('  ✓ Cleared c_country_translations');
                
                DB::table('c_countries')->truncate();
                $this->info('  ✓ Cleared c_countries');
                
                // Reset destination_type in related tables
                DB::table('destination_faqs')->update(['destination_type' => null]);
                DB::table('destination_fish_charts')->update(['destination_type' => null]);
                DB::table('destination_fish_size_limits')->update(['destination_type' => null]);
                DB::table('destination_fish_time_limits')->update(['destination_type' => null]);
                $this->info('  ✓ Reset destination_type in related tables');
                
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
                
                DB::commit();
                $this->newLine();
                $this->info('✅ Rollback completed successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
                $this->error('❌ Rollback failed: ' . $e->getMessage());
                return;
            }
        } else {
            $this->info('  [DRY RUN] Would truncate all new tables');
        }
        $this->newLine();
    }

    protected function runCorrectedMigration($isDryRun)
    {
        $this->info('🚀 RUNNING CORRECTED MIGRATION...');
        $this->info('─────────────────────────────────────────────────────────────');
        $this->newLine();

        DB::beginTransaction();

        try {
            $this->migrateCountriesFixed($isDryRun);
            $this->migrateRegionsFixed($isDryRun);
            $this->migrateCitiesFixed($isDryRun);
            
            // Update related tables
            $this->updateRelatedTables($isDryRun);

            if ($isDryRun) {
                DB::rollBack();
                $this->newLine();
                $this->info('✅ Dry run completed. No changes were made.');
            } else {
                DB::commit();
                $this->newLine();
                $this->info('✅ Migration completed successfully!');
            }

            $this->displaySummary();
            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Migration failed: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }
    }

    protected function migrateCountriesFixed($isDryRun)
    {
        $this->info('📍 Migrating Countries (FIXED)...');

        // Get all countries and group by normalized name
        $countries = Destination::where('type', 'country')->get();
        
        // Group by base name (normalize Finland/Finnland, etc.)
        $countryGroups = $this->groupCountriesByBaseName($countries);

        $bar = $this->output->createProgressBar(count($countryGroups));

        foreach ($countryGroups as $baseName => $variants) {
            // Use the first variant as base, prefer EN if available
            $baseCountry = $variants->firstWhere('language', 'en') ?? $variants->first();

            if (!$isDryRun) {
                $country = Country::create([
                    'name' => $baseCountry->name,
                    'slug' => $this->generateUniqueSlug($baseCountry),
                    'countrycode' => $baseCountry->countrycode,
                    'filters' => $baseCountry->filters,
                    'thumbnail_path' => $baseCountry->thumbnail_path,
                ]);

                // Create translation for each language variant
                foreach ($variants as $variant) {
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
                foreach ($variants as $variant) {
                    $this->countryIdMap[$variant->id] = 'DRY_' . $variant->id;
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("   ✓ Migrated " . count($countryGroups) . " countries with " . $countries->count() . " translations");
    }

    protected function groupCountriesByBaseName($countries)
    {
        $groups = [];
        $nameMapping = [
            'finland' => ['finland', 'finnland'],
            'germany' => ['germany', 'deutschland'],
            'austria' => ['austria', 'österreich', 'oesterreich'],
            'switzerland' => ['switzerland', 'schweiz'],
            'netherlands' => ['netherlands', 'niederlande'],
            'denmark' => ['denmark', 'dänemark', 'daenemark'],
            'sweden' => ['sweden', 'schweden'],
            'norway' => ['norway', 'norwegen'],
            'poland' => ['poland', 'polen'],
            'spain' => ['spain', 'spanien'],
            'france' => ['france', 'frankreich'],
        ];

        foreach ($countries as $country) {
            $normalized = strtolower($country->name);
            $normalized = str_replace(['ä', 'ö', 'ü', 'ß'], ['ae', 'oe', 'ue', 'ss'], $normalized);
            
            $baseName = $normalized;
            foreach ($nameMapping as $base => $variants) {
                if (in_array($normalized, $variants)) {
                    $baseName = $base;
                    break;
                }
            }

            if (!isset($groups[$baseName])) {
                $groups[$baseName] = collect();
            }
            $groups[$baseName]->push($country);
        }

        return $groups;
    }

    protected function generateUniqueSlug($destination)
    {
        // Normalize to English base name for slug
        $nameMapping = [
            'finnland' => 'finland',
            'deutschland' => 'germany',
            'österreich' => 'austria',
            'oesterreich' => 'austria',
            'schweiz' => 'switzerland',
            'niederlande' => 'netherlands',
            'dänemark' => 'denmark',
            'daenemark' => 'denmark',
            'schweden' => 'sweden',
            'norwegen' => 'norway',
            'polen' => 'poland',
            'spanien' => 'spain',
            'frankreich' => 'france',
        ];

        $normalized = strtolower($destination->name);
        $slug = $nameMapping[$normalized] ?? $destination->slug;
        
        return $slug;
    }

    protected function migrateRegionsFixed($isDryRun)
    {
        $this->info('📍 Migrating Regions (FIXED)...');

        $regions = Destination::where('type', 'region')->get();
        
        // Group by slug AND country_id AND normalized name
        $regionGroups = $this->groupRegionsByIdentity($regions);

        $bar = $this->output->createProgressBar(count($regionGroups));

        foreach ($regionGroups as $identity => $variants) {
            $baseRegion = $variants->firstWhere('language', 'en') ?? $variants->first();

            if (!$isDryRun) {
                $oldCountryId = $baseRegion->country_id;
                $newCountryId = $this->countryIdMap[$oldCountryId] ?? null;

                if (!$newCountryId) {
                    $this->warn("\n   ⚠️  Skipping region '{$baseRegion->name}' - country not found");
                    continue;
                }

                $region = Region::create([
                    'country_id' => $newCountryId,
                    'name' => $baseRegion->name,
                    'slug' => $baseRegion->slug,
                    'filters' => $baseRegion->filters,
                    'thumbnail_path' => $baseRegion->thumbnail_path,
                ]);

                foreach ($variants as $variant) {
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
                    $this->regionIdMap[$variant->id] = 'DRY_' . $variant->id;
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("   ✓ Migrated " . count($regionGroups) . " regions with " . $regions->count() . " translations");
    }

    protected function groupRegionsByIdentity($regions)
    {
        $groups = [];

        foreach ($regions as $region) {
            // Create identity based on slug and country_id
            $identity = $region->country_id . '_' . $region->slug;

            if (!isset($groups[$identity])) {
                $groups[$identity] = collect();
            }
            $groups[$identity]->push($region);
        }

        return $groups;
    }

    protected function migrateCitiesFixed($isDryRun)
    {
        $this->info('📍 Migrating Cities (FIXED)...');

        $cities = Destination::where('type', 'city')->get();
        
        // Group by slug AND country_id AND region_id
        $cityGroups = $this->groupCitiesByIdentity($cities);

        $bar = $this->output->createProgressBar(count($cityGroups));

        foreach ($cityGroups as $identity => $variants) {
            $baseCity = $variants->firstWhere('language', 'en') ?? $variants->first();

            if (!$isDryRun) {
                $oldCountryId = $baseCity->country_id;
                $oldRegionId = $baseCity->region_id;
                
                $newCountryId = $this->countryIdMap[$oldCountryId] ?? null;
                $newRegionId = $oldRegionId ? ($this->regionIdMap[$oldRegionId] ?? null) : null;

                if (!$newCountryId) {
                    $this->warn("\n   ⚠️  Skipping city '{$baseCity->name}' - country not found");
                    continue;
                }

                $city = City::create([
                    'country_id' => $newCountryId,
                    'region_id' => $newRegionId,
                    'name' => $baseCity->name,
                    'slug' => $baseCity->slug,
                    'filters' => $baseCity->filters,
                    'thumbnail_path' => $baseCity->thumbnail_path,
                ]);

                foreach ($variants as $variant) {
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
                    $this->cityIdMap[$variant->id] = 'DRY_' . $variant->id;
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("   ✓ Migrated " . count($cityGroups) . " cities with " . $cities->count() . " translations");
    }

    protected function groupCitiesByIdentity($cities)
    {
        $groups = [];

        foreach ($cities as $city) {
            $identity = $city->country_id . '_' . ($city->region_id ?? 'null') . '_' . $city->slug;

            if (!isset($groups[$identity])) {
                $groups[$identity] = collect();
            }
            $groups[$identity]->push($city);
        }

        return $groups;
    }

    protected function updateRelatedTables($isDryRun)
    {
        $this->newLine();
        $this->info('🔗 Updating Related Tables...');
        
        $this->updateFaqs($isDryRun);
        $this->updateFishCharts($isDryRun);
        $this->updateFishSizeLimits($isDryRun);
        $this->updateFishTimeLimits($isDryRun);
    }

    protected function updateFaqs($isDryRun)
    {
        $this->info('  📝 Updating FAQs...');
        
        $faqs = DestinationFaq::all();
        $updated = 0;

        foreach ($faqs as $faq) {
            $oldDestId = $faq->destination_id;
            
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

        $this->info("     ✓ Updated {$updated} FAQs");
    }

    protected function updateFishCharts($isDryRun)
    {
        $this->info('  🐟 Updating Fish Charts...');

        $charts = DestinationFishChart::all();
        $updated = 0;

        foreach ($charts as $chart) {
            $oldDestId = $chart->destination_id;
            
            if (isset($this->countryIdMap[$oldDestId])) {
                if (!$isDryRun) {
                    $chart->update([
                        'destination_id' => $this->countryIdMap[$oldDestId],
                        'destination_type' => 'country'
                    ]);
                }
                $updated++;
            } elseif (isset($this->regionIdMap[$oldDestId])) {
                if (!$isDryRun) {
                    $chart->update([
                        'destination_id' => $this->regionIdMap[$oldDestId],
                        'destination_type' => 'region'
                    ]);
                }
                $updated++;
            } elseif (isset($this->cityIdMap[$oldDestId])) {
                if (!$isDryRun) {
                    $chart->update([
                        'destination_id' => $this->cityIdMap[$oldDestId],
                        'destination_type' => 'city'
                    ]);
                }
                $updated++;
            }
        }

        $this->info("     ✓ Updated {$updated} fish charts");
    }

    protected function updateFishSizeLimits($isDryRun)
    {
        $this->info('  📏 Updating Fish Size Limits...');

        $limits = DestinationFishSizeLimit::all();
        $updated = 0;

        foreach ($limits as $limit) {
            $oldDestId = $limit->destination_id;
            
            if (isset($this->countryIdMap[$oldDestId])) {
                if (!$isDryRun) {
                    $limit->update([
                        'destination_id' => $this->countryIdMap[$oldDestId],
                        'destination_type' => 'country'
                    ]);
                }
                $updated++;
            } elseif (isset($this->regionIdMap[$oldDestId])) {
                if (!$isDryRun) {
                    $limit->update([
                        'destination_id' => $this->regionIdMap[$oldDestId],
                        'destination_type' => 'region'
                    ]);
                }
                $updated++;
            } elseif (isset($this->cityIdMap[$oldDestId])) {
                if (!$isDryRun) {
                    $limit->update([
                        'destination_id' => $this->cityIdMap[$oldDestId],
                        'destination_type' => 'city'
                    ]);
                }
                $updated++;
            }
        }

        $this->info("     ✓ Updated {$updated} fish size limits");
    }

    protected function updateFishTimeLimits($isDryRun)
    {
        $this->info('  ⏰ Updating Fish Time Limits...');

        $limits = DestinationFishTimeLimit::all();
        $updated = 0;

        foreach ($limits as $limit) {
            $oldDestId = $limit->destination_id;
            
            if (isset($this->countryIdMap[$oldDestId])) {
                if (!$isDryRun) {
                    $limit->update([
                        'destination_id' => $this->countryIdMap[$oldDestId],
                        'destination_type' => 'country'
                    ]);
                }
                $updated++;
            } elseif (isset($this->regionIdMap[$oldDestId])) {
                if (!$isDryRun) {
                    $limit->update([
                        'destination_id' => $this->regionIdMap[$oldDestId],
                        'destination_type' => 'region'
                    ]);
                }
                $updated++;
            } elseif (isset($this->cityIdMap[$oldDestId])) {
                if (!$isDryRun) {
                    $limit->update([
                        'destination_id' => $this->cityIdMap[$oldDestId],
                        'destination_type' => 'city'
                    ]);
                }
                $updated++;
            }
        }

        $this->info("     ✓ Updated {$updated} fish time limits");
    }

    protected function displaySummary()
    {
        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════════════');
        $this->info('📊 MIGRATION SUMMARY:');
        $this->info('─────────────────────────────────────────────────────────────');
        $this->info('   Countries: ' . count(array_unique($this->countryIdMap)) . ' unique');
        $this->info('   Regions: ' . count(array_unique($this->regionIdMap)) . ' unique');
        $this->info('   Cities: ' . count(array_unique($this->cityIdMap)) . ' unique');
        $this->info('   Total old destination IDs mapped: ' . (count($this->countryIdMap) + count($this->regionIdMap) + count($this->cityIdMap)));
        $this->info('═══════════════════════════════════════════════════════════════');
    }
}



