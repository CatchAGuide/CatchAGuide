<?php

use App\Models\CategoryCountry;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoryCountryFishChartTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_country_fish_chart', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(CategoryCountry::class)->constrained()->cascadeOnDelete();
            $table->string('fish_en');
            $table->string('fish_de');
            $table->string('withdrawal_window_en');
            $table->string('withdrawal_window_de');
            $table->string('closed_season_en');
            $table->string('closed_season_de');
            $table->tinyInteger('jan');
            $table->tinyInteger('feb');
            $table->tinyInteger('mar');
            $table->tinyInteger('apr');
            $table->tinyInteger('may');
            $table->tinyInteger('jun');
            $table->tinyInteger('jul');
            $table->tinyInteger('aug');
            $table->tinyInteger('sep');
            $table->tinyInteger('oct');
            $table->tinyInteger('nov');
            $table->tinyInteger('dec');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('category_country_fish_chart');
    }
}
