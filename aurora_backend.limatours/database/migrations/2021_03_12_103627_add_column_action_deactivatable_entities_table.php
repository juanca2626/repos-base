<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnActionDeactivatableEntitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('deactivatable_entities', function(Blueprint $table){
            $table->string('action')->default('set')->after('entity')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('deactivatable_entities', function(Blueprint $table){
            $table->removeColumn(['action']);
        });
    }
}
