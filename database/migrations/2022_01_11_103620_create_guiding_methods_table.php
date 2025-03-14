<?php

use App\Models\Guiding;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGuidingMethodsTable extends Migration
{
    public function up()
    {
        Schema::create('guiding_methods', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('name');
            $table->foreignIdFor(Guiding::class)->constrained()->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('guiding_methods');
    }
}
