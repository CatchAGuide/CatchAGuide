<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJsonColumnsToAccommodationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accommodations', function (Blueprint $table) {
            // Add only the missing JSON columns that weren't added by previous migrations
            $table->json('extras')->nullable();
            $table->json('inclusives')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accommodations', function (Blueprint $table) {
            // Drop only the columns we added
            $table->dropColumn([
                'extras',
                'inclusives'
            ]);
        });
    }
}
