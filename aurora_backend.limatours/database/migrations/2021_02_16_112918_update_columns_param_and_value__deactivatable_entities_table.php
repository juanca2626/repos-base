<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateColumnsParamAndValueDeactivatableEntitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('deactivatable_entities', function (Blueprint $table) {
            $table->renameColumn('action', 'param');
            $table->string('value')->default("0");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('deactivatable_entities', function (Blueprint $table) {
            $table->renameColumn('param', 'action');
            $table->dropColumn(['value']);
        });
    }
}
