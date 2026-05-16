<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_scheduled_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->text('description')->nullable();
            $table->string('command', 512);
            $table->boolean('is_enabled')->default(false);
            $table->string('frequency', 32);
            $table->string('schedule_time', 8)->nullable();
            $table->unsignedTinyInteger('day_of_week')->nullable();
            $table->string('cron_expression', 64)->nullable();
            $table->boolean('without_overlapping')->default(false);
            $table->boolean('run_in_background')->default(false);
            $table->string('append_output_to', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_scheduled_tasks');
    }
};
