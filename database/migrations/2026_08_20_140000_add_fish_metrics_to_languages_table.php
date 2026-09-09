<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('languages', function (Blueprint $table) {
            if (! Schema::hasColumn('languages', 'fish_avail_title')) {
                $table->string('fish_avail_title')->nullable()->after('faq_title');
            }
            if (! Schema::hasColumn('languages', 'fish_avail_intro')) {
                $table->text('fish_avail_intro')->nullable()->after('fish_avail_title');
            }
            if (! Schema::hasColumn('languages', 'size_limit_title')) {
                $table->string('size_limit_title')->nullable()->after('fish_avail_intro');
            }
            if (! Schema::hasColumn('languages', 'size_limit_intro')) {
                $table->text('size_limit_intro')->nullable()->after('size_limit_title');
            }
            if (! Schema::hasColumn('languages', 'time_limit_title')) {
                $table->string('time_limit_title')->nullable()->after('size_limit_intro');
            }
            if (! Schema::hasColumn('languages', 'time_limit_intro')) {
                $table->text('time_limit_intro')->nullable()->after('time_limit_title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('languages', function (Blueprint $table) {
            foreach ([
                'fish_avail_title',
                'fish_avail_intro',
                'size_limit_title',
                'size_limit_intro',
                'time_limit_title',
                'time_limit_intro',
            ] as $column) {
                if (Schema::hasColumn('languages', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
