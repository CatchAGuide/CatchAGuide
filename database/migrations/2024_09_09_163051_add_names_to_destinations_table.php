<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNamesToDestinationsTable extends Migration
{
    public function up()
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->string('fish_avail_title')->nullable();
            $table->text('fish_avail_intro')->nullable();

            $table->string('size_limit_title')->nullable();
            $table->text('size_limit_intro')->nullable();

            $table->string('time_limit_title')->nullable();
            $table->text('time_limit_intro')->nullable();

            $table->string('faq_title')->nullable();
        });
    }

    public function down()
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->dropColumn([
                'fish_avail_title', 'fish_avail_intro',
                'size_limit_title', 'size_limit_intro',
                'time_limit_title', 'time_limit_intro',
                'faq_title'
            ]);
        });
    }
}
