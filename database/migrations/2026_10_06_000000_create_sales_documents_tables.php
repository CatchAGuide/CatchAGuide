<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Offer & booking confirmation builder (Admin › Sales › Offers): one sales document is sent
 * as an offer, a booking confirmation, or both. Items hold the priced lines with listing
 * snapshots; revisions are written on every send, events form the status log.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_documents', function (Blueprint $table) {
            $table->id();
            // CAG-2026-00123; filled right after insert from the id (internal, list + subject only).
            $table->string('number', 32)->nullable()->unique();
            $table->string('public_token', 32)->unique();
            $table->string('status', 20)->default('draft')->index();
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('email')->nullable()->index();
            $table->string('phone', 50)->nullable();
            $table->json('traveller_names')->nullable();
            $table->string('language', 2)->default('de');
            $table->date('valid_until')->nullable();
            $table->text('intro_text_offer')->nullable();
            $table->text('intro_text_confirmation')->nullable();
            $table->text('good_to_know')->nullable();
            $table->json('not_included')->nullable();
            $table->text('payment_note')->nullable();
            $table->date('travel_from')->nullable()->index();
            $table->date('travel_to')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->timestamp('offer_sent_at')->nullable();
            $table->timestamp('confirmation_sent_at')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            // Set while the team has not looked at a customer acceptance yet (list badge).
            $table->timestamp('acceptance_seen_at')->nullable();
            $table->unsignedBigInteger('legacy_custom_camp_offer_id')->nullable()->unique();
            $table->foreignId('created_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('sales_document_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('sales_documents')->cascadeOnDelete();
            // Camp options and tour extras point to their card item.
            $table->foreignId('parent_item_id')->nullable()->constrained('sales_document_items')->cascadeOnDelete();
            $table->string('item_type', 30);
            $table->string('listing_type', 30)->nullable();
            $table->unsignedBigInteger('listing_id')->nullable();
            $table->foreignId('partner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title_snapshot')->nullable();
            $table->string('location_snapshot')->nullable();
            $table->string('listing_url_snapshot', 500)->nullable();
            $table->text('description')->nullable();
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->unsignedInteger('persons')->nullable();
            $table->boolean('persons_follow_parent')->default(false);
            $table->unsignedInteger('days')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->string('unit_label', 100)->nullable();
            $table->string('price_unit', 30)->nullable();
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->decimal('calculated_price', 10, 2)->default(0);
            $table->decimal('line_total', 10, 2)->default(0);
            $table->boolean('is_adjusted')->default(false);
            // Listing data the builder needs to re-price the card (price table, extras, options).
            $table->json('meta')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['listing_type', 'listing_id']);
        });

        Schema::create('sales_document_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('sales_documents')->cascadeOnDelete();
            $table->unsignedInteger('revision_no');
            $table->string('output', 20);
            $table->json('snapshot');
            $table->foreignId('created_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamp('created_at')->nullable();

            $table->unique(['document_id', 'revision_no']);
        });

        Schema::create('sales_document_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('sales_documents')->cascadeOnDelete();
            $table->string('type', 30);
            // Employee who acted; null for the customer or the system (expiry job).
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('actor', 20)->default('employee');
            $table->json('payload')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['document_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_document_events');
        Schema::dropIfExists('sales_document_revisions');
        Schema::dropIfExists('sales_document_items');
        Schema::dropIfExists('sales_documents');
    }
};
