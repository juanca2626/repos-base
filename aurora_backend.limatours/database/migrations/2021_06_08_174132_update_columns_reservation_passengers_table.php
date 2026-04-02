
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateColumnsReservationPassengersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE reservation_passengers CHANGE COLUMN `type` `type` ENUM('ADL', 'CHD', 'INF', 'TC', 'GUI') NOT NULL DEFAULT 'ADL' ;");

        Schema::table('reservation_passengers', function (Blueprint $table) {
            $table->string('suggested_room_type', 1)->after('type')->nullable()->comment('ifx tiphab');
            $table->string('city_iso', 4)->after('country_iso')->nullable()->comment('ifx ciunac');
            $table->string('frequent', 20)->after('reservation_id')->nullable()->comment('ifx nropax');
            $table->smallInteger('order_number')->after('reservation_id')->nullable()->comment('ifx nroord');
            $table->smallInteger('sequence_number')->after('reservation_id')->nullable()->comment('ifx nrosec');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE reservation_passengers CHANGE COLUMN `type` `type` ENUM('ADL', 'CHD', 'INF') NOT NULL DEFAULT 'ADL' ;");
        Schema::table('reservation_passengers', function (Blueprint $table) {
            $table->dropColumn(['']);
        });
    }
}
