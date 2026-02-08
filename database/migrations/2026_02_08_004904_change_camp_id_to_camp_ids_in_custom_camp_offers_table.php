<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeCampIdToCampIdsInCustomCampOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('custom_camp_offers', function (Blueprint $table) {
            $table->json('camp_ids')->nullable()->after('recipient_phone');
        });

        // Migrate existing camp_id to camp_ids
        \DB::table('custom_camp_offers')->whereNotNull('camp_id')->get()->each(function ($row) {
            \DB::table('custom_camp_offers')->where('id', $row->id)->update([
                'camp_ids' => json_encode([$row->camp_id]),
            ]);
        });

        Schema::table('custom_camp_offers', function (Blueprint $table) {
            $table->dropForeign(['camp_id']);
            $table->dropColumn('camp_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('custom_camp_offers', function (Blueprint $table) {
            $table->unsignedBigInteger('camp_id')->nullable()->after('recipient_phone');
        });

        // Migrate camp_ids back to camp_id (use first)
        \DB::table('custom_camp_offers')->whereNotNull('camp_ids')->get()->each(function ($row) {
            $ids = json_decode($row->camp_ids, true);
            $firstId = is_array($ids) && !empty($ids) ? $ids[0] : null;
            if ($firstId) {
                \DB::table('custom_camp_offers')->where('id', $row->id)->update(['camp_id' => $firstId]);
            }
        });

        Schema::table('custom_camp_offers', function (Blueprint $table) {
            $table->foreign('camp_id')->references('id')->on('camps')->onDelete('set null');
            $table->dropColumn('camp_ids');
        });
    }
}
