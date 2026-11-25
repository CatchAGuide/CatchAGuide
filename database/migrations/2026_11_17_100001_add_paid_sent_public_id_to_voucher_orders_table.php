<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Guide Area › Vouchers: the guide marks an order as paid and as sent; status (open /
 * in process / done) stays and is kept in sync for the admin lists. public_id is the short
 * "G-0001" reference shown to guides and buyers.
 *
 * Backfill (plan "Data migration"): done → paid + sent, in_process → paid, open → neither;
 * G- numbers by creation date. Requests (kind = request) have no guide and get neither.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('voucher_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('voucher_orders', 'is_paid')) {
                $table->boolean('is_paid')->default(false);
                $table->timestamp('paid_at')->nullable();
            }
            if (! Schema::hasColumn('voucher_orders', 'is_sent')) {
                $table->boolean('is_sent')->default(false);
                $table->timestamp('sent_at')->nullable();
            }
            if (! Schema::hasColumn('voucher_orders', 'public_id')) {
                $table->string('public_id', 20)->nullable()->unique();
            }
        });

        DB::table('voucher_orders')->where('kind', 'order')->where('status', 'done')->where('is_paid', false)
            ->update(['is_paid' => true, 'paid_at' => DB::raw('updated_at'), 'is_sent' => true, 'sent_at' => DB::raw('updated_at')]);
        DB::table('voucher_orders')->where('kind', 'order')->where('status', 'in_process')->where('is_paid', false)
            ->update(['is_paid' => true, 'paid_at' => DB::raw('updated_at')]);

        $next = 1 + (int) DB::table('voucher_orders')->whereNotNull('public_id')->count();
        DB::table('voucher_orders')->where('kind', 'order')->whereNull('public_id')
            ->orderBy('created_at')->orderBy('id')
            ->select('id')
            ->chunk(500, function ($rows) use (&$next) {
                foreach ($rows as $row) {
                    DB::table('voucher_orders')->where('id', $row->id)
                        ->update(['public_id' => 'G-'.str_pad((string) $next++, 4, '0', STR_PAD_LEFT)]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('voucher_orders', function (Blueprint $table) {
            foreach (['is_paid', 'paid_at', 'is_sent', 'sent_at'] as $column) {
                if (Schema::hasColumn('voucher_orders', $column)) {
                    $table->dropColumn($column);
                }
            }
            if (Schema::hasColumn('voucher_orders', 'public_id')) {
                $table->dropUnique(['public_id']);
                $table->dropColumn('public_id');
            }
        });
    }
};
