<?php

use App\Models\Country;
use App\Models\Destination;
use App\Models\DestinationFaq;
use App\Models\DestinationFishChart;
use App\Models\DestinationFishSizeLimit;
use App\Models\DestinationFishTimeLimit;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->dropForeignKeys();

        $destinations = Destination::where('type', 'vacations')->get();
        $grouped = $destinations->groupBy(fn (Destination $d) => strtolower(str_replace(' ', '-', $d->name)));

        foreach ($grouped as $slug => $rows) {
            $country = Country::where('slug', $slug)->orWhere('name', $rows->first()->name)->first();

            if (! $country) {
                continue;
            }

            foreach ($rows as $dest) {
                $this->migrateRelated('destination_fish_charts', $dest->id, $country->id);
                $this->migrateRelated('destination_fish_size_limits', $dest->id, $country->id);
                $this->migrateRelated('destination_fish_time_limits', $dest->id, $country->id);

                DB::table('destination_faqs')
                    ->where('destination_id', $dest->id)
                    ->where(function ($q) {
                        $q->whereNull('destination_type')->orWhere('destination_type', '');
                    })
                    ->update([
                        'destination_id' => $country->id,
                        'destination_type' => 'country',
                    ]);
            }

            Log::info("Migrated fish/faq data for '{$rows->first()->name}' -> country#{$country->id}");
        }
    }

    private function migrateRelated(string $table, int $oldDestId, int $newCountryId): void
    {
        DB::table($table)
            ->where('destination_id', $oldDestId)
            ->where(function ($q) {
                $q->whereNull('destination_type')->orWhere('destination_type', '');
            })
            ->update([
                'destination_id' => $newCountryId,
                'destination_type' => 'country',
            ]);
    }

    private function dropForeignKeys(): void
    {
        $tables = ['destination_faqs', 'destination_fish_charts', 'destination_fish_size_limits', 'destination_fish_time_limits'];

        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $fks = DB::select("
                SELECT CONSTRAINT_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = ?
                  AND COLUMN_NAME = 'destination_id'
                  AND REFERENCED_TABLE_NAME = 'destinations'
            ", [$table]);

            foreach ($fks as $fk) {
                DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
            }
        }
    }

    public function down(): void
    {
        // Not safely reversible
    }
};
