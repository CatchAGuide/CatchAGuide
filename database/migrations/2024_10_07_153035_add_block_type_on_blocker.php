<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBlockTypeOnBlocker extends Migration
{
    public function up()
    {
        Schema::table('blocked_events', function (Blueprint $table) {
            $table->enum('source', ['personal', 'global'])->nullable()->before('user_id');
        });
    }

    public function down()
    {
        Schema::table('blocked_events', function (Blueprint $table) {
            $table->dropColumn([
                'source'
            ]);
        });
    }
}