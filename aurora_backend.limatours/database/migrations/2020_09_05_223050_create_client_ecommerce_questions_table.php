<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientEcommerceQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_ecommerce_questions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger("client_id");
            $table->foreign("client_id")->references("id")->on('clients');
            $table->unsignedBigInteger("frequent_questions_id");
            $table->foreign("frequent_questions_id")->references("id")->on('frequent_questions');
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
        Schema::dropIfExists('client_ecommerce_questions');
    }
}
