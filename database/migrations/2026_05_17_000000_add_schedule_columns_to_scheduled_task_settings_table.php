<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scheduled_task_settings', function (Blueprint $table) {
            $table->string('frequency', 32)->nullable()->after('is_enabled');
            $table->string('schedule_time', 8)->nullable()->after('frequency');
            $table->unsignedTinyInteger('day_of_week')->nullable()->after('schedule_time');
            $table->string('cron_expression', 64)->nullable()->after('day_of_week');
        });
    }

    public function down(): void
    {
        Schema::table('scheduled_task_settings', function (Blueprint $table) {
            $table->dropColumn(['frequency', 'schedule_time', 'day_of_week', 'cron_expression']);
        });
    }
};
