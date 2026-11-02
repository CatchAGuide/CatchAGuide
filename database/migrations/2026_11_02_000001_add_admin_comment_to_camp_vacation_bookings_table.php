<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Internal team comment on camp/vacation requests (Admin › Bookings › Camps), like the other
 * admin request lists already have.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('camp_vacation_bookings', 'admin_comment')) {
            return;
        }

        Schema::table('camp_vacation_bookings', function (Blueprint $table) {
            $table->text('admin_comment')->nullable()->after('message');
        });
    }

    public function down(): void
    {
        Schema::table('camp_vacation_bookings', function (Blueprint $table) {
            $table->dropColumn('admin_comment');
        });
    }
};
