<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gift voucher requests from the /guidings/voucher landing page share voucher_orders with the
 * tour orders and are told apart by kind (App\Enums\VoucherOrderKind). A request names a
 * destination and an amount but no tour, so the tour, guide and voucher columns become nullable;
 * CaG follows up with the buyer and issues the voucher by hand.
 *
 * status / done_reason are the request's open → in process → done workflow (logged in
 * booking_status_changes, like camp/trip and contact requests). Orders have no status.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('voucher_orders', function (Blueprint $table) {
            $table->string('kind', 20)->default('order')->after('voucher_code')->index();
            $table->string('status', 20)->nullable()->after('kind')->index();
            $table->string('done_reason', 20)->nullable()->after('status');
            $table->string('request_country', 100)->nullable()->after('tour_title_snapshot');
            $table->string('request_region', 100)->nullable()->after('request_country');
            $table->string('request_city', 100)->nullable()->after('request_region');

            $table->unsignedBigInteger('guiding_id')->nullable()->change();
            $table->unsignedBigInteger('guide_id')->nullable()->change();
            $table->string('tour_title_snapshot')->nullable()->change();
            $table->unsignedSmallInteger('number_of_guests')->nullable()->change();
            $table->string('recipient_names', 100)->nullable()->change();
            $table->string('buyer_last_name', 100)->nullable()->change();
            $table->string('design_type', 20)->nullable()->change();
            $table->date('valid_until')->nullable()->change();
            $table->decimal('commission_rate', 6, 4)->default(0)->change();
            $table->decimal('commission_amount', 10, 2)->default(0)->change();
        });
    }

    /**
     * Leaves the columns nullable: requests stored meanwhile have no tour to put back.
     */
    public function down(): void
    {
        Schema::table('voucher_orders', function (Blueprint $table) {
            $table->dropIndex(['kind']);
            $table->dropIndex(['status']);
            $table->dropColumn(['kind', 'status', 'done_reason', 'request_country', 'request_region', 'request_city']);
        });
    }
};
