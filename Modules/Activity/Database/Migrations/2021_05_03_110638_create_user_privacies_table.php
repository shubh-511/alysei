<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserPrivaciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_privacies', function (Blueprint $table) {
            $table->increments('user_privacy_id');

            //Privacy
            $table->bigInteger('user_id');
            $table->enum('allow_message_from',['anyone','followers','community','nobody'])->default('anyone');
            $table->enum('who_can_view_age',['anyone','followers','community','justme'])->default('anyone');
            $table->enum('who_can_view_profile',['anyone','followers','community','justme'])->default('anyone');
            $table->string('who_can_connect')->nullable();

            //Email preferences
            $table->enum('private_messages',['0','1'])->default('1');
            $table->enum('when_someone_request_to_follow',['0','1'])->default('1');
            $table->enum('weekly_updates',['0','1'])->default('1');
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
        Schema::dropIfExists('privacy');
    }
}
