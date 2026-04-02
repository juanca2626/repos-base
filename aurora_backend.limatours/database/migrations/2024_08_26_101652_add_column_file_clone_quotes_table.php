<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnFileCloneQuotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->bigInteger('clone_file_id')->nullable();
            $table->longText('clone_parameters')->nullable();
            $table->smallInteger('clone_executed')->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn(['clone_file_id']);
            $table->dropColumn(['clone_parameters']);
            $table->dropColumn(['clone_executed']); 
        });
    }
}
