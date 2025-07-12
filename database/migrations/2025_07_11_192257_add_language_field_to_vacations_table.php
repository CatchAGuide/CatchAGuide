<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vacations', function (Blueprint $table) {
            $table->string('language', 2)->default('de')->after('status')->comment('Original language of the vacation data');
            $table->timestamp('content_updated_at')->nullable()->after('language')->comment('When the content was last significantly updated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vacations', function (Blueprint $table) {
            $table->dropColumn(['language', 'content_updated_at']);
        });
    }
};
