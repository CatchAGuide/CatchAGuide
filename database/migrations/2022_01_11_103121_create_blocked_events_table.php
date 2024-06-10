<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlockedEventsTable extends Migration
{
    public function up()
    {
        Schema::create('blocked_events', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->dateTime('from');
            $table->dateTime('due');
            $table->enum('type', ['blockiert', 'booking', 'privat']);

            $table->foreignIdFor(User::class)->constrained();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('blocked_events');
    }
}
