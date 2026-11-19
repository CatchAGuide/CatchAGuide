<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_information', function (Blueprint $table) {
            if (! Schema::hasColumn('user_information', 'display_name')) {
                $table->string('display_name', 80)->nullable();
            }
            if (! Schema::hasColumn('user_information', 'crew_size')) {
                $table->unsignedSmallInteger('crew_size')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_information', function (Blueprint $table) {
            foreach (['display_name', 'crew_size'] as $column) {
                if (Schema::hasColumn('user_information', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
