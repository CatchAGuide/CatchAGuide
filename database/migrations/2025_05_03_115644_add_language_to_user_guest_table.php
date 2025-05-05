<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLanguageToUserGuestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_guests', function (Blueprint $table) {
            $table->string('language')->nullable()->after('email')->default('de');
        });
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'language_speak')) {
                $table->dropColumn('language_speak');
            }
            if (!Schema::hasColumn('users', 'language')) {
                $table->string('language')->nullable()->default('de');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_guests', function (Blueprint $table) {
            $table->dropColumn('language');
        });
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'language')) {
                $table->dropColumn('language');
            }
            if (!Schema::hasColumn('users', 'language_speak')) {
                $table->string('language_speak')->nullable();
            }
        });
    }
}
