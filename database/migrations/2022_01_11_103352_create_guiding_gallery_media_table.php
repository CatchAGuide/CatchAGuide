<?php

use App\Models\Guiding;
use App\Models\Media;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGuidingGalleryMediaTable extends Migration
{
    public function up()
    {
        Schema::create('guiding_gallery_media', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignIdFor(Media::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Guiding::class)->constrained()->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('guiding_gallery_media');
    }
}
