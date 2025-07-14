<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('model_change_history', function (Blueprint $table) {
            $table->id();
            $table->string('model_type'); // e.g., 'Vacation', 'Guiding'
            $table->unsignedBigInteger('model_id'); // ID of the model record
            $table->string('field_name'); // Name of the field that changed
            $table->text('old_value')->nullable(); // Previous value (JSON encoded if needed)
            $table->text('new_value')->nullable(); // New value (JSON encoded if needed)
            $table->string('change_type')->default('update'); // 'create', 'update', 'delete'
            $table->timestamp('changed_at'); // When the change occurred
            $table->unsignedBigInteger('user_id')->nullable(); // Who made the change
            $table->string('source')->default('web'); // Source of change ('web', 'api', 'console')
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['model_type', 'model_id']);
            $table->index('changed_at');
            $table->index('field_name');
            $table->index(['model_type', 'model_id', 'field_name']);
            
            // Foreign key constraint for user_id
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_change_history');
    }
};
