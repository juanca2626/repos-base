<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnTypeClassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('type_classes', 'color')){

            Schema::table('type_classes', function (Blueprint $table) {
                $table->string('color', 8);
            });

            DB::statement("UPDATE type_classes SET color='#DDCA1F' WHERE id=1 ");
            DB::statement("UPDATE type_classes SET color='#CE3B4D' WHERE id=2 ");
            DB::statement("UPDATE type_classes SET color='#979797' WHERE id=3 ");
            DB::statement("UPDATE type_classes SET color='#EA882D' WHERE id=4 ");
            DB::statement("UPDATE type_classes SET color='#4A90E2' WHERE id=5 ");
            DB::statement("UPDATE type_classes SET color='#4bc910' WHERE id=6 ");
            DB::statement("UPDATE type_classes SET color='#04B5AA' WHERE id=7 ");
            DB::statement("UPDATE type_classes SET color='#979797' WHERE id=8 ");
            DB::statement("UPDATE type_classes SET color='#979797' WHERE id=9 ");
            DB::statement("UPDATE type_classes SET color='#979797' WHERE id=14 ");
        }




    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
