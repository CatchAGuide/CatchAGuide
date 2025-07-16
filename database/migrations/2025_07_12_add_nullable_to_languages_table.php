<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('languages', function (Blueprint $table) {
            $table->string('sub_title')->nullable()->change();
            $table->text('introduction')->nullable()->change();
            $table->text('content')->nullable()->change();
            $table->string('faq_title')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('languages', function (Blueprint $table) {
            $table->string('sub_title')->nullable(false)->change();
            $table->text('introduction')->nullable(false)->change();
            $table->text('content')->nullable(false)->change();
            $table->string('faq_title')->nullable(false)->change();
        });
    }
}; 