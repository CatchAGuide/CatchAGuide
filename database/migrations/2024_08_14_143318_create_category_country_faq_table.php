<?php

use App\Models\CategoryCountry;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoryCountryFaqTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_country_faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(CategoryCountry::class)->constrained()->cascadeOnDelete();
            $table->text('question_en');
            $table->text('answer_en');
            $table->text('question_de');
            $table->text('answer_de');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('category_country_faqs');
    }
}
