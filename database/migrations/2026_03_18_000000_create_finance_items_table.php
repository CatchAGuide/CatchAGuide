<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('finance_items', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->morphs('billable');

            $table->string('invoice_status')->default('not_sent');
            $table->timestamp('invoice_sent_at')->nullable();
            $table->string('invoice_number')->nullable();

            $table->string('paid_status')->default('unpaid');
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->unique(['billable_type', 'billable_id']);
            $table->index(['invoice_status', 'paid_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_items');
    }
};

