<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('bookings', 'admin_comment')) {
            return;
        }

        Schema::table('bookings', function (Blueprint $table) {
            $table->text('admin_comment')->nullable()->after('additional_information');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('bookings', 'admin_comment')) {
            return;
        }

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('admin_comment');
        });
    }
};

