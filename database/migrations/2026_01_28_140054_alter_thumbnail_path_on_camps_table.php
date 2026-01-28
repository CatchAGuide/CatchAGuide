<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Use raw SQL to avoid requiring doctrine/dbal
        DB::statement('ALTER TABLE camps MODIFY thumbnail_path TEXT NULL');
    }

    public function down(): void
    {
        // Revert back to VARCHAR(255) if needed
        DB::statement('ALTER TABLE camps MODIFY thumbnail_path VARCHAR(255) NULL');
    }
};