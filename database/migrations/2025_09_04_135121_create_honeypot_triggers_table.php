<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHoneypotTriggersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('honeypot_triggers', function (Blueprint $table) {
            $table->id();
            $table->string('ip', 45)->index();
            $table->text('user_agent');
            $table->json('triggers');
            $table->string('url', 500);
            $table->json('request_data');
            $table->json('headers');
            $table->timestamp('created_at')->index();
            $table->timestamp('updated_at');
            
            // Indexes for performance
            $table->index(['ip', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('honeypot_triggers');
    }
}
