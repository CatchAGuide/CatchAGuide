<?php



use Illuminate\Database\Migrations\Migration;

use Illuminate\Database\Schema\Blueprint;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Schema;



return new class extends Migration

{

    public function up(): void

    {

        Schema::table('users', function (Blueprint $table) {

            if (! Schema::hasColumn('users', 'guide_status')) {

                $table->string('guide_status', 20)->nullable()->after('is_guide');

            }

            if (! Schema::hasColumn('users', 'guide_type')) {

                $table->string('guide_type', 20)->nullable()->after('guide_status');

            }

            if (! Schema::hasColumn('users', 'guide_submitted_at')) {

                $table->timestamp('guide_submitted_at')->nullable()->after('guide_type');

            }

            if (! Schema::hasColumn('users', 'guide_verified_at')) {

                $table->timestamp('guide_verified_at')->nullable()->after('guide_submitted_at');

            }

        });



        if (Schema::hasColumn('users', 'guide_status') && ! $this->hasIndexOnColumn('users', 'guide_status')) {

            Schema::table('users', function (Blueprint $table) {

                $table->index('guide_status');

            });

        }

    }



    public function down(): void

    {

        if ($this->hasIndexOnColumn('users', 'guide_status')) {

            Schema::table('users', function (Blueprint $table) {

                $table->dropIndex(['guide_status']);

            });

        }



        Schema::table('users', function (Blueprint $table) {

            $columns = array_filter([

                'guide_status',

                'guide_type',

                'guide_submitted_at',

                'guide_verified_at',

            ], fn (string $column) => Schema::hasColumn('users', $column));



            if ($columns !== []) {

                $table->dropColumn($columns);

            }

        });

    }



    private function hasIndexOnColumn(string $table, string $column): bool

    {

        $indexes = DB::select('SHOW INDEX FROM `'.$table.'` WHERE Column_name = ?', [$column]);



        return count($indexes) > 0;

    }

};

