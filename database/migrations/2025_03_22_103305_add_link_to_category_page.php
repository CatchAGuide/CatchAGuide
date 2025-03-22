<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLinkToCategoryPage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('category_pages', function (Blueprint $table) {
            $table->string('source_id')->nullable()->after('thumbnail_path');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('category_pages', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('source_id');
        });
    }
}
