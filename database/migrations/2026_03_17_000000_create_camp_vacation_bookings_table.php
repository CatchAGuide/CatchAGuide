<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('camp_vacation_bookings', function (Blueprint $table) {
            $table->id();

            $table->string('source_type', 20); // camp | vacation
            $table->unsignedBigInteger('source_id');

            $table->date('preferred_date');
            $table->unsignedSmallInteger('number_of_persons');

            $table->string('name');
            $table->string('email');
            $table->string('phone_country_code', 10);
            $table->string('phone', 20);

            $table->text('message');

            $table->string('status', 20)->default('open');

            $table->timestamps();

            $table->index(['source_type', 'source_id'], 'idx_cvb_source');
            $table->index(['status', 'created_at'], 'idx_cvb_status_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('camp_vacation_bookings');
    }
};

