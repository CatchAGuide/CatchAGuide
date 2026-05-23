<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_information', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('country');
            $table->string('legal_form')->nullable()->after('company_name');
            $table->unsignedSmallInteger('founded_year')->nullable()->after('legal_form');
            $table->string('contact_position')->nullable()->after('founded_year');
            $table->string('trade_register_number')->nullable()->after('contact_position');
            $table->string('trade_register_court')->nullable()->after('trade_register_number');
            $table->string('tax_number')->nullable()->after('trade_register_court');
            $table->json('company_profile')->nullable()->after('fishing_start_year');
        });
    }

    public function down(): void
    {
        Schema::table('user_information', function (Blueprint $table) {
            $table->dropColumn([
                'company_name',
                'legal_form',
                'founded_year',
                'contact_position',
                'trade_register_number',
                'trade_register_court',
                'tax_number',
                'company_profile',
            ]);
        });
    }
};
