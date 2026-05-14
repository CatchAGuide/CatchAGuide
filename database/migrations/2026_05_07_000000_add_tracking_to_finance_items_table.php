<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('finance_items', function (Blueprint $table) {
            // Snapshot of invoice amounts (so later price changes won't rewrite history)
            $table->decimal('gross_amount', 10, 2)->nullable()->after('invoice_number');
            $table->decimal('commission_amount', 10, 2)->nullable()->after('gross_amount');
            $table->decimal('tax_amount', 10, 2)->nullable()->after('commission_amount');
            $table->string('currency', 3)->default('EUR')->after('tax_amount');

            // Due + reminders (bank transfer collection workflow)
            $table->timestamp('invoice_due_at')->nullable()->after('paid_at');
            $table->unsignedTinyInteger('reminder_step')->default(0)->after('invoice_due_at'); // 0 none, 1/2/3 = 3/7/10-day reminders
            $table->timestamp('last_reminder_sent_at')->nullable()->after('reminder_step');
            $table->timestamp('next_reminder_at')->nullable()->after('last_reminder_sent_at');

            $table->index(['paid_status', 'invoice_due_at']);
            $table->index(['next_reminder_at']);
        });
    }

    public function down(): void
    {
        Schema::table('finance_items', function (Blueprint $table) {
            $table->dropIndex(['paid_status', 'invoice_due_at']);
            $table->dropIndex(['next_reminder_at']);

            $table->dropColumn([
                'gross_amount',
                'commission_amount',
                'tax_amount',
                'currency',
                'invoice_due_at',
                'reminder_step',
                'last_reminder_sent_at',
                'next_reminder_at',
            ]);
        });
    }
};

