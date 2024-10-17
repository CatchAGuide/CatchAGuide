<?php

use App\Models\BlockedEvent;
use App\Models\Guiding;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingsTable extends Migration
{
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->integer('count_of_users')->default(0);
            $table->float('price')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->foreignIdFor(User::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Guiding::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(BlockedEvent::class)->nullable()->constrained()->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bookings');
    }
}
