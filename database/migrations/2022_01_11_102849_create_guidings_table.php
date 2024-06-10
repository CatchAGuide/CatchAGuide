<?php

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGuidingsTable extends Migration
{
    public function up()
    {
        Schema::create('guidings', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('title');
            $table->string('slug')->nullable();
            $table->string('location');
            $table->boolean('recommended_for_anfaenger')->default(0);
            $table->boolean('recommended_for_fortgeschrittene')->default(0);
            $table->boolean('recommended_for_profis')->default(0);

            $table->text('water')->nullable();
            $table->string('water_sonstiges')->nullable();

            $table->text('targets')->nullable();
            $table->string('target_fish_sonstiges')->nullable();

            $table->text('methods')->nullable();
            $table->string('methods_sonstiges')->nullable();


            $table->integer('max_guests');
            $table->float('duration');

            $table->string('required_special_license')->nullable();

            $table->string('fishing_type')->nullable();
            $table->string('fishing_from')->nullable();

            $table->longText('description')->nullable();

            $table->string('required_equipment')->nullable();
            $table->string('provided_equipment')->nullable();
            $table->string('additional_information')->nullable();

            $table->float('price');
            $table->float('price_two_persons')->nullable();
            $table->float('price_three_persons')->nullable();
            $table->float('price_four_persons')->nullable();
            $table->float('price_five_persons')->nullable();
            $table->string('rest_method')->nullable();
            $table->string('water_name')->nullable();
            $table->string('catering')->nullable();

            $table->boolean('status')->default(1);

            $table->foreignIdFor(Media::class, 'thumbnail_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('guidings');
    }
}
