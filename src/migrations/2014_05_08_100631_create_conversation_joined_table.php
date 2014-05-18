<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateConversationJoinedTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conversation_joined', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('conversation_id');
            $table->integer('participant_id');
            $table->string('participant_type')->default('User');
            $table->softDeletes();
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
        Schema::drop('conversation_joined');
    }

}
