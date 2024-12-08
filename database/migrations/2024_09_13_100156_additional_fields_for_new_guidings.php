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
            if (Schema::hasColumn('guidings', 'tour_type')) {
                $table->dropColumn('tour_type');
            }
            if (Schema::hasColumn('guidings', 'months')) {
                $table->dropColumn('months');
            }
        });

        Schema::table('guidings', function (Blueprint $table) {
            if (!Schema::hasColumn('guidings', 'is_newguiding')) {
                $table->integer('is_newguiding')->default(0);
            }
            if (!Schema::hasColumn('guidings', 'boat_extras')) {
                $table->longText('boat_extras')->nullable();
            }
            if (!Schema::hasColumn('guidings', 'target_fish')) {
                $table->longText('target_fish')->nullable();
            }
            if (!Schema::hasColumn('guidings', 'fishing_methods')) {
                $table->longText('fishing_methods')->nullable();
            }
            if (!Schema::hasColumn('guidings', 'water_types')) {
                $table->longText('water_types')->nullable();
            }
            if (!Schema::hasColumn('guidings', 'experience_level')) {
                $table->string('experience_level')->nullable();
            }
            if (!Schema::hasColumn('guidings', 'inclusions')) {
                $table->longText('inclusions')->nullable();
            }
            if (!Schema::hasColumn('guidings', 'requirements')) {
                $table->longText('requirements')->nullable();
            }
            if (!Schema::hasColumn('guidings', 'recommendations')) {
                $table->longText('recommendations')->nullable();
            }
            if (!Schema::hasColumn('guidings', 'other_information')) {
                $table->longText('other_information')->nullable();
            }
            if (!Schema::hasColumn('guidings', 'duration_type')) {
                $table->string('duration_type')->nullable();
            }
            if (!Schema::hasColumn('guidings', 'price_type')) {
                $table->string('price_type')->nullable();
            }
            if (!Schema::hasColumn('guidings', 'prices')) {
                $table->longText('prices')->nullable();
            }
            if (!Schema::hasColumn('guidings', 'pricing_extra')) {
                $table->longText('pricing_extra')->nullable();
            }
            if (!Schema::hasColumn('guidings', 'tour_type')) {
                $table->longText('tour_type')->nullable();
            }
            if (!Schema::hasColumn('guidings', 'months')) {
                $table->longText('months')->nullable();
            }
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
