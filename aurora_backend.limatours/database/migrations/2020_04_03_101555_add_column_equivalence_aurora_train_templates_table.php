<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnEquivalenceAuroraTrainTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('train_templates', function (Blueprint $table) {
            $table->string('equivalence_aurora')->after('aurora_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('train_templates', function (Blueprint $table) {
            $table->dropColumn('equivalence_aurora');
        });
    }
}
