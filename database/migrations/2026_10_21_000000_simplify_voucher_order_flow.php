<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * The guide no longer confirms the buyer's payment: they get the voucher with the order email
 * and handle payment and delivery themselves, so payment_token and paid_at go. The voucher is
 * written in the guide's language (voucher_language), snapshotted at order time; language stays
 * the buyer's checkout language for their emails.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('voucher_orders', function (Blueprint $table) {
            $table->string('voucher_language', 5)->nullable()->after('language');
        });

        DB::table('voucher_orders')->whereNull('voucher_language')->update(['voucher_language' => DB::raw('language')]);

        Schema::table('voucher_orders', function (Blueprint $table) {
            $table->dropUnique(['payment_token']);
            $table->dropColumn(['payment_token', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::table('voucher_orders', function (Blueprint $table) {
            $table->string('payment_token', 32)->nullable()->unique()->after('voucher_code');
            $table->timestamp('paid_at')->nullable()->after('pdf_path');
        });

        DB::table('voucher_orders')->whereNull('payment_token')->orderBy('id')->each(function ($row) {
            DB::table('voucher_orders')->where('id', $row->id)->update(['payment_token' => Str::random(32)]);
        });

        Schema::table('voucher_orders', function (Blueprint $table) {
            $table->dropColumn('voucher_language');
        });
    }
};
