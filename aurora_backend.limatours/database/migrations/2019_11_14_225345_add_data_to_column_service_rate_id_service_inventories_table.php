<?php

use App\Service;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use \Illuminate\Support\Facades\DB;
class AddDataToColumnServiceRateIdServiceInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_inventories', function (Blueprint $table) {
            DB::transaction(function () {
                $services = Service::with('service_rate')->whereHas('service_rate')->get();
                foreach ($services as $service) {
                    DB::table('service_inventories')
                        ->where('service_id', '=', $service->id)
                        ->update([
                            'service_rate_id' => $service->service_rate[0]->id
                        ]);
                }
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
