<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Internal team comment on gift voucher orders (Admin › Bookings › Vouchers), like the other
 * admin booking lists have.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('voucher_orders', 'admin_comment')) {
            return;
        }

        Schema::table('voucher_orders', function (Blueprint $table) {
            $table->text('admin_comment')->nullable()->after('personal_message');
        });
    }

    public function down(): void
    {
        Schema::table('voucher_orders', function (Blueprint $table) {
            $table->dropColumn('admin_comment');
        });
    }
};
