<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Purchased vouchers now use the same open → in process → done status as landing-page
 * requests. Rows created before that have a null status; treat them as still open.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('voucher_orders')
            ->where('kind', 'order')
            ->whereNull('status')
            ->update(['status' => 'open']);
    }

    public function down(): void
    {
        DB::table('voucher_orders')
            ->where('kind', 'order')
            ->where('status', 'open')
            ->whereNull('done_reason')
            ->update(['status' => null]);
    }
};
