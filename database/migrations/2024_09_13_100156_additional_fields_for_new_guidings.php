<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AdditionalFieldsForNewGuidings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('guidings', function (Blueprint $table) {
            $table->dropColumn([
                'tour_type',
                'months',
            ]);
        });

        Schema::table('guidings', function (Blueprint $table) {
            $table->integer('is_newguiding')->default(0);
            $table->longText('boat_extras')->nullable();
            $table->longText('target_fish')->nullable();
            $table->longText('fishing_methods')->nullable();
            $table->longText('water_types')->nullable();
            $table->string('experience_level')->nullable();
            $table->longText('inclusions')->nullable();
            $table->longText('requirements')->nullable();
            $table->longText('recommendations')->nullable();
            $table->longText('other_information')->nullable();
            $table->string('duration_type')->nullable();
            $table->string('price_type')->nullable();
            $table->longText('prices')->nullable();
            $table->longText('pricing_extra')->nullable();
            $table->longText('tour_type')->nullable();
            $table->longText('months')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('guidings', function (Blueprint $table) {
            $table->dropColumn([
                'is_newguiding',
                'boat_extras',
                'target_fish',
                'fishing_methods',
                'water_types',
                'experience_level',
                'inclusions',
                'requirements',
                'recommendations',
                'other_information',
                'duration_type',
            ]);
        });
    }
}
