<?php

use App\ProgressBar;
use App\Service;
use App\ServiceInclusion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceInclusionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $file_inclusions = File::get("database/data/service_inclusions.json");
        $inclusions = json_decode($file_inclusions, true);
        $created_at = date("Y-m-d H:i:s");

        DB::transaction(function () use ($inclusions, $created_at) {
            $serviceIds = [];
            foreach ($inclusions as $inclusion) {
                $service_id = '';
                if (count($serviceIds) > 0) {
                    foreach ($serviceIds as $service) {
                        if (($service['aurora_code'] == $inclusion['aurora_code']) and
                            ($service['equivalence_aurora'] == $inclusion['equivalence_aurora'])) {
                            $service_id = $service['service_id'];
                        }
                    }
                }

                if ($service_id == '') {
                    $findService = Service::where('aurora_code', $inclusion['aurora_code'])
                        ->where('equivalence_aurora', $inclusion['equivalence_aurora'])->get()->toArray();
                    if ($findService) {
                        $service_id = $findService[0]['id'];
                        $serviceIds[] = [
                            'service_id' => $service_id,
                            'aurora_code' => $inclusion['aurora_code'],
                            'equivalence_aurora' => $inclusion['equivalence_aurora']
                        ];
                    }
                }

                if ($service_id !== '') {
                    $newInclusion = new ServiceInclusion();
                    $newInclusion->service_id = $service_id;
                    $newInclusion->inclusion_id = $inclusion['inclusion_id'];
                    $newInclusion->include = $inclusion['include'];
                    $newInclusion->created_at = $created_at;
                    $newInclusion->updated_at = $created_at;
                    $newInclusion->save();

                    ProgressBar::updateOrCreate([
                        'slug' => 'service_progress_inclusions',
                        'value' => 10,
                        'type' => 'service',
                        'object_id' => $service_id
                    ]);
                }

            }
        });
    }
}
