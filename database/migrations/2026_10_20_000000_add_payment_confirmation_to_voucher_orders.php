<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * The guide confirms the buyer's payment (link in the guide email, or Profile › Gift vouchers);
 * only then does Catch A Guide email the voucher to the buyer. payment_token is the secret in
 * the emailed link; paid_at is null while the payment is still open.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('voucher_orders', function (Blueprint $table) {
            $table->string('payment_token', 32)->nullable()->unique()->after('voucher_code');
            $table->timestamp('paid_at')->nullable()->after('pdf_path');
        });

        // Orders placed before this step can still be confirmed from the profile.
        DB::table('voucher_orders')->whereNull('payment_token')->orderBy('id')->each(function ($row) {
            DB::table('voucher_orders')->where('id', $row->id)->update(['payment_token' => Str::random(32)]);
        });
    }

    public function down(): void
    {
        Schema::table('voucher_orders', function (Blueprint $table) {
            $table->dropUnique(['payment_token']);
            $table->dropColumn(['payment_token', 'paid_at']);
        });
    }
};
