<?php

use App\ServicePenalty;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServicePenaltiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $file_service_penalties = File::get("database/data/service_penalties.json");
        $penalties = json_decode($file_service_penalties, true);
        $created_at = date("Y-m-d H:i:s");

        DB::transaction(function () use ($penalties, $created_at) {
            foreach ($penalties as $penalty) {
                $newPenalty = new ServicePenalty();
                $newPenalty->id = $penalty['id'];
                $newPenalty->name = $penalty['name'];
                $newPenalty->status = $penalty['status'];
                $newPenalty->created_at = $created_at;
                $newPenalty->updated_at = $created_at;
                $newPenalty->save();
            }
        });
    }
}
