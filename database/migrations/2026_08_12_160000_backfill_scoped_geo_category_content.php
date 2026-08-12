<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->backfillGeoTranslations('c_country_translations', 'country_id', 'geo_country');
        $this->backfillGeoTranslations('c_region_translations', 'region_id', 'geo_region');
        $this->backfillGeoTranslations('c_city_translations', 'city_id', 'geo_city');

        $destinations = DB::table('destinations')
            ->whereIn('type', ['vacations', 'trips'])
            ->get(['id', 'language', 'title', 'sub_title', 'introduction', 'content', 'faq_title', 'type']);

        foreach ($destinations as $row) {
            $scope = $row->type === 'trips' ? 'trips' : 'vacations';

            DB::table('languages')->updateOrInsert(
                [
                    'source_id' => (string) $row->id,
                    'type' => 'destination_country',
                    'scope' => $scope,
                    'language' => $row->language ?? 'de',
                ],
                [
                    'title' => $row->title ?? '',
                    'sub_title' => $row->sub_title ?? '',
                    'introduction' => $row->introduction ?? '',
                    'content' => $row->content ?? '',
                    'faq_title' => $row->faq_title ?? '',
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
        }
    }

    private function backfillGeoTranslations(string $table, string $fk, string $entityType): void
    {
        if (! DB::getSchemaBuilder()->hasTable($table)) {
            return;
        }

        $rows = DB::table($table)->get();

        foreach ($rows as $row) {
            DB::table('languages')->updateOrInsert(
                [
                    'source_id' => (string) $row->{$fk},
                    'type' => $entityType,
                    'scope' => 'tours',
                    'language' => $row->language ?? 'de',
                ],
                [
                    'title' => $row->title ?? '',
                    'sub_title' => $row->sub_title ?? '',
                    'introduction' => $row->introduction ?? '',
                    'content' => $row->content ?? '',
                    'faq_title' => $row->faq_title ?? '',
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
        }
    }

    public function down(): void
    {
        // Non-destructive: scoped rows may have been edited after backfill.
    }
};
