<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnExceptRatePlanAssociationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rate_plan_associations', function (Blueprint $table) {
            $table->boolean('except')->after('object_id')->default(0)
                ->comment('Si el campo es true debe de excluir de acuerdo al campo entity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rate_plan_associations', function (Blueprint $table) {
            //
        });
    }
}
