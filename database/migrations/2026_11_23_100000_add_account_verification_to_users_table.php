<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Guide Area › Account & security (spec chapter 13 / 2.4). The legal and billing data stays in
 * the columns onboarding already fills (users.guide_type, users.tax_id, user_information
 * address / company_name / legal_form / birthday). New here:
 *  - a billing verification status of its own. users.guide_status can't be reused: "pending"
 *    there takes the guide's tours offline, and a type change must not (spec 2.4);
 *  - account_type_changed_at, so the other type's data is cleared after 30 days (spec 13.6);
 *  - password_changed_at for "Last changed on …";
 *  - user_information.vat_not_registered ("I am not VAT registered", spec AC-03).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('account_verification_status', 16)->nullable()->after('guide_verified_at');
            $table->timestamp('account_verified_at')->nullable()->after('account_verification_status');
            $table->unsignedBigInteger('account_verified_by')->nullable()->after('account_verified_at');
            $table->text('account_rejection_reason')->nullable()->after('account_verified_by');
            $table->timestamp('account_type_changed_at')->nullable()->after('account_rejection_reason');
            $table->timestamp('password_changed_at')->nullable()->after('password');
        });

        Schema::table('user_information', function (Blueprint $table) {
            $table->boolean('vat_not_registered')->default(false)->after('tax_number');
        });

        // Spec chapter 15: existing guides are individual unless a company name / legal form is stored.
        DB::table('users')
            ->whereNotNull('guide_status')
            ->whereNull('guide_type')
            ->whereIn('user_information_id', DB::table('user_information')
                ->where(fn ($q) => $q->where('company_name', '<>', '')->orWhere('legal_form', '<>', ''))
                ->select('id'))
            ->update(['guide_type' => 'company']);

        DB::table('users')
            ->whereNotNull('guide_status')
            ->whereNull('guide_type')
            ->update(['guide_type' => 'private']);

        // Verified guides were checked when they were approved; everyone else in the guide
        // lifecycle starts as pending.
        DB::table('users')
            ->where('guide_status', 'verified')
            ->whereNull('account_verification_status')
            ->update([
                'account_verification_status' => 'verified',
                'account_verified_at' => DB::raw('guide_verified_at'),
            ]);

        DB::table('users')
            ->whereIn('guide_status', ['pending', 'rejected'])
            ->whereNull('account_verification_status')
            ->update(['account_verification_status' => 'pending']);
    }

    public function down(): void
    {
        Schema::table('user_information', function (Blueprint $table) {
            $table->dropColumn('vat_not_registered');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'account_verification_status',
                'account_verified_at',
                'account_verified_by',
                'account_rejection_reason',
                'account_type_changed_at',
                'password_changed_at',
            ]);
        });
    }
};
