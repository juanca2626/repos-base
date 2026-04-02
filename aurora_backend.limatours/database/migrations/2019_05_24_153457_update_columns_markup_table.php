<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

// @codingStandardsIgnoreLine
class UpdateColumnsMarkupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE markups MODIFY period VARCHAR(4)");
        DB::statement("ALTER TABLE hotel_clients MODIFY period VARCHAR(4)");
        DB::statement("ALTER TABLE hotel_clients MODIFY markup DECIMAL(10,2)");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE markups MODIFY period VARCHAR(4)");
        DB::statement("ALTER TABLE hotel_clients MODIFY period VARCHAR(4)");
        DB::statement("ALTER TABLE hotel_clients MODIFY markup DECIMAL(10,2)");
    }
}
