<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('finance_item_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('finance_item_id');
            $table->string('event_type'); // invoice_sent, invoice_unsent, paid_marked, unpaid_marked, reminder_sent, note
            $table->json('payload')->nullable();

            // Optional actor (employee/admin) - keep generic for future use
            $table->nullableMorphs('actor');

            $table->timestamps();

            $table->foreign('finance_item_id')->references('id')->on('finance_items')->onDelete('cascade');
            $table->index(['finance_item_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_item_events');
    }
};

