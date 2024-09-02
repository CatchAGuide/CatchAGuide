<?php

use App\Models\Destination;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDestinationFishChartTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('destination_fish_charts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Destination::class)->constrained()->cascadeOnDelete();
            $table->string('fish');
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
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('destination_fish_charts');
    }
}
