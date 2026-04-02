<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQuoteFatherIdToQuoteMergeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quote_merge', function (Blueprint $table) {
            $table->unsignedBigInteger('quote_father_id')->nullable()->after('id');

            $table->foreign('quote_father_id')
                  ->references('id')
                  ->on('quotes')
                  ->onDelete('set null'); // si se borra el quote padre, se pone null

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
        Schema::table('quote_merge', function (Blueprint $table) {
            $table->dropColumn('quote_father_id');
            $table->dropSoftDeletes();
        });
    }
}
