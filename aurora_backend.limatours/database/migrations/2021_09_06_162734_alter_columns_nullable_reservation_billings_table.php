<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColumnsNullableReservationBillingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE `reservation_billings` CHANGE COLUMN `phone` `phone` VARCHAR(191) NULL;');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE `reservation_billings` CHANGE COLUMN `email` `email` VARCHAR(191) NULL;');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE `reservation_billings` CHANGE COLUMN `address` `address` TEXT NULL;');
        Schema::disableForeignKeyConstraints();
        Schema::table('reservation_billings', function (Blueprint $table) {
            $table->unsignedBigInteger('state_id')->nullable()->change();
        });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservation_billings', function (Blueprint $table) {
            //
        });
    }
}
