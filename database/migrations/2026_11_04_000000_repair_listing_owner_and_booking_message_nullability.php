<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Production recorded 2026_11_02_000000 and 2026_11_03_000001 as run, but its
 * schema kept listings.user_id and camp_vacation_bookings.message NOT NULL.
 * The detach update then stored user_id = 0 instead of NULL on every listing,
 * and every staff save (user_id null) failed with "cannot be null".
 *
 * Checks the live schema, so it is safe where the earlier migrations did apply.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->listingTables() as $table) {
            if (! Schema::hasColumn($table, 'user_id')) {
                continue;
            }

            if (! $this->isNullable($table, 'user_id')) {
                $this->dropUserForeignKey($table);

                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->unsignedBigInteger('user_id')->nullable()->change();
                });
            }

            DB::table($table)->where('user_id', 0)->update(['user_id' => null]);
            DB::table($table)
                ->whereNotNull('user_id')
                ->whereNotIn('user_id', DB::table('users')->select('id'))
                ->update(['user_id' => null]);

            if ($this->userForeignKeyOnDelete($table) !== 'set null') {
                $this->dropUserForeignKey($table);

                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->foreign('user_id')->references('id')->on('users')->nullOnDelete();
                });
            }
        }

        if (Schema::hasColumn('camp_vacation_bookings', 'message')
            && ! $this->isNullable('camp_vacation_bookings', 'message')) {
            Schema::table('camp_vacation_bookings', function (Blueprint $table) {
                $table->text('message')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // Repair only; the original migrations own the reverse schema change.
    }

    /**
     * @return list<string>
     */
    private function listingTables(): array
    {
        return ['rental_boats', 'camps', 'trips', 'accommodations', 'special_offers'];
    }

    private function isNullable(string $table, string $column): bool
    {
        foreach (Schema::getColumns($table) as $definition) {
            if ($definition['name'] === $column) {
                return (bool) $definition['nullable'];
            }
        }

        return false;
    }

    private function userForeignKeyOnDelete(string $table): ?string
    {
        foreach (Schema::getForeignKeys($table) as $foreignKey) {
            if ($foreignKey['columns'] === ['user_id']) {
                return strtolower((string) $foreignKey['on_delete']);
            }
        }

        return null;
    }

    private function dropUserForeignKey(string $table): void
    {
        foreach (Schema::getForeignKeys($table) as $foreignKey) {
            if ($foreignKey['columns'] === ['user_id']) {
                Schema::table($table, function (Blueprint $blueprint) use ($foreignKey) {
                    $blueprint->dropForeign($foreignKey['name']);
                });
            }
        }
    }
};
