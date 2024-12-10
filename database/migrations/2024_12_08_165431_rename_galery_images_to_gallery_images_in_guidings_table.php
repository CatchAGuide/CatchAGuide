<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class RenameGaleryImagesToGalleryImagesInGuidingsTable extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('guidings', 'galery_images')) {
            Schema::table('guidings', function (Blueprint $table) {
                $table->renameColumn('galery_images', 'gallery_images');
            });
        }
    }

    public function down()
    {
        Schema::table('guidings', function (Blueprint $table) {
            $table->renameColumn('gallery_images', 'galery_images');
        });
    }
}
