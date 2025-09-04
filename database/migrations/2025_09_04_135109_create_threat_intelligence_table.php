<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThreatIntelligenceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('threat_intelligence', function (Blueprint $table) {
            $table->id();
            $table->string('ip', 45)->index();
            $table->string('context', 50)->index();
            $table->integer('threat_score')->index();
            $table->json('threat_data');
            $table->timestamp('created_at')->index();
            $table->timestamp('updated_at');
            
            // Indexes for performance
            $table->index(['ip', 'context']);
            $table->index(['threat_score', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('threat_intelligence');
    }
}
