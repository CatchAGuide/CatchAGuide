<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guidings', function (Blueprint $table) {
            if (! Schema::hasColumn('guidings', 'country_iso')) {
                $table->char('country_iso', 2)->nullable()->after('country');
                $table->index('country_iso');
            }
        });
    }

    public function down(): void
    {
        Schema::table('guidings', function (Blueprint $table) {
            if (Schema::hasColumn('guidings', 'country_iso')) {
                $table->dropIndex(['country_iso']);
                $table->dropColumn('country_iso');
            }
        });
    }
};
