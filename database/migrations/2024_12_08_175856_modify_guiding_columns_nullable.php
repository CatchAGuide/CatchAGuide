<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('guidings', function (Blueprint $table) {
            $table->boolean('recommended_for_anfaenger')->nullable()->change();
            $table->boolean('recommended_for_fortgeschrittene')->nullable()->change();
            $table->boolean('recommended_for_profis')->nullable()->change();
            $table->decimal('price', 10, 2)->nullable()->change();
            $table->unsignedBigInteger('fishing_from_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guidings', function (Blueprint $table) {
            $table->boolean('recommended_for_anfaenger')->nullable(false)->change();
            $table->boolean('recommended_for_fortgeschrittene')->nullable(false)->change();
            $table->boolean('recommended_for_profis')->nullable(false)->change();
            $table->decimal('price', 10, 2)->nullable(false)->change();
            $table->unsignedBigInteger('fishing_from_id')->nullable(false)->change();
        });
    }
};