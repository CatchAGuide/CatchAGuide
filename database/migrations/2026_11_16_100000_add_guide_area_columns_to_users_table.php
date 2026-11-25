<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Guide Area redesign: per-guide beta flag, plus structured bank-transfer details that guests
 * see after booking (holder / IBAN / BIC). The free-text users.banktransferdetails stays and is
 * kept in sync on save, because the guest confirmation mail and the old dashboard read it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'guide_area_beta')) {
                $table->boolean('guide_area_beta')->default(false);
            }
            // Encrypted casts on the model: text columns, not strings.
            if (! Schema::hasColumn('users', 'banktransfer_holder')) {
                $table->text('banktransfer_holder')->nullable();
            }
            if (! Schema::hasColumn('users', 'banktransfer_iban')) {
                $table->text('banktransfer_iban')->nullable();
            }
            if (! Schema::hasColumn('users', 'banktransfer_bic')) {
                $table->text('banktransfer_bic')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['guide_area_beta', 'banktransfer_holder', 'banktransfer_iban', 'banktransfer_bic'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
