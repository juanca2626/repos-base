<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnIsoIfxToCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->string('iso_ifx', 2)->default("")->after('iso');
        });
        DB::statement('UPDATE countries SET iso_ifx = iso');
        $update_uk = \App\Country::where('iso', "GB")->first();
        $update_uk->iso_ifx = "UK";
        $update_uk->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->removeColumn(['iso_ifx']);
        });
    }
}
