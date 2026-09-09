<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('languages', function (Blueprint $table) {
            if (! Schema::hasColumn('languages', 'scope')) {
                $table->string('scope', 32)->nullable()->after('type');
            }
        });

        Schema::table('faqs', function (Blueprint $table) {
            if (! Schema::hasColumn('faqs', 'scope')) {
                $table->string('scope', 32)->nullable()->after('language');
            }
        });

        $categoryPageIds = DB::table('category_pages')->pluck('id');

        if ($categoryPageIds->isNotEmpty()) {
            DB::table('languages')
                ->whereIn('source_id', $categoryPageIds->map(fn ($id) => (string) $id))
                ->update([
                    'type' => 'category_page',
                    'scope' => 'tours',
                ]);
        }

        DB::table('faqs')
            ->whereIn('page', ['Targets', 'Methods'])
            ->whereNull('scope')
            ->update(['scope' => 'tours']);
    }

    public function down(): void
    {
        Schema::table('languages', function (Blueprint $table) {
            if (Schema::hasColumn('languages', 'scope')) {
                $table->dropColumn('scope');
            }
        });

        Schema::table('faqs', function (Blueprint $table) {
            if (Schema::hasColumn('faqs', 'scope')) {
                $table->dropColumn('scope');
            }
        });
    }
};
