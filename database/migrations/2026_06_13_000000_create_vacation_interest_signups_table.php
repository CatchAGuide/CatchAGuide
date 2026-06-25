<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacation_interest_signups', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('country', 100);
            $table->string('pillar', 10);
            $table->string('locale', 5)->default('en');
            $table->timestamps();

            $table->index(['country', 'pillar']);
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacation_interest_signups');
    }
};
