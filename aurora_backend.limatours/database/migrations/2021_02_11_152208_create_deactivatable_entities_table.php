<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeactivatableEntitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deactivatable_entities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string("entity");
            $table->bigInteger("object_id");
            $table->integer("after_hours");
            $table->string("action");
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
        Schema::dropIfExists('deactivatable_entities');
    }
}
