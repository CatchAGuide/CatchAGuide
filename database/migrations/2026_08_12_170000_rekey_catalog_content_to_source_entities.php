<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->rekeyCatalogRows('Targets', 'target_fish');
        $this->rekeyCatalogRows('Methods', 'method');
    }

    private function rekeyCatalogRows(string $pageType, string $entityType): void
    {
        $pages = DB::table('category_pages')
            ->where('type', $pageType)
            ->get(['id', 'source_id']);

        foreach ($pages as $page) {
            if ($page->source_id === null) {
                continue;
            }

            DB::table('languages')
                ->where('source_id', (string) $page->id)
                ->where(function ($query) {
                    $query->where('type', 'category_page')->orWhereNull('type');
                })
                ->update([
                    'source_id' => (string) $page->source_id,
                    'type' => $entityType,
                ]);

            DB::table('faqs')
                ->where('source_id', $page->id)
                ->whereIn('page', ['Targets', 'Methods', 'category_page', $entityType])
                ->update([
                    'source_id' => $page->source_id,
                    'page' => $entityType,
                ]);
        }
    }

    public function down(): void
    {
        // Non-destructive: rows may have been edited after rekey.
    }
};
