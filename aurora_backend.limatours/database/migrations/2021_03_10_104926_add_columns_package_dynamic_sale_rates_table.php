<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsPackageDynamicSaleRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('package_dynamic_sale_rates', function (Blueprint $table) {
            $table->decimal('child_with_bed', 10, 2)->after('triple')->default(0);
            $table->decimal('child_without_bed', 10, 2)->after('child_with_bed')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('package_dynamic_sale_rates', function (Blueprint $table) {
            //
        });
    }
}
