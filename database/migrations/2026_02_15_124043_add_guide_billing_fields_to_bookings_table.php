<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGuideBillingFieldsToBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('guide_invoice_sent_at')->nullable()->after('is_paid');
            $table->boolean('is_guide_billed')->default(false)->after('guide_invoice_sent_at');
            $table->timestamp('guide_billed_at')->nullable()->after('is_guide_billed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'guide_invoice_sent_at',
                'is_guide_billed',
                'guide_billed_at',
            ]);
        });
    }
}
