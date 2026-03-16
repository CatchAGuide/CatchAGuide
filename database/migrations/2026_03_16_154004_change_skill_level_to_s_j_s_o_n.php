<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ChangeSkillLevelToSJSON extends Migration
{
    public function up(): void
    {
        // Normalize existing data to valid JSON before changing the column type.
        //
        // - Empty strings become NULL
        // - Non-empty, non-JSON values are wrapped in a JSON array via JSON_ARRAY(...)
        //   so that Tagify-compatible array-of-objects data can be introduced going forward.
        DB::statement("
            UPDATE trips
            SET skill_level = NULL
            WHERE skill_level IS NULL OR skill_level = ''
        ");

        DB::statement("
            UPDATE trips
            SET skill_level = JSON_ARRAY(skill_level)
            WHERE skill_level IS NOT NULL
              AND skill_level <> ''
              AND JSON_VALID(skill_level) = 0
        ");

        Schema::table('trips', function (Blueprint $table) {
            $table->json('skill_level')->nullable()->change();
        });
    }
    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            // Revert back to string if you ever roll back
            $table->string('skill_level')->nullable()->change();
        });
    }
}
