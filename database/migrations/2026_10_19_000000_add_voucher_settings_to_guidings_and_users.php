<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gift voucher settings (spec "Gift Vouchers for Guides" v1.1, section 8.2): every tour can be
 * bought as a gift voucher unless its guide switches it off, and each guide picks once whether
 * buyers get the Catch a Guide standard voucher PDF or the guide's own voucher. The column
 * default turns vouchers on for all existing tours at go-live.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guidings', function (Blueprint $table) {
            $table->boolean('voucher_enabled')->default(true);
        });

        Schema::table('users', function (Blueprint $table) {
            // App\Enums\VoucherDesign
            $table->string('voucher_design', 20)->default('cag_standard');
        });
    }

    public function down(): void
    {
        Schema::table('guidings', function (Blueprint $table) {
            $table->dropColumn('voucher_enabled');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('voucher_design');
        });
    }
};
