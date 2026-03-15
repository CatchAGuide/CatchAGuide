<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->softDeletes();
            $table->foreignId('deleted_by')->nullable()->after('deleted_at')->constrained('employees')->nullOnDelete();
            $table->timestamp('password_reset_at')->nullable()->after('remember_token');
            $table->foreignId('password_reset_by')->nullable()->after('password_reset_at')->constrained('employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['deleted_by']);
            $table->dropForeign(['password_reset_by']);
            $table->dropColumn(['deleted_at', 'deleted_by', 'password_reset_at', 'password_reset_by']);
        });
    }
};
