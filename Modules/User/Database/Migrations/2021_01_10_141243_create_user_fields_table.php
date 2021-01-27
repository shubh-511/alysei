<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_fields', function (Blueprint $table) {
            $table->increments('user_field_id');
            $table->string('title');
            $table->string('name')->nullable();
            $table->string('type');
            $table->string('description')->nullable();
            $table->string('hint')->nullable();
            $table->enum('required',['yes','no'])->default('no');
            $table->text('css')->nullable();
            $table->string('class_name')->nullable();
            $table->enum('conditional',['yes','no'])->default('no');
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
        Schema::dropIfExists('user_fields');
    }
}
