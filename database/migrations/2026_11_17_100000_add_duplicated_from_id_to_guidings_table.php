<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Guide Area › My fishing tours › Duplicate: the copy remembers which tour it came from.
 * No foreign key on purpose: deleting the original must not touch the copy.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('guidings', 'duplicated_from_id')) {
            return;
        }

        Schema::table('guidings', function (Blueprint $table) {
            $table->unsignedBigInteger('duplicated_from_id')->nullable()->index();
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('guidings', 'duplicated_from_id')) {
            Schema::table('guidings', function (Blueprint $table) {
                $table->dropIndex(['duplicated_from_id']);
                $table->dropColumn('duplicated_from_id');
            });
        }
    }
};
