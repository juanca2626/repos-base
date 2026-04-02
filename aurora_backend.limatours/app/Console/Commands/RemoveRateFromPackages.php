<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemoveRateFromPackages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'packages:remove-rate {rate_plan_id : The ID of the rate plan to remove from packages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove all package service rooms associated with a specific rate plan';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $rate_plan_id = $this->argument('rate_plan_id');

        // Obtener los IDs de las habitaciones del plan de tarifas
        $rate_plan_room_ids = DB::table('rates_plans_rooms')
            ->where('rates_plans_id', $rate_plan_id)
            ->pluck('id')
            ->toArray();

        if (empty($rate_plan_room_ids)) {
            $this->info("No rate plan rooms found for rate plan ID: {$rate_plan_id}");
            return 0;
        }

        $this->info("Found " . count($rate_plan_room_ids) . " rate plan rooms to remove from packages");

        // Eliminar los registros de package_service_rooms
        $deleted = DB::table('package_service_rooms')
            ->whereIn('rate_plan_room_id', $rate_plan_room_ids)
            ->delete();

        $this->info("Successfully removed {$deleted} package service rooms");

        return 0;
    }
}