<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecipesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->increments('recipe_id');
            $table->integer('user_id');
            $table->string('name');
            $table->integer('meal_id');
            $table->integer('course_id');
            $table->integer('hours')->comment('preparation time');
            $table->integer('minutes')->comment('preparation time');
            $table->integer('serving');
            $table->integer('region_id');
            $table->integer('image_id');
            $table->integer('favourite_count');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recipes');
    }
}
